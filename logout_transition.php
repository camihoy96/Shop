<?php
session_start();
session_destroy();

// If you want to keep the transition page separate, use the code above
// If you want everything in one file, use this version:

// Set a flag to show we're logged out but don't redirect immediately
$logged_out = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        
        .logout-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .logo {
            margin-bottom: 20px;
        }
        
        .logo svg {
            width: 60px;
            height: 60px;
            fill: #6a11cb;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #6a11cb;
            border-radius: 50%;
            margin: 0 auto 30px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .redirect-message {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .redirect-message a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: 500;
        }
        
        .redirect-message a:hover {
            text-decoration: underline;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 0.8rem;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logo">
            <svg viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
            </svg>
        </div>
        
        <h1>Logging Out</h1>
        <p>You are being securely logged out of your account. Please wait...</p>
        
        <div class="spinner"></div>
        
        <div class="redirect-message">
            You will be redirected to the login page in a few seconds. 
            <br>If not, <a href="index.php">click here</a>.
        </div>
        
        <div class="footer">
            &copy; <?php echo date('Y'); ?> Your Company Name. All rights reserved.
        </div>
    </div>

    <script>
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = "index.php";
        }, 3000);
    </script>
</body>
</html>