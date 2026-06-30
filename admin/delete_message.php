<?php
require '../dbconn.php';
header('Content-Type: application/json');

// Start session and check admin authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Message ID is required.']);
        exit;
    }

    if (!is_numeric($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid message ID format.']);
        exit;
    }

    $id = intval($_POST['id']);

    // Check if the message exists before deleting
    $check = $conn->prepare("SELECT id, email FROM messages WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Message not found or already deleted.']);
        $check->close();
        $conn->close();
        exit;
    }
    
    // Get message email for logging (optional)
    $check->bind_result($message_id, $message_email);
    $check->fetch();
    $check->close();

    // Proceed to delete
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Check if any rows were actually deleted
        if ($stmt->affected_rows > 0) {
            // Log the deletion (optional)
            error_log("Admin {$_SESSION['user_name']} deleted message ID: $id from email: $message_email");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Message deleted successfully.',
                'deleted_id' => $id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No message was deleted.']);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: Failed to delete message.',
            'error' => $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST requests are allowed.']);
}
?>