<?php
/**
 * KUET Photography Society - Navigation Bar Template
 */
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo-section">
            <a href="home.php" class="club-logo-link">
                <img src="images/logo.png" alt="KUET Photography Club Logo" class="club-logo">
            </a>
            <span class="club-name">KUET Photography Society</span>
        </div>
        <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <ul class="nav-links" id="nav-menu">
            <li><a class="page-link <?php echo $current_page === 'home' ? 'active' : ''; ?>" href="home.php">Home</a></li>
            <li><a class="page-link <?php echo $current_page === 'about' ? 'active' : ''; ?>" href="about.php">About</a></li>
            <li><a class="page-link <?php echo $current_page === 'gallery' ? 'active' : ''; ?>" href="gallery.php">Gallery</a></li>
            <li><a class="page-link <?php echo $current_page === 'events' ? 'active' : ''; ?>" href="events.php">Events</a></li>
            <li><a class="page-link <?php echo $current_page === 'team' ? 'active' : ''; ?>" href="team.php">Team</a></li>
            <li><a class="page-link <?php echo $current_page === 'contact' ? 'active' : ''; ?>" href="contact.php">Contact</a></li>
            <li><a class="page-link <?php echo $current_page === 'register' ? 'active' : ''; ?>" href="register.php">Register</a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-separator"></li>
                <li class="profile-menu">
                    <button class="profile-btn" id="profile-btn">
                        <?php echo isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' ? '⚙️ Admin' : '👤 ' . htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    </button>
                    <div class="dropdown-menu" id="dropdown-menu">
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="control.php">📊 Control Panel</a>
                        <?php else: ?>
                            <a href="home.php#submit-photo">📸 Submit Photo</a>
                            <a href="profile.php">👤 My Profile</a>
                        <?php endif; ?>
                        <a href="api/logout.php" class="logout-link">🚪 Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li class="nav-separator"></li>
                <li><a class="page-link cta-link" href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
    const profileBtn = document.getElementById('profile-btn');
    const dropdownMenu = document.getElementById('dropdown-menu');
    
    if (profileBtn && dropdownMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('active');
        });
        
        document.addEventListener('click', function() {
            dropdownMenu.classList.remove('active');
        });
    }
</script>
