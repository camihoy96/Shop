<?php
require_once '../admin/dbconn.php';

header('Content-Type: application/json');

if (!isset($_GET['tracking'])) {
    echo json_encode(['success' => false, 'message' => 'No tracking number provided']);
    exit;
}

$tracking_number = $conn->real_escape_string($_GET['tracking']);

// Get order details
$order_sql = "SELECT o.*, 
              (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
              FROM orders o 
              WHERE o.order_number = ? OR o.tracking_number = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ss", $tracking_number, $tracking_number);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Get order items
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order['id']);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];
while($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

// Get tracking history
$tracking_sql = "SELECT * FROM order_tracking WHERE order_id = ? ORDER BY updated_at DESC";
$tracking_stmt = $conn->prepare($tracking_sql);
$tracking_stmt->bind_param("i", $order['id']);
$tracking_stmt->execute();
$tracking_result = $tracking_stmt->get_result();
$tracking_history = [];
while($track = $tracking_result->fetch_assoc()) {
    $tracking_history[] = $track;
}

// Parse shipping address
$shipping_address = json_decode($order['shipping_address'], true);

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items,
    'tracking_history' => $tracking_history,
    'shipping_address' => $shipping_address
]);
?>