<?php
/**
 * Get events the current user is registered for
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
    
    $query = "SELECT e.id, e.title, e.description, e.date, e.location, e.capacity, e.registered_count
              FROM events e
              INNER JOIN event_registrations er ON e.id = er.event_id
              WHERE er.user_id = ?
              ORDER BY e.date ASC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($events),
        'events' => $events
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
