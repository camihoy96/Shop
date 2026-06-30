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

// Count pending orders
$orderCountSql = "SELECT COUNT(*) AS pending_count FROM orders WHERE order_status = 'pending' AND is_read = 0";
$result = $conn->query($orderCountSql);
$pendingOrders = 0;
if ($result) {
    $row = $result->fetch_assoc();
    $pendingOrders = $row['pending_count'] ?? 0;
}

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
$user_id = intval($_SESSION['user_id'])
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items | ChronoVerse Admin</title>
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
            width: 280px;
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
    width: 50px;   /* space reserved for logo */
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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

        .stat-card.published {
            border-top-color: var(--secondary);
        }

        .stat-card.featured {
            border-top-color: var(--accent);
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

        .stat-card.published i {
            background: rgba(2, 202, 2, 0.1);
            color: var(--secondary);
        }

        .stat-card.featured i {
            background: rgba(255, 107, 0, 0.1);
            color: var(--accent);
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

        /* Product Content */
        .product-content {
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

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #c5303a;
        }

        /* Product Grid - Fixed equal height cards */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.product-item {
    background: var(--white);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid var(--light-gray);
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 550px;
}

.product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.product-image {
    height: 200px;
    min-height: 200px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 48px;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.product-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--dark);
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 44px;
}

.product-description {
    font-size: 14px;
    color: var(--gray);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 63px;
}

.price-tag {
    font-size: 20px;
    font-weight: bold;
    color: var(--secondary);
    margin: 10px 0;
    padding: 8px 0;
    border-top: 1px solid var(--light-gray);
    border-bottom: 1px solid var(--light-gray);
}

.features-list {
    list-style: none;
    padding-left: 0;
    flex: 1;
    min-height: 80px;
    max-height: 100px;
    overflow: hidden;
}

.features-list li {
    margin-bottom: 5px;
    font-size: 13px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.more-features {
    color: var(--primary);
    font-size: 12px;
    font-weight: bold;
    margin-top: 5px;
    display: block;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    font-size: 13px;
    padding-top: 10px;
    border-top: 1px solid var(--light-gray);
}

.product-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.product-date {
    font-size: 12px;
    color: var(--gray);
    white-space: nowrap;
}

.product-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid var(--light-gray);
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
    flex: 1;
    justify-content: center;
}

.category-header {
    padding: 8px 15px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    font-size: 12px;
    font-weight: bold;
    flex-shrink: 0;
}

.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--warning);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    z-index: 2;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
    
    .product-item {
        min-height: 520px;
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
    
    .product-item {
        min-height: 500px;
    }
    
    .product-actions {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
    }
}
        .action-btn.edit {
            background: rgba(7, 90, 174, 0.1);
            color: var(--primary);
            border: 1px solid rgba(7, 90, 174, 0.2);
        }

        .action-btn.delete {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .action-btn.edit:hover {
            background: var(--primary);
            color: var(--white);
        }

        .action-btn.delete:hover {
            background: var(--danger);
            color: var(--white);
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

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .form-check input {
            width: auto;
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

        /* Service Categories */
        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            background: var(--light);
            color: var(--dark);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 8px;
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
            
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
            
            .product-actions {
                flex-direction: column;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .product-grid {
            animation: fadeIn 0.6s ease-out;
        }

        /* File Upload Preview Styles */
.file-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.file-preview-item {
    position: relative;
    border: 1px solid var(--light-gray);
    border-radius: 8px;
    padding: 8px;
    background: var(--light);
    display: flex;
    align-items: center;
    gap: 8px;
    max-width: 200px;
}

.file-preview-item img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}

.file-preview-info {
    flex: 1;
    min-width: 0;
}

.file-preview-name {
    font-size: 12px;
    font-weight: 500;
    color: var(--dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-preview-size {
    font-size: 10px;
    color: var(--gray);
}

.file-preview-remove {
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 10px;
}

.upload-progress {
    width: 100%;
    height: 4px;
    background: var(--light-gray);
    border-radius: 2px;
    margin-top: 5px;
    overflow: hidden;
}

.upload-progress-bar {
    height: 100%;
    background: var(--primary);
    transition: width 0.3s ease;
}

/* Confirmation Overlay */
.confirm-box-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* Confirmation Box */
.confirm-box {
    background: #fff;
    padding: 25px 30px;
    border-radius: 10px;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    animation: fadeIn 0.3s ease;
}

.confirm-box h3 {
    margin-bottom: 10px;
    color: #dc3545;
}

.confirm-box p {
    margin-bottom: 20px;
    font-size: 15px;
    color: #333;
}

.confirm-actions {
    display: flex;
    justify-content: space-evenly;
}

.confirm-actions button {
    border: none;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

#confirmYes {
    background-color: #dc3545;
    color: #fff;
}

#confirmYes:hover {
    background-color: #b02a37;
}

#confirmNo {
    background-color: #6c757d;
    color: #fff;
}

#confirmNo:hover {
    background-color: #5a6268;
}

/* Fade animation */
@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
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
/* Toast Notifications */
.toast {
    padding: 16px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    min-width: 300px;
    max-width: 400px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s;
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: 'Montserrat', sans-serif;
}

.toast-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    border-left: 4px solid #1e7e34;
}

.toast-error {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    border-left: 4px solid #a71d2a;
}

.toast-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    border-left: 4px solid #d39e00;
    color: #333;
}

