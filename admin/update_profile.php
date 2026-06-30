<?php
require 'dbconn.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location='../index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use session user_id for security instead of POST data
    $id = $_SESSION['user_id'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    
    // Validate inputs
    if (empty($name) || empty($email)) {
        echo "<script>alert('❌ Please fill in all fields.'); window.location='profile.php';</script>";
        exit;
    }
    
    // Check if email already exists (excluding current user)
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo "<script>alert('❌ Email already exists. Please use a different email.'); window.location='profile.php';</script>";
        exit;
    }
    
    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $email, $id);
    
    if ($stmt->execute()) {
        // Update session name if changed
        $_SESSION['user_name'] = $name;
        echo "<script>alert('✅ Profile updated successfully!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to update profile: " . $conn->error . "'); window.location='profile.php';</script>";
    }
    
    $stmt->close();
} else {
    echo "<script>alert('❌ Invalid request method.'); window.location='profile.php';</script>";
}
?>