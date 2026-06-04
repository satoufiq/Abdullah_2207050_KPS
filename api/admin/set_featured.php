<?php
/**
 * Set Featured or Photo of Week
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
    $action = $_POST['action'] ?? ''; // 'featured' or 'photo_of_week'
    
    if ($photo_id === 0 || !in_array($action, ['featured', 'photo_of_week'])) {
        throw new Exception('Invalid parameters');
    }
    
    if ($action === 'featured') {
        // Toggle featured
        $stmt = $conn->prepare("UPDATE photos SET is_featured = !is_featured WHERE id = ?");
    } else {
        // For photo of week, clear others first then set this one
        $clear_stmt = $conn->prepare("UPDATE photos SET is_photo_of_week = 0 WHERE id != ?");
        $clear_stmt->bind_param('i', $photo_id);
        $clear_stmt->execute();
        $clear_stmt->close();
        
        $stmt = $conn->prepare("UPDATE photos SET is_photo_of_week = 1 WHERE id = ?");
    }
    
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    $stmt->bind_param('i', $photo_id);
    
    if ($stmt->execute()) {
        $message = $action === 'featured' ? 'Featured status updated' : 'Photo of week set';
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        throw new Exception('Failed to update photo');
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
