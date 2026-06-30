<?php
session_start();
require 'dbconn.php';

function require_admin() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        header("Location: ../index.php");
        exit;
    }
}

// Get user info for header
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$first_letter = strtoupper(substr($user_name, 0, 1));

// ✅ Get user avatar if exists
$avatar_path = "assets/images/avatars/{$user_id}.png";
$default_avatar = "assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);
// Load site settings from database
$result = $conn->query("SELECT site_title FROM site_settings WHERE id = 1");
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
}
// Get unread message count for notification badge
$unread_query = "SELECT COUNT(*) as unread_count FROM messages WHERE is_read = 0";
$unread_result = mysqli_query($conn, $unread_query);
$unread_data = mysqli_fetch_assoc($unread_result);
$unread_count = $unread_data['unread_count'] ?? 0;

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id_to_delete = intval($_GET['delete']);

    if ($user_id_to_delete === $_SESSION['user_id']) {
        $_SESSION['flash_message'] = [
            'text' => "You cannot delete your own account.",
            'type' => 'error'
        ];
    } else {
        $delete_query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user_id_to_delete);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = [
                'text' => "User deleted successfully!",
                'type' => 'success'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'text' => "Error deleting user. Please try again.",
                'type' => 'error'
            ];
        }

        $stmt->close();
    }

    // Redirect back to user management page
    header("Location: user.php");
    exit();
}


// Fetch user data for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM users WHERE id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_user = mysqli_fetch_assoc($edit_result);
    }
}

