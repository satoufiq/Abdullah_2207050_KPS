<?php
/**
 * KUET Photography Society - Navigation Bar Template
 */
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo-section">
            <img src="images/logo.png" alt="KUET Photography Club Logo" class="club-logo">
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
        </ul>
    </div>
</nav>
