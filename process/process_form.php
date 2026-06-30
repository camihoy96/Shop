<?php
session_set_cookie_params(0, "/");
session_start();

require '../dbconn.php';

header('Content-Type: application/json');

// Handle CAPTCHA verification request
if (isset($_POST['verify_captcha']) && isset($_POST['captcha_input'])) {
    $user_input = strtoupper(trim($_POST['captcha_input']));
    $session_captcha = isset($_SESSION['captcha']) ? strtoupper($_SESSION['captcha']) : '';
    
    if ($user_input === $session_captcha && !empty($session_captcha)) {
        $_SESSION['captcha_verified'] = true;
        echo json_encode([
            'success' => true,
            'message' => 'CAPTCHA verified successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CAPTCHA code. Please try again.'
        ]);
    }
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty($_SESSION['captcha_verified']) || $_SESSION['captcha_verified'] !== true) {
        echo json_encode([
            'success' => false,
            'message' => 'Please verify CAPTCHA before sending.'
        ]);
        exit;
    }

    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $message_id = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you soon.',
            'message_id' => $message_id
        ]);

        unset($_SESSION['captcha_verified']);
        unset($_SESSION['captcha']);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save your message. Please try again.'
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>
