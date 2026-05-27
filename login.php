<?php
/**
 * KUET Photography Society - Login Page
 */
require_once 'config.php';

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $login_error = 'Security token verification failed';
    } else {
        $login_type = $_POST['login_type'] ?? 'member';
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $login_error = 'Email and password are required';
        } elseif ($login_type === 'admin') {
            // Admin login with hardcoded credentials (in production, use database)
            $admin_email = 'admin@kuetphoto.com';
            $admin_password = 'admin123'; // Should be hashed in production

            if ($email === $admin_email && $password === $admin_password) {
                $_SESSION['user_id'] = 'admin';
                $_SESSION['user_role'] = 'admin';
                $_SESSION['user_email'] = $email;
                header('Location: control.php');
                exit;
            } else {
                $login_error = 'Invalid admin credentials';
            }
        } else {
            // Member login
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $login_error = 'Invalid email address';
            } else {
                // Check credentials against database
                $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
                if ($stmt) {
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        // Verify password
                        if (password_verify($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_role'] = $user['role'];
                            header('Location: home.php');
                            exit;
                        } else {
                            $login_error = 'Invalid email or password';
                        }
                    } else {
                        $login_error = 'Invalid email or password';
                    }
                    $stmt->close();
                } else {
                    $login_error = 'Database error. Please try again.';
                }
            }
        }
    }
}

$page_title = 'Login';
$body_class = 'luxury-site auth-page';
include 'header.php';
include 'navbar.php';
?>

<main class="auth-shell">
    <section class="auth-hero reveal-target">
        <div>
            <span class="auth-kicker">Secure member access</span>
            <h1>Enter the archive.</h1>
            <p class="auth-hero-copy">Sign in to manage your uploads, view member-only galleries, and join workshops without losing the visual identity of the club.</p>

            <?php
            // Dynamic auth stats
            $active_photographers = 0;
            $annual_events = 0;
            $major_awards = 0;
            $genres = 0;

            if (isset($conn) && $conn instanceof mysqli) {
                $active_photographers = (int)($conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'member'")->fetch_assoc()['cnt'] ?? 0);
                $annual_events = (int)($conn->query("SELECT COUNT(*) AS cnt FROM events WHERE YEAR(date) = " . (int)CURRENT_YEAR)->fetch_assoc()['cnt'] ?? 0);
                $major_awards = (int)($conn->query("SELECT COUNT(*) AS cnt FROM photos WHERE is_featured = 1")->fetch_assoc()['cnt'] ?? 0);
                $genres = (int)($conn->query("SELECT COUNT(DISTINCT category) AS cnt FROM photos WHERE category IS NOT NULL AND category <> ''")->fetch_assoc()['cnt'] ?? 0);
            }
            ?>

            <div class="auth-stat-grid">
                <div class="auth-stat">
                    <span class="auth-stat-value"><?php echo $active_photographers > 999 ? number_format($active_photographers) . '+' : $active_photographers; ?></span>
                    <span class="auth-stat-label">Active photographers</span>
                </div>
                <div class="auth-stat">
                    <span class="auth-stat-value"><?php echo $annual_events; ?></span>
                    <span class="auth-stat-label">Annual events</span>
                </div>
                <div class="auth-stat">
                    <span class="auth-stat-value"><?php echo $major_awards; ?></span>
                    <span class="auth-stat-label">Featured works</span>
                </div>
                <div class="auth-stat">
                    <span class="auth-stat-value"><?php echo $genres; ?></span>
                    <span class="auth-stat-label">Photography genres</span>
                </div>
            </div>
        </div>

        <div class="auth-note-panel">
            <h2>What members can do</h2>
            <ul class="auth-promo-list">
                <li><strong>Upload</strong> share photos for review and featured placement</li>
                <li><strong>Browse</strong> high-resolution gallery images and collections</li>
                <li><strong>Register</strong> for workshops, events, and club announcements</li>
            </ul>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-header">
            <h1>Welcome back</h1>
            <p>Choose member or admin access. Both flows stay on one clean, focused surface.</p>
        </div>

        <div class="auth-tabs">
            <button class="auth-tab-btn login-tab-btn<?php echo (!isset($_POST['login_type']) || ($_POST['login_type'] ?? 'member') === 'member') ? ' active' : ''; ?>" data-tab="member-login">Member Login</button>
            <button class="auth-tab-btn login-tab-btn<?php echo (($_POST['login_type'] ?? 'member') === 'admin') ? ' active' : ''; ?>" data-tab="admin-login">Admin Login</button>
        </div>

        <div class="auth-alert-stack" id="alert-message">
            <?php if ($login_error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">✕</span>
                    <span><?php echo htmlspecialchars($login_error); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div id="member-login" class="auth-form-panel login-tab-content<?php echo (!isset($_POST['login_type']) || ($_POST['login_type'] ?? 'member') === 'member') ? ' active' : ''; ?>">
            <form method="POST" class="auth-form login-form">
                <input type="hidden" name="login_type" value="member">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="form-group">
                    <label for="member-email">Email Address</label>
                    <input type="email" id="member-email" name="email" placeholder="your.email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="member-password">Password</label>
                    <input type="password" id="member-password" name="password" placeholder="Enter your password" required>
                </div>

                <div class="auth-meta-row">
                    <label class="auth-check-row" for="remember-me">
                        <input type="checkbox" id="remember-me" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="inline-link">Forgot password?</a>
                </div>

                <button type="submit" class="cta-button login-submit auth-submit">Login</button>
            </form>

            <div class="auth-footer-links">
                <p>Need an account? <a href="register.php" class="inline-link">Register here</a></p>
                <p><a href="home.php" class="inline-link">Continue browsing</a></p>
            </div>
        </div>

        <div id="admin-login" class="auth-form-panel login-tab-content<?php echo (($_POST['login_type'] ?? 'member') === 'admin') ? ' active' : ''; ?>">
            <form method="POST" class="auth-form login-form">
                <input type="hidden" name="login_type" value="admin">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="auth-support-item" style="border-left: 4px solid var(--warning);">
                    <h4>Admin access only</h4>
                    <p>Use the control panel account for moderation, photo approvals, and club operations.</p>
                </div>

                <div class="form-group">
                    <label for="admin-email">Admin Email</label>
                    <input type="email" id="admin-email" name="email" placeholder="admin@kuetphoto.com" required>
                </div>

                <div class="form-group">
                    <label for="admin-password">Admin Password</label>
                    <input type="password" id="admin-password" name="password" placeholder="Enter admin password" required>
                </div>

                <button type="submit" class="cta-button login-submit auth-submit">Admin Login</button>
            </form>
        </div>

        <div class="auth-divider"><span>or continue browsing</span></div>

        <div class="login-guest">
            <a href="home.php" class="cta-button cta-button-secondary auth-submit">Continue as Guest</a>
        </div>
    </section>
</main>

<script>
    // Tab switching for login types
    document.querySelectorAll('.login-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            document.querySelectorAll('.login-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.login-tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });
</script>

<?php include 'footer.php'; ?>
