<?php
/**
 * Login API Endpoint
 */
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$login_type = $_POST['login_type'] ?? 'member';
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

try {
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }

    if ($login_type === 'admin') {
        // Admin login
        if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
            $_SESSION['user_id'] = 'admin';
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_email'] = $email;
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Admin login successful',
                'redirect' => 'control.html'
            ]);
        } else {
            throw new Exception('Invalid admin credentials');
        }
    } else {
        // Member login
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }

        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception('Database error');
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => 'profile.html'
                ]);
            } else {
                throw new Exception('Invalid email or password');
            }
        } else {
            throw new Exception('Invalid email or password');
        }
        $stmt->close();
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
