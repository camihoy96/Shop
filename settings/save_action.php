<?php
require 'dbconn.php';

if (isset($_POST['google_analytics'])) {
    $mode = intval($_POST['google_analytics']);
    $conn->query("UPDATE site_settings SET google_analytics = $mode WHERE id = 1");
    echo "google_analytics_success";
}

if (isset($_POST['social_sharing'])) {
    $mode = intval($_POST['social_sharing']);
    $conn->query("UPDATE site_settings SET social_sharing = $mode WHERE id = 1");
    echo "social_sharing_success";
}
?>
