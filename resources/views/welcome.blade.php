<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&G Construction Inc. | Design. Build. Deliver.</title>
    <meta name="description" content="D&G Construction Inc. delivers residential, commercial, and renovation services with a modern, reliable approach.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landingpage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
</head>
<body class="landing-page">
<div class="page-shell">
    
    <nav class="site-nav">
        <a class="brand" href="#top" aria-label="D&G Construction home">
            <img src="{{ asset('images/D&G.png') }}" alt="D&G Construction logo">
            <span class="brand-text">D&G CONSTRUCTION INC.</span>
        </a>

        <div class="nav-links">
            <a href="#top" class="active">Home</a>
            <a href="#about">About Us</a>
            <a href="#services">Services</a>
            <a href="#projects">Projects</a>
            <a href="#resources">Resources</a>
            <a href="#contact">Contact</a>
        </div>

        <a class="nav-cta" href="{{ route('login') }}">Request a Quote <span class="arrow">→</span></a>
    </nav>

    <main>
        <!-- HERO SECTION -->
        <section class="hero" id="top">
            <div class="hero-copy">
                <h1>Design. Build.<br>Deliver.</h1>
                <p class="hero-lead">
                    D&G Construction Inc. delivers quality craftsmanship and reliable construction solutions from concept to completion.
                </p>
                <div class="hero-actions">
                    <a class="button button-primary" href="{{ route('login') }}">Get a Quote <span class="arrow">→</span></a>
                    <a class="button button-secondary" href="#projects">
                        View Our Work 
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <!-- Replace with your combined rendering background image as seen in image_60ea5c.jpg -->
                <img src="{{ asset('images/h4.jpg') }}" alt="Construction site with workers and equipment">
            </div>
        </section>

        <!-- SERVICES ACCORDION/BAND -->
        <section class="services-band" id="services">
            <div class="service-grid">
                <article class="service-card">
                    <div class="service-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 5 7 7-7 7"/><path d="M5 12h14"/></svg>
                    </div>
                    <h3>Design & Planning</h3>
                    <p>Custom designs tailored to your vision and needs.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    </div>
                    <h3>Construction</h3>
                    <p>Quality construction built to last.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <h3>Project Management</h3>
                    <p>On-time, on-budget, every step of the way.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 21H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v7"/><path d="M18 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                    </div>
                    <h3>Renovations</h3>
                    <p>Transforming spaces with expert care.</p>
                </article>
                <article class="service-card">
                    <div class="service-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>Client Focused</h3>
                    <p>Clear communication and trusted partnerships.</p>
                </article>
            </div>
        </section>

        <!-- ABOUT & METRICS SECTION -->
        <section class="about-section" id="about">
            <div class="about-copy">
                <h2>Building With Integrity.<br>Delivering Excellence.</h2>
                <p>
                    At D&G Construction Inc., we take pride in our craftsmanship, attention to detail, and commitment to our clients. From residential to commercial projects, we build with purpose and precision.
                </p>
                <a class="button button-outline" href="#contact">Learn More About Us <span class="arrow">→</span></a>
            </div>

            <div class="about-metrics">
                <div class="metric-card">
                    <div class="metric-icon">👥</div>
                    <strong>100+</strong>
                    <span>Projects Completed</span>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">🏅</div>
                    <strong>15+</strong>
                    <span>Years of Experience</span>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">😊</div>
                    <strong>75+</strong>
                    <span>Happy Clients</span>
                </div>
                <div class="metric-card">
                    <div class="metric-icon">📍</div>
                    <strong>Serving</strong>
                    <span>Our Community with Pride</span>
                </div>
            </div>
        </section>

        <!-- PROJECTS SECTION -->
        <section class="projects-section" id="projects">
            <div class="section-heading-row">
                <h2>Featured Projects</h2>
                <div class="carousel-controls">
                    <button class="carousel-btn prev-btn" aria-label="Previous project" disabled>←</button>
                    <button class="carousel-btn next-btn" aria-label="Next project">→</button>
                </div>
            </div>

            <div class="carousel-view-window">
                <div class="project-carousel-track">
                    <article class="project-card">
                        <div class="project-img-container">
                            <img src="{{ asset('images/h1.jpg') }}" alt="Custom Home Build">
                        </div>
                        <div class="project-body">
                            <h3>Custom Home Build</h3>
                            <p>Barrie, ON</p>
                        </div>
                        <a href="#" class="project-arrow-btn">→</a>
                    </article>
                    <article class="project-card">
                        <div class="project-img-container">
                            <img src="{{ asset('images/h2.jpg') }}" alt="Full Home Renovation">
                        </div>
                        <div class="project-body">
                            <h3>Full Home Renovation</h3>
                            <p>Innisfil, ON</p>
                        </div>
                        <a href="#" class="project-arrow-btn">→</a>
                    </article>
                    <article class="project-card">
                        <div class="project-img-container">
                            <img src="{{ asset('images/h3.jpg') }}" alt="Commercial Build">
                        </div>
                        <div class="project-body">
                            <h3>Commercial Build</h3>
                            <p>Newmarket, ON</p>
                        </div>
                        <a href="#" class="project-arrow-btn">→</a>
                    </article>
                    <article class="project-card">
                        <div class="project-img-container">
                            <img src="{{ asset('images/h5.jpg') }}" alt="Interior Renovation">
                        </div>
                        <div class="project-body">
                            <h3>Interior Renovation</h3>
                            <p>Aurora, ON</p>
                        </div>
                        <a href="#" class="project-arrow-btn">→</a>
                    </article>
                    <article class="project-card">
                        <div class="project-img-container">
                            <img src="{{ asset('images/h1.jpg') }}" alt="Custom Home Build">
                        </div>
                        <div class="project-body">
                            <h3>Custom Home Build</h3>
                            <p>Barrie, ON</p>
                        </div>
                        <a href="#" class="project-arrow-btn">→</a>
                    </article>
                    <article class="project-card">
                        <div class="project-img-container">
                            <img src="{{ asset('images/h3.jpg') }}" alt="Commercial Build">
                        </div>
                        <div class="project-body">
                            <h3>Commercial Build</h3>
                            <p>Newmarket, ON</p>
                        </div>
                        <a href="#" class="project-arrow-btn">→</a>
                    </article>
                </div>
            </div>
        </section>

        <!-- TESTIMONIAL SLIDER -->
        <section class="testimonial-strip">
            <div class="testimonial-content">
                <span class="quote-icon">“</span>
                <p class="quote-text">D&G Construction Inc. exceeded our expectations. Their team was professional, reliable, and the quality of work is outstanding.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="author-meta">
                        <strong>Mark & Sarah T.</strong>
                        <span>Happy Homeowners</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </section>

        <!-- CALL TO ACTION -->
        <section class="cta-section" id="contact">
            <div class="cta-container">
                <h2>Let's Build Something Great Together</h2>
                <p>From design to construction, we are ready to bring your vision to life. Get in touch with our team today.</p>
                <a class="button button-light" href="{{ route('login') }}">Get In Touch <span class="arrow">→</span></a>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-top">
            <div class="footer-brand-column">
                <div class="footer-logo-row">
                    <img src="{{ asset('images/bg.png') }}" alt="D&G Construction logo">
                    <strong>D&G CONSTRUCTION INC.</strong>
                </div>
                <p>Designing and building spaces that stand the test of time.</p>
            </div>
            
            <div class="footer-links-column">
                <h4>Quick Links</h4>
                <a href="#about">About Us</a>
                <a href="#services">Services</a>
                <a href="#projects">Projects</a>
                <a href="#contact">Contact</a>
            </div>
            
            <div class="footer-links-column">
                <h4>Services</h4>
                <a href="#services">Custom Homes</a>
                <a href="#services">Renovations</a>
                <a href="#services">Commercial</a>
                <a href="#services">Project Management</a>
            </div>
            
            <div class="footer-links-column">
                <h4>Contact Us</h4>
                <span>📞 (705) 123-4567</span>
                <span>✉️ info@dgconstruction.ca</span>
                <span>📍 Barrie, Ontario</span>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; 2026 D&G Construction Inc. All Rights Reserved.</span>
            <div class="footer-socials">
                <a href="#" aria-label="Facebook">🌐</a>
                <a href="#" aria-label="Instagram">📸</a>
            </div>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const track = document.querySelector('.project-carousel-track');
    const cards = Array.from(document.querySelectorAll('.project-carousel-track .project-card'));
    const prevBtn = document.querySelector('.carousel-controls .prev-btn');
    const nextBtn = document.querySelector('.carousel-controls .next-btn');
    const carouselWindow = document.querySelector('.carousel-view-window');

    if (!track || cards.length === 0) return;

    let currentIndex = 0;
    let autoSlideInterval = null;
    const slideDelay = 4000; // Time in milliseconds (4 seconds) between slides

    function getVisibleCardsCount() {
        const width = window.innerWidth;
        if (width > 1024) return 3; 
        if (width > 768) return 2;  
        return 1;                   
    }

    function updateCarouselPosition() {
        const visibleCount = getVisibleCardsCount();
        const maxIndex = Math.max(0, cards.length - visibleCount);
        
        if (currentIndex > maxIndex) {
            currentIndex = maxIndex;
        }

        const cardWidth = cards[0].getBoundingClientRect().width;
        const gap = parseFloat(window.getComputedStyle(track).gap) || 0;
        
        const amountToMove = currentIndex * (cardWidth + gap);
        track.style.transform = `translateX(-${amountToMove}px)`;

        // Control button state flags
        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex >= maxIndex;
    }

    function moveToNextSlide() {
        const visibleCount = getVisibleCardsCount();
        const maxIndex = Math.max(0, cards.length - visibleCount);

        if (currentIndex < maxIndex) {
            currentIndex++;
        } else {
            currentIndex = 0; // Loop back to the first slide seamlessly
        }
        updateCarouselPosition();
    }

    function moveToPrevSlide() {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            // Loop forward to the absolute end if they press back at index 0
            const visibleCount = getVisibleCardsCount();
            currentIndex = Math.max(0, cards.length - visibleCount);
        }
        updateCarouselPosition();
    }

    // Timer control functions
    function startAutoSlide() {
        if (autoSlideInterval === null) {
            autoSlideInterval = setInterval(moveToNextSlide, slideDelay);
        }
    }

    function stopAutoSlide() {
        if (autoSlideInterval !== null) {
            clearInterval(autoSlideInterval);
            autoSlideInterval = null;
        }
    }

    // Manual Event Listeners
    nextBtn.addEventListener('click', () => {
        moveToNextSlide();
        // Restart timer on interaction so it doesn't jump immediately after a click
        stopAutoSlide();
        startAutoSlide();
    });

    prevBtn.addEventListener('click', () => {
        moveToPrevSlide();
        stopAutoSlide();
        startAutoSlide();
    });

    // Pause on Hover for User Accessibility
    if (carouselWindow) {
        carouselWindow.addEventListener('mouseenter', stopAutoSlide);
        carouselWindow.addEventListener('mouseleave', startAutoSlide);
    }

    // Handle viewport changes to keep positions stable
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            updateCarouselPosition();
        }, 100);
    });

    // Initialize systems
    updateCarouselPosition();
    startAutoSlide();
});
</script>
</body>
</html>