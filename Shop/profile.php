<?php
require 'dbconn.php';
require 'auth.php';

// ✅ Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Enforce login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ✅ Fetch user info from new_users table
$sql = "SELECT id, name, email, phone, street, barangay, city, province, zip_code, country, user_type, created_at FROM new_users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle missing user record
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// ✅ User display data
$user_name = $user['name'];
$first_letter = strtoupper(substr($user_name, 0, 1));
$user_type = $user['user_type'];

// ✅ Avatar handling
$avatar_path = "assets/images/avatars/{$user_id}.png";
$default_avatar = "assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);

// ✅ Calculate profile completion percentage
$profile_fields = ['name', 'email', 'phone', 'street', 'barangay', 'city', 'province', 'zip_code', 'country'];
$filled_fields = 0;
foreach ($profile_fields as $field) {
    if (!empty($user[$field])) {
        $filled_fields++;
    }
}
$completion_percentage = round(($filled_fields / count($profile_fields)) * 100);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ChronoVerse</title>
   <link rel="stylesheet" href="../css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #0a0a2a;
            color: white;
            min-height: 100vh;
        }
        
        /* Navigation - Match shop.php */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0, 49, 156, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            border-bottom: 1px solid rgba(74, 158, 255, 0.2);
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(45deg, #e6f2ffff, #f3fdffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        nav a:hover,
        nav a.active {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
        }
        
        /* User Dropdown - Match shop.php */
        .user-dropdown {
            position: relative;
            margin-left: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 25px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(74, 158, 255, 0.2);
        }

        .user-info:hover {
            background: rgba(74, 158, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 158, 255, 0.2);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 16px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.3);
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
            top: calc(100% + 10px);
            background: rgba(10, 10, 42, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            margin-top: 5px;
            min-width: 200px;
            z-index: 1001;
            overflow: hidden;
            border: 1px solid rgba(74, 158, 255, 0.3);
            animation: slideIn 0.3s ease;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #a0c8ff;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            border-bottom: 1px solid rgba(74, 158, 255, 0.1);
        }

        .dropdown-menu a:last-child {
            border-bottom: none;
        }

        .dropdown-menu a:hover {
            background: rgba(74, 158, 255, 0.2);
            color: white;
        }

        .dropdown-menu i {
            width: 20px;
            text-align: center;
            color: #4a9eff;
        }

        /* Profile Header */
        .profile-header-section {
            background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.22)), 
                        url('../image/hero-bg.jpg') center/cover no-repeat;
            padding: 140px 40px 80px;
            text-align: center;
            margin-top: 80px;
        }
        
        .profile-header-section h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .profile-header-section p {
            color: #a0c8ff;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        /* Profile Container */
        .profile-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        /* Profile Avatar Section */
        .profile-avatar-section {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .profile-avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(45deg, #1a1a3a, #2a2a4a);
            border: 5px solid rgba(74, 158, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            color: #4a9eff;
            margin: 0 auto;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(74, 158, 255, 0.3);
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        
        .profile-avatar.has-image img {
            display: block;
        }
        
        .profile-avatar.has-image .avatar-initial {
            display: none;
        }
        
        .avatar-edit-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid #0a0a2a;
        }
        
        .avatar-edit-btn:hover {
            transform: scale(1.1);
            background: linear-gradient(45deg, #0066ff, #4a9eff);
        }
        
        .profile-name {
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .profile-email {
            color: #a0c8ff;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(45deg, #00ccff, #4a9eff);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(74, 158, 255, 0.3);
        }
        
        /* Progress Bar */
        .completion-bar {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }
        
        .completion-fill {
            height: 100%;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .completion-text {
            font-size: 0.9rem;
            color: #a0c8ff;
            margin-top: 5px;
        }
        
        /* Profile Content Grid */
        .profile-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .profile-section {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            padding: 25px;
            border-left: 4px solid #4a9eff;
        }
        
        .profile-section h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #4a9eff;
            font-size: 1.5rem;
        }
        
        .profile-section h3 i {
            font-size: 1.2rem;
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #a0c8ff;
        }
        
        .info-value {
            color: white;
            text-align: right;
        }
        
        .info-value.empty {
            color: #666;
            font-style: italic;
        }
        
        .status-active {
            color: #00ff88;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-active::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #00ff88;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Action Buttons */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 14px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(74, 158, 255, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #00cc88, #00ffaa);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 204, 136, 0.4);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 3000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: linear-gradient(135deg, #1a1a3a, #2a2a4a);
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, #1a1a3a, #2a2a4a);
            z-index: 1;
        }
        
        .modal-header h3 {
            font-size: 1.8rem;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: #a0c8ff;
            font-size: 2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
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
            color: #a0c8ff;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4a9eff;
            box-shadow: 0 0 0 2px rgba(74, 158, 255, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        select.form-control option {
            background: #1a1a3a;
            color: white;
        }
        
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 3px solid rgba(74, 158, 255, 0.3);
            background: linear-gradient(45deg, #1a1a3a, #2a2a4a);
        }
        
        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .file-input {
            text-align: center;
            padding: 20px;
            border: 2px dashed rgba(74, 158, 255, 0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .file-input:hover {
            border-color: #4a9eff;
            background: rgba(74, 158, 255, 0.1);
        }
        
        .file-input input[type="file"] {
            display: none;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            position: sticky;
            bottom: 0;
            background: linear-gradient(135deg, #1a1a3a, #2a2a4a);
            padding: 15px 0 0;
        }
        
        .modal-actions .btn {
            flex: 1;
        }
        
        /* Alert Messages */
        .alert {
            position: fixed;
            top: 100px;
            right: 20px;
            max-width: 300px;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 2000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                gap: 10px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .user-dropdown {
                margin-left: 0;
                margin-top: 10px;
            }
            
            .profile-header-section {
                padding: 120px 20px 60px;
            }
            
            .profile-header-section h1 {
                font-size: 2.5rem;
            }
            
            .profile-container {
                padding: 0 15px;
            }
            
            .profile-card {
                padding: 25px;
            }
            
            .profile-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .modal-content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation - Match shop.php -->
    <header>
        <div class="logo">
            <img src="../image/logo.png" alt="ChronoVerse Logo">
            ChronoVerse
        </div>
        
        <nav>
            <ul>
                <li><a href="../shop.php">SHOP</a></li>
                <li><a href="profile.php" class="active">PROFILE</a></li>
            </ul>
        </nav>
        
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
                <a href="../shop.php">
                    <i class="fa-solid fa-store"></i><span>Shop</span>
                </a>
                <a href="../logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Profile Header -->
    <div class="profile-header-section">
        <h1>My Profile</h1>
        <p>Manage your account settings and personal information</p>
    </div>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_messages'])): ?>
        <?php foreach ($_SESSION['error_messages'] as $error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['error_messages']); ?>
    <?php endif; ?>

    <!-- Profile Container -->
    <div class="profile-container">
        <div class="profile-card">
            <!-- Profile Avatar Section -->
            <div class="profile-avatar-section">
                <div class="profile-avatar-container">
                    <div class="profile-avatar <?php echo $has_custom_avatar ? 'has-image' : ''; ?>" onclick="openModal('avatarModal')">
                        <?php if ($has_custom_avatar): ?>
                            <img src="<?php echo $avatar_path; ?>?<?php echo time(); ?>" alt="Profile Photo" id="currentAvatar">
                        <?php endif; ?>
                        <div class="avatar-initial"><?php echo $first_letter; ?></div>
                    </div>
                    <div class="avatar-edit-btn" onclick="openModal('avatarModal')">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="role-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    <?php echo strtoupper($user['user_type']); ?> ACCOUNT
                </div>
                
                <!-- Profile Completion Bar -->
                <div class="completion-bar">
                    <div class="completion-fill" style="width: <?php echo $completion_percentage; ?>%;"></div>
                </div>
                <p class="completion-text">Profile Completion: <?php echo $completion_percentage; ?>%</p>
            </div>

            <!-- Profile Information -->
            <div class="profile-content">
                <!-- Personal Information -->
                <div class="profile-section">
                    <h3><i class="fa-solid fa-user-circle"></i> Personal Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <span class="info-value <?php echo empty($user['phone']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not set'; ?>
                            </span>
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

                <!-- Shipping Information -->
                <div class="profile-section">
                    <h3><i class="fa-solid fa-truck"></i> Shipping Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Street</span>
                            <span class="info-value <?php echo empty($user['street']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['street']) ? htmlspecialchars($user['street']) : 'Not set'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Barangay</span>
                            <span class="info-value <?php echo empty($user['barangay']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['barangay']) ? htmlspecialchars($user['barangay']) : 'Not set'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">City</span>
                            <span class="info-value <?php echo empty($user['city']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['city']) ? htmlspecialchars($user['city']) : 'Not set'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Province</span>
                            <span class="info-value <?php echo empty($user['province']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['province']) ? htmlspecialchars($user['province']) : 'Not set'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">ZIP Code</span>
                            <span class="info-value <?php echo empty($user['zip_code']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['zip_code']) ? htmlspecialchars($user['zip_code']) : 'Not set'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Country</span>
                            <span class="info-value <?php echo empty($user['country']) ? 'empty' : ''; ?>">
                                <?php echo !empty($user['country']) ? htmlspecialchars($user['country']) : 'Not set'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Account Details -->
                <div class="profile-section">
                    <h3><i class="fa-solid fa-chart-line"></i> Account Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Member Since</span>
                            <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Account Type</span>
                            <span class="info-value"><?php echo ucfirst($user['user_type']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Login</span>
                            <span class="info-value">Recently</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Profile Complete</span>
                            <span class="info-value"><?php echo $completion_percentage; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="actions-grid">
                <button class="btn btn-primary" onclick="openModal('editProfileModal')">
                    <i class="fa-solid fa-user-pen"></i> Edit Profile & Shipping
                </button>
                <button class="btn btn-secondary" onclick="openModal('changePasswordModal')">
                    <i class="fa-solid fa-lock"></i> Change Password
                </button>
                <button class="btn btn-success" onclick="window.location.href='../shop.php'">
                    <i class="fa-solid fa-store"></i> Back to Shop
                </button>
            </div>
        </div>
    </div>

    <!-- Avatar Upload Modal -->
    <div class="modal" id="avatarModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-camera"></i> Update Profile Picture</h3>
                <button class="close-modal" onclick="closeModal('avatarModal')">×</button>
            </div>
            <div class="modal-body">
                <form id="avatarForm" method="POST" action="upload_avatar.php" enctype="multipart/form-data">
                    <div class="avatar-preview">
                        <?php if ($has_custom_avatar): ?>
                            <img src="<?php echo $avatar_path; ?>?<?php echo time(); ?>" alt="Current Avatar" id="avatarPreview">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#4a9eff;font-size:48px;font-weight:bold;" id="avatarInitial">
                                <?php echo $first_letter; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="file-input" onclick="document.getElementById('avatarFile').click()">
                        <i class="fa-solid fa-cloud-upload-alt" style="font-size: 2rem; margin-bottom: 10px; color: #4a9eff;"></i>
                        <div>Click to upload new photo</div>
                        <small style="color: #a0c8ff;">JPG, PNG, GIF (Max 2MB)</small>
                        <input type="file" name="avatar" id="avatarFile" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                    </div>
                    
                    <div class="modal-actions">
                        <?php if ($has_custom_avatar): ?>
                        <button type="button" class="btn btn-secondary" onclick="removeAvatar()">
                            <i class="fa-solid fa-trash"></i> Remove Photo
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-primary" onclick="closeModal('avatarModal')">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save"></i> Save Photo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Profile & Shipping Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-user-pen"></i> Edit Profile & Shipping Info</h3>
                <button class="close-modal" onclick="closeModal('editProfileModal')">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="update_profile.php">
                    <h4 style="color: #4a9eff; margin-bottom: 15px;">Personal Information</h4>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="form-control" placeholder="+63 912 345 6789">
                    </div>
                    
                    <hr style="border-color: rgba(255,255,255,0.1); margin: 25px 0;">
                    
                    <h4 style="color: #4a9eff; margin-bottom: 15px;">Shipping Information</h4>
                    <p style="color: #a0c8ff; font-size: 0.9rem; margin-bottom: 20px;">
                        <i class="fa-solid fa-info-circle"></i> This information will be used to auto-fill your checkout form.
                    </p>
                    
                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" name="street" value="<?php echo htmlspecialchars($user['street'] ?? ''); ?>" class="form-control" placeholder="123 Main Street, Building #">
                    </div>
                    
                    <div class="form-group">
                        <label>Barangay</label>
                        <input type="text" name="barangay" value="<?php echo htmlspecialchars($user['barangay'] ?? ''); ?>" class="form-control" placeholder="Poblacion">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" class="form-control" placeholder="Cebu City">
                        </div>
                        <div class="form-group">
                            <label>Province</label>
                            <input type="text" name="province" value="<?php echo htmlspecialchars($user['province'] ?? ''); ?>" class="form-control" placeholder="Cebu">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>ZIP Code</label>
                            <input type="text" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>" class="form-control" placeholder="6000">
                        </div>
                        <div class="form-group">
                            <label>Country</label>
                            <select name="country" class="form-control">
                                <option value="">Select Country</option>
                                <option value="PH" <?php echo ($user['country'] ?? '') === 'PH' ? 'selected' : ''; ?>>Philippines</option>
                                <option value="US" <?php echo ($user['country'] ?? '') === 'US' ? 'selected' : ''; ?>>United States</option>
                                <option value="UK" <?php echo ($user['country'] ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="CA" <?php echo ($user['country'] ?? '') === 'CA' ? 'selected' : ''; ?>>Canada</option>
                                <option value="AU" <?php echo ($user['country'] ?? '') === 'AU' ? 'selected' : ''; ?>>Australia</option>
                                <option value="JP" <?php echo ($user['country'] ?? '') === 'JP' ? 'selected' : ''; ?>>Japan</option>
                                <option value="SG" <?php echo ($user['country'] ?? '') === 'SG' ? 'selected' : ''; ?>>Singapore</option>
                                <option value="other" <?php echo ($user['country'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-primary" onclick="closeModal('editProfileModal')">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-floppy-disk"></i> Save All Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal" id="changePasswordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-lock"></i> Change Password</h3>
                <button class="close-modal" onclick="closeModal('changePasswordModal')">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="change_password.php">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-primary" onclick="closeModal('changePasswordModal')">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-key"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Toggle dropdown
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu');
        if (menu.style.display === 'block' || menu.style.display === '') {
            menu.style.display = 'none';
        } else {
            menu.style.display = 'block';
            
            // Close when clicking outside
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!menu.contains(e.target) && !e.target.closest('.user-info')) {
                        menu.style.display = 'none';
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 100);
        }
    }

    // Modal functions
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Avatar preview
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.querySelector('.avatar-preview');
                const existingImg = document.getElementById('avatarPreview');
                const existingInitial = document.getElementById('avatarInitial');
                
                if (existingImg) {
                    existingImg.src = e.target.result;
                } else {
                    const img = document.createElement('img');
                    img.id = 'avatarPreview';
                    img.src = e.target.result;
                    img.alt = 'Avatar Preview';
                    previewContainer.innerHTML = '';
                    previewContainer.appendChild(img);
                }
                
                if (existingInitial) {
                    existingInitial.style.display = 'none';
                }
            }
            reader.readAsDataURL(file);
        }
    }

    // Remove avatar
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

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    closeModal(modal.id);
                }
            });
        }
    });

    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'all 0.3s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(100%)';
            setTimeout(() => el.remove(), 300);
        });
    }, 5000);
    </script>
</body>
</html>