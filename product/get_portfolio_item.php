<?php
session_start();
require 'dbconn.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No project ID provided']);
    exit;
}

$project_id = intval($_GET['id']);

try {
    // Get project details
    $stmt = $conn->prepare("SELECT * FROM portfolio_items WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
        
        // Get all project files
        $files_stmt = $conn->prepare("
            SELECT id, file_path, file_type, file_name, is_featured 
            FROM portfolio_files 
            WHERE portfolio_id = ? 
            ORDER BY is_featured DESC, created_at ASC
        ");
        $files_stmt->bind_param("i", $project_id);
        $files_stmt->execute();
        $files_result = $files_stmt->get_result();
        
        $files = [];
        while ($file = $files_result->fetch_assoc()) {
            $files[] = $file;
        }
        $files_stmt->close();
        
        // Get featured image
        $featured_image = '';
        foreach ($files as $file) {
            if ($file['is_featured'] == 1 && $file['file_type'] === 'image') {
                $featured_image = $file['file_path'];
                break;
            }
        }
        
        // If no featured image found, use the first image
        if (empty($featured_image)) {
            foreach ($files as $file) {
                if ($file['file_type'] === 'image') {
                    $featured_image = $file['file_path'];
                    break;
                }
            }
        }
        
        $item = [
            'id' => $project['id'],
            'title' => $project['title'],
            'description' => $project['description'],
            'category' => $project['category'],
            'project_url' => $project['project_url'],
            'technologies' => $project['technologies'],
            'client' => $project['client'],
            'completion_date' => $project['completion_date'],
            'featured' => $project['featured'],
            'status' => $project['status'],
            'featured_image' => $featured_image,
            'files' => $files
        ];
        
        echo json_encode(['success' => true, 'item' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Get Portfolio Item Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading project data: ' . $e->getMessage()]);
}

$conn->close();
?>