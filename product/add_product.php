<?php
session_start();
require 'dbconn.php';

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic validation
    $required_fields = ['name', 'description', 'price', 'category', 'stock'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || (is_string($_POST[$field]) && trim($_POST[$field]) === '')) {
            echo json_encode(['success' => false, 'message' => "$field is required"]);
            exit;
        }
    }

    // Sanitize inputs
    $id = $_POST['id'] ?? 0;
    $name = $conn->real_escape_string(trim($_POST['name']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $price = floatval($_POST['price']);
    $category = $conn->real_escape_string(trim($_POST['category']));
    $stock = intval($_POST['stock']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $features = $_POST['features'] ?? [];

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($id > 0) {
            // Update existing product
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, category=?, stock=?, featured=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("ssdsiii", $name, $description, $price, $category, $stock, $featured, $id);
        } else {
            // Insert new product
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, stock, featured) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsii", $name, $description, $price, $category, $stock, $featured);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error saving product: " . $stmt->error);
        }
        
        $product_id = $id > 0 ? $id : $stmt->insert_id;
        $stmt->close();

        // Handle features
        if (!empty($features)) {
            // Delete existing features if editing
            if ($id > 0) {
                $delete_stmt = $conn->prepare("DELETE FROM product_features WHERE product_id = ?");
                $delete_stmt->bind_param("i", $product_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
            
            // Insert new features
            $feature_stmt = $conn->prepare("INSERT INTO product_features (product_id, feature_text, sort_order) VALUES (?, ?, ?)");
            foreach ($features as $index => $feature) {
                $cleanFeature = trim($feature);
                if (!empty($cleanFeature)) {
                    $feature_stmt->bind_param("isi", $product_id, $cleanFeature, $index);
                    $feature_stmt->execute();
                }
            }
            $feature_stmt->close();
        }

        // Handle image uploads
        if (!empty($_FILES['product_images']['name'][0])) {
            $upload_dir = "uploads/products/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $featured_image_index = $_POST['featured_image'] ?? 0;
            
            foreach ($_FILES['product_images']['tmp_name'] as $index => $tmp_name) {
                if ($_FILES['product_images']['error'][$index] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . '_' . basename($_FILES['product_images']['name'][$index]);
                    $target_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $is_featured = ($index == $featured_image_index) ? 1 : 0;
                        $image_stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_featured, sort_order) VALUES (?, ?, ?, ?)");
                        $image_stmt->bind_param("isii", $product_id, $target_path, $is_featured, $index);
                        $image_stmt->execute();
                        $image_stmt->close();
                    }
                }
            }
        }

        $conn->commit();

        // Always return JSON response
        echo json_encode([
    'success' => true,
    'message' => 'Product saved successfully!',
    'redirect' => 'product.php'
]);

        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}
?>