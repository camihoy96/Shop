<?php
require 'dbconn.php';
require '../admin/auth.php';
$user_name = $_SESSION['user_name'];
$settings_updated = false;
$error_message = '';

// Load settings from database
$sql = "SELECT * FROM site_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $current_settings = $result->fetch_assoc();
} else {
    $current_settings = [
        'site_title' => 'St4nger Dev',
        'subtitle' => 'Development Services',
        'site_description' => '',
        'subdescription' => '',
        'admin_email' => 'hacknet395@gmail.com',
        'address' => 'Cadawinonan, Dumaguete City, Negros Oriental',
        'phone' => '09056152262',
        'hero_image' => 'image/background.avif',
        'timezone' => 'UTC',
        'maintenance_mode' => 0,
        'user_registration' => 1,
        'email_notifications' => 1,
        'google_analytics' => 1,
        'social_sharing' => 1
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = trim($_POST['site_title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $site_description = trim($_POST['site_description'] ?? '');
    $subdescription = trim($_POST['subdescription'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $timezone = $_POST['timezone'] ?? 'UTC';
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    $user_registration = isset($_POST['user_registration']) ? 1 : 0;
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $google_analytics = isset($_POST['google_analytics']) ? 1 : 0;
    $social_sharing = isset($_POST['social_sharing']) ? 1 : 0;

    // Handle hero image upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = time() . '_' . basename($_FILES['hero_image']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile)) {
            $hero_image = $targetFile;
        } else {
            $error_message = 'Failed to upload hero image.';
            $hero_image = $current_settings['hero_image'] ?? 'image/background.avif';
        }
    } else {
        $hero_image = $current_settings['hero_image'] ?? 'image/background.avif';
    }

    // Validation
    if (empty($site_title) || empty($admin_email)) {
        $error_message = 'Site title and admin email are required.';
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $check = $conn->query("SELECT id FROM site_settings LIMIT 1");

        if ($check && $check->num_rows > 0) {
            // ✅ UPDATE existing settings
            $sql = "UPDATE site_settings SET 
                site_title = ?, 
                subtitle = ?, 
                site_description = ?, 
                subdescription = ?, 
                admin_email = ?, 
                address = ?, 
                phone = ?, 
                hero_image = ?, 
                timezone = ?, 
                maintenance_mode = ?, 
                user_registration = ?, 
                email_notifications = ?, 
                google_analytics = ?, 
                social_sharing = ?
                WHERE id = 1";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssiiiii",
                $site_title,
                $subtitle,
                $site_description,
                $subdescription,
                $admin_email,
                $address,
                $phone,
                $hero_image,
                $timezone,
                $maintenance_mode,
                $user_registration,
                $email_notifications,
                $google_analytics,
                $social_sharing
            );
        } else {
            // ✅ INSERT new settings
            $sql = "INSERT INTO site_settings 
                (site_title, subtitle, site_description, subdescription, admin_email, address, phone, hero_image, timezone, maintenance_mode, user_registration, email_notifications, google_analytics, social_sharing)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssiiiiii",
                $site_title,
                $subtitle,
                $site_description,
                $subdescription,
                $admin_email,
                $address,
                $phone,
                $hero_image,
                $timezone,
                $maintenance_mode,
                $user_registration,
                $email_notifications,
                $google_analytics,
                $social_sharing
            );
        }

        if ($stmt->execute()) {
            $settings_updated = true;
            $result = $conn->query("SELECT * FROM site_settings LIMIT 1");
            $current_settings = $result->fetch_assoc();
        } else {
            $error_message = 'Database error: ' . $conn->error;
        }

        $stmt->close();
    }
}
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | St4nger Dev</title>
    <<link rel="stylesheet" href="../css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
    font-size: 20px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
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
/* Actual logo (can scale larger visually) */
.sidebar-header .sidebar-logo {
    width: 70px;       /* bigger visual size */
    height: 70px;
    border-radius: 12px;
    object-fit: cover;
    transform: scale(1.3); /* visually enlarge */
    transition: transform 0.3s ease;
}

.sidebar-header .sidebar-logo:hover {
    transform: scale(1.4); /* optional hover grow */
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            background: var(--light);
            padding: 8px 16px;
            border-radius: 30px;
        }

        .user-info i {
            color: var(--primary);
            font-size: 20px;
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
        /* Settings Content */
        .settings-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
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

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: var(--transition);
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .form-check:hover {
            background: #f8f9fa;
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .form-check label {
            margin-bottom: 0;
            font-weight: 500;
            cursor: pointer;
        }

        .form-check .description {
            font-size: 13px;
            color: var(--gray);
            margin-top: 4px;
            display: block;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            margin-right: 10px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--primary);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .setting-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .setting-item h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .setting-item p {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 15px;
        }

        /* Buttons */
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
        }

        .btn-success:hover {
            background: #3ab0d9;
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #c5303a;
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

        /* Alerts */
        .alert {
            padding: 10px 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.15);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .alert-error {
            background: rgba(230, 57, 70, 0.15);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        /* Tabs */
        .settings-tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            border-bottom: 2px solid transparent;
            transition: var(--transition);
        }

        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab:hover {
            color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Danger Zone */
        .danger-zone {
            border: 2px solid var(--danger);
            border-radius: 12px;
            padding: 25px;
            background: rgba(230, 57, 70, 0.05);
        }

        .danger-zone .card-header {
            border-bottom-color: var(--danger);
        }

        .danger-zone .card-header h3 {
            color: var(--danger);
        }

        .danger-zone .card-header i {
            color: var(--danger);
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
            
            .logout-btn span {
                display: none;
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
            
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .settings-tabs {
                flex-direction: column;
            }
            
            .tab {
                text-align: left;
                border-bottom: 1px solid #eee;
                border-left: 2px solid transparent;
            }
            
            .tab.active {
                border-left-color: var(--primary);
                border-bottom-color: #eee;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 0;
            }
            
            .main {
                margin-left: 0;
            }
        }

          .user-dropdown {
    position: relative;
    display: inline-block;
    font-family: 'Poppins', sans-serif;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #333;
    font-weight: 500;
    transition: color 0.3s ease;
}

.user-info:hover {
    color: #007bff;
}

.user-info i.fa-caret-down {
    font-size: 0.9rem;
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    margin-top: 8px;
    min-width: 160px;
    z-index: 1000;
    overflow: hidden;
}

/* Styled Logout Button */
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
button:disabled {
    background-color: #ccc !important;
    color: #666 !important;
    border-color: #bbb !important;
    cursor: not-allowed;
    opacity: 0.6;
}   
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px 25px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    animation: fadeIn 0.3s ease-in-out;
}
.close {
    float: right;
    font-size: 22px;
    cursor: pointer;
}
.close:hover {
    color: red;
}
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-10px);}
    to {opacity: 1; transform: translateY(0);}
}

.custom-modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.custom-modal-content {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    box-shadow: 0 4px 25px rgba(0,0,0,0.2);
}
.custom-modal-content h3 {
    color: #e74c3c;
    margin-bottom: 10px;
    font-weight: 700;
}
.confirm-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}
.confirm-buttons .btn {
    flex: 1;
    padding: 10px;
    border: none;
    cursor: pointer;
    border-radius: 6px;
    font-weight: 600;
}
.btn-secondary {
    background: #ccc;
}
.btn-danger {
    background: #e74c3c;
    color: white;
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
           <h2>
        <img src="../image/logo.png" alt="Logo" class="sidebar-logo">
        <span><?php echo htmlspecialchars($settings['site_title']); ?> Settings</span>
    </h2>
        </div>
        
        <div class="sidebar-nav">
            <a href="../admin/home.php"><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
             <a href="../admin/message.php"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a>
             <a href="../portfolio/port.php"><i class="fa-solid fa-briefcase"></i> <span>Portfolio</span></a>
            <a href="../analytic/analytic.php"><i class="fa-solid fa-chart-bar"></i> <span>Analytics</span></a>
            <a href="../settings/settings.php" class="active"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Settings</h1>
            <!-- User Info Dropdown -->
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
                    <a href="../admin/profile.php" class="logout-btn">
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

        <?php if ($settings_updated): ?>
    <div class="alert alert-success" id="settingsAlert">
        <i class="fa-solid fa-check-circle"></i>
        <span>Settings updated successfully!</span>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-error" id="errorAlert">
        <i class="fa-solid fa-exclamation-circle"></i>
        <span><?php echo htmlspecialchars($error_message); ?></span>
    </div>
<?php endif; ?>

        <div class="settings-content">
          <!-- General Settings -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-sliders"></i> General Settings</h3>
    </div>

    <form method="POST" id="settingsForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="site_title">Site Title</label>
            <input type="text" id="site_title" name="site_title"
                   value="<?php echo htmlspecialchars($current_settings['site_title']); ?>"
                   required placeholder="Enter your site title">
        </div>

        <div class="form-group">
            <label for="subtitle">Subtitle</label>
            <input type="text" id="subtitle" name="subtitle"
                   value="<?php echo htmlspecialchars($current_settings['subtitle'] ?? ''); ?>"
                   placeholder="Enter your website subtitle">
        </div>

        <div class="form-group">
            <label for="site_description">Site Description</label>
            <textarea id="site_description" name="site_description"
                      placeholder="Enter a short description of your website" rows="3"><?php 
                echo htmlspecialchars($current_settings['site_description'] ?? ''); 
            ?></textarea>
        </div>

        <div class="form-group">
            <label for="subdescription">Subdescription</label>
            <textarea id="subdescription" name="subdescription"
                      placeholder="Enter a secondary description or tagline" rows="3"><?php
                echo htmlspecialchars($current_settings['subdescription'] ?? '');
            ?></textarea>
        </div>

        <div class="form-group">
            <label for="hero_image">Hero Background Image</label>
            <?php if (!empty($current_settings['hero_image'])): ?>
                <div style="margin-bottom:10px;">
                    <img src="../<?php echo htmlspecialchars($current_settings['hero_image']); ?>" 
                         alt="Hero Image" style="max-width:200px; height:auto; border:1px solid #ccc; padding:5px;">
                </div>
            <?php endif; ?>
            <input type="file" id="hero_image" name="hero_image" accept="image/*">
        </div>

        <div class="form-group">
            <label for="admin_email">Admin Email</label>
            <input type="email" id="admin_email" name="admin_email"
                   value="<?php echo htmlspecialchars($current_settings['admin_email']); ?>"
                   required placeholder="admin@example.com">
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address"
                   value="<?php echo htmlspecialchars($current_settings['address'] ?? 'Cadawinonan, Dumaguete City, Negros Oriental'); ?>"
                   placeholder="Enter your business address">
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone"
                   value="<?php echo htmlspecialchars($current_settings['phone'] ?? '09056152262'); ?>"
                   placeholder="Enter your phone number">
        </div>

        <div class="form-group">
            <label for="timezone">Timezone</label>
            <select id="timezone" name="timezone">
                <option value="UTC" <?php echo $current_settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                <option value="America/New_York" <?php echo $current_settings['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time (ET)</option>
                <option value="America/Chicago" <?php echo $current_settings['timezone'] === 'America/Chicago' ? 'selected' : ''; ?>>Central Time (CT)</option>
                <option value="America/Denver" <?php echo $current_settings['timezone'] === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time (MT)</option>
                <option value="America/Los_Angeles" <?php echo $current_settings['timezone'] === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time (PT)</option>
                <option value="Europe/London" <?php echo $current_settings['timezone'] === 'Europe/London' ? 'selected' : ''; ?>>London</option>
            </select>
        </div>

        <button type="submit" class="btn">
            <i class="fa-solid fa-save"></i> Save Changes
        </button>
    </form>
</div>

      <!-- Feature Toggles -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-toggle-on"></i> Feature Management</h3>
    </div>

    <!-- Maintenance Mode -->
    <div class="form-check">
        <label class="toggle-switch">
            <input type="checkbox" name="maintenance_mode"
                <?php echo isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1 ? 'checked' : ''; ?>>
            <span class="toggle-slider"></span>
        </label>
        <div>
            <label for="maintenance_mode">Maintenance Mode</label>
            <span class="description">When enabled, the site will be unavailable to visitors and show a maintenance page.</span>
        </div>
    </div>

    <!-- Google Analytics -->
    <div class="form-check">
        <label class="toggle-switch">
            <input type="checkbox" name="google_analytics"
                <?php echo isset($current_settings['google_analytics']) && $current_settings['google_analytics'] == 1 ? 'checked' : ''; ?>>
            <span class="toggle-slider"></span>
        </label>
        <div>
            <label for="google_analytics">Google Analytics</label>
            <span class="description">Enable Google Analytics tracking for your website.</span>
        </div>
    </div>

    <!-- Social Sharing -->
    <div class="form-check">
        <label class="toggle-switch">
            <input type="checkbox" name="social_sharing"
                <?php echo isset($current_settings['social_sharing']) && $current_settings['social_sharing'] == 1 ? 'checked' : ''; ?>>
            <span class="toggle-slider"></span>
        </label>
        <div>
            <label for="social_sharing">Social Sharing</label>
            <span class="description">Allow visitors to share your content on social media platforms.</span>
        </div>
    </div>
</div>

       <!-- Quick Settings Grid -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
    </div>
    <div class="settings-grid">
        <!-- Clear Cache -->
        <div class="setting-item">
            <h4>Clear Cache</h4>
            <p>Remove temporary files and refresh system cache</p>
            <button class="btn btn-outline"
                id="clearCacheBtn"
                onclick="clearCache()"
                <?php echo (isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1) ? '' : 'disabled'; ?>>
                <i class="fa-solid fa-broom"></i> Clear Now
            </button>
        </div>

        <!-- Backup Database -->
        <div class="setting-item">
            <h4>Backup Database</h4>
            <p>Create a backup of your website database</p>
            <button class="btn btn-outline"
                id="backupDatabaseBtn"
                onclick="backupDatabase()"
                <?php echo (isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1) ? '' : 'disabled'; ?>>
                <i class="fa-solid fa-database"></i> Backup Now
            </button>
        </div>

        <!-- System Health -->
        <div class="setting-item">
            <h4>System Health</h4>
            <p>Check system status and performance</p>
            <button class="btn btn-outline"
                id="systemHealthBtn"
                onclick="checkSystemHealth()"
                <?php echo (isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1) ? '' : 'disabled'; ?>>
                <i class="fa-solid fa-heart-pulse"></i> Check Health
            </button>
        </div>

       <!-- Update Check -->
<div class="setting-item">
    <h4>Update Check</h4>
    <p>Check for system and plugin updates</p>
    <button class="btn btn-outline"
        id="updateCheckBtn"
        onclick="checkUpdates()"
        <?php echo (isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1) ? '' : 'disabled'; ?>>
        <i class="fa-solid fa-rotate"></i> Check Updates
    </button>
</div>
<!-- Clear Cache Modal -->
<div id="cacheModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close" onclick="closeModal('cacheModal')">&times;</span>
        <h3><i class="fa-solid fa-broom"></i> Clear Cache</h3>
        <div id="cacheResult" style="margin-top: 10px; font-size: 15px;">
            <p><i class="fa-solid fa-spinner fa-spin"></i> Clearing cache...</p>
        </div>
        <div style="text-align:right; margin-top:20px;">
            <button class="btn btn-outline" onclick="closeModal('cacheModal')">Close</button>
        </div>
    </div>
</div>

<!-- Backup Database Modal -->
<div id="backupModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close" onclick="closeModal('backupModal')">&times;</span>
        <h3><i class="fa-solid fa-database"></i> Backup Database</h3>
        <div id="backupResult" style="margin-top: 10px; font-size: 15px;">
            <p><i class="fa-solid fa-spinner fa-spin"></i> Creating database backup...</p>
        </div>
        <div style="text-align:right; margin-top:20px;">
            <button class="btn btn-outline" onclick="closeModal('backupModal')">Close</button>
        </div>
    </div>
</div>

<!-- System Health Modal -->
<div id="healthModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close" onclick="closeModal('healthModal')">&times;</span>
        <h3><i class="fa-solid fa-heart-pulse"></i> System Health</h3>
        <div id="healthResult" style="margin-top: 10px; font-size: 15px;">
            <p><i class="fa-solid fa-spinner fa-spin"></i> Checking system status...</p>
        </div>
        <div style="text-align:right; margin-top:20px;">
            <button class="btn btn-outline" onclick="closeModal('healthModal')">Close</button>
        </div>
    </div>
</div>

<!-- Update Check Modal -->
<div id="updateModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close" onclick="closeUpdateModal()">&times;</span>
        <h3><i class="fa-solid fa-rotate"></i> System Update Status</h3>
        <div id="updateResult" style="margin-top: 10px; font-size: 15px;">
            <p><i class="fa-solid fa-spinner fa-spin"></i> Checking for updates...</p>
        </div>
        <div style="text-align: right; margin-top: 20px;">
            <button class="btn btn-outline" onclick="closeUpdateModal()">Close</button>
        </div>
    </div>
</div>

    </div>
</div>
<!-- Confirmation Modal -->
<div id="confirmModal" class="modal" style="display:none;">
  <div class="modal-content" style="max-width: 420px;">
    <h3><i class="fa-solid fa-circle-question"></i> Confirm Action</h3>
    <p id="confirmMessage" style="margin: 15px 0; font-size: 15px;"></p>
    <div style="text-align:right;">
      <button class="btn btn-outline" onclick="closeModal('confirmModal')">Cancel</button>
      <button class="btn btn" id="confirmYesBtn">Proceed</button>
    </div>
  </div>
</div>


        <!-- Danger Zone -->
<div class="card danger-zone">
    <div class="card-header">
        <h3><i class="fa-solid fa-triangle-exclamation"></i> Danger Zone</h3>
    </div>

    <!-- Reset All Settings -->
    <div class="form-group">
        <h4 style="color: var(--danger); margin-bottom: 15px;">Reset All Settings</h4>
        <p style="margin-bottom: 15px; color: var(--gray);">
            This will reset all settings to their default values. This action will not delete users or content.
        </p>
        <button class="btn btn-danger" 
            id="resetBtn"
            onclick="confirmReset()"
            <?php echo (isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1) ? '' : 'disabled'; ?>>
            <i class="fa-solid fa-rotate-left"></i> Reset to Defaults
        </button>
    </div>

    <!-- Delete All Data -->
    <div class="form-group">
        <h4 style="color: var(--danger); margin-bottom: 15px;">Delete All Data</h4>
        <p style="margin-bottom: 15px; color: var(--gray);">
            Permanently delete all website data including users, content, and settings. This action cannot be undone.
        </p>
        <button class="btn btn-danger" 
            id="deleteBtn"
            onclick="confirmDeleteAll()"
            <?php echo (isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1) ? '' : 'disabled'; ?>>
            <i class="fa-solid fa-trash"></i> Delete Everything
        </button>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="customConfirm" class="custom-modal" style="display:none;">
    <div class="custom-modal-content">
        <h3 id="confirmTitle">⚠️ Warning</h3>
        <p id="confirmMessage"></p>
        <div class="confirm-buttons">
            <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
            <button class="btn btn-danger" id="confirmBtn">Proceed</button>
        </div>
    </div>
</div>


    <script>
        // Tab functionality
        function openTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab content and activate the tab
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }


// ===============================
// 🛠️ Maintenance Mode Toggle Control (Unified)
// ===============================
document.addEventListener('DOMContentLoaded', () => {
    const maintenanceToggle = document.querySelector('input[name="maintenance_mode"]');
    if (!maintenanceToggle) return; // Not on admin page

    // ✅ Include all dependent buttons (Quick Actions + Danger Zone)
    const controlledButtons = [
        document.getElementById('clearCacheBtn'),
        document.getElementById('backupDatabaseBtn'),
        document.getElementById('systemHealthBtn'),
        document.getElementById('updateCheckBtn'),
        document.getElementById('resetBtn'),
        document.getElementById('deleteBtn')
    ];

    // ✅ Function to visually enable/disable all buttons
    const setButtonState = (enabled) => {
        controlledButtons.forEach(btn => {
            if (!btn) return;
            btn.disabled = !enabled;
            btn.style.opacity = enabled ? '1' : '0.5';
            btn.style.cursor = enabled ? 'pointer' : 'not-allowed';
            btn.title = enabled
                ? 'Click to perform action'
                : 'Disabled while Maintenance Mode is off';
        });
    };

    // ✅ Apply initial button state on page load
    setButtonState(maintenanceToggle.checked);

    // ✅ Handle toggle instantly + save via AJAX
    maintenanceToggle.addEventListener('change', function () {
        const enabled = this.checked;
        setButtonState(enabled); // instant front-end response

        // 🔄 Update the database via AJAX
        fetch('save_settings.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'maintenance_mode=' + (enabled ? 1 : 0)
        })
        .then(res => res.text())
        .then(data => console.log('Maintenance mode updated:', data))
        .catch(err => console.error('Error updating maintenance mode:', err));
    });
});

      // ===============================
