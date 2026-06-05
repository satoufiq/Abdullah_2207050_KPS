<?php
/**
 * KUET Photography Society - Admin Control Panel
 */
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$admin_action = $_GET['action'] ?? 'dashboard';
$success_message = '';
$error_message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security verification failed';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'approve_photo') {
            $photo_id = (int)$_POST['photo_id'];
            $stmt = $conn->prepare("UPDATE photos SET is_approved = 1 WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $photo_id);
                if ($stmt->execute()) {
                    $success_message = 'Photo approved successfully';
                }
                $stmt->close();
            }
        } elseif ($action === 'set_featured') {
            $photo_id = (int)$_POST['photo_id'];
            // Remove featured from all, then set this one
            $conn->query("UPDATE photos SET is_featured = 0");
            $stmt = $conn->prepare("UPDATE photos SET is_featured = 1 WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $photo_id);
                if ($stmt->execute()) {
                    $success_message = 'Featured image updated';
                }
                $stmt->close();
            }
        } elseif ($action === 'set_photo_of_week') {
            $photo_id = (int)$_POST['photo_id'];
            $stmt = $conn->prepare("UPDATE photos SET is_photo_of_week = 1 WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $photo_id);
                if ($stmt->execute()) {
                    $success_message = 'Photo of the week set successfully';
                }
                $stmt->close();
            }
        } elseif ($action === 'reject_submission') {
            $photo_id = (int)$_POST['photo_id'];
            $stmt = $conn->prepare("DELETE FROM photos WHERE id = ? AND is_approved = 0");
            if ($stmt) {
                $stmt->bind_param('i', $photo_id);
                if ($stmt->execute()) {
                    $success_message = 'Submission rejected';
                }
                $stmt->close();
            }
        } elseif ($action === 'approve_membership') {
            $app_id = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;
            $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
            $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
            $batch = isset($_POST['batch']) ? sanitize_input($_POST['batch']) : '';
            $position = isset($_POST['position']) ? sanitize_input($_POST['position']) : 'Member';
            $admin_id = $_SESSION['user_id'] ?? 0;
            
            if (!$app_id || !$name || !$email || !$admin_id) {
                $error_message = 'Invalid submission data';
            } else {
                try {
                    if ($batch === '') {
                        $batch = 'Unknown';
                    }
                    // Check if user exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    if (!$stmt) {
                        throw new Exception("Database error: " . $conn->error);
                    }
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user_id = null;
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $user_id = $row['id'];
                    } else {
                        // Create new user account
                        $temp_password = password_hash('temp_password_123', PASSWORD_DEFAULT);
                        $stmt_new = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'member')");
                        if (!$stmt_new) {
                            throw new Exception("Database error: " . $conn->error);
                        }
                        $stmt_new->bind_param('sss', $name, $email, $temp_password);
                        if (!$stmt_new->execute()) {
                            throw new Exception("Failed to create user: " . $stmt_new->error);
                        }
                        $user_id = $stmt_new->insert_id;
                        $stmt_new->close();
                    }
                    
                    $stmt->close();
                    
                    // If user was created or found, check if already in team_members and add/update it
                    if ($user_id) {
                        $stmt_check = $conn->prepare("SELECT id FROM team_members WHERE user_id = ?");
                        if ($stmt_check) {
                            $stmt_check->bind_param('i', $user_id);
                            $stmt_check->execute();
                            $check_result = $stmt_check->get_result();
                            
                            if ($check_result->num_rows === 0) {
                                // Not already in team_members, so add them with chosen designation and batch
                                $stmt_team = $conn->prepare("INSERT INTO team_members (user_id, position, batch) VALUES (?, ?, ?)");
                                if (!$stmt_team) {
                                    throw new Exception("Database error: " . $conn->error);
                                }
                                $stmt_team->bind_param('iss', $user_id, $position, $batch);
                                if (!$stmt_team->execute()) {
                                    throw new Exception("Failed to add to team: " . $stmt_team->error);
                                }
                                $stmt_team->close();
                            } else {
                                $stmt_team = $conn->prepare("UPDATE team_members SET position = ?, batch = ? WHERE user_id = ?");
                                if ($stmt_team) {
                                    $stmt_team->bind_param('ssi', $position, $batch, $user_id);
                                    if (!$stmt_team->execute()) {
                                        throw new Exception("Failed to update team member: " . $stmt_team->error);
                                    }
                                    $stmt_team->close();
                                }
                            }
                            $stmt_check->close();
                        }
                    }
                    
                    // Ensure admin exists in users table; if not, write NULL to approved_by to avoid FK error
                    $approved_by_value = null;
                    $check_admin = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
                    if ($check_admin) {
                        $check_admin->bind_param('i', $admin_id);
                        $check_admin->execute();
                        $res_admin = $check_admin->get_result();
                        if ($res_admin && $res_admin->num_rows > 0) {
                            $approved_by_value = $admin_id;
                        }
                        $check_admin->close();
                    }

                    if ($approved_by_value !== null) {
                        $stmt_update = $conn->prepare("UPDATE membership_applications SET status = 'approved', approved_by = ?, reviewed_at = NOW() WHERE id = ?");
                        if (!$stmt_update) {
                            throw new Exception("Database error: " . $conn->error);
                        }
                        $stmt_update->bind_param('ii', $approved_by_value, $app_id);
                        if (!$stmt_update->execute()) {
                            throw new Exception("Failed to update application: " . $stmt_update->error);
                        }
                        $stmt_update->close();
                    } else {
                        $stmt_update = $conn->prepare("UPDATE membership_applications SET status = 'approved', approved_by = NULL, reviewed_at = NOW() WHERE id = ?");
                        if (!$stmt_update) {
                            throw new Exception("Database error: " . $conn->error);
                        }
                        $stmt_update->bind_param('i', $app_id);
                        if (!$stmt_update->execute()) {
                            throw new Exception("Failed to update application: " . $stmt_update->error);
                        }
                        $stmt_update->close();
                    }
                    $success_message = 'Membership application approved! New member added to team.';
                    
                } catch (Exception $e) {
                    $error_message = 'Error: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'reject_membership') {
            $app_id = (int)$_POST['app_id'];
            $admin_id = $_SESSION['user_id'] ?? 0;
            // Ensure admin exists
            $approved_by_value = null;
            $check_admin = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
            if ($check_admin) {
                $check_admin->bind_param('i', $admin_id);
                $check_admin->execute();
                $res_admin = $check_admin->get_result();
                if ($res_admin && $res_admin->num_rows > 0) {
                    $approved_by_value = $admin_id;
                }
                $check_admin->close();
            }

            if ($approved_by_value !== null) {
                $stmt = $conn->prepare("UPDATE membership_applications SET status = 'rejected', approved_by = ?, reviewed_at = NOW() WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('ii', $approved_by_value, $app_id);
                    if ($stmt->execute()) {
                        $success_message = 'Membership application rejected';
                    }
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE membership_applications SET status = 'rejected', approved_by = NULL, reviewed_at = NOW() WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('i', $app_id);
                    if ($stmt->execute()) {
                        $success_message = 'Membership application rejected';
                    }
                    $stmt->close();
                }
            }
        } elseif ($action === 'update_member') {
            $member_id = (int)($_POST['member_id'] ?? 0);
            $position = sanitize_input($_POST['position'] ?? 'Member');
            $batch = sanitize_input($_POST['batch'] ?? '');
            $order_index = (int)($_POST['order_index'] ?? 0);

            if (!$member_id) {
                $error_message = 'Invalid member selected';
            } else {
                $stmt = $conn->prepare("UPDATE team_members SET position = ?, batch = ?, order_index = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('ssii', $position, $batch, $order_index, $member_id);
                    if ($stmt->execute()) {
                        $success_message = 'Member updated successfully';
                    } else {
                        $error_message = 'Failed to update member: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        } elseif ($action === 'remove_member') {
            $member_id = (int)($_POST['member_id'] ?? 0);
            if (!$member_id) {
                $error_message = 'Invalid member selected';
            } else {
                $stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('i', $member_id);
                    if ($stmt->execute()) {
                        $success_message = 'Member removed successfully';
                    } else {
                        $error_message = 'Failed to remove member: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        } elseif ($action === 'create_event') {
            $title = sanitize_input($_POST['title'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $event_time = $_POST['event_time'] ?? '';
            $location = sanitize_input($_POST['location'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            $created_by = $_SESSION['user_id'] ?? 0;
            
            // Combine date and time, or set to default future date
            if (!empty($event_date)) {
                if (!empty($event_time)) {
                    $date = $event_date . ' ' . $event_time;
                } else {
                    $date = $event_date . ' 00:00:00';
                }
            } else {
                // Default to 7 days from now at noon if no date provided
                $date = date('Y-m-d H:i:s', strtotime('+7 days noon'));
            }
            
            if (!$title) {
                $error_message = 'Event title is required';
            } else {
                // First, check if session user exists
                $user_id_to_use = 0;
                if ($created_by > 0) {
                    $check = $conn->prepare("SELECT id FROM users WHERE id = ?");
                    if ($check) {
                        $check->bind_param('i', $created_by);
                        $check->execute();
                        $result = $check->get_result();
                        if ($result->num_rows > 0) {
                            $user_id_to_use = $created_by;
                        }
                        $check->close();
                    }
                }
                
                // If session user doesn't exist, find any admin user
                if ($user_id_to_use === 0) {
                    $admin_check = $conn->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                    if ($admin_check) {
                        $admin_check->execute();
                        $admin_result = $admin_check->get_result();
                        if ($admin_result->num_rows > 0) {
                            $row = $admin_result->fetch_assoc();
                            $user_id_to_use = $row['id'];
                        }
                        $admin_check->close();
                    }
                }
                
                // If still no user, find ANY user
                if ($user_id_to_use === 0) {
                    $any_user = $conn->prepare("SELECT id FROM users LIMIT 1");
                    if ($any_user) {
                        $any_user->execute();
                        $user_result = $any_user->get_result();
                        if ($user_result->num_rows > 0) {
                            $row = $user_result->fetch_assoc();
                            $user_id_to_use = $row['id'];
                        }
                        $any_user->close();
                    }
                }
                
                if ($user_id_to_use === 0) {
                    $error_message = 'No users found in the system. Please create an account first.';
                } else {
                    // Insert the event
                    $stmt = $conn->prepare("INSERT INTO events (title, description, date, location, capacity, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($stmt) {
                        $stmt->bind_param('ssssii', $title, $description, $date, $location, $capacity, $user_id_to_use);
                        if ($stmt->execute()) {
                            $success_message = 'Event created successfully';
                        } else {
                            $error_message = 'Failed to create event: ' . $stmt->error;
                        }
                        $stmt->close();
                    } else {
                        $error_message = 'Database error: ' . $conn->error;
                    }
                }
            }
        } elseif ($action === 'delete_event') {
            $event_id = (int)$_POST['event_id'];
            $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $event_id);
                if ($stmt->execute()) {
                    $success_message = 'Event deleted successfully';
                } else {
                    $error_message = 'Failed to delete event: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$page_title = 'Admin Control Panel';
include 'header.php';
include 'navbar.php';

// Fetch data based on active tab
$pending_submissions = [];
$approved_photos = [];
$all_photos = [];
$pending_memberships = [];
$all_members = [];
$tables_exist = true;

// Simple table existence check - try to query each table
@$check = $conn->query("SELECT 1 FROM membership_applications LIMIT 1");
if (!$check) {
    $tables_exist = false;
    $error_message = '<strong>⚠️ Database Not Initialized</strong><br>
    The required database tables are missing. <a href="setup.php" style="color: #d4af37; text-decoration: underline; font-weight: bold;">🔧 Click here to run the database setup</a><br>
    <small style="color: #bbb; margin-top: 0.5rem; display: block;">This will create all necessary tables for the application to function.</small>';
}

if ($tables_exist) {
    if ($admin_action === 'submissions' || $admin_action === 'dashboard') {
        $query = "SELECT p.id, p.title, p.image_url, u.name, p.created_at FROM photos p 
                  JOIN users u ON p.photographer_id = u.id 
                  WHERE p.is_approved = 0 ORDER BY p.created_at DESC";
        $result = $conn->query($query);
        if ($result) {
            $pending_submissions = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Submissions query error: " . $conn->error);
            $pending_submissions = [];
        }
    }

    if ($admin_action === 'membership' || $admin_action === 'dashboard') {
        $query = "SELECT id, name, email, phone, batch, experience, interests, message, applied_at 
                  FROM membership_applications 
                  WHERE status = 'pending' ORDER BY applied_at DESC";
        $result = $conn->query($query);
        if ($result) {
            $pending_memberships = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            // Log error but don't break the page
            error_log("Membership query error: " . $conn->error);
            $pending_memberships = [];
        }
    }

    if ($admin_action === 'members' || $admin_action === 'dashboard') {
        $query = "SELECT tm.id, tm.position, tm.batch, tm.order_index, u.name, u.profile_image, u.bio, u.email
                  FROM team_members tm
                  JOIN users u ON tm.user_id = u.id
                  ORDER BY tm.order_index ASC, tm.id ASC";
        $result = $conn->query($query);
        if ($result) {
            $all_members = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log('Members query error: ' . $conn->error);
            $all_members = [];
        }
    }

    if ($admin_action === 'featured' || $admin_action === 'dashboard') {
        $query = "SELECT p.id, p.title, p.image_url, u.name, p.is_featured, p.is_photo_of_week 
                  FROM photos p 
                  JOIN users u ON p.photographer_id = u.id 
                  WHERE p.is_approved = 1 ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 20";
        $result = $conn->query($query);
        if ($result) {
            $approved_photos = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            error_log("Featured photos query error: " . $conn->error);
            $approved_photos = [];
        }
    }

    if ($admin_action === 'events') {
        $query = "SELECT e.id, e.title, e.description, e.date, e.location, e.capacity, e.registered_count, COALESCE(u.name, 'System') as created_by_name
                  FROM events e 
                  LEFT JOIN users u ON e.created_by = u.id 
                  ORDER BY e.date DESC";
        $result = $conn->query($query);
        $all_events = [];
        if ($result) {
            $all_events = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Admin events fetched: " . count($all_events));
        } else {
            error_log("Admin events query error: " . $conn->error);
        }
    }
}
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-header">
            <h2>Admin Panel</h2>
            <p class="admin-welcome">Welcome, Admin</p>
        </div>

        <nav class="admin-menu">
            <a href="?action=dashboard" class="admin-menu-item <?php echo $admin_action === 'dashboard' ? 'active' : ''; ?>">
                <span class="menu-icon">📊</span> Dashboard
            </a>
            <a href="?action=submissions" class="admin-menu-item <?php echo $admin_action === 'submissions' ? 'active' : ''; ?>">
                <span class="menu-icon">📋</span> Pending Submissions
                <?php if (!empty($pending_submissions)): ?>
                    <span class="badge"><?php echo count($pending_submissions); ?></span>
                <?php endif; ?>
            </a>
            <a href="?action=membership" class="admin-menu-item <?php echo $admin_action === 'membership' ? 'active' : ''; ?>">
                <span class="menu-icon">🎟️</span> Membership Applications
                <?php if (!empty($pending_memberships)): ?>
                    <span class="badge"><?php echo count($pending_memberships); ?></span>
                <?php endif; ?>
            </a>
            <a href="?action=featured" class="admin-menu-item <?php echo $admin_action === 'featured' ? 'active' : ''; ?>">
                <span class="menu-icon">⭐</span> Featured Images
            </a>
            <a href="?action=events" class="admin-menu-item <?php echo $admin_action === 'events' ? 'active' : ''; ?>">
                <span class="menu-icon">🎯</span> Events Management
            </a>
            <a href="?action=members" class="admin-menu-item <?php echo $admin_action === 'members' ? 'active' : ''; ?>">
                <span class="menu-icon">👔</span> Team Members
                <?php if (!empty($all_members)): ?>
                    <span class="badge"><?php echo count($all_members); ?></span>
                <?php endif; ?>
            </a>
            <!-- Users management removed; use Team Members for member management -->
            <hr class="menu-divider">
            <a href="api/logout.php" class="admin-menu-item logout">
                <span class="menu-icon">🚪</span> Logout
            </a>
        </nav>
    </div>

    <div class="admin-content">
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <span class="alert-icon">✕</span>
                <span><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Dashboard Overview -->
        <?php if ($admin_action === 'dashboard'): ?>
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-icon">📸</div>
                    <div class="stat-info">
                        <h3>Total Photos</h3>
                        <p class="stat-number">
                            <?php 
                            $result = $conn->query("SELECT COUNT(*) as count FROM photos");
                            echo $result->fetch_assoc()['count'];
                            ?>
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3>Pending Approval</h3>
                        <p class="stat-number" style="color: #ff6b35;"><?php echo count($pending_submissions); ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Total Members</h3>
                        <p class="stat-number">
                            <?php 
                            $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'member'");
                            echo $result->fetch_assoc()['count'];
                            ?>
                        </p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-info">
                        <h3>Active Events</h3>
                        <p class="stat-number">
                            <?php 
                            $result = $conn->query("SELECT COUNT(*) as count FROM events WHERE date >= NOW()");
                            echo $result->fetch_assoc()['count'];
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="admin-section">
                <h2>Recent Pending Submissions</h2>
                <?php if (empty($pending_submissions)): ?>
                    <p class="empty-state">No pending submissions</p>
                <?php else: ?>
                    <div class="submissions-grid">
                        <?php foreach ($pending_submissions as $photo): ?>
                            <div class="submission-card">
                                <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                <div class="submission-info">
                                    <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                                    <p class="by">by <?php echo htmlspecialchars($photo['name']); ?></p>
                                    <p class="date"><?php echo date('M d, Y', strtotime($photo['created_at'])); ?></p>
                                </div>
                                <div class="submission-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="approve_photo">
                                        <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                        <button type="submit" class="action-btn approve">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="reject_submission">
                                        <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                        <button type="submit" class="action-btn reject">Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Pending Submissions Tab -->
        <?php if ($admin_action === 'submissions'): ?>
            <div class="admin-section">
                <h2>Pending Photo Submissions</h2>
                <?php if (empty($pending_submissions)): ?>
                    <div class="empty-state">
                        <p>✓ All submissions have been reviewed</p>
                    </div>
                <?php else: ?>
                    <div class="submissions-grid">
                        <?php foreach ($pending_submissions as $photo): ?>
                            <div class="submission-card">
                                <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                <div class="submission-info">
                                    <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                                    <p class="by">by <?php echo htmlspecialchars($photo['name']); ?></p>
                                    <p class="date"><?php echo date('M d, Y', strtotime($photo['created_at'])); ?></p>
                                </div>
                                <div class="submission-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="approve_photo">
                                        <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                        <button type="submit" class="action-btn approve">✓ Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="reject_submission">
                                        <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                        <button type="submit" class="action-btn reject">✕ Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Featured Images Tab -->
        <?php if ($admin_action === 'featured'): ?>
            <div class="admin-section">
                <h2>Manage Featured Images</h2>
                <p class="section-subtitle">Select featured images and photo of the week</p>
                
                <?php if (empty($approved_photos)): ?>
                    <p class="empty-state">No approved photos yet</p>
                <?php else: ?>
                    <div class="featured-grid">
                        <?php foreach ($approved_photos as $photo): ?>
                            <div class="featured-card <?php echo $photo['is_featured'] ? 'is-featured' : ''; ?>">
                                <img src="<?php echo htmlspecialchars($photo['image_url']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                <div class="featured-overlay">
                                    <div class="featured-info">
                                        <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                                        <p class="by">by <?php echo htmlspecialchars($photo['name']); ?></p>
                                    </div>
                                    <div class="featured-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="set_featured">
                                            <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                            <button type="submit" class="action-btn featured-btn <?php echo $photo['is_featured'] ? 'active' : ''; ?>">
                                                ⭐ Set as Featured
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="set_photo_of_week">
                                            <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                            <button type="submit" class="action-btn week-btn <?php echo $photo['is_photo_of_week'] ? 'active' : ''; ?>">
                                                🏆 Photo of the Week
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Membership Applications -->
        <?php if ($admin_action === 'membership'): ?>
            <div class="admin-section">
                <h2>Membership Applications</h2>
                <p class="section-subtitle">Review and approve new member applications</p>
                
                <?php if (empty($pending_memberships)): ?>
                    <p class="empty-state">No pending membership applications</p>
                <?php else: ?>
                    <div class="applications-list">
                        <?php foreach ($pending_memberships as $app): ?>
                            <div class="application-card">
                                <div class="app-header">
                                    <div class="app-info">
                                        <h4><?php echo htmlspecialchars($app['name']); ?></h4>
                                        <p class="app-email">📧 <?php echo htmlspecialchars($app['email']); ?></p>
                                        <p class="app-phone">📱 <?php echo htmlspecialchars($app['phone'] ?? 'N/A'); ?></p>
                                        <p class="app-batch">🎓 Batch: <strong><?php echo htmlspecialchars($app['batch'] ?? 'N/A'); ?></strong></p>
                                        <p class="app-experience">📷 Experience: <strong><?php echo ucfirst($app['experience']); ?></strong></p>
                                    </div>
                                    <div class="app-date">
                                        <small><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></small>
                                    </div>
                                </div>
                                <div class="app-interests">
                                    <?php 
                                    $interests = json_decode($app['interests'], true);
                                    if (!empty($interests)): ?>
                                        <p><strong>Interests:</strong> <?php echo implode(', ', $interests); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="app-message">
                                    <p><strong>Message:</strong></p>
                                    <p><?php echo htmlspecialchars($app['message']); ?></p>
                                </div>
                                <div class="app-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="approve_membership">
                                        <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                        <input type="hidden" name="name" value="<?php echo htmlspecialchars($app['name']); ?>">
                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($app['email']); ?>">
                                        <input type="hidden" name="batch" value="<?php echo htmlspecialchars($app['batch'] ?? ''); ?>">
                                        <div style="display: grid; gap: 0.6rem; margin-bottom: 0.8rem;">
                                            <label style="font-size: 0.85rem; color: #d4af37; font-weight: 600;">Designation</label>
                                            <select name="position" style="width: 100%; padding: 0.7rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit;">
                                                <option value="Member">Member</option>
                                                <option value="President">President</option>
                                                <option value="Vice President">Vice President</option>
                                                <option value="General Secretary">General Secretary</option>
                                                <option value="Event Manager">Event Manager</option>
                                                <option value="Recruiter">Recruiter</option>
                                                <option value="Treasurer">Treasurer</option>
                                                <option value="Photographer">Photographer</option>
                                                <option value="Designer">Designer</option>
                                                <option value="Coordinator">Coordinator</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="action-btn approve">✓ Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="reject_membership">
                                        <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                        <button type="submit" class="action-btn reject" onclick="return confirm('Are you sure?')">✕ Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Team Members Tab -->
        <?php if ($admin_action === 'members'): ?>
            <div class="admin-section">
                <h2>Team Members</h2>
                <p class="section-subtitle">Update a member's designation or batch, or remove them from the team list.</p>

                <?php if (empty($all_members)): ?>
                    <p class="empty-state">No team members yet.</p>
                <?php else: ?>
                    <div class="applications-list">
                        <?php foreach ($all_members as $member): ?>
                            <div class="application-card">
                                <div class="app-header">
                                    <div class="app-info">
                                        <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                                        <p class="app-email">📧 <?php echo htmlspecialchars($member['email']); ?></p>
                                        <p class="app-experience">🎓 Batch: <strong><?php echo htmlspecialchars($member['batch'] ?? 'N/A'); ?></strong></p>
                                        <p class="app-experience">🏷️ Position: <strong><?php echo htmlspecialchars($member['position'] ?? 'Member'); ?></strong></p>
                                        <?php if (!empty($member['bio'])): ?>
                                            <p class="app-message"><strong>Bio:</strong> <?php echo htmlspecialchars($member['bio']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="app-date">
                                        <small>Order: <?php echo (int)$member['order_index']; ?></small>
                                    </div>
                                </div>

                                <div class="app-actions" style="display: grid; gap: 0.9rem;">
                                    <form method="POST" style="display: grid; gap: 0.75rem;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="update_member">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <label style="font-size: 0.85rem; color: #d4af37; font-weight: 600;">Designation</label>
                                        <select name="position" style="width: 100%; padding: 0.7rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit;">
                                            <?php
                                                $member_positions = ['Member','President','Vice President','General Secretary','Event Manager','Recruiter','Treasurer','Photographer','Designer','Coordinator'];
                                                foreach ($member_positions as $member_position):
                                            ?>
                                                <option value="<?php echo htmlspecialchars($member_position); ?>" <?php echo (($member['position'] ?? 'Member') === $member_position) ? 'selected' : ''; ?>><?php echo htmlspecialchars($member_position); ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <label style="font-size: 0.85rem; color: #d4af37; font-weight: 600;">Batch</label>
                                        <input type="text" name="batch" value="<?php echo htmlspecialchars($member['batch'] ?? ''); ?>" placeholder="Batch 2022" style="width: 100%; padding: 0.7rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit;">

                                        <label style="font-size: 0.85rem; color: #d4af37; font-weight: 600;">Order</label>
                                        <input type="number" name="order_index" value="<?php echo (int)$member['order_index']; ?>" min="0" style="width: 100%; padding: 0.7rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit;">

                                        <button type="submit" class="action-btn approve">Save Changes</button>
                                    </form>

                                    <form method="POST" onsubmit="return confirm('Remove this member from the team?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="remove_member">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <button type="submit" class="action-btn reject">Remove Member</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Events Management -->
        <?php if ($admin_action === 'events'): ?>
            <div class="admin-section">
                <h2>📅 Events Management</h2>

                <!-- Create Event Form -->
                <div style="background: rgba(212, 175, 55, 0.05); border: 1px solid rgba(212, 175, 55, 0.2); padding: 2rem; border-radius: 8px; margin-bottom: 2rem;">
                    <h3 style="color: #d4af37; margin-bottom: 1.5rem;">+ Create New Event</h3>
                    <form method="POST" style="display: grid; gap: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                        <input type="hidden" name="action" value="create_event">
                        
                        <!-- Title -->
                        <div>
                            <label style="display: block; color: #d4af37; font-size: 0.9rem; margin-bottom: 0.3rem; font-weight: 600;">Event Title <span style="color: #ff6b6b;">*</span></label>
                            <input type="text" name="title" placeholder="e.g., Monthly Photography Walk" required style="width: 100%; padding: 0.8rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit;">
                        </div>
                        
                        <!-- Date & Time -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; color: #d4af37; font-size: 0.9rem; margin-bottom: 0.3rem; font-weight: 600;">📅 Date (Optional)</label>
                                <input type="date" name="event_date" style="width: 100%; padding: 0.8rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit; cursor: pointer;">
                            </div>
                            <div>
                                <label style="display: block; color: #d4af37; font-size: 0.9rem; margin-bottom: 0.3rem; font-weight: 600;">🕒 Time (Optional)</label>
                                <input type="time" name="event_time" style="width: 100%; padding: 0.8rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit; cursor: pointer;">
                            </div>
                        </div>
                        
                        <!-- Location & Capacity -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; color: #d4af37; font-size: 0.9rem; margin-bottom: 0.3rem; font-weight: 600;">📍 Location (Optional)</label>
                                <input type="text" name="location" placeholder="e.g., Downtown Park" style="width: 100%; padding: 0.8rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit;">
                            </div>
                            <div>
                                <label style="display: block; color: #d4af37; font-size: 0.9rem; margin-bottom: 0.3rem; font-weight: 600;">👥 Capacity (Optional)</label>
                                <input type="number" name="capacity" placeholder="Max participants" min="1" style="width: 100%; padding: 0.8rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; font-family: inherit;">
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label style="display: block; color: #d4af37; font-size: 0.9rem; margin-bottom: 0.3rem; font-weight: 600;">📝 Description (Optional)</label>
                            <textarea name="description" placeholder="Event details, agenda, requirements, etc." style="width: 100%; padding: 0.8rem; background: #1a1a2e; border: 1px solid #d4af37; color: #fff; border-radius: 4px; min-height: 100px; font-family: inherit; resize: vertical;"></textarea>
                        </div>
                        
                        <button type="submit" style="padding: 0.8rem 1.5rem; background: #d4af37; color: #0a0e27; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-size: 1rem;">
                            ✓ Create Event
                        </button>
                    </form>
                </div>

                <!-- Events List -->
                <?php if (!empty($all_events)): ?>
                    <div style="display: grid; gap: 1rem;">
                        <h3 style="color: #d4af37; margin-top: 1rem;">Upcoming & Past Events</h3>
                        <?php foreach ($all_events as $event): ?>
                            <div style="background: rgba(212, 175, 55, 0.05); border: 1px solid rgba(212, 175, 55, 0.2); padding: 1.5rem; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                                    <div style="flex: 1;">
                                        <h4 style="color: #d4af37; margin: 0 0 0.5rem 0; font-size: 1.2rem;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <p style="margin: 0.3rem 0; color: #bbb;">
                                            📅 <?php echo date('F j, Y \a\t g:i A', strtotime($event['date'])); ?>
                                        </p>
                                        <?php if ($event['location']): ?>
                                            <p style="margin: 0.3rem 0; color: #bbb;">
                                                📍 <?php echo htmlspecialchars($event['location']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($event['description']): ?>
                                            <p style="margin: 0.5rem 0 0 0; color: #999; font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($event['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p style="margin: 0.5rem 0 0 0; color: #999; font-size: 0.9rem;">
                                            Created by: <strong><?php echo htmlspecialchars($event['created_by_name']); ?></strong>
                                            <?php if ($event['capacity'] > 0): ?>
                                                | Capacity: <strong><?php echo $event['capacity']; ?></strong>
                                                | Registered: <strong><?php echo $event['registered_count']; ?></strong>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" onclick="return confirm('Delete this event?');" style="padding: 0.6rem 1rem; background: #ff6b6b; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: all 0.3s ease;">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-state">No events yet. Create one to get started!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Users management removed per admin preference -->
    </div>
</div>

<?php include 'footer.php'; ?>
