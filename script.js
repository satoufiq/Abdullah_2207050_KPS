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
        navMenu.classList.toggle('active');
        menuToggle.classList.toggle('is-active');
    });

    // Close menu when a link is clicked
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            navMenu.classList.remove('active');
            menuToggle.classList.remove('is-active');
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (event) {
        if (!event.target.closest('.navbar')) {
            navMenu.classList.remove('active');
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
    const uploadForm = document.getElementById('photo-submission-form');
    const fileInput = document.getElementById('photo-file');
    const fileUploadDiv = document.querySelector('.file-upload');

    if (!fileInput || !fileUploadDiv) return;

    // Handle file selection via input
    fileInput.addEventListener('change', handleFileSelect);

    // Handle drag and drop
    fileUploadDiv.addEventListener('dragover', handleDragOver, false);
    fileUploadDiv.addEventListener('dragleave', handleDragLeave, false);
    fileUploadDiv.addEventListener('drop', handleDrop, false);

    // Click to browse files
    fileUploadDiv.addEventListener('click', () => fileInput.click());

    // Form submission
    if (uploadForm) {
        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            
            if (fileInput.files.length === 0) {
                showFormMessage(uploadForm, 'Please select a photo to upload', 'error');
                return;
            }

            const formData = new FormData(uploadForm);
            
            fetch('api/submit_photo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showFormMessage(uploadForm, 'Photo submitted successfully! ✓', 'success');
                    uploadForm.reset();
                    fileUploadDiv.classList.remove('dragover');
                    setTimeout(() => {
                        uploadForm.reset();
                    }, 2000);
                } else {
                    showFormMessage(uploadForm, data.error || 'Error uploading photo', 'error');
                }
            })
            .catch(error => {
                showFormMessage(uploadForm, 'Error: ' + error.message, 'error');
            });
        });
    }

    function handleFileSelect(e) {
        const files = e.target.files;
        validateAndHandleFiles(files);
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        fileUploadDiv.classList.add('dragover');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        fileUploadDiv.classList.remove('dragover');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        fileUploadDiv.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        validateAndHandleFiles(files);
    }

    function validateAndHandleFiles(files) {
        if (files.length === 0) return;

        const file = files[0];

        // Validate file type
        if (!file.type.startsWith('image/')) {
            showFormMessage(uploadForm, 'Please select a valid image file', 'error');
            return;
        }

        // Validate file size (max 10MB)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            showFormMessage(uploadForm, 'File size must be less than 10MB', 'error');
            return;
        }

        fileInput.files = files;
        
        // Show file name in label
        const fileLabel = document.querySelector('.file-label');
        if (fileLabel) {
            fileLabel.textContent = `Selected: ${file.name}`;
        }
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