.toast-info {
    background: linear-gradient(135deg, #17a2b8, #0dcaf0);
    border-left: 4px solid #117a8b;
}

.toast i {
    font-size: 20px;
}

.toast-content {
    flex: 1;
}

.toast-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 2px;
}

.toast-message {
    font-size: 13px;
    opacity: 0.9;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <div class="sidebar-logo-container">
                    <img src="../image/logo.png" alt="St4nger Logo" class="sidebar-logo">
                </div>
                <span>ChronoVerse Product</span>
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
            <a href="../product/product.php" class="active"><i class="fa-solid fa-briefcase"></i> <span>Product</span></a>
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
            <h1>Products Management <i class="fa-solid fa-briefcase" style="color: var(--primary);"></i></h1>
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
          <!-- Update the stats-grid section -->
<div class="stats-grid">
<?php
// Get counts for each category / overall stats
$stats_sql = "
    SELECT 
        COUNT(*) AS total_products,
        COALESCE(SUM(stock), 0) AS total_stock,
        (SELECT COUNT(*) FROM orders WHERE order_status = 'completed') AS total_orders
    FROM products
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>


    
    <div class="stat-card total">
        <i class="fa-solid fa-boxes-stacked"></i>
        <h4>Total Products</h4>
        <div class="number"><?php echo $stats['total_products'] ?? 0; ?></div>
    </div>
    
    <div class="stat-card published">
        <i class="fa-solid fa-cubes"></i>
        <h4>Total Stock</h4>
        <div class="number"><?php echo $stats['total_stock'] ?? 0; ?></div>
    </div>
    
    <div class="stat-card featured">
        <i class="fa-solid fa-shopping-cart"></i>
        <h4>Total Orders</h4>
        <div class="number"><?php echo $stats['total_orders'] ?? 0; ?></div>
    </div>
</div>   
        </div>

<?php
// First check if categories table exists
$table_check = $conn->query("SHOW TABLES LIKE 'product_categories'");
if ($table_check->num_rows > 0) {
    // Fetch from categories table
    $category_sql = "SELECT * FROM product_categories ORDER BY name";
} else {
    // Fallback: fetch from products table (distinct categories)
    $category_sql = "SELECT DISTINCT category FROM products WHERE name NOT LIKE 'New category product%' ORDER BY category";
}

$category_result = $conn->query($category_sql);
$categories = [];
while($row = $category_result->fetch_assoc()) {
    if (isset($row['slug'])) {
        // From categories table
        $categories[] = [
            'slug' => $row['slug'],
            'name' => $row['name']
        ];
    } else {
        // From products table
        $categories[] = [
            'slug' => $row['category'],
            'name' => ucfirst(str_replace('-', ' ', $row['category']))
        ];
    }
}
?>

<div class="product-content">
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-box"></i> Product List</h3>
            <div style="display: flex; gap: 10px; align-items: center;">
                <!-- Category Filter -->
                <div class="category-filter" style="display: flex; gap: 5px; flex-wrap: wrap;">
                    <button class="category-btn active" data-category="all">
                        <i class="fa-solid fa-layer-group"></i> All Products
                    </button>
                    <?php foreach($categories as $cat): ?>
                        <button class="category-btn" data-category="<?php echo htmlspecialchars($cat['slug']); ?>">
                            <i class="fa-solid fa-folder"></i> 
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Add Product Button -->
                <button class="btn" id="addProductBtn">
                    <i class="fa-solid fa-plus"></i> Add Product
                </button>
            </div>
        </div>

        <!-- Category Stats -->
        <div class="category-stats" style="margin-bottom: 20px; padding: 15px; background: var(--light-gray); border-radius: 8px;">
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <?php 
                // Get product count for each category
                foreach($categories as $cat):
                    // Check if we have a separate categories table
                    $count_sql = "SELECT COUNT(*) as count FROM products WHERE category = ? AND name NOT LIKE 'New category product%'";
                    $count_stmt = $conn->prepare($count_sql);
                    $count_stmt->bind_param("s", $cat['slug']);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $count = $count_result->fetch_assoc()['count'];
                    $count_stmt->close();
                ?>
                    <div class="category-stat" data-category="<?php echo htmlspecialchars($cat['slug']); ?>" 
                         style="padding: 10px 15px; background: white; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="font-size: 12px; color: var(--gray);"><?php echo htmlspecialchars($cat['name']); ?></div>
                        <div style="font-size: 20px; font-weight: bold; color: var(--primary);"><?php echo $count; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php 
        // Fetch all products with their featured images (excluding dummy category products)
        $product_sql = "SELECT p.*, pi.image_path 
                       FROM products p 
                       LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_featured = 1 
                       WHERE p.name NOT LIKE 'New category product%'
                       ORDER BY p.category, p.featured DESC, p.created_at DESC";
        $product_result = $conn->query($product_sql);
        
        if ($product_result->num_rows > 0): ?>
            <div class="product-grid" id="productGrid">
                <?php while ($product = $product_result->fetch_assoc()): ?>
                    <?php
                    // Skip if this is a dummy product (additional check)
                    if (strpos($product['name'], 'New category product') !== false) {
                        continue;
                    }
                    
                    // Get category name from categories table if available
                    $cat_name = ucfirst(str_replace('-', ' ', $product['category']));
                    $table_check2 = $conn->query("SHOW TABLES LIKE 'product_categories'");
                    if ($table_check2->num_rows > 0) {
                        $cat_sql = "SELECT name FROM product_categories WHERE slug = ?";
                        $cat_stmt = $conn->prepare($cat_sql);
                        $cat_stmt->bind_param("s", $product['category']);
                        $cat_stmt->execute();
                        $cat_result = $cat_stmt->get_result();
                        if ($cat_result->num_rows > 0) {
                            $cat_row = $cat_result->fetch_assoc();
                            $cat_name = $cat_row['name'];
                        }
                        $cat_stmt->close();
                    }
                    ?>
                    
                    <div class="product-item" data-category="<?php echo htmlspecialchars($product['category']); ?>">
                        <!-- Category Badge at top -->
                        <div class="category-header" style="padding: 8px 15px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; font-size: 12px; font-weight: bold;">
                            <i class="fa-solid fa-tag"></i> 
                            <?php echo htmlspecialchars($cat_name); ?>
                        </div>
                        
                        <div class="product-image">
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <i class="fa-solid fa-box-open"></i>
                            <?php endif; ?>
                            <?php if ($product['featured']): ?>
                                <span class="featured-badge" style="position: absolute; top: 10px; right: 10px; background: var(--warning); color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                                    <i class="fa-solid fa-star"></i> Featured
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                            
                            <div class="price-tag" style="font-size: 20px; font-weight: bold; color: var(--secondary); margin: 10px 0;">
                                $<?php echo number_format($product['price'], 2); ?>
                            </div>
                            
                            <!-- Stock Information -->
                            <?php 
                            $stock = isset($product['stock']) ? $product['stock'] : 0;
                            $stock_class = '';
                            $stock_text = '';
                            
                            if($stock > 10) {
                                $stock_class = 'status-published';
                                $stock_text = 'In Stock';
                            } elseif($stock > 0) {
                                $stock_class = 'status-draft';
                                $stock_text = 'Low Stock';
                            } else {
                                $stock_class = 'status-draft';
                                $stock_text = 'Out of Stock';
                            }
                            ?>
                            
                            <!-- Fetch and display first 3 features -->
                            <?php 
                            $features_sql = "SELECT feature_text FROM product_features 
                                           WHERE product_id = ? 
                                           ORDER BY sort_order LIMIT 3";
                            $features_stmt = $conn->prepare($features_sql);
                            $features_stmt->bind_param("i", $product['id']);
                            $features_stmt->execute();
                            $features_result = $features_stmt->get_result();
                            ?>
                            
                            <ul class="features-list" style="list-style: none; padding-left: 0; margin: 10px 0;">
                                <?php while ($feature = $features_result->fetch_assoc()): ?>
                                    <li style="margin-bottom: 5px; font-size: 13px;">
                                        <i class="fa-solid fa-check" style="color: var(--secondary); margin-right: 5px;"></i> 
                                        <?php echo htmlspecialchars($feature['feature_text']); ?>
                                    </li>
                                <?php endwhile; ?>
                                <?php 
                                $total_features = $features_result->num_rows;
                                $features_stmt->close();
                                
                                $count_sql = "SELECT COUNT(*) as total FROM product_features WHERE product_id = ?";
                                $count_stmt = $conn->prepare($count_sql);
                                $count_stmt->bind_param("i", $product['id']);
                                $count_stmt->execute();
                                $count_result = $count_stmt->get_result();
                                $total_count = $count_result->fetch_assoc()['total'];
                                $count_stmt->close();
                                
                                if ($total_count > 3): ?>
                                    <li class="more-features" style="color: var(--primary); font-size: 12px; font-weight: bold;">
                                        <i class="fa-solid fa-plus-circle"></i> <?php echo ($total_count - 3); ?> more features
                                    </li>
                                <?php endif; ?>
                            </ul>
                            
                            <div class="product-meta">
                                <span class="product-status <?php echo $stock_class; ?>">
                                    <i class="fa-solid fa-cube"></i> <?php echo $stock_text; ?> 
                                    <?php if($stock > 0): ?>
                                        (<?php echo $stock; ?>)
                                    <?php endif; ?>
                                </span>
                                <span class="product-date" style="font-size: 12px; color: var(--gray);">
                                    <i class="fa-solid fa-calendar"></i> 
                                    <?php echo date('M d, Y', strtotime($product['created_at'])); ?>
                                </span>
                            </div>
                            
                            <div class="product-actions">
                                <button class="action-btn edit" data-id="<?php echo $product['id']; ?>">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>
                                <button class="action-btn delete" data-id="<?php echo $product['id']; ?>">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                                <button class="action-btn view" onclick="window.location.href='product_detail.php?id=<?php echo $product['id']; ?>'">
                                    <i class="fa-solid fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-regular fa-box" style="font-size: 64px; color: var(--light-gray); margin-bottom: 20px;"></i>
                <h3>No Products Yet</h3>
                <p style="color: var(--gray); margin-bottom: 20px;">Add your first product to start selling.</p>
                <button class="btn" id="addFirstItemBtn">
                    <i class="fa-solid fa-plus"></i> Add Your First Product
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Edit Product Modal -->
<div id="editProductModal" class="modal" style="display:none;">
    <div class="modal-content" style="width: 900px; max-width: 95%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-edit"></i> Edit Product</h3>
            <button class="close-btn" id="closeEditModal">&times;</button>
        </div>
        
        <form id="editProductForm" enctype="multipart/form-data" action="add_product.php" method="POST">
            <input type="hidden" id="editProductId" name="id">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="editName">Product Name *</label>
                        <input type="text" id="editName" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDescription">Product Description *</label>
                        <textarea id="editDescription" name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editPrice">Price ($) *</label>
                        <input type="number" id="editPrice" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editStock">Stock Quantity *</label>
                        <input type="number" id="editStock" name="stock" class="form-control" min="0" required>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="editCategory">Category *</label>
                        <select id="editCategory" name="category" class="form-control" required>
                            <option value="">Select category</option>
                            <?php 
                            // Fetch categories for dropdown
                            $cat_sql = "SELECT * FROM product_categories ORDER BY name";
                            $cat_result = $conn->query($cat_sql);
                            while($cat = $cat_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($cat['slug']); ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Features Management -->
                    <div class="form-group">
                        <label for="editFeatures" style="font-weight: 600; margin-bottom: 10px; display: block;">
                            <i class="fa-solid fa-list-check"></i> Product Features
                        </label>
                        <div id="editFeaturesContainer">
                            <!-- Features will be added here dynamically -->
                        </div>
                        <button type="button" id="editAddFeatureBtn" class="btn" style="margin-top: 10px;">
                            <i class="fa-solid fa-plus"></i> Add Another Feature
                        </button>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="editFeatured" name="featured" value="1">
                        <label for="editFeatured">Feature this product on homepage</label>
                    </div>
                </div>
            </div>
            
            <!-- Image Upload Section -->
            <div class="form-group" style="margin-top: 20px;">
                <label style="font-weight: 600; margin-bottom: 15px; display: block;">
                    <i class="fa-solid fa-images"></i> Product Images
                </label>
                
                <!-- Existing Images -->
                <div id="existingImages" style="margin-bottom: 20px;">
                    <h5 style="color: var(--primary-dark); margin-bottom: 10px;">Current Images:</h5>
                    <div id="editImagesList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;">
                        <!-- Existing images will be loaded here -->
                    </div>
                </div>
                
                <!-- Add New Images -->
                <div>
                    <h5 style="color: var(--primary-dark); margin-bottom: 10px;">Add New Images:</h5>
                    <input type="file" id="editProductImages" name="product_images[]" class="form-control" multiple accept="image/*" style="padding: 8px;">
                    <small style="color: var(--gray); font-size: 12px; margin-top: 5px; display: block;">
                        <i class="fa-solid fa-info-circle"></i> Add new product images
                    </small>
                    <div id="editImagePreview" style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;"></div>
                    
                    <!-- Featured Image Selection -->
                    <div id="editFeaturedImageContainer" style="margin-top: 15px; display: none;">
                        <label for="editFeaturedImage">Select Featured Image *</label>
                        <select id="editFeaturedImage" name="featured_image" class="form-control" required>
                            <option value="">Select featured image</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="submit-btn" style="flex: 1;">
                    <i class="fa-solid fa-save"></i> Update Product
                </button>
                <button type="button" class="btn btn-outline" onclick="closeEditModal()" style="flex: 0.5;">
                    <i class="fa-solid fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Update the Category Modal -->
<div id="categoryModal" class="modal" style="display:none;">
    <div class="modal-content" style="width: 600px; max-width: 90%; text-align: center;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-folder"></i> Select Category</h3>
            <button class="close-btn" onclick="closeCategoryModal()">&times;</button>
        </div>
        
        <div style="padding: 20px;">
            <p style="margin-bottom: 30px; color: var(--gray);">Select a category to add a product to:</p>
            
            <div class="category-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                <?php foreach($categories as $cat): ?>
                    <button class="category-select-btn" data-category="<?php echo htmlspecialchars($cat['slug']); ?>" 
                            style="padding: 20px; background: white; border: 2px solid var(--light-gray); border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-folder" style="font-size: 32px; color: var(--primary);"></i>
                        <span style="font-weight: bold;"><?php echo htmlspecialchars($cat['name']); ?></span>
                    </button>
                <?php endforeach; ?>
                
                <!-- Add New Category Option -->
                <button class="category-select-btn" data-category="new" 
                        style="padding: 20px; background: var(--light); border: 2px dashed var(--gray); border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-plus" style="font-size: 32px; color: var(--gray);"></i>
                    <span style="font-weight: bold; color: var(--gray);">New Category</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirm Box -->
<div class="confirm-box-overlay" id="confirmBox">
    <div class="confirm-box">
        <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete</h3>
        <p id="confirmMessage"></p>
        <div class="confirm-actions">
            <button id="confirmYes">Yes, Delete</button>
            <button id="confirmNo">Cancel</button>
        </div>
    </div>
</div>

<!-- Add this Product Modal after the Category Modal -->
<div id="productModal" class="modal" style="display:none;">
    <div class="modal-content" style="width: 900px; max-width: 95%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fa-solid fa-box"></i> Add Product</h3>
            <button class="close-btn" id="closeModal">&times;</button>
        </div>
        
        <form id="productForm" enctype="multipart/form-data" method="POST">
            <input type="hidden" id="productId" name="id">
            
            <!-- Category Info Display -->
            <div id="categoryInfoDisplay" style="padding: 10px; background: var(--light); border-radius: 6px; margin-bottom: 15px; font-weight: bold; color: var(--primary); display: none;">
                <i class="fa-solid fa-folder"></i> Adding to: <span id="selectedCategoryName"></span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Product Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity *</label>
                        <input type="number" id="stock" name="stock" class="form-control" min="0" value="0" required>
                        <small style="color: var(--gray); font-size: 12px;">
                            <i class="fa-solid fa-info-circle"></i> Set to 0 for out of stock
                        </small>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div>
                    <!-- Features Management -->
                    <div class="form-group">
                        <label for="features" style="font-weight: 600; margin-bottom: 10px; display: block;">
                            <i class="fa-solid fa-list-check"></i> Product Features
                        </label>
                        <div id="featuresContainer">
                            <!-- Features will be added here dynamically -->
                            <div class="feature-input-group" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <input type="text" name="features[]" class="form-control" placeholder="Enter a feature (e.g., Exposed gear mechanism)">
                                <button type="button" class="remove-feature-btn" style="background: #dc3545; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 4px;">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" id="addFeatureBtn" class="btn" style="margin-top: 10px; background: var(--primary); color: white;">
                            <i class="fa-solid fa-plus"></i> Add Another Feature
                        </button>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="featured" name="featured" value="1">
                        <label for="featured">Feature this product on homepage</label>
                    </div>
                </div>
            </div>
            
            <!-- Image Upload Section -->
            <div class="form-group" style="margin-top: 20px;">
                <label for="product_images" style="font-weight: 600; margin-bottom: 15px; display: block;">
                    <i class="fa-solid fa-images"></i> Product Images
                </label>
                <input type="file" id="product_images" name="product_images[]" class="form-control" multiple accept="image/*" style="padding: 8px;">
                <small style="color: var(--gray); font-size: 12px; margin-top: 5px; display: block;">
                    <i class="fa-solid fa-info-circle"></i> Upload product images (multiple images allowed)
                </small>
                
                <!-- Image Preview -->
                <div id="imagePreview" style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;"></div>
                
                <!-- Featured Image Selection -->
                <div id="featuredImageContainer" style="margin-top: 15px; display: none;">
                    <label for="featured_image">Select Featured Image *</label>
                    <select id="featured_image" name="featured_image" class="form-control" required>
                        <option value="">Select featured image</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="submit-btn" style="flex: 1;">
                    <i class="fa-solid fa-plus"></i> Add Product
                </button>
                <button type="button" class="btn btn-outline" onclick="closeProductModal()" style="flex: 0.5;">
                    <i class="fa-solid fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal" style="display:none;">
    <div class="modal-content" style="width: 450px; max-width: 90%;">
        <div class="modal-header" style="border-bottom: 2px solid var(--danger);">
            <h3 style="color: var(--danger);">
                <i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete
            </h3>
            <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>
        
        <div style="padding: 25px; text-align: center;">
            <div style="font-size: 48px; color: var(--danger); margin-bottom: 20px;">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            
            <p style="font-size: 16px; color: var(--dark); margin-bottom: 15px;">
                Are you sure you want to delete this product?
            </p>
            
            <div id="deleteProductName" style="font-weight: bold; font-size: 18px; color: var(--primary); margin-bottom: 10px; padding: 10px; background: var(--light); border-radius: 6px;">
                <!-- Product name will be inserted here -->
            </div>
            
            <p style="font-size: 14px; color: var(--danger); margin-bottom: 25px;">
                <i class="fa-solid fa-circle-exclamation"></i> 
                This action cannot be undone. All product data including images and features will be permanently deleted.
            </p>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="closeDeleteModal()" class="btn" style="flex: 1; background: var(--gray);">
                    <i class="fa-solid fa-times"></i> Cancel
                </button>
                <button id="confirmDeleteBtn" class="btn" style="flex: 1; background: var(--danger);">
                    <i class="fa-solid fa-trash"></i> Delete Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;"></div>
 <script>
// ====================================
// TOAST NOTIFICATION SYSTEM
// ====================================
function showToast(type, title, message, duration = 3000) {
    const container = document.getElementById('toastContainer');
    if (!container) {
        console.error('Toast container not found!');
        return;
    }
    
    const icons = {
        success: 'fa-circle-check',
        error: 'fa-circle-xmark',
        warning: 'fa-triangle-exclamation',
        info: 'fa-circle-info'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fa-solid ${icons[type] || icons.info}"></i>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            ${message ? `<div class="toast-message">${message}</div>` : ''}
        </div>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, duration);
}

// ====================================
// DELETE CONFIRMATION MODAL
// ====================================
let pendingDeleteData = null;

function openDeleteModal(productId, productName) {
    pendingDeleteData = { id: productId, name: productName };
    document.getElementById('deleteProductName').textContent = productName;
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    deleteBtn.innerHTML = '<i class="fa-solid fa-trash"></i> Delete Product';
    deleteBtn.disabled = false;
    
    document.getElementById('deleteConfirmModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    pendingDeleteData = null;
}

// Close delete modal when clicking outside
document.addEventListener('click', function(e) {
    const deleteModal = document.getElementById('deleteConfirmModal');
    if (deleteModal && e.target === deleteModal) {
        closeDeleteModal();
    }
});

// Close delete modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const deleteModal = document.getElementById('deleteConfirmModal');
        if (deleteModal && deleteModal.style.display === 'flex') {
            closeDeleteModal();
        }
    }
});

