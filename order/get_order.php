<?php
require 'dbconn.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$orderId = intval($_GET['id']);

try {
    // Get order details, include expected delivery if available
    $order_sql = "SELECT o.*, 
                  DATE_FORMAT(o.delivered_at, '%Y-%m-%d %H:%i:%s') as delivered_at_formatted,
                  (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
                  FROM orders o 
                  WHERE o.id = ?";
    
    $stmt = $conn->prepare($order_sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $order_result = $stmt->get_result();
    
    if ($order_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $order = $order_result->fetch_assoc();
    
    // Get order items
    $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $orderId);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $items = [];
    
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    // Get tracking history
    $tracking_sql = "SELECT * FROM order_tracking 
                     WHERE order_id = ? 
                     ORDER BY created_at DESC";
    $tracking_stmt = $conn->prepare($tracking_sql);
    $tracking_stmt->bind_param("i", $orderId);
    $tracking_stmt->execute();
    $tracking_result = $tracking_stmt->get_result();
    $tracking = [];
    
    while ($track = $tracking_result->fetch_assoc()) {
        $tracking[] = $track;
    }
    
    // Build delivery_info
    $delivery_info = [
        'expected_delivery_start' => $order['expected_delivery_start'] ?? null,
        'expected_delivery_end'   => $order['expected_delivery_end'] ?? null,
        'delivered_date'          => null,
        'delivered_time'          => null
    ];

    // If delivered, populate delivered date/time
    if ($order['order_status'] === 'delivered' && $order['delivered_at_formatted']) {
        $delivery_info['delivered_date'] = date('M d, Y', strtotime($order['delivered_at_formatted']));
        $delivery_info['delivered_time'] = date('h:i A', strtotime($order['delivered_at_formatted']));
    }
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items,
        'tracking' => $tracking,
        'delivery_info' => $delivery_info,
        'stats' => [
            'item_count' => count($items),
            'has_tracking' => !empty($tracking),
            'is_delivered' => $order['order_status'] === 'delivered'
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
