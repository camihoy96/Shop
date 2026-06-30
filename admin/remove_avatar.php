<?php
require 'dbconn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location='../index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$avatar_path = "assets/images/avatars/{$user_id}.png";

if (file_exists($avatar_path)) {
    if (unlink($avatar_path)) {
        echo "<script>alert('✅ Profile picture removed successfully!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to remove profile picture. Please try again.'); window.location='profile.php';</script>";
    }
} else {
    echo "<script>alert('ℹ️ No profile picture found to remove.'); window.location='profile.php';</script>";
}
?>