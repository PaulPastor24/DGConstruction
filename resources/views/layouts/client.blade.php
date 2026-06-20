<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Client Portal D&G Construction Monitor')</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --client-primary: #003366;
            --client-secondary: #336699;
            --client-accent: #6699CC;
            --client-light: #99CCFF;
            --client-bg: #CCFFFF;
            --client-surface: #F7FBFF;
            --client-border: #D7E7F5;
            --client-muted: #5b6b7f;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(180deg, #f4fbff 0%, var(--client-bg) 100%);
            color: #15304d;
        }

        .heading-syne {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #0b335f 0%, var(--client-primary) 100%);
            color: #fff;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 18px rgba(0, 51, 102, 0.18);
        }

        .sidebar-logo {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .logo-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.35rem;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: var(--client-light);
            color: #0f2c52;
            font-weight: 800;
        }

        .logo-text {
            font-family: 'Syne', sans-serif;
            font-size: 0.88rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 700;
        }

        .logo-sub {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.78);
            padding-left: 3rem;
        }

        .sidebar-nav { padding: 0.8rem 0; }

        .nav-section-label {
            padding: 0.75rem 1rem 0.4rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.62);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
            color: rgba(255, 255, 255, 0.88);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(153, 204, 255, 0.1);
            color: #fff;
            border-left-color: var(--client-light);
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 0.75rem;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--client-light);
            color: #0f2c52;
            font-size: 1rem;
        }

        .user-name {
            font-size: 0.92rem;
            font-weight: 700;
        }

        .user-role {
            font-size: 0.76rem;
            color: rgba(255, 255, 255, 0.72);
        }

        .main { flex: 1; }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.9rem 1rem;
            background: var(--client-surface);
            border-bottom: 1px solid var(--client-border);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .topbar-title {
            font-weight: 700;
            color: var(--client-primary);
        }

        #sidebarToggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--client-border);
            background: #fff;
            color: var(--client-primary);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.38);
            z-index: 1040;
        }

        .content { padding: 1rem; }

        @media (max-width: 1024px) {
            .app { display: block; }
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: 280px;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            #sidebarToggle { display: flex; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-badge">
                <div class="logo-icon">CL</div>
                <div>
                    <div class="logo-text">Client</div>
                </div>
            </div>
            <div class="logo-sub">D&G Construction</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('client.timeline') }}" class="nav-item {{ request()->routeIs('client.timeline') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> My Projects
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <a href="#" class="nav-item" onclick="event.preventDefault(); if(confirm('Are you sure you want to sign out?')) document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left"></i> Sign Out
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><i class="bi bi-person-fill"></i></div>
                <div>
                    <div class="user-name">{{ Auth::user()->name ?? 'Client' }}</div>
                    <div class="user-role">{{ Auth::user()->title ?? 'Project Client' }}</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main">
        <div class="topbar">
            <div style="display:flex; align-items:center; gap:.75rem; flex:1; min-width:0;">
                <button id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-title" id="pageTitle">@yield('page_title', 'Client Dashboard')</div>
            </div>
            <div style="font-size:12px; color:var(--client-muted);">{{ now()->format('D, d M Y') }}</div>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function updateToggleVisibility() {
            if (window.innerWidth <= 1024) {
                sidebarToggle.style.display = 'flex';
            } else {
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        }

        updateToggleVisibility();
        window.addEventListener('resize', updateToggleVisibility);

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            const navItems = document.querySelectorAll('.sidebar .nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
        }
    });
</script>
@stack('scripts')
</body>
</html>
