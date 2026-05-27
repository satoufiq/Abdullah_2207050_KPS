<?php
/**
 * KUET Photography Society - Team Page
 */
require_once 'config.php';

$page_title = 'Team';
$current_page = 'team';
$body_class = 'luxury-site team-page';

// Fetch team members
$team_members = [];
if (isset($conn)) {
    $query = "SELECT tm.id, tm.position, tm.batch, u.name, u.profile_image, u.bio, u.email 
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

<header class="page-hero reveal-target" style="background-image: linear-gradient(rgba(8,17,31,0.58), rgba(8,17,31,0.88)), url('images/bts/team-working.jpg');">
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
        <h2>Team Members</h2>
    </div>

    <?php if (empty($team_members)): ?>
        <div style="text-align: center; padding: 3rem 2rem; background: rgba(212, 175, 55, 0.05); border-radius: 8px;">
            <p style="color: #bbb; font-size: 1.1rem;">No team members yet.</p>
            <p style="color: #999; margin-top: 0.5rem;">Submit a membership application to join our team!</p>
        </div>
    <?php else: ?>
        <div class="team-grid">
            <?php foreach ($team_members as $member): ?>
                <?php
                    $initials = '';
                    foreach (preg_split('/\s+/', trim($member['name'])) as $part) {
                        if ($part !== '') {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                    }
                    if ($initials === '') {
                        $initials = 'KPS';
                    }
                ?>
                <article class="team-profile-card">
                    <div class="team-avatar">
                        <?php if (!empty($member['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($member['profile_image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($initials); ?></span>
                        <?php endif; ?>
                    </div>

                    <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p class="team-role"><?php echo htmlspecialchars($member['position'] ?? 'Member'); ?></p>

                    <?php if (!empty($member['batch'])): ?>
                        <p class="team-batch">Batch <?php echo htmlspecialchars($member['batch']); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($member['bio'])): ?>
                        <p class="team-bio"><?php echo htmlspecialchars($member['bio']); ?></p>
                    <?php endif; ?>

                    <button type="button" class="team-details-btn" 
                        data-name="<?php echo htmlspecialchars($member['name'], ENT_QUOTES); ?>" 
                        data-position="<?php echo htmlspecialchars($member['position'] ?? 'Member', ENT_QUOTES); ?>" 
                        data-batch="<?php echo htmlspecialchars($member['batch'] ?? '', ENT_QUOTES); ?>" 
                        data-email="<?php echo htmlspecialchars($member['email'] ?? '', ENT_QUOTES); ?>" 
                        data-bio="<?php echo htmlspecialchars($member['bio'] ?? '', ENT_QUOTES); ?>" 
                        data-image="<?php echo htmlspecialchars($member['profile_image'] ?? '', ENT_QUOTES); ?>">
                        View Details
                    </button>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Member Details Modal -->
<div id="member-modal" class="member-modal" aria-hidden="true">
    <div class="member-modal-panel" role="dialog" aria-modal="true" aria-labelledby="member-modal-title">
        <button type="button" class="member-modal-close" data-close aria-label="Close">×</button>
        <p class="member-modal-tag">Team Member Details</p>
        <h3 id="member-modal-title"></h3>
        <p class="member-modal-role" id="member-modal-role"></p>
        <p class="member-modal-meta" id="member-modal-batch"></p>
        <p class="member-modal-profile" id="member-modal-bio"></p>
        <a href="#" class="member-modal-email" id="member-modal-email"></a>
    </div>
</div>

<script>
document.addEventListener('click', function(e){
    // Ensure we operate on an Element node (not a Text node) and safely find the closest button
    var tgt = e.target;
    if (tgt && tgt.nodeType === 3) tgt = tgt.parentNode; // text node -> element
    var btn = (tgt && typeof tgt.closest === 'function') ? tgt.closest('.team-details-btn') : null;
    if(btn){
        var modal = document.getElementById('member-modal');
        document.getElementById('member-modal-title').textContent = btn.dataset.name || '';
        document.getElementById('member-modal-role').textContent = btn.dataset.position || '';
        document.getElementById('member-modal-batch').textContent = btn.dataset.batch ? ('Batch ' + btn.dataset.batch) : 'Batch not listed';
        document.getElementById('member-modal-bio').textContent = btn.dataset.bio || '';
        var emailEl = document.getElementById('member-modal-email');
        if(btn.dataset.email){
            emailEl.href = 'mailto:' + btn.dataset.email;
            emailEl.textContent = 'Email: ' + btn.dataset.email;
            emailEl.style.display = 'inline-block';
        } else {
            emailEl.style.display = 'none';
        }
        modal.setAttribute('aria-hidden','false');
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    if(e.target.closest('[data-close]') || e.target.closest('.member-modal-close')){
        var modal = document.getElementById('member-modal');
        modal.setAttribute('aria-hidden','true');
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }
});
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        var modal = document.getElementById('member-modal');
        if(modal && modal.classList.contains('is-open')){
            modal.setAttribute('aria-hidden','true');
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }
});
</script>

<?php include 'footer.php'; ?>
