<?php
require 'dbconn.php';   // ← this connects to MySQL

$period = $_GET['period'] ?? '7d';

$days = 7;
if ($period == '30d') $days = 30;
if ($period == '90d') $days = 90;

$sql = "
SELECT 
    DATE(created_at) AS sale_date,
    SUM(total_amount) AS revenue,
    COUNT(id) AS orders
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
GROUP BY DATE(created_at)
ORDER BY sale_date ASC
";

$result = $conn->query($sql);

$dates = [];
$revenue = [];
$orders = [];

while ($row = $result->fetch_assoc()) {
    $dates[]   = $row['sale_date'];
    $revenue[] = $row['revenue'];
    $orders[]  = $row['orders'];
}

echo json_encode([
    'dates'   => $dates,
    'revenue' => $revenue,
    'orders'  => $orders
]);
