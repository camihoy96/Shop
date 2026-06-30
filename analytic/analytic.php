<?php
require 'dbconn.php';
require '../admin/auth.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in
    echo "Access Denied. Please log in.";
    exit; // Stop executing the page
}
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_id = intval($_SESSION['user_id'] ?? 0);

// Default stats
$stats = [
    'total_orders' => 0,
    'total_revenue' => 0,
    'avg_order_value' => 0,
    'top_product' => 'None',
    'customer_count' => 0,
    'best_selling_category' => 'None',
    'pending_orders' => 0,
    'processing_orders' => 0,
    'shipped_orders' => 0,
    'delivered_orders' => 0,
];

// ====== E-COMMERCE STATISTICS ======
try {
    // Total orders (all statuses) and revenue (exclude cancelled if exists)
    $orders_sql = "SELECT 
        COUNT(*) AS total_orders,
        IFNULL(SUM(total_amount),0) AS total_revenue,
        IFNULL(AVG(total_amount),0) AS avg_order_value
        FROM orders";
    $order_stats = $conn->query($orders_sql)->fetch_assoc();
    $stats['total_orders'] = $order_stats['total_orders'] ?? 0;
    $stats['total_revenue'] = round($order_stats['total_revenue'], 2);
    $stats['avg_order_value'] = round($order_stats['avg_order_value'], 2);

    // Pending / processing / shipped / delivered counts
    $status_counts = $conn->query(
        "SELECT order_status, COUNT(*) AS cnt 
         FROM orders 
         GROUP BY order_status"
    );
    while ($row = $status_counts->fetch_assoc()) {
        $status = $row['order_status'];
        $count = intval($row['cnt']);
        switch ($status) {
            case 'pending': $stats['pending_orders'] = $count; break;
            case 'processing': $stats['processing_orders'] = $count; break;
            case 'shipped': $stats['shipped_orders'] = $count; break;
            case 'delivered': $stats['delivered_orders'] = $count; break;
        }
    }

    // Unique customers
    $stats['customer_count'] = $conn->query(
        "SELECT COUNT(DISTINCT customer_email) AS cnt FROM orders"
    )->fetch_assoc()['cnt'] ?? 0;

    // Top product
    $top_product_row = $conn->query(
        "SELECT product_name, COUNT(*) AS sales_count 
         FROM order_items 
         GROUP BY product_name 
         ORDER BY sales_count DESC 
         LIMIT 1"
    )->fetch_assoc();
    if ($top_product_row) {
        $stats['top_product'] = $top_product_row['product_name'] . ' (' . $top_product_row['sales_count'] . ' sales)';
    }

    // Best selling category (if you have products.category linked)
    $best_category_row = $conn->query(
        "SELECT p.category, COUNT(*) AS sales_count 
         FROM order_items oi
         JOIN products p ON oi.product_id = p.id
         GROUP BY p.category 
         ORDER BY sales_count DESC
         LIMIT 1"
    )->fetch_assoc();
    if ($best_category_row) {
        $stats['best_selling_category'] = ucfirst(str_replace('-', ' ', $best_category_row['category'])) 
                                         . ' (' . $best_category_row['sales_count'] . ' sales)';
    }

} catch (mysqli_sql_exception $e) {
    $stats['error'] = $e->getMessage();
}

// ====== USER INFO ======
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// User avatar
$avatar_path = "../admin/assets/images/avatars/{$user_id}.png";
$default_avatar = "assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);

// Unread messages
$unread_count_row = $conn->query("SELECT COUNT(*) AS unread_count FROM messages WHERE is_read = 0")->fetch_assoc();
$unread_count = $unread_count_row['unread_count'] ?? 0;

// Count pending orders
$orderCountSql = "SELECT COUNT(*) AS pending_count FROM orders WHERE order_status = 'pending' AND is_read = 0";
$result = $conn->query($orderCountSql);
$pendingOrders = 0;
if ($result) {
    $row = $result->fetch_assoc();
    $pendingOrders = $row['pending_count'] ?? 0;
}

/* ========== 1. SALES TREND ========== */
$sales_trend_sql = "
SELECT 
    DATE(created_at) as day,
    SUM(total_amount) as revenue,
    COUNT(id) as orders
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
GROUP BY DATE(created_at)
ORDER BY day ASC
";

$sales_trend = $conn->query($sales_trend_sql)->fetch_all(MYSQLI_ASSOC);