// ⚙️ Quick Actions Functionality
// ===============================

// Helper: Disable protection for toggled-off buttons
function isButtonDisabled(btnId) {
    const btn = document.getElementById(btnId);
    if (btn && btn.disabled) {
        alert('⚠️ This feature is currently disabled by the administrator.');
        return true;
    }
    return false;
}

function isButtonDisabled(btnId) {
    const btn = document.getElementById(btnId);
    return btn && btn.disabled;
}

// Generic close modal function
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// 🧹 Clear Cache
function clearCache() {
  if (isButtonDisabled('clearCacheBtn')) return;

  showConfirmation('Are you sure you want to clear the system cache?', () => {
    const modal = document.getElementById('cacheModal');
    const result = document.getElementById('cacheResult');
    const btn = document.getElementById('clearCacheBtn');

    modal.style.display = 'block';
    result.innerHTML = '<p><i class="fa-solid fa-spinner fa-spin"></i> Clearing cache...</p>';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Clearing...';

    fetch('actions/clear_cache.php')
      .then(res => res.text())
      .then(data => {
        result.innerHTML = `<p style="color:green;"><i class="fa-solid fa-circle-check"></i> Cache cleared successfully!</p>
                            <pre style="background:#f7f7f7; padding:8px; border-radius:5px;">${data}</pre>`;
      })
      .catch(err => {
        result.innerHTML = `<p style="color:red;"><i class="fa-solid fa-circle-xmark"></i> Error: ${err}</p>`;
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-broom"></i> Clear Now';
      });
  });
}


