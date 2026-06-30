<?php
require 'dbconn.php';
require 'auth.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in
    echo "Access Denied. Please log in.";
    exit; // Stop executing the page
}
$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_id = intval($_SESSION['user_id'] ?? 0);

$user_name = $_SESSION['user_name'];
$user_id = intval($_SESSION['user_id']);

// ✅ Fetch messages (renamed variable to avoid overwrite)
$sql_messages = "SELECT * FROM messages ORDER BY date_sent DESC";
$result_messages = $conn->query($sql_messages);

// ✅ Count unread messages
$sql_unread = "SELECT COUNT(*) AS unread_count FROM messages WHERE is_read = 0";
$result_unread = $conn->query($sql_unread);
$row_unread = $result_unread->fetch_assoc();
$unread_count = $row_unread['unread_count'];

// ✅ Load site settings from database (use different variable)
$result_settings = $conn->query("SELECT site_title FROM site_settings WHERE id = 1");
if ($result_settings && $result_settings->num_rows > 0) {
    $settings = $result_settings->fetch_assoc();
}

// ✅ Fetch user details from database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();
$stmt->close();

// ✅ Get user avatar if exists
$avatar_path = "assets/images/avatars/{$user_id}.png";
$default_avatar = "assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);

$first_letter = strtoupper(substr($user_name, 0, 1));