/* ========== 2. TOP SELLING PRODUCTS ========== */
$top_products_sql = "
SELECT 
    oi.product_name,
    SUM(oi.quantity) AS sales,
    SUM(oi.total_price) AS revenue,
    AVG(oi.product_price) AS avg_price
FROM order_items oi
GROUP BY oi.product_id, oi.product_name
ORDER BY sales DESC
LIMIT 10
";

$top_products = $conn->query($top_products_sql)->fetch_all(MYSQLI_ASSOC);


/* ========== 3. ORDER STATUS SUMMARY ========== */
$order_status_sql = "
SELECT 
    order_status,
    COUNT(*) as count
FROM orders
GROUP BY order_status
";

$order_statuses = $conn->query($order_status_sql)->fetch_all(MYSQLI_ASSOC);


/* ========== 4. PAYMENT METHODS ========== */
$payment_sql = "
SELECT 
    payment_method,
    COUNT(*) as orders,
    SUM(total_amount) as revenue
FROM orders
GROUP BY payment_method
";

$payment_methods = $conn->query($payment_sql)->fetch_all(MYSQLI_ASSOC);


/* ========== 5. GEOGRAPHIC (simple from address) ========== */
$geo_sql = "
SELECT 
    SUBSTRING_INDEX(shipping_address, ',', -1) as country,
    COUNT(*) as orders
FROM orders
GROUP BY country
ORDER BY orders DESC
LIMIT 10
";

$geo_data = $conn->query($geo_sql)->fetch_all(MYSQLI_ASSOC);

$sql = "
SELECT 
    DATE(created_at) AS sale_date,
    SUM(total_amount) AS revenue,
    COUNT(id) AS orders
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY sale_date ASC
";