// ====================================
// DROPDOWN TOGGLE
// ====================================
function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

window.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('dropdownMenu').style.display = 'none';
    }
});

// ====================================
// CATEGORY FILTERING
// ====================================
document.querySelectorAll('.category-btn').forEach(button => {
    button.addEventListener('click', function() {
        const category = this.getAttribute('data-category');
        
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        this.classList.add('active');
        
        const productItems = document.querySelectorAll('.product-item');
        productItems.forEach(item => {
            if (category === 'all' || item.getAttribute('data-category') === category) {
                item.style.display = 'block';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'scale(1)';
                }, 10);
            } else {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    item.style.display = 'none';
                }, 300);
            }
        });
    });
});

// ====================================
// ADD PRODUCT BUTTONS
// ====================================
document.getElementById('addProductBtn').addEventListener('click', function() {
    document.getElementById('categoryModal').style.display = 'flex';
});

const addFirstItemBtn = document.getElementById('addFirstItemBtn');
if (addFirstItemBtn) {
    addFirstItemBtn.addEventListener('click', function() {
        document.getElementById('categoryModal').style.display = 'flex';
    });
}

// ====================================
// CATEGORY SELECTION IN MODAL
// ====================================
document.querySelectorAll('.category-select-btn').forEach(button => {
    button.addEventListener('click', function() {
        const category = this.getAttribute('data-category');
        
        if (category === 'new') {
            const newCategory = prompt('Enter new category name:');
            if (newCategory) {
                fetch('add_category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        category: newCategory,
                        action: 'add_category'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', 'Category Created', `"${newCategory}" category has been added successfully.`);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('error', 'Error', data.message || 'Failed to create category.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'Error', 'An error occurred while creating the category.');
                });
            }
        } else {
            closeCategoryModal();
            openProductModal(category);
        }
    });
});

