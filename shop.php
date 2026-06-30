<?php
session_start();
require 'dbconn.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Access Denied. Please log in.";
    exit;
}

// Get all categories
$category_sql = "SELECT * FROM product_categories ORDER BY name";
$category_result = $conn->query($category_sql);
$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

// Determine selected category
$selected_category_slug = $_GET['category'] ?? 'all';
$search_query = $_GET['search'] ?? '';

$category_id = null;
if ($selected_category_slug !== 'all') {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $selected_category_slug) {
            $category_id = $cat['id'];
            break;
        }
    }
    // Fallback if category slug not found
    if (!$category_id) {
        $selected_category_slug = 'all';
        $category_id = null;
    }
}

// Build product query 
if ($selected_category_slug === 'all') {

    $product_sql = "SELECT p.*, 
        (SELECT image_path FROM product_images 
         WHERE product_id = p.id 
         ORDER BY is_featured DESC, sort_order ASC 
         LIMIT 1) AS featured_image,
        p.category AS category_name,
        (SELECT slug FROM product_categories WHERE name = p.category LIMIT 1) AS category_slug
    FROM products p";

    if ($search_query) {
        $product_sql .= " WHERE p.name LIKE ? OR p.description LIKE ?";
    }

    $product_sql .= " ORDER BY p.featured DESC, p.created_at DESC";

} else {

    // Get category NAME from slug
    $cat_name = null;
    foreach ($categories as $cat) {
        if ($cat['slug'] === $selected_category_slug) {
            $cat_name = $cat['name'];
            break;
        }
    }

    $product_sql = "SELECT p.*, 
        (SELECT image_path FROM product_images 
         WHERE product_id = p.id 
         ORDER BY is_featured DESC, sort_order ASC 
         LIMIT 1) AS featured_image,
        p.category AS category_name,
        (SELECT slug FROM product_categories WHERE name = p.category LIMIT 1) AS category_slug
    FROM products p
    WHERE p.category = ?";

    if ($search_query) {
        $product_sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    }

    $product_sql .= " ORDER BY p.featured DESC, p.created_at DESC";
}


// Prepare & execute
$product_stmt = $conn->prepare($product_sql);

if ($selected_category_slug === 'all' && $search_query) {
    $param = "%$search_query%";
    $product_stmt->bind_param("ss", $param, $param);
} elseif ($selected_category_slug !== 'all') {
    if ($search_query) {
        $param = "%$search_query%";
        $product_stmt->bind_param("iss", $category_id, $param, $param);
    } else {
        $product_stmt->bind_param("i", $category_id);
    }
}

$product_stmt->execute();
$product_result = $product_stmt->get_result();

$products = [];
while ($product = $product_result->fetch_assoc()) {
    // Fetch features
    $features_sql = "SELECT feature_text FROM product_features WHERE product_id = ? ORDER BY sort_order ASC";
    $features_stmt = $conn->prepare($features_sql);
    $features_stmt->bind_param("i", $product['id']);
    $features_stmt->execute();
    $features_result = $features_stmt->get_result();

    $features = [];
    while ($f = $features_result->fetch_assoc()) {
        $features[] = $f['feature_text'];
    }
    $features_stmt->close();

    $product['features'] = $features;
    $product['status'] = (!empty($product['stock']) && $product['stock'] > 0) ? 'In Stock' : 'Out of Stock';

    $products[] = $product;
}
$product_stmt->close();


// ✅ Get user ID from session only
$user_id = (int) $_SESSION['user_id'];

