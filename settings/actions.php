<?php
require 'dbconn.php';
session_start();

$action = $_POST['action'] ?? '';

if ($action === 'reset_settings') {
    // ✅ Reset site settings to default values
    $sql = "
        UPDATE site_settings SET
            site_title = 'My Website',
            subtitle = NULL,
            admin_email = 'admin@example.com',
            timezone = 'UTC',
            maintenance_mode = 0,
            user_registration = 1,
            email_notifications = 1,
            site_description = NULL,
            subdescription = NULL,
            address = 'Cadawinonan, Dumaguete City, Negros Oriental',
            phone = '09056152262',
            hero_image = NULL,
            google_analytics = 1,
            social_sharing = 1
        WHERE id = 1
    ";

    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => '✅ Settings have been reset to default values.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '❌ Failed to reset settings: ' . $conn->error]);
    }
    exit;
}

elseif ($action === 'delete_all') {
    // ⚠️ Delete all records EXCEPT users
    $tables = [
        'analytics_data',
        'conversation',
        'messages',
        'portfolio_files',
        'portfolio_items',
        'projects',
        'tasks',
        'visitors'
        // ❌ users table intentionally excluded
    ];

    // Disable foreign key checks to avoid constraint issues
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    $error = false;
    foreach ($tables as $table) {
        if (!$conn->query("DELETE FROM `$table`")) {
            $error = true;
            $errorMsg = $conn->error;
            break;
        }
    }

    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    if ($error) {
        echo json_encode([
            'status' => 'error',
            'message' => "❌ Something went wrong while deleting data: $errorMsg"
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => '🗑️ All records (except users) have been deleted successfully.'
        ]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
