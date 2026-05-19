<?php
/**
 * KUET Photography Society - Gallery Page
 */
require_once 'config.php';

$page_title = 'Gallery';
$current_page = 'gallery';

// Fetch gallery images
$gallery_images = [];
if (isset($conn)) {
    $query = "SELECT p.id, p.title, p.image_url, p.category, p.location, p.lens_info, COALESCE(u.name, 'Unknown') as name, p.likes, p.views, p.is_photo_of_week
              FROM photos p 
              LEFT JOIN users u ON p.photographer_id = u.id 
              WHERE p.is_approved = 1 
              ORDER BY p.is_photo_of_week DESC, p.is_featured DESC, p.created_at DESC";
    $result = $conn->query($query);
    if ($result) {
        $gallery_images = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        error_log("Gallery query error: " . $conn->error);
    }
}

// Get categories
$categories = [];
if (isset($conn)) {
    $query = "SELECT DISTINCT category FROM photos WHERE is_approved = 1 ORDER BY category";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row['category']) {
                $categories[] = $row['category'];
            }
        }
    }
}

include 'header.php';
include 'navbar.php';
?>

<div class="site-loader" id="site-loader"><span></span></div>

<header class="page-hero gallery-hero reveal-target" style="background-image: linear-gradient(135deg, rgba(8,17,31,0.68), rgba(212,175,55,0.08)), url('images/collections/creative/474908052_1024641812833930_1691534703905046745_n.jpg'); background-size: cover; background-position: center;">
    <div class="page-hero-content">
        <p class="hero-badge">Curated Collection</p>
        <h1>Our Visual Archive</h1>
        <p class="hero-subtitle">Explore the finest photography from our community</p>
    </div>
</header>

<section class="gallery-container reveal-target">
    <div class="gallery-controls">
        <div class="gallery-filter">
            <button class="filter-btn active" data-filter="all">All Photos</button>
            <?php foreach ($categories as $category): ?>
                <button class="filter-btn" data-filter="<?php echo strtolower($category); ?>">
                    <?php echo ucfirst($category); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="gallery-search">
            <input type="text" id="gallery-search" placeholder="Search photos..." class="search-input">
        </div>
    </div>

    <?php if (empty($gallery_images)): ?>
        <div class="gallery-empty">
            <p class="empty-message">No photos in the gallery yet.</p>
            <p class="empty-subtitle">Be the first to submit your amazing work!</p>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'member'): ?>
                <a href="home.php#photo-submission" class="cta-button">Submit Photos</a>
            <?php else: ?>
                <a href="register.php" class="cta-button">Join as Member</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="gallery-grid" id="gallery-grid">
            <?php foreach ($gallery_images as $photo): ?>
                <div class="gallery-item" data-category="<?php echo strtolower($photo['category']); ?>" data-photo-id="<?php echo $photo['id']; ?>">
                    <div class="gallery-card">
                        <!-- Image Container -->
                        <div class="gallery-image-wrapper">
                            <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($photo['title']); ?>"
                                 class="gallery-image"
                                 loading="lazy"
                                 data-title="<?php echo htmlspecialchars($photo['title']); ?>"
                                 data-photographer="<?php echo htmlspecialchars($photo['name']); ?>">
                            
                            <?php if ($photo['is_photo_of_week']): ?>
                                <div class="photo-badge premium-badge">🏆 Photo of the Week</div>
                            <?php endif; ?>

                            <!-- Quick Action Overlay -->
                            <div class="quick-actions-overlay">
                                <button class="quick-action-btn view-full-btn" data-photo-id="<?php echo $photo['id']; ?>" title="View Full Size">
                                    <span class="icon">👁️</span>
                                    <span class="label">View</span>
                                </button>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="api/download_photo.php?photo_id=<?php echo $photo['id']; ?>" 
                                       class="quick-action-btn download-full-btn" title="Download">
                                        <span class="icon">⬇️</span>
                                        <span class="label">Download</span>
                                    </a>
                                <?php else: ?>
                                    <button class="quick-action-btn download-full-btn" onclick="alert('Please login to download')" title="Download">
                                        <span class="icon">⬇️</span>
                                        <span class="label">Download</span>
                                    </button>
                                <?php endif; ?>
                                <button class="quick-action-btn like-btn" data-photo-id="<?php echo $photo['id']; ?>" title="Like this photo">
                                    <span class="icon">❤️</span>
                                    <span class="label">Like</span>
                                </button>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="gallery-card-body">
                            <div class="card-header">
                                <h3 class="photo-title"><?php echo htmlspecialchars($photo['title']); ?></h3>
                                <span class="category-badge"><?php echo ucfirst($photo['category']); ?></span>
                            </div>

                            <p class="photographer-name">by <?php echo htmlspecialchars($photo['name']); ?></p>

                            <div class="photo-details">
                                <?php if ($photo['location']): ?>
                                    <div class="detail-item">
                                        <span class="detail-icon">📍</span>
                                        <span class="detail-text"><?php echo htmlspecialchars($photo['location']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($photo['lens_info']): ?>
                                    <div class="detail-item">
                                        <span class="detail-icon">📷</span>
                                        <span class="detail-text"><?php echo htmlspecialchars($photo['lens_info']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <div class="stat-group">
                                    <span class="stat-item">
                                        <span class="stat-icon">👁️</span>
                                        <span class="stat-value"><?php echo $photo['views']; ?></span>
                                    </span>
                                    <span class="stat-item">
                                        <span class="stat-icon">❤️</span>
                                        <span class="stat-value"><?php echo $photo['likes']; ?></span>
                                    </span>
                                </div>
                                <button class="card-action-btn view-details-btn" data-photo-id="<?php echo $photo['id']; ?>">
                                    View Details →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox-modal">
    <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close">&times;</button>
    <div class="lightbox-content">
        <img class="lightbox-image" src="" alt="">
        <div class="lightbox-info">
            <h2 id="lightbox-title"></h2>
            <p id="lightbox-photographer"></p>
        </div>
    </div>
</div>

<script>
    // Gallery filtering
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.gallery-item').forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Search functionality
    document.getElementById('gallery-search').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        
        document.querySelectorAll('.gallery-item').forEach(item => {
            const title = item.querySelector('.photo-title').textContent.toLowerCase();
            if (title.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Lightbox functionality - FIXED
    function openLightbox(img) {
        const title = img.getAttribute('data-title') || 'Photo';
        const photographer = img.getAttribute('data-photographer') || 'Unknown';
        
        document.querySelector('.lightbox-image').src = img.src;
        document.querySelector('.lightbox-image').alt = title;
        document.getElementById('lightbox-title').textContent = title;
        document.getElementById('lightbox-photographer').textContent = 'by ' + photographer;
        
        document.getElementById('lightbox').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // View full size buttons
    document.querySelectorAll('.view-full-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const img = this.closest('.gallery-image-wrapper').querySelector('.gallery-image');
            openLightbox(img);
        });
    });

    // Close lightbox when clicking outside
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this || e.target.classList.contains('lightbox-close')) {
            closeLightbox();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });

    // Like button functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const photoId = this.getAttribute('data-photo-id');
            fetch('api/like_photo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'photo_id=' + photoId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.add('liked');
                    alert('Photo liked!');
                } else {
                    alert(data.error || 'Please login to like photos');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // View details button
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const photoId = this.getAttribute('data-photo-id');
            const img = this.closest('.gallery-item').querySelector('.gallery-image');
            openLightbox(img);
        });
    });
</script>

<?php include 'footer.php'; ?>
