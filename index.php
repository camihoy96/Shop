<?php
session_start();
require('dbconn.php'); // your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {

    header('Content-Type: application/json');

    $stmt = $conn->prepare("SELECT id, name, email, password, type FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($_POST['password'], $user['password'])) {

            session_regenerate_id(true); // Prevent session fixation

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type']  = $user['type'];

            $_SESSION['flash_message'] = [
                'text' => "Welcome, {$user['name']}!",
                'type' => 'success'
            ];

            if ($user['type'] === 'admin') {
                echo json_encode(['status' => 'success', 'redirect' => 'admin/home.php']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Access denied. Admins only.']);
            }

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }

    exit;
}
// Fetch products with their category name
$sql = "
SELECT 
    p.*,
    c.name AS category_name,
    img.image_path AS featured_image
FROM products p
LEFT JOIN product_categories c 
    ON p.category = c.id
INNER JOIN product_images img 
    ON p.id = img.product_id AND img.is_featured = 1
ORDER BY p.created_at DESC
";

$result = $conn->query($sql);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChronoVerse – The Universe of Time</title>
   <link rel="stylesheet" href="css/all.min.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/en_US/sdk.js"></script>

    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            background: #0a0a2a;
            color: white;
            line-height: 1.6;
            overflow-x: hidden;
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
            background: linear-gradient(45deg, #bdd8f3ff, #fafeffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: white;
        }
        
        .logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
            color: white;
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
        
        /* Hero Section */
        .hero {
    background: linear-gradient(
        rgba(156, 141, 141, 0.11),
        rgba(212, 212, 212, 0.02)
    ),
    url('image/shop-bg.jpg');   

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                url('image/bg.jpg') center/cover no-repeat,
                linear-gradient(135deg, rgba(10, 10, 42, 0.9), rgba(26, 26, 58, 0.8));
            background-blend-mode: overlay;
            z-index: -2;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(74, 158, 255, 0.15) 0%, transparent 50%),
                      radial-gradient(circle at 80% 20%, rgba(0, 204, 255, 0.1) 0%, transparent 50%);
            z-index: -1;
        }
        
        .hero-content {
            max-width: 800px;
            text-align: center;
            z-index: 1;
        }
        
        .hero h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #4a9eff, #00ccff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }rgba(44, 8, 8, 0.3)rgba(44, 8, 8, 0.92)
        
        .hero h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            color:  #a0c8ff;
        }
        
        .hero p {
    font-size: 1.3rem;
    margin: 0 auto 40px auto;
    color: #7bf11aff;
    max-width: 600px;
    font-weight: bold;     /* this makes the text bold */
}

        
        /* Section Common Styles */
        section {
            padding: 120px 40px;
            position: relative;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-header h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ffffffff, #ffffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: white;
        }
        
        .section-header p {
            font-size: 1.2rem;
            color: #a0c8ff;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* About Section with Background */
        .about {
            background: 
                url('image/bk.jpg') center/cover no-repeat fixed,
                linear-gradient(135deg, rgba(172, 172, 172, 0), rgba(26, 26, 58, 0.14));
            background-blend-mode: overlay;
            position: relative;
        }
        
        .about::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
        }
        
        .about-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 60px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .about-column {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .about-column h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #4a9eff;
        }
        
        /* Shop Preview with Background */
        .shop-preview {
            padding: 100px 40px;
            background: 
                url('image/bg.jpg') center/cover no-repeat,
                linear-gradient(135deg, rgba(10, 10, 42, 0.03), rgba(26, 26, 58, 0.14));
            background-blend-mode: overlay;
            position: relative;
        }
        
        .shop-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(74, 158, 255, 0.25);
        }
        
       .product-image {
    height: 200px;
    border-radius: 15px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}


.product-image img {
    width: auto;
    height: auto;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    position: relative;
    z-index: 2;
}

.watch-img {
    transition: transform 0.4s ease;
}

