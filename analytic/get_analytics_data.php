<?php
require 'dbconn.php';

$period = $_GET['period'] ?? '7d';

$days = 7;
if ($period == '30d') $days = 30;
if ($period == '90d') $days = 90;

$sql = "
SELECT 
    DATE(created_at) as day,
    SUM(total_amount) as revenue
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
GROUP BY DATE(created_at)
ORDER BY day ASC
";

$result = $conn->query($sql);

echo json_encode($result->fetch_all(MYSQLI_ASSOC));
