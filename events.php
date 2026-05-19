<?php
/**
 * KUET Photography Society - Events Page
 */
require_once 'config.php';

$page_title = 'Events';
$current_page = 'events';

// Fetch all events (upcoming and past)
$events = [];
if (isset($conn)) {
    $query = "SELECT id, title, date, location, description, capacity, registered_count FROM events ORDER BY date DESC";
    $result = $conn->query($query);
    if ($result) {
        $events = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        error_log("Events query error: " . $conn->error);
    }
}

// DEBUG: Log number of events found
error_log("Events found: " . count($events));

// Check if user is registered for events
$user_registrations = [];
if (isset($_SESSION['user_id']) && isset($conn)) {
    $query = "SELECT event_id FROM event_registrations WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $user_registrations[] = $row['event_id'];
        }
        $stmt->close();
    }
}

include 'header.php';
include 'navbar.php';
?>

<div class="site-loader" id="site-loader"><span></span></div>

<section class="events-hero" style="background-image: linear-gradient(135deg, rgba(8,17,31,0.68), rgba(212,175,55,0.08)), url('images/bts/shooting-setup.jpg'); background-size: cover; background-position: center;">
    <div class="hero-content">
        <p class="hero-badge">Join Our Community</p>
        <h1>Photography Events</h1>
        <p class="hero-subtitle">Exclusive workshops, photo walks, and community gatherings</p>
    </div>
</section>

<section class="events-container reveal-target">
    <div class="section-heading">
        <p class="eyebrow">What's Happening</p>
        <h2>Photography Events <?php echo count($events) > 0 ? '(' . count($events) . ')' : ''; ?></h2>
    </div>

    <!-- DEBUG: Show event count -->
    <div style="background: rgba(212, 175, 55, 0.1); padding: 1rem; margin-bottom: 2rem; border-radius: 4px; display: block !important; visibility: visible !important;">
        <p style="color: #d4af37; margin: 0; display: block !important;">DEBUG: Found <?php echo count($events); ?> events</p>
        <?php if (!empty($events)): ?>
            <p style="color: #bbb; margin: 0.5rem 0 0 0; font-size: 0.9rem; display: block !important;">
                <?php foreach ($events as $e): ?>
                    Event: <?php echo htmlspecialchars($e['title']); ?> (<?php echo $e['date']; ?>)<br>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (empty($events)): ?>
        <div class="events-empty">
            <p class="empty-message">No events scheduled at the moment.</p>
            <p class="empty-subtitle">Check back soon for exciting photography workshops and photo walks!</p>
        </div>
    <?php else: ?>
        <div class="events-list" style="display: flex !important; flex-direction: column; gap: 2rem; visibility: visible !important; opacity: 1 !important;">
            <?php foreach ($events as $event): 
                $is_registered = in_array($event['id'], $user_registrations);
                $is_full = $event['capacity'] && $event['registered_count'] >= $event['capacity'];
            ?>
                <div class="event-card <?php echo $is_full ? 'is-full' : ''; ?>" style="display: grid !important; visibility: visible !important; opacity: 1 !important; grid-template-columns: 100px 1fr; gap: 2rem; padding: 2rem; border: 2px solid #d4af37; border-radius: 12px; background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);">
                    <div class="event-date-badge">
                        <span class="date-month"><?php echo date('M', strtotime($event['date'])); ?></span>
                        <span class="date-day"><?php echo date('d', strtotime($event['date'])); ?></span>
                    </div>

                    <div class="event-content">
                        <div class="event-header">
                            <div>
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-datetime">
                                    <span class="time-icon">🕐</span>
                                    <?php echo date('g:i A', strtotime($event['date'])); ?>
                                </p>
                                <p class="event-location">
                                    <span class="location-icon">📍</span>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                            </div>
                            <?php if ($is_registered): ?>
                                <div class="registered-badge">✓ Registered</div>
                            <?php endif; ?>
                        </div>

                        <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>

                        <div class="event-footer">
                            <div class="event-capacity">
                                <span class="capacity-icon">👥</span>
                                <?php 
                                if ($event['capacity']) {
                                    echo $event['registered_count'] . ' / ' . $event['capacity'] . ' Registered';
                                } else {
                                    echo $event['registered_count'] . ' Registered';
                                }
                                ?>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form class="event-form" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                    <input type="hidden" class="event_id" value="<?php echo $event['id']; ?>">
                                    
                                    <?php if ($is_registered): ?>
                                        <button type="button" class="event-btn unregister-btn" onclick="handleEventAction(this, 'unregister')">
                                            Cancel Registration
                                        </button>
                                    <?php elseif ($is_full): ?>
                                        <button type="button" class="event-btn full-btn" disabled>Event Full</button>
                                    <?php else: ?>
                                        <button type="button" class="event-btn register-btn" onclick="handleEventAction(this, 'register')">
                                            Register Now
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="event-btn login-btn">Login to Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="events-info reveal-target">
    <div class="info-grid">
        <div class="info-card">
            <div class="info-icon">📸</div>
            <h3>Photography Workshops</h3>
            <p>Learn advanced techniques from experienced photographers. Topics include composition, lighting, editing, and more.</p>
        </div>
        <div class="info-card">
            <div class="info-icon">🚶</div>
            <h3>Photo Walks</h3>
            <p>Explore Khulna with fellow photographers. Visit interesting locations and capture beautiful moments together.</p>
        </div>
        <div class="info-card">
            <div class="info-icon">🏆</div>
            <h3>Photo Contests</h3>
            <p>Showcase your work and compete with other members. Win prizes and get featured on our gallery.</p>
        </div>
        <div class="info-card">
            <div class="info-icon">👥</div>
            <h3>Community Meetups</h3>
            <p>Network with other photography enthusiasts. Share ideas, experiences, and inspirations.</p>
        </div>
    </div>
</section>

<script>
    async function handleEventAction(button, action) {
        const form = button.closest('.event-form');
        const eventId = form.querySelector('.event_id').value;
        const csrfToken = form.querySelector('input[name="csrf_token"]').value;

        try {
            const response = await fetch('api/register_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    csrf_token: csrfToken,
                    event_id: eventId,
                    action: action
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Show success message
                alert(data.message);
                // Reload page to update registration status
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        }
    }
</script>

<?php include 'footer.php'; ?>
