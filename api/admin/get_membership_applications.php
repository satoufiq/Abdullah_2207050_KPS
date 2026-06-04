<?php
/**
 * Admin API - list membership applications
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, email, phone, experience, interests, message, status, approved_by, applied_at, reviewed_at FROM membership_applications ORDER BY applied_at DESC");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$applications = [];
while ($row = $result->fetch_assoc()) {
    $row['interests'] = json_decode($row['interests'], true);
    $applications[] = $row;
}

echo json_encode(['success' => true, 'applications' => $applications]);

?>
