<?php
/**
 * Get Events API
 */
require_once '../config.php';

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $query = "SELECT e.id, e.title, e.description, e.date as event_date, e.location, 'workshop' as category, 
                     e.capacity, e.registered_count, e.location as image_url,
                     (CASE WHEN e.id IN (SELECT event_id FROM event_registrations WHERE user_id = ?) THEN 1 ELSE 0 END) as is_registered
              FROM events e 
              WHERE e.date >= NOW()
              ORDER BY e.date ASC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Database error');
    }
    
    if ($user_id) {
        $stmt->bind_param('i', $user_id);
    } else {
        $stmt->bind_param('i', $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $row['available_spots'] = $row['capacity'] - $row['registered_count'];
        $row['is_full'] = $row['available_spots'] <= 0 ? true : false;
        $row['event_date_formatted'] = date('M d, Y', strtotime($row['event_date']));
        $row['event_time_formatted'] = date('g:i A', strtotime($row['event_date']));
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
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
