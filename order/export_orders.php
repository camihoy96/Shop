<?php
require 'dbconn.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, [
    'Order ID',
    'Order Number',
    'Customer Name',
    'Customer Email',
    'Order Date',
    'Status',
    'Payment Status',
    'Total Amount',
    'Shipping Method',
    'Tracking Number'
]);

// Data
$sql = "SELECT 
    id,
    order_number,
    customer_name,
    customer_email,
    created_at,
    order_status,
    payment_status,
    total_amount,
    shipping_method,
    tracking_number
    FROM orders 
    ORDER BY created_at DESC";
    
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['order_number'],
        $row['customer_name'],
        $row['customer_email'],
        $row['created_at'],
        $row['order_status'],
        $row['payment_status'],
        $row['total_amount'],
        $row['shipping_method'] ?? 'N/A',
        $row['tracking_number'] ?? 'N/A'
    ]);
}

fclose($output);
?>