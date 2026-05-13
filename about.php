<?php
/**
 * KUET Photography Society - About Page
 */
require_once 'config.php';

$page_title = 'About';
$current_page = 'about';

// Calculate years since foundation
$years_of_excellence = max(1, date('Y') - 2018);

// Default fallback stats
$photos_count = '500+';
$exhibitions_count = '45';
$workshops_count = '28';

// Try to fetch real numbers from the database when available
if (isset($conn) && $conn instanceof mysqli) {
    // Photos count
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM photos");
    if ($res) {
        $row = $res->fetch_assoc();
        $photos_count = (int)$row['cnt'];
        if ($photos_count > 999) {
            $photos_count = $photos_count . '+';
        }
    }

    // Exhibitions (use events as a proxy)
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM events");
    if ($res) {
        $row = $res->fetch_assoc();
        $exhibitions_count = (int)$row['cnt'];
    }

    // Workshops (search for 'workshop' keyword in title/description)
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM events WHERE LOWER(title) LIKE '%workshop%' OR LOWER(description) LIKE '%workshop%'");
    if ($res) {
        $row = $res->fetch_assoc();
        $workshops_count = (int)$row['cnt'];
    }
}

include 'header.php';
include 'navbar.php';
?>

    <div class="site-loader" id="site-loader"><span></span></div>

    <header class="page-hero reveal-target" style="background-image: linear-gradient(rgba(8,17,31,0.52), rgba(8,17,31,0.9)), url('images/collections/portrait/photo_6199518806691395404_w.jpg');">
        <div class="page-hero-content">
            <p class="hero-badge">Our Story</p>
            <h1>Crafting Visual Narratives Since 2018</h1>
            <p>A premier student photography society dedicated to creative excellence, ethical storytelling, and mentorship.</p>
        </div>
    </header>

    <section class="featured-intro">
        <div class="intro-grid">
            <div class="intro-card reveal-target">
                <h2>Who We Are</h2>
                <p>KUET Photography Society is a vibrant community of visual storytellers committed to pushing creative boundaries and exploring photography's power to document, inspire, and connect.</p>
                <p>We believe photography is more than technical skill—it's about perspective, empathy, and the courage to see the world differently.</p>
                <a href="gallery.php" class="btn-link">Explore Our Work →</a>
            </div>
            <div class="intro-stats reveal-target">
                <div class="stat-block">
                    <span class="stat-value" data-count-to="<?php echo $years_of_excellence; ?>" data-duration="2000"><?php echo $years_of_excellence; ?></span>
                    <span class="stat-name">Years of Excellence</span>
                </div>
                <div class="stat-block">
                    <span class="stat-value" data-count-to="<?php echo is_numeric($photos_count) ? $photos_count : 500; ?>" data-duration="2000" data-suffix="+"><?php echo $photos_count; ?></span>
                    <span class="stat-name">Photos Archived</span>
                </div>
                <div class="stat-block">
                    <span class="stat-value" data-count-to="<?php echo is_numeric($exhibitions_count) ? $exhibitions_count : 45; ?>" data-duration="2000"><?php echo $exhibitions_count; ?></span>
                    <span class="stat-name">Exhibitions Held</span>
                </div>
                <div class="stat-block">
                    <span class="stat-value" data-count-to="<?php echo is_numeric($workshops_count) ? $workshops_count : 28; ?>" data-duration="2000"><?php echo $workshops_count; ?></span>
                    <span class="stat-name">Workshops Conducted</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mission-vision-section">
        <div class="section-heading">
            <p class="eyebrow">Foundation</p>
            <h2>Our Mission & Vision</h2>
        </div>
        <div class="mv-grid reveal-target">
            <article class="mv-card">
                <h3>Mission</h3>
                <p>To foster creativity and technical excellence through practical learning, critical feedback, and ethical visual storytelling that connects with audiences and inspires change.</p>
                <ul class="mv-points">
                    <li>Mentor emerging photographers through structured programs</li>
                    <li>Showcase diverse perspectives and untold stories</li>
                    <li>Create ethical standards for visual documentation</li>
                    <li>Build community through collaborative projects</li>
                </ul>
            </article>
            <article class="mv-card">
                <h3>Vision</h3>
                <p>To become one of Bangladesh's most influential and respected student photography societies, recognized for meaningful work and the next generation of visual artists.</p>
                <ul class="mv-points">
                    <li>Lead conversations through powerful photography</li>
                    <li>Establish mentorship as our core value</li>
                    <li>Expand globally while staying rooted locally</li>
                    <li>Inspire future photographers with our legacy</li>
                </ul>
            </article>
        </div>
    </section>

    <section class="journey-section">
        <div class="section-heading">
            <p class="eyebrow">Milestones</p>
            <h2>Our Creative Evolution</h2>
        </div>
        <div class="timeline-modern">
            <article class="timeline-node reveal-target">
                <div class="timeline-marker">
                    <span class="timeline-dot"></span>
                    <span class="timeline-year">2018</span>
                </div>
                <div class="timeline-content">
                    <h3>Foundation Year</h3>
                    <p>Small group of passionate photographers gathered to document campus moments and learn fundamentals together.</p>
                </div>
            </article>
            <article class="timeline-node reveal-target">
                <div class="timeline-marker">
                    <span class="timeline-dot"></span>
                    <span class="timeline-year">2019</span>
                </div>
                <div class="timeline-content">
                    <h3>First Workshop</h3>
                    <p>Organized outdoor sessions exploring portrait lighting, composition, and real-world photography challenges.</p>
                </div>
            </article>
            <article class="timeline-node reveal-target">
                <div class="timeline-marker">
                    <span class="timeline-dot"></span>
                    <span class="timeline-year">2020</span>
                </div>
                <div class="timeline-content">
                    <h3>Public Exhibition</h3>
                    <p>Hosted inter-university showcase featuring portrait series, architecture studies, and documentary work.</p>
                </div>
            </article>
            <article class="timeline-node reveal-target">
                <div class="timeline-marker">
                    <span class="timeline-dot"></span>
                    <span class="timeline-year">2022</span>
                </div>
                <div class="timeline-content">
                    <h3>Mentorship Program</h3>
                    <p>Launched structured mentor-mentee model with weekly critiques and personalized guidance for growth.</p>
                </div>
            </article>
            <article class="timeline-node reveal-target">
                <div class="timeline-marker">
                    <span class="timeline-dot"></span>
                    <span class="timeline-year">2024</span>
                </div>
                <div class="timeline-content">
                    <h3>National Recognition</h3>
                    <p>Members earned prestigious awards in storytelling, street photography, and event documentation.</p>
                </div>
            </article>
            <article class="timeline-node reveal-target">
                <div class="timeline-marker">
                    <span class="timeline-dot"></span>
                    <span class="timeline-year">2026</span>
                </div>
                <div class="timeline-content">
                    <h3>Digital Platform</h3>
                    <p>Launched premium website showcasing portfolio, stories, events, member profiles, and submissions.</p>
                </div>
            </article>
        </div>
    </section>

    <section class="core-values-section">
        <div class="section-heading">
            <p class="eyebrow">What Drives Us</p>
            <h2>Our Core Values</h2>
        </div>
        <div class="values-grid">
            <article class="value-card reveal-target">
                <div class="value-icon">📸</div>
                <h3>Technical Excellence</h3>
                <p>We pursue mastery through continuous learning, experimentation, and respect for the craft.</p>
            </article>
            <article class="value-card reveal-target">
                <div class="value-icon">🤝</div>
                <h3>Community First</h3>
                <p>Our strength lies in mentorship, collaboration, and lifting each other toward creative heights.</p>
            </article>
            <article class="value-card reveal-target">
                <div class="value-icon">✨</div>
                <h3>Authentic Stories</h3>
                <p>We believe in ethical documentation that honors subjects and reveals deeper human truths.</p>
            </article>
            <article class="value-card reveal-target">
                <div class="value-icon">🌍</div>
                <h3>Global Impact</h3>
                <p>Through photography, we aim to connect cultures, celebrate diversity, and create meaningful discourse.</p>
            </article>
        </div>
    </section>

<?php
include 'footer.php';
?>
