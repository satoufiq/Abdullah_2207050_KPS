<?php
/**
 * Member Registration API Endpoint
 */
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $phone = sanitize_input($_POST['phone'] ?? '');
    $bio = sanitize_input($_POST['bio'] ?? '');

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        throw new Exception('Name, email, and password are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    if ($password !== $password_confirm) {
        throw new Exception('Passwords do not match');
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Database error');
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Email already registered. Please login or use a different email');
    }

    // Hash password and insert
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, bio, role) VALUES (?, ?, ?, ?, ?, 'member')");
    
    if (!$insert_stmt) {
        throw new Exception('Database error');
    }

    $insert_stmt->bind_param('sssss', $name, $email, $hashed_password, $phone, $bio);

    if ($insert_stmt->execute()) {
        $_SESSION['user_id'] = $insert_stmt->insert_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'member';

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully!',
            'redirect' => 'profile.html'
        ]);
    } else {
        throw new Exception('Registration failed');
    }

    $insert_stmt->close();
    $stmt->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
