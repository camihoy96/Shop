<?php
session_start();
require_once 'dbconn.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in
    echo "Access Denied. Please log in.";
    exit;
}
$user_name = $_SESSION['user_name'];

// Handle form submission to update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_input = trim($_POST['order_input']);
    $new_status = $_POST['status'];
    $delivered_at = date('Y-m-d H:i:s');
    
    if (empty($order_input)) {
        $_SESSION['error'] = "Please enter an order number or tracking number";
    } else {
        $check_query = "SELECT id, order_number, tracking_number, payment_status FROM orders 
                       WHERE order_number = ? OR tracking_number = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $order_input, $order_input);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $order = $check_result->fetch_assoc();
            $order_id = $order['id'];
            
            if ($new_status == 'delivered') {
                $update_query = "UPDATE orders SET order_status = ?, delivered_at = ?, payment_status = 'paid' WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ssi", $new_status, $delivered_at, $order_id);
                
                if ($update_stmt->execute()) {
                    $tracking_query = "INSERT INTO order_tracking (order_id, tracking_number, status, description) 
                                      VALUES (?, ?, ?, ?)";
                    $tracking_stmt = $conn->prepare($tracking_query);
                    $description = 'Order delivered and payment marked as paid';
                    $tracking_stmt->bind_param("isss", $order_id, $order['tracking_number'], $new_status, $description);
                    $tracking_stmt->execute();
                    
                    $_SESSION['success'] = "Order " . $order['order_number'] . " marked as delivered! Payment status updated to PAID.";
                } else {
                    $_SESSION['error'] = "Error updating order status";
                }
            } else {
                $update_query = "UPDATE orders SET order_status = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("si", $new_status, $order_id);
                
                if ($update_stmt->execute()) {
                    $tracking_query = "INSERT INTO order_tracking (order_id, tracking_number, status, description) 
                                      VALUES (?, ?, ?, ?)";
                    $tracking_stmt = $conn->prepare($tracking_query);
                    $description = 'Order cancelled';
                    $tracking_stmt->bind_param("isss", $order_id, $order['tracking_number'], $new_status, $description);
                    $tracking_stmt->execute();
                    
                    $_SESSION['success'] = "Order " . $order['order_number'] . " marked as cancelled!";
                } else {
                    $_SESSION['error'] = "Error updating order status";
                }
            }
        } else {
            $_SESSION['error'] = "Order or tracking number not found: " . $order_input;
        }
    }
    
    header("Location: sales.php");
    exit();
}

// Get parameters
$display_type = $_GET['display'] ?? 'delivered';
$search_query = $_GET['search'] ?? '';
$export_start_date = $_GET['export_start_date'] ?? '';
$export_end_date = $_GET['export_end_date'] ?? '';
$export_min_amount = $_GET['export_min_amount'] ?? '';
$export_max_amount = $_GET['export_max_amount'] ?? '';
$export_payment_method = $_GET['export_payment_method'] ?? '';

// Build WHERE clause
if ($display_type == 'delivered') {
    $where_conditions = ["order_status = 'delivered'", "payment_status = 'paid'"];
    $table_title = "Delivered Orders";
} else {
    $where_conditions = ["order_status = 'cancelled'"];
    $table_title = "Cancelled Orders";
}

$params = [];
$param_types = "";

if (!empty($search_query)) {
    $where_conditions[] = "(order_number LIKE ? OR tracking_number LIKE ? OR customer_name LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "sss";
}

// Add export filters
if (!empty($export_start_date)) {
    $where_conditions[] = "DATE(created_at) >= ?";
    $params[] = $export_start_date;
    $param_types .= "s";
}

if (!empty($export_end_date)) {
    $where_conditions[] = "DATE(created_at) <= ?";
    $params[] = $export_end_date;
    $param_types .= "s";
}

if (!empty($export_min_amount)) {
    $where_conditions[] = "total_amount >= ?";
    $params[] = $export_min_amount;
    $param_types .= "d";
}

