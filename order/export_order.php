<?php 
require 'dbconn.php';

if (!isset($_GET['id'])) {
    die('Order ID required');
}

$orderId = intval($_GET['id']);

/* Fetch order */
$orderStmt = $conn->prepare("SELECT *, 
                             DATE_FORMAT(delivered_at, '%Y-%m-%d %H:%i:%s') as delivered_at_formatted 
                             FROM orders WHERE id = ?");
$orderStmt->bind_param("i", $orderId);
$orderStmt->execute();
$order = $orderStmt->get_result()->fetch_assoc();

if (!$order) {
    die('Order not found');
}

/* Fetch items */
$itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->bind_param("i", $orderId);
$itemStmt->execute();
$items = $itemStmt->get_result();

/* Prepare delivery info */
$delivery_start = $order['expected_delivery_start'] ?? null;
$delivery_end   = $order['expected_delivery_end'] ?? null;
$delivered_date = $order['delivered_at_formatted'] ? date('M d, Y', strtotime($order['delivered_at_formatted'])) : null;
$delivered_time = $order['delivered_at_formatted'] ? date('h:i A', strtotime($order['delivered_at_formatted'])) : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt - Order #<?= htmlspecialchars($order['order_number']) ?></title>
    <style>
        /* Small receipt style (~80mm width) */
        body { font-family: Arial, sans-serif; font-size: 12px; max-width: 300px; margin: 0 auto; padding: 10px; }
        h2, h3 { margin: 5px 0; font-weight: bold; font-size: 14px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { text-align: left; padding: 3px 0; }
        th { border-bottom: 1px dashed #000; font-size: 12px; }
        td { font-size: 12px; }
        .total { font-weight: bold; text-align: right; margin-top: 5px; border-top: 1px dashed #000; padding-top: 5px; }
        .section { margin-top: 8px; }
        .print-btn { display: block; margin: 10px auto; padding: 5px 10px; font-size: 12px; }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<h2>ChronoVerse Timepieces</h2>
<h3>Order No: <?= htmlspecialchars($order['order_number']) ?></h3>

<div class="section">
    <strong>Order Date:</strong> <?= date('M d, Y', strtotime($order['created_at'])) ?><br>
    <strong>Payment:</strong> <?= ucfirst($order['payment_method']) ?><br>
    <strong>Shipping:</strong> <?= ucfirst($order['shipping_method']) ?><br>
    <strong>Tracking:</strong> <?= htmlspecialchars($order['tracking_number'] ?: 'N/A') ?><br>
    <strong>Delivery:</strong> <?= $delivery_start ? date('M d, Y', strtotime($delivery_start)) : 'N/A' ?>
      - <?= $delivery_end ? date('M d, Y', strtotime($delivery_end)) : 'N/A' ?><br>
    <?php if ($delivered_date): ?>
    <strong>Delivered:</strong> <?= $delivered_date ?> <?= $delivered_time ?><br>
    <?php endif; ?>
</div>

<div class="section">
    <strong>Customer:</strong><br>
    <?= htmlspecialchars($order['customer_name']) ?><br>
    <?= htmlspecialchars($order['customer_email']) ?><br>
    <?= htmlspecialchars($order['customer_phone'] ?: 'N/A') ?><br>
    <?= htmlspecialchars($order['shipping_address']) ?>
</div>

<div class="section">
    <strong>Items</strong>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['total_price'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<p class="total">
    Shipping: $<?= number_format($order['shipping_cost'], 2) ?><br>
    Total: $<?= number_format($order['total_amount'], 2) ?>
</p>

<button class="print-btn" onclick="window.print()">Print Receipt</button>

</body>
</html>
