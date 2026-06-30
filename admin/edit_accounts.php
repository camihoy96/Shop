<?php
session_start();
require 'dbconn.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- VALIDATION ---
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Full name must be at least 2 characters long.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Full name must not exceed 100 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }
    }

    // --- CHECK EMAIL DUPLICATES (excluding current user) ---
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email already exists.";
        }
        $stmt->close();
    }

    // --- UPDATE USER ---
    if (empty($errors)) {
        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Error updating account: " . $stmt->error;
        }
        $stmt->close();
    }
}

// --- RETURN JSON RESPONSE ---
header('Content-Type: application/json');
if ($success) {
    echo json_encode(['success' => true, 'message' => 'Account updated successfully!']);
} else {
    echo json_encode(['success' => false, 'errors' => $errors]);
}
exit;
?>