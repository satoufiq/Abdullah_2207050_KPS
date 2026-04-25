// ============================================================================
// KUET Photography Society - Main JavaScript
// ============================================================================

/**
 * Initialize all functionality on DOM ready
 */
document.addEventListener('DOMContentLoaded', function () {
    initializeNavigation();
    initializeLoader();
    initializeRevealAnimations();
    initializeCountAnimations();
    initializeFormHandlers();
    initializeLightbox();
    initializeUploadPreview();
    initializePageTransitions();
    initializeScrollEffects();
});

// ============================================================================
// NAVIGATION & MENU TOGGLE
// ============================================================================

function initializeNavigation() {
    const menuToggle = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    const navLinks = document.querySelectorAll('.nav-links a');

    if (!menuToggle || !navMenu) return;

    // Toggle menu on hamburger click
    menuToggle.addEventListener('click', function () {
        navMenu.classList.toggle('is-open');
        menuToggle.classList.toggle('is-active');
    });

    // Close menu when a link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            navMenu.classList.remove('is-open');
            menuToggle.classList.remove('is-active');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (event) {
        if (!event.target.closest('.navbar')) {
            navMenu.classList.remove('is-open');
            menuToggle.classList.remove('is-active');
        }
    });

    // Set active link based on current page
    setActiveNavLink();
}

function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'home.html')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// ============================================================================
// PAGE LOADER
// ============================================================================

function initializeLoader() {
    const loader = document.getElementById('site-loader');
    if (!loader) return;

    // Hide loader after page loads
    window.addEventListener('load', function () {
        loader.style.opacity = '0';
        loader.style.transition = 'opacity 0.5s ease-out';
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    });

    // Timeout fallback
    setTimeout(() => {
        if (loader && loader.style.display !== 'none') {
            loader.style.opacity = '0';
            loader.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }
    }, 3000);
}

// ============================================================================
// REVEAL ANIMATIONS ON SCROLL
// ============================================================================

function initializeRevealAnimations() {
    const revealElements = document.querySelectorAll('.reveal-target');
    if (!revealElements.length) return;

    // Fallback for older browsers: show all reveal targets.
    if (!('IntersectionObserver' in window)) {
        revealElements.forEach(el => {
            el.classList.add('reveal-animated', 'is-visible');
        });
        return;
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Many section styles use .is-visible while others use .reveal-animated.
                entry.target.classList.add('reveal-animated', 'is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(el => observer.observe(el));
}

// ============================================================================
// COUNT ANIMATIONS (ANIMATED COUNTERS)
// ============================================================================

function initializeCountAnimations() {
    const countElements = document.querySelectorAll('[data-count-to]');
    if (!countElements.length) return;

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCount(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    countElements.forEach(el => observer.observe(el));
}

function animateCount(element) {
    const targetValue = parseInt(element.getAttribute('data-count-to')) || 0;
    const duration = parseInt(element.getAttribute('data-duration')) || 2000;
    const suffix = element.getAttribute('data-suffix') || '';
    const prefix = element.getAttribute('data-prefix') || '';

    let currentValue = 0;
    const startTime = Date.now();

    function updateCount() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        currentValue = Math.floor(targetValue * progress);
        element.textContent = prefix + currentValue + suffix;

        if (progress < 1) {
            requestAnimationFrame(updateCount);
        } else {
            element.textContent = prefix + targetValue + suffix;
        }
    }

    updateCount();
}

// ============================================================================
// FORM HANDLERS
// ============================================================================

function initializeFormHandlers() {
    // Membership form
    const membershipForm = document.getElementById('membership-form');
    if (membershipForm) {
        membershipForm.addEventListener('submit', handleMembershipSubmit);
    }

    // Event registration form
    const registrationForm = document.getElementById('event-registration-form');
    if (registrationForm) {
        registrationForm.addEventListener('submit', handleRegistrationSubmit);
    }

    // Contact form
    const contactForm = document.querySelector('form[action*="/api/v1/memberships"]');
    if (contactForm && contactForm !== membershipForm) {
        contactForm.addEventListener('submit', handleFormSubmit);
    }
}

function handleMembershipSubmit(e) {
    e.preventDefault();
    if (validateForm(this)) {
        showFormMessage(this, 'Thank you! We\'ll contact you soon.', 'success');
        setTimeout(() => this.reset(), 1500);
    }
}

function handleRegistrationSubmit(e) {
    e.preventDefault();
    if (validateForm(this)) {
        showFormMessage(this, 'Registration successful! See you at the event.', 'success');
        setTimeout(() => this.reset(), 1500);
    }
}

function handleFormSubmit(e) {
    e.preventDefault();
    if (validateForm(this)) {
        showFormMessage(this, 'Message sent successfully!', 'success');
        setTimeout(() => this.reset(), 1500);
    }
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('input-error');
            isValid = false;
        } else if (input.type === 'email') {
            if (!isValidEmail(input.value)) {
                input.classList.add('input-error');
                isValid = false;
            } else {
                input.classList.remove('input-error');
            }
        } else {
            input.classList.remove('input-error');
        }
    });

    return isValid;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function showFormMessage(form, message, type = 'info') {
    const statusElement = form.querySelector('.form-status');
    if (statusElement) {
        statusElement.textContent = message;
        statusElement.className = 'form-status form-status-' + type;
        statusElement.style.animation = 'fadeIn 0.5s ease-in-out';
    }
}

