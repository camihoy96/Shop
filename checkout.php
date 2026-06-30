<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require 'dbconn.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$conn->begin_transaction();

try {
    $customer = $data['customer'];
    $address  = $customer['address'];
    $payment  = $data['payment'];
    $shipping = $data['shipping'];
    $items    = $data['items'];

    $orderNumber    = $data['orderNumber'];
    $trackingNumber = 'TRK-' . strtoupper(substr(md5(time() . rand()), 0, 10));

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $shippingMethod = $shipping['method'];
    $shippingCost   = $shipping['cost'];

    $today         = date('Y-m-d');
    $expectedStart = $today;

    switch ($shippingMethod) {
        case 'express':
            $expectedEnd = date('Y-m-d', strtotime('+3 days'));
            break;
        case 'nextday':
            $expectedEnd = date('Y-m-d', strtotime('+1 day'));
            break;
        default:
            $expectedEnd = date('Y-m-d', strtotime('+7 days'));
    }

    $totalAmount   = $subtotal + $shippingCost;
    $customerName  = $customer['firstName'] . ' ' . $customer['lastName'];
    $customerEmail = $customer['email'];
    $customerPhone = $customer['phone'];

    $shippingAddressText = implode(', ', [
        $address['street'],
        $address['barangay'],
        $address['city'],
        $address['province'],
        $address['zipCode'],
        $address['country']
    ]);

    $paymentMethod      = $payment['method'];
    $paymentDetailsJson = json_encode($payment['details']);

    // Step 1: CHECK STOCK AVAILABILITY FIRST
    foreach ($items as $item) {
        $productId = $item['id'];
        $quantity = $item['quantity'];
        
        // Check current stock
        $stockCheck = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stockCheck->bind_param("i", $productId);
        $stockCheck->execute();
        $stockResult = $stockCheck->get_result();
        
        if ($stockResult->num_rows === 0) {
            throw new Exception("Product ID {$productId} not found");
        }
        
        $product = $stockResult->fetch_assoc();
        $currentStock = $product['stock'];
        
        if ($currentStock < $quantity) {
            // Get product name for error message
            $nameCheck = $conn->prepare("SELECT name FROM products WHERE id = ?");
            $nameCheck->bind_param("i", $productId);
            $nameCheck->execute();
            $nameResult = $nameCheck->get_result();
            $productName = $nameResult->fetch_assoc()['name'] ?? 'Unknown Product';
            
            throw new Exception("Insufficient stock for '{$productName}'. Available: {$currentStock}, Requested: {$quantity}");
        }
        
        $stockCheck->close();
        if (isset($nameCheck)) $nameCheck->close();
    }

    // Step 2: INSERT ORDER
    $stmt = $conn->prepare("
        INSERT INTO orders (
            order_number,
            customer_name,
            customer_email,
            customer_phone,
            shipping_address,
            payment_method,
            payment_details,
            payment_status,
            order_status,
            shipping_method,
            shipping_cost,
            subtotal,
            total_amount,
            tracking_number,
            expected_delivery_start,
            expected_delivery_end
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->bind_param(
        "ssssssssdddsss",
        $orderNumber,
        $customerName,
        $customerEmail,
        $customerPhone,
        $shippingAddressText,
        $paymentMethod,
        $paymentDetailsJson,
        $shippingMethod,
        $shippingCost,
        $subtotal,
        $totalAmount,
        $trackingNumber,
        $expectedStart,
        $expectedEnd
    );

    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();

    // Step 3: INSERT ORDER ITEMS AND UPDATE STOCK
    $itemStmt = $conn->prepare("
        INSERT INTO order_items (
            order_id,
            product_id,
            product_name,
            product_price,
            quantity,
            total_price
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    // Also prepare stock update statement
    $stockUpdateStmt = $conn->prepare("
        UPDATE products 
        SET stock = stock - ?,
            updated_at = NOW()
        WHERE id = ?
    ");

    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        
        // Insert order item
        $itemStmt->bind_param(
            "iisidd",
            $orderId,
            $item['id'],
            $item['name'],
            $item['price'],
            $item['quantity'],
            $itemTotal
        );
        $itemStmt->execute();
        
        // Update product stock
        $stockUpdateStmt->bind_param(
            "ii",
            $item['quantity'],
            $item['id']
        );
        $stockUpdateStmt->execute();
        
        // Check if stock went negative (safety check)
        $checkStock = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $checkStock->bind_param("i", $item['id']);
        $checkStock->execute();
        $stockResult = $checkStock->get_result();
        $updatedStock = $stockResult->fetch_assoc()['stock'];
        $checkStock->close();
        
        if ($updatedStock < 0) {
            throw new Exception("Stock went negative for product ID {$item['id']}. Rolling back transaction.");
        }
    }
    $itemStmt->close();
    $stockUpdateStmt->close();

    // Step 4: INSERT TRACKING
    $trackStmt = $conn->prepare("
        INSERT INTO order_tracking (
            order_id,
            tracking_number,
            status,
            description,
            estimated_delivery,
            updated_at,
            created_at
        ) VALUES (?, ?, 'pending', 'Order received and is being processed.', ?, NOW(), NOW())
    ");

    $trackStmt->bind_param("iss", $orderId, $trackingNumber, $expectedEnd);
    $trackStmt->execute();
    $trackStmt->close();

    // Step 5: LOG STOCK CHANGES (Optional but recommended)
    $logStmt = $conn->prepare("
        INSERT INTO stock_logs (
            product_id,
            order_id,
            quantity_change,
            new_stock,
            reason,
            created_at
        ) VALUES (?, ?, ?, ?, 'Order placed', NOW())
    ");
    
    // Get final stock quantities for logging
    foreach ($items as $item) {
        $checkStock = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $checkStock->bind_param("i", $item['id']);
        $checkStock->execute();
        $stockResult = $checkStock->get_result();
        $finalStock = $stockResult->fetch_assoc()['stock'];
        $checkStock->close();
        
        $logStmt->bind_param(
            "iiii",
            $item['id'],
            $orderId,
            $item['quantity'],
            $finalStock
        );
        $logStmt->execute();
    }
    $logStmt->close();

    $conn->commit();

    echo json_encode([
        'success'        => true,
        'orderNumber'   => $orderNumber,
        'trackingNumber'=> $trackingNumber,
        'orderId'       => $orderId,
        'message'       => 'Order placed successfully! Stock quantities have been updated.'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Order processing failed: ' . $e->getMessage()
    ]);
}
?>