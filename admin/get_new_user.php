<?php
session_start();
require 'dbconn.php';

// Get user ID from request
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId > 0) {
    $query = "SELECT * FROM new_users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
}

$conn->close();
?>