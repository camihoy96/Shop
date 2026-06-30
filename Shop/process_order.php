<?php
require_once '../admin/dbconn.php';
require_once '../admin/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$order_data = json_decode(file_get_contents('php://input'), true);

if (!$order_data) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Generate order number
    $order_number = 'CV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    $tracking_number = 'TRK-' . strtoupper(bin2hex(random_bytes(4)));
    
    // Prepare order data
    $customer_name = $order_data['customer']['firstName'] . ' ' . $order_data['customer']['lastName'];
    $shipping_address = json_encode($order_data['customer']['address']);
    $subtotal = floatval(str_replace(['$', ','], '', $order_data['subtotal']));
    $total_amount = floatval(str_replace(['$', ','], '', $order_data['total']));
    
    // Insert order
    $order_sql = "INSERT INTO orders (
        order_number, customer_name, customer_email, customer_phone,
        shipping_address, payment_method, payment_status, order_status,
        shipping_method, shipping_cost, subtotal, total_amount, tracking_number
    ) VALUES (?, ?, ?, ?, ?, ?, 'paid', 'pending', ?, ?, ?, ?, ?)";
    
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param(
        "ssssssddddss",
        $order_number,
        $customer_name,
        $order_data['customer']['email'],
        $order_data['customer']['phone'],
        $shipping_address,
        $order_data['customer']['paymentMethod'],
        $order_data['shipping']['method'],
        $order_data['shipping']['cost'],
        $subtotal,
        $total_amount,
        $tracking_number
    );
    
    if (!$order_stmt->execute()) {
        throw new Exception("Error creating order: " . $order_stmt->error);
    }
    
    $order_id = $order_stmt->insert_id;
    $order_stmt->close();
    
    // Insert order items
    $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    
    foreach ($order_data['items'] as $item) {
        $total_price = $item['price'] * $item['quantity'];
        $item_stmt->bind_param(
            "iisdid",
            $order_id,
            $item['id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $total_price
        );
        
        if (!$item_stmt->execute()) {
            throw new Exception("Error adding order item: " . $item_stmt->error);
        }
        
        // Update product stock
        $update_stock_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_stock_sql);
        $update_stmt->bind_param("ii", $item['quantity'], $item['id']);
        $update_stmt->execute();
        $update_stmt->close();
    }
    $item_stmt->close();
    
    // Create initial tracking entry
    $tracking_sql = "INSERT INTO order_tracking (order_id, tracking_number, status, description) VALUES (?, ?, 'pending', 'Order received and being processed')";
    $tracking_stmt = $conn->prepare($tracking_sql);
    $tracking_stmt->bind_param("iss", $order_id, $tracking_number);
    $tracking_stmt->execute();
    $tracking_stmt->close();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_number' => $order_number,
        'tracking_number' => $tracking_number,
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>