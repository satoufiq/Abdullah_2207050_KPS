<?php
/**
 * Get Gallery Photos API
 */
require_once '../config.php';

header('Content-Type: application/json');

try {
    $category = sanitize_input($_GET['category'] ?? '');
    
    $query = "SELECT p.id, p.title, p.image_url, p.category, p.location, p.lens_info, u.name as photographer_name, 
                     p.likes, p.views, p.is_photo_of_week
              FROM photos p 
              JOIN users u ON p.photographer_id = u.id 
              WHERE p.is_approved = 1";
    
    if (!empty($category)) {
        $query .= " AND p.category = '" . $conn->real_escape_string($category) . "'";
    }
    
    $query .= " ORDER BY p.is_photo_of_week DESC, p.is_featured DESC, p.created_at DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Database query failed');
    }
    
    $photos = [];
    while ($row = $result->fetch_assoc()) {
        $row['can_download'] = isset($_SESSION['user_id']) ? true : false;
        $photos[] = $row;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($photos),
        'photos' => $photos
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
