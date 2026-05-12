<?php
/**
 * KUET Photography Society - Configuration File
 * Database and application settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kuet_photography');

// Site Configuration
define('SITE_NAME', 'KUET Photography Society');
define('SITE_URL', 'http://localhost/KUET%20Photography%20Society/');
define('BASE_PATH', __DIR__);

// Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Database Connection Failed: ' . $conn->connect_error);
    }
    
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    error_log($e->getMessage());
    // For development - comment out in production
    // die($e->getMessage());
}

// Error Reporting (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session Configuration
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Admin Credentials
define('ADMIN_EMAIL', 'admin@kuetphoto.com');
define('ADMIN_PASSWORD', 'admin123');
define('CURRENT_YEAR', date('Y'));

// Helper Functions
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function get_csrf_token() {
    return $_SESSION['csrf_token'] ?? '';
}

function verify_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}
?>