$sales_data = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Analytics | ChronoVerse Admin</title>
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="../js/chart.umd.min.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --dark: #0a192f;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #4cc9f0;
            --warning: #f72585;
            --danger: #e63946;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --revenue: #28a745;
            --customers: #17a2b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
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
            font-size: 16px;
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
            transform: scale(1.4);
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
            gap: 12px;
            padding: 14px 25px;
            transition: var(--transition);
            font-weight: 500;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            padding-left: 28px;
        }

        .sidebar-nav a.active {
            background: rgba(67, 97, 238, 0.15);
            color: white;
            border-left: 10px solid rgba(115, 255, 0, 1);
        }

        .sidebar-nav a i {
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        /* Main content */
        .main {
            margin-left: 260px;
            padding: 25px;
            transition: var(--transition);
        }

        .header {
            background: #fff;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--card-shadow);
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 24px;
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
            padding: 8px 16px;
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
            box-shadow: var(--card-shadow);
            margin-top: 10px;
            min-width: 180px;
            z-index: 1000;
            overflow: hidden;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            color: #e74c3c;
            font-weight: 300;
            text-decoration: none;
            transition: all 0.3s ease;
            background-color: #fff;
            border-top: 1px solid #eee;
        }

        .logout-btn i {
            font-size: 16px;
        }

        .logout-btn:hover {
            background-color: #e74c3c;
            color: #fff;
            transform: translateX(3px);
        }

        .logout-btn:hover i {
            color: #fff;
        }

        /* E-commerce Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            text-align: center;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card.orders {
            border-top-color: var(--primary);
        }

        .stat-card.revenue {
            border-top-color: var(--revenue);
        }

        .stat-card.customers {
            border-top-color: var(--customers);
        }

        .stat-card.avg-order {
            border-top-color: var(--secondary);
        }

        .stat-card.top-product {
            border-top-color: var(--success);
        }

        .stat-card.category {
            border-top-color: var(--warning);
        }

        .stat-card.abandoned {
            border-top-color: var(--danger);
        }

        .stat-card i {
            font-size: 28px;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 10px;
        }

        .stat-card.orders i {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .stat-card.revenue i {
            background: rgba(40, 167, 69, 0.1);
            color: var(--revenue);
        }

        .stat-card.customers i {
            background: rgba(23, 162, 184, 0.1);
            color: var(--customers);
        }

        .stat-card.avg-order i {
            background: rgba(114, 9, 183, 0.1);
            color: var(--secondary);
        }

        .stat-card.top-product i {
            background: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }

        .stat-card.category i {
            background: rgba(247, 37, 133, 0.1);
            color: var(--warning);
        }

        .stat-card.abandoned i {
            background: rgba(230, 57, 70, 0.1);
            color: var(--danger);
        }

        .stat-card h4 {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-card .trend {
            font-size: 12px;
            margin-top: 5px;
        }

        .trend.up {
            color: var(--success);
        }

        .trend.down {
            color: var(--danger);
        }

        /* Analytics Content */
        .analytics-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        @media (max-width: 1200px) {
            .analytics-content {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin-bottom: 25px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h3 i {
            color: var(--primary);
        }

        .time-filter {
            display: flex;
            gap: 10px;
        }

        .time-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: var(--transition);
        }

        .time-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .time-btn:hover {
            border-color: var(--primary);
        }

        /* Chart Container */
        .chart-container {
            height: 300px;
            position: relative;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
        }

        .data-table tr:hover {
            background: #f8fbff;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 3px;
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .status-processing {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }

        .status-shipped {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .status-delivered {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .status-cancelled {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        /* Database Setup */
        .database-setup {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            border-left: 4px solid var(--warning);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .sidebar-header h2 span,
            .sidebar-nav a span {
                display: none;
            }
            
            .sidebar-nav a {
                justify-content: center;
                padding: 16px;
            }
            
            .sidebar-nav a i {
                font-size: 20px;
            }
            
            .main {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .time-filter {
                width: 100%;
                justify-content: space-between;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 0;
            }
            
            .main {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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

    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <img src="../image/logo.png" alt="Logo" class="sidebar-logo">
                <span>ChronoVerse Analytics</span>
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
            <a href="../product/product.php"><i class="fa-solid fa-briefcase"></i> <span>Products</span></a>
            <a href="../order/order.php" id="ordersLink"> 
    <i class="fa-solid fa-box"></i> 
    <span>Orders</span>
    <?php if($pendingOrders > 0): ?>
        <span class="orderbadge"><?php echo $pendingOrders; ?></span> 
    <?php endif; ?>
</a>


            <a href="../analytic/analytic.php" class="active"><i class="fa-solid fa-chart-bar"></i> <span>Analytics</span></a>
            <a href="../Sales/Sales.php">
    <i class="fa-solid fa-chart-line"></i>
    <span>Sales Report</span>
</a>

        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>E-commerce Analytics Dashboard</h1>
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

    <!-- Total Orders -->
    <div class="stat-card orders">
        <i class="fa-solid fa-shopping-cart"></i>
        <h4>Total Orders</h4>
        <div class="number"><?php echo number_format($stats['total_orders']); ?></div>
        <div class="trend up">
            <i class="fa-solid fa-arrow-up"></i>
            <?php echo number_format($stats['total_orders']); ?> orders
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="stat-card revenue">
        <i class="fa-solid fa-money-bill-wave"></i>
        <h4>Total Revenue</h4>
        <div class="number">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
        <div class="trend up">
            <i class="fa-solid fa-arrow-up"></i>
            Last 30 days
        </div>
    </div>

    <!-- Unique Customers -->
    <div class="stat-card customers">
        <i class="fa-solid fa-users"></i>
        <h4>Unique Customers</h4>
        <div class="number"><?php echo number_format($stats['customer_count']); ?></div>
        <div style="font-size: 12px; color: var(--gray); margin-top: 5px;">Last 30 days</div>
    </div>

    <!-- Average Order Value -->
    <div class="stat-card avg-order">
        <i class="fa-solid fa-chart-line"></i>
        <h4>Avg Order Value</h4>
        <div class="number">$<?php echo number_format($stats['avg_order_value'], 2); ?></div>
    </div>

    <!-- Top Product -->
    <div class="stat-card top-product">
        <i class="fa-solid fa-crown"></i>
        <h4>Top Product</h4>
        <div class="number" style="font-size: 16px; line-height: 1.4;"><?php echo htmlspecialchars($stats['top_product']); ?></div>
    </div>

    <!-- Top Category -->
    <div class="stat-card category">
        <i class="fa-solid fa-tag"></i>
        <h4>Top Category</h4>
        <div class="number" style="font-size: 16px; line-height: 1.4;"><?php echo htmlspecialchars($stats['best_selling_category']); ?></div>
    </div>

    <!-- Abandoned Carts -->
    <div class="stat-card abandoned">
        <i class="fa-solid fa-shopping-basket"></i>
        <h4>Abandoned Carts</h4>
        <div class="number"><?php echo number_format($stats['abandoned_carts'] ?? 0); ?></div>
        <div style="font-size: 12px; color: var(--gray); margin-top: 5px;">Last 7 days</div>
    </div>

    <!-- Pending Orders -->
    <div class="stat-card pending">
        <i class="fa-solid fa-clock"></i>
        <h4>Pending Orders</h4>
        <div class="number"><?php echo number_format($stats['pending_orders']); ?></div>
        <div style="font-size: 12px; color: var(--gray); margin-top: 5px;">Waiting Processing</div>
    </div>

    <!-- Shipped Orders -->
    <div class="stat-card shipped">
        <i class="fa-solid fa-truck"></i>
        <h4>Shipped Orders</h4>
        <div class="number"><?php echo number_format($stats['shipped_orders']); ?></div>
        <div style="font-size: 12px; color: var(--gray); margin-top: 5px;">On the way</div>
    </div>

    <!-- Delivered Orders -->
    <div class="stat-card delivered">
        <i class="fa-solid fa-check-circle"></i>
        <h4>Delivered Orders</h4>
        <div class="number"><?php echo number_format($stats['delivered_orders']); ?></div>
        <div style="font-size: 12px; color: var(--gray); margin-top: 5px;">Completed</div>
    </div>

</div>
<div class="analytics-content">

<!-- LEFT COLUMN -->
<div>

<!-- Sales Trend -->
<div class="card">
<div class="card-header">
<h3><i class="fa-solid fa-chart-line"></i> Sales Performance</h3>
<div class="time-filter">
<button class="time-btn active" onclick="updateChart('7d')">7D</button>
<button class="time-btn" onclick="updateChart('30d')">30D</button>
<button class="time-btn" onclick="updateChart('90d')">90D</button>
</div>
</div>

<div class="chart-container">
<canvas id="salesChart"></canvas>
</div>
</div>


<!-- TOP PRODUCTS -->
<div class="card">
<div class="card-header">
<h3><i class="fa-solid fa-star"></i> Top Selling Products</h3>
</div>

<table class="data-table">
<thead>
<tr>
<th>Product</th>
<th>Sales</th>
<th>Revenue</th>
<th>Avg. Price</th>
</tr>
</thead>

<tbody>
<?php if (!empty($top_products)): ?>
<?php foreach ($top_products as $product): ?>
<tr>
<td><?php echo htmlspecialchars($product['product_name'] ?? 'Unknown'); ?></td>
<td><?php echo number_format($product['sales'] ?? 0); ?></td>
<td>$<?php echo number_format($product['revenue'] ?? 0, 2); ?></td>
<td>$<?php echo number_format($product['avg_price'] ?? 0, 2); ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr>
<td colspan="4" style="text-align:center;">No sales data available</td>
</tr>
<?php endif; ?>
</tbody>
</table>

</div>
</div>


<!-- RIGHT COLUMN -->
<div>

<!-- ORDER STATUS -->
<div class="card">
<div class="card-header">
<h3><i class="fa-solid fa-chart-pie"></i> Order Status</h3>
</div>

<div class="chart-container">
<canvas id="statusChart"></canvas>
</div>

<div style="margin-top:15px;">
<?php if (!empty($order_statuses)): ?>
<?php foreach ($order_statuses as $status): ?>
<div style="display:flex;justify-content:space-between;margin-bottom:8px;">

<span class="status-badge status-<?php echo strtolower($status['order_status']); ?>">
<?php echo ucfirst($status['order_status']); ?>
</span>

<span><?php echo $status['count'] ?? 0; ?> orders</span>

</div>
<?php endforeach; ?>
<?php else: ?>
<p style="text-align:center;">No order data available</p>
<?php endif; ?>
</div>
</div>


<!-- PAYMENT METHODS -->
<div class="card">
<div class="card-header">
<h3><i class="fa-solid fa-credit-card"></i> Payment Methods</h3>
</div>

<?php if (!empty($payment_methods)): ?>
<?php foreach ($payment_methods as $payment): ?>

<div style="display:flex;justify-content:space-between;padding:10px 0;">

<div>
<div><?php echo ucfirst($payment['payment_method'] ?? 'N/A'); ?></div>
<div style="font-size:12px;">
<?php echo $payment['orders'] ?? 0; ?> orders
</div>
</div>

<div>
$<?php echo number_format($payment['revenue'] ?? 0, 2); ?>
</div>

</div>

<?php endforeach; ?>
<?php else: ?>
<p>No payment data available</p>
<?php endif; ?>

</div>


<!-- GEO -->
<div class="card">
<div class="card-header">
<h3><i class="fa-solid fa-globe"></i> Geographic Distribution</h3>
</div>

<div class="chart-container">
<canvas id="geoChart"></canvas>
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

document.addEventListener('DOMContentLoaded', function() {

    // ===== SALES CHART =====
    const salesCtx = document.getElementById('salesChart').getContext('2d');

    window.salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($sales_data, 'sale_date')); ?>,
            datasets: [
                {
                    label: 'Revenue ($)',
                    data: <?php echo json_encode(array_column($sales_data, 'revenue')); ?>,
                    borderColor: '#4361ee',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders',
                    data: <?php echo json_encode(array_column($sales_data, 'orders')); ?>,
                    borderColor: '#4cc9f0',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    position: 'left',
                    title: { display: true, text: 'Revenue ($)' }
                },
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });


    // ===== ORDER STATUS =====
    const statusCtx = document.getElementById('statusChart').getContext('2d');

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($order_statuses, 'order_status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($order_statuses, 'count')); ?>
            }]
        }
    });


    // ===== GEO =====
    const geoCtx = document.getElementById('geoChart').getContext('2d');

    new Chart(geoCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($geographic_data, 'country')); ?>,
            datasets: [{
                label: 'Orders',
                data: <?php echo json_encode(array_column($geographic_data, 'orders')); ?>
            }]
        }
    });


    // ===== TIME FILTER =====
    document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', function() {

            document.querySelectorAll('.time-btn')
                .forEach(b => b.classList.remove('active'));

            this.classList.add('active');

            updateChartData(this.textContent.toLowerCase());
        });
    });

});


