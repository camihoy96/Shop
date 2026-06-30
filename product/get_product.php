<?php
require 'dbconn.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No product ID provided']);
    exit;
}

$productId = intval($_GET['id']);

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Fetch features
$features = [];
$featureStmt = $conn->prepare("SELECT feature_text FROM product_features WHERE product_id = ? ORDER BY sort_order");
$featureStmt->bind_param("i", $productId);
$featureStmt->execute();
$featureResult = $featureStmt->get_result();
while ($row = $featureResult->fetch_assoc()) {
    $features[] = $row['feature_text'];
}

$product['features'] = $features;

echo json_encode(['success' => true, ...$product]);
?>