.product-card:hover .watch-img {
    transform: scale(1.08);
}

        .product-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #fff;
        }
        
        .product-card p {
            color: #f8fbffff;
            margin-bottom: 20px;
            min-height: 60px;
        }
        
        .price {
            font-size: 1.8rem;
            font-weight: bold;
            color: #141414ff;
            margin: 15px 0;
        }
        
        .shop-now-section {
            text-align: center;
            margin-top: 60px;
            position: relative;
            z-index: 1;
        }
        
        /* Contact Section with Background */
        .contact {
            background: 
                url('image/contact-bg.jpg') center/cover no-repeat fixed,
                linear-gradient(135deg, rgba(10, 10, 42, 0.15), rgba(26, 26, 58, 0.17));
            background-blend-mode: overlay;
            position: relative;
        }
        
        .contact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 60px;
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(46, 44, 44, 0.43);
            position: relative;
            z-index: 1;
        }
        
        .contact-form {
            background: rgba(232, 223, 223, 0.37);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .contact-info {
            background: rgba(255, 255, 255, 0.38);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ffffffff;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: Black;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4a9eff;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .info-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .info-item span {
            font-size: 1.5rem;
            margin-right: 15px;
            color: #4a9eff;
        }
        
        /* Buttons */
        .btn {
            padding: 15px 35px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            background-color: blue;
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(74, 158, 255, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
        }
        
        .btn-outline {
            border: 2px solid #ffffffff;
            color: #ffffffff;
            background: transparent;
        }
        
        .btn-outline:hover {
            background: #4a9eff;
            color: white;
        }
        
        .btn-block {
            width: 100%;
            text-align: center;
        }
        
        .btn-sm {
            padding: 10px 25px;
            font-size: 0.9rem;
        }
        
        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 60px 40px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: #4a9eff;
            transform: translateY(-3px);
        }
        
        /* Login Modal */
        .login-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .login-box {
            background: linear-gradient(135deg, #1a1a3a, #2a2a4a);
            padding: 50px;
            border-radius: 25px;
            width: 90%;
            max-width: 450px;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
        }
        
        /* Responsive */
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
            
            nav a {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            section {
                padding: 80px 20px;
            }
            
            .section-header h1 {
                font-size: 2.2rem;
            }
            
            .about-content,
            .products-grid,
            .contact-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .hero,
            .about-column,
            .product-card,
            .contact-form,
            .contact-info {
                padding: 30px 20px;
            }
        }
        
        /* Scroll Indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #fafafaff;
            animation: bounce 2s infinite;
            cursor: pointer;
        }
        
        .scroll-indicator span {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateX(-50%) translateY(0);}
            40% {transform: translateX(-50%) translateY(-10px);}
            60% {transform: translateX(-50%) translateY(-5px);}
        }
        
        /* Active Navigation */
        .nav-active {
            background: linear-gradient(45deg, #4a9eff, #0066ff);
            color: white !important;
        }
        
        /* Watch Animation */
        .watch-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .captcha-box {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #ddd;
    width: fit-content;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.captcha-box input[type="checkbox"] {
    transform: scale(1.3);
    cursor: pointer;
}
.auth-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
    overflow-y: auto; /* Make modal container scrollable */
    padding: 20px; /* Add padding for mobile */
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.auth-modal-content {
    background: white;
    border-radius: 20px;
    width: 90%;
    max-width: 450px;
    max-height: 90vh; /* Limit height to 90% of viewport */
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    animation: slideUp 0.3s ease;
    position: relative; /* For positioning */
}

@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Make the content inside scrollable */
.auth-tab-content {
    display: none;
    padding: 30px;
    max-height: 60vh; /* Limit tab content height */
    overflow-y: auto; /* Make tab content scrollable */
}

.auth-tab-content.active {
    display: block;
}

/* Scrollbar styling for auth modal */
.auth-tab-content::-webkit-scrollbar {
    width: 6px;
}

.auth-tab-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.auth-tab-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.auth-tab-content::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* For Firefox */
.auth-tab-content {
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

/* Mobile Responsive */
@media (max-width: 576px) {
    .auth-modal {
        padding: 10px;
        align-items: flex-start; /* Align to top on mobile */
    }
    
    .auth-modal-content {
        width: 95%;
        margin: 20px auto; /* Center with margin */
        max-height: 85vh; /* Slightly more height on mobile */
    }
    
    .auth-modal-header {
        padding: 20px;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .auth-tab-content {
        padding: 20px;
        max-height: calc(85vh - 150px); /* Account for header and footer */
    }
    
    /* Make sure form inputs are visible */
    .form-group input {
        font-size: 16px; /* Prevents iOS zoom on focus */
    }
}

/* Tablet and larger mobile */
@media (max-height: 700px) {
    .auth-modal-content {
        max-height: 95vh; /* Use more height on shorter screens */
    }
    
    .auth-tab-content {
        max-height: calc(95vh - 180px);
    }
}

/* Extra small devices */
@media (max-width: 400px) {
    .auth-modal-content {
        width: 98%;
        max-width: 350px;
        border-radius: 15px;
    }
    
    .auth-modal-header h2 {
        font-size: 1.5rem;
    }
    
    .auth-tabs {
        flex-direction: column;
    }
    
    .auth-tab {
        padding: 15px;
    }
    
    .auth-tab-content {
        padding: 15px;
    }
}
@keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.auth-modal-header {
    background: linear-gradient(135deg, #075aae 0%, #054a8c 100%);
    color: white;
    padding: 30px;
    text-align: center;
    position: relative;
}

.auth-modal-header h2 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.close-modal {
    position: absolute;
    top: 20px;
    right: 25px;
    font-size: 28px;
    cursor: pointer;
    color: white;
    transition: transform 0.3s;
}

.close-modal:hover {
    transform: scale(1.2);
}

.auth-tabs {
    display: flex;
    border-bottom: 1px solid #eaeaea;
    background: #f8f9fa;
}

.auth-tab {
    flex: 1;
    padding: 18px;
    background: none;
    border: none;
    font-size: 16px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.3s;
}

.auth-tab.active {
    color: #075aae;
    border-bottom: 3px solid #075aae;
    background: white;
}

.auth-tab-content {
    display: none;
    padding: 30px;
}

.auth-tab-content.active {
    display: block;
}

.auth-form {
    margin-top: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #eaeaea;
    border-radius: 10px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    border-color: #075aae;
    outline: none;
    box-shadow: 0 0 0 3px rgba(7, 90, 174, 0.1);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    font-size: 14px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.forgot-password {
    color: #075aae;
    text-decoration: none;
    font-weight: 500;
}

.forgot-password:hover {
    text-decoration: underline;
}

.form-agreement {
    margin: 20px 0;
    font-size: 14px;
    color: #666;
}

.form-agreement label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.form-agreement a {
    color: #075aae;
    text-decoration: none;
}

.form-agreement a:hover {
    text-decoration: underline;
}

.btn-auth {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #075aae 0%, #054a8c 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 20px;
}

.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(7, 90, 174, 0.2);
}

.auth-divider {
    display: flex;
    align-items: center;
    margin: 25px 0;
    color: #888;
}

.auth-divider::before,
.auth-divider::after {
    content: "";
    flex: 1;
    height: 1px;
    background: #eaeaea;
}

.auth-divider span {
    padding: 0 15px;
    font-size: 14px;
}

.btn-social {
    width: 100%;
    padding: 14px;
    margin-bottom: 12px;
    border: 2px solid #eaeaea;
    background: white;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-social:hover {
    border-color: #075aae;
    background: #f8fafd;
}

.btn-social i {
    font-size: 18px;
}

.btn-google {
    color: #DB4437;
}

.btn-facebook {
    color: #4267B2;
}

.auth-footer {
    padding: 20px 30px;
    text-align: center;
    border-top: 1px solid #eaeaea;
    background: #f8f9fa;
}

.auth-footer p {
    margin: 10px 0;
    color: #666;
}

.auth-footer a {
    color: #075aae;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.guest-option {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eaeaea;
}

/* Mobile Responsive */
@media (max-width: 576px) {
    .auth-modal-content {
        width: 95%;
        margin: 10px;
    }
    
    .auth-modal-header {
        padding: 20px;
    }
    
    .auth-tab-content {
        padding: 20px;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
    </style>
</head>
<body>
    <!-- Navigation -->
    <header>
        <div class="logo" id="login-btn">
    <img src="image/logo.png" alt="ChronoVerse Logo">
    ChronoVerse
</div>

        <nav>
            <ul>
                <li><a href="#home" class="nav-link active">HOME</a></li>
                <li><a href="#about" class="nav-link">ABOUT</a></li>
                <li><a href="#shop-preview" class="nav-link">SHOP</a></li>
                <li><a href="#contact" class="nav-link">CONTACT</a></li>
            </ul>
        </nav>
    </header>

   <section id="home" class="hero">
    <div class="hero-content">
        <h1>ChronoVerse – The Universe of Time</h1>
        <h3>Discover a Watch That Captures More Than Moments</h3>
        <p>
            ChronoVerse isn't just a timepiece — it's a universe on your wrist.
            With exposed gears, cosmic blue accents, and master-crafted mechanics,
            each watch reveals the beauty of time in motion.
        </p>

        <div class="buttons">
            <a href="#shop-preview" class="btn btn-primary">SHOP NOW</a>
            <a href="#about" class="btn btn-outline">LEARN MORE</a>
        </div>
    </div>

    <div class="scroll-indicator" onclick="scrollToSection('about')">
        ↓
        <span>Scroll to Explore</span>
    </div>
</section>


    <!-- About Section -->
    <section id="about" class="about">
        <div class="section-header">
            <h1>About ChronoVerse</h1>
            <p>Crafting Timepieces That Tell Stories</p>
        </div>
        <div class="about-content">
            <div class="about-column">
                <h2>MISSION</h2>
                <p>
                    At ChronoVerse, we believe time is more than just numbers on a dial. 
                    It's a journey, a story, a universe of moments waiting to be experienced. 
                    Our watches are designed not just to tell time, but to celebrate it.
                </p>
                <p>
                    Each piece combines traditional Swiss craftsmanship with futuristic design, 
                    creating timepieces that are both functional works of art and conversation starters.
                </p>
            </div>
            <div class="about-column">
                <h2>HISTORY</h2>
                <p>
                    Founded in 2026 by a team of watch enthusiasts and aerospace engineers, 
                    ChronoVerse was born from a simple idea: what if a watch could show time 
                    as it truly is – a beautiful, complex system of interconnected movements?
                </p>
                <p>
                    Our signature skeleton watches reveal the intricate mechanics that power
                    each moment, turning every tick into a story of precision, craftsmanship,
                    and artistry. Inspired by cosmic depth and engineered with modern innovation.
                </p>
            </div>
        </div>
    </section>

    <!-- Shop Preview Section -->
    <section id="shop-preview" class="shop-preview">
        <div class="section-header">
            <h1>Featured Timepieces</h1>
            <p>Preview Our Premium Collection</p>
        </div>
        <div class="products-grid">
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

        $price = number_format($row['price'], 2);

        $stock_status = $row['stock'] > 0 
            ? "In Stock ({$row['stock']})" 
            : "Out of Stock";

        $stock_class = $row['stock'] > 0 
            ? "in-stock" 
            : "out-of-stock";

            $image_path = !empty($row['featured_image']) 
            ? 'product/' . $row['featured_image'] 
            : './image/default-product.png';        

        $description = strlen($row['description']) > 100
            ? substr($row['description'], 0, 100) . '...'
            : $row['description'];
?>
<div class="product-card watch-float">

    <?php if(isset($row['category_name'])): ?>
        <span class="category-tag">
            <?php echo htmlspecialchars($row['category_name']); ?>
        </span>
    <?php endif; ?>

    <div class="product-image">
        <img src="<?php echo htmlspecialchars($image_path); ?>" 
             alt="<?php echo htmlspecialchars($row['name']); ?>"
             class="watch-img">
    </div>

    <h3><?php echo htmlspecialchars($row['name']); ?></h3>

    <p><?php echo htmlspecialchars($description); ?></p>

    <div class="price">$<?php echo $price; ?></div>

    <div class="stock-info <?php echo $stock_class; ?>">
        <?php echo $stock_status; ?>
    </div>

    <button class="btn btn-primary btn-sm"
       onclick="openAuthModal()">
        VIEW NOW
    </button>

</div>

<?php
    }
} else {
    echo "<p style='text-align:center; grid-column: 1/-1;'>No products found.</p>";
}

$conn->close();
?>
</div>

        
        <div class="shop-now-section">
            <h2 style="margin-bottom: 20px; color: #fff;">Ready to Explore More?</h2>
            <p style="margin-bottom: 30px; color: #a0c8ff; max-width: 600px; margin-left: auto; margin-right: auto;">
                Discover our complete collection featuring over 20 unique timepieces, 
                each crafted with precision and passion.
            </p>
            <!-- Change this button -->
<a href="javascript:void(0)" class="btn btn-secondary" style="padding: 15px 50px;" onclick="openAuthModal()">
    EXPLORE FULL COLLECTION
</a>
        </div>
    </section>
<!-- Login/Signup Modal -->
<div id="authModal" class="auth-modal" style="display: none;">
    <div class="auth-modal-content">
        <div class="auth-modal-header">
            <h2>Welcome to ChronoVerse</h2>
            <span class="close-modal" onclick="closeAuthModal()">&times;</span>
        </div>
        
        <div class="auth-tabs">
            <button class="auth-tab active" onclick="switchAuthTab('login')">Sign In</button>
            <button class="auth-tab" onclick="switchAuthTab('signup')">Sign Up</button>
        </div>
        
        <!-- Login Form -->
        <div id="loginTab" class="auth-tab-content active">
            <form id="autologinForm" class="auth-form">
                <div class="form-group">
                    <input type="email" id="loginEmail" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" id="loginPassword" placeholder="Password" required>
                </div>
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox"> Remember me
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-auth">Sign In</button>
                <div class="auth-divider">
                    <span>or continue with</span>
                </div>
                <button type="button" class="btn-social" onclick="loginWithGoogle()">
                    <i class="fab fa-google"></i> Google
                </button>
                <button type="button" class="btn-social" onclick="loginWithFacebook()">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
            </form>
        </div>
        
        <!-- Signup Form -->
        <div id="signupTab" class="auth-tab-content">
            <form id="signupForm" class="auth-form">
                <div class="form-group">
                    <input type="text" id="signupName" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="email" id="signupEmail" placeholder="Email Address" required>
                </div>
                <div class="form-group">
                    <input type="password" id="signupPassword" placeholder="Password (min. 8 characters)" required>
                </div>
                <div class="form-group">
                    <input type="password" id="signupConfirmPassword" placeholder="Confirm Password" required>
                </div>
                <div class="form-agreement">
                    <label>
                        <input type="checkbox" required>
                        I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
                    </label>
                </div>
                <button type="submit" class="btn-auth">Create Account</button>
                <div class="auth-divider">
                    <span>or sign up with</span>
                </div>
                <button type="button" class="btn-social" onclick="signupWithGoogle()">
                    <i class="fab fa-google"></i> Google
                </button>
                <button type="button" class="btn-social" onclick="signupWithFacebook()">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
            </form>
        </div>
        
        <div class="auth-footer">
            <p id="authSwitchText">
                Don't have an account? <a href="#" onclick="switchAuthTab('signup')">Sign up</a>
            </p>
            <p class="guest-option">
                <a href="#" onclick="continueAsGuest()">Continue as Guest</a>
            </p>
        </div>
    </div>
</div>
    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="section-header">
            <h1>Get In Touch</h1>
            <p>We'd Love to Hear From You</p>
        </div>
        <div class="contact-container">
        <div class="contact-form">
        <form id="contactForm">
    <div class="form-group">
        <small class="form-text">
            📝 Please enter your valid information here and we'll get back to you soon.
        </small>
    </div>

    <div class="form-group">
        <label for="name">Your Name *</label>
        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. John Doe" required>
    </div>

    <div class="form-group">
        <label for="email">Your Email *</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. john@example.com" required>
        <small class="form-text">
            We'll use this to contact you back
        </small>
    </div>

    <div class="form-group">
        <label for="message">Your Message *</label>
        <textarea id="message" name="message" class="form-control" rows="5" placeholder="Tell us about your concern..." required></textarea>
    </div>

    <!-- CAPTCHA Section -->
    <div id="captchaBox" style="margin-bottom: 15px;">
        <button type="button" id="verifyCaptchaBtn" 
            style="padding: 10px 20px; border: none; border-radius: 5px; background: #075aae; color: white; cursor: pointer;">
            <i class="fas fa-robot"></i> Verify if you're not a robot
        </button>
    </div>

    <!-- CAPTCHA Challenge -->
    <div id="captchaChallenge" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #ddd;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <img id="captchaImage" src="captcha/captcha.php" style="height: 40px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="button" id="refreshCaptcha" style="background: none; border: none; cursor: pointer; color: #075aae; font-size: 16px;" title="Refresh CAPTCHA">
                <i class="fas fa-redo-alt"></i>
            </button>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input type="text" id="captchaInput" class="form-control" placeholder="Enter the code you see" style="max-width: 200px;">
            <button type="button" id="submitCaptcha" style="padding: 8px 15px; border: none; border-radius: 4px; background: #28a745; color: white; cursor: pointer;">
                Verify
            </button>
        </div>
        <small class="form-text" style="display: block; margin-top: 5px; color: #666;">Type the characters you see in the image</small>
    </div>

    <button type="submit" class="btn btn-primary" id="submitBtn" disabled style="width:40%; padding: 16px; margin-top: 10px; margin-left:150px; ">
        Send Message
    </button>
</form>

</div>

            <div class="contact-info">
                <h3>Contact Information</h3>
                <div class="info-item">
                    <span>✉</span>
                    <div>support@chronoverse.com</div>
                </div>
                <div class="info-item">
                    <span>📞</span>
                    <div>+1 000-123-4567</div>
                </div>
                <div class="info-item">
                    <span>📍</span>
                    <div>EJ Blanco Ext. Drive Daro, Dumaguete City<br>Metro Dumaguete College, PH 6200</div>
                </div>
                <div class="info-item">
                    <span>🕒</span>
                    <div>Monday - Friday: 9AM - 6PM</div>
                </div>
            </div>
        </div>
    </section>

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
        <p>&copy; 2026 St4ngerDev. All rights reserved 2026.</p>
    </footer>

  <!-- Login Modal -->
<div class="login-modal" id="loginModal">
    <div class="login-box">
        <button class="close-modal" id="closeModal">×</button>
        <h2>Welcome Back</h2>
        <p class="tagline">Enter the Universe of Time</p>
        <form id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">LOGIN</button>
            <p style="text-align: center; margin-top: 25px;">
                Don't have an account? <a href="#" style="color: #4a9eff;">Sign up</a>
            </p>
        </form>
    </div>
</div>

    </div>

    <!-- Product Detail Modal -->
    <div class="login-modal" id="productModal">
        <div class="login-box" style="max-width: 600px;">
            <button class="close-modal" id="closeProductModal">×</button>
            <div id="productModalContent">
            </div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    // ===========================
    // NAVIGATION & SCROLL
    // ===========================
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if(this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);

                if(targetSection) {
                    navLinks.forEach(l => l.classList.remove('active', 'nav-active'));
                    this.classList.add('active', 'nav-active');

                    window.scrollTo({
                        top: targetSection.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    window.addEventListener('scroll', () => {
        const sections = document.querySelectorAll('section');
        let current = '';
        sections.forEach(section => {
            if(scrollY >= (section.offsetTop - 150)) current = section.getAttribute('id');
        });
        navLinks.forEach(link => {
            link.classList.remove('active', 'nav-active');
            if(link.getAttribute('href') === `#${current}`) link.classList.add('active', 'nav-active');
        });

        // Parallax effect
        const scrolled = window.pageYOffset;
        const aboutSection = document.querySelector('.about');
        const shopSection = document.querySelector('.shop-preview');
        if(aboutSection) aboutSection.style.backgroundPosition = `center ${scrolled * 0.5}px`;
        if(shopSection) shopSection.style.backgroundPosition = `center ${scrolled * 0.3}px`;
    });

    // ===========================
    // LOGIN MODAL
    // ===========================
    const loginBtn = document.getElementById('login-btn');
    const loginModal = document.getElementById('loginModal');
    const closeModalBtn = document.getElementById('closeModal');

    if (loginBtn && loginModal) {
        loginBtn.addEventListener('click', e => {
            e.preventDefault();
            loginModal.style.display = 'flex';
        });
        closeModalBtn.addEventListener('click', () => loginModal.style.display = 'none');
        window.addEventListener('click', e => { if(e.target === loginModal) loginModal.style.display = 'none'; });
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', e => {
            e.preventDefault();
            fetch('index.php', { method: 'POST', body: new FormData(loginForm) })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') window.location.href = data.redirect;
                    else alert(data.message);
                })
                .catch(err => console.error(err));
        });
    }

    // ===========================
    // PRODUCT MODAL
    // ===========================
    const productModal = document.getElementById('productModal');
    const closeProductModalBtn = document.getElementById('closeProductModal');

    function closeProductModal() {
        if(productModal) {
            productModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    if (productModal && closeProductModalBtn) {
        closeProductModalBtn.addEventListener('click', closeProductModal);
        productModal.addEventListener('click', e => { if(e.target === productModal) closeProductModal(); });
    }

    // ===========================
    // CAPTCHA IMPLEMENTATION
    // ===========================
    let captchaVerified = false;
    let captchaSessionSet = false;
    let autoResetTimer;

    const verifyCaptchaBtn = document.getElementById('verifyCaptchaBtn');
    const refreshCaptchaBtn = document.getElementById('refreshCaptcha');
    const submitCaptchaBtn = document.getElementById('submitCaptcha');
    const captchaInput = document.getElementById('captchaInput');
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');

    function refreshCaptchaImage() {
        const captchaImage = document.getElementById('captchaImage');
        if(captchaImage) captchaImage.src = 'captcha/captcha.php?' + new Date().getTime();
    }

    function showCaptchaChallenge() {
        const captchaChallenge = document.getElementById('captchaChallenge');
        if(captchaChallenge) {
            captchaChallenge.style.display = 'block';
            refreshCaptchaImage();
            if(captchaInput) captchaInput.focus();
        }
    }

    async function verifyCaptchaInput() {
        if(!captchaInput) return;
        const code = captchaInput.value.trim();
        if(!code) { alert('⚠️ Please enter the CAPTCHA code.'); captchaInput.focus(); return; }

        try {
            const formData = new FormData();
            formData.append('verify_captcha', true);
            formData.append('captcha_input', code);

            const response = await fetch('process/process_form.php', { method:'POST', body: formData });
            const result = await response.json();

            if(result.success) {
                alert('✅ ' + result.message);
                captchaVerified = true;
                captchaSessionSet = true;
                if(verifyCaptchaBtn) { verifyCaptchaBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verified'; verifyCaptchaBtn.style.background = '#28a745'; verifyCaptchaBtn.disabled = true; }
                if(document.getElementById('captchaChallenge')) document.getElementById('captchaChallenge').style.display = 'none';
                if(submitBtn) submitBtn.disabled = false;
                startAutoResetTimer();
            } else {
                alert('❌ ' + result.message);
                captchaVerified = false;
                captchaSessionSet = false;
                refreshCaptchaImage();
                captchaInput.value = '';
                captchaInput.focus();
            }
        } catch(error) {
            console.error('Error verifying CAPTCHA:', error);
            alert('❌ Could not verify CAPTCHA. Please try again.');
        }
    }

    function resetCaptcha() {
        captchaVerified = false;
        captchaSessionSet = false;
        if(verifyCaptchaBtn) { verifyCaptchaBtn.innerHTML = '<i class="fas fa-robot"></i> Verify if you\'re not a robot'; verifyCaptchaBtn.style.background = '#075aae'; verifyCaptchaBtn.disabled = false; }
        if(document.getElementById('captchaChallenge')) document.getElementById('captchaChallenge').style.display = 'none';
        if(captchaInput) captchaInput.value = '';
        if(submitBtn) submitBtn.disabled = true;
        if(autoResetTimer) { clearTimeout(autoResetTimer); autoResetTimer = null; }
    }

    function startAutoResetTimer() {
        if(autoResetTimer) clearTimeout(autoResetTimer);
        autoResetTimer = setTimeout(() => {
            if(captchaVerified) {
                resetCaptcha();
                alert('⚠️ CAPTCHA verification has expired. Please verify again.');
            }
        }, 30 * 60 * 1000);
    }

    // Attach CAPTCHA event listeners
    if(verifyCaptchaBtn) verifyCaptchaBtn.addEventListener('click', showCaptchaChallenge);
    if(refreshCaptchaBtn) refreshCaptchaBtn.addEventListener('click', e => { e.preventDefault(); refreshCaptchaImage(); if(captchaInput) captchaInput.focus(); });
    if(submitCaptchaBtn) submitCaptchaBtn.addEventListener('click', verifyCaptchaInput);
    if(captchaInput) captchaInput.addEventListener('keypress', e => { if(e.key==='Enter'){ e.preventDefault(); verifyCaptchaInput(); } });

    // ===========================
    // FORM SUBMISSION
    // ===========================
    if(form) {
        form.addEventListener('submit', async e => {
            e.preventDefault();

            if(!captchaVerified || !captchaSessionSet) { alert('⚠️ Please complete the CAPTCHA verification first.'); showCaptchaChallenge(); return; }

            const name = document.getElementById('name')?.value.trim();
            const email = document.getElementById('email')?.value.trim();
            const message = document.getElementById('message')?.value.trim();

            if(!name || !email || !message) { alert('❌ Please fill in all required fields.'); return; }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailRegex.test(email)) { alert('❌ Please enter a valid email address.'); return; }
            if(!submitBtn) return;

            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('name', name);
                formData.append('email', email);
                formData.append('message', message);

                const response = await fetch('process/process_form.php', { method:'POST', body: formData });
                const result = await response.json();

                if(result.success) {
                    alert('✅ ' + result.message);
                    form.reset();
                    resetCaptcha();
                } else {
                    alert('❌ ' + (result.message || 'Failed to send message. Please try again.'));
                    if(result.message && result.message.includes('CAPTCHA')) resetCaptcha();
                }
            } catch(error) {
                console.error('Error:', error);
                alert('❌ An error occurred while sending your message. Please try again.');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = !captchaVerified;
            }
        });
    }

    // ===========================
    // SHOP REDIRECT
    // ===========================
    window.redirectToShop = function(productType) {
        window.location.href = `shop.php?product=${productType}`;
    }

    // ===========================
    // ESC KEY FOR MODALS
    // ===========================
    document.addEventListener('keydown', e => {
        if(e.key === 'Escape') {
            if(loginModal && loginModal.style.display==='flex') loginModal.style.display='none';
            if(productModal && productModal.style.display==='flex') closeProductModal();
        }
    });
});

let isLoggedIn = false; // You can check from PHP session later

function openAuthModal() {
    // Check if user is already logged in (you should check PHP session here)
    <?php if(isset($_SESSION['user_id'])): ?>
        // If logged in, go directly to shop
        window.location.href = 'shop.php';
    <?php else: ?>
        // If not logged in, show modal
        document.getElementById('authModal').style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    <?php endif; ?>
}

function closeAuthModal() {
    document.getElementById('authModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function switchAuthTab(tab) {
    // Update tabs
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-tab-content').forEach(c => c.classList.remove('active'));
    
    // Activate selected tab
    if (tab === 'login') {
        document.querySelector('.auth-tab:first-child').classList.add('active');
        document.getElementById('loginTab').classList.add('active');
        document.getElementById('authSwitchText').innerHTML = 
            'Don\'t have an account? <a href="#" onclick="switchAuthTab(\'signup\')">Sign up</a>';
    } else {
        document.querySelector('.auth-tab:last-child').classList.add('active');
        document.getElementById('signupTab').classList.add('active');
        document.getElementById('authSwitchText').innerHTML = 
            'Already have an account? <a href="#" onclick="switchAuthTab(\'login\')">Sign in</a>';
    }
}

function continueAsGuest() {
    // Set a session/cookie for guest user
    document.cookie = "guest_mode=true; path=/; max-age=86400"; // 24 hours
    closeAuthModal();
    window.location.href = 'shop.php';
}

// Handle login form submission
document.getElementById('autologinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Add your login API call here
    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email, password: password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Login successful
            closeAuthModal();
            window.location.href = 'shop.php';
        } else {
            alert(data.message || 'Login failed. Please check your credentials.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Handle signup form submission
document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('signupName').value;
    const email = document.getElementById('signupEmail').value;
    const password = document.getElementById('signupPassword').value;
    const confirmPassword = document.getElementById('signupConfirmPassword').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    if (password.length < 8) {
        alert('Password must be at least 8 characters long!');
        return;
    }
    
    // Add your signup API call here
    fetch('signup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            name: name, 
            email: email, 
            password: password 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Signup successful, auto login
            closeAuthModal();
            window.location.href = 'shop.php';
        } else {
            alert(data.message || 'Signup failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

/* =========================
   GOOGLE – Continue with Google
   ========================= */
function loginWithGoogle() {
    google.accounts.id.initialize({
        client_id: "YOUR_GOOGLE_CLIENT_ID",
        callback: handleGoogleResponse,
        auto_select: true,
        cancel_on_tap_outside: false
    });

    google.accounts.id.prompt();
}

function handleGoogleResponse(response) {
    fetch("google-auth.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token: response.credential })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect ?? "dashboard.php";
        } else {
            alert(data.message || "Google login failed");
        }
    })
    .catch(() => alert("Network error"));
}

/* =========================
   FACEBOOK – Continue with Facebook
   ========================= */
window.fbAsyncInit = function () {
    FB.init({
        appId: "YOUR_FACEBOOK_APP_ID",
        cookie: true,
        xfbml: false,
        version: "v19.0"
    });
};

function loginWithFacebook() {
    FB.login(function (response) {
        if (!response.authResponse) return;

        fetch("facebook-auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                accessToken: response.authResponse.accessToken
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect ?? "dashboard.php";
            } else {
                alert("Facebook login failed");
            }
        });
    }, { scope: "email,public_profile" });
}


/* =========================
   SIGNUP WITH GOOGLE
   ========================= */
function signupWithGoogle() {
    loginWithGoogle(); // SAME FLOW
}

/* =========================
   SIGNUP WITH FACEBOOK
   ========================= */
function signupWithFacebook() {
    loginWithFacebook(); // SAME FLOW
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('authModal');
    if (event.target === modal) {
        closeAuthModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAuthModal();
    }
});
</script>

</body>
</html>