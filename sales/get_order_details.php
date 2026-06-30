<?php
session_start();
require_once 'dbconn.php';

$order_id = $_GET['id'] ?? 0;

// Get order details - MySQLi version
$order_query = "SELECT * FROM orders WHERE id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $order_id); // "i" for integer
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc(); // MySQLi method, no parameters

if (!$order) {
    echo '<div style="text-align: center; padding: 40px; color: #a0c8ff;">Order not found</div>';
    exit;
}

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC); // MySQLi method

// Get tracking history
$tracking_query = "SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at DESC";
$tracking_stmt = $conn->prepare($tracking_query);
$tracking_stmt->bind_param("i", $order_id);
$tracking_stmt->execute();
$tracking_result = $tracking_stmt->get_result();
$tracking = $tracking_result->fetch_all(MYSQLI_ASSOC); // MySQLi method
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0a0a2a 0%, #1a1a3a 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            min-height: 100vh;
        }
        
        .order-details-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .order-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(74, 158, 255, 0.3);
        }
        
        .order-header h1 {
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .detail-section {
            margin-bottom: 25px;
        }
        
        .detail-section h4 {
            color: #4a9eff;
            margin-bottom: 15px;
            border-bottom: 2px solid rgba(74, 158, 255, 0.3);
            padding-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .detail-item {
            margin-bottom: 12px;
            display: flex;
        }
        
        .detail-label {
            color: #a0c8ff;
            font-weight: 500;
            width: 180px;
            flex-shrink: 0;
        }
        
        .detail-value {
            color: white;
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .items-table th {
            background: rgba(74, 158, 255, 0.1);
            color: #a0c8ff;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .items-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending { background: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .status-processing { background: rgba(0, 123, 255, 0.15); color: #007bff; }
        .status-shipped { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }
        .status-delivered { background: rgba(40, 167, 69, 0.15); color: #28a745; }
        .status-cancelled { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
        
        .payment-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .payment-pending { background: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .payment-paid { background: rgba(40, 167, 69, 0.15); color: #28a745; }
        .payment-failed { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
        
        @media (max-width: 768px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }
            
            .detail-item {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
        
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4a9eff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: #2d8bff;
            transform: translateY(-2px);
        }
        .backs-button {
            display: inline-block;
            padding: 10px 20px;
            background: #4a9eff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-left: 960px;
            margin-top: -80px;
        }
        
        .backs-button:hover {
            background: #2d8bff;
            transform: translateY(-2px);
        }
        
        .print-button {
            display: inline-block;
            margin-top: 20px;
            margin-left: 10px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        
        .print-button:hover {
            background: #218838;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="order-details-container">
        <div class="order-header">
            <button onclick="window.close()" class="backs-button">
                <i class="fas fa-times"></i> Close Window
            </button>
            <h1><i class="fas fa-receipt"></i> Order Details</h1>
            <p>Order #<?= htmlspecialchars($order['order_number']) ?> | <?= date('F j, Y', strtotime($order['created_at'])) ?></p>
        </div>
        
        <div class="order-details-grid">
            <!-- Left Column -->
            <div>
                <div class="detail-section">
                    <h4><i class="fas fa-user"></i> Customer Information</h4>
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?= htmlspecialchars($order['customer_email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value"><?= htmlspecialchars($order['customer_phone']) ?></span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-map-marker-alt"></i> Shipping Information</h4>
                    <div class="detail-item">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Shipping Method:</span>
                        <span class="detail-value"><?= htmlspecialchars($order['shipping_method']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Shipping Cost:</span>
                        <span class="detail-value">$<?= number_format($order['shipping_cost'], 2) ?></span>
                    </div>
                    <?php if ($order['tracking_number']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Tracking Number:</span>
                        <span class="detail-value"><?= htmlspecialchars($order['tracking_number']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['expected_delivery_start']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Expected Delivery:</span>
                        <span class="detail-value">
                            <?= date('M j, Y', strtotime($order['expected_delivery_start'])) ?> - 
                            <?= date('M j, Y', strtotime($order['expected_delivery_end'])) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['delivered_at']): ?>
                    <div class="detail-item">
                        <span class="detail-label">Delivered At:</span>
                        <span class="detail-value"><?= date('M j, Y g:i A', strtotime($order['delivered_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column -->
            <div>
                <div class="detail-section">
                    <h4><i class="fas fa-receipt"></i> Order Summary</h4>
                    <div class="detail-item">
                        <span class="detail-label">Order Number:</span>
                        <span class="detail-value"><?= htmlspecialchars($order['order_number']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Order Date:</span>
                        <span class="detail-value"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="status-badge status-<?= $order['order_status'] ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Payment Status:</span>
                        <span class="payment-badge payment-<?= $order['payment_status'] ?>">
                            <?= ucfirst($order['payment_status']) ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Payment Method:</span>
                        <span class="detail-value"><?= ucfirst($order['payment_method']) ?></span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-dollar-sign"></i> Price Breakdown</h4>
                    <div class="detail-item">
                        <span class="detail-label">Subtotal:</span>
                        <span class="detail-value">$<?= number_format($order['subtotal'], 2) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Shipping:</span>
                        <span class="detail-value">$<?= number_format($order['shipping_cost'], 2) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total:</span>
                        <span class="detail-value" style="font-weight: bold; color: #4a9eff;">
                            $<?= number_format($order['total_amount'], 2) ?>
                        </span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-box"></i> Order Items (<?= count($items) ?>)</h4>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px; color: #a0c8ff;">
                                        No items found for this order
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td>$<?= number_format($item['product_price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>$<?= number_format($item['total_price'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if (!empty($tracking)): ?>
        <div class="detail-section" style="grid-column: 1 / -1; margin-top: 30px;">
            <h4><i class="fas fa-truck"></i> Tracking History</h4>
            <div style="padding: 15px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                <?php foreach ($tracking as $track): ?>
                <div style="padding: 10px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                    <strong style="color: #4a9eff;"><?= ucfirst(str_replace('_', ' ', $track['status'])) ?></strong>
                    <div style="color: #a0c8ff; font-size: 0.9rem;">
                        <?= date('M j, Y g:i A', strtotime($track['created_at'])) ?>
                        <?php if ($track['location']): ?> | <?= htmlspecialchars($track['location']) ?><?php endif; ?>
                    </div>
                    <?php if ($track['description']): ?>
                    <div style="color: white; margin-top: 5px;"><?= htmlspecialchars($track['description']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($order['notes']): ?>
        <div class="detail-section" style="grid-column: 1 / -1;">
            <h4><i class="fas fa-sticky-note"></i> Order Notes</h4>
            <div style="padding: 15px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                <?= nl2br(htmlspecialchars($order['notes'])) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
            <button onclick="window.print()" class="print-button">
                <i class="fas fa-print"></i> Print Order Details
            </button>
            <button onclick="window.close()" class="back-button">
                <i class="fas fa-times"></i> Close Window
            </button>
        </div>
    </div>

    <script>
        // Auto-close after printing
        window.onafterprint = function() {
            // Optional: You can add a delay before closing
            // setTimeout(() => window.close(), 1000);
        };
    </script>
</body>
</html>