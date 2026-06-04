<?php
/**
 * Like Photo API Endpoint
 * Handles liking and unliking photos
 */
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please login to like photos']);
    exit;
}

$photo_id = isset($_POST['photo_id']) ? intval($_POST['photo_id']) : 0;

if (!$photo_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid photo ID']);
    exit;
}

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Check if photo exists
$check_photo = $conn->prepare("SELECT id FROM photos WHERE id = ?");
$check_photo->bind_param('i', $photo_id);
$check_photo->execute();
$photo_result = $check_photo->get_result();

if ($photo_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Photo not found']);
    exit;
}

$check_photo->close();

$user_id = $_SESSION['user_id'];

// Check if user has already liked this photo
$check_like = $conn->prepare("SELECT id FROM photo_likes WHERE photo_id = ? AND user_id = ?");
$check_like->bind_param('ii', $photo_id, $user_id);
$check_like->execute();
$like_result = $check_like->get_result();

if ($like_result->num_rows > 0) {
    // Unlike the photo
    $unlike = $conn->prepare("DELETE FROM photo_likes WHERE photo_id = ? AND user_id = ?");
    $unlike->bind_param('ii', $photo_id, $user_id);
    
    if ($unlike->execute()) {
        echo json_encode(['success' => true, 'action' => 'unliked']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to unlike photo']);
    }
    $unlike->close();
} else {
    // Like the photo
    $like = $conn->prepare("INSERT INTO photo_likes (photo_id, user_id, created_at) VALUES (?, ?, NOW())");
    $like->bind_param('ii', $photo_id, $user_id);
    
    if ($like->execute()) {
        echo json_encode(['success' => true, 'action' => 'liked']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to like photo']);
    }
    $like->close();
}

$check_like->close();
?>