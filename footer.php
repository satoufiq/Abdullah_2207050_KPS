<?php
/**
 * KUET Photography Society - Footer Template
 */
$current_year = date('Y');
?>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>KUET Photography Society</h4>
                <p>Capturing moments, telling stories since 2018.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="contact.php">Contact</a></li>
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
            <p>&copy; <span id="year"><?php echo $current_year; ?></span> KUET Photography Society. All rights reserved.</p>
        </div>
    </footer>
    </body>
</html>
