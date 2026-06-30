<?php
require 'dbconn.php';
require 'auth.php';
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in
    echo "Access Denied. Please log in.";
    exit;
}
$user_id = $_SESSION['user_id'];

// ✅ Fetch user info using prepared statement
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle missing user record
if (!$user) {
    die("<p style='color:red; text-align:center;'>❌ User not found in database. Please check your users table.</p>");
}

// Count unread messages
$sql_unread = "SELECT COUNT(*) AS unread_count FROM messages WHERE is_read = 0";
$result_unread = $conn->query($sql_unread);
$row_unread = $result_unread->fetch_assoc();
$unread_count = $row_unread['unread_count'];

$user_name = $user['name'];
$first_letter = strtoupper(substr($user_name, 0, 1));


// Load site settings from database
$result = $conn->query("SELECT site_title FROM site_settings WHERE id = 1");
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
}
// ✅ Get user avatar if exists
$avatar_path = "assets/images/avatars/{$user_id}.png";
$default_avatar = "assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Panel</title>
    <link rel="stylesheet" href="../css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Sidebar and other existing styles remain the same */
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

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

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

        .user-info i.fa-caret-down {
            color: var(--gray);
            font-size: 14px;
            transition: var(--transition);
        }

        .user-info:hover i.fa-caret-down {
            color: var(--primary);
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
            color: var(--dark);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            border-bottom: 1px solid var(--light-gray);
        }

        .logout-btn:hover {
            background: var(--light);
            color: var(--primary);
        }

        .logout-btn:last-child {
            border-bottom: none;
            color: var(--danger);
        }

        .logout-btn:last-child:hover {
            background: var(--danger);
            color: var(--white);
        }

        /* Enhanced Profile Avatar Styles */
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }

        .profile-background {
            height: 180px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            position: relative;
        }

        .profile-header {
            text-align: center;
            position: relative;
            margin-top: -80px;
            padding: 0 30px 30px;
        }

        .profile-avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 6px solid var(--white);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin: 0 auto;
            position: relative;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            color: var(--white);
            overflow: hidden;
            cursor: pointer;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            display: none;
        }

        .profile-avatar.has-image img {
            display: block;
        }

        .profile-avatar.has-image .avatar-initial {
            display: none;
        }

        .avatar-edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
            cursor: pointer;
        }

        .profile-avatar-container:hover .avatar-edit-overlay {
            opacity: 1;
        }

        .avatar-edit-overlay i {
            color: var(--white);
            font-size: 24px;
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            background: var(--primary);
            border: 3px solid var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
        }

        .avatar-upload-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }

        .profile-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .profile-header p {
            color: var(--gray);
            font-size: 16px;
            margin-bottom: 15px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--secondary), #028a02);
            color: var(--white);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(2, 202, 2, 0.3);
        }

        /* Rest of the existing styles remain the same */
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px;
        }

        .profile-section {
            background: var(--light);
            padding: 25px;
            border-radius: 12px;
            border-left: 4px solid var(--primary);
        }

        .profile-section h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--dark);
            font-size: 18px;
        }

        .profile-section h3 i {
            color: var(--primary);
        }

        .info-grid {
            display: grid;
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark);
            min-width: 120px;
        }

        .info-value {
            color: var(--gray);
            text-align: right;
            flex: 1;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            box-shadow: 0 4px 15px rgba(7, 90, 174, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(7, 90, 174, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #e01a6c);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary), #028a02);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(2, 202, 2, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(2, 202, 2, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--light-gray);
            color: var(--gray);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        /* Avatar Upload Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 20px;
        }

        .modal-content {
            background: var(--white);
            padding: 35px;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            position: relative;
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }

        .modal-header h3 {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 22px;
            color: var(--dark);
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            background: var(--light-gray);
            color: var(--danger);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--light-gray);
            border-radius: 10px;
            font-size: 15px;
            transition: var(--transition);
            background: var(--light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(7, 90, 174, 0.1);
        }

        .file-input-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed var(--light-gray);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-input:hover {
            border-color: var(--primary);
            background: var(--light);
        }

        .file-input input[type="file"] {
            display: none;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--light-gray);
            overflow: hidden;
            margin: 0 auto;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .status-active {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--success);
            font-weight: 600;
        }

        .status-active::before {
            content: '';
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header h2 span,
            .sidebar-nav a span {
                display: none;
            }
            .sidebar-nav a {
                justify-content: center;
                padding: 16px;
            }
            .main {
                margin-left: 100px;
            }
            .profile-content {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            .profile-background {
                height: 150px;
            }
            .profile-header {
                margin-top: -60px;
            }
            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 36px;
            }
            .actions-grid {
                grid-template-columns: 1fr;
            }
            .modal-content {
                padding: 25px;
                margin: 20px;
            }
        }

        @media (max-width: 480px) {
            .main {
                margin-left: 0;
                padding: 15px;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .header {
                padding: 20px;
            }
            .header h1 {
                font-size: 22px;
            }
            .profile-container {
                margin: 0 auto;
            }
            .form-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }

      .alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 280px; /* ✅ reduced width */
    width: auto;
    padding: 10px 14px; /* tighter padding */
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 1000;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    animation: fadeIn 0.3s ease-in-out;
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.alert i {
    font-size: 16px;
}

/* ✅ Success Message */
.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* ❌ Error Message */
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ✨ Fade + slide animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 🕒 Auto-hide transition */
.alert.hide {
    opacity: 0;
    transform: translateY(-10px);
}


    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>
            <img src="../image/logo.png" alt="St4nger Logo" class="sidebar-logo">
            <span>ChronoVerse Profile</span>
        </h2>
    </div>
    
    <div class="sidebar-nav">
        <a href="home.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
        <a href="message.php" class="nav-link">
            <i class="fa-solid fa-envelope"></i>
            <span>Messages</span>
            <?php if ($unread_count > 0): ?>
                <span class="notif-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="../portfolio/port.php"><i class="fa-solid fa-briefcase"></i> <span>Portfolio</span></a>
        <a href="../analytic/analytic.php"><i class="fa-solid fa-chart-bar"></i> <span>Analytics</span></a>
       <a href="../Sales/Sales.php">
    <i class="fa-solid fa-chart-line"></i>
    <span>Sales Report</span>
</a>

    </div>
</div>

<!-- Main Content -->
<div class="main">
    <!-- Header -->
    <div class="header">
        <h1>Profile Settings 👤</h1>
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
<?php
// Display success message
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">';
    echo '<i class="fa-solid fa-circle-check"></i> ' . $_SESSION['success_message'];
    echo '</div>';
    unset($_SESSION['success_message']);
}

// Display error messages
if (isset($_SESSION['error_messages'])) {
    foreach ($_SESSION['error_messages'] as $error) {
        echo '<div class="alert alert-danger">';
        echo '<i class="fa-solid fa-circle-exclamation"></i> ' . $error;
        echo '</div>';
    }
    unset($_SESSION['error_messages']);
}
?>
    <!-- Profile Container -->
    <div class="profile-container">
        <!-- Background Header -->
        <div class="profile-background"></div>
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar-container">
                <div class="profile-avatar <?php echo $has_custom_avatar ? 'has-image' : ''; ?>" onclick="openModal('avatarModal')">
                    <?php if ($has_custom_avatar): ?>
                        <img src="<?php echo $avatar_path; ?>?<?php echo time(); ?>" alt="Profile Photo">
                    <?php endif; ?>
                    <div class="avatar-initial"><?php echo $first_letter; ?></div>
                </div>
                <div class="avatar-edit-overlay" onclick="openModal('avatarModal')">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <div class="avatar-upload-btn" onclick="openModal('avatarModal')">
                    <i class="fa-solid fa-camera"></i>
                </div>
            </div>
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <div class="role-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <?php echo strtoupper($user['type']); ?> ACCESS
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Personal Information -->
            <div class="profile-section">
                <h3>
                    <i class="fa-solid fa-user-circle"></i>
                    Personal Information
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">User ID</span>
                        <span class="info-value">#<?php echo $user['id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value status-active">Active</span>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="profile-section">
                <h3>
                    <i class="fa-solid fa-chart-line"></i>
                    Account Details
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Member Since</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Account Type</span>
                        <span class="info-value"><?php echo ucfirst($user['type']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Login</span>
                        <span class="info-value">Recently</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Profile Complete</span>
                        <span class="info-value">100%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="padding: 0 30px 30px;">
            <div class="actions-grid">
                <button class="btn btn-primary" onclick="openModal('editProfileModal')">
                    <i class="fa-solid fa-user-pen"></i>
                    Edit Profile
                </button>
                <button class="btn btn-danger" onclick="openModal('changePasswordModal')">
                    <i class="fa-solid fa-lock"></i>
                    Change Password
                </button>
            
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div class="modal" id="avatarModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fa-solid fa-camera"></i>
                Update Profile Picture
            </h3>
            <button class="close-btn" onclick="closeModal('avatarModal')">&times;</button>
        </div>
        <form id="avatarForm" method="POST" action="upload_avatar.php" enctype="multipart/form-data">
            <div class="form-group">
                <div class="file-input-container">
                    <div class="avatar-preview">
                        <?php if ($has_custom_avatar): ?>
                            <img src="<?php echo $avatar_path; ?>?<?php echo time(); ?>" alt="Current Avatar" id="avatarPreview">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;color:white;font-size:36px;font-weight:bold;">
                                <?php echo $first_letter; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label class="file-input">
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" required>
                        <i class="fa-solid fa-cloud-upload-alt" style="font-size: 24px; margin-bottom: 8px;"></i>
                        <div>Click to upload new photo</div>
                        <small style="color: var(--gray);">JPG, PNG, GIF (Max 2MB)</small>
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <?php if ($has_custom_avatar): ?>
                <button type="button" class="btn btn-danger" onclick="removeAvatar()">
                    <i class="fa-solid fa-trash"></i>
                    Remove Photo
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline" onclick="closeModal('avatarModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i>
                    Save Photo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal" id="editProfileModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fa-solid fa-user-pen"></i>
                Edit Profile
            </h3>
            <button class="close-btn" onclick="closeModal('editProfileModal')">&times;</button>
        </div>
        <form method="POST" action="update_profile.php">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('editProfileModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal" id="changePasswordModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fa-solid fa-lock"></i>
                Change Password
            </h3>
            <button class="close-btn" onclick="closeModal('changePasswordModal')">&times;</button>
        </div>
        <form method="POST" action="change_password.php">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('changePasswordModal')">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-key"></i>
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Avatar upload preview
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

function removeAvatar() {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        fetch('remove_avatar.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error removing photo: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error removing photo');
            });
    }
}

// Close modal when clicking outside
window.onclick = function(e) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (e.target === modal) {
            closeModal(modal.id);
        }
    });
    
    // Close dropdown when clicking outside
    const dropdown = document.querySelector('.user-dropdown');
    if (!dropdown.contains(e.target)) {
        document.getElementById('dropdownMenu').style.display = 'none';
    }
};

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.style.display === 'flex') {
                closeModal(modal.id);
            }
        });
        document.getElementById('dropdownMenu').style.display = 'none';
    }
});

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => el.classList.add('hide'));
}, 4000);
</script>

</body>
</html>