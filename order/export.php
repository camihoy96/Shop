<?php
require 'dbconn.php';

$tab = $_GET['tab'] ?? 'pending';

// Define queries for each tab
$queries = [
    'pending' => "SELECT o.*, COUNT(oi.id) as item_count 
                  FROM orders o 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.order_status = 'pending'
                  GROUP BY o.id 
                  ORDER BY o.created_at DESC",
    
    'accepted' => "SELECT o.*, COUNT(oi.id) as item_count 
                   FROM orders o 
                   LEFT JOIN order_items oi ON o.id = oi.order_id 
                   WHERE o.order_status IN ('processing', 'shipped')
                   GROUP BY o.id 
                   ORDER BY o.created_at DESC",
    
    'history' => "SELECT o.*, COUNT(oi.id) as item_count 
                  FROM orders o 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.order_status = 'delivered'
                  GROUP BY o.id 
                  ORDER BY o.created_at DESC"
];

$result = $conn->query($queries[$tab] ?? $queries['pending']);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $tab . '_orders_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Add headers
if ($tab === 'history') {
    fputcsv($output, ['Order ID', 'Customer Name', 'Customer Email', 'Order Date', 'Delivery Date', 'Items', 'Total Amount', 'Payment Status']);
} else {
    fputcsv($output, ['Order ID', 'Customer Name', 'Customer Email', 'Order Date', 'Items', 'Total Amount', 'Order Status', 'Payment Status']);
}

// Add data
while ($row = $result->fetch_assoc()) {
    if ($tab === 'history') {
        // Get delivery date
        $delivery_date_sql = "SELECT updated_at FROM order_tracking 
                            WHERE order_id = ? AND status = 'delivered' 
                            ORDER BY updated_at DESC LIMIT 1";
        $stmt = $conn->prepare($delivery_date_sql);
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $delivery_result = $stmt->get_result();
        $delivery_date = $delivery_result->fetch_assoc();
        $stmt->close();
        
        fputcsv($output, [
            $row['order_number'],
            $row['customer_name'],
            $row['customer_email'],
            date('Y-m-d', strtotime($row['created_at'])),
            $delivery_date ? date('Y-m-d', strtotime($delivery_date['updated_at'])) : 'N/A',
            $row['item_count'],
            '$' . number_format($row['total_amount'], 2),
            ucfirst($row['payment_status'])
        ]);
    } else {
        fputcsv($output, [
            $row['order_number'],
            $row['customer_name'],
            $row['customer_email'],
            date('Y-m-d', strtotime($row['created_at'])),
            $row['item_count'],
            '$' . number_format($row['total_amount'], 2),
            ucfirst($row['order_status']),
            ucfirst($row['payment_status'])
        ]);
    }
}

fclose($output);
?>