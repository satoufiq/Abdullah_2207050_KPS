<?php
/**
 * Newsletter Subscription API
 */
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF token verification failed']);
    exit;
}

$email = sanitize_input($_POST['email'] ?? '');

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

try {
    // Insert subscriber into database
    $stmt = $conn->prepare("INSERT INTO subscribers (email, subscribed_at) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE subscribed_at = NOW()");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $email);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $stmt->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Successfully subscribed to our newsletter!'
    ]);
    
} catch (Exception $e) {
    error_log('Subscribe error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to subscribe. Please try again later.']);
}
?>
