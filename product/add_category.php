<?php
require 'dbconn.php';
require '../admin/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $category_name = trim($data['category']);
    $category_slug = strtolower(str_replace(' ', '-', $category_name));
    
    // First, let's check if we need to create a categories table
    $table_check = $conn->query("SHOW TABLES LIKE 'product_categories'");
    
    if ($table_check->num_rows == 0) {
        // Create categories table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS product_categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) UNIQUE NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($create_table_sql);
    }
    
    // Check if category exists in categories table
    $check_sql = "SELECT COUNT(*) as count FROM product_categories WHERE slug = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $category_slug);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $exists = $check_result->fetch_assoc()['count'] > 0;
    
    if (!$exists) {
        // Insert into categories table (NO dummy product)
        $insert_sql = "INSERT INTO product_categories (name, slug) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $category_name, $category_slug);
        
        if ($insert_stmt->execute()) {
            echo json_encode(['success' => true, 'category' => $category_slug]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create category']);
        }
    } else {
        echo json_encode(['success' => true, 'category' => $category_slug]);
    }
}
?>