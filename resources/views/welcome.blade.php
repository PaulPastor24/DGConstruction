<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&G Construction Inc. | Design. Build. Deliver.</title>
    <meta name="description" content="D&G Construction Inc. delivers residential, commercial, and renovation services with a modern, reliable approach.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landingpage.css') }}">

    <style>
        .button {
            appearance: none;
            -webkit-appearance: none;
        }

        .button-ghost-light {
            border-color: rgba(23, 56, 36, 0.22);
            background: #ffffff;
            color: var(--forest, #173824);
        }

        .button-ghost-light:hover {
            background: rgba(255, 255, 255, 0.88);
            color: var(--forest, #173824);
            transform: translateY(-1px);
        }

        .cta-action-row {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .landing-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(10, 20, 14, 0.58);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .landing-modal-overlay.is-open {
            display: flex;
        }

        .landing-modal-card {
            width: min(760px, 100%);
            max-height: min(88vh, 820px);
            overflow: hidden;
            border: 1px solid rgba(229, 236, 231, 0.85);
            border-radius: 28px;
            background:
                radial-gradient(circle at top right, rgba(23, 56, 36, 0.08), transparent 34%),
                #ffffff;
            box-shadow: 0 28px 80px rgba(0, 0, 0, 0.24);
            animation: landingModalIn 180ms ease-out;
        }

        @keyframes landingModalIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .landing-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 24px 18px;
            border-bottom: 1px solid #eef3ef;
        }

        .landing-modal-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: var(--forest, #173824);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .landing-modal-title {
            margin: 0 0 6px;
            color: var(--text-dark, #10271b);
            font-family: var(--font-heading, 'Syne', sans-serif);
            font-size: clamp(1.35rem, 4vw, 2rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.08;
        }

        .landing-modal-subtitle {
            max-width: 560px;
            margin: 0;
            color: var(--text-muted, #6f7d74);
            font-size: 0.93rem;
            line-height: 1.6;
        }

        .landing-modal-close {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            place-items: center;
            border: 1px solid #e1e9e3;
            border-radius: 50%;
            background: #ffffff;
            color: var(--forest, #173824);
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .landing-modal-close:hover {
            background: var(--forest, #173824);
            color: #ffffff;
        }

        .landing-modal-body {
            max-height: calc(min(88vh, 820px) - 132px);
            overflow-y: auto;
            padding: 22px 24px 24px;
        }

        .landing-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .landing-form-field {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 7px;
        }

        .landing-form-field.full {
            grid-column: 1 / -1;
        }

        .landing-form-field label {
            color: #324338;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .landing-form-field input,
        .landing-form-field select,
        .landing-form-field textarea {
            width: 100%;
            border: 1px solid #dbe5df;
            border-radius: 14px;
            background: #fbfdfb;
            color: var(--text-dark, #10271b);
            font-size: 0.92rem;
            outline: none;
            padding: 13px 14px;
            transition: all 0.2s ease;
        }

        .landing-form-field textarea {
            min-height: 118px;
            resize: vertical;
        }

        .landing-form-field input:focus,
        .landing-form-field select:focus,
        .landing-form-field textarea:focus {
            border-color: var(--forest, #173824);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(23, 56, 36, 0.08);
        }

        .landing-form-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 16px 0 0;
            padding: 13px 14px;
            border: 1px solid #dfeae2;
            border-radius: 16px;
            background: #f7fbf8;
            color: #516157;
            font-size: 0.83rem;
            line-height: 1.5;
        }

        .landing-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .landing-form-submit {
            min-width: 180px;
        }

        .landing-form-cancel {
            border-color: #dbe5df;
            background: #ffffff;
            color: var(--forest, #173824);
        }

        .landing-form-message {
            display: none;
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 0.86rem;
            font-weight: 700;
        }

        .landing-form-message.is-visible {
            display: block;
        }

        .landing-form-message.success {
            border: 1px solid #bfefd0;
            background: #effcf4;
            color: #166534;
        }

        @media (max-width: 768px) {
            .cta-action-row {
                flex-direction: column;
            }

            .landing-modal-overlay {
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                min-height: 100dvh;
                padding: max(10px, env(safe-area-inset-top)) 10px max(10px, env(safe-area-inset-bottom));
                overflow: hidden;
            }

            .landing-modal-card {
                display: flex;
                width: 100%;
                max-height: calc(100vh - 20px);
                max-height: calc(100dvh - 20px);
                flex-direction: column;
                border-radius: 24px;
                overflow: hidden;
            }

            .landing-modal-header {
                flex: 0 0 auto;
                padding: 18px 16px 12px;
                gap: 12px;
            }

            .landing-modal-title {
                font-size: 1.42rem;
                line-height: 1.08;
            }

            .landing-modal-subtitle {
                font-size: 0.84rem;
                line-height: 1.48;
            }

            .landing-modal-close {
                width: 40px;
                height: 40px;
                box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
            }

            .landing-modal-body {
                flex: 1 1 auto;
                min-height: 0;
                max-height: none;
                overflow-y: auto;
                padding: 14px 16px 0;
                -webkit-overflow-scrolling: touch;
            }

            .landing-form-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .landing-form-field {
                gap: 5px;
            }

            .landing-form-field label {
                font-size: 0.75rem;
            }

            .landing-form-field input,
            .landing-form-field select,
            .landing-form-field textarea {
                min-height: 46px;
                border-radius: 13px;
                font-size: 0.9rem;
                padding: 11px 13px;
            }

            .landing-form-field textarea {
                min-height: 92px;
            }

            .landing-form-note {
                margin-top: 12px;
                padding: 11px 12px;
                border-radius: 14px;
                font-size: 0.78rem;
                line-height: 1.45;
            }

            .landing-form-actions {
                position: sticky;
                bottom: 0;
                z-index: 20;
                display: grid;
                grid-template-columns: 1fr;
                gap: 9px;
                margin: 14px -16px 0;
                padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
                border-top: 1px solid #eef3ef;
                background: linear-gradient(180deg, rgba(255,255,255,0.92) 0%, #ffffff 45%, #ffffff 100%);
                box-shadow: 0 -12px 30px rgba(15, 23, 42, 0.07);
            }

            .landing-form-actions .button {
                width: 100%;
                min-height: 46px;
                border-radius: 14px;
                font-size: 0.9rem;
            }

            .landing-form-submit {
                order: 1;
            }

            .landing-form-cancel {
                order: 2;
            }

            .landing-form-message {
                margin-bottom: 8px;
            }
        }

        @media (max-width: 420px) {
            .landing-modal-overlay {
                padding-left: 8px;
                padding-right: 8px;
            }

            .landing-modal-header {
                padding: 16px 14px 11px;
            }

            .landing-modal-body {
                padding-left: 14px;
                padding-right: 14px;
            }

            .landing-form-actions {
                margin-left: -14px;
                margin-right: -14px;
                padding-left: 14px;
                padding-right: 14px;
            }
        }
    </style>
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

        <a class="nav-cta" href="{{ route('login') }}">Login <span class="arrow">→</span></a>
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
                    <button type="button" class="button button-primary js-open-landing-modal" data-target-modal="quoteModal">Get a Quote <span class="arrow">→</span></button>
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
                <h2>Ready to Start or Ask a Question?</h2>
                <p>Request a project estimate or send a message to our team. We will review your concern and get back to you with the next steps.</p>
                <div class="cta-action-row">
                    <button type="button" class="button button-light js-open-landing-modal" data-target-modal="quoteModal">Get a Quote <span class="arrow">→</span></button>
                    <button type="button" class="button button-ghost-light js-open-landing-modal" data-target-modal="contactModal">Get in Touch <span class="arrow">→</span></button>
                </div>
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

    <!-- QUOTE REQUEST MODAL -->
    <div class="landing-modal-overlay" id="quoteModal" aria-hidden="true">
        <div class="landing-modal-card" role="dialog" aria-modal="true" aria-labelledby="quoteModalTitle">
            <div class="landing-modal-header">
                <div>
                    <span class="landing-modal-eyebrow">Estimate Request</span>
                    <h2 class="landing-modal-title" id="quoteModalTitle">Tell us about your project</h2>
                    <p class="landing-modal-subtitle">
                        Use this form for quote requests. Share the basic project details so the team can review the scope before contacting you.
                    </p>
                </div>
                <button type="button" class="landing-modal-close js-close-landing-modal" aria-label="Close quote form">×</button>
            </div>

            <div class="landing-modal-body">
                <form class="landing-contact-form" data-form-type="quote">
                    <div class="landing-form-grid">
                        <div class="landing-form-field">
                            <label for="quoteName">Full Name</label>
                            <input id="quoteName" name="Full Name" type="text" placeholder="Your name" required>
                        </div>

                        <div class="landing-form-field">
                            <label for="quoteEmail">Email Address</label>
                            <input id="quoteEmail" name="Email" type="email" placeholder="your@email.com" required>
                        </div>

                        <div class="landing-form-field">
                            <label for="quotePhone">Phone Number</label>
                            <input id="quotePhone" name="Phone" type="tel" placeholder="09XX XXX XXXX">
                        </div>

                        <div class="landing-form-field">
                            <label for="quoteProjectType">Project Type</label>
                            <select id="quoteProjectType" name="Project Type" required>
                                <option value="">Select project type</option>
                                <option>Residential Construction</option>
                                <option>Commercial Construction</option>
                                <option>Renovation / Remodeling</option>
                                <option>Project Management</option>
                                <option>Other Construction Service</option>
                            </select>
                        </div>

                        <div class="landing-form-field">
                            <label for="quoteLocation">Project Location</label>
                            <input id="quoteLocation" name="Location" type="text" placeholder="City / site location">
                        </div>

                        <div class="landing-form-field">
                            <label for="quoteTimeline">Target Timeline</label>
                            <select id="quoteTimeline" name="Target Timeline">
                                <option value="">Select timeline</option>
                                <option>As soon as possible</option>
                                <option>Within 1 month</option>
                                <option>Within 3 months</option>
                                <option>Planning stage only</option>
                            </select>
                        </div>

                        <div class="landing-form-field full">
                            <label for="quoteMessage">Project Details</label>
                            <textarea id="quoteMessage" name="Project Details" placeholder="Briefly describe the work needed, estimated size, preferred schedule, or special requirements." required></textarea>
                        </div>
                    </div>

                    <div class="landing-form-note">
                        <span>ℹ️</span>
                        <span>This quote request will open your email app with the project details already prepared. You can review it before sending.</span>
                    </div>

                    <div class="landing-form-actions">
                        <button type="button" class="button landing-form-cancel js-close-landing-modal">Cancel</button>
                        <button type="submit" class="button button-primary landing-form-submit">Prepare Quote Request <span class="arrow">→</span></button>
                    </div>

                    <div class="landing-form-message success" role="status"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- CONTACT / SUPPORT ASSISTANT MODAL -->
    <div class="landing-modal-overlay" id="contactModal" aria-hidden="true">
        <div class="landing-modal-card" role="dialog" aria-modal="true" aria-labelledby="contactModalTitle">
            <div class="landing-modal-header">
                <div>
                    <span class="landing-modal-eyebrow">Support Assistant</span>
                    <h2 class="landing-modal-title" id="contactModalTitle">How can we help?</h2>
                    <p class="landing-modal-subtitle">
                        Send a message to the D&G Construction team. Use this for general questions, project support, account access concerns, or follow-up requests.
                    </p>
                </div>
                <button type="button" class="landing-modal-close js-close-landing-modal" aria-label="Close contact form">×</button>
            </div>

            <div class="landing-modal-body">
                <form class="landing-contact-form" data-form-type="contact">
                    <div class="landing-form-grid">
                        <div class="landing-form-field">
                            <label for="contactName">Full Name</label>
                            <input id="contactName" name="Full Name" type="text" placeholder="Your name" required>
                        </div>

                        <div class="landing-form-field">
                            <label for="contactEmail">Email Address</label>
                            <input id="contactEmail" name="Email" type="email" placeholder="your@email.com" required>
                        </div>

                        <div class="landing-form-field">
                            <label for="contactConcern">Concern Type</label>
                            <select id="contactConcern" name="Concern Type" required>
                                <option value="">Select concern</option>
                                <option>General Inquiry</option>
                                <option>Project Update Question</option>
                                <option>Client Portal Access</option>
                                <option>Report / Timeline Concern</option>
                                <option>Other Support Request</option>
                            </select>
                        </div>

                        <div class="landing-form-field">
                            <label for="contactProject">Project Name</label>
                            <input id="contactProject" name="Project Name" type="text" placeholder="Optional">
                        </div>

                        <div class="landing-form-field full">
                            <label for="contactMessage">Message</label>
                            <textarea id="contactMessage" name="Message" placeholder="Write your question or concern here." required></textarea>
                        </div>
                    </div>

                    <div class="landing-form-note">
                        <span>💬</span>
                        <span>This works like a simple support assistant: fill out the form, submit, then your email app will open with the message prepared.</span>
                    </div>

                    <div class="landing-form-actions">
                        <button type="button" class="button landing-form-cancel js-close-landing-modal">Cancel</button>
                        <button type="submit" class="button button-primary landing-form-submit">Prepare Message <span class="arrow">→</span></button>
                    </div>

                    <div class="landing-form-message success" role="status"></div>
                </form>
            </div>
        </div>
    </div>

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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const companyEmail = 'info@dgconstruction.ca';

    function getModalByButton(button) {
        const targetId = button?.dataset?.targetModal;
        return targetId ? document.getElementById(targetId) : null;
    }

    function openLandingModal(modal) {
        if (!modal) return;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        const closeButton = modal.querySelector('.landing-modal-close');
        setTimeout(() => closeButton?.focus({ preventScroll: true }), 80);
    }

    function closeLandingModal(modal) {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        modal.querySelectorAll('.landing-form-message').forEach(message => {
            message.classList.remove('is-visible');
            message.textContent = '';
        });
    }

    document.querySelectorAll('.js-open-landing-modal').forEach(button => {
        button.addEventListener('click', () => {
            openLandingModal(getModalByButton(button));
        });
    });

    document.querySelectorAll('.js-close-landing-modal').forEach(button => {
        button.addEventListener('click', () => {
            closeLandingModal(button.closest('.landing-modal-overlay'));
        });
    });

    document.querySelectorAll('.landing-modal-overlay').forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                closeLandingModal(modal);
            }
        });
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.landing-modal-overlay.is-open').forEach(closeLandingModal);
        }
    });

    document.querySelectorAll('.landing-contact-form').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();

            const formType = form.dataset.formType || 'contact';
            const formData = new FormData(form);
            const messageBox = form.querySelector('.landing-form-message');

            const subject = formType === 'quote'
                ? 'D&G Construction Quote Request'
                : 'D&G Construction Contact / Support Request';

            const lines = [
                subject,
                '',
                ...Array.from(formData.entries()).map(([key, value]) => `${key}: ${value || 'N/A'}`),
                '',
                'Sent from the D&G Construction landing page.'
            ];

            const mailtoUrl = `mailto:${companyEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(lines.join('\n'))}`;

            if (messageBox) {
                messageBox.textContent = formType === 'quote'
                    ? 'Quote request prepared. Your email app will open so you can send it.'
                    : 'Message prepared. Your email app will open so you can send it.';
                messageBox.classList.add('is-visible');
            }

            window.location.href = mailtoUrl;
        });
    });
});
</script>

</body>
</html>