if (!empty($export_max_amount)) {
    $where_conditions[] = "total_amount <= ?";
    $params[] = $export_max_amount;
    $param_types .= "d";
}

if (!empty($export_payment_method)) {
    $where_conditions[] = "payment_method = ?";
    $params[] = $export_payment_method;
    $param_types .= "s";
}

$where_clause = implode(' AND ', $where_conditions);

// Handle exports
if (isset($_GET['export'])) {
    $export_query = "SELECT * FROM orders WHERE $where_clause ORDER BY created_at DESC";
    $export_stmt = $conn->prepare($export_query);
    
    if (!empty($params)) {
        $export_stmt->bind_param($param_types, ...$params);
    }
    
    $export_stmt->execute();
    $export_result = $export_stmt->get_result();
    $export_orders = $export_result->fetch_all(MYSQLI_ASSOC);
    
    if ($_GET['export'] === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $display_type . '_orders_' . date('Y-m-d') . '.xls"');
        
        echo "<table border='1'>
            <tr>
                <th colspan='8'>$table_title - Exported on " . date('F j, Y g:i A') . "</th>
            </tr>
            <tr>
                <th>Order #</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Total Amount</th>
                <th>Payment Method</th>
                <th>Tracking Number</th>
            </tr>";
        
        foreach ($export_orders as $order) {
            echo "<tr>
                <td>{$order['order_number']}</td>
                <td>" . date('M j, Y', strtotime($order['created_at'])) . "</td>
                <td>{$order['customer_name']}</td>
                <td>" . ucfirst($order['order_status']) . "</td>
                <td>" . ucfirst($order['payment_status']) . "</td>
                <td>$" . number_format($order['total_amount'], 2) . "</td>
                <td>" . ucfirst($order['payment_method']) . "</td>
                <td>{$order['tracking_number']}</td>
            </tr>";
        }
        
        echo "</table>";
        exit();
    } elseif ($_GET['export'] === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $display_type . '_orders_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order #', 'Date', 'Customer', 'Status', 'Payment', 'Total Amount', 'Payment Method', 'Tracking Number']);
        
        foreach ($export_orders as $order) {
            fputcsv($output, [
                $order['order_number'],
                date('M j, Y', strtotime($order['created_at'])),
                $order['customer_name'],
                ucfirst($order['order_status']),
                ucfirst($order['payment_status']),
                '$' . number_format($order['total_amount'], 2),
                ucfirst($order['payment_method']),
                $order['tracking_number']
            ]);
        }
        
        fclose($output);
        exit();
    }
}

// Get data for display
$orders_query = "SELECT * FROM orders WHERE $where_clause ORDER BY created_at DESC";
$orders_stmt = $conn->prepare($orders_query);
if (!empty($params)) {
    $orders_stmt->bind_param($param_types, ...$params);
}
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders = $orders_result->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE $where_clause
";

$stats_stmt = $conn->prepare($stats_query);
if (!empty($params)) {
    $stats_stmt->bind_param($param_types, ...$params);
}
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

// User data
$first_letter = strtoupper(substr($user_name, 0, 1));
$user_id = intval($_SESSION['user_id']);

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$avatar_path = "assets/images/avatars/{$user_id}.png";
$has_custom_avatar = file_exists($avatar_path);

$sql_unread = "SELECT COUNT(*) AS unread_count FROM messages WHERE is_read = 0";
$result_unread = $conn->query($sql_unread);
$row_unread = $result_unread->fetch_assoc();
$unread_count = $row_unread['unread_count'];