// 💾 Backup Database
function backupDatabase() {
  if (isButtonDisabled('backupDatabaseBtn')) return;

  showConfirmation('Create a database backup now?', () => {
    const modal = document.getElementById('backupModal');
    const result = document.getElementById('backupResult');
    const btn = document.getElementById('backupDatabaseBtn');

    modal.style.display = 'block';
    result.innerHTML = '<p><i class="fa-solid fa-spinner fa-spin"></i> Creating backup...</p>';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Backing up...';

    fetch('actions/backup_database.php')
      .then(res => res.text())
      .then(data => {
        if (data.includes('failed')) {
          result.innerHTML = `<p style="color:red;"><i class="fa-solid fa-circle-xmark"></i> Backup failed.</p>
                              <pre style="background:#fff3cd; padding:8px; border-radius:5px;">${data}</pre>`;
        } else {
          result.innerHTML = `<p style="color:green;"><i class="fa-solid fa-circle-check"></i> Backup completed successfully!</p>
                              <pre style="background:#f7f7f7; padding:8px; border-radius:5px;">${data}</pre>`;
        }
      })
      .catch(err => {
        result.innerHTML = `<p style="color:red;"><i class="fa-solid fa-circle-xmark"></i> Error: ${err}</p>`;
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-database"></i> Backup Now';
      });
  });
}


