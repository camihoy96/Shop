<?php
require 'dbconn.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location='../index.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $upload_dir = 'assets/images/avatars/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['avatar'];
    $file_name = $user_id . '.png'; // Save as user_id.png
    $file_path = $upload_dir . $file_name;
    
    // Check if file was uploaded successfully
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'File upload failed. ';
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= 'File too large.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= 'File upload was incomplete.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message .= 'No file was selected.';
                break;
            default:
                $error_message .= 'Upload error code: ' . $file['error'];
        }
        echo "<script>alert('❌ $error_message'); window.location='profile.php';</script>";
        exit;
    }
    
    // Check file size (2MB max)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo "<script>alert('❌ File too large. Maximum size allowed is 2MB.'); window.location='profile.php';</script>";
        exit;
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        echo "<script>alert('❌ Invalid file type. Only JPG, PNG, and GIF images are allowed.'); window.location='profile.php';</script>";
        exit;
    }
    
    // Process image
    try {
        switch ($file['type']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            default:
                throw new Exception('Unsupported image format');
        }
        
        if (!$image) {
            throw new Exception('Failed to create image from uploaded file');
        }
        
        // Create square image
        $width = imagesx($image);
        $height = imagesy($image);
        $size = min($width, $height);
        
        $square_image = imagecreatetruecolor(400, 400);
        
        // Add transparency support for PNG
        if ($file['type'] === 'image/png' || $file['type'] === 'image/gif') {
            imagealphablending($square_image, false);
            imagesavealpha($square_image, true);
            $transparent = imagecolorallocatealpha($square_image, 0, 0, 0, 127);
            imagefill($square_image, 0, 0, $transparent);
        }
        
        // Resize and crop to square
        imagecopyresampled($square_image, $image, 0, 0, 
                          ($width - $size) / 2, ($height - $size) / 2, 
                          400, 400, $size, $size);
        
        // Save as PNG
        if (imagepng($square_image, $file_path, 9)) {
            echo "<script>alert('✅ Profile picture updated successfully!'); window.location='profile.php';</script>";
        } else {
            throw new Exception('Failed to save image to server');
        }
        
        // Clean up
        imagedestroy($image);
        imagedestroy($square_image);
        
    } catch (Exception $e) {
        echo "<script>alert('❌ Error processing image: " . addslashes($e->getMessage()) . "'); window.location='profile.php';</script>";
    }
} else {
    echo "<script>alert('❌ No file was selected for upload.'); window.location='profile.php';</script>";
}
?>