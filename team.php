<?php
/**
 * KUET Photography Society - Team Page
 */
require_once 'config.php';

$page_title = 'Team';
$current_page = 'team';

// Fetch team members
$team_members = [];
if (isset($conn)) {
    $query = "SELECT tm.id, tm.position, u.name, u.profile_image, u.bio, u.email 
              FROM team_members tm
              JOIN users u ON tm.user_id = u.id
              ORDER BY tm.order_index ASC, tm.id ASC";
    $result = $conn->query($query);
    if ($result) {
        $team_members = $result->fetch_all(MYSQLI_ASSOC);
    }
}

include 'header.php';
include 'navbar.php';
?>

<div class="site-loader" id="site-loader"><span></span></div>

<header class="page-hero reveal-target" style="background-image: linear-gradient(rgba(8,17,31,0.58), rgba(8,17,31,0.88)), url('images/bts/shooting-setup.jpg');">
    <div class="page-hero-content">
        <p class="hero-badge">Our Team</p>
        <h1>Photographers and Collaborators</h1>
        <p>Our photographers include KUET members and invited outside collaborators. Click any card for full profile details.</p>
    </div>
</header>

<!-- Team Members Section -->
<section class="team-section reveal-target" style="max-width: 1200px; margin: 3rem auto; padding: 0 2rem; opacity: 1 !important; transform: translateY(0) !important;">
    <div class="section-heading">
        <p class="eyebrow">Meet Our Team</p>
        <h2>Team Members <?php echo !empty($team_members) ? '(' . count($team_members) . ')' : ''; ?></h2>
    </div>

    <?php if (empty($team_members)): ?>
        <div style="text-align: center; padding: 3rem 2rem; background: rgba(212, 175, 55, 0.05); border-radius: 8px;">
            <p style="color: #bbb; font-size: 1.1rem;">No team members yet.</p>
            <p style="color: #999; margin-top: 0.5rem;">Submit a membership application to join our team!</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <?php foreach ($team_members as $member): ?>
                <div class="team-card" style="
                    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
                    border: 2px solid rgba(212, 175, 55, 0.3);
                    border-radius: 12px;
                    padding: 2rem;
                    text-align: center;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                ">
                    <!-- Profile Image -->
                    <?php if ($member['profile_image']): ?>
                        <img src="<?php echo htmlspecialchars($member['profile_image']); ?>" 
                             alt="<?php echo htmlspecialchars($member['name']); ?}"
                             style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid #d4af37; object-fit: cover; margin-bottom: 1rem;">
                    <?php else: ?>
                        <div style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid #d4af37; background: rgba(212, 175, 55, 0.2); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 3rem;">
                            📷
                        </div>
                    <?php endif; ?>
                    
                    <!-- Name -->
                    <h3 style="color: #d4af37; margin: 0 0 0.5rem 0; font-size: 1.2rem;">
                        <?php echo htmlspecialchars($member['name']); ?>
                    </h3>
                    
                    <!-- Position -->
                    <p style="color: #d4af37; margin: 0 0 1rem 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                        <?php echo htmlspecialchars($member['position'] ?? 'Member'); ?>
                    </p>
                    
                    <!-- Bio -->
                    <?php if ($member['bio']): ?>
                        <p style="color: #bbb; margin: 0 0 1rem 0; font-size: 0.95rem; line-height: 1.5; flex-grow: 1;">
                            <?php echo htmlspecialchars($member['bio']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Email Contact -->
                    <?php if ($member['email']): ?>
                        <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" 
                           style="color: #d4af37; text-decoration: none; font-size: 0.9rem; padding: 0.6rem 1rem; border: 1px solid #d4af37; border-radius: 4px; transition: all 0.3s ease; display: inline-block; margin-top: auto;">
                            📧 Contact
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
