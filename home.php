<?php
/**
 * KUET Photography Society - Home Page
 */
require_once 'config.php';

$page_title = 'Home';
$current_page = 'home';

// Fetch featured photos from database (will be used once DB is set up)
$featured_photos = [];
if (isset($conn)) {
    $query = "SELECT * FROM photos WHERE is_featured = 1 LIMIT 4";
    $result = $conn->query($query);
    if ($result) {
        $featured_photos = $result->fetch_all(MYSQLI_ASSOC);
    }
}

include 'header.php';
include 'navbar.php';
?>

    <div class="site-loader" id="site-loader"><span></span></div>

    <section class="hero" id="home">
        <div class="hero-slider" aria-hidden="true">
            <div class="hero-slide is-active" style="background-image: url('images/hero-banner.jpg');"></div>
            <div class="hero-slide" style="background-image: url('images/collections/nature/472355752_1010415957589849_7730853507225014461_n.jpg');"></div>
            <div class="hero-slide" style="background-image: url('images/collections/extra/476166310_1031456308819147_2939687961136825344_n.jpg');"></div>
            <div class="hero-slide" style="background-image: url('images/collections/extra/556579849_1203912284906881_8471027009373828179_n.jpg');"></div>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content reveal-target">
            <p class="hero-badge">KHULNA UNIVERSITY OF ENGINEERING AND TECHNOLOGY</p>
            <h1>Light, Culture, Stories</h1>
            <p class="tagline">Where passion meets precision. A photography society crafting visual narratives.</p>
            <div class="hero-actions">
                <a class="cta-button page-link" href="gallery.php">Explore Gallery</a>
                <a class="cta-button cta-button-secondary page-link" href="contact.php">Join Us</a>
            </div>
        </div>
    </section>

    <section class="featured-intro reveal-target">
        <div class="intro-grid">
            <div class="intro-card">
                <p class="intro-eyebrow">About KUET Photography Society</p>
                <h2>Building a culture of excellence</h2>
                <p>We're a community of visual storytellers at KUET, dedicated to exploring light, composition, and narrative through photography. Since 2018, we've trained members, published work, and captured the essence of campus life.</p>
                <a class="inline-link" href="about.php">Learn our story →</a>
            </div>
            <div class="intro-stats">
                <div class="stat-block reveal-target">
                    <span class="stat-value" data-count-to="70" data-duration="2000" data-suffix="+">0+</span>
                    <span class="stat-name">Active Members</span>
                </div>
                <div class="stat-block reveal-target">
                    <span class="stat-value" data-count-to="32" data-duration="2000" data-suffix="+">0+</span>
                    <span class="stat-name">Annual Events</span>
                </div>
                <div class="stat-block reveal-target">
                    <span class="stat-value" data-count-to="14" data-duration="2000">0</span>
                    <span class="stat-name">Major Awards</span>
                </div>
                <div class="stat-block reveal-target">
                    <span class="stat-value" data-count-to="8" data-duration="2000">0</span>
                    <span class="stat-name">Photography Genres</span>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-work" id="highlights">
        <div class="section-heading">
            <p class="eyebrow">This Week's Highlights</p>
            <h2>Curated moments from our community</h2>
        </div>
        <div class="featured-work-grid">
            <article class="feature-card feature-primary reveal-target featured-photo" data-title="Quiet Light, Strong Composition" data-by="Photographer: Abdullah Al Nafi" data-meta="Category: Nature | Lens: 50mm | Location: KUET Lake">
                <img src="images/collections/extra/560642219_1215681497063293_2118513156112656301_n.jpg" alt="Photo of the week">
                <div class="feature-card-content">
                    <p class="feature-label">Photo of the Week</p>
                    <h3>Quiet Light, Strong Composition</h3>
                    <p>A nature frame balancing atmosphere with narrative depth.</p>
                    <button class="learn-more" data-lightbox-trigger>View Details</button>
                </div>
            </article>

            <article class="feature-card reveal-target featured-photo" data-title="Urban Energy" data-by="Photographer: Sarah Khan" data-meta="Category: Street | Lens: 35mm | Location: Downtown Khulna">
                <img src="images/collections/street/sample-street.jpg" alt="Urban street photography">
                <div class="feature-card-content">
                    <p class="feature-label">Community Spotlight</p>
                    <h3>Urban Energy</h3>
                    <p>Capturing the pulse of everyday city moments.</p>
                    <button class="learn-more" data-lightbox-trigger>View Details</button>
                </div>
            </article>

            <article class="feature-card reveal-target featured-photo" data-title="Portrait in Time" data-by="Photographer: Md. Hasan" data-meta="Category: Portrait | Lens: 85mm | Location: KUET Studio">
                <img src="images/collections/portrait/sample-portrait.jpg" alt="Portrait photography">
                <div class="feature-card-content">
                    <p class="feature-label">Member Feature</p>
                    <h3>Portrait in Time</h3>
                    <p>Where light reveals character and emotion.</p>
                    <button class="learn-more" data-lightbox-trigger>View Details</button>
                </div>
            </article>
        </div>
    </section>

    <section class="recent-events reveal-target">
        <div class="section-heading">
            <p class="eyebrow">What's Next</p>
            <h2>Upcoming Events</h2>
        </div>
        <div class="events-list">
            <?php
            // Fetch upcoming events from database
            $events = [];
            if (isset($conn)) {
                $query = "SELECT id, title, date, location, description FROM events WHERE date >= NOW() ORDER BY date ASC LIMIT 3";
                $result = $conn->query($query);
                if ($result) {
                    $events = $result->fetch_all(MYSQLI_ASSOC);
                }
            }
            
            if (empty($events)) {
                echo '<p style="text-align: center; color: var(--text-muted);">Check back soon for upcoming events!</p>';
            } else {
                foreach ($events as $event) {
                    echo '<div class="event-item">';
                    echo '<p class="event-date">' . date('M d, Y', strtotime($event['date'])) . '</p>';
                    echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars($event['description']) . '</p>';
                    echo '<p class="event-location">📍 ' . htmlspecialchars($event['location']) . '</p>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a class="cta-button" href="events.php">View All Events</a>
        </div>
    </section>

    <section class="newsletter reveal-target">
        <div class="newsletter-content">
            <h2>Stay Updated</h2>
            <p>Get the latest photography tips, event announcements, and member spotlights delivered to your inbox.</p>
            <form class="newsletter-form" method="POST" action="api/subscribe.php">
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <button type="submit" class="cta-button">Subscribe</button>
            </form>
            <p id="newsletter-message"></p>
        </div>
    </section>

<?php
include 'footer.php';
?>
