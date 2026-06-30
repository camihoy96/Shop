<?php
require '../dbconn.php';
header('Content-Type: application/json');

// Start session and check admin authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_POST['type'])) {
        $id = intval($_POST['id']);
        $type = $_POST['type']; // 'user' or 'admin'

        // Determine which table to delete from based on message type
        if ($type === 'user') {
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
        } elseif ($type === 'admin') {
            $stmt = $conn->prepare("DELETE FROM conversation WHERE id = ?");
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid message type.']);
            exit;
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Check if any rows were actually deleted
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Message deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Message not found or already deleted.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters: id and type.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>