// ❤️ System Health
function checkSystemHealth() {
    if (isButtonDisabled('systemHealthBtn')) return;

    const modal = document.getElementById('healthModal');
    const result = document.getElementById('healthResult');
    const btn = document.getElementById('systemHealthBtn');

    modal.style.display = 'block';
    result.innerHTML = '<p><i class="fa-solid fa-spinner fa-spin"></i> Checking system health...</p>';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';

    fetch('actions/check_system_health.php')
        .then(res => res.text())
        .then(data => {
            result.innerHTML = `<p style="color:green;"><i class="fa-solid fa-heart-pulse"></i> System Health Report</p>
                                <pre style="background:#f7f7f7; padding:8px; border-radius:5px;">${data}</pre>`;
        })
        .catch(err => {
            result.innerHTML = `<p style="color:red;"><i class="fa-solid fa-circle-xmark"></i> Error: ${err}</p>`;
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-heart-pulse"></i> Check Health';
        });
}
function checkUpdates() {
    const button = document.getElementById("updateCheckBtn");
    const modal = document.getElementById("updateModal");
    const resultDiv = document.getElementById("updateResult");

    // Show modal immediately
    modal.style.display = "block";
    resultDiv.innerHTML = `
        <p><i class="fa-solid fa-spinner fa-spin"></i> Checking for updates...</p>
    `;

    // Disable button during check
    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';

    fetch('actions/check_updates.php')
        .then(response => response.text())
        .then(data => {
            // Normalize whitespace and newlines for safe display
            const formatted = data.replace(/\n/g, "<br>");

            if (data.toLowerCase().includes("up-to-date")) {
                resultDiv.innerHTML = `
                    <p style="color:green; font-weight:600;">
                        <i class="fa-solid fa-circle-check"></i> Your system is up-to-date.
                    </p>
                    <div style="background:#f7f7f7; padding:10px; border-radius:6px; white-space:pre-line;">
                        ${formatted}
                    </div>
                `;
            } else if (data.toLowerCase().includes("update available")) {
                resultDiv.innerHTML = `
                    <p style="color:orange; font-weight:600;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Update available!
                    </p>
                    <div style="background:#fff3cd; padding:10px; border-radius:6px; white-space:pre-line;">
                        ${formatted}
                    </div>
                `;
            } else if (data.toLowerCase().includes("contact")) {
                // For developer contact section
                resultDiv.innerHTML = `
                    <p style="color:#007bff; font-weight:600;">
                        <i class="fa-solid fa-user-gear"></i> Contact the developer for assistance.
                    </p>
                    <div style="background:#eaf3ff; padding:10px; border-radius:6px; white-space:pre-line;">
                        ${formatted}
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <p style="color:red;">
                        <i class="fa-solid fa-circle-xmark"></i> ${formatted}
                    </p>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <p style="color:red;">
                    <i class="fa-solid fa-circle-xmark"></i> Error checking updates: ${error}
                </p>
            `;
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-rotate"></i> Check Updates';
        });
}


function showConfirmation(message, onConfirm) {
  const modal = document.getElementById('confirmModal');
  const msg = document.getElementById('confirmMessage');
  const yesBtn = document.getElementById('confirmYesBtn');

  msg.innerHTML = message;
  modal.style.display = 'block';

  // Remove any previous event
  yesBtn.onclick = () => {
    modal.style.display = 'none';
    onConfirm();
  };
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

function closeUpdateModal() {
    document.getElementById("updateModal").style.display = "none";
}
     function showConfirm(message, callback) {
    const modal = document.getElementById('customConfirm');
    const msg = document.getElementById('confirmMessage');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    msg.textContent = message;
    modal.style.display = 'flex';

    confirmBtn.onclick = () => {
        modal.style.display = 'none';
        callback(true);
    };
    cancelBtn.onclick = () => {
        modal.style.display = 'none';
        callback(false);
    };
}

// ✅ Show custom confirmation modal
function showConfirm(message, callback) {
    const modal = document.getElementById('customConfirm');
    const messageEl = document.getElementById('confirmMessage');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    messageEl.textContent = message;
    modal.style.display = 'flex';

    confirmBtn.onclick = () => {
        modal.style.display = 'none';
        callback(true);
    };
    cancelBtn.onclick = () => {
        modal.style.display = 'none';
        callback(false);
    };
}

// ✅ Show Toast / Alert (customize this with your preferred library)
function showToast(message, type = 'info') {
    alert(message);
}

// ✅ Check Maintenance Mode before action
function isMaintenanceModeEnabled() {
    return <?php echo isset($current_settings['maintenance_mode']) && $current_settings['maintenance_mode'] == 1 ? 'true' : 'false'; ?>;
}

// 🔄 Reset Settings
function confirmReset() {
    if (!isMaintenanceModeEnabled()) {
        showToast('⚠️ Enable Maintenance Mode to reset settings.', 'warning');
        return;
    }

    showConfirm("⚠️ Reset all settings to default? This cannot be undone.", (confirmed) => {
        if (confirmed) {
            fetch('actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=reset_settings'
            })
            .then(res => res.json())
            .then(data => showToast(data.message, data.status === 'success' ? 'info' : 'error'))
            .catch(() => showToast('❌ Something went wrong while resetting.', 'error'));
        }
    });
}

// 🗑️ Delete All Data
function confirmDeleteAll() {
    if (!isMaintenanceModeEnabled()) {
        showToast('⚠️ Enable Maintenance Mode to delete all data.', 'warning');
        return;
    }

    showConfirm("⚠️ This will permanently delete ALL data including users and settings. Proceed?", (confirmed) => {
        if (confirmed) {
            fetch('actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete_all'
            })
            .then(res => res.json())
            .then(data => showToast(data.message, data.status === 'success' ? 'error' : 'info'))
            .catch(() => showToast('❌ Something went wrong while deleting.', 'error'));
        }
    });
}

