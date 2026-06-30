<?php
require 'dbconn.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$product_id = intval($_GET['id']);

try {
    // Get product details (no status filter)
    $sql = "SELECT p.*, 
                   pc.name AS category_name
            FROM products p
            LEFT JOIN product_categories pc ON p.category = pc.id
            WHERE p.id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Get all product images (no featured prioritization)
    $images_sql = "SELECT * FROM product_images WHERE product_id = :id ORDER BY sort_order ASC";
    $images_stmt = $conn->prepare($images_sql);
    $images_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $images_stmt->execute();
    $images = $images_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product features
    $features_sql = "SELECT * FROM product_features WHERE product_id = :id ORDER BY sort_order ASC";
    $features_stmt = $conn->prepare($features_sql);
    $features_stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $features_stmt->execute();
    $features = $features_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add images and features to product array
    $product['images'] = $images;
    $product['features'] = $features;
    
    echo json_encode([
        'success' => true,
        'product' => $product
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
