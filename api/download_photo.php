<?php
/**
 * Photo Download API
 */
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('You must be logged in to download images');
}

$photo_id = (int)($_GET['photo_id'] ?? 0);

if ($photo_id <= 0) {
    http_response_code(400);
    die('Invalid photo ID');
}

try {
    // Get photo information
    $stmt = $conn->prepare("SELECT id, image_url, title FROM photos WHERE id = ? AND is_approved = 1");
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    $stmt->bind_param('i', $photo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Photo not found');
    }
    
    $photo = $result->fetch_assoc();
    $stmt->close();
    
    // Get full file path
    $file_path = realpath('../' . $photo['image_url']);
    
    // Security check: ensure file is within allowed directory
    $allowed_dir = realpath('../images/submissions');
    if ($allowed_dir && strpos($file_path, $allowed_dir) !== 0) {
        throw new Exception('Invalid file path');
    }
    
    // Check if file exists
    if (!file_exists($file_path)) {
        throw new Exception('File not found on server');
    }
    
    // Increment download count (optional - requires adding download_count column)
    // $update_stmt = $conn->prepare("UPDATE photos SET downloads = downloads + 1 WHERE id = ?");
    // if ($update_stmt) {
    //     $update_stmt->bind_param('i', $photo_id);
    //     $update_stmt->execute();
    //     $update_stmt->close();
    // }
    
    // Set headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($photo['title']) . '_' . time() . '.' . pathinfo($file_path, PATHINFO_EXTENSION) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Send file
    readfile($file_path);
    exit;
    
} catch (Exception $e) {
    error_log('Photo download error: ' . $e->getMessage());
    http_response_code(500);
    die('Error downloading photo: ' . $e->getMessage());
}
?>
