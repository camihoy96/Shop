<?php

require 'dbconn.php';
require 'auth.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in
    echo "Access Denied. Please log in.";
    exit; // Stop executing the page
}
$user_name = $_SESSION['user_name'];

// Query total messages
$sql = "SELECT COUNT(*) AS total FROM messages";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_messages = $row['total'];

// Query how many messages were added this week (optional trend)
$sql_week = "SELECT COUNT(*) AS weekly_total 
             FROM messages 
             WHERE YEARWEEK(date_sent, 1) = YEARWEEK(CURDATE(), 1)";
$result_week = $conn->query($sql_week);
$row_week = $result_week->fetch_assoc();
$weekly_messages = $row_week['weekly_total'];

// Track the visitor
$ip = $_SERVER['REMOTE_ADDR'];

// Insert only if not already logged today
$sql_check = "SELECT * FROM visitors WHERE ip_address='$ip' AND DATE(visit_time)=CURDATE()";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows == 0) {
    $conn->query("INSERT INTO visitors (ip_address) VALUES ('$ip')");
}

// =============================
// Get total visitors
// =============================
$sql_total = "SELECT COUNT(*) AS total FROM visitors";
$result_total = $conn->query($sql_total);
$total_visitors = 0;
if ($result_total && $row_total = $result_total->fetch_assoc()) {
    $total_visitors = $row_total['total'];
}

// =============================
// Get today's visitors
// =============================
$sql_today = "SELECT COUNT(*) AS today_total FROM visitors WHERE DATE(visit_time) = CURDATE()";
$result_today = $conn->query($sql_today);
$today_visitors = 0;
if ($result_today && $row_today = $result_today->fetch_assoc()) {
    $today_visitors = $row_today['today_total'];
}

// Fetch latest messages
$messages_sql = "SELECT name, date_sent FROM messages ORDER BY date_sent DESC LIMIT 3";
$messages_result = $conn->query($messages_sql);

// Fetch recent visitors
$visitors_sql = "SELECT ip_address, visit_time FROM visitors ORDER BY visit_time DESC LIMIT 2";
$visitors_result = $conn->query($visitors_sql);

// Count unread messages
$sql_unread = "SELECT COUNT(*) AS unread_count FROM messages WHERE is_read = 0";
$result_unread = $conn->query($sql_unread);
$row_unread = $result_unread->fetch_assoc();

// Load site settings from database
$result = $conn->query("SELECT site_title FROM site_settings WHERE id = 1");
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
}
$unread_count = $row_unread['unread_count'];

$first_letter = strtoupper(substr($user_name, 0, 1));
$user_id = intval($_SESSION['user_id']);

// ✅ Fetch user details from database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
// ✅ Get user avatar if exists
$avatar_path = "assets/images/avatars/{$user_id}.png";
$default_avatar = "assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);

// TOTAL ITEMS (all products)
$sql_total = "SELECT COUNT(*) AS total FROM products";
$res_total = $conn->query($sql_total);
$total_active_items = $res_total->fetch_assoc()['total'] ?? 0;

// NEW ITEMS THIS WEEK
$sql_week = "
    SELECT COUNT(*) AS new_week 
    FROM products 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
";
$res_week = $conn->query($sql_week);
$new_items_this_week = $res_week->fetch_assoc()['new_week'] ?? 0;

// OPTIONAL: Items in stock
$sql_stock = "
    SELECT COUNT(*) AS in_stock 
    FROM products 
    WHERE stock > 0
";
$res_stock = $conn->query($sql_stock);
$items_in_stock = $res_stock->fetch_assoc()['in_stock'] ?? 0;

// Total Orders
$sql1 = "SELECT COUNT(*) AS total FROM orders";
$result1 = $conn->query($sql1);
$row1 = $result1->fetch_assoc();
$total_orders = $row1['total'];

// Pending Orders (based on order_status column)
$sql2 = "SELECT COUNT(*) AS total FROM orders WHERE order_status = 'pending'";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
$total_pending_orders = $row2['total'];

// Orders Today
$sql3 = "SELECT COUNT(*) AS total FROM orders 
         WHERE DATE(created_at) = CURDATE()";
$result3 = $conn->query($sql3);
$row3 = $result3->fetch_assoc();
$orders_today = $row3['total'];

