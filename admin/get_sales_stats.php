<?php
session_start();
require_once 'dbconn.php'; // Adjust this path if needed

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'quick_stats':
        getQuickStats($conn);
        break;
    case 'revenue_chart':
        $days = intval($_GET['days'] ?? 7);
        getRevenueChart($conn, $days);
        break;
    case 'status_chart':
        getStatusChart($conn);
        break;
    case 'payment_chart':
        getPaymentChart($conn);
        break;
    case 'top_products':
        getTopProducts($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getQuickStats($conn) {
    $today = date('Y-m-d');
    
    // Today's orders
    $sql = "SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue 
            FROM orders 
            WHERE DATE(created_at) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $today_data = $result->fetch_assoc() ?? ['count' => 0, 'revenue' => 0];
    
    // Pending orders
    $sql = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'";
    $result = $conn->query($sql);
    $pending_data = $result->fetch_assoc() ?? ['count' => 0];
    
    // Cancelled orders
    $sql = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'cancelled' AND DATE(created_at) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $cancelled_data = $result->fetch_assoc() ?? ['count' => 0];
    
    echo json_encode([
        'success' => true,
        'today_orders' => $today_data['count'] ?? 0,
        'today_revenue' => $today_data['revenue'] ?? 0,
        'pending_orders' => $pending_data['count'] ?? 0,
        'cancelled_orders' => $cancelled_data['count'] ?? 0
    ]);
}

function getRevenueChart($conn, $days) {
    $start_date = date('Y-m-d', strtotime("-$days days"));
    $end_date = date('Y-m-d');
    
    // Generate date range
    $date_range = [];
    $current_date = strtotime($start_date);
    $end_date_ts = strtotime($end_date);
    
    while ($current_date <= $end_date_ts) {
        $date_range[date('Y-m-d', $current_date)] = 0;
        $current_date = strtotime('+1 day', $current_date);
    }
    
    // Get revenue data - using order_status = 'delivered' for actual sales
    $sql = "SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue 
            FROM orders 
            WHERE created_at >= ? AND order_status = 'delivered' 
            GROUP BY DATE(created_at) 
            ORDER BY date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $date_range[$row['date']] = floatval($row['revenue']);
    }
    
    $labels = array_keys($date_range);
    $values = array_values($date_range);
    
    // Format labels for display
    $formatted_labels = array_map(function($date) {
        return date('M j', strtotime($date));
    }, $labels);
    
    echo json_encode([
        'success' => true,
        'labels' => $formatted_labels,
        'values' => $values
    ]);
}

function getStatusChart($conn) {
    $sql = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
    $result = $conn->query($sql);
    
    $labels = [];
    $values = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = ucfirst($row['order_status']);
            $values[] = intval($row['count']);
        }
    }
    
    // If no data, return default values
    if (empty($labels)) {
        $labels = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        $values = [0, 0, 0, 0, 0];
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);
}

function getPaymentChart($conn) {
    $sql = "SELECT payment_method, COUNT(*) as count FROM orders GROUP BY payment_method";
    $result = $conn->query($sql);
    
    $labels = [];
    $values = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = ucfirst($row['payment_method']);
            $values[] = intval($row['count']);
        }
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);
}

function getTopProducts($conn) {
    try {
        // Query using your actual database structure
        $sql = "SELECT 
                    oi.product_name as name,
                    COALESCE(SUM(oi.quantity), 0) as total_sold,
                    COALESCE(SUM(oi.total_price), 0) as revenue
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE o.order_status = 'delivered'
                GROUP BY oi.product_id, oi.product_name
                ORDER BY total_sold DESC
                LIMIT 10";
        
        $result = $conn->query($sql);
        $products = [];
        $total_sold = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = [
                    'name' => $row['name'],
                    'total_sold' => intval($row['total_sold']),
                    'revenue' => floatval($row['revenue'])
                ];
                $total_sold += $row['total_sold'];
            }
        } else {
            // If no data, try to get any products from order_items
            $fallback_sql = "SELECT 
                                product_name as name,
                                COALESCE(SUM(quantity), 0) as total_sold,
                                COALESCE(SUM(total_price), 0) as revenue
                            FROM order_items
                            GROUP BY product_id, product_name
                            ORDER BY total_sold DESC
                            LIMIT 10";
            
            $fallback_result = $conn->query($fallback_sql);
            
            if ($fallback_result && $fallback_result->num_rows > 0) {
                while ($row = $fallback_result->fetch_assoc()) {
                    $products[] = [
                        'name' => $row['name'],
                        'total_sold' => intval($row['total_sold']),
                        'revenue' => floatval($row['revenue'])
                    ];
                    $total_sold += $row['total_sold'];
                }
            } else {
                // If still no data, return empty array
                $products = [];
                $total_sold = 0;
            }
        }
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'total_sold' => $total_sold
        ]);
        
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Error in getTopProducts: " . $e->getMessage());
        
        // Return empty data instead of sample data to avoid confusion
        echo json_encode([
            'success' => true,
            'products' => [],
            'total_sold' => 0
        ]);
    }
}
?>