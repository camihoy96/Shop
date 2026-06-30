<?php
session_start();
require 'dbconn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_messages'] = ['Please login first.'];
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    $errors = [];
    
    // Validate
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Check if email is already taken by another user
    $check_sql = "SELECT id FROM new_users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $errors[] = "Email is already in use by another account.";
    }
    $check_stmt->close();
    
    if (empty($errors)) {
        $update_sql = "UPDATE new_users SET 
            name = ?, 
            email = ?, 
            phone = ?, 
            street = ?, 
            barangay = ?, 
            city = ?, 
            province = ?, 
            zip_code = ?, 
            country = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssssssssi", 
            $name, $email, $phone, $street, $barangay, 
            $city, $province, $zip_code, $country, $user_id
        );
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully!";
        } else {
            $_SESSION['error_messages'] = ["Error updating profile. Please try again."];
        }
        
        $update_stmt->close();
    } else {
        $_SESSION['error_messages'] = $errors;
    }
    
    $conn->close();
    header("Location: profile.php");
    exit;
}
?>