// Count pending orders
$orderCountSql = "SELECT COUNT(*) AS pending_count FROM orders WHERE order_status = 'pending' AND is_read = 0";
$result = $conn->query($orderCountSql);
$pendingOrders = 0;
if ($result) {
    $row = $result->fetch_assoc();
    $pendingOrders = $row['pending_count'] ?? 0;
}

$orders_sql = "SELECT customer_name, created_at 
               FROM orders 
               WHERE order_status='pending' 
               ORDER BY created_at DESC 
               LIMIT 5";
$orders_result = $conn->query($orders_sql);


// Fetch latest delivered orders (limit to 5 for activity feed)
$sql = "
SELECT order_number, customer_name, updated_at
FROM orders
WHERE order_status = 'delivered'
ORDER BY updated_at DESC
LIMIT 5
";

$result = $conn->query($sql);

$delivered_orders = [];
while ($row = $result->fetch_assoc()) {
    $delivered_orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ChronoVerse</title>
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            --info: #17a2b8;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, var(--dark) 0%, #172a45 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 25px;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 17px;
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

        .sidebar-logo:hover {
            transform: scale(1.05);
        }

        .sidebar-nav {
            flex: 1;
            width: 100%;
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

        .sidebar-nav a i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        /* Fixed Notification Badge */
        .nav-link {
            position: relative;
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

        .action-btn {
            position: relative;
        }

        .action-btn .notif-badge {
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            font-size: 0.6rem;
            padding: 2px 5px;
            min-width: 16px;
            height: 16px;
        }

        /* Subtle pulse effect */
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Main Content */
        .main {
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
            min-height: 100vh;
        }

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
            font-size: 28px;
            font-weight: 600;
            color: var(--dark);
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
            display: inline-block;
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

        .user-info i.fa-user-circle {
            color: var(--primary);
            font-size: 22px;
        }

        /* Updated User Avatar in Header */
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--white);
            font-size: 16px;
            overflow: hidden;
            border: 2px solid var(--white);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .user-avatar.has-image img {
            display: block;
        }

        .user-avatar.has-image .avatar-initial {
            display: none;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-top: 10px;
            min-width: 180px;
            z-index: 1000;
            overflow: hidden;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            border-bottom: 1px solid var(--light-gray);
        }

        .dropdown-menu a:last-child {
            border-bottom: none;
        }

        .dropdown-menu a:hover {
            background: var(--primary);
            color: var(--white);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--danger);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .logout-btn:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.messages {
            border-top-color: var(--primary);
        }

        .stat-card.users {
            border-top-color: var(--secondary);
        }

        .stat-card.items {
            border-top-color: var(--accent);
        }

        .stat-card.orders {
            border-top-color: var(--info);
        }

        .stat-card.pending {
            border-top-color: var(--warning);
        }

        .stat-card i {
            font-size: 32px;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 10px;
            transition: var(--transition);
            display: inline-block;
        }

        .stat-card.messages i {
            background: rgba(7, 90, 174, 0.1);
            color: var(--primary);
        }

        .stat-card.users i {
            background: rgba(2, 202, 2, 0.1);
            color: var(--secondary);
        }

        .stat-card.items i {
            background: rgba(255, 107, 0, 0.1);
            color: var(--accent);
        }

        .stat-card.orders i {
            background: rgba(23, 162, 184, 0.1);
            color: var(--info);
        }

        .stat-card.pending i {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .stat-card h4 {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-card .trend {
            font-size: 14px;
            font-weight: 500;
        }

        .trend.up {
            color: var(--success);
        }

        .trend.down {
            color: var(--danger);
        }

        /* Dashboard Content */
        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card h3 {
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card h3 i {
            color: var(--primary);
        }

        /* Activity List */
        .activity-list {
            list-style: none;
        }

        .activity-list li {
            padding: 16px 0;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
        }

        .activity-list li:hover {
            background: var(--light);
            margin: 0 -20px;
            padding: 16px 20px;
            border-radius: 8px;
        }

        .activity-list li:last-child {
            border-bottom: none;
        }

        .activity-list i {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(7, 90, 174, 0.1);
            color: var(--primary);
            transition: var(--transition);
        }

        .activity-list li:hover i {
            background: var(--primary);
            color: var(--white);
            transform: scale(1.1);
        }

        .activity-time {
            font-size: 12px;
            color: var(--gray);
            margin-top: 4px;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--light);
            border: 2px solid transparent;
            border-radius: 10px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            text-align: center;
            position: relative;
        }

        .action-btn:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-3px);
            border-color: var(--primary);
        }

        .action-btn i {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .action-btn span {
            font-size: 14px;
            font-weight: 500;
        }

       /* Sales Analytics Section */
.analytics-section {
    margin-top: 40px;
}

.analytics-section h2 {
    color: var(--primary);
    margin-bottom: 25px;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-card {
    background: var(--white);
    padding: 20px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-top: 4px solid var(--primary);
    min-height: 320px; /* Reduced from auto */
    display: flex;
    flex-direction: column;
}

.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.chart-card h3 {
    color: var(--primary);
    font-size: 1.1rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.chart-card .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-shrink: 0;
}

.chart-card select {
    padding: 6px 12px;
    background: var(--light);
    border: 1px solid var(--light-gray);
    border-radius: 6px;
    color: var(--dark);
    font-size: 0.85rem;
    cursor: pointer;
    transition: var(--transition);
    height: 34px;
}

.chart-card select:hover {
    border-color: var(--primary);
}

.chart-container {
    flex: 1;
    min-height: 200px;
    max-height: 250px;
    position: relative;
    width: 100%;
}

/* Compact chart styling */
canvas {
    width: 100% !important;
    height: 100% !important;
    max-height: 220px;
}

/* Specific chart adjustments */
#revenueChart {
    max-height: 200px;
}

#statusChart {
    max-height: 200px;
}

#paymentChart {
    max-height: 200px;
}

        /* Top Products List */
        #topProductsList {
            max-height: 250px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .product-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            background: var(--light);
            border-radius: 10px;
            margin-bottom: 10px;
            border: 1px solid var(--light-gray);
            transition: var(--transition);
        }

        .product-item:hover {
            background: var(--light-gray);
        }

        .product-rank {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 0.9rem;
            margin-right: 12px;
        }

        .rank-1 { background: linear-gradient(135deg, #ffc107, #ff9800); }
        .rank-2 { background: linear-gradient(135deg, #6c757d, #495057); }
        .rank-3 { background: linear-gradient(135deg, #cd7f32, #a6682a); }
        .rank-other { background: linear-gradient(135deg, #4a9eff, #357ae8); }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 500;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .product-sales {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .product-revenue {
            text-align: right;
        }

        .revenue-amount {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .revenue-percentage {
            font-size: 0.8rem;
            color: var(--success);
        }

        /* Loading Spinner */
        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .spinner {
            margin: 0 auto;
            width: 30px;
            height: 30px;
            border: 3px solid var(--light-gray);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Flash Message */
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 18px;
            border-radius: 8px;
            color: #fff;
            font-weight: 500;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.6s ease;
        }

        .flash-message.success {
            background-color: var(--success);
        }

        .flash-message.error {
            background-color: var(--danger);
        }

        .flash-message {
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .orderbadge {
            background-color: #ff3b3b;
            color: #fff;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
            vertical-align: top;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header h2 span,
            .sidebar-nav a span {
                display: none;
            }
            
            .sidebar-nav a {
                justify-content: center;
                padding: 18px;
            }
            
            .sidebar-nav a i {
                font-size: 20px;
            }

            .notif-badge {
                top: 8px;
                right: 8px;
            }
            
            .main {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
            }
            
            .main {
                margin-left: 0;
                padding: 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .stats-grid,
            .dashboard-content,
            .analytics-grid,
            .quick-stats {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .main {
                padding: 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .stat-card,
            .card,
            .chart-card {
                padding: 20px;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        /* Animation for numbers */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .number {
            animation: countUp 0.8s ease-out;
        }
    </style>
</head>
<body>
    <?php
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message']['text'];
        $type = $_SESSION['flash_message']['type'];
        unset($_SESSION['flash_message']);
        echo "
        <div class='flash-message $type'>
            <span>$message</span>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.querySelector('.flash-message');
                if (msg) {
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 600);
                }
            }, 3000);
        </script>
        ";
    }
    ?>

    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <img src="../image/logo.png" alt="ChronoVerse Logo" class="sidebar-logo">
                <span>ChronoVerse Dashboard</span>
            </h2>
        </div>
        
        <div class="sidebar-nav">
            <a href="home.php" class="active"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
            <a href="message.php" class="nav-link">
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
            <a href="../Sales/Sales.php">
                <i class="fa-solid fa-chart-line"></i>
                <span>Sales Report</span>
            </a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Welcome Back, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
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
                    <a href="profile.php">
                        <i class="fa-solid fa-user"></i><span>Profile</span>
                    </a>
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                        <a href="user.php">
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

        <div class="stats-grid">
            <!-- Total Messages -->
            <div class="stat-card messages">
                <i class="fa-solid fa-envelope"></i>
                <h4>Total Messages</h4>
                <div class="number"><?php echo $total_messages ?? 0; ?></div>
                <div class="trend up">+<?php echo $weekly_messages ?? 0; ?> this week</div>
            </div>

            <!-- Website Visitors -->
            <div class="stat-card users">
                <i class="fa-solid fa-users"></i>
                <h4>Website Visitors</h4>
                <div class="number"><?php echo number_format($total_visitors ?? 0); ?></div>
                <div class="trend up">+<?php echo $today_visitors ?? 0; ?> today</div>
            </div>

            <!-- Total Items -->
            <div class="stat-card items">
                <i class="fa-solid fa-box"></i>
                <h4>Total Items</h4>
                <div class="number"><?php echo number_format($total_active_items); ?></div>
                <div class="trend up">+<?php echo $new_items_this_week; ?> this week</div>
                <div style="font-size:12px; color:#888; margin-top:4px;">
                    <?php echo $items_in_stock; ?> in stock
                </div>
            </div>

            <!-- Total Orders -->
            <div class="stat-card orders">
                <i class="fa-solid fa-cart-shopping"></i>
                <h4>Total Orders</h4>
                <div class="number"><?php echo $total_orders ?? 0; ?></div>
                <div class="trend up">+<?php echo $orders_today ?? 0; ?> today</div>
            </div>

            <!-- Pending Orders -->
            <div class="stat-card pending">
                <i class="fa-solid fa-hourglass-half"></i>
                <h4>Pending Orders</h4>
                <div class="number"><?php echo $total_pending_orders ?? 0; ?></div>
                <div class="trend down">- processing</div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="card">
                <h3><i class="fa-solid fa-chart-line"></i> Overview</h3>
                <p>Welcome to your <strong>ChronoVerse Dashboard</strong>! Manage your product, track performance, respond to clients, and customize your settings — all in one place.</p>
                
                <div class="quick-actions">
                    <a href="message.php" class="action-btn">
                        <i class="fa-solid fa-envelope"></i>
                        <span>Messages</span>
                        <?php if ($unread_count > 0): ?>
                            <span class="notif-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../product/product.php" class="action-btn">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>Product</span>
                    </a>
                    <a href="../analytic/analytic.php" class="action-btn">
                        <i class="fa-solid fa-chart-bar"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="../order/order.php" class="action-btn">
                        <i class="fa-solid fa-box"></i> 
                        <span>Orders</span>
                        <?php if($pendingOrders > 0): ?>
                            <span class="orderbadge"><?php echo $pendingOrders; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../settings/settings.php" class="action-btn">
                        <i class="fa-solid fa-gear"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </div>

            <div class="card recent-activity">
                <h3><i class="fa-solid fa-bell"></i> Recent Activity</h3>
                <ul class="activity-list">
                    <!-- Recent Messages -->
                    <?php if ($messages_result->num_rows > 0): ?>
                        <?php while ($msg = $messages_result->fetch_assoc()): ?>
                            <li>
                                <i class="fa-solid fa-message"></i>
                                <div>
                                    <strong>New message from <?php echo htmlspecialchars($msg['name']); ?></strong>
                                    <div class="activity-time">
                                        <?php echo date("M d, h:i A", strtotime($msg['date_sent'])); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <!-- Recent Visitors -->
                    <?php if ($visitors_result->num_rows > 0): ?>
                        <?php while ($visit = $visitors_result->fetch_assoc()): ?>
                            <li>
                                <i class="fa-solid fa-eye"></i>
                                <div>
                                    <strong>New visitor: <?php echo htmlspecialchars($visit['ip_address']); ?></strong>
                                    <div class="activity-time">
                                        <?php echo date("M d, h:i A", strtotime($visit['visit_time'])); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <!-- Recent Orders -->
                    <?php if ($orders_result->num_rows > 0): ?>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <li>
                                <i class="fa-solid fa-box"></i>
                                <div>
                                    <strong>New order from: <?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                    <div class="activity-time">
                                        <?php echo date("M d, h:i A", strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <!-- Delivered Orders -->
                    <?php if (!empty($delivered_orders)): ?>
                        <?php foreach ($delivered_orders as $order): ?>
                            <li>
                                <i class="fa-solid fa-check-circle"></i>
                                <div>
                                    <strong>Order #<?php echo htmlspecialchars($order['order_number']); ?> delivered</strong>
                                    <div class="activity-time">
                                        <?php 
                                        $updated = strtotime($order['updated_at']);
                                        $diff = time() - $updated;
                                        if ($diff < 3600) {
                                            echo floor($diff/60) . " minutes ago";
                                        } elseif ($diff < 86400) {
                                            echo floor($diff/3600) . " hours ago";
                                        } elseif ($diff < 172800) {
                                            echo "Yesterday";
                                        } else {
                                            echo date("M d, Y", $updated);
                                        }
                                        ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>
                            <i class="fa-solid fa-check-circle"></i>
                            <div>
                                <strong>No delivered orders yet</strong>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Sales Analytics Section -->
        <div class="analytics-section">
            <h2><i class="fas fa-chart-line"></i> Sales Analytics</h2>
            
            <div class="analytics-grid">
                <!-- Revenue Chart -->
                <div class="chart-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3><i class="fas fa-money-bill-wave"></i> Revenue Overview</h3>
                        <select id="revenuePeriod">
                            <option value="7">Last 7 Days</option>
                            <option value="30">Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last Year</option>
                        </select>
                    </div>
                    <canvas id="revenueChart" height="250"></canvas>
                </div>
                
                <!-- Order Status Distribution -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Order Status Distribution</h3>
                    <canvas id="statusChart" height="250"></canvas>
                </div>
                
                <!-- Payment Methods -->
                <div class="chart-card">
                    <h3><i class="fas fa-credit-card"></i> Payment Methods</h3>
                    <canvas id="paymentChart" height="250"></canvas>
                </div>
                
                <!-- Top Products -->
                <div class="chart-card">
                    <h3><i class="fas fa-star"></i> Top Selling Products</h3>
                    <div id="topProductsList">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Loading top products...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle dropdown
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.user-dropdown');
            if (!dropdown.contains(e.target)) {
                document.getElementById('dropdownMenu').style.display = 'none';
            }
        });

        // Active navigation
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.sidebar-nav a');
            const currentPage = window.location.pathname.split('/').pop();
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');
                if (linkPage === currentPage || (currentPage === 'home.php' && linkPage === 'home.php')) {
                    link.classList.add('active');
                }
                
                link.addEventListener('click', function() {
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Animate numbers on load
            const numbers = document.querySelectorAll('.number');
            numbers.forEach(number => {
                const target = parseInt(number.textContent.replace(/,/g, ''));
                if (!isNaN(target)) {
                    let current = 0;
                    const increment = target / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        number.textContent = Math.floor(current).toLocaleString();
                    }, 30);
                }
            });

            // Initialize Charts
            loadSalesData();
            
            // Handle period change for revenue chart
            document.getElementById('revenuePeriod').addEventListener('change', function() {
                loadRevenueChart(this.value);
            });
        });

        // Function to load sales data
        function loadSalesData() {
            loadRevenueChart(7); // Default to 7 days
            loadStatusChart();
            loadPaymentChart();
            loadTopProducts();
        }

        // Function to load revenue chart
        function loadRevenueChart(days = 7) {
            fetch(`get_sales_stats.php?action=revenue_chart&days=${days}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ctx = document.getElementById('revenueChart').getContext('2d');
                        
                        // Destroy existing chart if it exists
                        if (window.revenueChartInstance) {
                            window.revenueChartInstance.destroy();
                        }
                        
                        window.revenueChartInstance = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: data.labels || [],
                                datasets: [{
                                    label: 'Revenue ($)',
                                    data: data.values || [],
                                    borderColor: '#075aae',
                                    backgroundColor: 'rgba(7, 90, 174, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#075aae',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        labels: {
                                            color: '#333',
                                            font: {
                                                size: 12
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                        titleColor: '#333',
                                        bodyColor: '#333',
                                        borderColor: '#075aae',
                                        borderWidth: 1,
                                        callbacks: {
                                            label: function(context) {
                                                return `Revenue: $${context.raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.05)'
                                        },
                                        ticks: {
                                            color: '#666',
                                            font: {
                                                size: 11
                                            }
                                        }
                                    },
                                    y: {
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.05)'
                                        },
                                        ticks: {
                                            color: '#666',
                                            font: {
                                                size: 11
                                            },
                                            callback: function(value) {
                                                return '$' + value.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                            }
                                        },
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Error loading revenue chart:', error));
        }

        // Function to load status chart
        function loadStatusChart() {
            fetch('get_sales_stats.php?action=status_chart')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ctx = document.getElementById('statusChart').getContext('2d');
                        
                        if (window.statusChartInstance) {
                            window.statusChartInstance.destroy();
                        }
                        
                        const backgroundColors = [
                            'rgba(255, 193, 7, 0.8)',   // pending - yellow
                            'rgba(0, 123, 255, 0.8)',   // processing - blue
                            'rgba(23, 162, 184, 0.8)',  // shipped - teal
                            'rgba(40, 167, 69, 0.8)',   // delivered - green
                            'rgba(220, 53, 69, 0.8)'    // cancelled - red
                        ];
                        
                        window.statusChartInstance = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: data.labels || [],
                                datasets: [{
                                    data: data.values || [],
                                    backgroundColor: backgroundColors,
                                    borderColor: 'rgba(255, 255, 255, 0.5)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            color: '#333',
                                            font: {
                                                size: 11
                                            },
                                            padding: 15
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                        titleColor: '#333',
                                        bodyColor: '#333',
                                        borderColor: '#075aae',
                                        borderWidth: 1,
                                        callbacks: {
                                            label: function(context) {
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                                return `${context.label}: ${context.raw} (${percentage}%)`;
                                            }
                                        }
                                    }
                                },
                                cutout: '60%'
                            }
                        });
                    }
                })
                .catch(error => console.error('Error loading status chart:', error));
        }

        // Function to load payment chart
        function loadPaymentChart() {
            fetch('get_sales_stats.php?action=payment_chart')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const ctx = document.getElementById('paymentChart').getContext('2d');
                        
                        if (window.paymentChartInstance) {
                            window.paymentChartInstance.destroy();
                        }
                        
                        const backgroundColors = [
                            'rgba(7, 90, 174, 0.8)',    // credit - primary blue
                            'rgba(40, 167, 69, 0.8)',   // paypal - green
                            'rgba(255, 193, 7, 0.8)',   // gcash - yellow
                            'rgba(220, 53, 69, 0.8)',   // cod - red
                            'rgba(153, 102, 255, 0.8)'  // bank - purple
                        ];
                        
                        window.paymentChartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels || [],
                                datasets: [{
                                    label: 'Orders',
                                    data: data.values || [],
                                    backgroundColor: backgroundColors,
                                    borderColor: 'rgba(255, 255, 255, 0.5)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    borderSkipped: false
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                        titleColor: '#333',
                                        bodyColor: '#333',
                                        borderColor: '#075aae',
                                        borderWidth: 1
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.05)',
                                            display: false
                                        },
                                        ticks: {
                                            color: '#666',
                                            font: {
                                                size: 11
                                            }
                                        }
                                    },
                                    y: {
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.05)'
                                        },
                                        ticks: {
                                            color: '#666',
                                            font: {
                                                size: 11
                                            },
                                            precision: 0
                                        },
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => console.error('Error loading payment chart:', error));
        }

        // Function to load top products
        function loadTopProducts() {
            fetch('get_sales_stats.php?action=top_products')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('topProductsList');
                        if (data.products && data.products.length > 0) {
                            let html = '';
                            data.products.forEach((product, index) => {
                                const percentage = product.total_sold > 0 ? 
                                    Math.round((product.total_sold / data.total_sold) * 100) : 0;
                                
                                html += `
                                    <div class="product-item">
                                        <div class="product-rank rank-${index < 3 ? index + 1 : 'other'}">
                                            ${index + 1}
                                        </div>
                                        <div class="product-info">
                                            <div class="product-name">${product.name}</div>
                                            <div class="product-sales">Sold: ${product.total_sold}</div>
                                        </div>
                                        <div class="product-revenue">
                                            <div class="revenue-amount">$${parseFloat(product.revenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                                            <div class="revenue-percentage">${percentage}% of sales</div>
                                        </div>
                                    </div>
                                `;
                            });
                            container.innerHTML = html;
                        } else {
                            container.innerHTML = `
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <p>No sales data available</p>
                                </div>
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading top products:', error);
                    const container = document.getElementById('topProductsList');
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Error loading data</p>
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>