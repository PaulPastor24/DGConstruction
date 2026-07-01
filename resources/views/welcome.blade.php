<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&G Construction Inc. | Design. Build. Deliver.</title>
    <meta name="description" content="D&G Construction Inc. delivers residential, commercial, and renovation services with a modern, reliable approach.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landingpage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
</head>
<body class="landing-page">
<div class="page-shell">
    <nav class="site-nav">
        <a class="brand" href="#top" aria-label="D&G Construction home">
            <img src="{{ asset('images/bg.png') }}" alt="D&G Construction logo">
            <span>
                <strong>D&G Construction Inc.</strong>
                <small>Design | Build | Deliver</small>
            </span>
        </a>

        <div class="nav-links">
            <a href="#services">Services</a>
            <a href="#about">About</a>
            <a href="#projects">Projects</a>
            <a href="#contact">Contact</a>
        </div>

        <a class="nav-cta" href="{{ route('login') }}">Request a Quote</a>
    </nav>

    <main>
        <section class="hero" id="top">
            <div class="hero-copy">
                <p class="eyebrow">Residential construction, commercial builds, and renovation work</p>
                <h1>Design. Build. Deliver.</h1>
                <p class="hero-lead">
                    D&G Construction Inc. delivers quality craftsmanship and reliable construction solutions from concept to completion.
                </p>

                <div class="hero-actions">
                    <a class="button button-primary" href="{{ route('login') }}">Get a Quote</a>
                    <a class="button button-secondary" href="#projects">View Our Work</a>
                </div>

                <div class="hero-stats">
                    <div class="stat-card">
                        <strong>100+</strong>
                        <span>Projects Completed</span>
                    </div>
                    <div class="stat-card">
                        <strong>15+</strong>
                        <span>Years of Experience</span>
                    </div>
                    <div class="stat-card">
                        <strong>75+</strong>
                        <span>Happy Clients</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual" aria-label="Featured construction project preview">
                <div class="hero-frame">
                    <div class="hero-image-wrap">
                        <img src="{{ asset('images/image2.jpg') }}" alt="Construction project exterior">
                        <div class="frame-overlay"></div>
                    </div>

                    <div class="floating-card floating-card-top">
                        <span class="floating-label">Current Project</span>
                        <strong>On Schedule</strong>
                        <small>Framing and site coordination in progress</small>
                    </div>

                    <div class="floating-card floating-card-bottom">
                        <span class="floating-label">Quality Focus</span>
                        <strong>Precision Work</strong>
                        <small>Clean finishes, disciplined timelines, clear updates</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="services-band" id="services">
            <div class="section-heading">
                <p class="section-kicker">What we do</p>
                <h2>Modern construction services built around clarity and delivery.</h2>
            </div>

            <div class="service-grid">
                <article class="service-card">
                    <span class="service-mark">01</span>
                    <h3>Design & Planning</h3>
                    <p>Thoughtful layouts, practical scope planning, and a clear path before work begins.</p>
                </article>
                <article class="service-card">
                    <span class="service-mark">02</span>
                    <h3>Construction</h3>
                    <p>Reliable execution for new builds with disciplined site coordination and workmanship.</p>
                </article>
                <article class="service-card">
                    <span class="service-mark">03</span>
                    <h3>Project Management</h3>
                    <p>Consistent oversight, progress visibility, and communication through every phase.</p>
                </article>
                <article class="service-card">
                    <span class="service-mark">04</span>
                    <h3>Renovations</h3>
                    <p>Refresh and transform existing spaces with careful detailing and low-disruption delivery.</p>
                </article>
                <article class="service-card">
                    <span class="service-mark">05</span>
                    <h3>Client Focused</h3>
                    <p>Direct communication and accountable service that keeps every stakeholder aligned.</p>
                </article>
            </div>
        </section>

        <section class="about-section" id="about">
            <div class="about-copy">
                <p class="section-kicker">About us</p>
                <h2>Building with integrity. Delivering excellence.</h2>
                <p>
                    At D&G Construction Inc., we take pride in our craftsmanship, attention to detail, and commitment to our clients.
                    From residential to commercial projects, we build with purpose and precision.
                </p>
                <p>
                    Our work is shaped by practical planning, transparent communication, and a steady focus on quality from the first meeting to handover.
                </p>

                <a class="text-link" href="#contact">Learn more about us <span>→</span></a>
            </div>

            <div class="about-metrics">
                <div class="metric-card">
                    <strong>100+</strong>
                    <span>Projects Completed</span>
                </div>
                <div class="metric-card">
                    <strong>15+</strong>
                    <span>Years of Experience</span>
                </div>
                <div class="metric-card">
                    <strong>75+</strong>
                    <span>Happy Clients</span>
                </div>
                <div class="metric-card metric-card-accent">
                    <strong>Serving</strong>
                    <span>Our Community with Pride</span>
                </div>
            </div>
        </section>

        <section class="projects-section" id="projects">
            <div class="section-heading section-heading-row">
                <div>
                    <p class="section-kicker">Featured projects</p>
                    <h2>Recent work that reflects the standard we bring to every site.</h2>
                </div>
                <a class="text-link" href="#contact">View all projects <span>→</span></a>
            </div>

            <div class="project-grid">
                <article class="project-card">
                    <img src="{{ asset('images/image3.jpg') }}" alt="Commercial construction project">
                    <div class="project-body">
                        <h3>Commercial Build</h3>
                        <p>Newmarket, ON</p>
                    </div>
                </article>
                <article class="project-card">
                    <img src="{{ asset('images/image2.jpg') }}" alt="Modern home build">
                    <div class="project-body">
                        <h3>Custom Home Build</h3>
                        <p>Barrie, ON</p>
                    </div>
                </article>
                <article class="project-card">
                    <img src="{{ asset('images/image3.jpg') }}" alt="Renovation site progress">
                    <div class="project-body">
                        <h3>Full Home Renovation</h3>
                        <p>Innisfil, ON</p>
                    </div>
                </article>
                <article class="project-card">
                    <img src="{{ asset('images/image2.jpg') }}" alt="Interior renovation project">
                    <div class="project-body">
                        <h3>Interior Renovation</h3>
                        <p>Aurora, ON</p>
                    </div>
                </article>
            </div>
        </section>

        <section class="testimonial-strip">
            <div class="testimonial-quote">
                <span class="quote-mark">“</span>
                <p>D&G Construction Inc. exceeded our expectations. Their team was professional, reliable, and the quality of work is outstanding.</p>
            </div>
            <div class="testimonial-author">
                <strong>Mark & Sarah T.</strong>
                <span>Happy Homeowners</span>
            </div>
        </section>

        <section class="cta-section" id="contact">
            <div>
                <p class="section-kicker">Let's build something great together</p>
                <h2>From design to construction, we are ready to bring your vision to life.</h2>
            </div>
            <a class="button button-light" href="{{ route('login') }}">Get in Touch</a>
        </section>
    </main>

    <footer class="footer">
        <div class="footer-brand">
            <img src="{{ asset('images/bg.png') }}" alt="D&G Construction logo">
            <div>
                <strong>D&G Construction Inc.</strong>
                <p>Designing and building spaces that stand the test of time.</p>
            </div>
        </div>

        <div class="footer-grid">
            <div>
                <h4>Quick Links</h4>
                <a href="#about">About Us</a>
                <a href="#services">Services</a>
                <a href="#projects">Projects</a>
                <a href="#contact">Contact</a>
            </div>
            <div>
                <h4>Services</h4>
                <a href="#services">Custom Homes</a>
                <a href="#services">Renovations</a>
                <a href="#services">Commercial</a>
                <a href="#services">Project Management</a>
            </div>
            <div>
                <h4>Contact Us</h4>
                <span>(705) 123-4567</span>
                <span>info@dgconstruction.ca</span>
                <span>Barrie, Ontario</span>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; 2026 D&G Construction Inc. All Rights Reserved.</span>
        </div>
    </footer>
</div>
</body>
</html>