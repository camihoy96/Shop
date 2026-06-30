<?php
require 'dbconn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$orderId = intval($_POST['id']);
$customerName = $conn->real_escape_string($_POST['customer_name']);
$customerEmail = $conn->real_escape_string($_POST['customer_email']);
$customerPhone = $conn->real_escape_string($_POST['customer_phone']);
$orderStatus = $conn->real_escape_string($_POST['order_status']);
$paymentStatus = $conn->real_escape_string($_POST['payment_status']);
$shippingAddress = $conn->real_escape_string($_POST['shipping_address']);
$trackingNumber = isset($_POST['tracking_number']) ? $conn->real_escape_string($_POST['tracking_number']) : null;
$notes = isset($_POST['notes']) ? $conn->real_escape_string($_POST['notes']) : null;

// Get current order status before update
$current_status_sql = "SELECT order_status FROM orders WHERE id = $orderId";
$current_result = $conn->query($current_status_sql);
$current_status = $current_result->fetch_assoc()['order_status'] ?? '';

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Update order
    $sql = "UPDATE orders SET 
            customer_name = '$customerName',
            customer_email = '$customerEmail',
            customer_phone = '$customerPhone',
            order_status = '$orderStatus',
            payment_status = '$paymentStatus',
            shipping_address = '$shippingAddress',
            tracking_number = " . ($trackingNumber ? "'$trackingNumber'" : "NULL") . ",
            notes = " . ($notes ? "'$notes'" : "NULL") . ",
            updated_at = NOW()";
    
    // Add delivered_at timestamp if status is being changed to delivered
    if ($orderStatus === 'delivered' && $current_status !== 'delivered') {
        $sql .= ", delivered_at = NOW()";
        
        // Also ensure payment is marked as paid if delivered
        if ($paymentStatus !== 'paid') {
            $sql .= ", payment_status = 'paid'";
            $paymentStatus = 'paid';
        }
    }
    
    $sql .= " WHERE id = $orderId";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error updating order: " . $conn->error);
    }
    
    // Add tracking entry for status changes
    if ($current_status !== $orderStatus) {
        $tracking_descriptions = [
            'pending' => 'Order created',
            'processing' => 'Order accepted and being processed',
            'shipped' => 'Order has been shipped',
            'delivered' => 'Order delivered successfully',
            'cancelled' => 'Order cancelled'
        ];
        
        $description = $tracking_descriptions[$orderStatus] ?? "Order status changed to {$orderStatus}";
        
        // If shipped with tracking number, include it in description
        if ($orderStatus === 'shipped' && $trackingNumber) {
            $description .= " - Tracking #: {$trackingNumber}";
        }
        
        $tracking_sql = "INSERT INTO order_tracking (order_id, status, description";
        if ($orderStatus === 'shipped' && $trackingNumber) {
            $tracking_sql .= ", tracking_number) VALUES ($orderId, '$orderStatus', '$description', '$trackingNumber')";
        } else {
            $tracking_sql .= ") VALUES ($orderId, '$orderStatus', '$description')";
        }
        
        if (!$conn->query($tracking_sql)) {
            throw new Exception("Error adding tracking entry: " . $conn->error);
        }
        
        // If marking as delivered, send notification email to customer
        if ($orderStatus === 'delivered' && $current_status !== 'delivered') {
            // You can add email notification logic here
            // sendDeliveryNotification($customerEmail, $customerName, $orderId, $trackingNumber);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return updated order info
    $updated_order_sql = "SELECT o.*, 
                         (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
                         FROM orders o WHERE id = $orderId";
    $updated_result = $conn->query($updated_order_sql);
    $updated_order = $updated_result->fetch_assoc();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order updated successfully',
        'order' => [
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'delivered_at' => $updated_order['delivered_at'] ?? null,
            'tracking_number' => $trackingNumber
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>