// ===== AJAX UPDATE =====
function updateChartData(period) {

    fetch('get_sales_data.php?period=' + period)
        .then(res => res.json())
        .then(data => {

            window.salesChart.data.labels = data.dates;

            window.salesChart.data.datasets[0].data = data.revenue;

            window.salesChart.data.datasets[1].data = data.orders;

            window.salesChart.update();
        });
}
document.addEventListener('DOMContentLoaded', function() {

const ctx = document.getElementById('salesChart').getContext('2d');

window.salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($sales_data, 'sale_date')); ?>,
        datasets: [
            {
                label: 'Revenue',
                data: <?php echo json_encode(array_column($sales_data, 'revenue')); ?>,
                borderColor: '#4361ee',
                tension: 0.4,
                fill: false,
                yAxisID: 'y'
            },
            {
                label: 'Orders',
                data: <?php echo json_encode(array_column($sales_data, 'orders')); ?>,
                borderColor: '#4cc9f0',
                tension: 0.4,
                fill: false,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

});


// ===== THIS CONNECTS BUTTONS TO DATABASE =====
function updateChart(period) {

fetch('get_sales_data.php?period=' + period)
.then(res => res.json())
.then(data => {

    // Convert to numbers
    const revenue = data.revenue.map(v => parseFloat(v));
    const orders  = data.orders.map(v => parseInt(v));

    // If chart not created yet → create it
    if (!window.salesChart) {

        const ctx = document.getElementById('salesChart').getContext('2d');

        window.salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.dates,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenue,
                        borderColor: '#4361ee',
                        tension: 0.3
                    },
                    {
                        label: 'Orders',
                        data: orders,
                        borderColor: '#4cc9f0',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

    } else {

        // Update existing chart
        window.salesChart.data.labels = data.dates;
        window.salesChart.data.datasets[0].data = revenue;
        window.salesChart.data.datasets[1].data = orders;
        window.salesChart.update();

    }

});

}
function loadGeoChart() {

fetch('get_geo_data.php')
.then(res => res.json())
.then(data => {

    const countries = data.map(d => d.country);
    const orders    = data.map(d => d.orders);

    const ctx = document.getElementById('geoChart').getContext('2d');

    // Create Chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: countries,
            datasets: [{
                label: 'Orders',
                data: orders,
                backgroundColor: '#4361ee',
                borderColor: '#3a56d4',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

});

}

// Call when page loads
document.addEventListener("DOMContentLoaded", function() {
    loadGeoChart();
});

</script>

</body>
</html>