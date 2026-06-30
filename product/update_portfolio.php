<?php
session_start();
require 'dbconn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$item_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$category = trim($_POST['category']);
$project_url = trim($_POST['project_url'] ?? '');
$technologies = trim($_POST['technologies'] ?? '');
$client = trim($_POST['client'] ?? '');
$completion_date = $_POST['completion_date'] ?? null;
$featured = isset($_POST['featured']) ? 1 : 0;
$status = isset($_POST['status']) ? 'published' : 'draft';

// Validate required fields
if (empty($title) || empty($description) || empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

try {
    // Update portfolio item
    $update_sql = "UPDATE portfolio_items SET 
                  title = ?, description = ?, category = ?, project_url = ?, 
                  technologies = ?, client = ?, completion_date = ?, 
                  featured = ?, status = ?, updated_at = NOW() 
                  WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssssisi", 
        $title, $description, $category, $project_url,
        $technologies, $client, $completion_date, 
        $featured, $status, $item_id
    );
    
    if ($update_stmt->execute()) {
        // Handle file uploads if any
        if (!empty($_FILES['project_images']['name'][0])) {
            $upload_dir = '../uploads/portfolio/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            foreach ($_FILES['project_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['project_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = basename($_FILES['project_images']['name'][$key]);
                    $file_tmp = $_FILES['project_images']['tmp_name'][$key];
                    $file_size = $_FILES['project_images']['size'][$key];
                    $file_type = $_FILES['project_images']['type'][$key];
                    
                    // Generate unique filename
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_filename = 'portfolio_' . $item_id . '_' . uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    // Validate file type
                    $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $allowed_doc_types = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'];
                    
                    if (in_array($file_type, $allowed_image_types) || in_array($file_type, $allowed_doc_types)) {
                        // Move uploaded file
                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            $is_featured = 0; // Default not featured
                            $file_type_category = in_array($file_type, $allowed_image_types) ? 'image' : 'document';
                            
                            // Insert file record with file_size
                            $file_sql = "INSERT INTO portfolio_files (portfolio_id, file_name, file_path, file_type, file_size, is_featured) VALUES (?, ?, ?, ?, ?, ?)";
                            $file_stmt = $conn->prepare($file_sql);
                            $file_stmt->bind_param("isssii", $item_id, $file_name, $upload_path, $file_type_category, $file_size, $is_featured);
                            
                            if (!$file_stmt->execute()) {
                                error_log("Failed to save file record: " . $file_stmt->error);
                            }
                            
                            $file_stmt->close();
                        } else {
                            error_log("Failed to upload file: " . $file_name);
                        }
                    } else {
                        error_log("Invalid file type: " . $file_name . '. Allowed types: Images (JPG, PNG, GIF, WebP) and Documents (DOC, DOCX, PDF)');
                    }
                }
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update project']);
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    error_log("Error updating portfolio: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>