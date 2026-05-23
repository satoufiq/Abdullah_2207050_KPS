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
        <h2>Photography Events</h2>
    </div>

    <?php if (empty($events)): ?>
        <div class="events-empty">
            <p class="empty-message">No events scheduled at the moment.</p>
            <p class="empty-subtitle">Check back soon for exciting photography workshops and photo walks!</p>
        </div>
    <?php else: ?>
        <div class="events-table-wrapper">
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Event</th>
                        <th>Details</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $event_index = 1; ?>
                    <?php foreach ($events as $event): 
                        $is_registered = in_array($event['id'], $user_registrations);
                        $is_full = $event['capacity'] && $event['registered_count'] >= $event['capacity'];
                        $event_status = $is_registered ? 'Registered' : ($is_full ? 'Full' : 'Open');
                    ?>
                        <tr class="event-row <?php echo $is_full ? 'is-full' : ''; ?> <?php echo $is_registered ? 'is-registered' : ''; ?>">
                            <td class="event-date-cell">
                                <span class="event-date-month"><?php echo date('M', strtotime($event['date'])); ?></span>
                                <span class="event-date-day"><?php echo date('d', strtotime($event['date'])); ?></span>
                                <span class="event-date-year"><?php echo date('Y', strtotime($event['date'])); ?></span>
                            </td>

                            <td class="event-title-cell">
                                <div class="event-title-wrap">
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <span class="event-order">#<?php echo str_pad($event_index, 2, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                            </td>

                            <td class="event-details-cell">
                                <span class="event-detail-item"><span class="detail-icon">🕐</span><?php echo date('g:i A', strtotime($event['date'])); ?></span>
                                <span class="event-detail-item"><span class="detail-icon">📍</span><?php echo htmlspecialchars($event['location']); ?></span>
                            </td>

                            <td class="event-capacity-cell">
                                <span class="capacity-count">
                                    <?php 
                                    if ($event['capacity']) {
                                        echo (int) $event['registered_count'] . ' / ' . (int) $event['capacity'];
                                    } else {
                                        echo (int) $event['registered_count'];
                                    }
                                    ?>
                                </span>
                                <span class="capacity-label">Registered</span>
                            </td>

                            <td class="event-status-cell">
                                <?php if ($is_registered): ?>
                                    <span class="event-status registered">Registered</span>
                                <?php elseif ($is_full): ?>
                                    <span class="event-status full">Full</span>
                                <?php else: ?>
                                    <span class="event-status open">Open</span>
                                <?php endif; ?>
                            </td>

                            <td class="event-action-cell">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form class="event-form" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" class="event_id" value="<?php echo $event['id']; ?>">

                                        <?php if ($is_registered): ?>
                                            <button type="button" class="event-btn unregister-btn" onclick="handleEventAction(this, 'unregister')">
                                                Cancel
                                            </button>
                                        <?php elseif ($is_full): ?>
                                            <button type="button" class="event-btn full-btn" disabled>Full</button>
                                        <?php else: ?>
                                            <button type="button" class="event-btn register-btn" onclick="handleEventAction(this, 'register')">
                                                Register
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="event-btn login-btn">Login</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php $event_index++; endforeach; ?>
                </tbody>
            </table>
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
