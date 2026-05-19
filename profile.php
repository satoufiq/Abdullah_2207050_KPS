<?php
/**
 * KUET Photography Society - User Profile Page
 */
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Fetch user submissions
$submissions = [];
$stmt = $conn->prepare("SELECT id, title, category, location, image_url, is_approved, created_at FROM photos WHERE photographer_id = ? ORDER BY created_at DESC LIMIT 12");
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    $stmt->close();
}

// Fetch user registered events
$events = [];
$stmt = $conn->prepare("SELECT e.id, e.title, e.date, e.location, e.description FROM events e
    JOIN event_registrations er ON e.id = er.event_id 
    WHERE er.user_id = ? ORDER BY e.date DESC");
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
}

$page_title = 'My Profile';
include 'header.php';
include 'navbar.php';
?>

<main class="profile-container">
    <section class="profile-header">
        <div class="profile-info">
            <div class="profile-avatar">👤</div>
            <div class="profile-details">
                <h1 id="profile-name"><?php echo htmlspecialchars($user_name); ?></h1>
                <p id="profile-email"><?php echo htmlspecialchars($user_email); ?></p>
            </div>
        </div>
    </section>

    <section class="profile-content">
        <div class="profile-tabs">
            <button class="tab-btn active" data-tab="submissions">📤 My Submissions</button>
            <button class="tab-btn" data-tab="events">📅 My Events</button>
            <button class="tab-btn" data-tab="upload">⬆️ Upload New Photo</button>
        </div>

        <!-- My Submissions Tab -->
        <div id="submissions-tab" class="tab-content active">
            <h2>My Photo Submissions</h2>
            <?php if (count($submissions) > 0): ?>
                <div class="submissions-list">
                    <?php foreach ($submissions as $photo): ?>
                        <div class="submission-card">
                            <div class="submission-image">
                                <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                <span class="submission-status <?php echo $photo['is_approved'] ? 'approved' : 'pending'; ?>"><?php echo $photo['is_approved'] ? 'Approved' : 'Pending'; ?></span>
                            </div>
                            <div class="submission-info">
                                <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                                <p class="category"><?php echo htmlspecialchars($photo['category']); ?> • <?php echo htmlspecialchars($photo['location']); ?></p>
                                <p class="date">Uploaded: <?php echo date('M d, Y', strtotime($photo['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">No submissions yet. <a href="#upload-tab" class="tab-btn" data-tab="upload">Upload your first photo</a></p>
            <?php endif; ?>
        </div>

        <!-- My Events Tab -->
        <div id="events-tab" class="tab-content">
            <h2>My Registered Events</h2>
            <?php if (count($events) > 0): ?>
                <div class="events-list">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                                <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                            </div>
                            <div class="event-info">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="location">📍 <?php echo htmlspecialchars($event['location']); ?></p>
                                <p class="description"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">You haven't registered for any events yet. <a href="events.php">Browse upcoming events</a></p>
            <?php endif; ?>
        </div>

        <!-- Upload Tab -->
        <div id="upload-tab" class="tab-content">
            <h2>Upload New Photo</h2>
            <form id="profile-upload-form" class="upload-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="upload-grid">
                    <label>
                        <span>Photo Title *</span>
                        <input type="text" name="title" id="upload-title" placeholder="Sunset at KUET" required>
                    </label>
                    <label>
                        <span>Category *</span>
                        <select name="category" id="upload-category" required>
                            <option value="">Select category</option>
                            <option value="portrait">Portrait</option>
                            <option value="street">Street</option>
                            <option value="nature">Nature</option>
                            <option value="event">Event</option>
                            <option value="creative">Creative</option>
                            <option value="product">Product</option>
                            <option value="extra">Other</option>
                        </select>
                    </label>
                </div>
                <label>
                    <span>Location *</span>
                    <input type="text" name="location" id="upload-location" placeholder="KUET Campus, Khulna" required>
                </label>
                <label>
                    <span>Photo File *</span>
                    <input type="file" id="upload-file" name="photo" accept="image/*" required>
                </label>
                <label>
                    <span>Description</span>
                    <textarea name="description" id="upload-description" placeholder="Share the story behind this photo..." rows="4"></textarea>
                </label>
                <button type="submit" class="cta-button">Upload Photo</button>
            </form>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
            });
            
            // Show selected tab and mark button as active
            document.getElementById(tabName + '-tab').classList.add('active');
            this.classList.add('active');
        });
    });

    // Photo upload handler
    document.getElementById('profile-upload-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('api/submit_photo.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert('Photo uploaded successfully! It will appear after admin approval.');
                this.reset();
                // Reload submissions
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Upload failed'));
            }
        } catch (error) {
            alert('Upload failed. Please try again.');
        }
    });
</script>
