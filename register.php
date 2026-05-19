<?php
/**
 * KUET Photography Society - Member Registration Page
 */
require_once 'config.php';

$registration_error = '';
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $registration_error = 'Security token verification failed';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $phone = sanitize_input($_POST['phone'] ?? '');
        $bio = sanitize_input($_POST['bio'] ?? '');

        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
            $registration_error = 'Name, email, and password are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $registration_error = 'Invalid email address';
        } elseif (strlen($password) < 8) {
            $registration_error = 'Password must be at least 8 characters long';
        } elseif ($password !== $password_confirm) {
            $registration_error = 'Passwords do not match';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $registration_error = 'Email already registered. Please login or use a different email.';
                } else {
                    // Hash password and insert new user
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, bio, role) VALUES (?, ?, ?, ?, ?, 'member')");

                    if ($insert_stmt) {
                        $insert_stmt->bind_param('sssss', $name, $email, $hashed_password, $phone, $bio);

                        if ($insert_stmt->execute()) {
                            $registration_success = true;
                            $_SESSION['user_id'] = $insert_stmt->insert_id;
                            $_SESSION['user_name'] = $name;
                            $_SESSION['user_email'] = $email;
                            $_SESSION['user_role'] = 'member';
                        } else {
                            $registration_error = 'Registration failed. Please try again.';
                        }
                        $insert_stmt->close();
                    } else {
                        $registration_error = 'Database error. Please try again.';
                    }
                }
                $stmt->close();
            }
        }
    }
}

$body_class = 'luxury-site auth-page';
$page_title = 'Register';
include 'header.php';
include 'navbar.php';
?>

<main class="auth-shell">
    <section class="auth-hero reveal-target">
        <div>
            <span class="auth-kicker">Welcome, photographer</span>
            <h1>Join us today.</h1>
            <p class="auth-hero-copy">Create a free account to start uploading photos, discovering member galleries, and connecting with other photographers at KUET.</p>

            <div class="auth-stat-grid">
                <div class="auth-stat">
                    <span class="auth-stat-value">5K+</span>
                    <span class="auth-stat-label">Total photos</span>
                </div>
                <div class="auth-stat">
                    <span class="auth-stat-value">18</span>
                    <span class="auth-stat-label">Collections</span>
                </div>
                <div class="auth-stat">
                    <span class="auth-stat-value">100%</span>
                    <span class="auth-stat-label">Community run</span>
                </div>
                <div class="auth-stat">
                    <span class="auth-stat-value">0$</span>
                    <span class="auth-stat-label">Fee to join</span>
                </div>
            </div>
        </div>

        <div class="auth-note-panel">
            <h2>Registration takes under 2 minutes</h2>
            <ul class="auth-promo-list">
                <li><strong>Email</strong> create your secure account</li>
                <li><strong>Bio (Optional)</strong> tell us about your style</li>
                <li><strong>Ready</strong> start sharing photos immediately</li>
            </ul>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-header">
            <h1>Create account</h1>
            <p>All fields are optional except email and password. Complete your profile anytime.</p>
        </div>

        <?php if (!$registration_success): ?>
            <div class="auth-alert-stack">
                <?php if ($registration_error): ?>
                    <div class="auth-alert auth-alert-error">
                        <span class="auth-alert-icon">✕</span>
                        <span><?php echo htmlspecialchars($registration_error); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" class="auth-form register-form">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="auth-field-grid">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" placeholder="Your full name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>

                <div class="auth-field-grid">
                    <div class="form-group">
                        <label for="phone">Phone (Optional)</label>
                        <input type="tel" id="phone" name="phone" placeholder="+880 1XX XXXXXX" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" placeholder="Min 8 characters" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password *</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm password" required>
                </div>

                <div class="form-group">
                    <label for="bio">About Your Photography</label>
                    <textarea id="bio" name="bio" placeholder="Your interests, style, or photography journey..." rows="3" maxlength="500"><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
                    <small style="color: var(--text-muted);">Max 500 characters</small>
                </div>

                <label class="auth-check-row" for="agree">
                    <input type="checkbox" id="agree" required>
                    <span>I agree to Terms of Service and Privacy Policy</span>
                </label>

                <button type="submit" class="cta-button register-submit auth-submit">Create Account</button>
            </form>

            <div class="auth-footer-links">
                <p>Already registered? <a href="login.php" class="inline-link">Login here</a></p>
                <p><a href="home.php" class="inline-link">Back to gallery</a></p>
            </div>
        <?php else: ?>
            <div style="padding: 40px 0; text-align: center;">
                <div style="color: var(--accent-gold); font-size: 48px; margin-bottom: 20px;">✓</div>
                <h2 style="color: var(--text-light); margin-bottom: 10px;">Welcome to KUET Photography Society!</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Your account has been successfully created.</p>
                <p style="color: var(--text-muted); font-size: 14px;">Redirecting to home page...</p>
                <script>
                    setTimeout(() => {
                        window.location.href = 'home.php';
                    }, 3000);
                </script>
            </div>
        <?php endif; ?>
    </section>
</main>
