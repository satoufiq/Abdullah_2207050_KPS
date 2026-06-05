<?php
/**
 * KUET Photography Society - Database Setup Installer
 * Visit this file once to initialize the database
 */

require_once 'config.php';

$message = '';
$success = false;
$detailed_info = [];

try {
    // First, verify database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
    }
    $detailed_info[] = "✓ Database connection successful";

    // Check if database exists and select it
    $result = $conn->query("SELECT DATABASE()");
    if (!$result) {
        // Try to create database
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME)) {
            throw new Exception("Failed to create database: " . $conn->error);
        }
        $detailed_info[] = "✓ Database created: " . DB_NAME;
    }
    
    if (!$conn->select_db(DB_NAME)) {
        throw new Exception("Failed to select database: " . $conn->error);
    }
    $detailed_info[] = "✓ Database selected: " . DB_NAME;

    // Read and execute SQL setup file
    $sql_file_path = __DIR__ . '/db/database_setup.sql';
    if (!file_exists($sql_file_path)) {
        throw new Exception("database_setup.sql not found at: " . $sql_file_path);
    }
    
    $sql_file = file_get_contents($sql_file_path);
    if (!$sql_file) {
        throw new Exception("Could not read database_setup.sql file");
    }
    $detailed_info[] = "✓ database_setup.sql file loaded";

    // Split SQL statements and execute them
    $statements = array_filter(array_map('trim', explode(';', $sql_file)));
    $errors = [];
    $created_count = 0;
    $skipped_count = 0;

    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        // Extract table name for logging
        preg_match('/CREATE TABLE IF NOT EXISTS\s+(\w+)/i', $statement, $matches);
        $table_name = $matches[1] ?? 'unknown';
        
        if ($conn->query($statement)) {
            $created_count++;
            $detailed_info[] = "✓ Table processed: " . $table_name;
        } else {
            $error_msg = $conn->error;
            // Check if it's a "table already exists" warning (not a critical error)
            if (strpos($error_msg, 'already exists') !== false) {
                $skipped_count++;
                $detailed_info[] = "ℹ Table already exists: " . $table_name;
            } else {
                $errors[] = $table_name . " - " . $error_msg;
                $detailed_info[] = "✕ Error in " . $table_name . ": " . $error_msg;
            }
        }
    }

    // Verify critical tables exist
    $required_tables = ['users', 'photos', 'team_members', 'membership_applications'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '" . $table . "'");
        if (!$check || $check->num_rows === 0) {
            $missing_tables[] = $table;
        } else {
            $detailed_info[] = "✓ Verified table exists: " . $table;
        }
    }

    if (!empty($missing_tables)) {
        throw new Exception("Critical tables missing: " . implode(', ', $missing_tables));
    }

    if (empty($errors)) {
        $success = true;
        $message = '✅ Database setup completed successfully! All tables created or already exist.';
    } else {
        $message = '⚠️ Setup completed with some warnings, but all critical tables exist.';
    }

} catch (Exception $e) {
    $success = false;
    $message = '❌ Setup Error: ' . $e->getMessage();
    $detailed_info[] = "❌ Exception: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - KUET Photography Society</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #f0f0f0;
            padding: 1rem;
        }
        
        .container {
            background: rgba(30, 35, 50, 0.95);
            border: 2px solid #d4af37;
            border-radius: 12px;
            padding: 3rem;
            max-width: 700px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.2);
        }
        
        h1 {
            color: #d4af37;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        h2 {
            color: #d4af37;
            font-size: 1.3rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .message {
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .message.success {
            background-color: rgba(39, 174, 96, 0.2);
            border: 2px solid #27ae60;
            color: #27ae60;
        }
        
        .message.error {
            background-color: rgba(255, 107, 107, 0.2);
            border: 2px solid #ff6b6b;
            color: #ff6b6b;
        }
        
        .info {
            background: rgba(212, 175, 55, 0.1);
            border-left: 4px solid #d4af37;
            padding: 1rem;
            margin: 1.5rem 0;
            border-radius: 4px;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .log-output {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 4px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            max-height: 300px;
            overflow-y: auto;
            line-height: 1.5;
        }
        
        .log-line {
            margin: 0.3rem 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .button {
            padding: 0.8rem 1.5rem;
            border: 2px solid #d4af37;
            background-color: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .button:hover {
            background-color: #d4af37;
            color: #0a0e27;
        }
        
        .button.primary {
            background-color: #d4af37;
            color: #0a0e27;
        }
        
        .steps {
            margin: 2rem 0;
            counter-reset: step-counter;
        }
        
        .step {
            counter-increment: step-counter;
            margin: 1rem 0;
            padding-left: 2rem;
            position: relative;
        }
        
        .step::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            background-color: #d4af37;
            color: #0a0e27;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.8rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            color: #999;
            font-size: 0.9rem;
        }
        
        a {
            color: #d4af37;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">📸</div>
        <h1>KUET Photography Society</h1>
        <h2>Database Setup</h2>
        
        <div class="message <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>

        <?php if (!empty($detailed_info)): ?>
            <div class="log-output">
                <?php foreach ($detailed_info as $line): ?>
                    <div class="log-line"><?php echo htmlspecialchars($line); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="info">
                <strong>✓ Setup Complete!</strong><br>
                Your database has been initialized successfully. You can now:
            </div>
            
            <div class="steps">
                <div class="step">Visit the <a href="home.php">Homepage</a></div>
                <div class="step">Create an account via <a href="register.php">Register</a></div>
                <div class="step">Login with your credentials via <a href="login.php">Login</a></div>
                <div class="step">Access admin panel as: <code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem;">admin@kuetphoto.com</code></div>
            </div>
            
            <div class="button-group">
                <a href="home.php" class="button primary">🏠 Go to Home</a>
                <a href="login.php" class="button">🔑 Login</a>
            </div>
        <?php else: ?>
            <div class="info">
                <strong>⚠️ Setup Failed - Troubleshooting Steps:</strong>
                <div class="steps" style="margin-top: 1rem;">
                    <div class="step">Ensure XAMPP MySQL server is <strong>running</strong> (green indicator)</div>
                    <div class="step">Check MySQL is accepting connections on localhost</div>
                    <div class="step">Verify database credentials in <code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem;">config.php</code></div>
                    <div class="step">Try again by refreshing this page</div>
                    <div class="step">If still failing, manually import <code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem;">db/database_setup.sql</code> in phpMyAdmin</div>
                </div>
            </div>
            
            <div class="button-group">
                <a href="setup.php" class="button primary">🔄 Try Setup Again</a>
                <a href="http://localhost/phpmyadmin" class="button">📊 Open phpMyAdmin</a>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            KUET Photography Society &copy; 2026 | Setup v2.0
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - KUET Photography Society</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #f0f0f0;
        }
        
        .container {
            background: rgba(30, 35, 50, 0.95);
            border: 2px solid #d4af37;
            border-radius: 12px;
            padding: 3rem;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.2);
        }
        
        h1 {
            color: #d4af37;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .logo {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .message {
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .message.success {
            background-color: rgba(39, 174, 96, 0.2);
            border: 2px solid #27ae60;
            color: #27ae60;
        }
        
        .message.error {
            background-color: rgba(255, 107, 107, 0.2);
            border: 2px solid #ff6b6b;
            color: #ff6b6b;
        }
        
        .info {
            background: rgba(212, 175, 55, 0.1);
            border-left: 4px solid #d4af37;
            padding: 1rem;
            margin: 1.5rem 0;
            border-radius: 4px;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .button {
            padding: 0.8rem 1.5rem;
            border: 2px solid #d4af37;
            background-color: rgba(212, 175, 55, 0.1);
            color: #d4af37;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .button:hover {
            background-color: #d4af37;
            color: #0a0e27;
        }
        
        .steps {
            margin: 2rem 0;
            counter-reset: step-counter;
        }
        
        .step {
            counter-increment: step-counter;
            margin: 1rem 0;
            padding-left: 2rem;
            position: relative;
        }
        
        .step::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            background-color: #d4af37;
            color: #0a0e27;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.8rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(212, 175, 55, 0.2);
            color: #999;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">📸</div>
        <h1>KUET Photography Society</h1>
        <h2 style="text-align: center; color: #d4af37; font-size: 1.3rem; margin-bottom: 2rem;">Database Setup</h2>
        
        <?php if ($success): ?>
            <div class="message success">
                <?php echo $message; ?>
            </div>
            
            <div class="info">
                <strong>✓ Setup Complete!</strong><br>
                Your database has been initialized successfully. You can now:
            </div>
            
            <div class="steps">
                <div class="step">Visit the <a href="home.php" style="color: #d4af37; text-decoration: underline;">Homepage</a></div>
                <div class="step">Create an account via <a href="register.php" style="color: #d4af37; text-decoration: underline;">Register</a></div>
                <div class="step">Login with your credentials via <a href="login.php" style="color: #d4af37; text-decoration: underline;">Login</a></div>
            </div>
            
            <div class="info" style="margin-top: 2rem; border-left-color: #27ae60; background: rgba(39, 174, 96, 0.1);">
                <strong>📝 Admin Access:</strong><br>
                To access the admin panel, login with email: <code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem; border-radius: 3px;">admin@kuetphoto.com</code>
            </div>
            
        <?php else: ?>
            <div class="message error">
                <?php echo $message; ?>
            </div>
            
            <div class="info">
                <strong>Troubleshooting:</strong>
                <div class="steps" style="margin-top: 1rem;">
                    <div class="step">Ensure XAMPP MySQL server is running</div>
                    <div class="step">Check database credentials in <code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem;">config.php</code></div>
                    <div class="step">Manually import <code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem;">db/database_setup.sql</code> in phpMyAdmin</div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="button-group">
            <a href="setup.php" class="button">🔄 Run Setup Again</a>
            <a href="home.php" class="button">🏠 Go to Home</a>
        </div>
        
        <div class="footer">
            KUET Photography Society &copy; 2026 | Setup v1.0
        </div>
    </div>
</body>
</html>