// ====================================
// PRODUCT MODAL FUNCTIONS
// ====================================
function openProductModal(category) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    
    form.reset();
    document.getElementById('productId').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-box"></i> Add Product';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('featuredImageContainer').style.display = 'none';
    
    const featuresContainer = document.getElementById('featuresContainer');
    featuresContainer.innerHTML = `
        <div class="feature-input-group" style="display: flex; gap: 10px; margin-bottom: 10px;">
            <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
            <button type="button" class="remove-feature-btn" style="background: #dc3545; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 4px;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    `;
    
    const categoryField = document.createElement('input');
    categoryField.type = 'hidden';
    categoryField.name = 'category';
    categoryField.value = category;
    categoryField.id = 'selectedCategory';
    
    const existingField = document.getElementById('selectedCategory');
    if (existingField) existingField.remove();
    
    form.appendChild(categoryField);
    
    const categoryInfo = document.getElementById('categoryInfoDisplay');
    const categoryName = document.getElementById('selectedCategoryName');
    categoryName.textContent = category.charAt(0).toUpperCase() + category.slice(1).replace('-', ' ');
    categoryInfo.style.display = 'block';
    
    modal.style.display = 'flex';
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

document.getElementById('closeModal').addEventListener('click', function() {
    closeProductModal();
});

