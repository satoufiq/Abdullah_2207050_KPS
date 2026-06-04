<?php
/**
 * Check if user is logged in - returns full session data
 */
require_once '../config.php';

header('Content-Type: application/json');

$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null;
$is_member = $is_logged_in && $_SESSION['user_role'] === 'member';
$is_admin = $is_logged_in && $_SESSION['user_role'] === 'admin';

// Get user details if logged in
$user_data = [];
if ($is_member) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, name, email, profile_image FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    }
}

http_response_code(200);
echo json_encode([
    'logged_in' => $is_logged_in,
    'is_member' => $is_member,
    'is_admin' => $is_admin,
    'user_id' => $is_logged_in ? $_SESSION['user_id'] : null,
    'user_email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null,
    'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null,
    'user_role' => isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null,
    'csrf_token' => get_csrf_token(),
    'user_data' => $user_data
]);
?>
