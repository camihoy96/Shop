<?php
require 'dbconn.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location='../index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use session user_id for security
    $id = $_SESSION['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($new_password) || empty($confirm_password)) {
        echo "<script>alert('❌ Please fill in all password fields.'); window.location='profile.php';</script>";
        exit;
    }
    
    // Check password strength
    if (strlen($new_password) < 6) {
        echo "<script>alert('❌ Password must be at least 6 characters long.'); window.location='profile.php';</script>";
        exit;
    }
    
    // Check if passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>alert('⚠️ Passwords do not match.'); window.location='profile.php';</script>";
        exit;
    }
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Use prepared statement to update password
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('✅ Password changed successfully!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to change password: " . $conn->error . "'); window.location='profile.php';</script>";
    }
    
    $stmt->close();
} else {
    echo "<script>alert('❌ Invalid request method.'); window.location='profile.php';</script>";
}
?>