// ====================================
// ADD FEATURE FUNCTIONALITY
// ====================================
document.getElementById('addFeatureBtn').addEventListener('click', function() {
    const container = document.getElementById('featuresContainer');
    const div = document.createElement('div');
    div.className = 'feature-input-group';
    div.style.display = 'flex';
    div.style.gap = '10px';
    div.style.marginBottom = '10px';
    div.innerHTML = `
        <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
        <button type="button" class="remove-feature-btn" style="background: #dc3545; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 4px;">
            <i class="fa-solid fa-times"></i>
        </button>
    `;
    container.appendChild(div);
    
    div.querySelector('.remove-feature-btn').addEventListener('click', function() {
        if (document.querySelectorAll('.feature-input-group').length > 1) {
            div.remove();
        }
    });
});

// ====================================
// IMAGE PREVIEW
// ====================================
document.getElementById('product_images').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const featuredSelect = document.getElementById('featured_image');
    preview.innerHTML = '';
    featuredSelect.innerHTML = '<option value="">Select featured image</option>';
    
    const files = e.target.files;
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '4px';
            img.style.border = '2px solid #ddd';
            preview.appendChild(img);
            
            const option = document.createElement('option');
            option.value = i;
            option.textContent = file.name;
            featuredSelect.appendChild(option);
        };
        
        reader.readAsDataURL(file);
    }
    
    if (files.length > 0) {
        document.getElementById('featuredImageContainer').style.display = 'block';
    }
});

