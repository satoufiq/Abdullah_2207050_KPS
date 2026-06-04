<?php
/**
 * KUET Photography Society - Membership Application API
 */
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Require logged-in user
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to apply for membership']);
    exit;
}

// Check if membership_applications table exists with a simple query
@$table_check = $conn->query("SELECT 1 FROM membership_applications LIMIT 1");
if (!$table_check) {
    http_response_code(500);
    echo json_encode(['error' => 'Database not initialized. Please contact the administrator to run the database setup.']);
    exit;
}

$_POST_NAME = $_POST['name'] ?? '';
$_POST_EMAIL = $_POST['email'] ?? '';
// prefer session values (require login)
$name = sanitize_input($_SESSION['user_name'] ?? $_POST_NAME);
$email = sanitize_input($_SESSION['user_email'] ?? $_POST_EMAIL);
$phone = sanitize_input($_POST['phone'] ?? '');
$batch = sanitize_input($_POST['batch'] ?? '');
$experience = sanitize_input($_POST['experience'] ?? 'beginner');
$message = sanitize_input($_POST['message'] ?? '');

// Get interests from checkbox array
$interests = isset($_POST['interests']) ? $_POST['interests'] : [];
if (!is_array($interests)) {
    $interests = [$interests];
}

// Validate required fields
$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
if (empty($batch)) $errors[] = 'Batch is required';
if (empty($message)) $errors[] = 'Message is required';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

// Validate experience level
$valid_experience = ['beginner', 'intermediate', 'advanced'];
if (!in_array($experience, $valid_experience)) {
    $experience = 'beginner';
}

// Insert membership application
$interests_json = json_encode($interests);

$stmt = $conn->prepare("INSERT INTO membership_applications (name, email, phone, batch, experience, interests, message, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");

if ($stmt) {
    $stmt->bind_param('sssssss', $name, $email, $phone, $batch, $experience, $interests_json, $message);
    
    if ($stmt->execute()) {
        $application_id = $stmt->insert_id;
        
        // Send confirmation email (optional - commented for now)
        // mail($email, 'KUET Photography Society - Membership Application Received', 
        //     "Dear $name,\n\nThank you for your interest in KUET Photography Society. We have received your membership application and will review it shortly.\n\nBest regards,\nKUET Photography Society");
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Your membership application has been submitted successfully. We will review it shortly!',
            'application_id' => $application_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to submit application. Please try again.']);
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error. Please try again.']);
}

// use sanitize_input() from config.php
