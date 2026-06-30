<?php
require 'dbconn.php';

// ✅ Check if 'email' parameter exists
if (!isset($_GET['email']) || empty($_GET['email'])) {
    http_response_code(400); // Bad request
    echo "Missing email parameter.";
    exit;
}

$email = $conn->real_escape_string($_GET['email']);

// ✅ Update read status in messages table (optional)
$conn->query("UPDATE messages SET is_read = 1 WHERE email='$email'");

// ✅ Update read status in gmail_replies table (if applicable)
$conn->query("UPDATE gmail_replies SET is_read = 1 WHERE sender_email='$email'");

// ✅ Confirm success
if ($conn->affected_rows > 0) {
    echo "Messages marked as read.";
} else {
    echo "No unread messages found for this email.";
}
?>