// ====================================
// EDIT MODAL SETUP
// ====================================
document.addEventListener('DOMContentLoaded', function() {
    setupEditModal();
});

function setupEditModal() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.action-btn.edit')) {
            const button = e.target.closest('.action-btn.edit');
            const productId = button.getAttribute('data-id');
            
            document.getElementById('editFeaturesContainer').innerHTML = '';
            document.getElementById('editImagesList').innerHTML = '';
            document.getElementById('editImagePreview').innerHTML = '';
            document.getElementById('editFeaturedImageContainer').style.display = 'none';
            
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        showToast('error', 'Error', data.message);
                        return;
                    }
                    
                    document.getElementById('editProductId').value = data.id;
                    document.getElementById('editName').value = data.name;
                    document.getElementById('editDescription').value = data.description;
                    document.getElementById('editPrice').value = data.price;
                    document.getElementById('editStock').value = data.stock || 0;
                    document.getElementById('editCategory').value = data.category;
                    document.getElementById('editFeatured').checked = data.featured == 1;
                    
                    const featuresContainer = document.getElementById('editFeaturesContainer');
                    featuresContainer.innerHTML = '';
                    
                    if (data.features && data.features.length > 0) {
                        data.features.forEach((feature) => {
                            addEditFeatureInput(feature.feature_text || feature);
                        });
                    } else {
                        addEditFeatureInput('');
                    }
                    
                    loadExistingImages(productId);
                    document.getElementById('editProductModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error fetching product:', error);
                    showToast('error', 'Error', 'Error loading product data.');
                });
        }
    });
}

