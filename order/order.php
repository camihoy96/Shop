<?php
require 'dbconn.php';
require '../admin/auth.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in
    echo "Access Denied. Please log in.";
    exit; // Stop executing the page
}
$user_name = $_SESSION['user_name'];

// Get order statistics
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) as pending FROM orders WHERE order_status = 'pending'")->fetch_assoc()['pending'] ?? 0;
$processing_orders = $conn->query("SELECT COUNT(*) as processing FROM orders WHERE order_status = 'processing'")->fetch_assoc()['processing'] ?? 0;
$completed_orders = $conn->query("SELECT COUNT(*) as completed FROM orders WHERE order_status = 'delivered'")->fetch_assoc()['completed'] ?? 0;
$total_revenue = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE order_status = 'delivered'")->fetch_assoc()['revenue'] ?? 0;

// Get orders based on current tab (default to pending)
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';

// Define queries for each tab
$queries = [
    'pending' => "SELECT o.*, COUNT(oi.id) as item_count 
                  FROM orders o 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.order_status = 'pending'
                  GROUP BY o.id 
                  ORDER BY o.created_at DESC",
    
    'accepted' => "SELECT o.*, COUNT(oi.id) as item_count 
                   FROM orders o 
                   LEFT JOIN order_items oi ON o.id = oi.order_id 
                   WHERE o.order_status IN ('processing', 'shipped')
                   GROUP BY o.id 
                   ORDER BY o.created_at DESC",
    
    'history' => "SELECT o.*, COUNT(oi.id) as item_count 
                  FROM orders o 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.order_status = 'delivered'
                  GROUP BY o.id 
                  ORDER BY o.created_at DESC"
];

// Execute query for current tab
$orders_query = $conn->query($queries[$current_tab]);

// Get order status distribution for chart
$status_distribution_sql = "SELECT 
    order_status,
    COUNT(*) as count,
    SUM(total_amount) as revenue
    FROM orders 
    GROUP BY order_status";
$status_distribution = $conn->query($status_distribution_sql);

// Load site settings from database
$result = $conn->query("SELECT site_title FROM site_settings WHERE id = 1");
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
}

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
$avatar_path = "../admin/assets/images/avatars/{$user_id}.png";
$default_avatar = "assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);

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
    <title>Order Management | ChronoVerse Admin</title>
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
            gap: 15px;
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

        /* Main Content */
        .main {
            margin-left: 260px;
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
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.total {
            border-top-color: var(--primary);
        }

        .stat-card.pending {
            border-top-color: var(--warning);
        }

        .stat-card.processing {
            border-top-color: var(--accent);
        }

        .stat-card.completed {
            border-top-color: var(--success);
        }

        .stat-card.revenue {
            border-top-color: var(--secondary);
        }

        .stat-card i {
            font-size: 32px;
            margin-bottom: 15px;
            padding: 20px;
            border-radius: 12px;
            transition: var(--transition);
        }

        .stat-card.total i {
            background: rgba(7, 90, 174, 0.1);
            color: var(--primary);
        }

        .stat-card.pending i {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .stat-card.processing i {
            background: rgba(255, 107, 0, 0.1);
            color: var(--accent);
        }

        .stat-card.completed i {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .stat-card.revenue i {
            background: rgba(2, 202, 2, 0.1);
            color: var(--secondary);
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

        /* Order Content */
        .table-wrapper {
    width: 100%;
    overflow-x: auto; /* enables horizontal scroll if table is wider */
}

/* Optional: hide scrollbar in some browsers */
.table-wrapper::-webkit-scrollbar {
    height: 8px;
}
.table-wrapper::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 4px;
}

    /* 2. Main container for centering */
.content-container {
    max-width: 100%;
    width: 100%;
    overflow-x: hidden;
}

/* 3. Update order-content to be more flexible */
.order-content {
    display: flex;
    flex-direction: column;
    gap: 25px;
    width: 100%;
}

/* 4. Make cards take full width but prevent overflow */
.card {
    background: var(--white);
    padding: 30px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-top: 4px solid var(--primary);
    width: 100%;
    overflow: hidden; /* Prevent content from overflowing */
}

/* 5. Improve card-header for mobile */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start; /* Changed from center to flex-start */
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--light-gray);
    flex-wrap: wrap; /* Allow wrapping on small screens */
    gap: 15px;
}
.card-header h3 {
    font-size: 22px;
    font-weight: 600;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0; /* Prevent shrinking */
}

/* 6. Improve filters for mobile */
.filters {
    display: flex;
    gap: 15px;
    margin-bottom: 0;
    flex-wrap: wrap;
    flex-grow: 1;
    justify-content: flex-end;
}

/* 7. Better table wrapper */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border-radius: 8px;
    margin-top: 20px;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
}

