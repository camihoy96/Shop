<?php
session_start();
require 'dbconn.php';

// Set JSON header for AJAX responses
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $type = $_POST['type'] ?? 'user';
    
    // Validation
    if (empty($name) || strlen($name) < 2) {
        $response['errors']['name'] = "Full name must be at least 2 characters long.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Please enter a valid email address.";
    }

    if (empty($password) || strlen($password) < 6) {
        $response['errors']['password'] = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $response['errors']['confirm_password'] = "Passwords do not match.";
    }

    if (empty($type) || !in_array($type, ['admin', 'user'])) {
        $response['errors']['type'] = "Please select a valid account type.";
    }

    // Check email duplicates (only if no validation errors)
    if (empty($response['errors'])) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['errors']['email'] = "An account with this email already exists.";
            }
            $stmt->close();
        } else {
            $response['errors']['general'] = "Database preparation failed.";
        }
    }

    // Insert new user
    if (empty($response['errors'])) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, type) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $type);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Account created successfully!";
            } else {
                $response['errors']['general'] = "Failed to create account. Please try again.";
            }
            $stmt->close();
        } else {
            $response['errors']['general'] = "Database error. Please try again.";
        }
    }
} else {
    $response['errors']['general'] = "Invalid request method.";
}

// Return JSON response
echo json_encode($response);
exit;
?>