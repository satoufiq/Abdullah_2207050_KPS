<?php
/**
 * Get current user's photo submissions
 */
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT id, title, description, image_url, category, location, lens_info, 
                     is_approved, is_featured, is_photo_of_week, views, likes, downloads, created_at
              FROM photos
              WHERE photographer_id = ?
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($submissions),
        'submissions' => $submissions
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