/* 8. Set minimum width for table */
.orders-table {
    min-width: 1000px;
    width: 100%;
    border-collapse: collapse;
}

/* 9. Improve stats grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

/* 10. Chart container sizing */
.chart-container {
    height: 300px;
    width: 100%;
    position: relative;
    margin-top: 20px;
}

        .order-content {
            display: grid;
            grid-template-columns: 1fr;
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

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-gray);
        }

        .card-header h3 {
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header h3 i {
            color: var(--primary);
        }

        .btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Montserrat', sans-serif;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--secondary);
        }

        .btn-success:hover {
            background: #029a02;
        }

        .btn-warning {
            background: var(--warning);
            color: var(--dark);
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #c5303a;
        }

        /* Order Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .orders-table th {
            background: var(--light-gray);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid var(--light-gray);
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid var(--light-gray);
        }

        .orders-table tr:hover {
            background: rgba(7, 90, 174, 0.03);
        }

        .order-id {
            font-weight: 600;
            color: var(--primary);
        }

        .customer-info {
            max-width: 200px;
        }

        .customer-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .customer-email {
            font-size: 12px;
            color: var(--gray);
        }

        .amount {
            font-weight: 600;
            color: var(--dark);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.15);
            color: var(--warning);
        }

        .status-processing {
            background: rgba(255, 107, 0, 0.15);
            color: var(--accent);
        }

        .status-shipped {
            background: rgba(0, 123, 255, 0.15);
            color: #007bff;
        }

        .status-delivered {
            background: rgba(40, 167, 69, 0.15);
            color: var(--success);
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.15);
            color: var(--danger);
        }

        .payment-status {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }

        .payment-pending {
            background: rgba(255, 193, 7, 0.15);
            color: var(--warning);
        }

        .payment-paid {
            background: rgba(40, 167, 69, 0.15);
            color: var(--success);
        }

        .payment-failed {
            background: rgba(220, 53, 69, 0.15);
            color: var(--danger);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn.view {
            background: rgba(7, 90, 174, 0.1);
            color: var(--primary);
        }

        .action-btn.edit {
            background: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .action-btn.delete {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .action-btn.view:hover {
            background: var(--primary);
            color: white;
        }

        .action-btn.edit:hover {
            background: var(--warning);
            color: white;
        }

        .action-btn.delete:hover {
            background: var(--danger);
            color: white;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 500;
            color: var(--gray);
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            background: white;
            font-family: 'Montserrat', sans-serif;
        }

        /* Charts */
        .chart-container {
            height: 300px;
            position: relative;
            margin-top: 20px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #e9ecef;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--gray);
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 20px;
            backdrop-filter: blur(5px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            width: 800px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border-top: 4px solid var(--primary);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }

        .modal-header h3 {
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
            transition: var(--transition);
            padding: 5px;
            border-radius: 50%;
        }

        .close-btn:hover {
            color: var(--danger);
            background: rgba(220, 53, 69, 0.1);
            transform: rotate(90deg);
        }

        /* Order Details */
        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .order-section {
            margin-bottom: 25px;
        }

        .order-section h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-gray);
        }

        .detail-label {
            font-weight: 500;
            color: var(--gray);
        }

        .detail-value {
            font-weight: 600;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items-table th {
            background: var(--light-gray);
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid var(--light-gray);
        }

        .timeline {
            margin-top: 20px;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }

        .timeline-date {
            font-size: 12px;
            color: var(--gray);
            min-width: 120px;
        }

        .timeline-content {
            flex: 1;
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
            
            .main {
                margin-left: 80px;
            }
            
            .order-details-grid {
                grid-template-columns: 1fr;
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
            
            .filters {
                flex-direction: column;
            }
            
            .action-buttons {
                flex-direction: column;
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
            .card {
                padding: 20px;
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
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
        .order-tabs {
            display: flex;
            border-bottom: 2px solid var(--light-gray);
            margin-bottom: 25px;
            background: var(--white);
            border-radius: 12px 12px 0 0;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .order-tab {
            padding: 18px 30px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray);
            background: var(--light);
        }
        
        .order-tab:hover {
            background: var(--light-gray);
            color: var(--dark);
        }
        
        .order-tab.active {
            background: var(--white);
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .order-tab i {
            font-size: 16px;
        }
        
        .tab-badge {
            background: var(--danger);
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Action buttons update */
        .action-btn.accept {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }
        
        .action-btn.accept:hover {
            background: var(--success);
            color: white;
        }
        
        .action-btn.complete {
            background: rgba(2, 202, 2, 0.1);
            color: var(--secondary);
        }
        
        .action-btn.complete:hover {
            background: var(--secondary);
            color: white;
        }
        
        .action-btn.ship {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .action-btn.ship:hover {
            background: #007bff;
            color: white;
        }
        
        /* Enhanced status badges */
        .status-badge.status-accepted {
            background: rgba(0, 123, 255, 0.15);
            color: #007bff;
        }
        
        /* Table wrapper improvements */
        .table-wrapper {
            max-height: 500px;
            overflow-y: auto;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .table-wrapper::-webkit-scrollbar {
            width: 8px;
        }
        
        .table-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.2);
            border-radius: 4px;
        }
        
        /* Notification for pending orders */
        .pending-notification {
            background: linear-gradient(135deg, var(--warning), #ff8c00);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
            50% { box-shadow: 0 4px 20px rgba(255, 193, 7, 0.5); }
            100% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
        }
        
        .pending-notification i {
            font-size: 24px;
        }
        
        .pending-count {
            background: white;
            color: var(--warning);
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        /* Responsive tabs */
        @media (max-width: 768px) {
            .order-tabs {
                flex-direction: column;
                border-radius: 12px;
            }
            
            .order-tab {
                padding: 15px 20px;
                border-bottom: 1px solid var(--light-gray);
                border-right: none;
            }
            
            .order-tab.active {
                border-right: 3px solid var(--primary);
                border-bottom-color: var(--light-gray);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <img src="../image/logo.png" alt="ChronoVerse Logo" class="sidebar-logo">
                <span>ChronoVerse Orders</span>
            </h2>
        </div>
        
        <div class="sidebar-nav">
            <a href="../admin/home.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
            <a href="../admin/message.php"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a>
            <a href="../product/product.php"><i class="fa-solid fa-briefcase"></i> <span>Products</span></a>
            <a href="../order/order.php" id="ordersLink" class="active">
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
            <h1>Order Management <i class="fa-solid fa-shopping-cart" style="color: var(--primary);"></i></h1>
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
                    <a href="profile.php" class="logout-btn">
                        <i class="fa-solid fa-user"></i><span>Profile</span>
                    </a>
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                        <a href="../admin/user.php" class="logout-btn">
                            <i class="fa-solid fa-user"></i><span>Manage Users</span>
                        </a>
                    <?php endif; ?>
                    <a href="../logout.php" class="logout-btn">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card total">
                <i class="fa-solid fa-shopping-cart"></i>
                <h4>Total Orders</h4>
                <div class="number"><?php echo number_format($total_orders); ?></div>
            </div>
            
            <div class="stat-card pending">
                <i class="fa-solid fa-clock"></i>
                <h4>Pending</h4>
                <div class="number"><?php echo number_format($pending_orders); ?></div>
            </div>
            
            <div class="stat-card processing">
                <i class="fa-solid fa-cog"></i>
                <h4>Processing</h4>
                <div class="number"><?php echo number_format($processing_orders); ?></div>
            </div>
            
            <div class="stat-card completed">
                <i class="fa-solid fa-check-circle"></i>
                <h4>Completed</h4>
                <div class="number"><?php echo number_format($completed_orders); ?></div>
            </div>
            
            <div class="stat-card revenue">
                <i class="fa-solid fa-money-bill-wave"></i>
                <h4>Total Revenue</h4>
                <div class="number">$<?php echo number_format($total_revenue, 2); ?></div>
            </div>
        </div>

        <?php if($pending_orders > 0): ?>
        <div class="pending-notification">
            <i class="fa-solid fa-bell"></i>
            <div style="flex: 1;">
                <strong>You have <?php echo $pending_orders; ?> pending order(s) requiring attention</strong>
                <p style="margin-top: 5px; font-size: 14px; opacity: 0.9;">Click on Pending tab to review and accept orders</p>
            </div>
            <a href="?tab=pending" class="pending-count">View Now</a>
        </div>
        <?php endif; ?>

        <div class="order-content">
            <div class="card">
                <div class="order-tabs">
                    <div class="order-tab <?php echo $current_tab == 'pending' ? 'active' : ''; ?>" 
                         onclick="switchTab('pending')">
                        <i class="fa-solid fa-clock"></i>
                        <span>Pending Orders</span>
                        <?php if($pending_orders > 0): ?>
                            <span class="tab-badge"><?php echo $pending_orders; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-tab <?php echo $current_tab == 'accepted' ? 'active' : ''; ?>" 
                         onclick="switchTab('accepted')">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Accepted Orders</span>
                    </div>
                    
                    <div class="order-tab <?php echo $current_tab == 'history' ? 'active' : ''; ?>" 
                         onclick="switchTab('history')">
                        <i class="fa-solid fa-history"></i>
                        <span>Order History</span>
                        <?php if($completed_orders > 0): ?>
                            <span class="tab-badge" style="background: var(--success);"><?php echo $completed_orders; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-header">
                    <h3>
                        <?php 
                            $tab_titles = [
                                'pending' => '<i class="fa-solid fa-clock"></i> Pending Orders',
                                'accepted' => '<i class="fa-solid fa-check-circle"></i> Accepted Orders',
                                'history' => '<i class="fa-solid fa-history"></i> Order History'
                            ];
                            echo $tab_titles[$current_tab];
                        ?>
                    </h3>
                    <div class="filters">
                        <?php if($current_tab == 'pending'): ?>
                        <div class="filter-group">
                            <label>Payment Status</label>
                            <select class="filter-select" id="paymentFilter">
                                <option value="all">All Payments</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <button class="btn" onclick="applyFilters()">
                            <i class="fa-solid fa-filter"></i> Apply Filters
                        </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-success" onclick="exportOrders('<?php echo $current_tab; ?>')">
                            <i class="fa-solid fa-download"></i> Export <?php echo ucfirst($current_tab); ?> Orders
                        </button>
                    </div>
                </div>

                <div class="tab-content <?php echo $current_tab == 'pending' ? 'active' : ''; ?>" id="pendingTab">
                    <?php if ($orders_query && $orders_query->num_rows > 0 && $current_tab == 'pending'): ?>
                        <div class="table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment Method</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders_query->fetch_assoc()): ?>
                                        <tr data-order-id="<?php echo $order['id']; ?>">
                                            <td class="order-id"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td class="customer-info">
                                                <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <div class="customer-email"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $order['item_count']; ?> item(s)</td>
                                            <td class="amount">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="payment-status payment-<?php echo $order['payment_method']; ?>">
                                                    <?php echo ucfirst($order['payment_method']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="payment-status payment-<?php echo $order['payment_status']; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn accept" 
                                                            onclick="acceptOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fa-solid fa-check"></i> Accept
                                                    </button>
                                                    <button class="action-btn view" 
                                                            onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </button>
                                                    <button class="action-btn edit" 
                                                            onclick="editOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fa-solid fa-edit"></i> Edit
                                                    </button>
                                                    <button class="action-btn delete" 
                                                            onclick="deleteOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fa-solid fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif($current_tab == 'pending'): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-clock" style="font-size: 64px; color: var(--light-gray); margin-bottom: 20px;"></i>
                            <h3>No Pending Orders</h3>
                            <p style="color: var(--gray); margin-bottom: 20px;">All orders have been processed.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-content <?php echo $current_tab == 'accepted' ? 'active' : ''; ?>" id="acceptedTab">
                    <?php 
                    // Re-execute query for accepted tab
                    if($current_tab == 'accepted') {
                        $accepted_orders = $conn->query($queries['accepted']);
                    }
                    ?>
                    <?php if (isset($accepted_orders) && $accepted_orders->num_rows > 0 && $current_tab == 'accepted'): ?>
                        <div class="table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment Method</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $accepted_orders->fetch_assoc()): ?>
                                        <tr data-order-id="<?php echo $order['id']; ?>">
                                            <td class="order-id"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td class="customer-info">
                                                <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <div class="customer-email"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $order['item_count']; ?> item(s)</td>
                                            <td class="amount">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['order_status']; ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="payment-status payment-<?php echo $order['payment_method']; ?>">
                                                    <?php echo ucfirst($order['payment_method']); ?>
                                                </span>
                                            </td>
                                             <td>
                                                <span class="payment-status payment-<?php echo $order['payment_status']; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
    <div class="action-buttons">
        <?php if ($order['order_status'] == 'processing'): ?>
        <button class="action-btn ship" 
                onclick="markAsShipped(<?php echo $order['id']; ?>)">
            <i class="fa-solid fa-truck"></i> Mark as Shipped
        </button>
        <?php endif; ?>
        
        <button class="action-btn view" 
                onclick="viewOrder(<?php echo $order['id']; ?>)">
            <i class="fa-solid fa-eye"></i> View
        </button>
        
        <button class="action-btn edit" 
                onclick="editOrder(<?php echo $order['id']; ?>)">
            <i class="fa-solid fa-edit"></i> Edit
        </button>
    </div>
</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif($current_tab == 'accepted'): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-check-circle" style="font-size: 64px; color: var(--light-gray); margin-bottom: 20px;"></i>
                            <h3>No Accepted Orders</h3>
                            <p style="color: var(--gray); margin-bottom: 20px;">No orders are currently being processed.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-content <?php echo $current_tab == 'history' ? 'active' : ''; ?>" id="historyTab">
                    <?php 
                    // Re-execute query for history tab
                    if($current_tab == 'history') {
                        $history_orders = $conn->query($queries['history']);
                    }
                    ?>
                    <?php if (isset($history_orders) && $history_orders->num_rows > 0 && $current_tab == 'history'): ?>
                        <div class="table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date Ordered</th>
                                        <th>Date Delivered</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Payment Method</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $history_orders->fetch_assoc()): 
                                        // Get delivery date from order tracking
                                        $delivery_date_sql = "SELECT updated_at FROM order_tracking 
                                                            WHERE order_id = ? AND status = 'delivered' 
                                                            ORDER BY updated_at DESC LIMIT 1";
                                        $stmt = $conn->prepare($delivery_date_sql);
                                        $stmt->bind_param("i", $order['id']);
                                        $stmt->execute();
                                        $delivery_result = $stmt->get_result();
                                        $delivery_date = $delivery_result->fetch_assoc();
                                        $stmt->close();
                                    ?>
                                        <tr>
                                            <td class="order-id"><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                                            <td class="customer-info">
                                                <div class="customer-name"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                                <div class="customer-email"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <?php if ($delivery_date): ?>
                                                    <?php echo date('M d, Y', strtotime($delivery_date['updated_at'])); ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $order['item_count']; ?> item(s)</td>
                                            <td class="amount">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="payment-status payment-<?php echo $order['payment_method']; ?>">
                                                    <?php echo ucfirst($order['payment_method']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="payment-status payment-<?php echo $order['payment_status']; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="action-btn view" 
                                                            onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </button>
                                                    <button class="action-btn" 
                                                            onclick="printInvoice(<?php echo $order['id']; ?>)">
                                                        <i class="fa-solid fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif($current_tab == 'history'): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-history" style="font-size: 64px; color: var(--light-gray); margin-bottom: 20px;"></i>
                            <h3>No Order History</h3>
                            <p style="color: var(--gray); margin-bottom: 20px;">No orders have been delivered yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Status Chart -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-chart-pie"></i> Order Status Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-file-invoice"></i> Order Details</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="orderModalContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div id="editOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-edit"></i> Edit Order</h3>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <div id="editOrderContent">
                <!-- Edit form will be loaded here -->
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
            if (dropdown && !dropdown.contains(e.target)) {
                document.getElementById('dropdownMenu').style.display = 'none';
            }
        });

      // Switch between tabs
        function switchTab(tabName) {
            window.location.href = `?tab=${tabName}`;
        }

        // View Order Details
        function viewOrder(orderId) {
            console.log('Fetching order:', orderId);
            fetch(`get_order.php?id=${orderId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Order data:', data);
                    if (data.success) {
                        const modalContent = document.getElementById('orderModalContent');
                        
                        // Create delivery info section if delivered
                       const deliverySection = (data.delivery_info &&
    (data.delivery_info.expected_delivery_start || data.delivery_info.expected_delivery_end)) ? `
    <div class="order-section">
        <h4><i class="fa-solid fa-truck"></i> Delivery Information</h4>

        <div class="detail-row">
            <span class="detail-label">Delivery Start:</span>
            <span class="detail-value">
                ${data.delivery_info.expected_delivery_start
                    ? new Date(data.delivery_info.expected_delivery_start).toLocaleDateString()
                    : 'N/A'}
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Arrival Date:</span>
            <span class="detail-value">
                ${data.delivery_info.expected_delivery_end
                    ? new Date(data.delivery_info.expected_delivery_end).toLocaleDateString()
                    : 'N/A'}
            </span>
        </div>

        ${data.delivery_info.delivered_at ? `
        <div class="detail-row">
            <span class="detail-label">Delivered On:</span>
            <span class="detail-value">
                ${data.delivery_info.delivered_date} ${data.delivery_info.delivered_time}
            </span>
        </div>
        ` : ''}

        ${data.order.tracking_number ? `
        <div class="detail-row">
            <span class="detail-label">Tracking Number:</span>
            <span class="detail-value">${data.order.tracking_number}</span>
        </div>
        ` : ''}
    </div>
` : '';

                        
                        modalContent.innerHTML = `
                            <div class="order-details-grid">
                                <div>
                                    <div class="order-section">
                                        <h4><i class="fa-solid fa-user"></i> Customer Information</h4>
                                        <div class="detail-row">
                                            <span class="detail-label">Name:</span>
                                            <span class="detail-value">${data.order.customer_name}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Email:</span>
                                            <span class="detail-value">${data.order.customer_email}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Phone:</span>
                                            <span class="detail-value">${data.order.customer_phone || 'N/A'}</span>
                                        </div>
                                    </div>

                                    ${deliverySection}
                                </div>

                                <div>
                                    <div class="order-section">
                                        <h4><i class="fa-solid fa-file-invoice-dollar"></i> Order Summary</h4>
                                        <div class="detail-row">
                                            <span class="detail-label">Order ID:</span>
                                            <span class="detail-value">${data.order.order_number}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Order Date:</span>
                                            <span class="detail-value">${new Date(data.order.created_at).toLocaleDateString()}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Status:</span>
                                            <span class="detail-value status-badge status-${data.order.order_status}">${data.order.order_status}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Payment:</span>
                                            <span class="detail-value payment-status payment-${data.order.payment_status}">${data.order.payment_status}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Payment Method:</span>
                                            <span class="detail-value">${data.order.payment_method || 'N/A'}</span>
                                        </div>
                                    </div>

                                    <div class="order-section">
                                        <h4><i class="fa-solid fa-receipt"></i> Price Breakdown</h4>
                                        <div class="detail-row">
                                            <span class="detail-label">Subtotal:</span>
                                            <span class="detail-value">$${parseFloat(data.order.subtotal || 0).toFixed(2)}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Shipping:</span>
                                            <span class="detail-value">$${parseFloat(data.order.shipping_cost || 0).toFixed(2)}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Total:</span>
                                            <span class="detail-value" style="font-size: 18px; color: var(--secondary);">$${parseFloat(data.order.total_amount || 0).toFixed(2)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="order-section">
                                <h4><i class="fa-solid fa-box"></i> Order Items (${data.items.length})</h4>
                                ${data.items.length > 0 ? `
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.items.map(item => `
                                            <tr>
                                                <td>${item.product_name || 'Unknown Product'}</td>
                                                <td>$${parseFloat(item.product_price || 0).toFixed(2)}</td>
                                                <td>${item.quantity || 1}</td>
                                                <td>$${parseFloat(item.total_price || 0).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                                ` : '<p>No items found for this order.</p>'}
                            </div>

                            ${data.tracking && data.tracking.length > 0 ? `
                                <div class="order-section">
                                    <h4><i class="fa-solid fa-road"></i> Tracking History</h4>
                                    <div class="timeline">
                                        ${data.tracking.map(track => `
                                            <div class="timeline-item">
                                                <div class="timeline-date">${new Date(track.created_at).toLocaleString()}</div>
                                                <div class="timeline-content">
                                                    <strong>${track.status}</strong>
                                                    ${track.description ? `<p style="margin-top: 5px; color: var(--gray);">${track.description}</p>` : ''}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
<div style="margin-top: 30px; display: flex; gap: 10px; justify-content: flex-end;">
    <button class="btn btn-secondary" onclick="exportOrder(${orderId})">
        <i class="fa-solid fa-file-export"></i> Export
    </button>
    <button class="btn btn-warning" onclick="editOrder(${orderId})">
        <i class="fa-solid fa-edit"></i> Edit Order
    </button>
    <button class="btn" onclick="closeModal()">
        <i class="fa-solid fa-times"></i> Close
    </button>
</div>
                        `;
                        document.getElementById('orderModal').classList.add('active');
                    } else {
                        alert('Error loading order details: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading order details. Please check console for details.');
                });
        }

        // Edit Order
        function editOrder(orderId) {
            console.log('Editing order:', orderId);
            fetch(`get_order.php?id=${orderId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Edit order data:', data);
                    if (data.success) {
                        const editContent = document.getElementById('editOrderContent');
                        editContent.innerHTML = `
                            <form id="editOrderForm" onsubmit="updateOrder(event, ${orderId})">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                    <div>
                                        <div class="order-section">
                                            <h4><i class="fa-solid fa-user"></i> Customer Information</h4>
                                            <div style="margin-bottom: 15px;">
                                                <label>Customer Name *</label>
                                                <input type="text" name="customer_name" class="form-control" value="${data.order.customer_name || ''}" readonly>
                                            </div>
                                            <div style="margin-bottom: 15px;">
                                                <label>Email *</label>
                                                <input type="email" name="customer_email" class="form-control" value="${data.order.customer_email || ''}" readonly>
                                            </div>
                                            <div style="margin-bottom: 15px;">
                                                <label>Phone</label>
                                                <input type="text" name="customer_phone" class="form-control" value="${data.order.customer_phone || ''}"readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="order-section">
                                            <h4><i class="fa-solid fa-cog"></i> Order Status</h4>
                                            <div style="margin-bottom: 15px;">
                                                <label>Order Status *</label>
                                                <select name="order_status" class="form-control" required>
                                                    <option value="pending" ${data.order.order_status === 'pending' ? 'selected' : ''}>Pending</option>
                                                    <option value="processing" ${data.order.order_status === 'processing' ? 'selected' : ''}>Processing</option>
                                                    <option value="shipped" ${data.order.order_status === 'shipped' ? 'selected' : ''}>Shipped</option>
                                                    <option value="delivered" ${data.order.order_status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                                    <option value="cancelled" ${data.order.order_status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                                </select>
                                            </div>
                                            <div style="margin-bottom: 15px;">
                                                <label>Payment Status *</label>
                                                <select name="payment_status" class="form-control" required>
                                                    <option value="pending" ${data.order.payment_status === 'pending' ? 'selected' : ''}>Pending</option>
                                                    <option value="paid" ${data.order.payment_status === 'paid' ? 'selected' : ''}>Paid</option>
                                                    <option value="failed" ${data.order.payment_status === 'failed' ? 'selected' : ''}>Failed</option>
                                                </select>
                                            </div>
                                            <div style="margin-bottom: 15px;">
                                                <label>Payment Method</label>
                                                <input type="text" name="payment_method" class="form-control" value="${data.order.payment_method || ''}"readonly>
                                            </div>
                                            <div style="margin-bottom: 15px;">
                                                <label>Order Number</label>
                                                <input type="text" name="order_number" class="form-control" value="${data.order.order_number || ''}" readonly>
                                            </div>
                                            <div style="margin-bottom: 15px;">
                                            <label>Tracking Number</label>
                                            <input type="text" name="tracking" class="form-control" value="${data.order.tracking_number || ''}" readonly>
                                        </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="order-section">
                                    <h4><i class="fa-solid fa-truck"></i> Shipping Information</h4>
                                    <div style="margin-bottom: 15px;">
                                        <label>Shipping Address *</label>
                                        <textarea name="shipping_address" class="form-control" rows="3" readonly>${data.order.shipping_address || ''}</textarea>
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <label>Shipping Method</label>
                                        <input type="text" name="shipping_method" class="form-control" value="${data.order.shipping_method || ''}"readonly>
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <label>Shipping Cost</label>
                                        <input type="number" step="0.01" name="shipping_cost" class="form-control" value="${data.order.shipping_cost || '0'}"readonly>
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control" rows="2">${data.order.notes || ''}</textarea>
                                    </div>
                                </div>

                                <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: flex-end;">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-save"></i> Update Order
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="deleteOrder(${orderId})">
                                        <i class="fa-solid fa-trash"></i> Delete Order
                                    </button>
                                    <button type="button" class="btn" onclick="closeEditModal()">
                                        <i class="fa-solid fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        `;
                        document.getElementById('editOrderModal').classList.add('active');
                    } else {
                        alert('Error loading order for edit: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading order for edit. Please check console for details.');
                });
        }

        // Update Order
        function updateOrder(e, orderId) {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('id', orderId);

            fetch('update_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order updated successfully!');
                    closeEditModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating order');
            });
        }

        // Delete Order
        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                fetch(`delete_order.php?id=${orderId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting order');
                });
            }
        }

        // Accept Order (move from pending to accepted)
        function acceptOrder(id) {
            if (!confirm("Accept this order and mark as PROCESSING?")) return;

            fetch('accept_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Remove row from table
                    const row = document.querySelector(`tr[data-order-id='${id}']`);
                    if (row) row.remove();
                    
                    // Update pending orders badge
                    updatePendingBadge();
                    
                    // Show success message
                    alert("Order accepted successfully. It has been moved to Accepted Orders tab.");
                } else {
                    alert(data.message || "Failed to accept order");
                }
            })
            .catch(err => {
                console.error(err);
                alert("System error while accepting order");
            });
        }

        // Mark order as shipped
        function markAsShipped(id) {
            if (!confirm("Mark this order as SHIPPED?")) return;

            fetch('update_order_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    id: id, 
                    status: 'shipped',
                    action: 'ship'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Order marked as shipped successfully!");
                    location.reload();
                } else {
                    alert(data.message || "Failed to update order");
                }
            })
            .catch(err => {
                console.error(err);
                alert("System error while updating order");
            });
        }

        // Mark order as delivered (move to history)
        function markAsDelivered(id) {
            if (!confirm("Mark this order as DELIVERED and move to Order History?")) return;

            fetch('update_order_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    id: id, 
                    status: 'delivered',
                    action: 'deliver'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Order marked as delivered successfully! It has been moved to Order History.");
                    location.reload();
                } else {
                    alert(data.message || "Failed to update order");
                }
            })
            .catch(err => {
                console.error(err);
                alert("System error while updating order");
            });
        }

        // Print invoice
        function printInvoice(id) {
            window.open(`print_invoice.php?id=${id}`, '_blank');
        }

        // Update pending badge count
        function updatePendingBadge() {
            // Update sidebar badge
            const badge = document.querySelector('#ordersLink .orderbadge');
            if (badge) {
                let count = parseInt(badge.textContent) || 0;
                count = Math.max(0, count - 1);
                if (count === 0) {
                    badge.remove();
                } else {
                    badge.textContent = count;
                }
            }
            
            // Update tab badge
            const tabBadge = document.querySelector('.order-tab[onclick*="pending"] .tab-badge');
            if (tabBadge) {
                let count = parseInt(tabBadge.textContent) || 0;
                count = Math.max(0, count - 1);
                if (count === 0) {
                    tabBadge.remove();
                } else {
                    tabBadge.textContent = count;
                }
            }
            
            // Update stats
            const pendingStat = document.querySelector('.stat-card.pending .number');
            if (pendingStat) {
                let count = parseInt(pendingStat.textContent.replace(/,/g, '')) || 0;
                count = Math.max(0, count - 1);
                pendingStat.textContent = count.toLocaleString();
            }
            
            const processingStat = document.querySelector('.stat-card.processing .number');
            if (processingStat) {
                let count = parseInt(processingStat.textContent.replace(/,/g, '')) || 0;
                count += 1;
                processingStat.textContent = count.toLocaleString();
            }
        }

        // Apply Filters
        function applyFilters() {
            const payment = document.getElementById('paymentFilter').value;
            
            // In a real implementation, you would fetch filtered data from server
            // For now, just show a message
            alert(`Filter applied: Payment=${payment}. This would filter the pending orders.`);
        }
        function exportOrder(orderId) {
    window.open(`export_order.php?id=${orderId}`, '_blank');
}

        // Export Orders
        function exportOrders(tab) {
            window.open(`export_orders.php?tab=${tab}`, '_blank');
        }

        // Close Modals
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        function closeEditModal() {
            document.getElementById('editOrderModal').classList.remove('active');
        }

        // Initialize Chart
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($status_distribution && $status_distribution->num_rows > 0): ?>
                const statusData = [];
                const statusLabels = [];
                const statusColors = [];
                
                <?php while ($status = $status_distribution->fetch_assoc()): ?>
                    statusLabels.push('<?php echo ucfirst($status['order_status']); ?>');
                    statusData.push(<?php echo $status['count']; ?>);
                    
                    <?php
                    $colorMap = [
                        'pending' => '#ffc107',
                        'processing' => '#ff6b00',
                        'shipped' => '#007bff',
                        'delivered' => '#28a745',
                        'cancelled' => '#dc3545'
                    ];
                    ?>
                    statusColors.push('<?php echo $colorMap[$status['order_status']] ?? '#6c757d'; ?>');
                <?php endwhile; ?>
                
                const ctx = document.getElementById('orderStatusChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusData,
                            backgroundColor: statusColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>