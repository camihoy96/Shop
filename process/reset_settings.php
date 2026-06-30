<?php
require 'dbconn.php';

// Example: reset the settings table
$defaultSettings = [
    'site_title' => 'My Website',
    'maintenance_mode' => '0',
    'theme' => 'light'
];

foreach ($defaultSettings as $key => $value) {
    $conn->query("UPDATE settings SET value='$value' WHERE name='$key'");
}

echo "<script>alert('✅ Settings have been reset to defaults.'); window.location.href='settings.php';</script>";
?>
