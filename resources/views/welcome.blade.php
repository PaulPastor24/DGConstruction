<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&G ConstructMonitor - Construction Project Monitoring System</title>
    <meta name="description" content="Construction project monitoring for workforce, progress, and site operations in one unified platform.">
    
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landingpage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>

<nav class="navbar">
    <div class="navbar-logo" onclick="window.scrollTo({ top: 0, behavior: 'smooth' }); return false;">
        <div class="navbar-logo-icon">
            <img src="{{ asset('images/bg.png') }}" alt="D&G Construction logo">
        </div>
    </div>
    <div class="navbar-nav">
        <a href="#features">Features</a>
        <a href="#about">About</a>
        <button type="button" class="btn-signin" onclick="window.location.href='{{ route('login') }}'">
            Sign In
        </button>
    </div>
</nav>

<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">Construction Operations Platform</div>
        <h1 class="hero-title">Manage every project with one clear source of truth.</h1>
        <p class="hero-subtitle">D&G ConstructMonitor brings together progress tracking, workforce attendance, phase reporting, and site visibility in a single professional dashboard built for modern project teams.</p>

        <div class="hero-cta-group">
            <a href="{{ url('/login') }}" class="btn-primary" style="text-decoration: none;">Open Dashboard</a>
            <button class="btn-secondary" onclick="document.querySelector('#about').scrollIntoView({ behavior: 'smooth' });">Explore Platform</button>
        </div>

        <div class="hero-stats">
            <div class="stat">
                <div class="stat-value">7</div>
                <div class="stat-label">Active Projects</div>
            </div>
            <div class="stat">
                <div class="stat-value">184</div>
                <div class="stat-label">Workforce Members</div>
            </div>
            <div class="stat">
                <div class="stat-value">4.93M</div>
                <div class="stat-label">Inventory Value</div>
            </div>
        </div>
    </div>
</section>

<section class="features-section" id="features">
    <div class="section-header">
        <h2 class="section-title">Built for construction teams</h2>
        <p class="section-subtitle">Everything needed to coordinate on-site activity, track performance, and keep stakeholders aligned.</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <h3 class="feature-title">Live Project Dashboards</h3>
            <p class="feature-description">Track progress, milestones, and site status with a clean operational view designed for faster decisions.</p>
        </div>
        <div class="feature-card">
            <h3 class="feature-title">Workforce Management</h3>
            <p class="feature-description">Capture attendance, verify workers, and keep a reliable log of daily on-site activity.</p>
        </div>
        <div class="feature-card">
            <h3 class="feature-title">Material Tracking</h3>
            <p class="feature-description">Monitor inventory, deliveries, and supply status to reduce delays and keep work moving.</p>
        </div>
        <div class="feature-card">
            <h3 class="feature-title">Phase Management</h3>
            <p class="feature-description">Organize project phases, report progress, and review completion updates in one place.</p>
        </div>
        <div class="feature-card">
            <h3 class="feature-title">Alert System</h3>
            <p class="feature-description">Surface attendance issues, project delays, and milestone changes before they become bottlenecks.</p>
        </div>
        <div class="feature-card">
            <h3 class="feature-title">Role-Based Access</h3>
            <p class="feature-description">Give admins, supervisors, and clients the right level of visibility for their responsibilities.</p>
        </div>
    </div>
</section>

<section class="about-section" id="about">
    <div class="about-content">
        <div class="about-grid">
            <div>
                <h2>About D&G ConstructMonitor</h2>
                <p>D&G Development Corporation uses ConstructMonitor to streamline project coordination across offices, job sites, and client updates. The platform is designed to improve visibility, accountability, and delivery quality.</p>
                <p>It supports operational teams with a professional workflow for attendance, progress review, and field reporting without adding unnecessary complexity.</p>

                <ul class="about-list">
                    <li>Centralized project oversight</li>
                    <li>Real-time workforce and attendance tracking</li>
                    <li>Structured phase and milestone management</li>
                    <li>Clear reporting for supervisors and clients</li>
                    <li>Built for reliable day-to-day operations</li>
                </ul>
            </div>

            <div class="about-metrics">
                <div class="metric-item">
                    <div class="metric-number">50+</div>
                    <div class="metric-text">Completed Projects</div>
                </div>
                <div class="metric-item">
                    <div class="metric-number">2500+</div>
                    <div class="metric-text">Workforce Capacity</div>
                </div>
                <div class="metric-item">
                    <div class="metric-number">₱15B+</div>
                    <div class="metric-text">Total Project Value</div>
                </div>
                <div class="metric-item">
                    <div class="metric-number">98%</div>
                    <div class="metric-text">On-Time Delivery</div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-col">
            <h4>Company</h4>
            <ul>
                <li><a href="#about">About Us</a></li>
                <li><a href="#features">Projects</a></li>
                <li><a href="#features">Capabilities</a></li>
                <li><a href="#about">News</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Product</h4>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#about">Documentation</a></li>
                <li><a href="#features">API</a></li>
                <li><a href="#about">Support</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Security</a></li>
                <li><a href="#">Compliance</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Contact</h4>
            <ul>
                <li><a href="mailto:info@dg-corp.ph">info@dg-corp.ph</a></li>
                <li><a href="tel:+6321234567">+63 (2) 1234-567</a></li>
                <li><a href="#">Support Center</a></li>
                <li><a href="#">Schedule Demo</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2026 D&G Development Corporation. All rights reserved.</p>
        <p>ConstructMonitor - Construction Project Monitoring System</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>