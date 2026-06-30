<?php
require 'dbconn.php';

// Get count of orders per country (shipping_address assumed to contain country info)
$sql = "
SELECT 
    shipping_address AS country,
    COUNT(id) AS orders
FROM orders
GROUP BY shipping_address
ORDER BY orders DESC
";

$result = $conn->query($sql);

$geo_data = [];

while ($row = $result->fetch_assoc()) {
    $geo_data[] = [
        'country' => $row['country'] ?? 'Unknown',
        'orders'  => (int)$row['orders']
    ];
}

echo json_encode($geo_data);