// Fetch all users except admins
$query = "SELECT * FROM users WHERE type = 'user' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo htmlspecialchars($settings['site_title']); ?></title>
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
            color: white;
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

        .user-info i.fa-caret-down {
            font-size: 14px;
            color: var(--gray);
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

        .dropdown-menu a:hover {
            background: var(--light);
        }

        .logout-btn {
            color: var(--danger) !important;
        }

        .logout-btn:hover {
            background: var(--danger) !important;
            color: var(--white) !important;
        }

        /* User Management Styles */
        .container {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        h2 {
            margin-bottom: 20px;
            color: var(--dark);
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background: var(--primary);
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: #f1f1f1;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: #17a2b8;
            color: white;
        }

        .btn-edit:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .add-user-btn {
            background: var(--success);
            color: white;
            padding: 12px 20px;
        }

        .add-user-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .user-type-admin {
            color: var(--primary);
            font-weight: 600;
        }

        .user-type-user {
            color: var(--success);
            font-weight: 500;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: var(--dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray);
            transition: var(--transition);
        }

        .close-modal:hover {
            color: var(--danger);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
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
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
            font-family: 'Montserrat', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(7, 90, 174, 0.1);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'><path fill='%236c757d' d='M2 0L0 2h4zm0 5L0 3h4z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 12px;
        }

        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-secondary {
            background: var(--gray);
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .error-message {
            color: var(--danger);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .success-message {
            background: var(--success);
            color: white;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }

        .password-note {
            background: var(--light);
            padding: 10px 15px;
            border-radius: 6px;
            border-left: 4px solid var(--warning);
            margin-bottom: 15px;
            font-size: 13px;
            color: var(--dark);
        }

        .password-note i {
            color: var(--warning);
        }

        /* Modal Container Styling */
.delete-modal {
    position: relative; /* or absolute if needed */
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: fadeInScale 0.25s ease;
    margin: 100px auto; /* centers horizontally and pushes it down */
    width: 400px; /* optional: control modal width */
    max-width: 90%; /* makes it responsive */
    background: #fff; /* ensure visible background */
}


/* Header */
.delete-modal .modal-header {
    background-color: #dc3545;
    color: #fff;
    border-bottom: none;
    padding: 15px 20px;
}

.delete-modal .modal-header .modal-title {
    font-weight: 600;
    font-size: 1.1rem;
}

/* Body */
.delete-modal .modal-body {
    padding: 20px;
    color: #333;
    font-size: 1rem;
}

/* Footer */
.delete-modal .modal-footer {
    border-top: none;
    padding: 15px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Buttons */
.delete-modal .confirm-btn {
    background-color: #dc3545;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.delete-modal .confirm-btn:hover {
    background-color: #bb2d3b;
    transform: scale(1.05);
}

.delete-modal .cancel-btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.delete-modal .cancel-btn:hover {
    background-color: #e9ecef;
}

/* Fade In Animation */
@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
/* New user type styling */
.user-type-customer {
    color: var(--secondary);
    font-weight: 600;
    background: rgba(2, 202, 2, 0.1);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
}

/* Table separation styling */
h3 {
    margin-top: 40px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--light-gray);
    color: var(--primary);
}

/* Responsive table container */
.table-container {
    overflow-x: auto;
    margin-bottom: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

/* Section titles */
.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.3rem;
    margin-bottom: 20px;
}
/* Filter Buttons */
.filter-buttons {
    margin: 20px 0;
    display: flex;
    gap: 15px;
    border-bottom: 2px solid var(--light-gray);
    padding-bottom: 15px;
}

.filter-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    background: var(--light-gray);
    color: var(--gray);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
}

.filter-btn:hover {
    background: #dde1e6;
    transform: translateY(-2px);
}

.filter-btn.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    box-shadow: 0 4px 12px rgba(7, 90, 174, 0.2);
}

/* Table Sections */
.table-section {
    display: none;
    animation: fadeIn 0.3s ease;
}

.table-section.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Table Container */
.table-container {
    overflow-x: auto;
    margin-bottom: 30px;
    border-radius: 8px;
    border: 1px solid var(--light-gray);
    background: white;
}

/* Enhanced table styles */
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
}

th {
    background: linear-gradient(135deg, var(--light-gray), #e9ecef);
    color: var(--dark);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    font-size: 12px;
    padding: 15px 12px;
    border-bottom: 2px solid var(--light-gray);
}

td {
    padding: 12px;
    border-bottom: 1px solid var(--light-gray);
    vertical-align: middle;
}

tr:hover {
    background: rgba(7, 90, 174, 0.03);
}

/* User type badges */
.user-type-admin, .user-type-user, .user-type-customer {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

.user-type-admin {
    background: rgba(220, 53, 69, 0.1);
    color: var(--danger);
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.user-type-user {
    background: rgba(7, 90, 174, 0.1);
    color: var(--primary);
    border: 1px solid rgba(7, 90, 174, 0.2);
}

.user-type-customer {
    background: rgba(2, 202, 2, 0.1);
    color: var(--secondary);
    border: 1px solid rgba(2, 202, 2, 0.2);
}

/* Table Header */
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.table-header h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    font-size: 1.2rem;
}

/* Badge */
.badge {
    background: var(--primary);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>
            <img src="../image/logo.png" alt="St4nger Logo" class="sidebar-logo">
            <span>ChronoVerse User Management</span>
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
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert <?= $_SESSION['flash_message']['type'] ?>">
        <?= $_SESSION['flash_message']['text'] ?>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="main">
    <div class="header">
        <h1>Manage Users</h1>
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
                <a href="../logout.php" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <div class="container">
    <div class="top-bar">
        <h2><i class="fa-solid fa-users"></i> Manage Users</h2>
        <button class="btn add-user-btn" onclick="openModal('addAccountModal')">
            <i class="fa-solid fa-user-plus"></i> Add User
        </button>
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons" style="margin: 20px 0; display: flex; gap: 15px;">
        <button class="filter-btn active" onclick="showTable('system-users')" id="systemUsersBtn">
            <i class="fa-solid fa-user-tie"></i> System Users
        </button>
        <button class="filter-btn" onclick="showTable('new-registrations')" id="newRegistrationsBtn">
            <i class="fa-solid fa-user-plus"></i> New Registrations
        </button>
    </div>
                        <!-- Search Bar -->
<div class="search-container" style="margin: 20px 0;">
    <div class="input-group" style="display: flex; max-width: 400px;">
        <input type="text" id="searchInput" class="form-control" 
               placeholder="Search users..." 
               style="border-radius: 8px 0 0 8px; border-right: none;">
        <button class="btn btn-primary" onclick="searchUsers()" 
                style="border-radius: 0 8px 8px 0; padding: 12px 20px;">
            <i class="fa-solid fa-search"></i>
        </button>
    </div>
</div>
    <!-- System Users Table -->
    <div id="system-users-table" class="table-section active">
        <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: var(--primary); margin: 0;">
                <i class="fa-solid fa-user-tie"></i> System Users
            </h3>
            <span class="badge" style="background: var(--primary); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                Total: <?= mysqli_num_rows($result) ?>
            </span>
        </div>
        
        <div class="table-container" style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <span class="<?= $row['type'] === 'admin' ? 'user-type-admin' : 'user-type-user'; ?>">
                                    <?= ucfirst($row['type']) ?>
                                </span>
                            </td>
                            <td><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['email']) ?>', '<?= $row['type'] ?>')">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                                <button class="btn btn-danger delete-btn" 
                                        data-user-id="<?= $row['id'] ?>" 
                                        data-user-name="<?= htmlspecialchars($row['name']) ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#999; padding: 40px 20px;">
                            <i class="fa-solid fa-users-slash" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            No system users found
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Fetch data from new_users table
    $new_users_query = "SELECT * FROM new_users ORDER BY created_at DESC";
    $new_users_result = mysqli_query($conn, $new_users_query);
    ?>
    
    <!-- New Registrations Table -->
    <div id="new-registrations-table" class="table-section">
        <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: var(--secondary); margin: 0;">
                <i class="fa-solid fa-user-plus"></i> New Registrations
            </h3>
            <span class="badge" style="background: var(--secondary); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                Total: <?= mysqli_num_rows($new_users_result) ?>
            </span>
        </div>
        
        <div class="table-container" style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($new_users_result) > 0): ?>
                    <?php while ($new_user = mysqli_fetch_assoc($new_users_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($new_user['name']) ?></td>
                            <td><?= htmlspecialchars($new_user['email']) ?></td>
                            <td>
                                <span class="<?= $new_user['user_type'] === 'admin' ? 'user-type-admin' : 'user-type-customer'; ?>">
                                    <?= ucfirst($new_user['user_type']) ?>
                                </span>
                            </td>
                            <td><?= date("M d, Y H:i", strtotime($new_user['created_at'])) ?></td>
                            <td><?= date("M d, Y H:i", strtotime($new_user['updated_at'])) ?></td>
                            <td>
                                <button class="btn btn-edit" onclick="openNewUserEditModal(<?= $new_user['id'] ?>)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                                <button class="btn btn-danger delete-new-user-btn" 
                                        data-user-id="<?= $new_user['id'] ?>" 
                                        data-user-name="<?= htmlspecialchars($new_user['name']) ?>"
                                        data-user-type="new_user"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#999; padding: 40px 20px;">
                            <i class="fa-solid fa-user-clock" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            No new registrations found
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal" id="addAccountModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fa-solid fa-user-plus"></i>
                Add New Account
            </h3>
            <button class="close-btn" onclick="closeModal('addAccountModal')">&times;</button>
        </div>
        
        <!-- Remove form action and use onsubmit -->
        <form id="addAccountForm" onsubmit="submitAccountForm(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name" required>
                    <div class="error-message" id="nameError"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email address" required>
                    <div class="error-message" id="emailError"></div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                    <div class="error-message" id="passwordError"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                    <div class="error-message" id="confirmPasswordError"></div>
                </div>
                <div class="form-group">
                    <label for="type">Account Type</label>
                    <select id="type" name="type" class="form-control form-select" required>
                        <option value="">Select account type</option>
                        <option value="admin">Administrator</option>
                        <option value="user">Standard User</option>
                    </select>
                    <div class="error-message" id="typeError"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAccountModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-user-plus"></i>
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Edit Account Modal -->
<div id="editAccountModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fa-solid fa-user-pen"></i>
                Edit User Account
            </h3>
            <button class="close-modal" onclick="closeModal('editAccountModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="editSuccessMessage" class="success-message"></div>
            <div class="password-note">
                <i class="fa-solid fa-info-circle"></i>
                Leave password fields blank to keep current password
            </div>
            <form id="editAccountForm" action="edit_accounts.php" method="POST">
                <input type="hidden" id="edit_user_id" name="user_id">
                
                <div class="form-group">
                    <label for="edit_name">Full Name</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required 
                           placeholder="Enter full name" minlength="2" maxlength="100">
                    <div class="error-message" id="editNameError"></div>
                </div>

                <div class="form-group">
                    <label for="edit_email">Email Address</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required 
                           placeholder="Enter email address">
                    <div class="error-message" id="editEmailError"></div>
                </div>

                <div class="form-group">
                    <label for="edit_password">New Password (Optional)</label>
                    <input type="password" id="edit_password" name="password" class="form-control" 
                           placeholder="Enter new password (leave blank to keep current)" minlength="6">
                    <div class="error-message" id="editPasswordError"></div>
                </div>

                <div class="form-group">
                    <label for="edit_confirm_password">Confirm New Password</label>
                    <input type="password" id="edit_confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Confirm new password">
                    <div class="error-message" id="editConfirmPasswordError"></div>
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editAccountModal')">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitEditForm()">Update Account</button>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content delete-modal">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel"><i class="fa-solid fa-triangle-exclamation"></i> Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete <strong id="userName"></strong>? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary cancel-btn" data-bs-dismiss="modal">
          <i class="fa-solid fa-xmark"></i> Cancel
        </button>
        <a href="#" id="confirmDeleteBtn" class="btn btn-danger confirm-btn">
          <i class="fa-solid fa-trash"></i> Delete
        </a>
      </div>
    </div>
  </div>
</div>
<!-- Bootstrap JS (v5 or higher) -->
<script src="../js/bootstrap.bundle.min.js"></script>

<script>
    
    function toggleDropdown() {
        const dropdown = document.getElementById('dropdownMenu');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdownMenu');
        const userInfo = document.querySelector('.user-info');
        
        if (!userInfo.contains(event.target) && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    });

    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        document.body.style.overflow = 'auto';
        resetForm(modalId);
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });
    }

    function resetForm(modalId) {
        if (modalId === 'addAccountModal') {
            document.getElementById('addAccountForm').reset();
            hideAllErrors('add');
        } else if (modalId === 'editAccountModal') {
            document.getElementById('editAccountForm').reset();
            hideAllErrors('edit');
        }
    }

    function hideAllErrors(formType) {
        const prefix = formType === 'add' ? '' : 'edit';
        const errorElements = document.querySelectorAll('.error-message');
        errorElements.forEach(error => {
            if (error.id.includes(prefix) || (formType === 'add' && !error.id.includes('edit'))) {
                error.textContent = '';
                error.style.display = 'none';
            }
        });
    }

    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    // Edit modal functions
    function openEditModal(userId, userName, userEmail) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_name').value = userName;
        document.getElementById('edit_email').value = userEmail;
        
        openModal('editAccountModal');
    }

    function validateAddForm() {
        let isValid = true;
        hideAllErrors('add');

        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Name validation
        if (name.length < 2) {
            showError('nameError', 'Full name must be at least 2 characters long.');
            isValid = false;
        } else if (name.length > 100) {
            showError('nameError', 'Full name must not exceed 100 characters.');
            isValid = false;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            showError('emailError', 'Email address is required.');
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showError('emailError', 'Please enter a valid email address.');
            isValid = false;
        }

        // Password validation
        if (!password) {
            showError('passwordError', 'Password is required.');
            isValid = false;
        } else if (password.length < 6) {
            showError('passwordError', 'Password must be at least 6 characters long.');
            isValid = false;
        }

        // Confirm password validation
        if (password !== confirmPassword) {
            showError('confirmPasswordError', 'Passwords do not match.');
            isValid = false;
        }

        return isValid;
    }

    function validateEditForm() {
        let isValid = true;
        hideAllErrors('edit');

        const name = document.getElementById('edit_name').value.trim();
        const email = document.getElementById('edit_email').value.trim();
        const password = document.getElementById('edit_password').value;
        const confirmPassword = document.getElementById('edit_confirm_password').value;

        // Name validation
        if (name.length < 2) {
            showError('editNameError', 'Full name must be at least 2 characters long.');
            isValid = false;
        } else if (name.length > 100) {
            showError('editNameError', 'Full name must not exceed 100 characters.');
            isValid = false;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            showError('editEmailError', 'Email address is required.');
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showError('editEmailError', 'Please enter a valid email address.');
            isValid = false;
        }

        // Password validation (only if provided)
        if (password && password.length < 6) {
            showError('editPasswordError', 'Password must be at least 6 characters long.');
            isValid = false;
        }

        // Confirm password validation (only if password provided)
        if (password && password !== confirmPassword) {
            showError('editConfirmPasswordError', 'Passwords do not match.');
            isValid = false;
        }

        return isValid;
    }

    function submitAccountForm(event) {
        event.preventDefault(); // Prevent traditional form submission
        
        const form = event.target;
        const formData = new FormData(form);
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
        submitBtn.disabled = true;
        
        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(error => {
            error.textContent = '';
            error.style.display = 'none';
        });
        
        fetch('add_account.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                // Success - close modal and update table dynamically
                closeModal('addAccountModal');
                
                if (data.userData) {
                    // Add new user to table without page reload
                    addUserToTable(data.userData);
                    showFlashMessage(data.message, 'success');
                } else {
                    // Fallback: reload page if no user data
                    window.location.reload();
                }
                
            } else {
                // Display validation errors
                processAddAccountResponse(data);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showError('emailError', 'An error occurred. Please try again.');
        })
        .finally(() => {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    function processAddAccountResponse(data) {
        // Clear all previous errors
        hideAllErrors('add');

        if (data.errors) {
            for (const [field, errorMsg] of Object.entries(data.errors)) {
                const errorElement = document.getElementById(`${field}Error`);
                if (errorElement) {
                    errorElement.textContent = errorMsg;
                    errorElement.style.display = 'block';
                } else {
                    // If no specific field, show in general area
                    const generalError = document.getElementById('emailError');
                    if (generalError) {
                        generalError.textContent = errorMsg;
                        generalError.style.display = 'block';
                    }
                }
            }
        }
    }

    // Dynamic table update functions
    function addUserToTable(userData) {
        const tbody = document.querySelector('table tbody');
        const noUsersRow = document.querySelector('table tbody tr td[colspan="6"]');
        
        // Remove "No users found" message if it exists
        if (noUsersRow) {
            noUsersRow.closest('tr').remove();
        }
        
        // Create new row
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${userData.id}</td>
            <td>${escapeHtml(userData.name)}</td>
            <td>${escapeHtml(userData.email)}</td>
            <td>
                <span class="user-type-${userData.type}">
                    ${userData.type.charAt(0).toUpperCase() + userData.type.slice(1)}
                </span>
            </td>
            <td>Just now</td>
            <td>
                <button class="btn btn-edit" onclick="openEditModal(${userData.id}, '${escapeHtml(userData.name)}', '${escapeHtml(userData.email)}', '${userData.type}')">
                    <i class="fa-solid fa-pen"></i> Edit
                </button>
                <button class="btn btn-danger delete-btn" 
                        data-user-id="${userData.id}" 
                        data-user-name="${escapeHtml(userData.name)}"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteModal">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </td>
        `;
        
        // Add to top of table
        tbody.insertBefore(newRow, tbody.firstChild);
        
        // Re-attach delete button event listeners
        attachDeleteButtonListeners();
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function showFlashMessage(message, type) {
        // Create flash message
        const flashDiv = document.createElement('div');
        flashDiv.className = `flash-message alert-${type}`;
        flashDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background: ${type === 'success' ? 'var(--success)' : 'var(--danger)'};
            color: white;
            z-index: 10000;
            box-shadow: var(--shadow);
            animation: slideIn 0.3s ease;
        `;
        flashDiv.innerHTML = `
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        `;
        
        document.body.appendChild(flashDiv);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            flashDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => flashDiv.remove(), 300);
        }, 3000);
    }

    function attachDeleteButtonListeners() {
        const deleteButtons = document.querySelectorAll(".delete-btn");
        const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
        const userName = document.getElementById("userName");

        deleteButtons.forEach(button => {
            // Remove existing listeners and add new ones
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });

        // Re-attach the event listeners
        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", function() {
                const userId = this.getAttribute("data-user-id");
                const name = this.getAttribute("data-user-name");
                userName.textContent = name;
                confirmDeleteBtn.href = "user.php?delete=" + userId;
            });
        });
    }

    function submitEditForm() {
        if (validateEditForm()) {
            const form = document.getElementById('editAccountForm');
            const formData = new FormData(form);

            fetch('edit_accounts.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('editSuccessMessage').textContent = data.message;
                    document.getElementById('editSuccessMessage').style.display = 'block';
                    setTimeout(() => {
                        closeModal('editAccountModal');
                        window.location.reload();
                    }, 1500);
                } else {
                    if (data.errors) {
                        data.errors.forEach(error => {
                            showError('editEmailError', error);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('editEmailError', 'An error occurred. Please try again.');
            });
        }
    }

    // Real-time validation
    document.getElementById('addAccountForm').addEventListener('input', function(e) {
        const target = e.target;
        const errorId = target.id + 'Error';
        
        if (target.value.trim()) {
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }
    });

    document.getElementById('editAccountForm').addEventListener('input', function(e) {
        const target = e.target;
        const errorId = 'edit' + target.id.charAt(0).toUpperCase() + target.id.slice(1) + 'Error';
        
        if (target.value.trim()) {
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const deleteButtons = document.querySelectorAll(".delete-btn");
        const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
        const userName = document.getElementById("userName");

        deleteButtons.forEach(button => {
            button.addEventListener("click", function() {
                const userId = this.getAttribute("data-user-id");
                const name = this.getAttribute("data-user-name");

                userName.textContent = name;
                confirmDeleteBtn.href = "user.php?delete=" + userId;
            });
        });

        // Add CSS for animations
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
    // Function to open edit modal for new users
function openNewUserEditModal(userId) {
    // Fetch user data via AJAX
    fetch(`get_new_user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate edit form
                document.getElementById('edit_user_id').value = data.user.id;
                document.getElementById('edit_name').value = data.user.name;
                document.getElementById('edit_email').value = data.user.email;
                
                // Open modal
                openModal('editAccountModal');
            } else {
                alert('Error loading user data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading user data');
        });
}

// Update delete functionality to handle both user types
document.addEventListener("DOMContentLoaded", function() {
    const deleteButtons = document.querySelectorAll(".delete-btn, .delete-new-user-btn");
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
    const userName = document.getElementById("userName");

    deleteButtons.forEach(button => {
        button.addEventListener("click", function() {
            const userId = this.getAttribute("data-user-id");
            const name = this.getAttribute("data-user-name");
            const userType = this.getAttribute("data-user-type") || 'user';

            userName.textContent = name;
            
            // Set correct delete URL based on user type
            if (userType === 'new_user') {
                confirmDeleteBtn.href = "delete_new_user.php?id=" + userId;
            } else {
                confirmDeleteBtn.href = "user.php?delete=" + userId;
            }
        });
    });
});
// Table Filtering Functions
function showTable(tableType) {
    // Hide all tables
    document.querySelectorAll('.table-section').forEach(table => {
        table.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected table
    const selectedTable = document.getElementById(`${tableType}-table`);
    if (selectedTable) {
        selectedTable.classList.add('active');
    }
    
    // Add active class to clicked button
    const buttonId = `${tableType.replace('-', '')}Btn`;
    const selectedBtn = document.getElementById(buttonId);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }
    
    // Update delete button event listeners for the active table
    updateDeleteButtonListeners();
}
// Update delete button event listeners based on active table
function updateDeleteButtonListeners() {
    const activeTable = document.querySelector('.table-section.active');
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
    const userName = document.getElementById("userName");
    
    // Clear existing listeners
    const oldDeleteButtons = document.querySelectorAll(".delete-btn, .delete-new-user-btn");
    oldDeleteButtons.forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    
    // Get all delete buttons from active table
    const activeDeleteButtons = activeTable.querySelectorAll(".delete-btn, .delete-new-user-btn");
    
    activeDeleteButtons.forEach(button => {
        button.addEventListener("click", function() {
            const userId = this.getAttribute("data-user-id");
            const name = this.getAttribute("data-user-name");
            const userType = this.getAttribute("data-user-type") || 'system_user';
            
            userName.textContent = name;
            
            // Set correct delete URL based on user type
            if (userType === 'new_user') {
                confirmDeleteBtn.href = "delete_new_user.php?id=" + userId;
            } else {
                confirmDeleteBtn.href = "user.php?delete=" + userId;
            }
        });
    });
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function() {
    // Set default active table (System Users)
    showTable('system-users');
    
    // Add CSS animation styles
    const style = document.createElement('style');
    style.textContent = `
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
});
// Search functionality
function searchUsers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const activeTable = document.querySelector('.table-section.active');
    const rows = activeTable.querySelectorAll('tbody tr');
    
    let foundCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            foundCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update badge count
    const badge = activeTable.querySelector('.badge');
    if (badge) {
        badge.textContent = `Found: ${foundCount}`;
    }
}

// Clear search when switching tables
function showTable(tableType) {
    // Hide all tables
    document.querySelectorAll('.table-section').forEach(table => {
        table.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected table
    const selectedTable = document.getElementById(`${tableType}-table`);
    if (selectedTable) {
        selectedTable.classList.add('active');
        
        // Reset search and show all rows
        document.getElementById('searchInput').value = '';
        const rows = selectedTable.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
        
        // Reset badge to total count
        const badge = selectedTable.querySelector('.badge');
        if (badge) {
            const totalCount = rows.length - 1; // Subtract 1 for the "no users" row
            if (totalCount > 0) {
                badge.textContent = `Total: ${totalCount}`;
            }
        }
    }
    
    // Add active class to clicked button
    const buttonId = `${tableType.replace('-', '')}Btn`;
    const selectedBtn = document.getElementById(buttonId);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }
    
    // Update delete button event listeners for the active table
    updateDeleteButtonListeners();
}
</script>

</body>
</html>