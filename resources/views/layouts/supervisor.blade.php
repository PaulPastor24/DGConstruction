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
            padding: 1rem 1rem 1.25rem;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            background: linear-gradient(180deg, #fafafa 0%, var(--supervisor-bg) 100%);
        }
        .page-frame { display: flex; flex-direction: column; gap: 1rem; max-width: 1500px; margin: 0 auto; }
        .page-card { background: var(--supervisor-surface); border: 1px solid var(--supervisor-border); border-radius: 18px; box-shadow: 0 8px 22px rgba(9,96,86,0.05); }
        .page-card-body { padding: 1rem; }
        .page-hero { padding: 1.1rem 1.1rem 1rem; display: flex; flex-direction: column; gap: 0.8rem; }
        .eyebrow { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.16em; color: var(--supervisor-muted); text-transform: uppercase; }
        .page-title { font-family: 'Syne', sans-serif; font-size: 1.55rem; font-weight: 700; color: var(--supervisor-primary); margin-bottom: 0.2rem; line-height: 1.1; }
        .page-subtitle { color: var(--supervisor-muted); font-size: 0.95rem; line-height: 1.45; }
        .section-card { background: var(--supervisor-surface); border: 1px solid var(--supervisor-border); border-radius: 18px; box-shadow: 0 10px 24px rgba(9,96,86,0.05); height: auto; display: flex; flex-direction: column; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .section-card:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(9,96,86,0.08); }
        .section-card-body { padding: 0.9rem 0.95rem; display: flex; flex-direction: column; gap: 0.6rem; }
        .section-card-body > :last-child { margin-bottom: 0 !important; }
        .section-card-body > :last-child > :last-child { margin-bottom: 0 !important; }
        .dashboard-card-grid { align-items: start; }
        .dashboard-card-grid > [class*="col-"] { display: flex; }
        .dashboard-card-grid > [class*="col-"] > .section-card { width: 100%; height: auto; }
        .dashboard-inline-item { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.28rem 0; border-bottom: none; }
        .dashboard-inline-item + .dashboard-inline-item { padding-top: 0.5rem; margin-top: 0.35rem; border-top: 1px solid rgba(9,96,86,0.06); }
        .dashboard-surface { background: linear-gradient(135deg, #fcfdfc 0%, #f4f8f6 100%); border: 1px solid rgba(9,96,86,0.08); border-radius: 14px; padding: 0.75rem 0.8rem; }
        .dashboard-key-value { display: flex; align-items: flex-start; gap: 0.65rem; padding: 0.7rem; background: linear-gradient(135deg, #fcfdfc 0%, #f4f8f6 100%); border: 1px solid rgba(9,96,86,0.08); border-radius: 14px; min-height: 72px; }
        .dashboard-summary-pill { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.7rem; border-radius: 999px; background: rgba(9,96,86,0.06); color: var(--supervisor-primary); font-size: 0.82rem; font-weight: 600; }
        .dashboard-summary-highlight { padding: 0.8rem 0.9rem; border-radius: 14px; background: linear-gradient(135deg, #f8fcf9 0%, #eef8f2 100%); border: 1px solid rgba(9,96,86,0.08); }
        .dashboard-kpi { font-family: 'Syne', sans-serif; font-size: 1.8rem; font-weight: 700; color: var(--supervisor-primary); line-height: 1.05; }
        .dashboard-info-stack { display: flex; flex-direction: column; gap: 0.25rem; }
        .dashboard-action-btn { width: fit-content; align-self: flex-start; display: inline-flex; align-items: center; justify-content: center; }
        .dashboard-empty-state { display: flex; align-items: center; gap: 0.7rem; padding: 0.85rem 0.95rem; border-radius: 14px; background: #fafcfb; border: 1px dashed rgba(9,96,86,0.16); color: var(--supervisor-muted); }
        .dashboard-empty-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: rgba(9,96,86,0.08); color: var(--supervisor-primary); flex-shrink: 0; }
        .dashboard-card-grid.dashboard-row-equal > [class*="col-"] { display: flex; }
        .dashboard-card-grid.dashboard-row-equal > [class*="col-"] > .section-card { width: 100%; height: 100%; }
        .stat-card { background: linear-gradient(135deg, #ffffff 0%, #f6fcf8 100%); border: 1px solid rgba(9,96,86,0.08); border-left: 4px solid var(--supervisor-accent); border-radius: 16px; padding: 0.9rem 0.95rem; height: 100%; display: flex; flex-direction: column; justify-content: center; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: 0 6px 16px rgba(9,96,86,0.04); }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(9,96,86,0.08); }
        .stat-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--supervisor-muted); }
        .stat-value { font-family: 'Syne', sans-serif; font-size: 1.8rem; font-weight: 700; color: var(--supervisor-primary); line-height: 1.1; }
        .stat-meta { font-size: 0.85rem; color: var(--supervisor-muted); }
        .btn-primary-soft { background: var(--supervisor-primary); color: #fff; border: none; border-radius: 12px; padding: 0.8rem 1.15rem; font-weight: 700; box-shadow: 0 8px 18px rgba(9,96,86,0.16); transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease; }
        .btn-primary-soft:hover { background: var(--supervisor-primary-deep); color: #fff; transform: translateY(-1px); box-shadow: 0 10px 20px rgba(9,96,86,0.18); }
        .btn-outline-soft { background: transparent; border: 1px solid rgba(9,96,86,0.2); color: var(--supervisor-primary); border-radius: 12px; padding: 0.8rem 1.15rem; font-weight: 700; transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease; }
        .btn-outline-soft:hover { background: rgba(9,96,86,0.08); color: var(--supervisor-primary); transform: translateY(-1px); box-shadow: 0 8px 16px rgba(9,96,86,0.08); }
        .empty-state { padding: 0.9rem 1rem; text-align: center; color: var(--supervisor-muted); background: #fafafa; border: 1px dashed rgba(9,96,86,0.16); border-radius: 12px; }
        .progress-track { height: 8px; border-radius: 999px; background: #ebf2ee; overflow: hidden; position: relative; }
        .progress-track::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,0.18), rgba(255,255,255,0)); animation: shimmer 2.4s ease-in-out infinite; }
        .progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--supervisor-secondary), var(--supervisor-accent)); transition: width 0.8s ease; }
        .compact-grid { display: grid; gap: 0.6rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .compact-item { background: linear-gradient(135deg, #fcfdfc 0%, #f4f8f6 100%); border: 1px solid rgba(9,96,86,0.08); border-radius: 12px; padding: 0.6rem 0.7rem; }
        .compact-label { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--supervisor-muted); margin-bottom: 0.2rem; }
        .compact-value { font-size: 0.9rem; font-weight: 700; color: var(--supervisor-text); line-height: 1.35; }
        .activity-item { display: flex; align-items: flex-start; gap: 0.65rem; padding: 0.6rem 0.7rem; background: #fcfdfd; border: 1px solid rgba(9,96,86,0.08); border-radius: 12px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .activity-item:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(9,96,86,0.06); }
        .dashboard-icon { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; background: var(--supervisor-soft); color: var(--supervisor-primary); flex-shrink: 0; }
        .activity-icon { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--supervisor-soft); color: var(--supervisor-primary); flex-shrink: 0; }
        .activity-group { margin-bottom: 0.7rem; }
        .activity-group-label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--supervisor-muted); margin-bottom: 0.45rem; }
        .activity-timeline { display: flex; flex-direction: column; gap: 0.5rem; }
        .dashboard-stepper { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.2rem; }
        .dashboard-layout-row { align-items: flex-start; }
        .dashboard-layout-row > [class*="col-"] { display: flex; align-items: flex-start; }
        .dashboard-layout-row > [class*="col-"] > .section-card { width: 100%; height: auto; align-self: flex-start; }
        .dashboard-step-item { position: relative; display: flex; gap: 0.65rem; align-items: flex-start; padding: 0.6rem 0.7rem; border-radius: 12px; border: 1px solid rgba(9,96,86,0.08); background: #fcfdfd; }
        .dashboard-step-item.current { border-color: rgba(9,96,86,0.2); box-shadow: 0 8px 18px rgba(9,96,86,0.06); }
        .dashboard-step-item.upcoming { opacity: 0.85; }
        .dashboard-step-dot { width: 28px; height: 28px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0; border: 2px solid rgba(9,96,86,0.16); background: #fff; color: var(--supervisor-primary); }
        .dashboard-step-item.completed .dashboard-step-dot { background: var(--supervisor-secondary); border-color: var(--supervisor-secondary); color: #fff; }
        .dashboard-step-item.current .dashboard-step-dot { background: var(--supervisor-primary); border-color: var(--supervisor-primary); color: #fff; }
        .dashboard-step-item.upcoming .dashboard-step-dot { background: #f6f8f8; color: var(--supervisor-muted); }
        .dashboard-step-title { font-size: 0.94rem; font-weight: 700; color: var(--supervisor-text); }
        .dashboard-step-meta { font-size: 0.8rem; color: var(--supervisor-muted); }
        .badge-soft { background: rgba(9,96,86,0.08); color: var(--supervisor-primary); }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.38); z-index: 1040; }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @media (max-width: 768px) {
            .compact-grid { grid-template-columns: 1fr; }
        }

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