$orderCountSql = "SELECT COUNT(*) AS pending_count FROM orders WHERE order_status = 'pending' AND is_read = 0";
$result = $conn->query($orderCountSql);
$pendingOrders = 0;
if ($result) {
    $row = $result->fetch_assoc();
    $pendingOrders = $row['pending_count'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - ChronoVerse Admin</title>
    <link rel="stylesheet" href="../css/admin-style.css">
   <link rel="stylesheet" href="../css/all.min.css">
   <script src="../js/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #075aae;
            --primary-dark: #054a8c;
            --secondary: #02CA02;
            --accent: #ff6b00;
            --light: #f8f9fa;
            --dark: #0a192f;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
           background-color: #f0f2f5;
            color: var(--dark);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--dark) 0%, #172a45 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 25px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 18px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-logo {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
            transform: scale(1.3);
            transition: transform 0.3s ease;
        }

        .sidebar-nav {
            flex: 1;
            width: 100%;
            padding-bottom: 20px;
        }

        .sidebar-nav a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 16px 25px;
            transition: var(--transition);
            font-weight: 500;
            border-left: 4px solid transparent;
            position: relative;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            padding-left: 30px;
            border-left: 4px solid var(--primary);
        }

        .sidebar-nav a.active {
            background: rgba(7, 90, 174, 0.15);
            color: var(--white);
            border-left: 10px solid rgba(115, 255, 0, 1);
        }

        .notif-badge {
            background: #73ff00;
            color: #030303;
            font-size: 0.7rem;
            font-weight: bold;
            border-radius: 50%;
            padding: 3px 6px;
            position: absolute;
            top: 8px;
            right: 25px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 8px rgba(115, 255, 0, 0.6);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }

        /* Header */
         .header {
            background: var(--white);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 1.8rem;
            color: #000000ff;
        }

        .user-dropdown {
            position: relative;
        }

       .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            background: var(--light);
            padding: 12px 20px;
            border-radius: 30px;
            font-weight: 500;
            transition: var(--transition);
        }

        .user-info:hover {
            background: var(--light-gray);
            transform: translateY(-2px);
        }


        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .user-avatar.has-image img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(26, 26, 58, 0.95);
            border-radius: 10px;
            padding: 10px;
            min-width: 200px;
            display: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 6px;
            transition: var(--transition);
        }

        .dropdown-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Status Form */
        .status-form {
            background: rgba(255, 255, 255, 1);
            border-radius: 15px;
            padding: 25px;
            margin-top: -20px;
            margin-bottom: 10px;
            border: 1px solid rgba(4, 190, 13, 1);
        }
        
        .status-form h3 {
            color: #4a9eff;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.3rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #000000ff;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 1);
            border: 1px solid rgba(0, 0, 0, 1);
            border-radius: 8px;
            color: black;
            font-size: 1rem;
        }
        
        .form-group select option {
            background: #f7f7fcff;
            color: black;
        }
        
        .form-group input::placeholder {
            color: rgba(5, 3, 3, 0.42);
        }
        
        .btn-generate {
            background: linear-gradient(45deg, #0d5c20ff, #0a8328ff);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            height: 42px;
            transition: all 0.3s ease;
            align-self: flex-end;
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        /* Orders Tabs */
        .orders-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            justify-content: center;
        }
        
        .tab-btn {
            padding: 12px 40px;
            background: rgba(255, 255, 255, 1);
            border: 1px solid rgba(0, 0, 0, 1);
            color: #000000ff;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 1rem;
        }
        
        .tab-btn.active {
            background: #2f42e7ff;
            color: white;
            border-color: #4a9eff;
        }
        
        .tab-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 1);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(46, 9, 9, 1);
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #01060cff;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 10px 0;
        }
        
        .stat-card .change {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 1);
        }
        
        .stat-card.primary .value { color: #0034c4ff; }
        .stat-card.success .value { color: #09521aff; }
        .stat-card.warning .value { color: #2c7e05ff; }
        .stat-card.danger .value { color: #c51324ff; }
        .stat-card.info .value { color: #0d3f9cff; }

        /* Search Bar */
        .search-container {
            max-width: 500px;
            margin: 0 auto 30px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .search-form input {
            flex: 1;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(8, 2, 2, 1);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }
        
        .search-form button {
            padding: 12px 20px;
            background: #1266c7ff;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .search-form button:hover {
            background: #107bf5ff;
        }

        /* Export Filters */
        .export-filters {
            background: rgba(255, 255, 255, 1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(0, 0, 0, 1);
        }
        
        .export-filters h4 {
            color: #123d6dff;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.2rem;
        }
        
        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #01357eff;
            font-size: 0.9rem;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 1);
            border: 1px solid rgba(8, 2, 2, 1);
            border-radius: 8px;
            color: black;
            font-size: 0.95rem;
        }
        
        .filter-group select option {
            background: #2a2a4a;
            color: white;
        }
        
        .export-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Export Buttons */
        .export-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #2014caff;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-info {
            background: #d5d81fff;
            color: black;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #4a9eff;
            color: #4a9eff;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Orders Table */
        .orders-table-container {
            background: rgba(255, 255, 255, 1);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(10, 6, 6, 1);
            overflow-x: auto;
            margin-top: 30px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }
        
        .orders-table th {
            background: rgba(167, 208, 255, 0.6);
            color: #041b3bff;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(7, 6, 6, 1);
        }
        
        .orders-table tr:hover {
            background: rgba(197, 187, 187, 1);
            color: black;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-delivered { background: rgba(19, 216, 65, 1); color: #06200cff; }
        .status-cancelled { background: rgba(220, 53, 70, 1); color: #2e0a0eff; }
        
        .payment-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .payment-paid { background: rgba(40, 167, 70, 0.89); color: #000000ff; }
        .payment-pending { background: rgba(255, 193, 7, 0.77); color: #000000ff; }
        .payment-failed { background: rgba(220, 53, 70, 0.96); color: #130708ff; }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 0.85rem;
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
        }
        
        .alert-error {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        
        .orderbadge {
            background-color: #ff3b3b;
            color: #fff;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
        }
        
        /* Active Filters */
        .active-filters {
            background: rgba(74, 158, 255, 0.1);
            padding: 10px 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #4a9eff;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a0c8ff;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 230px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main {
                margin-left: 0;
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .export-grid {
                grid-template-columns: 1fr;
            }
            
            .orders-tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
                text-align: center;
            }
            
            .export-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <img src="../image/logo.png" alt="ChronoVerse Logo" class="sidebar-logo">
                <span>ChronoVerse Sales</span>
            </h2>
        </div>
        
        <div class="sidebar-nav">
            <a href="../admin/home.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
            <a href="../admin/message.php" class="nav-link">
                <i class="fa-solid fa-envelope"></i>
                <span>Messages</span>
                <?php if ($unread_count > 0): ?>
                    <span class="notif-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="../product/product.php"><i class="fa-solid fa-briefcase"></i> <span>Product</span></a>
            <a href="../order/order.php">
                <i class="fa-solid fa-box"></i>  
                <span>Orders</span>
                <?php if($pendingOrders > 0): ?>
                    <span class="orderbadge"><?php echo $pendingOrders; ?></span>
                <?php endif; ?>
            </a>
            <a href="../analytic/analytic.php"><i class="fa-solid fa-chart-bar"></i> <span>Analytics</span></a>
            <a href="../Sales/Sales.php" class="active"><i class="fa-solid fa-chart-line"></i> <span>Sales Report</span></a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Sales Report Management <i class="fa-solid fa-chart-line" style="color: var(--primary);"></i></h1>
            <div class="user-dropdown">
                <div class="user-info" onclick="toggleDropdown()">
                    <div class="user-avatar <?php echo $has_custom_avatar ? 'has-image' : ''; ?>">
                        <?php if ($has_custom_avatar): ?>
                            <img src="<?php echo $avatar_path; ?>?<?php echo time(); ?>" alt="Profile Photo">
                        <?php endif; ?>
                        <div class="avatar-initial"><?php echo $first_letter; ?></div>
                    </div>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                    <i class="fa-solid fa-caret-down"></i>
                </div>
                <div id="dropdownMenu" class="dropdown-menu">
                    <a href="../admin/profile.php">
                        <i class="fa-solid fa-user"></i><span>Profile</span>
                    </a>
                                            
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                        <a href="..admin/user.php">
                            <i class="fa-solid fa-users"></i><span>Manage Users</span>
                        </a>
                    <?php endif; ?>

                    <a href="../logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Update Form -->
        <div class="status-form">
            <h3><i class="fas fa-truck"></i> Update Order Status</h3>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-barcode"></i> Order/Tracking Number</label>
                        <input type="text" name="order_input" placeholder="Enter order number or tracking number" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-filter"></i> Status</label>
                        <select name="status" required>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="update_status" class="btn-generate">
                            <i class="fas fa-check-circle"></i> Generate
                        </button>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 10px; font-size: 0.9rem; color: #000000ff;">
                    <i class="fas fa-info-circle"></i> Enter order number or tracking number to mark as delivered or cancelled
                </div>
            </form>
        </div>

        <!-- Orders Tabs -->
        <div class="orders-tabs">
            <button class="tab-btn <?= $display_type == 'delivered' ? 'active' : '' ?>" 
                    onclick="window.location.href='?display=delivered<?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>'">
                <i class="fas fa-check-circle"></i> Delivered Orders
            </button>
            <button class="tab-btn <?= $display_type == 'cancelled' ? 'active' : '' ?>" 
                    onclick="window.location.href='?display=cancelled<?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>'">
                <i class="fas fa-times-circle"></i> Cancelled Orders
            </button>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>TOTAL <?= strtoupper($display_type) ?> ORDERS</h3>
                <div class="value"><?= $stats['total_orders'] ?? 0 ?></div>
                <div class="change"><?= $table_title ?></div>
            </div>
            
            <?php if ($display_type == 'delivered'): ?>
            <div class="stat-card success">
                <h3>TOTAL REVENUE</h3>
                <div class="value">$<?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
                <div class="change">From delivered orders</div>
            </div>
            
            <div class="stat-card info">
                <h3>AVERAGE ORDER VALUE</h3>
                <div class="value">$<?= number_format($stats['avg_order_value'] ?? 0, 2) ?></div>
                <div class="change">Per delivered order</div>
            </div>
            <?php else: ?>
            <div class="stat-card warning">
                <h3>TOTAL CANCELLED AMOUNT</h3>
                <div class="value">$<?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
                <div class="change">Lost revenue</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Search -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="display" value="<?= $display_type ?>">
                <input type="text" name="search" placeholder="Search within <?= $display_type ?> orders..." 
                       value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search_query)): ?>
                <button type="button" class="btn btn-outline" onclick="window.location.href='?display=<?= $display_type ?>'">
                    <i class="fas fa-times"></i>
                </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Export Filters -->
        <div class="export-filters">
            <h4><i class="fas fa-download"></i> Export Filters</h4>
            <form method="GET" action="">
                <input type="hidden" name="display" value="<?= $display_type ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                
                <div class="export-grid">
                    <div class="filter-group">
                        <label><i class="fas fa-calendar"></i> Date From</label>
                        <input type="date" name="export_start_date" value="<?= $export_start_date ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-calendar"></i> Date To</label>
                        <input type="date" name="export_end_date" value="<?= $export_end_date ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-dollar-sign"></i> Min Amount</label>
                        <input type="number" name="export_min_amount" placeholder="0" step="0.01" 
                               value="<?= $export_min_amount ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-dollar-sign"></i> Max Amount</label>
                        <input type="number" name="export_max_amount" placeholder="9999" step="0.01" 
                               value="<?= $export_max_amount ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-money-bill-wave"></i> Payment Method</label>
                        <select name="export_payment_method">
                            <option value="">All Methods</option>
                            <option value="credit" <?= $export_payment_method == 'credit' ? 'selected' : '' ?>>Credit Card</option>
                            <option value="paypal" <?= $export_payment_method == 'paypal' ? 'selected' : '' ?>>PayPal</option>
                            <option value="gcash" <?= $export_payment_method == 'gcash' ? 'selected' : '' ?>>GCash</option>
                            <option value="cod" <?= $export_payment_method == 'cod' ? 'selected' : '' ?>>Cash on Delivery</option>
                            <option value="bank" <?= $export_payment_method == 'bank' ? 'selected' : '' ?>>Bank Transfer</option>
                        </select>
                    </div>
                </div>
                
                <div class="export-actions">
                    <button type="submit" name="apply_export_filters" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Export Filters
                    </button>
                    <button type="button" onclick="window.location.href='?display=<?= $display_type ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>'" 
                            class="btn btn-outline">
                        <i class="fas fa-redo"></i> Clear Export Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Export Buttons -->
        <div class="export-buttons">
            <?php 
            $export_params = $_GET;
            $export_params['export'] = 'excel';
            ?>
            <a href="?<?= http_build_query($export_params) ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Filtered Data to Excel
            </a>
            
            <?php 
            $export_params['export'] = 'csv';
            ?>
            <a href="?<?= http_build_query($export_params) ?>" class="btn btn-info">
                <i class="fas fa-file-csv"></i> Export as CSV
            </a>
            
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Filtered Report
            </button>
        </div>

        <!-- Active Filters Indicator -->
        <?php 
        $active_filters = [];
        if (!empty($export_start_date)) $active_filters[] = "From: $export_start_date";
        if (!empty($export_end_date)) $active_filters[] = "To: $export_end_date";
        if (!empty($export_min_amount)) $active_filters[] = "Min: $$export_min_amount";
        if (!empty($export_max_amount)) $active_filters[] = "Max: $$export_max_amount";
        if (!empty($export_payment_method)) $active_filters[] = "Method: " . ucfirst($export_payment_method);
        if (!empty($search_query)) $active_filters[] = "Search: \"$search_query\"";
        
        if (!empty($active_filters)): ?>
            <div class="active-filters">
                <i class="fas fa-filter"></i> 
                <strong>Active Filters:</strong> <?= implode(', ', $active_filters) ?>
            </div>
        <?php endif; ?>

        <!-- Orders Table -->
        <div class="orders-table-container">
            <h3><?= $table_title ?> (<?= count($orders) ?> orders)</h3>
            
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Orders Found</h3>
                    <p>
                        <?php if (!empty($search_query)): ?>
                            No <?= $display_type ?> orders found for "<?= htmlspecialchars($search_query) ?>"
                        <?php else: ?>
                            No <?= $display_type ?> orders found with the current filters
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Tracking</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                    <?php if ($display_type == 'delivered' && $order['delivered_at']): ?>
                                        <div style="font-size: 0.75rem; color: #2f323bff;">
                                            Delivered: <?= date('M j, Y', strtotime($order['delivered_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($order['customer_name']) ?></div>
                                    <div style="font-size: 0.8rem; color: #324b6eff;"><?= htmlspecialchars($order['customer_email']) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $order['order_status'] ?>">
                                        <?= ucfirst($order['order_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="payment-badge payment-<?= $order['payment_status'] ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #103b6bff; font-size: 1.1rem;">$<?= number_format($order['total_amount'], 2) ?></strong>
                                    <div style="font-size: 0.8rem; color: #393f52ff;">
                                        Subtotal: $<?= number_format($order['subtotal'], 2) ?>
                                    </div>
                                </td>
                                <td>
                                    <?= ucfirst($order['payment_method']) ?>
                                </td>
                                <td>
                                    <?php if ($order['tracking_number']): ?>
                                        <div style="font-size: 0.9rem; color: #0b4383ff;">
                                            <i class="fas fa-truck"></i> <?= htmlspecialchars($order['tracking_number']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #333c58ff; font-size: 0.9rem;">No tracking</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="window.open('get_order_details.php?id=<?= $order['id'] ?>', '_blank')">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Export Buttons at Bottom -->
                <div class="export-buttons" style="margin-top: 30px;">
                    <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle Dropdown
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownMenu');
            const userInfo = document.querySelector('.user-info');
            
            if (!userInfo.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Toggle Sidebar on Mobile
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        if (window.innerWidth <= 992) {
            document.addEventListener('click', function(event) {
                const sidebar = document.querySelector('.sidebar');
                const sidebarToggle = document.querySelector('.sidebar-toggle');
                
                if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            });
        }

        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('dropdownMenu').classList.remove('show');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>