// ============================================================================
// LIGHTBOX FUNCTIONALITY
// ============================================================================

function initializeLightbox() {
    const lightboxTriggers = document.querySelectorAll('[data-lightbox-trigger]');
    const lightbox = document.getElementById('lightbox');

    if (!lightbox) return;

    lightboxTriggers.forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            
            const img = trigger.querySelector('img');
            if (!img) return;

            const title = img.getAttribute('data-title') || 'Photo';
            const by = img.getAttribute('data-by') || '';
            const meta = img.getAttribute('data-meta') || '';
            const src = img.getAttribute('src');

            openLightbox(lightbox, src, title, by, meta);
        });
    });

    // Close lightbox
    const closeBtn = lightbox.querySelector('.lightbox-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => closeLightbox(lightbox));
    }

    // Close on background click
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox(lightbox);
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.getAttribute('aria-hidden') === 'false') {
            closeLightbox(lightbox);
        }
    });
}

function openLightbox(lightbox, src, title, by, meta) {
    const img = lightbox.querySelector('.lightbox-image');
    const titleEl = lightbox.querySelector('#lightbox-title');
    const byEl = lightbox.querySelector('#lightbox-by');
    const metaEl = lightbox.querySelector('#lightbox-meta');

    if (img) img.src = src;
    if (titleEl) titleEl.textContent = title;
    if (byEl) byEl.textContent = by;
    if (metaEl) metaEl.textContent = meta;

    lightbox.setAttribute('aria-hidden', 'false');
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        lightbox.classList.add('lightbox-open');
    }, 10);
}

function closeLightbox(lightbox) {
    lightbox.setAttribute('aria-hidden', 'true');
    lightbox.classList.remove('lightbox-open');
    document.body.style.overflow = '';
    
    setTimeout(() => {
        lightbox.style.display = 'none';
    }, 300);
}

// ============================================================================
// UPLOAD PREVIEW
// ============================================================================

function initializeUploadPreview() {
    const uploadForm = document.getElementById('home-upload-form');
    const uploadImage = document.getElementById('upload-image');
    const uploadPreview = document.getElementById('upload-preview');
    const previewImage = document.getElementById('upload-preview-image');
    const previewTitle = document.getElementById('upload-preview-title');
    const previewMeta = document.getElementById('upload-preview-meta');

    if (!uploadImage || !uploadPreview) return;

    uploadImage.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.match('image.*')) {
            alert('Please select an image file');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            previewImage.src = event.target.result;
            
            const title = document.getElementById('upload-title').value || 'Untitled';
            const category = document.getElementById('upload-category').value || 'Uncategorized';
            
            previewTitle.textContent = title;
            previewMeta.textContent = `Category: ${category}`;
            
            uploadPreview.hidden = false;
            uploadPreview.style.animation = 'fadeIn 0.5s ease-in-out';
        };

        reader.readAsDataURL(file);
    });

    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (uploadImage.files.length === 0) {
                alert('Please select an image');
                return;
            }
            showFormMessage(uploadForm, 'Image submitted successfully! ✓', 'success');
            setTimeout(() => {
                uploadForm.reset();
                uploadPreview.hidden = true;
            }, 1500);
        });
    }
}

// ============================================================================
// PAGE TRANSITIONS
// ============================================================================

function initializePageTransitions() {
    const pageLinks = document.querySelectorAll('.page-link');

    pageLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            
            // Don't prevent default for anchor links
            if (href.startsWith('#')) {
                return;
            }

            // Allow middle-click and ctrl+click to open in new tab
            if (e.button === 1 || e.ctrlKey || e.metaKey) {
                return;
            }

            e.preventDefault();
            transitionToPage(href);
        });
    });
}

function transitionToPage(url) {
    const loader = document.getElementById('site-loader');
    if (loader) {
        loader.style.display = 'flex';
        loader.style.opacity = '1';
        loader.style.transition = 'opacity 0.3s ease-in-out';
    }

    setTimeout(() => {
        window.location.href = url;
    }, 300);
}

// ============================================================================
// SCROLL EFFECTS
// ============================================================================

function initializeScrollEffects() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    let lastScrollTop = 0;
    const navbarHeight = navbar.offsetHeight;

    window.addEventListener('scroll', function () {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > navbarHeight) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }

        lastScrollTop = scrollTop;
    }, false);

    // Hero slider auto-advance
    const heroSlides = document.querySelectorAll('.hero-slide');
    if (heroSlides.length > 1) {
        initializeHeroSlider(heroSlides);
    }
}

// ============================================================================
// HERO SLIDER
// ============================================================================

function initializeHeroSlider(slides) {
    let currentSlide = 0;

    function nextSlide() {
        slides[currentSlide].classList.remove('is-active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('is-active');
    }

    // Auto-advance slide every 5 seconds
    setInterval(nextSlide, 5000);
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Smooth scroll to element
 */
function smoothScrollTo(element) {
    element.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Debounce function for performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function for performance
 */
function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}
