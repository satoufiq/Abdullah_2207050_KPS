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

    <section class="featured-work" id="highlights">
        <div class="section-heading">
            <p class="eyebrow">This Week's Highlights</p>
            <h2>Weekly Best & Featured Images</h2>
        </div>
        <div class="featured-work-grid">
            <?php
            // Fetch photo of the week
            $photo_of_week = null;
            $featured_photos = [];
            
            if (isset($conn)) {
                // Get photo of the week
                $query = "SELECT p.id, p.title, p.image_url, p.description, p.category, p.location, p.lens_info, u.name 
                         FROM photos p 
                         JOIN users u ON p.photographer_id = u.id 
                         WHERE p.is_photo_of_week = 1 AND p.is_approved = 1 
                         LIMIT 1";
                $result = $conn->query($query);
                if ($result && $result->num_rows > 0) {
                    $photo_of_week = $result->fetch_assoc();
                }
                
                // Get featured photos (limit to 2 additional)
                $query = "SELECT p.id, p.title, p.image_url, p.description, p.category, p.location, p.lens_info, u.name 
                         FROM photos p 
                         JOIN users u ON p.photographer_id = u.id 
                         WHERE p.is_featured = 1 AND p.is_approved = 1 
                         ORDER BY p.created_at DESC 
                         LIMIT 2";
                $result = $conn->query($query);
                if ($result) {
                    $featured_photos = $result->fetch_all(MYSQLI_ASSOC);
                }
            }
            ?>
            
            <?php if ($photo_of_week): ?>
                <article class="feature-card feature-primary reveal-target featured-photo" data-title="<?php echo htmlspecialchars($photo_of_week['title']); ?>" data-by="Photographer: <?php echo htmlspecialchars($photo_of_week['name']); ?>" data-meta="Category: <?php echo htmlspecialchars($photo_of_week['category']); ?> | Lens: <?php echo htmlspecialchars($photo_of_week['lens_info'] ?? 'Unknown'); ?> | Location: <?php echo htmlspecialchars($photo_of_week['location'] ?? 'Unknown'); ?>">
                    <img src="<?php echo htmlspecialchars($photo_of_week['image_url']); ?>" alt="Photo of the week">
                    <div class="feature-card-content">
                        <p class="feature-label">🏆 Photo of the Week</p>
                        <h3><?php echo htmlspecialchars($photo_of_week['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($photo_of_week['description'] ?? 'Exceptional work from our community', 0, 100)); ?></p>
                        <button class="learn-more" data-lightbox-trigger>View Details</button>
                    </div>
                </article>
            <?php endif; ?>
            
            <?php foreach ($featured_photos as $featured): ?>
                <article class="feature-card reveal-target featured-photo" data-title="<?php echo htmlspecialchars($featured['title']); ?>" data-by="Photographer: <?php echo htmlspecialchars($featured['name']); ?>" data-meta="Category: <?php echo htmlspecialchars($featured['category']); ?>">
                    <img src="<?php echo htmlspecialchars($featured['image_url']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                    <div class="feature-card-content">
                        <p class="feature-label">⭐ Featured Work</p>
                        <h3><?php echo htmlspecialchars($featured['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($featured['description'] ?? 'Outstanding photography', 0, 100)); ?></p>
                        <button class="learn-more" data-lightbox-trigger>View Details</button>
                    </div>
                </article>
            <?php endforeach; ?>
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


    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'member'): ?>
    <section class="submit-photo reveal-target" id="submit-photo">
        <div class="section-heading">
            <p class="eyebrow">Share Your Work</p>
            <h2>Submit Your Photography</h2>
        </div>
        <div class="submit-photo-container">
            <form id="photo-submission-form" method="POST" action="api/submit_photo.php" enctype="multipart/form-data" class="photo-form">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

                <div class="form-group">
                    <label for="photo-title">Photo Title *</label>
                    <input type="text" id="photo-title" name="title" placeholder="Give your photo a compelling title" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="photo-category">Category *</label>
                        <select id="photo-category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="nature">Nature</option>
                            <option value="portrait">Portrait</option>
                            <option value="street">Street</option>
                            <option value="product">Product</option>
                            <option value="event">Event</option>
                            <option value="creative">Creative</option>
                            <option value="extra">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="photo-location">Location</label>
                        <input type="text" id="photo-location" name="location" placeholder="Where was this taken?">
                    </div>

                    <div class="form-group">
                        <label for="photo-lens">Lens/Equipment</label>
                        <input type="text" id="photo-lens" name="lens_info" placeholder="e.g., 50mm f/1.8">
                    </div>
                </div>

                <div class="form-group">
                    <label for="photo-description">Description</label>
                    <textarea id="photo-description" name="description" placeholder="Tell the story behind this photo..." rows="4" maxlength="500"></textarea>
                    <small>Max 500 characters</small>
                </div>

                <div class="form-group">
                    <label for="photo-file">Upload Photo *</label>
                    <div class="file-upload">
                        <input type="file" id="photo-file" name="photo" accept="image/*" required>
                        <span class="file-label">Click to select or drag and drop (Max 10MB)</span>
                    </div>
                </div>

                <button type="submit" class="cta-button submit-btn">Submit Photo</button>
                <p id="submission-message"></p>
            </form>
        </div>
    </section>

    <script>
        document.getElementById('photo-submission-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const messageEl = document.getElementById('submission-message');
            
            try {
                const response = await fetch('api/submit_photo.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    messageEl.style.color = 'var(--success)';
                    messageEl.textContent = '✓ ' + data.message;
                    this.reset();
                    setTimeout(() => {
                        messageEl.textContent = '';
                    }, 5000);
                } else {
                    messageEl.style.color = 'var(--error)';
                    messageEl.textContent = '✕ ' + data.error;
                }
            } catch (error) {
                messageEl.style.color = 'var(--error)';
                messageEl.textContent = '✕ An error occurred. Please try again.';
            }
        });

        // Drag and drop file upload
        const fileInput = document.getElementById('photo-file');
        const fileUpload = document.querySelector('.file-upload');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUpload.addEventListener(eventName, () => {
                fileUpload.classList.add('dragover');
            });
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileUpload.addEventListener(eventName, () => {
                fileUpload.classList.remove('dragover');
            });
        });
        
        fileUpload.addEventListener('drop', (e) => {
            fileInput.files = e.dataTransfer.files;
        });
    </script>
    <?php else: ?>

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

    <?php endif; ?>


<?php
include 'footer.php';
?>
