<?php
require 'dbconn.php'; // adjust path if needed
header('Content-Type: application/json');

try {
    // Decode the incoming JSON request
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['file_id'], $data['project_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
        exit;
    }

    $file_id = intval($data['file_id']);
    $portfolio_id = intval($data['project_id']); // JS sends 'project_id'

    // ✅ Fetch the file record
    $stmt = $conn->prepare("SELECT file_path, file_name FROM portfolio_files WHERE id = ? AND portfolio_id = ?");
    $stmt->bind_param("ii", $file_id, $portfolio_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'File not found in database.']);
        exit;
    }

    $file = $result->fetch_assoc();
    $file_path = $file['file_path'];

    // ✅ Delete the record from DB
    $delete_stmt = $conn->prepare("DELETE FROM portfolio_files WHERE id = ? AND portfolio_id = ?");
    $delete_stmt->bind_param("ii", $file_id, $portfolio_id);
    $delete_stmt->execute();

    if ($delete_stmt->affected_rows > 0) {
        // ✅ Delete the actual file
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        echo json_encode(['success' => true, 'message' => 'File deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete file record.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
