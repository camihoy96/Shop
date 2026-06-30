<?php
require 'dbconn.php';

// ✅ Fetch current settings from the correct table
$settings_query = $conn->query("SELECT maintenance_mode FROM site_settings LIMIT 1");
$settings = $settings_query ? $settings_query->fetch_assoc() : ['maintenance_mode' => 0];

// ✅ If maintenance mode is ON, show maintenance page
if (!empty($settings['maintenance_mode'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance Mode</title>
        <style>
            body {
                text-align: center;
                padding: 50px;
                font-family: Arial, sans-serif;
                background: #f8f9fa;
                color: #333;
            }
            h1 {
                font-size: 50px;
                color: #e67e22;
            }
            p {
                font-size: 20px;
            }
        </style>
    </head>
    <body>
        <h1>⚙️ Site Under Maintenance</h1>
        <p>We’re performing updates to improve your experience.<br>Please check back soon.</p>
    </body>
    </html>
    <?php
    exit(); // stop the rest of index.php from loading
}
?>
<?php
require 'dbconn.php';

// ✅ Get category from URL
$category = $_GET['category'] ?? 'web-development';

$category_names = [
    'web-development' => 'Web Development',
    'application-development' => 'Application Development',
    'software-development' => 'Software Development',
    'it-services' => 'IT & Computer Services'
];

$category_name = $category_names[$category] ?? 'Our Projects';

// ✅ Fetch portfolio items for this category
$stmt = $conn->prepare("
    SELECT p.*, 
           (SELECT pf.file_path 
            FROM portfolio_files pf 
            WHERE pf.portfolio_id = p.id 
              AND pf.is_featured = 1 
              AND pf.file_type = 'image'
            LIMIT 1) AS featured_image
    FROM portfolio_items p 
    WHERE p.category = ? 
      AND p.status = 'published'
    ORDER BY p.featured DESC, p.created_at DESC
");
$stmt->bind_param("s", $category);
$stmt->execute();
$portfolio_result = $stmt->get_result();

// ✅ Load site settings separately
$settings_query = $conn->query("
    SELECT site_title, subtitle, site_description, subdescription, 
           admin_email, address, phone 
    FROM site_settings 
    WHERE id = 1
");

if ($settings_query && $settings_query->num_rows > 0) {
    $settings = $settings_query->fetch_assoc();
} else {
    // Fallback values if site_settings is empty
    $settings = [
        'site_title' => 'St4nger',
        'subtitle' => 'Development Services',
        'site_description' => "Let's take your business further — smarter, faster, and more productive.",
        'subdescription' => "We provide innovative digital solutions built to help you grow and succeed in the modern digital landscape.",
        'admin_email' => 'admin@example.com',
        'address' => 'Cadawinonan, Dumaguete City, Negros Oriental',
        'phone' => '09056152262'
    ];
}

// ✅ Fetch social sharing setting separately (avoid overwriting $settings)
$settingsResult = $conn->query("SELECT social_sharing FROM site_settings LIMIT 1");
if ($settingsResult && $settingsResult->num_rows > 0) {
    $socialSettings = $settingsResult->fetch_assoc();
    $settings = array_merge($settings, $socialSettings); // merge safely
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> | St4nger Devs</title>
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #075aae;
            --primary-dark: #054a8c;
            --primary-light: #1e7fd6;
            --secondary: #02CA02;
            --secondary-dark: #029a02;
            --accent: #ff6b00;
            --accent-light: #ff8c3a;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --gradient-primary: linear-gradient(135deg, #075aae 0%, #1e7fd6 100%);
            --gradient-secondary: linear-gradient(135deg, #02CA02 0%, #03e003 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, var(--white) 0%, #f0f4f8 100%);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
           margin: 10px;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: var(--shadow-sm);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        header.scrolled {
            box-shadow: var(--shadow);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .logo:hover {
            transform: scale(1.05);
        }

       .logo img {
            height: 70px;
            width:80px;
            margin-right: 12px;
            filter: brightness(0.6);
            transition: var(--transition);
        }

        .logo:hover img {
            filter: brightness(0.5) drop-shadow(0 0 10px rgba(7, 90, 174, 0.5));
        }

        .logo-text h1 {
            font-size: 1.6rem;
            margin-bottom: 0;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-text p {
            font-size: 0.85rem;
            margin-bottom: 0;
            color: var(--gray);
            font-weight: 500;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 5px;
        }

        nav a {
            font-weight: 600;
            color: var(--dark);
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 50px;
            transition: var(--transition);
            font-size: 0.95rem;
            position: relative;
        }

        nav a::before {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 60%;
            height: 2px;
            background: var(--gradient-primary);
            transition: transform 0.3s ease;
        }

        nav a:hover::before {
            transform: translateX(-50%) scaleX(1);
        }

        nav a:hover {
            color: var(--primary);
            background: linear-gradient(135deg, rgba(7, 90, 174, 0.1) 0%, rgba(30, 127, 214, 0.05) 100%);
        }

         .mobile-menu-btn {
            display: none;
            background: var(--gradient-primary);
            border: none;
            font-size: 1.3rem;
            cursor: pointer;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(7, 90, 174, 0.3);
            transition: var(--transition);
        }

        .mobile-menu-btn:hover {
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 6px 20px rgba(7, 90, 174, 0.4);
        }

        /* Hero Section */
        .portfolio-hero {
            background: var(--gradient-primary);
            color: var(--white);
            padding: 180px 0 100px;
            text-align: center;
            margin-top: 90px;
            position: relative;
            overflow: hidden;
        }

        .portfolio-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: pulse 8s ease-in-out infinite;
        }

        .portfolio-hero::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 80px;
            background: #f0f4f8;
            clip-path: polygon(0 50%, 100% 0, 100% 100%, 0 100%);
        }

        .portfolio-hero .container {
            position: relative;
            z-index: 1;
        }

        .portfolio-hero h1 {
            font-size: 3.5rem;
            margin-bottom: 24px;
            font-weight: 900;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease;
        }

        .portfolio-hero p {
            font-size: 1.3rem;
            max-width: 650px;
            margin: 0 auto;
            opacity: 0.95;
            animation: fadeInUp 0.8s ease 0.2s both;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Category Pills */
        .category-filter {
            background: white;
            padding: 30px 0;
            box-shadow: var(--shadow-sm);
            /*position: sticky;
            top: 80px;
            z-index: 100;*/ 
        }

        .category-pills {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .category-pill {
            padding: 12px 28px;
            background: var(--light);
            border: 2px solid transparent;
            border-radius: 50px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 600;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .category-pill:hover {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(7, 90, 174, 0.3);
        }

        .category-pill.active {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 6px 20px rgba(7, 90, 174, 0.4);
        }

        /* Portfolio Section */
        .portfolio-section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInUp 0.8s ease;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title h2::before {
            content: '';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #ff6b00 0%, #ff8c3a 100%);
            border-radius: 2px;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        .section-title p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 35px;
        }

        .portfolio-item {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 2px solid transparent;
            position: relative;
        }

        .portfolio-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
            z-index: 1;
        }

        .portfolio-item:hover::before {
            transform: scaleX(1);
        }

        .portfolio-item:hover {
            transform: translateY(-15px);
            box-shadow: var(--shadow-xl);
            border-color: rgba(7, 90, 174, 0.2);
        }

        .portfolio-image {
            height: 280px;
            overflow: hidden;
            position: relative;
            background: var(--light);
        }

        .portfolio-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .portfolio-item:hover .portfolio-image img {
            transform: scale(1.15);
        }

        .portfolio-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(7, 90, 174, 0.4) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .portfolio-item:hover .portfolio-image::after {
            opacity: 1;
        }

        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: var(--dark);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--light-gray) 0%, #e0e0e0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: var(--gray);
        }

        .image-placeholder i {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .portfolio-content {
            padding: 30px;
        }

        .portfolio-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--dark);
            line-height: 1.3;
        }

        .portfolio-description {
            color: var(--gray);
            margin-bottom: 20px;
            line-height: 1.8;
            font-size: 0.95rem;
        }

        .portfolio-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 14px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .portfolio-technologies {
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(7, 90, 174, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
        }

        .portfolio-client {
            color: var(--gray);
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .portfolio-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 10px;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 400;
            transition: var(--transition);
            cursor: pointer;
            text-align: center;
            flex: 1;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(7, 90, 174, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(7, 90, 174, 0.4);
            color: var(--white);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            box-shadow: none;
        }

        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray);
            animation: fadeInUp 0.8s ease;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 28px;
            color: #e9ecef;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 28px;
            margin-bottom: 16px;
            color: var(--dark);
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 32px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Scroll to top button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 55px;
            height: 55px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 6px 20px rgba(7, 90, 174, 0.4);
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 999;
        }

        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            transform: translateY(-8px) scale(1.1);
            box-shadow: 0 10px 30px rgba(7, 90, 174, 0.5);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #1a1a1a 0%, var(--dark) 100%);
            color: var(--white);
            padding: 80px 0 30px;
            position: relative;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 60px;
        }

        .footer-col h3 {
            color: var(--white);
            margin-bottom: 28px;
            font-size: 1.4rem;
            position: relative;
            padding-bottom: 15px;
        }

        .footer-col h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--gradient-primary);
        }

        .footer-col p {
            opacity: 0.85;
            line-height: 1.9;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 14px;
        }

        .footer-col ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            display: inline-block;
        }

        .footer-col ul li a:hover {
            color: var(--white);
            transform: translateX(8px);
        }

        .footer-col ul li i {
            margin-right: 12px;
            color: var(--primary-light);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.95rem;
            opacity: 0.8;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.active {
            display: flex;
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            border-bottom: 2px solid var(--light-gray);
            background: linear-gradient(135deg, var(--white), #f8f9fa);
            border-radius: 20px 20px 0 0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 700;
        }

        .close-btn {
            background: var(--light-gray);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--gray);
            transition: var(--transition);
        }

        .close-btn:hover {
            background: var(--danger);
            color: white;
            transform: rotate(90deg);
        }

        /* Smooth scrollbar for modal */
        .modal-content::-webkit-scrollbar {
            width: 10px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 20px 20px 0;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark), #054a8c);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .portfolio-hero h1 {
                font-size: 2.8rem;
            }

            .portfolio-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

       @media (max-width: 768px) {
            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            nav {
                position: fixed;
                top: 95px;
                left: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(15px);
                box-shadow: var(--shadow-lg);
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: var(--transition);
            }

            nav.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            nav ul {
                flex-direction: column;
                padding: 25px;
                gap: 0;
            }

            nav li {
                margin: 0 0 12px 0;
            }

            nav a {
                display: block;
                padding: 14px 18px;
            }

            .hero {
                padding: 160px 0 110px;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            section {
                padding: 70px 0;
            }

            .modal-content {
                padding: 40px 30px;
            }

            .stats-container {
                gap: 30px;
            }

            .stat-number {
                font-size: 3rem;
            }

            .technologies-logos {
                gap: 35px;
            }

            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
            }

            .portfolio-hero {
                padding: 150px 0 80px;
            }

            .portfolio-hero h1 {
                font-size: 2.3rem;
            }

            .portfolio-hero p {
                font-size: 1.1rem;
            }

            .portfolio-grid {
                grid-template-columns: 1fr;
            }

            .category-filter {
                top: 140px;
            }

            .portfolio-section {
                padding: 60px 0;
            }

            .modal {
                padding: 10px;
            }

            .modal-content {
                max-height: 95vh;
            }

            .modal-header {
                padding: 20px;
            }

            .modal-header h3 {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 576px) {
            .portfolio-hero {
                padding: 130px 0 70px;
            }

            .portfolio-hero h1 {
                font-size: 2rem;
            }

            .portfolio-actions {
                flex-direction: column;
            }

            .category-pills {
                gap: 10px;
            }

            .category-pill {
                padding: 10px 20px;
                font-size: 0.85rem;
            }

            .scroll-to-top {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
            }
        }
        .share-popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 15px 20px;
    z-index: 9999;
    animation: fadeIn 0.3s ease;
}
.share-popup .share-links a {
    display: block;
    margin: 8px 0;
    color: #333;
    text-decoration: none;
    font-weight: 500;
}
.share-popup .share-links a i {
    margin-right: 8px;
    color: #007bff;
}
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(10px);}
    to {opacity: 1; transform: translateY(0);}
}
/* 🔒 Disabled Share Button Style */
.share-project:disabled,
.share-project.disabled-share {
    background-color: #d6d6d6 !important;
    color: #777 !important;
    border-color: #bdbdbd !important;
    opacity: 0.7 !important;
    cursor: not-allowed !important;
    pointer-events: none;
}

    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="container header-container">
            <div class="logo" onclick="window.location.href='../index.php'">
                <img src="../image/logo.png" alt="St4nger Dev">
                <div class="logo-text">
                    <h1>St4nger</h1>
                    <p>Development Services</p>
                </div>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../index.php#about">About</a></li>
                    <li><a href="../index.php#services">Services</a></li>
                    <li><a href="../index.php#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="portfolio-hero">
        <div class="container">
            <h1><?php echo htmlspecialchars($category_name); ?></h1>
            <p>Explore our portfolio of successful projects and see how we've helped businesses transform their digital presence.</p>
       
        </div>
    </section>

    <!-- Category Filter -->
    <div class="category-filter">
        <div class="container">
            <div class="category-pills">
                <a href="?category=web-development" class="category-pill <?php echo $category === 'web-development' ? 'active' : ''; ?>">
                    🌐 Web Development
                </a>
                <a href="?category=application-development" class="category-pill <?php echo $category === 'application-development' ? 'active' : ''; ?>">
                    📱 App Development
                </a>
                <a href="?category=software-development" class="category-pill <?php echo $category === 'software-development' ? 'active' : ''; ?>">
                    💻 Software Development
                </a>
                <a href="?category=it-services" class="category-pill <?php echo $category === 'it-services' ? 'active' : ''; ?>">
                    🔧 IT Services
                </a>
            </div>
        </div>
    </div>

    <!-- Portfolio Section -->
    <section class="portfolio-section">
        <div class="container">
            <div class="section-title">
                <h2>Featured Projects</h2>
                <p>Discover our work in <?php echo htmlspecialchars($category_name); ?></p>
            </div>

            <?php if ($portfolio_result->num_rows > 0): ?>
    <div class="portfolio-grid">
        <?php while ($item = $portfolio_result->fetch_assoc()): ?>
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <?php 
                    $image_sql = "SELECT file_path FROM portfolio_files WHERE portfolio_id = ? AND file_type = 'image' ORDER BY is_featured DESC LIMIT 1";
                    $image_stmt = $conn->prepare($image_sql);
                    $image_stmt->bind_param("i", $item['id']);
                    $image_stmt->execute();
                    $image_result = $image_stmt->get_result();
                    
                    if ($image_result->num_rows > 0) {
                        $image = $image_result->fetch_assoc();
                        echo '<img src="' . htmlspecialchars($image['file_path']) . '" alt="' . htmlspecialchars($item['title']) . '" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                        echo '<div class="image-placeholder" style="display: none;">';
                        echo '<i class="fa-solid fa-image"></i>';
                        echo '<span>Image not found</span>';
                        echo '</div>';
                    } else {
                        echo '<div class="image-placeholder">';
                        echo '<i class="fa-solid fa-image"></i>';
                        echo '<span>No image</span>';
                        echo '</div>';
                    }
                    $image_stmt->close();
                    ?>
                    <?php if ($item['featured']): ?>
                        <div class="featured-badge">
                            <i class="fa-solid fa-star"></i> Featured
                        </div>
                    <?php endif; ?>
                </div>

                <div class="portfolio-content">
                    <h3 class="portfolio-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="portfolio-description"><?php echo htmlspecialchars($item['description']); ?></p>
                    
                    <div class="portfolio-meta">
                        <?php if (!empty($item['technologies'])): ?>
                            <div class="portfolio-technologies">
                                <i class="fa-solid fa-code"></i> 
                                <?php echo htmlspecialchars($item['technologies']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['client'])): ?>
                            <div class="portfolio-client">
                                <i class="fa-solid fa-building"></i> 
                                <?php echo htmlspecialchars($item['client']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="portfolio-actions">
    <?php if (!empty($item['project_url'])): ?>
        <a href="<?php echo htmlspecialchars($item['project_url']); ?>" target="_blank" class="btn">
            <i class="fa-solid fa-external-link-alt"></i> Live Demo
        </a>
    <?php endif; ?>

    <button class="btn btn-outline view-project-details" data-id="<?php echo $item['id']; ?>">
        <i class="fa-solid fa-eye"></i> View Details
    </button>

  <button class="btn btn-outline share-project"
    <?php echo ($settings['social_sharing'] == 0) ? 'disabled' : ''; ?>
    data-title="<?php echo htmlspecialchars($item['title']); ?>"
    data-url="http://127.0.0.1/for/portfolio/web-development.php?project_id=<?php echo $item['id']; ?>">
    <i class="fa-solid fa-share-nodes"></i> Share
</button>
</div>

                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fa-regular fa-folder-open"></i>
        <h3>No Projects Yet</h3>
        <p>We're currently working on some amazing <?php echo htmlspecialchars($category_name); ?> projects. Check back soon!</p>
        <a href="../index.php#contact" class="btn" style="margin-top: 20px;">
            <i class="fa-solid fa-paper-plane"></i> Start Your Project
        </a>
    </div>
<?php endif; ?>

        </div>
    </section>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>St4nger Dev</h3>
                    <p>Let's take your business further — smarter, faster, and more productive. Your trusted partner in digital transformation.</p>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../index.php#about">About Us</a></li>
                        <li><a href="../index.php#services">Services</a></li>
                        <li><a href="../index.php#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Our Services</h3>
                    <ul>
                        <li><a href="?category=web-development">Web Development</a></li>
                        <li><a href="?category=application-development">Application Development</a></li>
                        <li><a href="?category=software-development">Software Development</a></li>
                        <li><a href="?category=it-services">IT & Computer Services</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contact Info</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($settings['address']); ?></li>
                        <li><i class="fas fa-phone"></i><?php echo htmlspecialchars($settings['phone']); ?></li>
                        <li><i class="fas fa-envelope"></i><?php echo htmlspecialchars($settings['admin_email']); ?></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>Copyright © 2025 St4nger Dev | All Rights Reserved | Crafted with ❤️ in Dumaguete</p>
            </div>
        </div>
    </footer>

    <!-- Project Details Modal -->
    <div id="projectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="projectModalTitle">Project Details</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div id="projectModalContent">
                <!-- Project details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Header scroll effect
        const header = document.getElementById('header');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Scroll to Top Button
        const scrollToTopBtn = document.getElementById('scrollToTop');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });

        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Portfolio items scroll reveal
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.portfolio-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Project details modal - FIXED VERSION
        const projectModal = document.getElementById('projectModal');
        const projectModalTitle = document.getElementById('projectModalTitle');
        const projectModalContent = document.getElementById('projectModalContent');

        // Modal control functions
        function openModal() {
            projectModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            projectModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Utility functions
        function handleImageError(img) {
            img.style.display = 'none';
            const placeholder = document.createElement('div');
            placeholder.style.cssText = 'width: 100%; height: 300px; background: var(--light-gray); display: flex; align-items: center; justify-content: center; flex-direction: column; color: var(--gray); border-radius: 8px;';
            placeholder.innerHTML = '<i class="fa-solid fa-image" style="font-size: 48px; margin-bottom: 10px;"></i><span>Image not found</span>';
            img.parentNode.appendChild(placeholder);
        }

      // Replace the handleDocumentAction function with this improved version
// Enhanced handleDocumentAction function
function handleDocumentAction(filePath, fileName, fileType) {
    const docModal = document.createElement('div');
    docModal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 4000;
        padding: 20px;
    `;
    
    const fileExtension = fileName.split('.').pop().toLowerCase();
    const isWordDoc = ['doc', 'docx'].includes(fileExtension);
    const isPDF = fileExtension === 'pdf';
    
    let actionButtons = '';
    
    if (isPDF) {
        actionButtons = `
            <button onclick="openPDFPreview('${filePath}')" 
                    style="background: var(--primary); color: white; border: none; padding: 15px 20px; border-radius: 8px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="fa-solid fa-eye"></i> Preview PDF in Browser
            </button>
        `;
    } else if (isWordDoc) {
        actionButtons = `
            <button onclick="tryConvertWord('${filePath}', '${fileName}')" 
                    style="background: var(--primary); color: white; border: none; padding: 15px 20px; border-radius: 8px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 10px;">
                <i class="fa-solid fa-eye"></i> Try PDF Preview
            </button>
            <div style="background: var(--warning-light); padding: 12px; border-radius: 8px; text-align: left; font-size: 14px; color: var(--dark);">
                <i class="fa-solid fa-lightbulb" style="color: var(--warning); margin-right: 8px;"></i>
                <strong>Tip:</strong> You can also use free online tools like Google Docs or SmallPDF to view Word documents.
            </div>
        `;
    } else {
        actionButtons = `
            <div style="background: var(--info-light); padding: 12px; border-radius: 8px; text-align: left; font-size: 14px; color: var(--dark);">
                <i class="fa-solid fa-info-circle" style="color: var(--info); margin-right: 8px;"></i>
                Download the file to view it with appropriate software.
            </div>
        `;
    }
    
    docModal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 500px; width: 100%;">
            <div style="margin-bottom: 20px;">
                <i class="fa-solid fa-file-${isWordDoc ? 'word' : (isPDF ? 'pdf' : '')}" 
                   style="font-size: 64px; color: ${isWordDoc ? '#2b579a' : (isPDF ? '#f40f02' : '#6c757d')}; margin-bottom: 15px;"></i>
                <h3 style="color: var(--dark); margin-bottom: 10px;">${fileName}</h3>
                <p style="color: var(--gray);">${isWordDoc ? 'Word Document' : (isPDF ? 'PDF File' : 'Document')}</p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px;">
                ${actionButtons}
                
                <a href="${filePath}" download="${fileName}" 
                   style="background: var(--success); color: white; border: none; padding: 15px 20px; border-radius: 8px; cursor: pointer; font-size: 16px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i class="fa-solid fa-download"></i> Download File
                </a>
            </div>
            
            <button onclick="this.parentElement.parentElement.remove()" 
                    style="background: var(--light-gray); color: var(--dark); border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; width: 100%;">
                <i class="fa-solid fa-times"></i> Close
            </button>
        </div>
    `;
    
    docModal.addEventListener('click', (e) => {
        if (e.target === docModal) {
            docModal.remove();
        }
    });
    
    document.body.appendChild(docModal);
}

// Enhanced conversion function with better error handling
function tryConvertWord(filePath, fileName) {
    // Show loading state
    const loadingModal = document.createElement('div');
    loadingModal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 4001;
        padding: 20px;
    `;
    
    loadingModal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px; width: 100%;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 48px; color: var(--primary); margin-bottom: 20px;"></i>
            <h4 style="color: var(--primary); margin-bottom: 15px;">Processing Document</h4>
            <p style="color: var(--gray);">Attempting to convert "${fileName}" for preview...</p>
            <p style="color: var(--gray); font-size: 14px; margin-top: 10px;">This may take a few moments.</p>
        </div>
    `;
    
    document.body.appendChild(loadingModal);
    
    // Call the PHP conversion script
    fetch(`create_pdf_preview.php?file_path=${encodeURIComponent(filePath)}`)
        .then(response => response.json())
        .then(data => {
            loadingModal.remove();
            
            if (data.success) {
                // Open the converted PDF
                openPDFPreview(data.pdf_path);
            } else {
                // Show helpful error message with alternatives
                showConversionError(fileName, data.message);
            }
        })
        .catch(error => {
            loadingModal.remove();
            showConversionError(fileName, 'Network error: ' + error.message);
        });
}

// Function to show conversion error with helpful alternatives
function showConversionError(fileName, errorMessage) {
    const errorModal = document.createElement('div');
    errorModal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 4002;
        padding: 20px;
    `;
    
    errorModal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 500px; width: 100%;">
            <div style="margin-bottom: 20px;">
                <i class="fa-solid fa-exclamation-triangle" style="font-size: 64px; color: var(--warning); margin-bottom: 15px;"></i>
                <h4 style="color: var(--dark); margin-bottom: 10px;">Preview Not Available</h4>
                <p style="color: var(--gray);">Unable to generate preview for "${fileName}"</p>
            </div>
            
            <div style="text-align: left; background: var(--light); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin: 0 0 10px 0; color: var(--dark);"><strong>Error:</strong> ${errorMessage}</p>
                <p style="margin: 0; color: var(--dark); font-size: 14px;">
                    <strong>Alternative Solutions:</strong>
                </p>
                <ul style="margin: 10px 0 0 0; padding-left: 20px; color: var(--dark); font-size: 14px;">
                    <li>Download the file and open it with Microsoft Word</li>
                    <li>Use free alternatives like Google Docs or LibreOffice</li>
                    <li>Convert online using services like SmallPDF or ILovePDF</li>
                </ul>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                        style="background: var(--primary); color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer;">
                    <i class="fa-solid fa-arrow-left"></i> Try Another Option
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(errorModal);
}

// New function to handle Word document conversion and preview
function convertAndPreviewWord(filePath, fileName) {
    // Show loading state
    const loadingModal = document.createElement('div');
    loadingModal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 4001;
        padding: 20px;
    `;
    
    loadingModal.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; max-width: 400px; width: 100%;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 48px; color: var(--primary); margin-bottom: 20px;"></i>
            <h4 style="color: var(--primary); margin-bottom: 15px;">Converting Document</h4>
            <p style="color: var(--gray);">Converting "${fileName}" to PDF format...</p>
            <p style="color: var(--gray); font-size: 14px; margin-top: 10px;">This may take a few moments.</p>
        </div>
    `;
    
    document.body.appendChild(loadingModal);
    
    // Call the PHP conversion script
    fetch(`create_pdf_preview.php?file_path=${encodeURIComponent(filePath)}`)
        .then(response => response.json())
        .then(data => {
            loadingModal.remove();
            
            if (data.success) {
                // Open the converted PDF
                openPDFPreview(data.pdf_path);
            } else {
                // Show error message
                alert('Conversion failed: ' + data.message);
                console.error('Conversion error:', data);
            }
        })
        .catch(error => {
            loadingModal.remove();
            alert('Error converting document: ' + error.message);
            console.error('Conversion error:', error);
        });
}

// Enhanced PDF preview function
function openPDFPreview(pdfPath) {
    const pdfModal = document.createElement('div');
    pdfModal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.95);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 5000;
        padding: 20px;
    `;
    
    // Create a unique ID for this iframe to avoid conflicts
    const iframeId = 'pdf-preview-' + Date.now();
    
    pdfModal.innerHTML = `
        <div style="position: relative; width: 100%; height: 100%; max-width: 1200px; background: white; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
            <div style="background: var(--primary); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-size: 18px;">
                    <i class="fa-solid fa-file-pdf" style="margin-right: 10px;"></i>
                    PDF Preview
                </h4>
                <div style="display: flex; gap: 10px;">
                    <a href="${pdfPath}" download 
                       style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; text-decoration: none; display: flex; align-items: center; gap: 6px; font-size: 14px;">
                        <i class="fa-solid fa-download"></i> Download
                    </a>
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" 
                            style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 14px;">
                        <i class="fa-solid fa-times"></i> Close
                    </button>
                </div>
            </div>
            <iframe id="${iframeId}" 
                    src="${pdfPath}" 
                    style="width: 100%; height: 100%; border: none; flex: 1;"
                    title="PDF Preview">
            </iframe>
            <div style="background: var(--light-gray); padding: 10px 20px; text-align: center; color: var(--gray); font-size: 14px;">
                <i class="fa-solid fa-info-circle" style="margin-right: 8px;"></i>
                If the PDF doesn't load, you can <a href="${pdfPath}" download style="color: var(--primary); text-decoration: underline;">download it here</a>
            </div>
        </div>
    `;
    
    pdfModal.addEventListener('click', (e) => {
        if (e.target === pdfModal) {
            pdfModal.remove();
        }
    });
    
    // Add escape key listener
    const closeOnEsc = (e) => {
        if (e.key === 'Escape') {
            pdfModal.remove();
            document.removeEventListener('keydown', closeOnEsc);
        }
    };
    document.addEventListener('keydown', closeOnEsc);
    
    document.body.appendChild(pdfModal);
    
    // Focus on the modal for better accessibility
    setTimeout(() => {
        const iframe = document.getElementById(iframeId);
        if (iframe) {
            iframe.focus();
        }
    }, 100);
}
        function openImageModal(imageSrc) {
            const imageModal = document.createElement('div');
            imageModal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.95);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 3000;
                cursor: zoom-out;
                padding: 20px;
            `;
            
            imageModal.innerHTML = `
                <div style="position: relative; max-width: 95%; max-height: 95%;">
                    <img src="${imageSrc}" 
                         style="max-width: 100%; max-height: 90vh; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);"
                         onerror="this.src='../image/portfolio-placeholder.jpg'">
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="position: absolute; top: -15px; right: -15px; background: var(--danger); color: white; border: none; border-radius: 50%; width: 50px; height: 50px; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                        ×
                    </button>
                    <div style="position: absolute; bottom: -60px; left: 0; right: 0; text-align: center; color: white; font-size: 14px;">
                        <i class="fa-solid fa-arrows-alt" style="margin-right: 8px;"></i>
                        Click anywhere to close
                    </div>
                </div>
            `;
            
            imageModal.addEventListener('click', (e) => {
                if (e.target === imageModal) {
                    imageModal.remove();
                }
            });
            
            const closeOnEsc = (e) => {
                if (e.key === 'Escape') {
                    imageModal.remove();
                    document.removeEventListener('keydown', closeOnEsc);
                }
            };
            document.addEventListener('keydown', closeOnEsc);
            
            document.body.appendChild(imageModal);
        }

        // Main project details loading function
        function loadProjectDetails(projectId) {
            projectModalTitle.textContent = 'Loading Project...';
            projectModalContent.innerHTML = `
                <div style="padding: 60px; text-align: center;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 48px; color: var(--primary); margin-bottom: 20px;"></i>
                    <h4 style="color: var(--primary); margin-bottom: 15px;">Loading Project Details</h4>
                    <p>Please wait while we fetch the project information...</p>
                </div>
            `;
            
            openModal();
            
            fetch('get_project_details.php?id=' + projectId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayProjectData(data.project);
                    } else {
                        showErrorContent(data.message || 'Project not found');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showErrorContent('Failed to load project details. Please try again.');
                });
        }

        function displayProjectData(project) {
            projectModalTitle.textContent = project.title;
            
            let filesHTML = '';
            if (project.files && project.files.length > 0) {
                const imageFiles = project.files.filter(file => file.file_type === 'image');
                const documentFiles = project.files.filter(file => file.file_type !== 'image');
                
                filesHTML = `
                    <div style="margin-bottom: 30px;">
                        <h4 style="color: var(--primary); margin-bottom: 20px; border-bottom: 2px solid var(--light-gray); padding-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-images"></i> Project Gallery (${imageFiles.length + documentFiles.length} files)
                        </h4>
                        ${imageFiles.length > 0 ? `
                        <div style="margin-bottom: 30px;">
                            <h5 style="color: var(--primary-dark); margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-camera"></i> Screenshots & Images (${imageFiles.length})
                            </h5>
                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                ${imageFiles.map(file => `
                                    <div style="border: 1px solid var(--light-gray); border-radius: 12px; overflow: hidden; background: var(--white); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                        <div style="position: relative;">
                                            <img src="${file.file_path}" 
                                                 alt="${file.file_name}" 
                                                 style="width: 100%; height: 400px; object-fit: contain; background: var(--light); cursor: pointer; display: block;"
                                                 onclick="openImageModal('${file.file_path}')"
                                                 onerror="handleImageError(this)">
                                            ${file.is_featured ? `
                                            <span style="position: absolute; top: 15px; right: 15px; background: var(--success); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                                                <i class="fa-solid fa-star"></i> Featured
                                            </span>
                                            ` : ''}
                                        </div>
                                        <div style="padding: 15px 20px; border-top: 1px solid var(--light-gray);">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span style="font-weight: 500; color: var(--dark);">${file.file_name}</span>
                                                <button onclick="openImageModal('${file.file_path}')" 
                                                        style="background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                                                    <i class="fa-solid fa-expand"></i> View Full Size
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}
                        
                        ${documentFiles.length > 0 ? `
                        <div style="margin-bottom: 20px;">
                            <h5 style="color: var(--primary-dark); margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-file"></i> Project Documents (${documentFiles.length})
                            </h5>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                                ${documentFiles.map(file => {
                                    const fileExtension = file.file_name.split('.').pop().toLowerCase();
                                    const isWordDoc = ['doc', 'docx'].includes(fileExtension);
                                    const isPDF = fileExtension === 'pdf';
                                    
                                    return `
                                    <div style="border: 1px solid var(--light-gray); border-radius: 12px; padding: 25px; background: linear-gradient(135deg, var(--light), #f8f9fa); text-align: center; transition: all 0.3s ease; cursor: pointer;"
                                         onclick="handleDocumentAction('${file.file_path}', '${file.file_name}', '${file.file_type}')">
                                        <div style="margin-bottom: 15px;">
                                            <i class="fa-solid fa-file-${isWordDoc ? 'word' : 'pdf'}" 
                                               style="font-size: 52px; color: ${isWordDoc ? '#2b579a' : '#f40f02'}; margin-bottom: 10px;"></i>
                                        </div>
                                        <div style="font-weight: 600; color: var(--dark); margin-bottom: 8px; word-break: break-word; font-size: 15px;">
                                            ${file.file_name}
                                        </div>
                                        <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 12px;">
                                            <span style="background: ${isWordDoc ? '#2b579a' : '#f40f02'}; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 500;">
                                                ${isWordDoc ? 'WORD' : 'PDF'}
                                            </span>
                                            ${isPDF ? `
                                            <span style="background: var(--success); color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 500;">
                                                PREVIEW AVAILABLE
                                            </span>
                                            ` : `
                                            <span style="background: var(--warning); color: var(--dark); padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 500;">
                                                DOWNLOAD TO VIEW
                                            </span>
                                            `}
                                        </div>
                                        <small style="color: var(--gray); font-size: 13px; display: block;">
                                            <i class="fa-solid fa-mouse-pointer"></i> Click to view options
                                        </small>
                                    </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
            }
            
            const featuredImagePath = project.featured_image || '../image/portfolio-placeholder.jpg';
            
            projectModalContent.innerHTML = `
                <div style="padding: 20px;">
                    <div style="margin-bottom: 30px; text-align: center;">
                        <div style="position: relative; display: inline-block; width: 100%; max-width: 800px;">
                            <img src="${featuredImagePath}" 
                                 alt="${project.title}" 
                                 style="width: 100%; max-height: 500px; object-fit: contain; border-radius: 12px; background: var(--light); padding: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); cursor: pointer;"
                                 onclick="openImageModal('${featuredImagePath}')"
                                 onerror="this.src='../image/portfolio-placeholder.jpg'">
                            ${project.featured_image ? `
                            <div style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.9); color: var(--dark); padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 6px; backdrop-filter: blur(10px);">
                                <i class="fa-solid fa-star" style="color: gold;"></i> Featured Image
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <div style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <i class="fa-solid fa-layer-group" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <strong style="display: block; margin-bottom: 5px;">Category</strong>
                            <span style="font-size: 14px;">${project.category ? project.category.replace(/-/g, ' ').toUpperCase() : 'N/A'}</span>
                        </div>
                        
                        ${project.completion_date ? `
                        <div style="background: linear-gradient(135deg, var(--success), #1e7e34); color: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <i class="fa-solid fa-calendar-check" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <strong style="display: block; margin-bottom: 5px;">Completed</strong>
                            <span style="font-size: 14px;">${new Date(project.completion_date).toLocaleDateString()}</span>
                        </div>
                        ` : ''}
                        
                        ${project.featured ? `
                        <div style="background: linear-gradient(135deg, var(--warning), #e0a800); color: var(--dark); padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <i class="fa-solid fa-star" style="font-size: 24px; margin-bottom: 10px; color: gold;"></i>
                            <strong style="display: block; margin-bottom: 5px;">Featured</strong>
                            <span style="font-size: 14px;">Premium Project</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div style="margin-bottom: 30px;">
                        <h4 style="color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px; font-size: 22px;">
                            <i class="fa-solid fa-file-lines"></i> Project Description
                        </h4>
                        <p style="line-height: 1.8; background: var(--light); padding: 25px; border-radius: 10px; border-left: 5px solid var(--primary); font-size: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            ${project.description || 'No description available.'}
                        </p>
                    </div>
                    
                    ${project.technologies ? `
                    <div style="margin-bottom: 30px;">
                        <h4 style="color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px; font-size: 22px;">
                            <i class="fa-solid fa-code"></i> Technologies Used
                        </h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                            ${project.technologies.split(',').map(tech => `
                                <span style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; padding: 10px 20px; border-radius: 25px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                        <i class="fa-solid fa-hashtag"></i> ${tech.trim()}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                    
                    ${project.client ? `
                    <div style="margin-bottom: 30px;">
                        <h4 style="color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px; font-size: 22px;">
                            <i class="fa-solid fa-building"></i> Client
                        </h4>
                        <div style="background: var(--light); padding: 20px; border-radius: 10px; display: inline-flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <i class="fa-solid fa-user-tie" style="font-size: 24px; color: var(--primary);"></i>
                            <span style="font-size: 16px; font-weight: 500;">${project.client}</span>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${filesHTML}
                    
                    ${project.project_url ? `
                    <div style="margin-top: 40px; text-align: center; padding-top: 30px; border-top: 3px solid var(--light-gray);">
                        <a href="${project.project_url}" target="_blank" 
                           style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 32px; font-size: 18px; background: linear-gradient(135deg, var(--secondary), #028a02); border: none; border-radius: 50px; text-decoration: none; color: white; font-weight: 600; box-shadow: 0 6px 20px rgba(2, 202, 2, 0.3); transition: transform 0.3s ease;">
                            <i class="fa-solid fa-external-link-alt"></i> View Live Project
                        </a>
                        <p style="color: var(--gray); margin-top: 15px; font-size: 14px;">
                            <i class="fa-solid fa-lightbulb"></i> Click to explore the live version of this project
                        </p>
                    </div>
                    ` : ''}
                </div>
            `;
        }

// ===============================
// 📤 Handle "Share" Button Click
// ===============================
document.querySelectorAll('.share-project').forEach(button => {
    button.addEventListener('click', () => {
        // Skip if disabled
        if (button.disabled) {
            alert('⚠️ Social sharing is currently disabled by the admin.');
            return;
        }

        const title = button.getAttribute('data-title');
        const url = button.getAttribute('data-url') || window.location.href;

        // ✅ Use native Web Share API (Mobile-friendly)
        if (navigator.share) {
            navigator.share({
                title: title,
                text: 'Check out this awesome project!',
                url: url
            })
            .then(() => console.log('Project shared successfully'))
            .catch(err => console.error('Share failed:', err));
        } 
        // ✅ Fallback for browsers without Web Share API
        else {
            const shareLinks = `
                <div class="share-links">
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank">
                        <i class="fa-brands fa-facebook"></i> Facebook
                    </a>

                    <!-- Messenger -->
                    <a href="https://www.facebook.com/dialog/send?link=${encodeURIComponent(url)}&app_id=YOUR_APP_ID&redirect_uri=${encodeURIComponent(url)}" target="_blank">
                        <i class="fa-brands fa-facebook-messenger"></i> Messenger
                    </a>

                    <!-- X (Twitter) -->
                    <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" target="_blank">
                        <i class="fa-brands fa-x-twitter"></i> X (Twitter)
                    </a>

                    <!-- LinkedIn -->
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}" target="_blank">
                        <i class="fa-brands fa-linkedin"></i> LinkedIn
                    </a>

                    <!-- Copy Link -->
                    <a href="#" id="copyLink">
                        <i class="fa-solid fa-copy"></i> Copy Link
                    </a>
                </div>
            `;

            // ✅ Create popup
            const container = document.createElement('div');
            container.className = 'share-popup';
            container.innerHTML = `
                <div class="share-popup-content">
                    <h4>Share this project</h4>
                    ${shareLinks}
                    <button class="close-share">Close</button>
                </div>
            `;
            document.body.appendChild(container);

            // ✅ Copy link to clipboard
            container.querySelector('#copyLink').addEventListener('click', e => {
                e.preventDefault();
                navigator.clipboard.writeText(url);
                alert('🔗 Link copied to clipboard!');
            });

            // ✅ Close popup manually or auto-remove
            container.querySelector('.close-share').addEventListener('click', () => container.remove());
            setTimeout(() => container.remove(), 10000);
        }
    });
});

// ===============================
// ⚙️ Social Sharing Toggle Control
// ===============================
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('input[name="social_sharing"]');
    const shareButtons = document.querySelectorAll('.share-project');

    if (!toggle) return; // Only runs in admin panel

    toggle.addEventListener('change', function() {
        const enabled = this.checked;

        shareButtons.forEach(btn => {
            btn.disabled = !enabled;
            btn.classList.toggle('disabled-share', !enabled);
            btn.style.opacity = enabled ? '1' : '0.7';
            btn.style.cursor = enabled ? 'pointer' : 'not-allowed';
            btn.title = enabled ? 'Share this project' : 'Social sharing is disabled by admin';
        });

        // Optional: update DB instantly
        fetch('save_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'social_sharing=' + (enabled ? 1 : 0)
        })
        .then(res => res.text())
        .then(data => console.log('Social Sharing updated:', data))
        .catch(err => console.error('Error:', err));
    });
});


        function showErrorContent(message) {
            projectModalContent.innerHTML = `
                <div style="padding: 40px; text-align: center;">
                    <i class="fa-solid fa-exclamation-triangle" style="font-size: 48px; color: var(--danger); margin-bottom: 15px;"></i>
                    <h4 style="color: var(--danger); margin-bottom: 10px;">Error Loading Project</h4>
                    <p>${message}</p>
                    <button onclick="closeModal()" style="margin-top: 20px; padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer;">
                        Close
                    </button>
                </div>
            `;
        }

        // Event listeners
        document.querySelectorAll('.view-project-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const projectId = this.getAttribute('data-id');
                loadProjectDetails(projectId);
            });
        });

        document.querySelector('#projectModal .close-btn').addEventListener('click', closeModal);

        window.addEventListener('click', (e) => {
            if (e.target === projectModal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && projectModal.classList.contains('active')) {
                closeModal();
            }
        });

        console.log('Portfolio page loaded with enhanced modal functionality!');
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>