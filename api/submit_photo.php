<?php
/**
 * Photo Submission API
 */
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'member') {
    http_response_code(403);
    echo json_encode(['error' => 'You must be logged in as a member to submit photos']);
    exit;
}

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

$title = sanitize_input($_POST['title'] ?? '');
$description = sanitize_input($_POST['description'] ?? '');
$category = sanitize_input($_POST['category'] ?? '');
$location = sanitize_input($_POST['location'] ?? '');
$lens_info = sanitize_input($_POST['lens_info'] ?? '');

// Validate file upload
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No photo uploaded or upload error occurred']);
    exit;
}

$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
$file_info = getimagesize($_FILES['photo']['tmp_name']);

if (!$file_info || !in_array($_FILES['photo']['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid image format. Only JPG, PNG, and WebP are allowed']);
    exit;
}

if ($_FILES['photo']['size'] > 10 * 1024 * 1024) { // 10MB limit
    http_response_code(400);
    echo json_encode(['error' => 'Image size exceeds 10MB limit']);
    exit;
}

// Validate required fields
if (empty($title) || empty($category)) {
    http_response_code(400);
    echo json_encode(['error' => 'Title and category are required']);
    exit;
}

try {
    // Create uploads directory if it doesn't exist
    $upload_dir = '../images/submissions/' . date('Y-m');
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $file_ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = 'photo_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
    $file_path = $upload_dir . '/' . $filename;
    $relative_path = 'images/submissions/' . date('Y-m') . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
        throw new Exception('Failed to save uploaded file');
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO photos (title, description, photographer_id, image_url, category, location, lens_info, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('ssissss', $title, $description, $_SESSION['user_id'], $relative_path, $category, $location, $lens_info);
    
    if (!$stmt->execute()) {
        unlink($file_path); // Delete uploaded file if DB insert fails
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $photo_id = $stmt->insert_id;
    $stmt->close();
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Photo submitted successfully! It will appear in the gallery after admin approval.',
        'photo_id' => $photo_id
    ]);
    
} catch (Exception $e) {
    error_log('Photo submission error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit photo. Please try again later.']);
}
?>