function loadExistingImages(productId) {
    fetch(`get_product_images.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            const imagesList = document.getElementById('editImagesList');
            
            if (data && data.length > 0) {
                let featuredSelect = document.getElementById('editFeaturedImage');
                if (!featuredSelect) {
                    featuredSelect = document.createElement('select');
                    featuredSelect.id = 'editFeaturedImage';
                    featuredSelect.name = 'featured_image';
                    featuredSelect.className = 'form-control';
                    
                    const container = document.getElementById('editFeaturedImageContainer');
                    if (container) {
                        container.innerHTML = '<label for="editFeaturedImage">Select Featured Image *</label>';
                        container.appendChild(featuredSelect);
                    }
                }
                
                featuredSelect.innerHTML = '<option value="">Select featured image</option>';
                
                data.forEach((image, index) => {
                    const imgDiv = document.createElement('div');
                    imgDiv.className = 'image-thumbnail';
                    imgDiv.style.position = 'relative';
                    imgDiv.innerHTML = `
                        <img src="${image.image_path}" alt="Product image" 
                             style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; border: ${image.is_featured ? '2px solid gold' : '1px solid #ddd'};">
                        <div style="font-size: 10px; text-align: center; margin-top: 5px; color: ${image.is_featured ? 'gold' : '#666'};">
                            ${image.is_featured ? '<i class="fa-solid fa-star"></i> Featured' : 'Image'}
                        </div>
                    `;
                    imagesList.appendChild(imgDiv);
                    
                    const option = document.createElement('option');
                    option.value = image.image_path;
                    option.textContent = `Image ${index + 1}`;
                    if (image.is_featured) {
                        option.selected = true;
                    }
                    featuredSelect.appendChild(option);
                });
                
                document.getElementById('editFeaturedImageContainer').style.display = 'block';
            } else {
                imagesList.innerHTML = '<p style="color: var(--gray); font-size: 14px;">No images uploaded yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading images:', error);
            document.getElementById('editImagesList').innerHTML = 
                '<p style="color: var(--danger); font-size: 14px;">Error loading images.</p>';
        });
}

function addEditFeatureInput(value = '') {
    const container = document.getElementById('editFeaturesContainer');
    const div = document.createElement('div');
    div.className = 'feature-input-group';
    div.style.display = 'flex';
    div.style.gap = '10px';
    div.style.marginBottom = '10px';
    div.innerHTML = `
        <input type="text" name="features[]" class="form-control" value="${value}" placeholder="Enter a feature">
        <button type="button" class="remove-feature-btn" style="background: #dc3545; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 4px;">
            <i class="fa-solid fa-times"></i>
        </button>
    `;
    container.appendChild(div);
    
    div.querySelector('.remove-feature-btn').addEventListener('click', function() {
        if (document.querySelectorAll('#editFeaturesContainer .feature-input-group').length > 1) {
            div.remove();
        }
    });
}

document.getElementById('editAddFeatureBtn').addEventListener('click', function() {
    addEditFeatureInput('');
});

function closeEditModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

document.getElementById('closeEditModal').addEventListener('click', closeEditModal);

// ====================================
// EDIT IMAGE PREVIEW
// ====================================
document.getElementById('editProductImages').addEventListener('change', function(e) {
    const preview = document.getElementById('editImagePreview');
    const featuredSelect = document.getElementById('editFeaturedImage');
    preview.innerHTML = '';
    featuredSelect.innerHTML = '<option value="">Select featured image</option>';
    
    const files = e.target.files;
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '4px';
            img.style.border = '2px solid #ddd';
            preview.appendChild(img);
            
            const option = document.createElement('option');
            option.value = i;
            option.textContent = file.name;
            featuredSelect.appendChild(option);
        };
        
        reader.readAsDataURL(file);
    }
    
    if (files.length > 0) {
        document.getElementById('editFeaturedImageContainer').style.display = 'block';
    }
});

// ====================================
// FORM SUBMISSIONS
// ====================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('productForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', 'Product Added', data.message || 'Product has been added successfully.');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'product.php';
                    }, 1500);
                } else {
                    showToast('error', 'Error', data.message || 'Failed to add product.');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('error', 'Error', 'Something went wrong.');
            });
        });
    }
});

// Edit form submission
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    showToast('success', 'Product Updated', 'Product has been updated successfully.');
                    closeEditModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', 'Error', response.message || 'Failed to update product.');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                showToast('error', 'Error', 'An error occurred. Please check the console for details.');
            }
        }
    };
    
    xhr.open('POST', 'add_product.php', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});

// ====================================
// DELETE PRODUCT FUNCTIONALITY
// ====================================
// Handle delete button clicks (event delegation)
document.addEventListener('click', function(e) {
    const deleteBtn = e.target.closest('.action-btn.delete');
    if (!deleteBtn) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const productId = deleteBtn.getAttribute('data-id');
    
    if (!productId) {
        showToast('error', 'Error', 'Product ID not found!');
        return;
    }
    
    const productItem = deleteBtn.closest('.product-item');
    const productName = productItem 
        ? productItem.querySelector('.product-title').textContent.trim() 
        : 'Unknown Product';
    
    openDeleteModal(productId, productName);
});

// Handle confirm delete button
document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!pendingDeleteData) {
        console.error('No pending delete data');
        return;
    }
    
    const { id: productId, name: productName } = pendingDeleteData;
    const deleteBtn = this;
    
    const originalHTML = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
    deleteBtn.disabled = true;
    
    try {
        const response = await fetch(`delete_product.php?id=${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeDeleteModal();
            showToast('success', 'Product Deleted', `"${productName}" has been successfully deleted.`, 4000);
            
            // Remove product card with animation
            const productCards = document.querySelectorAll('.product-item');
            productCards.forEach(card => {
                const delBtn = card.querySelector(`.action-btn.delete[data-id="${productId}"]`);
                if (delBtn) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.remove();
                        const remaining = document.querySelectorAll('.product-item');
                        if (remaining.length === 0) {
                            setTimeout(() => location.reload(), 500);
                        }
                        updateProductStats();
                    }, 300);
                }
            });
        } else {
            showToast('error', 'Delete Failed', data.message || 'Failed to delete product.');
            deleteBtn.innerHTML = originalHTML;
            deleteBtn.disabled = false;
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast('error', 'Error', 'An unexpected error occurred.');
        deleteBtn.innerHTML = '<i class="fa-solid fa-trash"></i> Delete Product';
        deleteBtn.disabled = false;
    }
});

