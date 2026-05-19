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
                    <form class="contact-form" id="membership-form" method="post" action="api/membership_submit.php" novalidate>
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" autocomplete="name" maxlength="120" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" autocomplete="email" maxlength="180" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" autocomplete="tel" maxlength="30">
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
                </div>
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
