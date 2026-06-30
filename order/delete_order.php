<?php
require 'dbconn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No order ID provided']);
    exit;
}

$orderId = intval($_GET['id']);

try {
    // Delete order (cascade will delete order_items and order_tracking)
    $sql = "DELETE FROM orders WHERE id = $orderId";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
    } else {
        throw new Exception("Error deleting order: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>