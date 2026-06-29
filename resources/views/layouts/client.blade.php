<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Client Portal D&G Construction Monitor')</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            /* Exact Luxury Premium Corporate Forest Green Palettes from Mockup */
            --sidebar-bg: #032b1d; 
            --sidebar-active: #155e43; 
            --sidebar-text: #ffffff;
            --sidebar-text-muted: #a3b899;
            
            /* Background Canvas Set to Matte Light Sage Mint-Cream Gray tint instead of standard bright blue */
            --bg-main: #f4f7f6; 
            --surface-card: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-muted: #64748b;
            
            --brand-green: #16a34a;
            --brand-mint: #dcfce7;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        .app {
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR CONTAINER CONTROL --- */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
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
        }

        .sidebar-brand {
            padding: 2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-icon, .sidebar-logo-img {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: transparent;
            overflow: hidden;
        }

        .sidebar-logo-img {
            border-radius: 50%;
            padding: 0;
            background: transparent;
        }

        .sidebar-logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-icon {
            font-size: 1.4rem;
            color: #84cc16;
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
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-item.active {
            background-color: var(--sidebar-active);
            color: #ffffff;
        }

        .sidebar-footer {
            padding: 1.25rem 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .user-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.04);
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
            background-color: #155e43;
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
            background: rgba(239, 68, 68, 0.08);
        }

        /* --- MAIN INTERFACE WORKSPACE --- */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2.25rem;
            background: transparent;
            gap: 1rem;
            min-height: 84px;
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

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
            min-width: 0;
        }

        .topbar-page-header {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.125rem;
            min-width: 0;
        }

        .page-header-label {
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            color: #16a34a;
        }

        .page-header-title {
            font-size: 1.35rem;
            margin: 0;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.1;
        }

        .page-header-copy {
            font-size: 0.92rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            justify-content: flex-end;
            width: auto;
        }

        .topbar-actions-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 0;
        }

        .date-badge,
        .topbar-user-block,
        .notification-bell {
            min-height: 44px;
        }

        .topbar-action-icons {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            justify-content: flex-end;
            min-width: 0;
        }

        .topbar-user-block {
            padding: 0.5rem 0.9rem;
            min-width: 220px;
            max-width: 300px;
        }

        .topbar-user-block {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            min-width: 180px;
            max-width: 280px;
        }

        .topbar-user-initial {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #16a34a;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .topbar-user-name div {
            font-size: 0.75rem;
            color: #64748b;
            line-height: 1.2;
        }

        .topbar-user-name strong {
            font-size: 0.95rem;
            display: block;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .topbar-actions .avatar-circle {
            width: 38px;
            height: 38px;
            font-size: 0.85rem;
        }

        .notification-bell {
            width: 38px;
            height: 38px;
        }

        .date-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-primary);
            background: #ffffff;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .notification-bell {
            position: relative;
            font-size: 1.2rem;
            color: var(--text-primary);
            cursor: pointer;
            background: #ffffff;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 9px;
            background: #f59e0b;
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
            background: #ffffff;
            border: 1px solid #e2e8f0;
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
            border-bottom: 1px solid #f1f5f9;
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
            border-bottom: 1px solid #f8fafc;
            transition: background 0.15s ease;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: #f8fafc;
        }

        .notification-item-title {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            color: var(--text-primary);
        }

        .notification-item-text {
            font-size: 0.82rem;
            color: #64748b;
            margin: 0;
        }

        .notification-item-time {
            display: block;
            margin-top: 0.55rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .content {
            padding: 0 2.25rem 2.25rem 2.25rem;
        }

        .topbar {
            flex-wrap: wrap;
            gap: 1rem;
        }

        .topbar-actions {
            flex-wrap: wrap;
            justify-content: flex-end;
            width: 100%;
        }

        .welcome-msg {
            min-width: 220px;
            flex: 1;
        }

        .notification-popup {
            right: 1.5rem;
        }

        #sidebarToggle {
            display: none;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 1.25rem;
            line-height: 1;
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
            #sidebarToggle { display: flex; }
            .topbar { padding: 1.25rem 1.5rem; }
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
            .topbar-actions-left { flex: 0 1 auto; }
            .topbar-action-icons { justify-content: flex-end; }
            .topbar-user-block { min-width: 100%; }
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
                    <img src="{{ asset('images/image.png') }}" alt="D&G Construction logo" style="width: 100%; height: 100%; object-fit: contain;">
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
            <a href="#" class="logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button id="sidebarToggle"><i class="bi bi-list"></i></button>
                <div class="topbar-page-header">
                    @hasSection('pageHeaderLabel')
                        <span class="page-header-label">@yield('pageHeaderLabel')</span>
                    @endif
                    <h2 class="page-header-title">@yield('pageHeaderTitle', 'Dashboard')</h2>
                    @hasSection('pageHeaderCopy')
                        <p class="page-header-copy">@yield('pageHeaderCopy')</p>
                    @endif
                </div>
            </div>
            
            <div class="topbar-actions">
                <div class="topbar-actions-left">
                    <div class="date-badge">
                        <i class="bi bi-calendar3 text-success"></i>
                        <span>{{ now()->format('D, M d, Y') }}</span>
                    </div>
                </div>
                <div class="topbar-action-icons">
                    <div class="topbar-user-block d-flex align-items-center gap-2">
                        <div class="topbar-user-initial">{{ strtoupper(substr(Auth::user()->name ?? 'C', 0, 1)) }}</div>
                        <div class="topbar-user-name">
                            <div>Welcome back</div>
                            <strong>{{ Auth::user()->name ? strtok(Auth::user()->name, ' ') : 'Client' }}</strong>
                        </div>
                    </div>
                    <div class="notification-bell" id="notificationBell" aria-label="Notifications" role="button" tabindex="0">
                        <i class="bi bi-bell"></i>
                        <div class="notification-badge"></div>
                    </div>
                </div>
                <div class="notification-popup" id="notificationPopup">
                    <div class="notification-popup-header">
                        <h6>Notifications</h6>
                        <span class="text-muted" style="font-size:0.78rem;">{{ $clientNotificationCount ?? 0 }} new</span>
                    </div>
                    <div class="notification-popup-list">
                        @if(!empty($clientNotifications) && count($clientNotifications))
                            @foreach($clientNotifications as $notification)
                                <div class="notification-item">
                                    <div class="notification-item-title">{{ $notification['title'] }}</div>
                                    <p class="notification-item-text">{{ $notification['message'] }}</p>
                                    <span class="notification-item-time">{{ $notification['time'] }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="notification-item">
                                <div class="notification-item-title">No new notifications</div>
                                <p class="notification-item-text">We will alert you when a milestone is delayed or a report is uploaded.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        toggle?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);

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