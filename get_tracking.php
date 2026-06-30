<?php
require_once 'dbconn.php';

header('Content-Type: application/json');

/**
 * Accept tracking input from the SAME form logic used on insert
 * Supports POST first, then GET fallback
 */
$tracking_input = $_POST['tracking_number']
    ?? $_POST['order_number']
    ?? $_GET['tracking']
    ?? null;

if (!$tracking_input) {
    echo json_encode([
        'success' => false,
        'message' => 'No tracking or order number provided'
    ]);
    exit;
}

/**
 * Get order details
 * Match against order_number OR tracking_number
 */
$order_sql = "
    SELECT 
        o.*,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
    FROM orders o
    WHERE o.order_number = ?
       OR o.tracking_number = ?
    LIMIT 1
";

$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ss", $tracking_input, $tracking_input);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode([
        'success' => false,
        'message' => 'Order not found'
    ]);
    exit;
}

/**
 * Get order items
 */
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order['id']);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$items = [];
$product_names = [];

while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;

    if (!empty($row['product_name'])) {
        $product_names[] = $row['product_name'];
    }
}

/**
 * Get tracking history
 */
$tracking_sql = "
    SELECT *
    FROM order_tracking
    WHERE order_id = ?
    ORDER BY updated_at DESC
";
$tracking_stmt = $conn->prepare($tracking_sql);
$tracking_stmt->bind_param("i", $order['id']);
$tracking_stmt->execute();
$tracking_result = $tracking_stmt->get_result();

$tracking_history = [];
while ($row = $tracking_result->fetch_assoc()) {
    $tracking_history[] = $row;
}

/**
 * Decode shipping address (if stored as JSON)
 */
$shipping_address = null;
if (!empty($order['shipping_address'])) {
    $shipping_address = json_decode($order['shipping_address'], true);
}

/**
 * Delivery info
 */
$delivery_info = [
    'expected_delivery_start' => $order['expected_delivery_start'],
    'expected_delivery_end'   => $order['expected_delivery_end'],
    'delivered_at'            => $order['delivered_at']
];

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items,
    'product_names' => $product_names, // ✅ ADDED
    'tracking_history' => $tracking_history,
    'shipping_address' => $shipping_address,
    'delivery_info' => $delivery_info
]);
