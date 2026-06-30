<?php
require 'dbconn.php';
require '../admin/auth.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$id = intval($data['id']);

$conn->begin_transaction();

try {

    // Update order status AND mark as read
    $stmt = $conn->prepare("
        UPDATE orders 
        SET order_status = 'processing',
            is_read = 1
        WHERE id = ? AND order_status = 'pending'
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("Order not found or already processed");
    }

    // Optional: add tracking record
    $stmt2 = $conn->prepare("
        INSERT INTO order_tracking
        (order_id, tracking_number, status, location, description)
        VALUES (?, '', 'processing', 'Warehouse', 'Order accepted and now processing')
    ");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    $conn->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
