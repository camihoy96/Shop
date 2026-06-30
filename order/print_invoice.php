<?php
require 'dbconn.php';

$order_id = $_GET['id'] ?? 0;

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch order items
$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?php echo $order['order_number']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .invoice { border: 1px solid #ddd; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; }
        .details { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f5f5f5; }
        .total { text-align: right; font-size: 18px; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <h1>INVOICE</h1>
            <h3>Order #<?php echo $order['order_number']; ?></h3>
            <p>Date: <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
        </div>
        
        <div class="details">
            <div style="float: left; width: 50%;">
                <h4>Bill To:</h4>
                <p><?php echo htmlspecialchars($order['customer_name']); ?><br>
                <?php echo htmlspecialchars($order['customer_email']); ?><br>
                <?php echo htmlspecialchars($order['customer_phone']); ?></p>
            </div>
            <div style="float: right; width: 50%; text-align: right;">
                <h4>Ship To:</h4>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
            <div style="clear: both;"></div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="total">
            <p>Subtotal: $<?php echo number_format($order['subtotal'], 2); ?></p>
            <p>Shipping: $<?php echo number_format($order['shipping_cost'], 2); ?></p>
            <p>Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
        </div>
        
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>ChronoVerse Watches</p>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>