// ✅ Fetch user details from database
$stmt = $conn->prepare("SELECT name FROM new_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// ✅ Use database value
$user_name = $user['name'];
$first_letter = strtoupper(substr($user_name, 0, 1));


// ✅ Fetch user details from database
$stmt = $conn->prepare("SELECT * FROM new_users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Get user avatar if exists
$avatar_path = "shop/assets/images/avatars/{$user_id}.png";
$default_avatar = "shop/assets/images/user.png";
$has_custom_avatar = file_exists($avatar_path);

// ✅ Prepare user data for checkout auto-fill
$user_data = [
    'name' => $user['name'] ?? '',
    'email' => $user['email'] ?? '',
    'phone' => $user['phone'] ?? '',
    'address' => $user['address'] ?? '',
    'city' => $user['city'] ?? '',
    'province' => $user['province'] ?? '',
    'zip_code' => $user['zip_code'] ?? '',
    'country' => $user['country'] ?? 'PH',
    'street' => $user['street'] ?? '',
    'barangay' => $user['barangay'] ?? ''
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - ChronoVerse</title>
     <link rel="stylesheet" href="css/all.min.css">
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
        
        /* Navigation */
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
        
         /* User Dropdown - Fixed for ChronoVerse Theme */
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

.user-name {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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

.logout-btn {
    color: #ff6b6b !important;
}

.logout-btn:hover {
    background: rgba(255, 107, 107, 0.2) !important;
    color: #ff6b6b !important;
}

/* Adjust header layout */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
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

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    header {
        flex-direction: column;
        gap: 15px;
        padding: 15px 20px;
    }
    
    .user-dropdown {
        margin-left: 0;
        margin-top: 10px;
        order: 3;
    }
    
    nav ul {
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .user-name {
        max-width: 80px;
    }
    
    .dropdown-menu {
        right: 50%;
        transform: translateX(50%);
    }
}
        
        .shop-header {
            background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.22)), 
                        url('image/shop-full-bg.jpg') center/cover no-repeat;
            padding: 140px 40px 80px;
            text-align: center;
            margin-top: 80px;
        }
        
        .shop-header h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .back-home {
            color: #4a9eff;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            padding: 8px 20px;
            border: 1px solid #4a9eff;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .back-home:hover {
            background: #4a9eff;
            color: white;
        }
        
        .container {
            margin:  auto;
            padding: 40px;
             background: linear-gradient(45deg, #dadbdb4d, #ffffff21);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            
        }
        
        .product-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(74, 158, 255, 0.2);
        }
        
        .product-img {
            height: 200px;
            background: linear-gradient(45deg, #1a1a3a, #2a2a4a);
            border-radius: 10px;
            margin-bottom: 20px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .product-img::after {
            content: '⌚';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            opacity: 0.3;
        }
        
        .price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4a9eff;
            margin: 15px 0;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 158, 255, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }
        
        .cart-sidebar {
            position: fixed;
            right: -400px;
            top: 0;
            width: 350px;
            height: 100%;
            background: rgba(10, 10, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            transition: right 0.3s ease;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 2000;
        }
        
        .cart-sidebar.active {
            right: 0;
        }
        
        .cart-items {
            max-height: 60vh;
            overflow-y: auto;
            margin: 20px 0;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .checkout-btn {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 25px;
            width: 100%;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Product Modal */
        .product-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 3000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: linear-gradient(135deg, #1a1a3a, #2a2a4a);
            border-radius: 25px;
            width: 100%;
            max-width: 900px;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            z-index: 1;
        }
        
        .modal-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 40px;
        }
        
        .modal-image {
            background: linear-gradient(45deg, #1a1a3a, #2a2a4a);
            border-radius: 15px;
            height: 300px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .modal-image::after {
            content: '⌚';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 4rem;
            opacity: 0.2;
        }
        
        .modal-info h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .modal-price {
            font-size: 2.5rem;
            color: #4a9eff;
            margin: 20px 0;
            font-weight: bold;
        }
        
        .modal-features {
            margin: 30px 0;
        }
        
        .modal-features li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        
        .modal-features li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4a9eff;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .modal-buttons .btn {
            flex: 1;
        }
        
        /* Cart toggle button */
        .cart-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 25px;
            cursor: pointer;
            z-index: 999;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(74, 158, 255, 0.3);
        }
        
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
            
            .modal-body {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            
            .shop-header {
                padding: 120px 20px 60px;
            }
            
            .shop-header h1 {
                font-size: 2.5rem;
            }
            
            .container {
                padding: 20px;
            }
            
            .button-group {
                flex-direction: column;
            }
        }
        
        /* Notification animation */
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
          
        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 60px 40px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Checkout Modal */
        .checkout-modal {
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

        .checkout-box {
            background: linear-gradient(135deg, #1a1a3a, #2a2a4a);
            border-radius: 25px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .close-checkout {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            z-index: 1;
        }

        .checkout-header {
            padding: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .checkout-header h2 {
            text-align: center;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .checkout-content {
            padding: 30px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        .checkout-section {
            margin-bottom: 30px;
        }

        .checkout-section h3 {
            color: #4a9eff;
            margin-bottom: 20px;
            font-size: 1.3rem;
            border-bottom: 2px solid rgba(74, 158, 255, 0.3);
            padding-bottom: 10px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row .input-group {
            flex: 1;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #a0c8ff;
            font-weight: 500;
        }

        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus,
        .input-group select:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: #4a9eff;
            box-shadow: 0 0 0 2px rgba(74, 158, 255, 0.2);
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .input-group option{
            color: black;
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .payment-option:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .payment-option.selected {
            border-color: #4a9eff;
            background: rgba(74, 158, 255, 0.1);
        }

        .payment-option input[type="radio"] {
            margin-right: 15px;
            transform: scale(1.2);
        }

        .payment-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .order-summary {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-items {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 2px solid rgba(255, 255, 255, 0.2);
            font-size: 1.2rem;
            font-weight: bold;
        }

        .total-amount {
            color: #4a9eff;
            font-size: 1.5rem;
        }

        .checkout-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .checkout-actions .btn {
            flex: 1;
        }

        .terms {
            margin-top: 20px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
        }

        .terms a {
            color: #4a9eff;
            text-decoration: none;
        }

        /* Success Modal */
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 4000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-box {
            background: linear-gradient(135deg, #1a1a3a, #2a2a4a);
            border-radius: 25px;
            width: 90%;
            max-width: 500px;
            padding: 50px;
            text-align: center;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .success-icon {
            font-size: 5rem;
            color: #4a9eff;
            margin-bottom: 20px;
        }

        .success-box h2 {
            color: #4a9eff;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .success-box p {
            color: #a0c8ff;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        /* Loading animation */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 5000;
            align-items: center;
            justify-content: center;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            border-top: 5px solid #4a9eff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
         /* Add these new styles */
        .category-navigation {
            background: #0a192f;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .category-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .category-title {
            color: #4a9eff;
            font-size: 1.2rem;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .category-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        
        .category-btn {
            padding: 10px 20px;
            background: rgba(74, 158, 255, 0.1);
            border: 1px solid rgba(74, 158, 255, 0.3);
            color: #a0c8ff;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: -5px;
        }
        
        .category-btn:hover,
        .category-btn.active {
            background: #4a9eff;
            color: white;
            border-color: #4a9eff;
            transform: translateY(-2px);
        }
        
        .search-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(74, 158, 255, 0.3);
            border-radius: 30px;
            padding: 5px;
            backdrop-filter: blur(10px);
        }
        
        .search-box input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 15px 20px;
            color: white;
            font-size: 1rem;
            outline: none;
        }
        
        .search-box input::placeholder {
            color: #a0c8ff;
        }
        
        .search-box button {
            background: #4a9eff;
            border: none;
            color: white;
            padding: 0 25px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .search-box button:hover {
            background: #2d8bff;
            transform: scale(1.05);
        }
        
        .products-header {
            text-align: center;
            margin: 10px 0;
        }
        
        .products-header h2 {
            font-size: 2.5rem;
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
            margin-top: -10px;
        }
        
        .products-header p {
            color: #a0c8ff;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
       .tracking-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.8);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    /* NEW */
    overflow-y: auto;
    padding: 20px;
}

        
        .tracking-box {
    background: linear-gradient(135deg, #0a192f, #1a2840);
    border-radius: 15px;
    padding: 40px;
    width: 100%;
    max-width: 500px;
    border: 1px solid rgba(74, 158, 255, 0.3);
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);

    /* NEW */
    max-height: 90vh;
    overflow-y: auto;
}

        .tracking-input {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        
        .tracking-input input {
            flex: 1;
            padding: 15px 20px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(74, 158, 255, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }
        
        .tracking-status {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #4a9eff;
        }
        
        .status-timeline {
            margin-top: 20px;
        }
        
        .status-step {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .status-step.active {
            opacity: 1;
        }
        
        .status-step.completed {
            opacity: 1;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(74, 158, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-icon.completed {
            background: #4a9eff;
        }
        
        .step-details {
            flex: 1;
        }
        
        .stock-info {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 8px;
        }
        
        .in-stock {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
            margin-top: 10px;
        }
        
        .low-stock {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .out-of-stock {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        .container {
      
            margin: auto;
            padding: 40px;
             background: linear-gradient(45deg, #dadbdb4d, #ffffff21);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
           
        }
        
        .product-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(74, 158, 255, 0.2);
        }
        
        .product-img {
            height: 200px;
            background: linear-gradient(45deg, #1a1a3a, #2a2a4a);
            border-radius: 10px;
            margin-bottom: 20px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .product-img::after {
            content: '⌚';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            opacity: 0.3;
        }
        
        .price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4a9eff;
            margin: 15px 0;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 158, 255, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }
        
        .cart-sidebar {
            position: fixed;
            right: -400px;
            top: 0;
            width: 350px;
            height: 100%;
            background: rgba(10, 10, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            transition: right 0.3s ease;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 2000;
        }
        
        .cart-sidebar.active {
            right: 0;
        }
        
        .cart-items {
            max-height: 60vh;
            overflow-y: auto;
            margin: 20px 0;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .checkout-btn {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 25px;
            width: 100%;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
        }
        .order-copy {
    background: #111;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    border: 1px dashed #4a9eff;
    letter-spacing: 1px;
    transition: all 0.2s ease;
}

.order-copy:hover {
    background: #1a1a1a;
    border-color: #fff;
}

.copy-badge {
    display: none;
    margin-left: 10px;
    background: limegreen;
    color: #000;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

    </style>
</head>
<body>
    <!-- Navigation - Match shop.php -->
    <header>
        <div class="logo">
            <img src="image/logo.png" alt="ChronoVerse Logo">
            ChronoVerse
        </div>
    
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
                 <a href="../Shop/shop/profile.php">
                    <i class="fa-solid fa-user"></i><span>Profile</span>
                </a>
                <a href="#" onclick="openTrackingModal()">
                    <i class="fas fa-truck"></i> Track Order
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>
      <!-- Shop Header -->
    <div class="shop-header">
        <h1>ChronoVerse Shop</h1>
        <p>Browse our complete collection of premium timepieces</p>
       
    </div>
  <!-- Category Navigation -->
<div class="category-navigation">
    <div class="category-container">
        <div class="category-title">BROWSE BY CATEGORY</div>

        <div class="category-buttons">

            <!-- ALL PRODUCTS BUTTON -->
            <button class="category-btn <?php echo $selected_category_slug === 'all' ? 'active' : ''; ?>" 
                    onclick="filterCategory('all')">
                <i class="fas fa-layer-group"></i> All Products
            </button>

            <!-- DYNAMIC CATEGORIES -->
            <?php foreach($categories as $cat): ?>
                <button class="category-btn <?php echo $selected_category_slug === $cat['slug'] ? 'active' : ''; ?>" 
                        onclick="filterCategory('<?php echo htmlspecialchars($cat['slug']); ?>')">
                    <i class="fas fa-folder"></i> 
                    <?php echo htmlspecialchars($cat['name']); ?>
                </button>
            <?php endforeach; ?>

        </div>
    </div>
</div>


<!-- Search Box -->
<div class="search-container">
    <form method="GET" action="shop.php" class="search-box">

        <!-- KEEP CURRENT CATEGORY WHEN SEARCHING -->
        <input type="hidden" name="category" 
               value="<?php echo htmlspecialchars($selected_category_slug); ?>">

        <input type="text" 
               name="search" 
               placeholder="Search for products..." 
               value="<?php echo htmlspecialchars($search_query); ?>">

        <button type="submit">
            <i class="fas fa-search"></i> Search
        </button>

    </form>
</div>


<!-- Products Header -->
<div class="products-header">

    <?php if($selected_category_slug === 'all'): ?>

        <h2>All Products</h2>
        <p>Discover our complete collection of premium watches</p>

    <?php else: 

        $cat_name = 'All Categories';

        foreach($categories as $cat) {
            if($cat['slug'] === $selected_category_slug) {
                $cat_name = $cat['name'];
                break;
            }
        }
    ?>

        <h2><?php echo htmlspecialchars($cat_name); ?> Collection</h2>

        <p>
            Explore our premium 
            <?php echo htmlspecialchars(strtolower($cat_name)); ?> watches
        </p>

    <?php endif; ?>


    <!-- RESULT COUNT -->
    <p style="color: #4a9eff; margin-top: 10px;">

        <?php echo count($products); ?> 
        product<?php echo count($products) !== 1 ? 's' : ''; ?> found

        <?php if($search_query): ?>
            for "<?php echo htmlspecialchars($search_query); ?>"
        <?php endif; ?>

    </p>

</div>


<!-- Products Grid -->
<div class="container">
    <div class="products-grid" id="productsGrid">
        <!-- Products will be loaded here via JS -->
    </div>
</div>


<script>
// INITIAL LOAD
document.addEventListener('DOMContentLoaded', function() {

    const urlParams = new URLSearchParams(window.location.search);

    const initialCategory = urlParams.get('category') || 'all';

    loadProducts(initialCategory);

});


// CATEGORY CLICK HANDLER
function filterCategory(slug) {

    const search = new URLSearchParams(window.location.search).get('search') || '';

    let url = 'shop.php?category=' + slug;

    if (search) {
        url += '&search=' + encodeURIComponent(search);
    }

    window.location.href = url;
}
</script>
    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <h3>Your Cart</h3>
        <div class="cart-items" id="cartItems">
            <!-- Cart items will be loaded here -->
        </div>
        <div class="cart-total">
            <h4>Total: $<span id="cartTotal">0</span></h4>
            <button class="checkout-btn">PROCEED TO CHECKOUT</button>
        </div>
    </div>
    
    <!-- Product Detail Modal -->
    <div class="product-modal" id="productModal">
        <div class="modal-content">
            <button class="close-modal" id="closeModalBtn">×</button>
            <div class="modal-body" id="modalBody">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
    </div>
     
     <!-- Order Tracking Modal -->
    <div class="tracking-modal" id="trackingModal">
        <div class="tracking-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #4a9eff; margin: 0;"><i class="fas fa-truck"></i> Track Your Order</h2>
                <button onclick="closeTrackingModal()" style="background: none; border: none; color: #a0c8ff; font-size: 24px; cursor: pointer;">×</button>
            </div>
            
            <p style="color: #a0c8ff; margin-bottom: 20px;">Enter your order number or tracking code to check the status</p>
            
            <div class="tracking-input">
                <input type="text" id="trackingNumber" placeholder="Enter order number or tracking code">
                <button class="btn btn-primary" onclick="trackOrder()">
                    <i class="fas fa-search"></i> Track
                </button>
            </div>
            
            <div id="trackingResult" style="display: none;">
                <!-- Tracking results will be displayed here -->
            </div>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(74, 158, 255, 0.3);">
                <p style="color: #8892b0; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Need help? Contact our support team at support@chronoverse.com
                </p>
            </div>
        </div>
    </div>

    <!-- Cart Toggle Button -->
    <button class="cart-toggle" onclick="toggleCart()">
        🛒 Cart (<span id="cartCount">0</span>)
    </button>

    <!-- Checkout Modal -->
<div class="checkout-modal" id="checkoutModal">
    <div class="checkout-box">
        <button class="close-checkout" id="closeCheckoutModal">×</button>
        
        <div class="checkout-header">
            <h2>Complete Your Purchase</h2>
            <p style="text-align: center; color: #a0c8ff;">Fill in your details to complete the checkout</p>
        </div>

        <!-- Wrap everything in a form -->
        <form id="checkoutForm" onsubmit="processCheckout(event)">
            <div class="checkout-content">
                <div class="checkout-grid">
                    <!-- Left Column: Shipping Information -->
                    <div class="checkout-section">
                        <h3>Shipping Information</h3>
                        
                        <div class="form-row">
                            <div class="input-group">
                                <label>First Name *</label>
                                <input type="text" id="firstName" name="firstName" placeholder="John" required>
                            </div>
                            <div class="input-group">
                                <label>Last Name *</label>
                                <input type="text" id="lastName" name="lastName" placeholder="Doe" required>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Email Address *</label>
                            <input type="email" id="email" name="email" placeholder="john.doe@example.com" required>
                        </div>

                        <div class="input-group">
                            <label>Phone Number *</label>
                            <input type="tel" id="phone" name="phone" placeholder="+1 (555) 123-4567" required>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label>Street *</label>
                                <input type="text" id="street" name="street" placeholder="Street, Building #" required>
                            </div>
                            <div class="input-group">
                                <label>Barangay *</label>
                                <input type="text" id="barangay" name="barangay" placeholder="Poblacion" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <label>City *</label>
                                <input type="text" id="city" name="city" placeholder="New York" required>
                            </div>
                            <div class="input-group">
                                <label>ZIP Code *</label>
                                <input type="text" id="zipCode" name="zipCode" placeholder="10001" required>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label>Province *</label>
                            <input type="text" id="province" name="province" placeholder="CEBU" required>
                        </div>
                        
                        <div class="input-group">
                            <label>Country *</label>
                            <select id="country" name="country" required>
                                <option value="">Select Country</option>
                                <option value="US">United States</option>
                                <option value="PH">Philippines</option>
                                <option value="UK">United Kingdom</option>
                                <option value="CA">Canada</option>
                                <option value="AU">Australia</option>
                                <option value="JP">Japan</option>
                                <option value="SG">Singapore</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                       <div class="input-group">
        <label>Shipping Method *</label>
        <select id="shippingMethod" name="shippingMethod" required>
            <option value="">Select Shipping</option>
            <option value="standard">Standard Shipping (5–7 days) - $5.99</option>
            <option value="express">Express Shipping (2–3 days) - $12.99</option>
            <option value="nextday">Next Day Delivery - $24.99</option>
        </select>
    </div>
</div>

                    <!-- Right Column: Payment & Order Summary -->
                    <div class="checkout-section">
                        <h3>Payment Method</h3>
                        
                        <div class="payment-methods">
                            <label class="payment-option" onclick="selectPayment('credit')">
                                <input type="radio" name="payment" value="credit" required>
                                <span class="payment-icon">💳</span>
                                <span>Credit/Debit Card</span>
                            </label>
                            
                            <label class="payment-option" onclick="selectPayment('paypal')">
                                <input type="radio" name="payment" value="paypal" required>
                                <span class="payment-icon">🅿️</span>
                                <span>PayPal</span>
                            </label>
                            
                            <label class="payment-option" onclick="selectPayment('gcash')">
                                <input type="radio" name="payment" value="gcash" required>
                                <span class="payment-icon">📱</span>
                                <span>GCash</span>
                            </label>
                            
                           <label class="payment-option" onclick="selectPayment('cod')">
                            <input type="radio" name="payment" value="cod" required>
                                <span class="payment-icon">💵</span>
                                <span>Cash on Delivery</span>
                            </label>
                            
                            <label class="payment-option" onclick="selectPayment('bank')">
                                <input type="radio" name="payment" value="bank" required>
                                <span class="payment-icon">🏦</span>
                                <span>Bank Transfer</span>
                            </label>
                        </div>

                        <!-- Credit Card Details (shown when credit card is selected) -->
                        <div id="cardDetails" style="display: none; margin-top: 20px;">
                            <h4>Card Details</h4>
                            <div class="input-group">
                                <label>Card Number *</label>
                                <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="form-row">
                                <div class="input-group">
                                    <label>Expiry Date *</label>
                                    <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY">
                                </div>
                                <div class="input-group">
                                    <label>CVV *</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Cardholder Name *</label>
                                <input type="text" id="cardName" name="cardName" placeholder="JOHN DOE">
                            </div>
                        </div>
                                                    <div id="walletDetails" style="display:none; margin-top:20px;">
                            <h4>Wallet Details</h4>
                            <div class="input-group">
                                <label>Account Email / Number *</label>
                                <input type="text" id="walletAccount" placeholder="example@email.com">
                            </div>
                            <div class="input-group">
                                <label>Reference Number *</label>
                                <input type="text" id="walletReference" placeholder="Transaction reference">
                            </div>
                        </div>

                        <div id="bankDetails" style="display:none; margin-top:20px;">
                            <h4>Bank Transfer Details</h4>
                            <div class="input-group">
                                <label>Bank Name *</label>
                                <input type="text" id="bankName">
                            </div>
                            <div class="input-group">
                                <label>Account Number *</label>
                                <input type="text" id="bankAccount">
                            </div>
                            <div class="input-group">
                                <label>Reference Number *</label>
                                <input type="text" id="bankReference">
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary">
                            <h4>Order Summary</h4>
                            <div class="order-items" id="checkoutItems">
                                <!-- Order items will be loaded here -->
                            </div>
                            <div class="order-total">
                                <span>Total Amount:</span>
                                <span class="total-amount" id="checkoutTotal">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Actions -->
                <div class="checkout-actions">
                    <button type="button" class="btn btn-outline" onclick="closeCheckoutModal()">CANCEL</button>
                    <button type="submit" class="btn btn-primary">COMPLETE PURCHASE</button>
                </div>

                <div class="terms">
                    <p>By completing your purchase, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></p>
                </div>
            </div>
        </form>
    </div>
</div>
    <!-- Success Modal -->
    <div class="success-modal" id="successModal">
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h2>Order Confirmed!</h2>
        <p>Thank you for your purchase. Your order has been successfully placed and will be processed shortly.</p>
        <p style="color: #fff; font-weight: bold; margin: 15px 0;">
            Order Number: 
            <span id="orderNumber" class="order-copy" onclick="copyOrderNumber()" title="Click to copy">
                ---
            </span>
            <span id="copyBadge" class="copy-badge">Copied!</span>
        </p>
        <p style="color: #a0c8ff; font-size: 0.9rem;">
            Please save this Order Number for tracking your order.
        </p>
        <p>You will receive a confirmation email with your order details and tracking information.</p>
        <div style="margin-top: 30px;">
            <button class="btn btn-primary" onclick="closeSuccessModal()">CONTINUE SHOPPING</button>
        </div>
    </div>
</div>

    <!-- Loading Overlay -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>


<script>
// Products data from PHP
const products = <?php 
echo json_encode(array_map(function($p) {
    $image_path = !empty($p['featured_image']) 
        ? 'product/' . $p['featured_image']
        : 'image/default-product.png';

    return [
        'id' => $p['id'],
        'name' => $p['name'],
        'price' => $p['price'],
        'description' => $p['description'],
        'image' => $image_path,
        'category' => $p['category_name'] ?? 'Uncategorized',
        'category_slug' => $p['category_slug'] ?? 'all',
        'features' => $p['features'] ?? [],
        'status' => (!empty($p['stock']) && $p['stock'] > 0) ? 'In Stock' : 'Out of Stock'
    ];
}, $products)); ?>;

// Cart state - DECLARE ONLY ONCE
let cart = JSON.parse(localStorage.getItem('chronoverse_cart')) || [];

// Shipping rules
const SHIPPING_RULES = {
    standard: { cost: 5.99, minDays: 5, maxDays: 7 },
    express: { cost: 12.99, minDays: 2, maxDays: 3 },
    nextday: { cost: 24.99, minDays: 1, maxDays: 1 }
};

// Modal elements
const productModal = document.getElementById('productModal');
const checkoutModal = document.getElementById('checkoutModal');
const successModal = document.getElementById('successModal');
const loading = document.getElementById('loading');

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
    // Get the current category from URL
    const urlParams = new URLSearchParams(window.location.search);
    const categorySlug = urlParams.get('category') || 'all';
    
    // Load products for the current category
    loadProducts(categorySlug);
    
    // Initialize cart
    updateCart();
    
    // Initialize payment method
    selectPayment('credit');
    
    // Set up event listeners
    initializeEventListeners();
});

// Load products with filtering - SINGLE DEFINITION
function loadProducts(filterSlug = 'all') {
    const productsGrid = document.getElementById('productsGrid');
    if (!productsGrid) return;

    productsGrid.innerHTML = '';

    // Filter products based on category slug
    const filteredProducts = filterSlug === 'all' 
        ? products 
        : products.filter(p => p.category_slug === filterSlug);

    // Show empty state if no products
    if (filteredProducts.length === 0) {
        productsGrid.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #a0c8ff;">
                <i class="fas fa-search" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                <h3>No Products Found</h3>
                <p>No products found in this category.</p>
                <button onclick="filterCategory('all')" 
                        class="btn btn-primary" style="margin-top: 20px;">
                    View All Products
                </button>
            </div>
        `;
        return;
    }

    // Display filtered products
    filteredProducts.forEach(product => {
        const productElement = document.createElement('div');
        productElement.className = 'product-item';

        productElement.innerHTML = `
            <div class="product-img" 
                 style="background-image: url('${product.image}');">
            </div>

            <h3>${product.name}</h3>

            <p>${product.description ? product.description.substring(0, 100) + '...' : ''}</p>

            <p class="product-status" 
               style="color: ${product.status === 'In Stock' ? 'limegreen' : '#ff6b6b'};">
                ${product.status}
            </p>

            <div class="price">$${product.price}</div>

            <div class="product-category">
                <small style="color:#aaa;">
                    Category: ${product.category}
                </small>
            </div>

            <div class="button-group">
                <button class="btn btn-secondary" 
                        onclick="viewProduct(${product.id})">
                    VIEW DETAILS
                </button>
                <button class="btn btn-primary" 
                    onclick="addToCart(${product.id})" 
                    ${product.status === 'Out of Stock' 
                        ? 'disabled style="opacity:0.6;cursor:not-allowed;"' 
                        : ''}>
                    ADD TO CART
                </button>
            </div>
        `;

        productsGrid.appendChild(productElement);
    });

    // Update products count in header
    updateProductsCount(filteredProducts.length, filterSlug);
}

// Update products count in header
function updateProductsCount(count, filterSlug) {
    const productsHeader = document.querySelector('.products-header');
    if (productsHeader) {
        const countElement = productsHeader.querySelector('p[style*="color: #4a9eff"]');
        if (countElement) {
            const searchQuery = new URLSearchParams(window.location.search).get('search') || '';
            let text = `${count} product${count !== 1 ? 's' : ''} found`;
            if (filterSlug !== 'all') {
                const catName = document.querySelector('.products-header h2')?.textContent.replace(' Collection', '') || 'category';
                text = `${count} ${catName} product${count !== 1 ? 's' : ''} found`;
            }
            if (searchQuery) {
                text += ` for "${searchQuery}"`;
            }
            countElement.textContent = text;
        }
    }
}

// Filter category function
function filterCategory(slug) {
    // Update URL
    const url = new URL(window.location);
    url.searchParams.set('category', slug);
    // Clear search when changing category
    url.searchParams.delete('search');
    window.history.pushState({}, '', url);
    
    // Update active button
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Find and activate the correct button
    const activeBtn = document.querySelector(`.category-btn[onclick*="filterCategory('${slug}')"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Update search form hidden input
    const searchFormCategory = document.querySelector('input[name="category"]');
    if (searchFormCategory) {
        searchFormCategory.value = slug;
    }
    
    // Update page header
    updatePageHeader(slug);
    
    // Load filtered products
    loadProducts(slug);
}

// Update page header based on category
function updatePageHeader(slug) {
    const productsHeader = document.querySelector('.products-header');
    if (!productsHeader) return;

    const h2 = productsHeader.querySelector('h2');
    const p  = productsHeader.querySelector('p');

    if (slug === 'all') {
        if (h2) h2.textContent = 'All Products';
        if (p)  p.textContent  = 'Discover our complete collection of premium watches';
        return;
    }

    // Find the button for this category
    const categoryBtn = document.querySelector(
        `.category-btn[onclick*="filterCategory('${slug}')"]`
    );

    if (categoryBtn) {
        // GET ONLY TEXT NODE – ignore <i> icon
        let categoryName = categoryBtn.childNodes;
        categoryName = Array.from(categoryName)
            .filter(node => node.nodeType === 3) // text nodes only
            .map(node => node.textContent)
            .join('')
            .trim();

        if (h2) h2.textContent = categoryName + ' Collection';
        if (p)  p.textContent  = 'Explore our premium ' + categoryName.toLowerCase() + ' watches';
    }
}

// Handle browser back/forward buttons
window.addEventListener('popstate', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category') || 'all';
    loadProducts(category);
});

// View product details
function viewProduct(productId) {
    console.log('Viewing product:', productId);
    const product = products.find(p => p.id === productId);
    const modalBody = document.getElementById('modalBody');
    
    if (!product || !modalBody) return;
    
    modalBody.innerHTML = `
        <div class="product-img" style="background-image: url('${product.image}');"></div>
        <div class="modal-info">
            <span class="modal-category" 
                  style="color: #a0c8ff; font-size: 0.9rem; cursor:pointer;" 
                  onclick="filterCategory('${product.category_slug || 'all'}'); closeProductModal();">
                ${product.category}
            </span>
            <h2>${product.name}</h2>
            <div class="modal-price">$${product.price}</div>
            <p style="color: #cccccc; line-height: 1.6; margin-bottom: 20px;">${product.description}</p>
            
            <div class="modal-features">
                <h3 style="margin-bottom: 15px;">Features:</h3>
                <ul style="list-style: none; padding: 0;">
                    ${product.features.map(feature => `<li>${feature}</li>`).join('')}
                </ul>
            </div>
            
            <div class="modal-buttons">
                <button class="btn btn-primary" onclick="addToCart(${product.id}); closeProductModal();">
                    ADD TO CART
                </button>
                <button class="btn btn-secondary" onclick="closeProductModal()">
                    CONTINUE SHOPPING
                </button>
            </div>
        </div>
    `;
    
    productModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Add to cart function
function addToCart(productId) {
    console.log('Adding to cart:', productId);
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            ...product,
            quantity: 1
        });
    }
    
    updateCart();
    showAddedNotification(product.name);
}

// Update cart display
function updateCart() {
    // Save to localStorage
    localStorage.setItem('chronoverse_cart', JSON.stringify(cart));
    
    // Update cart count
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCountElement.textContent = totalItems;
    }
    
    // Update cart items
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    
    if (!cartItems || !cartTotal) return;
    
    cartItems.innerHTML = '';
    let total = 0;
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p style="text-align: center; color: #a0c8ff;">Your cart is empty</p>';
    } else {
        cart.forEach(item => {
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item';
            itemElement.innerHTML = `
                <div>
                    <strong>${item.name}</strong>
                    <div style="font-size: 0.9rem; color: #a0c8ff;">$${item.price} × ${item.quantity}</div>
                    <div style="font-size: 0.9rem; color: #4a9eff;">$${item.price * item.quantity}</div>
                </div>
                <div>
                    <button onclick="removeFromCart(${item.id})" style="background: none; border: none; color: #ff6b6b; cursor: pointer; font-size: 1.2rem;">✕</button>
                </div>
            `;
            cartItems.appendChild(itemElement);
            total += item.price * item.quantity;
        });
    }
    
    cartTotal.textContent = total.toFixed(2);
}

// Remove from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
}

// Toggle cart sidebar
function toggleCart() {
    console.log('Toggling cart');
    const sidebar = document.getElementById('cartSidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Close product modal
function closeProductModal() {
    if (productModal) {
        productModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close checkout modal
function closeCheckoutModal() {
    if (checkoutModal) {
        checkoutModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close success modal
function closeSuccessModal() {
    if (successModal) {
        successModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        // Clear cart after successful purchase
        cart = [];
        updateCart();
        closeCheckoutModal();
    }
}

// Show notification
function showAddedNotification(productName) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 30px;
        background: linear-gradient(45deg, #4a9eff, #0066ff);
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        z-index: 1001;
        animation: slideIn 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    `;
    notification.textContent = `✓ Added ${productName} to cart!`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Open checkout modal
function openCheckoutModal() {
    console.log('Opening checkout modal');
    if (cart.length === 0) {
        alert('Your cart is empty. Add some products first!');
        return;
    }
  updateCheckoutSummary();
    checkoutModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Auto-fill user data when opening checkout
    autoFillCheckoutForm();
}

// Update checkout summary
function updateCheckoutSummary() {
    const checkoutItems = document.getElementById('checkoutItems');
    const checkoutTotal = document.getElementById('checkoutTotal');
    
    if (!checkoutItems || !checkoutTotal) return;
    
    checkoutItems.innerHTML = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'order-item';
        itemElement.innerHTML = `
            <div>
                <strong>${item.name}</strong>
                <div style="font-size: 0.9rem; color: #a0c8ff;">Qty: ${item.quantity}</div>
            </div>
            <div>$${(item.price * item.quantity).toFixed(2)}</div>
        `;
        checkoutItems.appendChild(itemElement);
        subtotal += item.price * item.quantity;
    });
    
    // Add shipping cost based on selection
    const shippingSelect = document.getElementById('shippingMethod');
    const shippingCost = shippingSelect && shippingSelect.value ? getShippingCost(shippingSelect.value) : 0;
    
    checkoutItems.innerHTML += `
        <div class="order-item">
            <div>Subtotal</div>
            <div>$${subtotal.toFixed(2)}</div>
        </div>
        <div class="order-item">
            <div>Shipping</div>
            <div id="shippingCost">$${shippingCost.toFixed(2)}</div>
        </div>
    `;
    
    const total = subtotal + shippingCost;
    checkoutTotal.textContent = `$${total.toFixed(2)}`;
}

// Get shipping cost based on method
function getShippingCost(method) {
    return SHIPPING_RULES[method]?.cost || 0;
}

function calculateExpectedDelivery(method) {
    const rule = SHIPPING_RULES[method];
    if (!rule) return null;

    const today = new Date();
    const expectedMin = new Date(today);
    expectedMin.setDate(today.getDate() + rule.minDays);

    const expectedMax = new Date(today);
    expectedMax.setDate(today.getDate() + rule.maxDays);

    return {
        min_date: expectedMin.toISOString().slice(0, 10),
        max_date: expectedMax.toISOString().slice(0, 10),
        display: rule.minDays === rule.maxDays
            ? `${rule.minDays} day`
            : `${rule.minDays}–${rule.maxDays} days`
    };
}


// Process checkout - DEBUG VERSION
function processCheckout(event) {
    event.preventDefault(); // Prevent form submission
    
    console.log('=== PROCESS CHECKOUT STARTED ===');
    
    // Get all required inputs
    const requiredInputs = [
        'firstName', 'lastName', 'email', 'phone', 'street', 
        'barangay', 'city', 'zipCode', 'province', 'country', 'shippingMethod'
    ];

    let isValid = true;
    const missingFields = [];

    // Validate required fields
    requiredInputs.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        console.log(`Checking field ${fieldId}:`, field?.value);
        
        if (field) {
            if (!field.value.trim()) {
                isValid = false;
                missingFields.push(fieldId.replace(/([A-Z])/g, ' $1').toLowerCase());
                field.style.borderColor = '#ff6b6b';
            } else {
                field.style.borderColor = '';
            }
        } else {
            console.error(`Field ${fieldId} not found!`);
        }
    });

    // Validate payment method
    const paymentSelected = document.querySelector('input[name="payment"]:checked');
    console.log('Payment selected:', paymentSelected?.value);
    
    if (!paymentSelected) {
        alert('Please select a payment method');
        isValid = false;
        return;
    }

    // Validate payment-specific details
    const paymentMethod = paymentSelected.value;
    const paymentError = validatePaymentDetails(paymentMethod);
    if (paymentError) {
        alert(paymentError);
        isValid = false;
        return;
    }

    if (!isValid) {
        if (missingFields.length > 0) {
            alert(`Please fill in the following required fields:\n• ${missingFields.join('\n• ')}`);
        } else {
            alert('Please fill in all required fields marked with *');
        }
        return;
    }

    // Show loading
    if (loading) loading.style.display = 'flex';

    // Generate order number
    const orderNumber = 'CV-' + Date.now().toString().slice(-8);
    const shippingMethod = document.getElementById('shippingMethod').value;
    const expectedDelivery = calculateExpectedDelivery(shippingMethod);
    
    console.log('Shipping method:', shippingMethod);
    console.log('Expected delivery:', expectedDelivery);

    // Build order data
    const orderData = {
        orderNumber: orderNumber,
        items: cart.map(item => ({
            id: item.id,
            name: item.name,
            price: parseFloat(item.price),
            quantity: item.quantity
        })),
        customer: {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: {
                street: document.getElementById('street').value,
                barangay: document.getElementById('barangay').value,
                city: document.getElementById('city').value,
                province: document.getElementById('province').value,
                zipCode: document.getElementById('zipCode').value,
                country: document.getElementById('country').value
            }
        },
        shipping: {
            method: shippingMethod,
            cost: getShippingCost(shippingMethod),
            expected_delivery: expectedDelivery
        },
        payment: {
            method: paymentMethod,
            details: getPaymentDetails(paymentMethod)
        }
    };

    console.log('Order data to send:', JSON.stringify(orderData, null, 2));

    // Send order to server
    fetch('checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(response => {
        console.log('Server response:', response);
        
        if (loading) loading.style.display = 'none';

        if (response.success) {
            // Display success modal
            const orderNumberElement = document.getElementById('orderNumber');
            if (orderNumberElement) {
                orderNumberElement.textContent = response.tracking_number || orderNumber;
            }
            successModal.style.display = 'flex';
            closeCheckoutModal();

            // Clear cart
            cart = [];
            updateCart();

            console.log('Order saved to database:', response);
        } else {
            alert('Checkout failed: ' + (response.message || 'Unknown error'));
            console.error('Server error details:', response);
        }
    })
    // Update the .catch() block in your processCheckout function:
.catch(err => {
    console.error('Fetch error details:', err);
    console.error('Error name:', err.name);
    console.error('Error message:', err.message);
    console.error('Error stack:', err.stack);
    
    if (loading) loading.style.display = 'none';
    
    // Try to get the raw response text
    fetch('checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        console.log('Raw response status:', response.status, response.statusText);
        return response.text();
    })
    .then(text => {
        console.error('Raw response text:', text);
        try {
            const data = JSON.parse(text);
            console.error('Parsed response:', data);
            alert('Checkout error: ' + (data.message || 'Unknown error'));
        } catch (e) {
            console.error('Response is not JSON:', text);
            alert('Server returned non-JSON response. Check console for details.');
        }
    })
    .catch(fetchErr => {
        console.error('Second fetch error:', fetchErr);
        alert('Network or server error. Check console for details.');
    });
});
}

// Validate payment details
function validatePaymentDetails(method) {
    console.log('Validating payment method:', method);
    
    if (method === 'credit') {
        const cardNumber = document.getElementById('cardNumber')?.value;
        const expiryDate = document.getElementById('expiryDate')?.value;
        const cvv = document.getElementById('cvv')?.value;
        const cardName = document.getElementById('cardName')?.value;
        
        console.log('Card details:', { cardNumber, expiryDate, cvv, cardName });
        
        if (!cardNumber || !expiryDate || !cvv || !cardName) {
            return 'Please fill in all card details';
        }
        
        // Simple card validation
        if (cardNumber.replace(/\s/g, '').length < 15) {
            return 'Please enter a valid card number';
        }
        if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
            return 'Please enter expiry date in MM/YY format';
        }
        if (!/^\d{3,4}$/.test(cvv)) {
            return 'Please enter a valid CVV';
        }
    }
    
    if (method === 'paypal' || method === 'gcash') {
        const walletAccount = document.getElementById('walletAccount')?.value;
        const walletReference = document.getElementById('walletReference')?.value;
        console.log('Wallet details:', { walletAccount, walletReference });
        
        if (!walletAccount || !walletReference) {
            return `Please fill in ${method} details`;
        }
    }
    
    if (method === 'bank' || method === 'bank transfer') {
        const bankName = document.getElementById('bankName')?.value;
        const bankAccount = document.getElementById('bankAccount')?.value;
        const bankReference = document.getElementById('bankReference')?.value;
        console.log('Bank details:', { bankName, bankAccount, bankReference });
        
        if (!bankName || !bankAccount || !bankReference) {
            return 'Please fill in all bank transfer details';
        }
    }
    
    return null; // No errors
}

// Get payment details
function getPaymentDetails(method) {
    console.log('Getting payment details for:', method);
    
    // Handle both "bank" and "bank transfer" values
    if (method === 'bank transfer') {
        method = 'bank'; // Normalize to 'bank'
    }
    
    if (method === 'credit') {
        return {
            card_last4: document.getElementById('cardNumber')?.value.slice(-4) || '',
            card_name: document.getElementById('cardName')?.value || '',
            expiry: document.getElementById('expiryDate')?.value || ''
        };
    }
    if (method === 'paypal' || method === 'gcash') {
        return {
            provider: method,
            account: document.getElementById('walletAccount')?.value || '',
            reference: document.getElementById('walletReference')?.value || ''
        };
    }
    if (method === 'bank') {
        return {
            bank_name: document.getElementById('bankName')?.value || '',
            account: document.getElementById('bankAccount')?.value || '',
            reference: document.getElementById('bankReference')?.value || ''
        };
    }
    if (method === 'cod') {
        return {
            method: 'Cash on Delivery'
        };
    }
    return {};
}

// Also update your selectPayment function to handle "bank transfer" value:
function selectPayment(method, event = null) {
    console.log('Selecting payment:', method);
    
    // Handle "bank transfer" value from HTML
    const radioValue = method === 'bank transfer' ? 'bank' : method;
    const radioBtn = document.querySelector(`input[value="${method}"]`);
    if (radioBtn) {
        radioBtn.checked = true;
    }
    
    // Remove selected class from all
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selected class to clicked element if event is provided
    if (event) {
        const clickedOption = event.target.closest('.payment-option');
        if (clickedOption) {
            clickedOption.classList.add('selected');
        }
    }
    
    // Show/hide appropriate details sections
    const cardDetails = document.getElementById('cardDetails');
    const walletDetails = document.getElementById('walletDetails');
    const bankDetails = document.getElementById('bankDetails');
    
    // Normalize method for display logic
    const displayMethod = method === 'bank transfer' ? 'bank' : method;
    
    if (cardDetails) cardDetails.style.display = displayMethod === 'credit' ? 'block' : 'none';
    if (walletDetails) walletDetails.style.display = (displayMethod === 'paypal' || displayMethod === 'gcash') ? 'block' : 'none';
    if (bankDetails) bankDetails.style.display = displayMethod === 'bank' ? 'block' : 'none';
    
    // Update required attributes
    const cardFields = ['cardNumber', 'expiryDate', 'cvv', 'cardName'];
    cardFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.required = displayMethod === 'credit';
        }
    });
    
    const walletFields = ['walletAccount', 'walletReference'];
    walletFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.required = (displayMethod === 'paypal' || displayMethod === 'gcash');
        }
    });
    
    const bankFields = ['bankName', 'bankAccount', 'bankReference'];
    bankFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.required = displayMethod === 'bank';
        }
    });
}
function copyOrderNumber() {
    const orderText = document.getElementById('orderNumber').innerText;

    navigator.clipboard.writeText(orderText).then(() => {
        const badge = document.getElementById('copyBadge');
        badge.style.display = 'inline-block';

        setTimeout(() => {
            badge.style.display = 'none';
        }, 2000);
    });
}

// Initialize event listeners
function initializeEventListeners() {
    console.log('Setting up event listeners...');
    
    // Close modal buttons
    const closeModalBtn = document.getElementById('closeModalBtn');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeProductModal);
        console.log('Close modal button listener added');
    }
    
    const closeCheckoutModalBtn = document.getElementById('closeCheckoutModal');
    if (closeCheckoutModalBtn) {
        closeCheckoutModalBtn.addEventListener('click', closeCheckoutModal);
        console.log('Close checkout modal button listener added');
    }
    
    // Close modals when clicking outside
    if (productModal) {
        productModal.addEventListener('click', (e) => {
            if (e.target === productModal) {
                closeProductModal();
            }
        });
    }
    
    // Close cart when clicking outside (on mobile)
    document.addEventListener('click', (e) => {
        const sidebar = document.getElementById('cartSidebar');
        const cartToggle = document.querySelector('.cart-toggle');
        
        if (sidebar && sidebar.classList.contains('active') && 
            !sidebar.contains(e.target) && 
            e.target !== cartToggle && 
            !cartToggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
    
    // Checkout button - IMPORTANT: Use event delegation or ensure button exists
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', openCheckoutModal);
        console.log('Checkout button listener added');
    } else {
        console.log('Checkout button not found, will use event delegation');
        // Use event delegation for dynamically created checkout button
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('checkout-btn')) {
                openCheckoutModal();
            }
        });
    }
    
    // Shipping method change listener
    const shippingMethod = document.getElementById('shippingMethod');
    if (shippingMethod) {
        shippingMethod.addEventListener('change', function() {
            updateCheckoutSummary();
        });
    }
    
    // Auto-select first payment option
    const firstPaymentOption = document.querySelector('.payment-option');
    if (firstPaymentOption) {
        selectPayment('credit');
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === checkoutModal) closeCheckoutModal();
        if (e.target === successModal) closeSuccessModal();
        if (e.target === productModal) closeProductModal();
        
        const loginModal = document.getElementById('loginModal');
        if (e.target === loginModal && loginModal) {
            loginModal.style.display = 'none';
        }
    });
    
    // Close modals with ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCheckoutModal();
            closeSuccessModal();
            closeProductModal();
            
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                loginModal.style.display = 'none';
            }
        }
    });
    
    // Check for product filter from home page
    const urlParams = new URLSearchParams(window.location.search);
    const productType = urlParams.get('product');
    
    if (productType) {
        // Find and view the specific product
        const product = products.find(p => p.category.toLowerCase() === productType.toLowerCase());
        if (product) {
            setTimeout(() => {
                viewProduct(product.id);
            }, 500);
        }
    }
    
    console.log('Event listeners initialized');
}
// User data from PHP for checkout auto-fill
const userData = <?php echo json_encode($user_data); ?>;

// Auto-fill checkout form with user data
function autoFillCheckoutForm() {
    console.log('Auto-filling form with user data:', userData);
    
    // Only fill if fields are empty (don't overwrite if user already typed something)
    
    // Split full name into first and last name
    if (userData.name) {
        const nameParts = userData.name.trim().split(' ');
        const firstName = document.getElementById('firstName');
        const lastName = document.getElementById('lastName');
        
        if (firstName && !firstName.value && nameParts.length > 0) {
            firstName.value = nameParts[0];
        }
        if (lastName && !lastName.value && nameParts.length > 1) {
            lastName.value = nameParts.slice(1).join(' ');
        }
    }
    
    // Fill email
    const emailField = document.getElementById('email');
    if (emailField && !emailField.value && userData.email) {
        emailField.value = userData.email;
    }
    
    // Fill phone
    const phoneField = document.getElementById('phone');
    if (phoneField && !phoneField.value && userData.phone) {
        phoneField.value = userData.phone;
    }
    
    // Fill address fields
    const streetField = document.getElementById('street');
    if (streetField && !streetField.value && userData.street) {
        streetField.value = userData.street;
    }
    
    const barangayField = document.getElementById('barangay');
    if (barangayField && !barangayField.value && userData.barangay) {
        barangayField.value = userData.barangay;
    }
    
    const cityField = document.getElementById('city');
    if (cityField && !cityField.value && userData.city) {
        cityField.value = userData.city;
    }
    
    const provinceField = document.getElementById('province');
    if (provinceField && !provinceField.value && userData.province) {
        provinceField.value = userData.province;
    }
    
    const zipField = document.getElementById('zipCode');
    if (zipField && !zipField.value && userData.zip_code) {
        zipField.value = userData.zip_code;
    }
    
    const countryField = document.getElementById('country');
    if (countryField && !countryField.value && userData.country) {
        countryField.value = userData.country;
    }
    
    console.log('Checkout form auto-filled successfully');
}
// Order tracking functions
function openTrackingModal() {
    document.getElementById('trackingModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeTrackingModal() {
    document.getElementById('trackingModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

async function trackOrder() {
    const trackingInput = document.getElementById('trackingNumber');
    const trackingNumber = trackingInput.value.trim();
    const trackingResult = document.getElementById('trackingResult');
    
    if (!trackingNumber) {
        alert('Please enter a tracking number');
        return;
    }
    
    // Show loading
    trackingResult.innerHTML = '<div style="text-align: center; color: #a0c8ff;"><i class="fas fa-spinner fa-spin"></i> Tracking...</div>';
    trackingResult.style.display = 'block';
    
    try {
        const response = await fetch(`get_tracking.php?tracking=${encodeURIComponent(trackingNumber)}`);
        const data = await response.json();
        
        if (data.success) {
            // Pass false to prevent auto-opening the modal
            displayTrackingInfo(data, false);
            // Now you can manually open it if needed, or keep it closed
            // openTrackingModal(); // Uncomment this if you want it to open
        } else {
            trackingResult.innerHTML = `
                <div style="background: rgba(220, 53, 69, 0.1); padding: 20px; border-radius: 10px; border-left: 4px solid #dc3545;">
                    <h4 style="color: #dc3545; margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Order Not Found</h4>
                    <p style="color: #a0c8ff;">${data.message || 'Please check your tracking number and try again.'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Tracking error:', error);
        trackingResult.innerHTML = `
            <div style="background: rgba(255, 193, 7, 0.1); padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107;">
                <h4 style="color: #ffc107; margin-top: 0;"><i class="fas fa-exclamation-triangle"></i> Connection Error</h4>
                <p style="color: #a0c8ff;">Unable to connect to server. Please try again later.</p>
            </div>
        `;
    }
}
function displayTrackingInfo(data, autoOpen = true) {
    const trackingResult = document.getElementById('trackingResult');
    const order = data.order;

    /* ---------- STATUS MAP ---------- */
    const statusMap = {
        pending: { text: 'Order Placed', icon: 'fa-box', color: '#4a9eff' },
        processing: { text: 'Processing', icon: 'fa-cogs', color: '#ffc107' },
        shipped: { text: 'Shipped', icon: 'fa-shipping-fast', color: '#17a2b8' },
        out_for_delivery: { text: 'Out for Delivery', icon: 'fa-truck', color: '#28a745' },
        delivered: { text: 'Delivered', icon: 'fa-check-circle', color: '#28a745' }
    };

    const statusInfo = statusMap[order.order_status] || statusMap.pending;

    /* ---------- DELIVERY DATE ---------- */
    const deliveryStart = order.expected_delivery_start || null;
    const deliveryEnd = order.expected_delivery_end || null;
    const deliveryText = (deliveryStart && deliveryEnd)
        ? `${new Date(deliveryStart).toLocaleDateString()} - ${new Date(deliveryEnd).toLocaleDateString()}`
        : 'Processing';

    /* ---------- ITEM + PRODUCT FIX ---------- */
    const itemCount = Number(order.item_count) || 0;
    const itemLabel = itemCount === 1 ? 'item' : 'item(s)';

    const productList = (Array.isArray(data.product_names) && data.product_names.length)
        ? data.product_names.join(', ')
        : 'Product details unavailable';

    /* ---------- RENDER ---------- */
    trackingResult.innerHTML = `
        <div class="tracking-status">
            <h4 style="color: ${statusInfo.color}; margin-top: 0;">
                <i class="fas ${statusInfo.icon}"></i> ${statusInfo.text}
            </h4>

            <p style="color: #a0c8ff; margin-bottom: 10px;">
                Order: <strong>${order.order_number}</strong><br>
                Tracking: <strong>${order.tracking_number || 'N/A'}</strong><br>
                Placed: ${new Date(order.created_at).toLocaleDateString()}
            </p>

            ${data.tracking_history.length ? `
                <div class="status-timeline">
                    ${data.tracking_history.map((track, index) => `
                        <div class="status-step ${index === 0 ? 'active' : ''}">
                            <div class="step-icon ${index === 0 ? 'completed' : ''}">
                                <i class="fas ${statusMap[track.status]?.icon || 'fa-info-circle'}"
                                   style="color: ${index === 0 ? 'white' : '#4a9eff'}"></i>
                            </div>
                            <div class="step-details">
                                <strong style="color: white;">
                                    ${statusMap[track.status]?.text || track.status}
                                </strong>
                                <p style="color: #a0c8ff; margin: 5px 0; font-size: 0.9em;">
                                    ${new Date(track.updated_at).toLocaleDateString()}
                                    ${new Date(track.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                </p>
                                ${track.location ? `<p style="color:#a0c8ff;font-size:.85em;"><i class="fas fa-map-marker-alt"></i> ${track.location}</p>` : ''}
                                ${track.notes ? `<p style="color:#a0c8ff;font-size:.85em;">📝 ${track.notes}</p>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : `
                <p style="color:#a0c8ff;text-align:center;padding:20px;">
                    <i class="fas fa-info-circle"></i> Tracking information will be available soon.
                </p>
            `}

            <div style="margin-top:20px;padding:15px;background:rgba(74,158,255,.1);border-radius:8px;border-left:3px solid #4a9eff;">
                <h5 style="color:white;margin-top:0;"><i class="fas fa-user"></i> Customer Details</h5>
                <p style="color:#a0c8ff;">
                    ${order.customer_name}<br>
                    ${order.customer_email}<br>
                    ${order.shipping_address || 'N/A'}
                </p>

                <h5 style="color:white;margin:15px 0 5px;"><i class="fas fa-box"></i> Package Details</h5>
                <p style="color:#a0c8ff;">
                    ${itemCount} ${itemLabel}<br>
                    ${productList}<br>
                    ${order.shipping_method || 'N/A'}<br>
                    Estimated delivery: ${deliveryText}
                </p>
            </div>

            <div style="margin-top:20px;text-align:center;">
                <button onclick="printTracking()" class="btn-primary">
                    <i class="fas fa-print"></i> Print Tracking
                </button>
                <button onclick="shareTracking()" class="btn-success">
                    <i class="fas fa-share-alt"></i> Share Tracking
                </button>
            </div>
        </div>
    `;

    if (autoOpen) {
        openTrackingModal();
    }
}

function printTracking() {
    const trackingResult = document.getElementById('trackingResult');
    const printContent = trackingResult.innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Tracking Details</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .status-timeline { margin: 20px 0; }
                .status-step { display: flex; margin-bottom: 15px; }
                .step-icon { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; }
                .completed { background: #28a745; }
                @media print {
                    button { display: none !important; }
                }
            </style>
        </head>
        <body>
            <h2>Order Tracking Details</h2>
            ${printContent}
        </body>
        </html>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
    location.reload(); // Reload to restore functionality
}

function shareTracking() {
    const trackingInput = document.getElementById('trackingNumber');
    const trackingNumber = trackingInput.value.trim();
    
    if (navigator.share) {
        navigator.share({
            title: 'Order Tracking',
            text: `Track your order with tracking number: ${trackingNumber}`,
            url: window.location.href
        }).catch(error => console.log('Error sharing:', error));
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(`Track your order: ${window.location.origin}/track?number=${trackingNumber}`)
            .then(() => alert('Tracking link copied to clipboard!'))
            .catch(err => alert('Failed to copy: ' + err));
    }
}

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
</script>
     <!-- Footer -->
    <footer>
        <div class="logo" style="font-size: 2rem; margin-bottom: 20px;">
            ChronoVerse
        </div>
        <p>The Universe of Time</p>
        <div class="social-links">
            <a href="#">📘</a>
            <a href="#">📸</a>
            <a href="#">🐦</a>
            <a href="#">📽</a>
        </div>
        <p>&copy; 2023 St4ngerDev. All rights reserved 2026.</p>
    </footer>
</body>
</html>