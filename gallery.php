<?php
/**
 * KUET Photography Society - Gallery Page
 */
require_once 'config.php';

$page_title = 'Gallery';
$current_page = 'gallery';
$body_class = 'luxury-site gallery-page';

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

<header class="page-hero gallery-hero reveal-target" style="background-image: linear-gradient(135deg, rgba(25,30,54,0.76), rgba(217,180,74,0.14)), url('images/collections/creative/474908052_1024641812833930_1691534703905046745_n.jpg'); background-size: cover; background-position: center;">
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
            <?php $gallery_index = 1; ?>
            <?php foreach ($gallery_images as $photo): ?>
                <div class="gallery-item" data-category="<?php echo strtolower($photo['category']); ?>" data-photo-id="<?php echo $photo['id']; ?>">
                    <div class="gallery-card">
                        <div class="gallery-image-wrapper">
                            <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($photo['title']); ?>"
                                 class="gallery-image"
                                 loading="lazy"
                                 data-photo-id="<?php echo $photo['id']; ?>"
                                   data-location="<?php echo htmlspecialchars($photo['location'] ?? ''); ?>"
                                   data-lens-info="<?php echo htmlspecialchars($photo['lens_info'] ?? ''); ?>"
                                   data-views="<?php echo (int) $photo['views']; ?>"
                                   data-likes="<?php echo (int) $photo['likes']; ?>"
                                   data-category="<?php echo htmlspecialchars($photo['category']); ?>"
                                 data-title="<?php echo htmlspecialchars($photo['title']); ?>"
                                 data-photographer="<?php echo htmlspecialchars($photo['name']); ?>">
                            
                            <?php if ($photo['is_photo_of_week']): ?>
                                <div class="photo-badge premium-badge">🏆 Photo of the Week</div>
                            <?php endif; ?>
                        </div>

                        <div class="gallery-card-body">
                            <div class="card-topline">
                                <span class="category-badge"><?php echo strtoupper($photo['category']); ?></span>
                                <span class="card-index"><?php echo str_pad($gallery_index, 2, '0', STR_PAD_LEFT); ?></span>
                            </div>

                            <h3 class="photo-title"><?php echo htmlspecialchars($photo['title']); ?></h3>

                            <p class="card-excerpt">
                                <?php
                                    $card_excerpt_parts = array();
                                    if (!empty($photo['location'])) {
                                        $card_excerpt_parts[] = $photo['location'];
                                    }
                                    if (!empty($photo['lens_info'])) {
                                        $card_excerpt_parts[] = $photo['lens_info'];
                                    }
                                    if (empty($card_excerpt_parts)) {
                                        $card_excerpt_parts[] = 'Clean frame and strong archival presence';
                                    }

                                    echo htmlspecialchars(implode(' • ', $card_excerpt_parts));
                                ?>
                            </p>

                            <div class="card-footer">
                                <button class="card-action-btn view-details-btn" data-photo-id="<?php echo $photo['id']; ?>">
                                    Open full frame
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php $gallery_index++; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox-modal">
    <button class="lightbox-close" onclick="photoLightboxClose()" aria-label="Close">&times;</button>
    <div class="lightbox-content">
        <img class="lightbox-image" src="" alt="">
        <div class="lightbox-info">
            <p class="lightbox-kicker">Full Frame View</p>
            <h2 id="lightbox-title"></h2>
            <p id="lightbox-photographer"></p>
            <p id="lightbox-meta" class="lightbox-meta"></p>

            <div class="lightbox-stats">
                <div class="lightbox-stat">
                    <span class="stat-icon">👁️</span>
                    <span id="lightbox-views"></span>
                </div>
                <div class="lightbox-stat">
                    <span class="stat-icon">❤️</span>
                    <span id="lightbox-likes"></span>
                </div>
            </div>

            <div class="lightbox-actions">
                <button type="button" id="lightbox-like-btn" class="download-btn lightbox-action-btn">Like</button>
                <a id="lightbox-download" class="download-btn lightbox-action-btn" href="#" download>Download</a>
            </div>
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
    function photoLightboxOpen(img) {
        const card = img.closest('.gallery-item');
        const photoId = img.getAttribute('data-photo-id') || (card ? card.getAttribute('data-photo-id') : '');
        const title = img.getAttribute('data-title') || 'Photo';
        const photographer = img.getAttribute('data-photographer') || 'Unknown';
        const category = img.getAttribute('data-category') || (card ? card.getAttribute('data-category') : '');
        const views = img.getAttribute('data-views') || '0';
        const likes = img.getAttribute('data-likes') || '0';
        const location = img.getAttribute('data-location') || '';
        const lensInfo = img.getAttribute('data-lens-info') || '';
        const downloadUrl = photoId ? 'api/download_photo.php?photo_id=' + photoId : '#';
        
        document.querySelector('.lightbox-image').src = img.src;
        document.querySelector('.lightbox-image').alt = title;
        document.getElementById('lightbox-title').textContent = title;
        document.getElementById('lightbox-photographer').textContent = 'by ' + photographer;
        document.getElementById('lightbox-meta').textContent = [category ? category.toUpperCase() : '', location, lensInfo].filter(Boolean).join(' • ');
        document.getElementById('lightbox-views').textContent = views + ' views';
        document.getElementById('lightbox-likes').textContent = likes + ' likes';
        document.getElementById('lightbox-download').setAttribute('href', downloadUrl);
        document.getElementById('lightbox-download').setAttribute('download', '');
        document.getElementById('lightbox-like-btn').setAttribute('data-photo-id', photoId);
        
        document.getElementById('lightbox').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function photoLightboxClose() {
        document.getElementById('lightbox').classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // View full size buttons
    document.querySelectorAll('.view-full-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const img = this.closest('.gallery-image-wrapper').querySelector('.gallery-image');
            photoLightboxOpen(img);
        });
    });

    // Close lightbox when clicking outside
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this || e.target.classList.contains('lightbox-close')) {
            photoLightboxClose();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            photoLightboxClose();
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

    document.getElementById('lightbox-like-btn').addEventListener('click', function() {
        const photoId = this.getAttribute('data-photo-id');
        if (!photoId) {
            return;
        }

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
                const likesEl = document.getElementById('lightbox-likes');
                const currentLikes = parseInt((likesEl.textContent || '0').replace(/[^0-9]/g, ''), 10) || 0;
                likesEl.textContent = (currentLikes + 1) + ' likes';
                this.textContent = 'Liked';
                this.disabled = true;
                alert('Photo liked!');
            } else {
                alert(data.error || 'Please login to like photos');
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // View details button
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const img = this.closest('.gallery-item').querySelector('.gallery-image');
            photoLightboxOpen(img);
        });
    });
</script>

<?php include 'footer.php'; ?>
