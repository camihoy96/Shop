<?php
require 'dbconn.php';

// Set JSON response header
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Handle both DELETE and POST methods
if (($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') && isset($_GET['id'])) {
    
    $product_id = intval($_GET['id']);
    
    // Validate product ID
    if ($product_id <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid product ID.'
        ]);
        exit;
    }
    
    try {
        // Start transaction for data integrity
        $conn->begin_transaction();
        
        // Step 1: Delete product features
        $stmt = $conn->prepare("DELETE FROM product_features WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare features delete statement: ' . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $features_deleted = $stmt->affected_rows;
        $stmt->close();
        
        // Step 2: Get product images to delete files from server
        $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare image select statement: ' . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $image_paths = [];
        while ($row = $result->fetch_assoc()) {
            $image_paths[] = $row['image_path'];
        }
        $stmt->close();
        
        // Step 3: Delete product images from database
        $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare images delete statement: ' . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $images_deleted = $stmt->affected_rows;
        $stmt->close();
        
        // Step 4: Check if product exists before deleting
        $stmt = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare product select statement: ' . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if (!$product) {
            $conn->rollback();
            echo json_encode([
                'success' => false, 
                'message' => 'Product not found. It may have been already deleted.'
            ]);
            exit;
        }
        
        // Step 5: Delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Failed to prepare product delete statement: ' . $conn->error);
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product_deleted = $stmt->affected_rows;
        $stmt->close();
        
        // Step 6: Delete image files from server
        $files_deleted = 0;
        $files_failed = 0;
        
        foreach ($image_paths as $path) {
            // Construct full file path
            $full_path = __DIR__ . '/' . ltrim($path, '/');
            
            if (file_exists($full_path)) {
                if (unlink($full_path)) {
                    $files_deleted++;
                } else {
                    $files_failed++;
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Prepare success response
        $response = [
            'success' => true,
            'message' => 'Product "' . htmlspecialchars($product['name']) . '" has been deleted successfully.',
            'details' => [
                'product_id' => $product_id,
                'product_name' => $product['name'],
                'features_deleted' => $features_deleted,
                'images_deleted' => $images_deleted,
                'files_removed' => $files_deleted,
                'files_failed' => $files_failed
            ]
        ];
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Log error for debugging
        error_log('Product Delete Error: ' . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred. Please try again later.'
        ]);
    }
    
} else {
    // Invalid request method or missing ID
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request. Product ID is required.'
    ]);
}

// Close database connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>