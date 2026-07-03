<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Client Portal D&G Construction Monitor')</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --sidebar-bg: #2a4028;
            --sidebar-active: #365233;
            --sidebar-text: #ffffff;
            --sidebar-text-muted: #cbd5d2;

            --bg-main: #fcfdfc;
            --surface-card: #ffffff;
            --border-color: #e2ebe4;
            --text-primary: #1e241e;
            --text-muted: #626e61;

            --brand-green: #2a4028;
            --brand-mint: #f4f7f1;
            --brand-yellow-green: #8fae85;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR CONTAINER CONTROL --- */
        .sidebar {
            width: 260px;
            background: linear-gradient(145deg, var(--sidebar-bg) 0%, var(--sidebar-active) 100%);
            color: var(--sidebar-text);
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1050;
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-brand {
            padding: 2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-icon, .sidebar-logo-img {
            width: 56px;
            height: 56px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--surface-card);
            overflow: hidden;
            padding: 0;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, 0.08);
        }

        .sidebar-logo-img {
            object-fit: contain;
            border-radius: 50%;
            background: var(--surface-card);
        }

        .sidebar-logo-img img {
            width: 90%;
            height: 90%;
            object-fit: cover;
        }

        .brand-icon {
            font-size: 1.4rem;
            color: var(--brand-yellow-green);
            line-height: 1;
        }

        .brand-text h5 {
            font-size: 0.95rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #ffffff;
        }

        .brand-text span {
            font-size: 0.72rem;
            color: var(--sidebar-text-muted);
            display: block;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 0 0.75rem;
            flex-grow: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.8rem 1.15rem;
            color: var(--sidebar-text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            border-radius: 12px;
            margin-bottom: 0.35rem;
            transition: all 0.2s ease;
        }

        .nav-item i {
            font-size: 1.1rem;
        }

        .nav-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }

        .sidebar-footer {
            padding: 1.25rem 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .user-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.08);
            padding: 0.75rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            color: #ffffff;
            margin-bottom: 0.75rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar-circle {
            width: 34px;
            height: 34px;
            background-color: var(--sidebar-active);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #ffffff;
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.6rem 1.15rem;
            color: var(--sidebar-text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            border-radius: 10px;
        }
        .logout-link:hover { 
            color: #ffffff; 
            background: rgba(255, 255, 255, 0.12);
        }

        .swal-actions-reverse {
            display: flex !important;
            flex-direction: row-reverse !important;
            justify-content: center !important;
            gap: 0.6rem !important;
        }

        .swal-confirm-btn,
        .swal-cancel-btn {
            min-width: 110px !important;
            border-radius: 10px !important;
            padding: 0.7rem 1rem !important;
            font-weight: 600 !important;
        }

        .swal-confirm-btn {
            background-color: var(--brand-green) !important;
            border: 1px solid var(--brand-green) !important;
            color: #ffffff !important;
        }

        .swal-cancel-btn {
            background-color: var(--surface-card) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
        }

        /* --- MAIN INTERFACE WORKSPACE --- */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .topbar {
            display: none;
        }

        .welcome-msg h2 {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0 0 0.15rem 0;
            color: var(--text-primary);
        }

        .welcome-msg p {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            justify-content: space-between;
            width: 100%;
        }

        .topbar-actions-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
            min-width: 0;
        }

        .topbar-action-icons {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .topbar-actions .avatar-circle {
            width: 38px;
            height: 38px;
            font-size: 0.85rem;
        }

        .notification-bell {
            width: 40px;
            height: 40px;
        }

        .date-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--text-primary);
            background: var(--surface-card);
            padding: 0.45rem 0.8rem;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .notification-bell {
            position: relative;
            font-size: 1.05rem;
            color: var(--text-primary);
            cursor: pointer;
            background: var(--surface-card);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 9px;
            background: var(--brand-yellow-green);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            box-shadow: 0 0 0 2px #ffffff;
        }

        .notification-popup {
            position: absolute;
            top: 56px;
            right: 0;
            width: 320px;
            background: var(--surface-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
            z-index: 1055;
            overflow: hidden;
            display: none;
        }

        .notification-popup.show {
            display: block;
        }

        .notification-popup-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(42, 64, 40, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .notification-popup-header h6 {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .notification-popup-list {
            max-height: 320px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 0.95rem 1.25rem;
            border-bottom: 1px solid rgba(42, 64, 40, 0.06);
            transition: background 0.15s ease;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: var(--brand-mint);
        }

        .notification-item-title {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            color: var(--text-primary);
        }

        .notification-item-text {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin: 0;
        }

        .notification-item-time {
            display: block;
            margin-top: 0.55rem;
            font-size: 0.75rem;
            color: rgba(98, 110, 97, 0.8);
        }

        .content {
            padding: 1.35rem 2.25rem 2.25rem 2.25rem;
        }

        .topbar {
            display: none;
        }

        .welcome-msg {
            min-width: 220px;
            flex: 1;
        }

        .notification-popup {
            right: 1.5rem;
        }

        .page-header-compact {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            margin: 0 0 1.2rem;
            padding: 0.2rem 0 0.7rem;
        }

        .page-header-copy {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            min-width: 0;
        }

        .page-header-kicker {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .page-header-title {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.05;
            margin: 0;
            color: var(--brand-green);
        }

        .page-header-subtitle {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .page-header-tools {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .dashboard-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.15rem 0 0.8rem;
            margin-bottom: 0.2rem;
        }

        .dashboard-page-heading {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .dashboard-page-eyebrow {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .dashboard-page-title {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.05;
            margin: 0;
            color: var(--brand-green);
        }

        .dashboard-page-description {
            margin: 0.2rem 0 0;
            font-size: 0.92rem;
            font-weight: 500;
            color: var(--text-muted);
            max-width: 420px;
        }

        .dashboard-page-tools {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .dashboard-date-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.6rem 0.9rem;
            border: 1px solid var(--border-color);
            background: var(--surface-card);
            border-radius: 14px;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-primary);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .dashboard-date-pill i {
            color: var(--brand-green);
        }

        .dashboard-notification-button {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            border: 1px solid var(--border-color);
            background: var(--surface-card);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
        }

        .dashboard-notification-button:hover {
            background: var(--brand-mint);
        }

        .global-mobile-nav {
            display: none;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            padding: 0 1rem;
            background: var(--surface-card);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 1px 0 rgba(226, 232, 240, 0.55);
            position: sticky;
            top: 0;
            z-index: 1045;
        }

        #sidebarToggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: none;
            background: transparent;
            padding: 0;
            border-radius: 12px;
            font-size: 1.45rem;
            line-height: 1;
            color: var(--text-primary);
        }

        #mobileNotificationBell {
            display: none;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: 1px solid var(--border-color);
            background: var(--surface-card);
            border-radius: 12px;
            font-size: 1.2rem;
            color: var(--text-primary);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1040;
            backdrop-filter: blur(2px);
        }

        @media (max-width: 991.98px) {
            .global-mobile-nav {
                display: flex;
            }
            #sidebarToggle {
                display: inline-flex;
            }
            #mobileNotificationBell {
                display: inline-flex;
            }
            .dashboard-page-tools .dashboard-notification-button {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .content {
                padding: 0 1rem 1rem 1rem;
            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                left: 0;
                transform: translateX(-100%);
                width: 100%;
                max-width: 320px;
            }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            #sidebarToggle { display: inline-flex; }
            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.9rem 1.25rem;
                margin: 0.5rem 0 0.75rem;
            }
            .content { padding: 0 1.5rem 1.5rem 1.5rem; }
            .notification-popup { right: 1.5rem; }
        }

        @media (max-width: 768px) {
            .sidebar-brand { padding: 1.5rem 1rem; }
            .brand-text h5 { font-size: 0.95rem; }
            .brand-text span { font-size: 0.72rem; }
            .topbar { padding: 1rem; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .welcome-msg { width: 100%; }
            .welcome-msg h2 { font-size: 1.15rem; }
            .welcome-msg p { font-size: 0.8rem; }
            .date-badge { width: 100%; justify-content: center; }
            .topbar-actions { width: 100%; gap: 0.75rem; justify-content: space-between; }
            .topbar-actions-left { flex: 1; }
            .topbar-action-icons { justify-content: flex-end; }
            .notification-popup { width: calc(100vw - 3rem); right: 0.75rem; }
            .content { padding: 0 1rem 1rem 1rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="app">
    
    <aside class="sidebar" id="appSidebar">
        <div>
            <div class="sidebar-brand">
                <div class="sidebar-logo-img">
                    <img src="{{ asset('images/D&G.png') }}" alt="D&G Construction logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div class="brand-text">
                    <h5>D&G Construction</h5>
                    <span>Client Tracking Portal</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('client.dashboard') }}" class="nav-item {{ Request::routeIs('client.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
                <a href="{{ route('client.myprojects') }}" class="nav-item {{ Request::routeIs('client.myprojects') ? 'active' : '' }}">
                    <i class="bi bi-folder-fill"></i> My Projects
                </a>
                <a href="{{ route('client.timeline') }}" class="nav-item {{ Request::routeIs('client.timeline') ? 'active' : '' }}">
                    <i class="bi bi-calendar3-event-fill"></i> Timeline
                </a>
                <a href="{{ route('client.reports') }}" class="nav-item {{ Request::routeIs('client.reports') || Request::routeIs('client.updates') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i> Reports
                </a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="user-pill">
                <div class="user-info">
                    <div class="avatar-circle">
                        {{ strtoupper(substr(Auth::user()->name ?? 'C', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size: 0.82rem; font-weight:700; line-height: 1.2;">{{ Auth::user()->name ?? 'Client Account' }}</div>
                        <div style="font-size: 0.72rem; color: var(--sidebar-text-muted);">External Client</div>
                    </div>
                </div>
            </div>
            <a href="#" class="logout-link" onclick="event.preventDefault(); Swal.fire({ title: 'Confirm logout', text: 'Are you sure you want to sign out?', icon: 'question', showCancelButton: true, confirmButtonColor: '#0f5132', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, log out', cancelButtonText: 'Cancel', buttonsStyling: false, customClass: { actions: 'swal-actions-reverse', confirmButton: 'swal-confirm-btn', cancelButton: 'swal-cancel-btn' } }).then((result) => { if (result.isConfirmed) { document.getElementById('logout-form').submit(); } });">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </aside>

    <div class="main">
        <div class="global-mobile-nav" aria-label="Global mobile navigation">
            <button id="sidebarToggle" type="button" class="global-mobile-toggle" aria-label="Open sidebar navigation">
                <i class="bi bi-list"></i>
            </button>
            <button id="mobileNotificationBell" type="button" class="dashboard-notification-button" aria-label="Notifications">
                <i class="bi bi-bell"></i>
            </button>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggles = document.querySelectorAll('#sidebarToggle');

        function closeSidebar() {
            sidebar?.classList.remove('show');
            overlay?.classList.remove('show');
        }

        function toggleSidebar() {
            const isOpen = sidebar?.classList.contains('show');
            sidebar?.classList.toggle('show', !isOpen);
            overlay?.classList.toggle('show', !isOpen);
        }

        toggles.forEach(function (toggle) {
            toggle?.addEventListener('click', toggleSidebar);
        });
        overlay?.addEventListener('click', closeSidebar);

        document.querySelectorAll('.sidebar .nav-item').forEach(function (item) {
            item.addEventListener('click', closeSidebar);
        });

        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (element) {
            new bootstrap.Popover(element);
        });

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            new bootstrap.Tooltip(element);
        });

        const bell = document.getElementById('notificationBell');
        const popup = document.getElementById('notificationPopup');

        function toggleNotifications() {
            popup?.classList.toggle('show');
        }

        bell?.addEventListener('click', toggleNotifications);
        bell?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleNotifications();
            }
        });

        document.addEventListener('click', function(event) {
            if (!bell?.contains(event.target) && !popup?.contains(event.target)) {
                popup?.classList.remove('show');
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>