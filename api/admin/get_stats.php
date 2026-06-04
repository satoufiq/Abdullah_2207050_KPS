<?php
/**
 * Get Admin Dashboard Stats
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
    // Get stats
    $pending_photos = $conn->query("SELECT COUNT(*) as count FROM photos WHERE is_approved = 0")->fetch_assoc()['count'];
    $total_photos = $conn->query("SELECT COUNT(*) as count FROM photos")->fetch_assoc()['count'];
    $total_members = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'member'")->fetch_assoc()['count'];
    $upcoming_events = $conn->query("SELECT COUNT(*) as count FROM events WHERE date >= NOW()")->fetch_assoc()['count'];
    $total_registrations = $conn->query("SELECT COUNT(*) as count FROM event_registrations")->fetch_assoc()['count'];
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'stats' => [
            'pending_photos' => $pending_photos,
            'total_photos' => $total_photos,
            'total_members' => $total_members,
            'upcoming_events' => $upcoming_events,
            'total_registrations' => $total_registrations
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
