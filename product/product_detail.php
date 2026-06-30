<?php
require 'dbconn.php';
require '../admin/auth.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: product.php');
    exit;
}

$product_id = intval($_GET['id']);
$user_name = $_SESSION['user_name'];
$first_letter = strtoupper(substr($user_name, 0, 1));
$user_id = intval($_SESSION['user_id']);

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get user avatar
$avatar_path = "../admin/assets/images/avatars/{$user_id}.png";
$has_custom_avatar = file_exists($avatar_path);

// Fetch product details
$product_sql = "SELECT p.*, 
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_featured = 1 LIMIT 1) as featured_image,
                (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as total_images
                FROM products p 
                WHERE p.id = ? AND p.name NOT LIKE 'New category product%'";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    header('Location: product.php');
    exit;
}

// Fetch all product images
$images_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_featured DESC, sort_order ASC";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param("i", $product_id);
$images_stmt->execute();
$images_result = $images_stmt->get_result();

// Fetch product features
$features_sql = "SELECT * FROM product_features WHERE product_id = ? ORDER BY sort_order ASC";
$features_stmt = $conn->prepare($features_sql);
$features_stmt->bind_param("i", $product_id);
$features_stmt->execute();
$features_result = $features_stmt->get_result();

// Get category name from categories table if available
$cat_name = ucfirst(str_replace('-', ' ', $product['category']));
$table_check = $conn->query("SHOW TABLES LIKE 'product_categories'");
if ($table_check->num_rows > 0) {
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

// Stock status
$stock = isset($product['stock']) ? $product['stock'] : 0;
if($stock > 10) {
    $stock_class = 'status-published';
    $stock_text = 'In Stock';
    $stock_color = 'var(--success)';
} elseif($stock > 0) {
    $stock_class = 'status-draft';
    $stock_text = 'Low Stock';
    $stock_color = 'var(--warning)';
} else {
    $stock_class = 'status-draft';
    $stock_text = 'Out of Stock';
    $stock_color = 'var(--danger)';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | ChronoVerse Admin</title>
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
        }

        /* Sidebar - same as product.php */
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

        .sidebar-logo-container {
            width: 50px;
            height: 50px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
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
            font-size: 28px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* User Dropdown - same as product.php */
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

        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Product Detail Container */
        .product-detail-container {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Product Header */
        .product-header {
            padding: 30px;
            border-bottom: 1px solid var(--light-gray);
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .product-header h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .product-meta-info {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .product-category {
            background: var(--primary);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .product-id {
            color: var(--gray);
            font-size: 14px;
        }

        .product-featured-tag {
            background: var(--warning);
            color: #212529;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Product Content */
        .product-content-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px;
        }

        @media (max-width: 992px) {
            .product-content-detail {
                grid-template-columns: 1fr;
            }
        }

        /* Images Section */
        .product-images {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-placeholder {
            font-size: 64px;
            color: var(--gray);
        }

        .thumbnail-images {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .thumbnail {
            width: 100%;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: var(--transition);
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thumbnail:hover, .thumbnail.active {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-placeholder {
            font-size: 24px;
            color: var(--gray);
        }

        /* Info Section */
        .product-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .product-description-detail {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .product-description-detail h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .product-description-detail p {
            color: var(--gray);
            line-height: 1.6;
        }

        /* Price Section */
        .price-section {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-amount {
            font-size: 32px;
            font-weight: 700;
            color: var(--secondary);
        }

        .stock-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .stock-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
        }

        .stock-count {
            font-size: 14px;
            color: var(--gray);
        }

        /* Features Section */
        .features-section {
            background: var(--light);
            padding: 20px;
            border-radius: 8px;
        }

        .features-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .features-list-detail {
            list-style: none;
            padding-left: 0;
        }

        .features-list-detail li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .features-list-detail li:last-child {
            border-bottom: none;
        }

        .feature-icon {
            color: var(--secondary);
            font-size: 14px;
            margin-top: 3px;
            flex-shrink: 0;
        }

        /* Additional Info */
        .additional-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .info-item {
            background: var(--light);
            padding: 15px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-size: 12px;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 500;
            color: var(--dark);
        }

        /* Product Actions */
        .product-actions-detail {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }

        .action-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Montserrat', sans-serif;
            text-decoration: none;
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

        .action-btn.back {
            background: rgba(108, 117, 125, 0.1);
            color: var(--gray);
            border: 1px solid rgba(108, 117, 125, 0.2);
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

        .action-btn.back:hover {
            background: var(--gray);
            color: var(--white);
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
            
            .additional-info {
                grid-template-columns: 1fr;
            }
            
            .product-header h2 {
                font-size: 24px;
            }
            
            .product-actions-detail {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
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
            
            .product-content-detail {
                padding: 20px;
            }
            
            .main-image {
                height: 300px;
            }
            
            .thumbnail-images {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .product-detail-container {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <div class="sidebar-logo-container">
                    <img src="../image/logo.png" alt="ChronoVerse Logo" class="sidebar-logo">
                </div>
                <span>ChronoVerse Product</span>
            </h2>
        </div>
        
        <div class="sidebar-nav">
            <a href="../admin/home.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
            <a href="../admin/message.php"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a>
            <a href="../product/product.php" class="active"><i class="fa-solid fa-briefcase"></i> <span>Product</span></a>
            <a href="../analytic/analytic.php"><i class="fa-solid fa-chart-bar"></i> <span>Analytics</span></a>
            <a href="../settings/settings.php"><i class="fa-solid fa-gear"></i> <span>Settings</span></a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1><i class="fa-solid fa-eye" style="color: var(--primary);"></i> Product Details</h1>
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

        <a href="product.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back to Products
        </a>

        <div class="product-detail-container">
            <!-- Product Header -->
            <div class="product-header">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <div class="product-meta-info">
                    <span class="product-category">
                        <i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($cat_name); ?>
                    </span>
                    <span class="product-id">
                        <i class="fa-solid fa-hashtag"></i> ID: #<?php echo $product['id']; ?>
                    </span>
                    <?php if ($product['featured']): ?>
                        <span class="product-featured-tag">
                            <i class="fa-solid fa-star"></i> Featured Product
                        </span>
                    <?php endif; ?>
                    <span class="stock-status" style="background: <?php echo $stock_color; ?>20; color: <?php echo $stock_color; ?>;">
                        <i class="fa-solid fa-cube"></i> <?php echo $stock_text; ?>
                        <?php if($stock > 0): ?>
                            (<?php echo $stock; ?> units available)
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Product Content -->
            <div class="product-content-detail">
                <!-- Images Section -->
                <div class="product-images">
                    <div class="main-image" id="mainImage">
                        <?php if (!empty($product['featured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" id="currentImage">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($images_result->num_rows > 0): ?>
                        <div class="thumbnail-images">
                            <?php 
                            $first_image = true;
                            while ($image = $images_result->fetch_assoc()): 
                            ?>
                                <div class="thumbnail <?php echo $first_image ? 'active' : ''; ?>" 
                                     onclick="changeImage('<?php echo htmlspecialchars($image['image_path']); ?>', this)">
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         alt="Product image <?php echo $image['id']; ?>">
                                </div>
                                <?php $first_image = false; ?>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info Section -->
                <div class="product-info">
                    <!-- Description -->
                    <div class="product-description-detail">
                        <h3><i class="fa-solid fa-file-lines"></i> Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <!-- Price -->
                    <div class="price-section">
                        <div>
                            <div style="font-size: 14px; color: var(--gray); margin-bottom: 5px;">Price</div>
                            <div class="price-amount">$<?php echo number_format($product['price'], 2); ?></div>
                        </div>
                        <div class="stock-info">
                            <span class="stock-status" style="background: <?php echo $stock_color; ?>20; color: <?php echo $stock_color; ?>;">
                                <i class="fa-solid fa-cube"></i> <?php echo $stock_text; ?>
                            </span>
                            <?php if($stock > 0): ?>
                                <span class="stock-count"><?php echo $stock; ?> units in stock</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Features -->
                    <?php if ($features_result->num_rows > 0): ?>
                        <div class="features-section">
                            <h3><i class="fa-solid fa-list-check"></i> Features</h3>
                            <ul class="features-list-detail">
                                <?php while ($feature = $features_result->fetch_assoc()): ?>
                                    <li>
                                        <span class="feature-icon"><i class="fa-solid fa-check"></i></span>
                                        <span><?php echo htmlspecialchars($feature['feature_text']); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Additional Info -->
                    <div class="additional-info">
                        <div class="info-item">
                            <span class="info-label">Created</span>
                            <span class="info-value">
                                <i class="fa-solid fa-calendar" style="margin-right: 5px;"></i>
                                <?php echo date('F d, Y', strtotime($product['created_at'])); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Updated</span>
                            <span class="info-value">
                                <i class="fa-solid fa-clock" style="margin-right: 5px;"></i>
                                <?php 
                                if (isset($product['updated_at']) && !empty($product['updated_at'])) {
                                    echo date('F d, Y', strtotime($product['updated_at']));
                                } else {
                                    echo 'Never updated';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total Images</span>
                            <span class="info-value">
                                <i class="fa-solid fa-images" style="margin-right: 5px;"></i>
                                <?php echo $product['total_images'] ?? 0; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                <i class="fa-solid fa-circle" style="color: <?php echo $stock_color; ?>; margin-right: 5px;"></i>
                                <?php echo $stock_text; ?>
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="product-actions-detail">
                        <a href="product.php" class="action-btn back">
                            <i class="fa-solid fa-arrow-left"></i> Back to List
                        </a>
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
        if (dropdown && !dropdown.contains(e.target)) {
            document.getElementById('dropdownMenu').style.display = 'none';
        }
    });

    // Change main image when clicking thumbnails
    function changeImage(imageSrc, clickedElement) {
        // Update main image
        const mainImage = document.getElementById('currentImage');
        if (mainImage) {
            mainImage.src = imageSrc;
        } else {
            const mainImageDiv = document.getElementById('mainImage');
            mainImageDiv.innerHTML = `<img src="${imageSrc}" alt="Product Image" id="currentImage">`;
        }
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        clickedElement.classList.add('active');
    }

    // Delete product confirmation
    function deleteProduct(productId) {
        if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
            fetch(`delete_product.php?id=${productId}`, { 
                method: 'DELETE' 
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product deleted successfully!');
                    window.location.href = 'product.php';
                } else {
                    alert('Error deleting product: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the product.');
            });
        }
    }

    // Initialize first image as active
    document.addEventListener('DOMContentLoaded', function() {
        const firstThumbnail = document.querySelector('.thumbnail');
        if (firstThumbnail) {
            firstThumbnail.classList.add('active');
        }
    });
    </script>
</body>
</html>