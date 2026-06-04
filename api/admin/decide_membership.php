<?php
/**
 * Admin API - approve or reject membership application
 */
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$app_id = isset($input['application_id']) ? intval($input['application_id']) : 0;
$action = $input['action'] ?? '';
$position = $input['position'] ?? '';

if ($app_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// Fetch application
$stmt = $conn->prepare("SELECT * FROM membership_applications WHERE id = ?");
$stmt->bind_param('i', $app_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Application not found']);
    exit;
}
$app = $res->fetch_assoc();

// On approve: find user by email and promote to member and optionally add to team_members
if ($action === 'approve') {
    // find user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $app['email']);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Corresponding user account not found for this email']);
        exit;
    }
    $user = $r->fetch_assoc();
    $user_id = intval($user['id']);

    // update user role to member
    $u = $conn->prepare("UPDATE users SET role = 'member' WHERE id = ?");
    $u->bind_param('i', $user_id);
    $u->execute();

    // If position provided, insert or update team_members
    if (!empty($position)) {
        // check existing
        $check = $conn->prepare("SELECT id FROM team_members WHERE user_id = ? LIMIT 1");
        $check->bind_param('i', $user_id);
        $check->execute();
        $cr = $check->get_result();
        if ($cr->num_rows > 0) {
            $tm = $cr->fetch_assoc();
            $update = $conn->prepare("UPDATE team_members SET position = ? WHERE id = ?");
            $update->bind_param('si', $position, $tm['id']);
            $update->execute();
        } else {
            $ins = $conn->prepare("INSERT INTO team_members (user_id, position) VALUES (?, ?)");
            $ins->bind_param('is', $user_id, $position);
            $ins->execute();
        }
    }

    // mark application approved - ensure admin exists, otherwise set approved_by NULL
    $admin_id = $_SESSION['user_id'] ?? 0;
    $approved_by_value = null;
    $check = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
    if ($check) {
        $check->bind_param('i', $admin_id);
        $check->execute();
        $r = $check->get_result();
        if ($r && $r->num_rows > 0) {
            $approved_by_value = $admin_id;
        }
        $check->close();
    }

    if ($approved_by_value !== null) {
        $upd = $conn->prepare("UPDATE membership_applications SET status = 'approved', approved_by = ?, reviewed_at = NOW() WHERE id = ?");
        $upd->bind_param('ii', $approved_by_value, $app_id);
        $upd->execute();
    } else {
        $upd = $conn->prepare("UPDATE membership_applications SET status = 'approved', approved_by = NULL, reviewed_at = NOW() WHERE id = ?");
        $upd->bind_param('i', $app_id);
        $upd->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Application approved']);
    exit;
}

// On reject: mark application rejected
if ($action === 'reject') {
    $upd = $conn->prepare("UPDATE membership_applications SET status = 'rejected', approved_by = ?, reviewed_at = NOW() WHERE id = ?");
    $admin_id = $_SESSION['user_id'];
    $upd->bind_param('ii', $admin_id, $app_id);
    $upd->execute();

    echo json_encode(['success' => true, 'message' => 'Application rejected']);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);

?>
