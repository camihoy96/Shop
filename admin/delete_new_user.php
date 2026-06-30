<?php
session_start();
require 'dbconn.php';

if (isset($_GET['id'])) {
    $user_id_to_delete = intval($_GET['id']);
    
    $delete_query = "DELETE FROM new_users WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $user_id_to_delete);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = [
            'text' => "User deleted successfully!",
            'type' => 'success'
        ];
    } else {
        $_SESSION['flash_message'] = [
            'text' => "Error deleting user. Please try again.",
            'type' => 'error'
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    // Redirect back to user management page
    header("Location: user.php");
    exit();
}
?>