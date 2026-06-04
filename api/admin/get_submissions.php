<?php
/**
 * Get Pending Photo Submissions for Admin Review
 */
require_once '../../config.php';

header('Content-Type: application/json');

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

try {
    $query = "SELECT p.id, p.title, p.image_url, p.category, u.name as photographer_name, 
                     p.is_approved, p.created_at
              FROM photos p
              JOIN users u ON p.photographer_id = u.id
              WHERE p.is_approved = 0
              ORDER BY p.created_at DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Database query failed');
    }
    
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
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
