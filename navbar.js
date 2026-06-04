/**
 * Global Navbar & Footer Sync
 * Include this near the end of HTML pages before page-specific inline scripts.
 */

(function () {
    const currentFile = window.location.pathname.split('/').pop() || 'home.html';
    const isPhpPage = currentFile.toLowerCase().endsWith('.php');
    const pageBase = currentFile.replace(/\.(html|php)$/i, '') || 'home';
    const pageSuffix = isPhpPage ? '.php' : '.html';
    const currentYear = new Date().getFullYear();

    function pageLink(name) {
        return `${name}${pageSuffix}`;
    }

    const navbarMarkup = `
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo-section">
                <a href="${pageLink('home')}" class="club-logo-link">
                    <span class="club-name">KUET Photography Society</span>
                </a>
            </div>
            <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-links" id="nav-menu">
                <li><a class="page-link" href="${pageLink('home')}">Home</a></li>
                <li><a class="page-link" href="${pageLink('about')}">About</a></li>
                <li><a class="page-link" href="${pageLink('gallery')}">Gallery</a></li>
                <li><a class="page-link" href="${pageLink('events')}">Events</a></li>
                <li><a class="page-link" href="${pageLink('team')}">Team</a></li>
                <li><a class="page-link" href="${pageLink('contact')}">Contact</a></li>
                <li id="auth-links">
                    <a class="page-link" href="${pageLink('login')}">Login</a> |
                    <a class="page-link" href="${pageLink('register')}">Register</a>
                </li>
                <li id="profile-menu" style="display: none;">
                    <button class="profile-btn" id="profile-btn">👤 <span id="user-name">Profile</span> ▼</button>
                    <div class="dropdown-menu" id="dropdown-menu" style="display: none;">
                        <a href="${pageLink('profile')}">📋 My Profile</a>
                        <a href="api/logout.php">🚪 Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    `;

    const footerMarkup = `
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>KUET Photography Society</h4>
                <p>Capturing moments, telling stories since 2018.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="${pageLink('home')}">Home</a></li>
                    <li><a href="${pageLink('gallery')}">Gallery</a></li>
                    <li><a href="${pageLink('events')}">Events</a></li>
                    <li><a href="${pageLink('contact')}">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Follow Us</h4>
                <ul>
                    <li><a href="#" target="_blank" rel="noopener noreferrer">Facebook</a></li>
                    <li><a href="#" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                    <li><a href="#" target="_blank" rel="noopener noreferrer">Twitter</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p>Email: info@kuetphoto.com</p>
                <p>Location: KUET, Khulna</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <span id="year">${currentYear}</span> KUET Photography Society. All rights reserved.</p>
        </div>
    </footer>
    `;

    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.outerHTML = navbarMarkup;
    }

    const footer = document.querySelector('.footer');
    if (footer) {
        footer.outerHTML = footerMarkup;
    }

    const activeLink = document.querySelector(`.nav-links a[href="${pageLink(pageBase)}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    // Check session status for profile menu
    async function checkSessionStatus() {
        try {
            const response = await fetch('api/check_session.php');
            const data = await response.json();
            
            const authLinks = document.getElementById('auth-links');
            const profileMenu = document.getElementById('profile-menu');
            const userName = document.getElementById('user-name');
            
            if (data.is_member || data.is_admin) {
                // User is logged in
                if (authLinks) authLinks.style.display = 'none';
                if (profileMenu) profileMenu.style.display = 'block';
                if (userName) userName.textContent = data.user_name || 'User';
                
                // Update profile link to profile.php instead of profile.html
                const profileLinks = document.querySelectorAll('a[href*="profile"]');
                profileLinks.forEach(link => {
                    if (link.href.includes('profile.html')) {
                        link.href = pageLink('profile');
                    }
                });
            } else {
                // User is logged out - show login/register
                if (authLinks) authLinks.style.display = 'block';
                if (profileMenu) profileMenu.style.display = 'none';
            }
        } catch (error) {
            // Session check failed — ignore silently in production
        }
    }
    
    // Run session check after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkSessionStatus);
    } else {
        checkSessionStatus();
    }

    // Dropdown toggle
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
})();