// Count pending orders
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
    <title>Messages | St4nger Admin</title>
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            background: linear-gradient(180deg, var(--dark) 0%, #2d3748 100%);
            color: var(--white);
            display: flex;
            flex-direction: column;
            padding-top: 25px;
            z-index: 1000;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 17px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        /* Wrapper for fixed logo size */
.sidebar-logo-container {
    width: 50px;
    height: 50px;
    flex-shrink: 0; /* prevents shrinking */
    display: flex;
    align-items: center;
    justify-content: center;
}
        .sidebar-logo {
            width: 50px;       /* bigger visual size */
    height: 50px;
    border-radius: 12px;
    object-fit: cover;
    transform: scale(1.3); /* visually enlarge */
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

        /* Table Container */
        .table-container {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .table-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-gray);
        }

        .table-header h3 {
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-header h3 i {
            color: var(--primary);
        }

        .table-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            background: var(--light);
            border: 2px solid transparent;
            padding: 10px 18px;
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
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 16px 14px;
            border-bottom: 1px solid var(--light-gray);
        }

        th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
            position: sticky;
            top: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr {
            transition: var(--transition);
        }

        tr:hover {
            background: #f8fbff;
            transform: translateX(5px);
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .no-data i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #e9ecef;
        }

        .no-data h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--gray);
        }

        .email-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .email-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            font-family: 'Montserrat', sans-serif;
        }

        .reply-btn {
            background: rgba(7, 90, 174, 0.1);
            color: var(--primary);
            border: 1px solid rgba(7, 90, 174, 0.2);
        }

        .delete-btn {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .view-convo-btn {
            background: rgba(2, 202, 2, 0.1);
            color: var(--secondary);
            border: 1px solid rgba(2, 202, 2, 0.2);
        }

        .reply-btn:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .delete-btn:hover {
            background: var(--danger);
            color: var(--white);
            transform: translateY(-2px);
        }

        .view-convo-btn:hover {
            background: var(--secondary);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* Notification Dot */
        .notif-dot {
            background-color: var(--danger);
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 12px;
            margin-left: 5px;
        }

        /* Message Status */
        .message-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-unread {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }

        .status-read {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        /* Modals */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 20px;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-height: 90vh;
            overflow-y: auto;
            border-top: 4px solid var(--primary);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            margin-left: 20px;
            margin-top: 10px;
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
            margin-right: 18px;
            margin-top: 10px;
        }

        .close-btn:hover {
            color: var(--danger);
            background: rgba(220, 53, 69, 0.1);
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(7, 90, 174, 0.1);
        }

        .form-control[readonly] {
            background: var(--light);
            color: var(--gray);
        }

        .submit-btn {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            width: 100%;
            transition: var(--transition);
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Chat Modal */
        .chat-modal {
            width: 500px;
            max-height: 600px;
            display: flex;
            flex-direction: column;
            padding: 0;
            overflow: hidden;
        }

        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            background: var(--light);
            max-height: 400px;
        }

        .message-bubble {
            padding: 14px 18px;
            border-radius: 18px;
            margin-bottom: 15px;
            max-width: 80%;
            position: relative;
            word-wrap: break-word;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .message-bubble:hover {
            transform: translateY(-2px);
        }

        .message-bubble.user {
            background: #e2eaff;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }

        .message-bubble.admin {
            background: #d1f5d3;
            align-self: flex-end;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }

        .message-bubble small {
            display: block;
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .chat-input {
            display: flex;
            border-top: 1px solid var(--light-gray);
            background: var(--white);
            padding: 20px;
        }

        .chat-input textarea {
            flex: 1;
            resize: none;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            padding: 12px;
            height: 50px;
            font-family: 'Montserrat', sans-serif;
            transition: var(--transition);
        }

        .chat-input textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(7, 90, 174, 0.1);
        }

        .chat-input button {
            margin-left: 12px;
            background: var(--primary);
            border: none;
            color: var(--white);
            border-radius: 8px;
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-input button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
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
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .table-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .modal-content, .chat-modal {
                width: 95%;
                padding: 20px;
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
            
            .table-container {
                padding: 20px;
            }
            
            th, td {
                padding: 12px 8px;
                font-size: 13px;
            }
               .notif-badge {
                top: 8px;
                right: 8px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table-container {
            animation: fadeIn 0.6s ease-out;
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
            top: 208px;
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

        /* Ensure SweetAlert always appears above your modals */
.swal2-container {
    z-index: 20000 !important;
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
                <img src="../image/logo.png" alt="St4nger Logo" class="sidebar-logo">
                <span>ChronoVerse Messages</span>
            </h2>
        </div>
        
        <div class="sidebar-nav">
            <a href="home.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
            <a href="message.php" class="active" class="nav-link">
                <i class="fa-solid fa-envelope"></i>
                <span>Messages</span>
                <?php if ($unread_count > 0): ?>
                    <span class="notif-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="../product/product.php"><i class="fa-solid fa-briefcase"></i> <span>Product</span></a>
            <a href="../order/order.php" id="ordersLink">
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
            <h1>Message Center <i class="fa-solid fa-envelope-circle-check" style="color: var(--primary);"></i></h1>
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
    <a href="user.php" class="logout-btn">
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

        <div class="table-container">
            <div class="table-header">
                <h3><i class="fa-solid fa-inbox"></i> Client Messages</h3>
                <div class="table-actions">
                    <button class="btn" onclick="refreshMessages()">
                        <i class="fa-solid fa-rotate"></i> Refresh
                    </button>
                    <button class="btn btn-primary">
                        <i class="fa-solid fa-download"></i> Export
                    </button>
                </div>
            </div>
            
            <?php if ($result_messages->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sender</th>
                            <th>Email</th>
                            <th>Date Sent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        while ($row = $result_messages->fetch_assoc()):
                            $email = $row['email'];
                            $is_read = $row['is_read'] ?? 0;
                        ?>
                        <tr id="msg-<?php echo $row['id']; ?>" class="<?php echo $is_read ? 'read' : 'unread'; ?>">
                            <td><?php echo $count++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td>
                                <?php if (!empty($email)): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="email-link">
                                        <?php echo htmlspecialchars($email); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--gray);">No email provided</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date("M d, Y h:i A", strtotime($row['date_sent'])); ?></td>
                            <td>
                                <span class="message-status <?php echo $is_read ? 'status-read' : 'status-unread'; ?>">
                                    <?php echo $is_read ? 'Read' : 'Unread'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if (!empty($email)): ?>
                                        <button class="action-btn view-convo-btn" data-email="<?php echo htmlspecialchars($email); ?>">
                                            <i class="fa-solid fa-comments"></i> View
                                            <?php if (!$is_read): ?>
                                                <span class="notif-dot">New</span>
                                            <?php endif; ?>
                                        </button>
                                        <button class="action-btn reply-btn" 
                                            data-email="<?php echo htmlspecialchars($email); ?>" 
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                            <i class="fa-solid fa-reply"></i> Reply
                                        </button>
                                    <?php endif; ?>
                                    <button class="action-btn delete-btn" data-id="<?php echo $row['id']; ?>">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fa-regular fa-envelope-open"></i>
                    <h3>No Messages Yet</h3>
                    <p>When clients contact you, their messages will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reply Modal -->
    <div id="replyModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-reply"></i> Reply to Message</h3>
                <button class="close-btn" onclick="closeReplyModal()">&times;</button>
            </div>
            <form id="replyForm">
                <div class="form-group">
                    <label>To:</label>
                    <input type="email" id="replyEmail" name="email" class="form-control" readonly required>
                </div>
                <div class="form-group">
                    <label>Your Message:</label>
                    <textarea id="replyMessage" name="message" class="form-control" rows="6" required placeholder="Type your professional reply here..."></textarea>
                </div>
                <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-paper-plane"></i> Send Reply
                </button>
            </form>
        </div>
    </div>

    <!-- Conversation Modal -->
    <div id="conversationModal" class="modal" style="display:none;">
        <div class="modal-content chat-modal">
            <div class="modal-header">
                <h3><i class="fa-solid fa-comments"></i> Conversation</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div id="conversationContent" class="chat-box"></div>
            <form id="chatReplyForm" class="chat-input" autocomplete="off">
                <input type="hidden" name="email" id="chatEmail">
                <textarea id="chatMessage" name="message" placeholder="Type your reply..." required></textarea>
                <button type="submit">
                    <i class="fa-solid fa-paper-plane"></i> Send
                </button>
            </form>
        </div>
    </div>
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:400px; text-align:center;">
        <h3><i class="fa-solid fa-triangle-exclamation" style="color: var(--danger);"></i> Confirm Delete</h3>
        <p id="deleteMessageText" style="margin: 15px 0;">Are you sure you want to delete this message?</p>
        <div class="modal-actions" style="display:flex; justify-content:center; gap:10px; margin-top:15px;">
            <button id="cancelDelete" class="btn">Cancel</button>
            <button id="confirmDelete" class="btn btn-danger" style=" background-color: red; color: white;">Delete</button>
        </div>
    </div>
</div>
<script src="../js/sweetalert2.min.js"></script>

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

        // Refresh messages
        function refreshMessages() {
            location.reload();
        }

        // Reply Modal Functions
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('replyEmail').value = this.dataset.email;
                document.getElementById('replyModal').style.display = 'flex';
            });
        });

        function closeReplyModal() {
            document.getElementById('replyModal').style.display = 'none';
        }

        // Handle Reply Form
        document.getElementById('replyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your reply logic here
            alert('Reply functionality would be implemented here');
            closeReplyModal();
        });

     let deleteId = null; // store the message ID temporarily

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        deleteId = this.dataset.id;
        const messageName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();

        document.getElementById('deleteMessageText').textContent = 
            `Are you sure you want to delete the message from "${messageName}"? This action cannot be undone.`;

        document.getElementById('deleteModal').style.display = 'flex';
    });
});

