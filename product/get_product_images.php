<?php
require 'dbconn.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Fetch product images
    $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_featured DESC, sort_order ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($images);
} else {
    echo json_encode([]);
}
?>