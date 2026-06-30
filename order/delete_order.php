<?php
require 'dbconn.php';

// Set JSON response header
header('Content-Type: application/json');

// Accept both DELETE and POST methods
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get order ID
$orderId = 0;
if (isset($_GET['id'])) {
    $orderId = intval($_GET['id']);
} elseif (isset($_POST['id'])) {
    $orderId = intval($_POST['id']);
}

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit;
}

// Check if order exists
$check_stmt = $conn->prepare("SELECT id, order_number, customer_name FROM orders WHERE id = ?");
$check_stmt->bind_param("i", $orderId);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    $check_stmt->close();
    $conn->close();
    exit;
}

$order = $check_result->fetch_assoc();
$check_stmt->close();

// Disable foreign key checks temporarily to allow deletion
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Start transaction
$conn->begin_transaction();

try {
    // Delete stock_logs related to this order (if table exists)
    $conn->query("DELETE FROM stock_logs WHERE order_id = $orderId");
    
    // Delete order tracking
    $stmt = $conn->prepare("DELETE FROM order_tracking WHERE order_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete order items
    $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Finally delete the order
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare delete statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $orderId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute delete: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Order was not deleted. No rows affected.');
    }
    
    $stmt->close();
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order "' . htmlspecialchars($order['order_number']) . '" deleted successfully.',
        'order_id' => $orderId
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    // Re-enable foreign key checks even on error
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>