document.getElementById('cancelDelete').addEventListener('click', () => {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
});

document.getElementById('confirmDelete').addEventListener('click', () => {
    if (!deleteId) return;

    fetch('delete_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(deleteId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('msg-' + deleteId);
            if (row) {
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
        document.getElementById('deleteModal').style.display = 'none';
    })
    .catch(err => {
        console.error('Delete error:', err);
        showNotification('Network error while deleting message', 'error');
        document.getElementById('deleteModal').style.display = 'none';
    });
});


// Notification function
function showNotification(message, type = 'info') {
    // Your existing notification code here
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    `;
    
    if (type === 'success') {
        notification.style.background = '#28a745';
    } else if (type === 'error') {
        notification.style.background = '#dc3545';
    } else {
        notification.style.background = '#075aae';
    }
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

      // CONVERSATION MODAL
// =============================
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('conversationModal');
    const modalBody = document.getElementById('conversationContent');
    const chatEmail = document.getElementById('chatEmail');
    const chatMessage = document.getElementById('chatMessage');
    const closeBtn = modal.querySelector('.close-btn');

    // Event delegation for View buttons
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.view-convo-btn');
        if (!btn) return;

        const email = btn.dataset.email;
        if (!email) {
            console.error('No email found for this conversation.');
            return;
        }

        // Show loading state
        modalBody.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--gray);"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading conversation...</p></div>';
        chatEmail.value = email;

        // Fetch conversation HTML
        fetch('get_conversation.php?email=' + encodeURIComponent(email))
            .then(res => {
                if (!res.ok) throw new Error('Failed to fetch conversation: ' + res.status);
                return res.text();
            })
            .then(html => {
                modalBody.innerHTML = html;
                // Open modal
                modal.style.display = 'flex';
                // Remove notification dot visually
                const notif = btn.querySelector('.notif-dot');
                if (notif) notif.remove();
                
                // Mark messages as read server-side
                fetch('mark_as_read.php?email=' + encodeURIComponent(email))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update status in the table
                            const statusCell = row.querySelector('.message-status');
                            if (statusCell) {
                                statusCell.className = 'message-status status-read';
                                statusCell.textContent = 'Read';
                            }
                        }
                    })
                    .catch(err => {
                        console.error('mark_as_read error:', err);
                    });
                
                // Focus reply box and scroll to bottom
                if (chatMessage) {
                    chatMessage.focus();
                    modalBody.scrollTop = modalBody.scrollHeight;
                }

               // Add event listeners for delete buttons in the conversation
modalBody.querySelectorAll('.delete-msg-btn').forEach(deleteBtn => {
    deleteBtn.addEventListener('click', function() {
        const messageId = this.dataset.id;
        const messageType = this.dataset.type;

        // 🧩 SweetAlert2 confirmation (replaces confirm)
        Swal.fire({
            title: 'Delete this reply?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with deletion
                deleteMessage(messageId, messageType, email);

                // Optional: show quick feedback while deleting
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Deleting reply...',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: true
                });
            }
        });
    });
});

            })
            .catch(err => {
                console.error(err);
                modalBody.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--danger);"><i class="fa-solid fa-exclamation-triangle"></i><p>Error loading conversation.</p></div>';
            });
    });

    // Function to delete individual messages
    function deleteMessage(messageId, messageType, email) {
        fetch('delete_conversation_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${encodeURIComponent(messageId)}&type=${encodeURIComponent(messageType)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload the conversation
                fetch('get_conversation.php?email=' + encodeURIComponent(email))
                    .then(res => res.text())
                    .then(html => {
                        modalBody.innerHTML = html;
                        modalBody.scrollTop = modalBody.scrollHeight;
                        
                        // Re-add event listeners for new delete buttons
                        modalBody.querySelectorAll('.delete-msg-btn').forEach(deleteBtn => {
                            deleteBtn.addEventListener('click', function() {
                                const messageId = this.dataset.id;
                                const messageType = this.dataset.type;
                                
                                if (confirm('Are you sure you want to delete this message?')) {
                                    deleteMessage(messageId, messageType, email);
                                }
                            });
                        });
                    });
            } else {
                alert('Failed to delete message: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            alert('Network error while deleting message');
        });
    }

    // Close modal by close button
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        // Refresh the page to update message status
        setTimeout(() => location.reload(), 300);
    });

    // Close modal by clicking outside content
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
            // Refresh the page to update message status
            setTimeout(() => location.reload(), 300);
        }
    });

    // Handle reply form submit
    const replyForm = document.getElementById('chatReplyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = chatMessage.value.trim();
            if (!message) {
                alert('Please type a message before sending.');
                chatMessage.focus();
                return;
            }
            
            // Show sending state
            const submitBtn = replyForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            
            // Send reply using your PHPMailer setup
            const formData = new FormData(replyForm);
            
            fetch('send_conversation_reply.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    chatMessage.value = '';
                    
                    // Show success message
                    showNotification('Reply sent successfully!', 'success');
                    
                    // Reload conversation to show the new reply
                    const email = chatEmail.value;
                    fetch('get_conversation.php?email=' + encodeURIComponent(email))
                        .then(r => r.text())
                        .then(html => {
                            modalBody.innerHTML = html;
                            modalBody.scrollTop = modalBody.scrollHeight;
                            
                            // Re-add event listeners for delete buttons
                            modalBody.querySelectorAll('.delete-msg-btn').forEach(deleteBtn => {
                                deleteBtn.addEventListener('click', function() {
                                    const messageId = this.dataset.id;
                                    const messageType = this.dataset.type;
                                    
                                    if (confirm('Are you sure you want to delete this message?')) {
                                        deleteMessage(messageId, messageType, email);
                                    }
                                });
                            });
                        });
                } else {
                    showNotification(data.message || 'Failed to send reply', 'error');
                }
            })
            .catch(err => {
                console.error('Reply send error:', err);
                showNotification('Network error while sending reply', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // Close modals with escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modal.style.display = 'none';
            // Refresh the page to update message status
            setTimeout(() => location.reload(), 300);
        }
    });

    // Notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
        `;
        
        if (type === 'success') {
            notification.style.background = 'var(--success)';
        } else if (type === 'error') {
            notification.style.background = 'var(--danger)';
        } else {
            notification.style.background = 'var(--primary)';
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Add CSS for notifications
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
});    

</script>
</body>
</html>