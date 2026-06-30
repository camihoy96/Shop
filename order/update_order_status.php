<?php
require 'dbconn.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $order_id = $data['id'] ?? 0;
    $status = $data['status'] ?? '';
    $action = $data['action'] ?? '';
    
    if ($order_id > 0 && in_array($status, ['shipped', 'delivered'])) {
        $conn->begin_transaction();
        
        try {
            // Update order status
            $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $order_id);
            $stmt->execute();
            
            // Add tracking record
            $descriptions = [
                'shipped' => 'Order has been shipped to customer',
                'delivered' => 'Order delivered successfully'
            ];
            
            $description = $descriptions[$status] ?? "Order status changed to {$status}";
            
            $tracking_stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, description) VALUES (?, ?, ?)");
            $tracking_stmt->bind_param("iss", $order_id, $status, $description);
            $tracking_stmt->execute();
            
            // If delivered, update delivered date
            if ($status === 'delivered') {
                $delivered_stmt = $conn->prepare("UPDATE orders SET delivered_at = NOW() WHERE id = ?");
                $delivered_stmt->bind_param("i", $order_id);
                $delivered_stmt->execute();
            }
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => "Order marked as {$status} successfully"
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data provided'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>