<?php
/**
 * Reject Photo Submission
 */
require_once '../../config.php';

header('Content-Type: application/json');

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $photo_id = (int)($_POST['photo_id'] ?? 0);
    
    if ($photo_id === 0) {
        throw new Exception('Invalid photo ID');
    }
    
    // Get photo path and delete file
    $result = $conn->query("SELECT image_url FROM photos WHERE id = $photo_id");
    $photo = $result->fetch_assoc();
    
    if ($photo && file_exists($photo['image_url'])) {
        unlink($photo['image_url']);
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    $stmt->bind_param('i', $photo_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Photo rejected and deleted'
        ]);
    } else {
        throw new Exception('Failed to reject photo');
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