// Update stats after deletion
function updateProductStats() {
    const totalProductsEl = document.querySelector('.stat-card.total .number');
    if (totalProductsEl) {
        const currentCount = document.querySelectorAll('.product-item:not([style*="opacity: 0"])').length;
        totalProductsEl.textContent = currentCount;
    }
}

// ====================================
// INITIALIZE REMOVE FEATURE BUTTONS
// ====================================
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-feature-btn')) {
        const featureGroup = e.target.closest('.feature-input-group');
        if (document.querySelectorAll('.feature-input-group').length > 1) {
            featureGroup.remove();
        }
    }
});

// ====================================
// ADD CATEGORY BUTTON STYLES
// ====================================
const style = document.createElement('style');
style.textContent = `
    .category-btn {
        padding: 8px 16px;
        background: var(--light);
        border: 1px solid var(--light-gray);
        border-radius: 20px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        color: var(--dark);
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 5px;
        border: none;
        font-family: 'Montserrat', sans-serif;
    }
    
    .category-btn:hover {
        background: var(--light-gray);
        transform: translateY(-2px);
    }
    
    .category-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .category-btn i {
        font-size: 12px;
    }
    
    .category-select-btn:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(7, 90, 174, 0.1);
    }
    
    .category-header {
        border-bottom: 2px solid var(--primary);
    }
    
    .btn-outline {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
    }
    
    .btn-outline:hover {
        background: var(--primary);
        color: white;
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>