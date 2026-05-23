<?php
/**
 * KUET Photography Society - Contact Page
 */
require_once 'config.php';

$page_title = 'Contact';
$current_page = 'contact';

include 'header.php';
include 'navbar.php';
?>

    <div class="site-loader" id="site-loader"><span></span></div>

    <header class="page-hero reveal-target" style="background-image: linear-gradient(rgba(8,17,31,0.58), rgba(8,17,31,0.88)), url('images/collections/product/msg-880120795-7940.jpg');">
        <div class="page-hero-content">
            <p class="hero-badge">Contact and Membership</p>
            <h1>Join KUET Photography Society</h1>
            <p>Reach out to collaborate, attend events, or become a member of our visual storytelling community.</p>
        </div>
    </header>

    <section class="contact-section" id="contact">
        <div class="section-heading">
            <p class="eyebrow">Contact</p>
            <h2>Get in touch</h2>
        </div>
        <div class="contact-wrapper">
            <div class="contact-container">
                <div class="contact-info reveal-target">
                    <h3>Contact Information</h3>
                    <div class="info-stack">
                        <div class="contact-details">
                            <p><strong>Email:</strong></p>
                            <a href="mailto:kuetphotosociety@gmail.com">kuetphotosociety@gmail.com</a>
                        </div>
                        <div class="contact-details">
                            <p><strong>Phone:</strong></p>
                            <p>+880-1000000000</p>
                        </div>
                        <div class="contact-details">
                            <p><strong>Location:</strong></p>
                            <p>KUET Campus, Khulna, Bangladesh</p>
                        </div>
                    </div>

                    <div class="join-instructions">
                        <h3>How to Join</h3>
                        <ol class="steps-list">
                            <li>Fill out the membership form with your details</li>
                            <li>Receive orientation details via email</li>
                            <li>Attend your first workshop session</li>
                            <li>Submit work and join creative projects</li>
                        </ol>
                    </div>
                </div>

                <div class="contact-form-container reveal-target">
                    <h3>Membership Interest Form</h3>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <!-- Login required modal -->
                        <div id="login-required-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="login-required-title">
                            <div class="modal">
                                <h3 id="login-required-title">Login required</h3>
                                <p>Please log in to submit a membership form.</p>
                                <div class="modal-actions">
                                    <a href="login.php" class="btn primary">Log in</a>
                                    <a href="register.php" class="btn">Register</a>
                                    <button id="modal-close-btn" class="btn-link">Close</button>
                                </div>
                            </div>
                        </div>

                        <style>
                        /* Minimal modal styles scoped to contact page */
                        #login-required-modal.modal-overlay{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);z-index:9999}
                        #login-required-modal .modal{background:#fff;padding:20px 22px;border-radius:8px;max-width:420px;width:92%;box-shadow:0 8px 30px rgba(0,0,0,0.4);color:#111}
                        #login-required-modal h3{margin:0 0 8px;font-size:1.1rem}
                        #login-required-modal p{margin:0 0 14px;color:#333}
                        #login-required-modal .modal-actions{display:flex;gap:8px;align-items:center}
                        #login-required-modal .btn{display:inline-block;padding:8px 12px;border-radius:6px;background:#191E36;color:#fff;text-decoration:none}
                        #login-required-modal .btn.primary{background:#D9B44A;color:#121212}
                        #login-required-modal .btn-link{background:transparent;border:0;color:#555;cursor:pointer;padding:6px}
                        @media (prefers-reduced-motion: no-preference){#login-required-modal .modal{transform:translateY(-6px);transition:transform .18s ease,opacity .18s ease}}
                        </style>

                        <script>
                        (function(){
                            const modal = document.getElementById('login-required-modal');
                            if (!modal) return;
                            // show modal on page load
                            modal.style.display = 'flex';
                            // close handler
                            const closeBtn = document.getElementById('modal-close-btn');
                            closeBtn && closeBtn.addEventListener('click', function(){ modal.style.display = 'none'; });
                            // allow ESC to close
                            document.addEventListener('keydown', function(e){ if (e.key === 'Escape') modal.style.display = 'none'; });
                            // clicking overlay closes
                            modal.addEventListener('click', function(e){ if (e.target === modal) modal.style.display = 'none'; });
                        })();
                        </script>
                    <?php else: ?>
                        <?php
                            // Pre-fill name and email from session when available
                            $prefill_name = htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8');
                            $prefill_email = htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES, 'UTF-8');
                        ?>
                        <form class="contact-form" id="membership-form" method="post" action="api/membership_submit.php" novalidate>
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" autocomplete="name" maxlength="120" required value="<?php echo $prefill_name; ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" autocomplete="email" maxlength="180" required value="<?php echo $prefill_email; ?>">
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" autocomplete="tel" maxlength="30">
                            </div>

                            <div class="form-group">
                                <label for="batch">Batch *</label>
                                <select id="batch" name="batch" required>
                                    <option value="">Select your batch</option>
                                    <option value="2020">Batch 2020</option>
                                    <option value="2021">Batch 2021</option>
                                    <option value="2022">Batch 2022</option>
                                    <option value="2023">Batch 2023</option>
                                    <option value="2024">Batch 2024</option>
                                    <option value="2025">Batch 2025</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="experience">Photography Experience</label>
                                <select id="experience" name="experience">
                                    <option value="">Select your level...</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="interests">Areas of Interest (select all that apply)</label>
                                <div class="checkbox-group">
                                    <label><input type="checkbox" name="interests[]" value="portrait"> Portrait Photography</label>
                                    <label><input type="checkbox" name="interests[]" value="landscape"> Landscape Photography</label>
                                    <label><input type="checkbox" name="interests[]" value="street"> Street Photography</label>
                                    <label><input type="checkbox" name="interests[]" value="events"> Event Coverage</label>
                                    <label><input type="checkbox" name="interests[]" value="editing"> Photo Editing</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="5" maxlength="1200" placeholder="Tell us about yourself and why you want to join..." required></textarea>
                            </div>

                            <button type="submit" class="submit-button">Submit</button>
                            <div id="form-response" class="form-status" aria-live="polite"></div>
                        </form>
                    <?php endif; ?>
                </div>
                <script>
                (function(){
                    const form = document.getElementById('membership-form');
                    if (!form) return;
                        form.addEventListener('submit', async function(e){
                        e.preventDefault();
                        const btn = form.querySelector('button[type="submit"]');
                        const respEl = document.getElementById('form-response');
                        btn.disabled = true;
                        respEl.textContent = 'Submitting...';

                        const formData = new FormData(form);
                        try {
                            const res = await fetch(form.action, { method: 'POST', body: formData });
                            // try parse JSON, but fallback to text for debugging
                            let data;
                            const text = await res.text();
                            try { data = JSON.parse(text); } catch (e) { data = null; }

                            if (!res.ok) {
                                // show server provided message if available
                                const msg = (data && data.error) ? data.error : text || ('Server error ' + res.status);
                                respEl.textContent = msg;
                            } else {
                                if (data && data.success) {
                                    respEl.textContent = data.message || 'Application submitted.';
                                    form.reset();
                                } else if (data && data.error) {
                                    respEl.textContent = data.error;
                                } else {
                                    respEl.textContent = 'Submission failed. Try again.';
                                }
                            }
                        } catch (err) {
                            console.error(err);
                            respEl.textContent = 'Submission failed. Try again.';
                        }
                        btn.disabled = false;
                    });
                })();
                </script>
            </div>
        </div>
    </section>

    <section class="social social--contact">
        <div class="section-heading">
            <p class="eyebrow">Social Presence</p>
            <h2>Follow our journey</h2>
        </div>
        <div class="social-links-container">
            <a href="https://www.instagram.com/kuetps.official/" class="social-button reveal-target">
                <img src="images/icons/instagram.png" alt="Instagram" class="social-btn-icon">
                <span>@kuetphotography</span>
            </a>
            <a href="https://x.com/kuetphotography" class="social-button reveal-target">
                <img src="images/icons/twitter.png" alt="X" class="social-btn-icon">
                <span>@kuetphotography</span>
            </a>
            <a href="https://www.facebook.com/kuetps/" class="social-button reveal-target">
                <img src="images/icons/facebook.png" alt="Facebook" class="social-btn-icon">
                <span>KUET Photography Club</span>
            </a>
        </div>
    </section>

    <?php include 'footer.php'; ?>