// Optional: simple toast for success/error feedback
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.bottom = '30px';
    toast.style.right = '30px';
    toast.style.background = type === 'error' ? '#e74c3c' : '#3498db';
    toast.style.color = '#fff';
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 4px 15px rgba(0,0,0,0.2)';
    toast.style.zIndex = '10000';
    toast.style.fontWeight = '600';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

        // Notification system
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
                animation: slideIn 0.3s ease;
            `;
            
            const icon = type === 'success' ? 'fa-check-circle' : 
                        type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            notification.innerHTML = `
                <i class="fa-solid ${icon}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }

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

        // Form validation
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const siteTitle = document.getElementById('site_title').value.trim();
            const adminEmail = document.getElementById('admin_email').value.trim();
            
            if (!siteTitle) {
                e.preventDefault();
                showNotification('Site title is required.', 'error');
                return;
            }
            
            if (!adminEmail) {
                e.preventDefault();
                showNotification('Admin email is required.', 'error');
                return;
            }
            
            if (!isValidEmail(adminEmail)) {
                e.preventDefault();
                showNotification('Please enter a valid email address.', 'error');
                return;
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Initialize first tab as active
        document.addEventListener('DOMContentLoaded', function() {
            // If tabs exist, activate the first one
            const firstTab = document.querySelector('.tab');
            if (firstTab) {
                firstTab.classList.add('active');
            }
            
            const firstTabContent = document.querySelector('.tab-content');
            if (firstTabContent) {
                firstTabContent.classList.add('active');
            }
        });

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

// Hide success alert after 5 seconds
    const settingsAlert = document.getElementById('settingsAlert');
    if (settingsAlert) {
        setTimeout(() => {
            settingsAlert.style.display = 'none';
        }, 5000);
    }

    // Hide error alert after 5 seconds
    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        setTimeout(() => {
            errorAlert.style.display = 'none';
        }, 5000);
    }

    document.querySelector('input[name="maintenance_mode"]').addEventListener('change', function() {
    let mode = this.checked ? 1 : 0;

    fetch('save_settings.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'maintenance_mode=' + mode
    })
    .then(res => res.text())
    .then(data => console.log('Maintenance mode updated:', data))
    .catch(err => console.error('Error:', err));
});
// Google Analytics
document.querySelector('input[name="google_analytics"]').addEventListener('change', function() {
    let mode = this.checked ? 1 : 0;

    fetch('save_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'google_analytics=' + mode
    })
    .then(res => res.text())
    .then(data => console.log('Google Analytics updated:', data))
    .catch(err => console.error('Error:', err));
});

// Social Sharing
document.querySelector('input[name="social_sharing"]').addEventListener('change', function() {
    let mode = this.checked ? 1 : 0;

    fetch('save_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'social_sharing=' + mode
    })
    .then(res => res.text())
    .then(data => console.log('Social Sharing updated:', data))
    .catch(err => console.error('Error:', err));
});


    </script>
</body>
</html>