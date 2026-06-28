<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Supervisor Dashboard D&G Construction Monitor')</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --supervisor-primary: #096056;
            --supervisor-primary-deep: #064a41;
            --supervisor-secondary: #4DA078;
            --supervisor-accent: #82DB72;
            --supervisor-highlight: #82DB72;
            --supervisor-bg: #F5F5F5;
            --supervisor-surface: #FFFFFF;
            --supervisor-border: rgba(9, 96, 86, 0.12);
            --supervisor-muted: #6b7280;
            --supervisor-text: #373737;
            --supervisor-soft: #eef8f2;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--supervisor-bg);
            color: var(--supervisor-text);
        }

        .heading-syne {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
        }

        .app {
            display: flex;
            height: 100vh;
            min-height: 100vh;
            overflow: hidden;
            background: linear-gradient(180deg, #fafafa 0%, var(--supervisor-bg) 100%);
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(145deg, var(--supervisor-primary-deep) 0%, var(--supervisor-primary) 100%);
            color: #fff;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 18px 0 40px rgba(9, 96, 86, 0.18);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.1rem 0.95rem 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .sidebar-logo {
            padding: 0.4rem 0.2rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .logo-badge { display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.35rem; }
        .logo-icon {
            width: 46px; height: 46px; display: flex; align-items: center; justify-content: center; border-radius: 14px;
            background: linear-gradient(135deg, var(--supervisor-accent), #b8efb0); color: var(--supervisor-primary-deep); font-weight: 800;
            box-shadow: 0 10px 24px rgba(130, 219, 114, 0.24);
        }
        .logo-text { font-family: 'Syne', sans-serif; font-size: 0.92rem; text-transform: uppercase; letter-spacing: 0.14em; font-weight: 700; }
        .logo-sub { font-size: 0.78rem; color: rgba(255,255,255,0.78); padding-left: 3.25rem; }

        .sidebar-nav { display: flex; flex-direction: column; gap: 0.2rem; padding: 0.2rem 0 0; }
        .nav-section-label { padding: 0.8rem 0.85rem 0.4rem; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.12em; color: rgba(255,255,255,0.7); }
        .nav-item {
            display: flex; align-items: center; gap: 0.85rem; padding: 0.82rem 0.9rem; color: rgba(255,255,255,0.95);
            text-decoration: none; transition: all 0.2s ease; border-left: 3px solid transparent; border-radius: 14px; font-weight: 500;
        }
        .nav-item i { width: 18px; text-align: center; font-size: 0.96rem; opacity: 0.95; }
        .nav-item:hover { background: rgba(255,255,255,0.12); color: #fff; transform: translateX(2px); }
        .nav-item.active { background: rgba(255,255,255,0.16); color: #fff; border-left-color: var(--supervisor-accent); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08); font-weight: 600; }

        .sidebar-footer { margin-top: auto; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.12); }
        .user-card { display: flex; align-items: center; gap: 0.8rem; background: rgba(255,255,255,0.08); border-radius: 16px; padding: 0.85rem; box-shadow: inset 0 1px 0 rgba(255,255,255,0.08); }
        .user-avatar { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 999px; background: var(--supervisor-accent); color: var(--supervisor-primary-deep); font-size: 1rem; font-weight: 700; }
        .user-name { font-size: 0.92rem; font-weight: 700; }
        .user-role { font-size: 0.76rem; color: rgba(255,255,255,0.72); }

        .main { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; overflow: hidden; }
        .topbar {
            display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.95rem 1.25rem; background: var(--supervisor-surface);
            border-bottom: 1px solid var(--supervisor-border); position: sticky; top: 0; z-index: 20; box-shadow: 0 1px 0 rgba(0,0,0,0.02);
        }
        .topbar-left { display: flex; align-items: center; gap: 0.8rem; min-width: 0; }
        .menu-button { display: none; width: 40px; height: 40px; border-radius: 10px; border: 1px solid var(--supervisor-border); background: #fff; color: var(--supervisor-primary); align-items: center; justify-content: center; }
        .topbar-breadcrumb { font-size: 0.95rem; font-weight: 700; color: var(--supervisor-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .topbar-right { display: flex; align-items: center; gap: 0.65rem; }
        .topbar-icon { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; background: transparent; border: none; color: var(--supervisor-primary); border-radius: 999px; transition: all 0.2s ease; }
        .topbar-icon:hover { background: rgba(9,96,86,0.08); }
        .topbar-date { font-size: 0.85rem; color: var(--supervisor-muted); min-width: 150px; text-align: right; }

        .content-shell {
            flex: 1;
            padding: 1.5rem 1.5rem 2rem;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            background: linear-gradient(180deg, #fafafa 0%, var(--supervisor-bg) 100%);
        }
        .page-frame { display: flex; flex-direction: column; gap: 1.25rem; max-width: 1500px; margin: 0 auto; }
        .page-card { background: var(--supervisor-surface); border: 1px solid var(--supervisor-border); border-radius: 22px; box-shadow: 0 10px 32px rgba(9,96,86,0.06); }
        .page-card-body { padding: 1.5rem; }
        .page-hero { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        .eyebrow { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.16em; color: var(--supervisor-muted); text-transform: uppercase; }
        .page-title { font-family: 'Syne', sans-serif; font-size: 1.7rem; font-weight: 700; color: var(--supervisor-primary); margin-bottom: 0.25rem; }
        .page-subtitle { color: var(--supervisor-muted); font-size: 0.95rem; line-height: 1.6; }
        .section-card { background: var(--supervisor-surface); border: 1px solid var(--supervisor-border); border-radius: 18px; box-shadow: 0 10px 24px rgba(9,96,86,0.05); }
        .section-card-body { padding: 1.25rem; }
        .stat-card { background: linear-gradient(135deg, #ffffff 0%, #f6fcf8 100%); border: 1px solid rgba(9,96,86,0.08); border-left: 4px solid var(--supervisor-accent); border-radius: 18px; padding: 1rem 1rem 1.1rem; height: 100%; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: 0 8px 20px rgba(9,96,86,0.04); }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(9,96,86,0.08); }
        .stat-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--supervisor-muted); }
        .stat-value { font-family: 'Syne', sans-serif; font-size: 1.75rem; font-weight: 700; color: var(--supervisor-primary); line-height: 1.1; }
        .stat-meta { font-size: 0.85rem; color: var(--supervisor-muted); }
        .btn-primary-soft { background: var(--supervisor-primary); color: #fff; border: none; border-radius: 12px; padding: 0.8rem 1.15rem; font-weight: 700; box-shadow: 0 8px 18px rgba(9,96,86,0.16); }
        .btn-primary-soft:hover { background: var(--supervisor-primary-deep); color: #fff; }
        .btn-outline-soft { background: transparent; border: 1px solid rgba(9,96,86,0.2); color: var(--supervisor-primary); border-radius: 12px; padding: 0.8rem 1.15rem; font-weight: 700; }
        .btn-outline-soft:hover { background: rgba(9,96,86,0.08); color: var(--supervisor-primary); }
        .empty-state { padding: 2rem; text-align: center; color: var(--supervisor-muted); background: #fafafa; border: 1px dashed rgba(9,96,86,0.16); border-radius: 16px; }
        .progress-track { height: 10px; border-radius: 999px; background: #ebf2ee; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--supervisor-secondary), var(--supervisor-accent)); }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.38); z-index: 1040; }

        @media (max-width: 1024px) {
            .app { display: block; }
            .sidebar {
                position: fixed; left: 0; top: 0; bottom: 0; width: 280px; z-index: 1050; transform: translateX(-100%); transition: transform 0.25s ease;
            }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .menu-button { display: inline-flex; }
        }

        @media (max-width: 768px) {
            .content-shell { padding: 1rem; }
            .page-card-body, .page-hero { padding: 1rem; }
            .topbar { padding: 0.85rem 0.95rem; }
            .topbar-date { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    @include('partials.supervisor.sidebar')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main">
        @include('partials.supervisor.topbar')
        <main class="content-shell">
            <div class="page-frame">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('supervisorSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function updateToggleVisibility() {
            if (window.innerWidth <= 1024) {
                if (sidebarToggle) sidebarToggle.style.display = 'inline-flex';
            } else {
                if (sidebarToggle) sidebarToggle.style.display = 'none';
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
            }
        }

        updateToggleVisibility();
        window.addEventListener('resize', updateToggleVisibility);

        if (sidebarToggle && sidebar && overlay) {
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

            document.querySelectorAll('.sidebar .nav-item').forEach(function(item) {
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
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-badge">
                <div class="logo-icon">SV</div>
                <div>
                    <div class="logo-text">Supervisor</div>
                </div>
            </div>
            <div class="logo-sub">D&G Construction</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Operations</div>
            <a href="{{ route('supervisor.dashboard') }}" class="nav-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('supervisor.timeline') }}" class="nav-item {{ request()->routeIs('supervisor.timeline') ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i> Project Timeline
            </a>
            <a href="{{ route('supervisor.timeline') }}" class="nav-item {{ request()->routeIs('supervisor.timeline') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Construction Phases
            </a>
            <a href="{{ route('supervisor.attendance') }}" class="nav-item {{ request()->routeIs('supervisor.attendance*') ? 'active' : '' }}">
                <i class="bi bi-person-check"></i> Attendance
            </a>
            <a href="{{ route('supervisor.materials') }}" class="nav-item {{ request()->routeIs('supervisor.materials*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Materials
            </a>
            <a href="{{ route('supervisor.reports') }}" class="nav-item {{ request()->routeIs('supervisor.reports*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Accomplishment Reports
            </a>

            <div class="nav-section-label">Account</div>
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
                    <div class="user-name">{{ Auth::user()->name ?? 'Supervisor' }}</div>
                    <div class="user-role">{{ Auth::user()->title ?? 'Site Supervisor' }}</div>
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
                <div class="topbar-title" id="pageTitle">@yield('page_title', 'Supervisor Dashboard')</div>
            </div>
            <div style="font-size:12px; color:var(--supervisor-muted);">{{ now()->format('D, d M Y') }}</div>
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
