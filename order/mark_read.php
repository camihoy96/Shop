<?php
require '../dbconn.php';

// Update all pending orders to processing or mark them as read
// Here we'll keep them pending but you can also set a flag if you have one
// For example, if you have a `is_read` column, you would do:
// UPDATE orders SET is_read = 1 WHERE order_status = 'pending'
$conn->query("UPDATE orders SET is_read = 1 WHERE order_status = 'pending' AND is_read = 0");

echo json_encode(['success' => true]);
?>
