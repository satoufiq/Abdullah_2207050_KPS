<?php
/**
 * Event Registration API
 */
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'You must be logged in to register for events']);
    exit;
}

// Verify CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF token verification failed']);
    exit;
}

$event_id = (int)($_POST['event_id'] ?? 0);
$action = $_POST['action'] ?? 'register'; // register or unregister

if ($event_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid event ID']);
    exit;
}

try {
    if ($action === 'register') {
        // Check if already registered
        $check_stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
        if ($check_stmt) {
            $check_stmt->bind_param('ii', $event_id, $_SESSION['user_id']);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'You are already registered for this event']);
                exit;
            }
            $check_stmt->close();
        }

        // Register user for event
        $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, user_id) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param('ii', $event_id, $_SESSION['user_id']);
            if ($stmt->execute()) {
                // Update registered count
                $update_stmt = $conn->prepare("UPDATE events SET registered_count = registered_count + 1 WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param('i', $event_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully registered for the event!',
                    'action' => 'registered'
                ]);
            } else {
                throw new Exception('Registration failed: ' . $stmt->error);
            }
            $stmt->close();
        }
    } elseif ($action === 'unregister') {
        // Unregister user from event
        $stmt = $conn->prepare("DELETE FROM event_registrations WHERE event_id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param('ii', $event_id, $_SESSION['user_id']);
            if ($stmt->execute()) {
                // Update registered count
                $update_stmt = $conn->prepare("UPDATE events SET registered_count = MAX(0, registered_count - 1) WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param('i', $event_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully unregistered from the event.',
                    'action' => 'unregistered'
                ]);
            } else {
                throw new Exception('Unregistration failed: ' . $stmt->error);
            }
            $stmt->close();
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log('Event registration error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred. Please try again later.']);
}
?>
