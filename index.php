<?php
/**
 * KUET Photography Society - Home
 * Redirect to home page
 */
require_once 'config.php';

// Redirect to home page
header('Location: home.php');
exit;
?>

<style>
    .quick-start {
        max-width: 900px;
        margin: 3rem auto;
        padding: 2rem;
    }
    
    .status-box {
        background: rgba(212, 175, 55, 0.1);
        border: 2px solid #d4af37;
        border-radius: 8px;
        padding: 2rem;
        margin: 2rem 0;
    }
    
    .status-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1rem 0;
        font-size: 1.1rem;
    }
    
    .status-icon {
        font-size: 1.5rem;
        min-width: 2rem;
    }
    
    .status-icon.ok { color: #27ae60; }
    .status-icon.error { color: #ff6b6b; }
    
    .next-steps {
        background: rgba(212, 175, 55, 0.05);
        border-left: 4px solid #d4af37;
        padding: 1.5rem;
        margin: 2rem 0;
        border-radius: 4px;
    }
    
    .next-steps h3 {
        color: #d4af37;
        margin-bottom: 1rem;
    }
    
    .step-item {
        margin: 1rem 0;
        padding-left: 2rem;
        position: relative;
    }
    
    .step-item::before {
        content: "→";
        position: absolute;
        left: 0;
        color: #d4af37;
        font-weight: bold;
    }
    
    .button-row {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2rem;
    }
    
    .btn {
        display: inline-block;
        padding: 0.8rem 1.5rem;
        border: 2px solid #d4af37;
        background: rgba(212, 175, 55, 0.1);
        color: #d4af37;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .btn:hover {
        background: #d4af37;
        color: #0a0e27;
    }
    
    .btn.primary {
        background: #d4af37;
        color: #0a0e27;
    }
</style>

<div class="quick-start">
    <h1 style="text-align: center; color: #d4af37; margin-bottom: 1rem;">📸 System Status</h1>
    
    <div class="status-box">
        <div class="status-item">
            <span class="status-icon <?php echo $db_connected ? 'ok' : 'error'; ?>">
                <?php echo $db_connected ? '✓' : '✕'; ?>
            </span>
            <span>Database Connection: <strong><?php echo $db_connected ? 'Connected ✓' : 'Failed ✕'; ?></strong></span>
        </div>
        
        <div class="status-item">
            <span class="status-icon <?php echo $tables_exist ? 'ok' : 'error'; ?>">
                <?php echo $tables_exist ? '✓' : '✕'; ?>
            </span>
            <span>Database Tables: <strong><?php echo $tables_exist ? 'Initialized ✓' : 'Not Initialized ✕'; ?></strong></span>
        </div>
    </div>
    
    <?php if ($tables_exist): ?>
        <!-- Everything is ready -->
        <div class="status-box" style="background: rgba(39, 174, 96, 0.1); border-color: #27ae60;">
            <h2 style="color: #27ae60; text-align: center; margin-top: 0;">✓ All Systems Ready!</h2>
            <p style="text-align: center; margin-top: 1rem;">Your database is fully initialized and ready to use.</p>
            
            <div class="button-row">
                <a href="home.php" class="btn primary">🏠 Go to Homepage</a>
                <a href="register.php" class="btn">👤 Create Account</a>
                <a href="login.php" class="btn">🔑 Login</a>
                <a href="contact.php" class="btn">📝 Join Membership</a>
            </div>
        </div>
        
        <div class="next-steps">
            <h3>Getting Started:</h3>
            <div class="step-item">Create an account via the <strong>Register</strong> page</div>
            <div class="step-item">Login with your credentials</div>
            <div class="step-item">Go to the <strong>Home</strong> page and upload photos in the "Submit Your Photography" section</div>
            <div class="step-item">Fill out the <strong>Membership Application</strong> in the Contact form to join the team</div>
            <div class="step-item">Admin approvals appear in the <strong>Control Panel</strong></div>
        </div>
        
    <?php else: ?>
        <!-- Setup needed -->
        <div class="status-box" style="background: rgba(255, 107, 107, 0.1); border-color: #ff6b6b;">
            <h2 style="color: #ff6b6b; text-align: center; margin-top: 0;">⚠️ Database Setup Required</h2>
            <p style="text-align: center; margin-top: 1rem; font-size: 1.1rem;">The database tables need to be initialized before using the application.</p>
            
            <div class="button-row">
                <a href="setup.php" class="btn primary">🔧 Run Database Setup</a>
            </div>
        </div>
        
        <div class="next-steps">
            <h3>Setup Instructions:</h3>
            <div class="step-item">Click the <strong>"Run Database Setup"</strong> button above</div>
            <div class="step-item">Wait for the setup to complete (you'll see a success message)</div>
            <div class="step-item">Return to this page to continue</div>
            <div class="step-item">Proceed with account creation and using the application</div>
        </div>
        
        <div class="next-steps" style="background: rgba(212, 175, 55, 0.15); border-color: #d4af37;">
            <h3>Troubleshooting:</h3>
            <div class="step-item">Ensure XAMPP MySQL server is <strong>running</strong> (green indicator)</div>
            <div class="step-item">If setup fails, try the manual method in <strong>phpMyAdmin</strong></div>
            <div class="step-item">Check the <strong><a href="DATABASE_SETUP.md" style="color: #d4af37;">DATABASE_SETUP.md</a></strong> file for detailed instructions</div>
        </div>
    <?php endif; ?>
    
</div>

<?php include 'footer.php'; ?>
