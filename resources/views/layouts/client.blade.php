<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Client Portal D&G Construction Monitor')</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            --font-brand: 'Syne', sans-serif;
            --font-ui: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-ui);
            font-size: 15px;
            font-weight: 400;
            line-height: 1.5;
            background-color: var(--bg-main);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        .app {
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-brand);
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }

        h1 { font-size: 1.8rem; }
        h2 { font-size: 1.45rem; }
        h3 { font-size: 1.2rem; }

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
            font-family: var(--font-brand);
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: #ffffff;
        }

        .brand-text span {
            font-family: var(--font-ui);
            font-size: 0.76rem;
            color: var(--sidebar-text-muted);
            display: block;
            font-weight: 400;
        }

        .sidebar-nav {
            padding: 0 0.75rem;
            flex-grow: 1;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1rem;
            color: var(--sidebar-text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
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

        .logout-icon-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            color: var(--sidebar-text-muted);
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            text-decoration: none;
            transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
        }
        .logout-icon-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
            transform: translateY(-1px);
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

        .notification-bell-animate {
            animation: bell-ring 1.2s ease-in-out infinite, pulse-soft 1.45s ease-out infinite;
            transform-origin: center top;
            color: #22c55e;
            background: #f0fdf4;
            border-color: #22c55e;
            position: relative;
        }

        .notification-bell-animate::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 999px;
            border: 2px solid rgba(34, 197, 94, 0.28);
            animation: ring-pulse 1.45s ease-out infinite;
            pointer-events: none;
        }

        .notification-bell-animate .bi-bell {
            color: #22c55e;
            z-index: 1;
        }

        @keyframes bell-ring {
            0%, 100% { transform: rotate(0deg); }
            10% { transform: rotate(12deg); }
            20% { transform: rotate(-10deg); }
            30% { transform: rotate(8deg); }
            40% { transform: rotate(-6deg); }
            50% { transform: rotate(4deg); }
            60% { transform: rotate(-2deg); }
            70% { transform: rotate(2deg); }
            80%, 90% { transform: rotate(0deg); }
        }

        @keyframes pulse-soft {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.24); }
            70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        @keyframes ring-pulse {
            0% { transform: scale(0.92); opacity: 0.9; }
            70% { transform: scale(1.12); opacity: 0; }
            100% { transform: scale(1.16); opacity: 0; }
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55);
            animation: ping-dot 1.4s ease-out infinite;
        }

        @keyframes ping-dot {
            0% { transform: scale(0.9); opacity: 1; }
            80% { transform: scale(1.65); opacity: 0; }
            100% { transform: scale(1.8); opacity: 0; }
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
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 0 0.15rem 0;
            color: var(--text-primary);
        }

        .welcome-msg p {
            color: var(--text-muted);
            margin: 0;
            font-size: 0.85rem;
            font-weight: 400;
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
            top: 4px;
            right: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 0.25rem;
            border: 2px solid #ffffff;
            border-radius: 999px;
            background: #22c55e !important;
            color: #ffffff;
            font-size: 0.68rem;
            font-weight: 700;
            line-height: 1;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
            animation: ping-dot 1.4s ease-out infinite;
            pointer-events: none;
        }

        .dashboard-notification-button.notification-bell-animate::after {
            content: '';
            position: absolute;
            top: 2px;
            right: 2px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(34, 197, 94, 0.12);
            animation: badge-ring 1.6s ease-out infinite;
            pointer-events: none;
        }

        @keyframes badge-ring {
            0% { transform: scale(0.9); opacity: 0.9; }
            60% { transform: scale(1.35); opacity: 0.1; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        .notification-popup {
            position: fixed;
            top: 5rem;
            right: 1.5rem;
            width: 340px;
            max-width: calc(100vw - 2rem);
            background: var(--surface-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
            z-index: 1055;
            overflow: hidden;
            display: none;
        }

        .notification-popup.show {
            display: block;
        }

        .notification-popup-header {
            padding: 1rem 1.15rem;
            border-bottom: 1px solid rgba(42, 64, 40, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            background: linear-gradient(135deg, rgba(42, 64, 40, 0.03), rgba(143, 174, 133, 0.08));
        }

        .notification-popup-header h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .notification-popup-list {
            max-height: 360px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 0.95rem 1.15rem;
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
            line-height: 1.4;
        }

        .notification-item-time {
            display: block;
            margin-top: 0.55rem;
            font-size: 0.75rem;
            color: rgba(98, 110, 97, 0.8);
        }

        .notification-item.unread {
            background: linear-gradient(90deg, rgba(143, 174, 133, 0.12), rgba(255,255,255,0));
        }

        .notification-item .notif-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--brand-green);
            display: inline-block;
            flex-shrink: 0;
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
            font-size: 1.7rem;
            font-weight: 700;
            line-height: 1.15;
            margin: 0;
            color: var(--brand-green);
        }

        .page-header-subtitle {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 400;
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
            font-size: 1.7rem;
            font-weight: 700;
            line-height: 1.15;
            margin: 0;
            color: var(--brand-green);
        }

        .dashboard-page-description {
            margin: 0.2rem 0 0;
            font-size: 0.9rem;
            font-weight: 400;
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
            position: relative;
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
            .content {
                padding: 1rem 1.1rem 1.25rem 1.1rem;
            }
            .page-header-compact,
            .dashboard-page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .page-header-tools,
            .dashboard-page-tools {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.6rem;
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
            .page-header-title,
            .dashboard-page-title {
                font-size: 1.45rem;
            }
            .page-header-subtitle,
            .dashboard-page-description {
                font-size: 0.85rem;
            }
            .topbar-actions {
                flex-direction: column;
                align-items: flex-start;
            }
            .topbar-action-icons {
                width: 100%;
                justify-content: flex-end;
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
                <a href="#" class="logout-icon-link" aria-label="Logout" onclick="event.preventDefault(); Swal.fire({ title: 'Confirm logout', text: 'Are you sure you want to sign out?', icon: 'question', showCancelButton: true, confirmButtonColor: '#0f5132', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes, log out', cancelButtonText: 'Cancel', buttonsStyling: false, customClass: { actions: 'swal-actions-reverse', confirmButton: 'swal-confirm-btn', cancelButton: 'swal-cancel-btn' } }).then((result) => { if (result.isConfirmed) { document.getElementById('logout-form').submit(); } });">
                    <i class="bi bi-box-arrow-left"></i>
                </a>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </div>
    </aside>

    <div class="main">
        <div class="global-mobile-nav" aria-label="Global mobile navigation">
            <button id="sidebarToggle" type="button" class="global-mobile-toggle" aria-label="Open sidebar navigation">
                <i class="bi bi-list"></i>
            </button>
            <button id="mobileNotificationBell" type="button" class="dashboard-notification-button notification-toggle-btn {{ ($clientUnreadCount ?? 0) > 0 ? 'notification-bell-animate' : '' }}" style="position: relative;" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                @if(($clientUnreadCount ?? 0) > 0)
                    <span class="notification-badge" aria-label="{{ $clientUnreadCount ?? 0 }} unread notifications">{{ $clientUnreadCount ?? 0 }}</span>
                @endif
            </button>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<div id="notificationPopup" class="notification-popup" role="dialog" aria-label="Client notifications">
    <div class="notification-popup-header">
        <div>
            <h6>Notifications</h6>
            <div class="text-muted" style="font-size: 0.78rem;">{{ $clientUnreadCount ?? 0 }} unread</div>
        </div>
        <a href="{{ route('client.notifications') }}" class="btn btn-sm btn-outline-secondary" style="font-size: 0.75rem; border-radius: 999px;">View all</a>
    </div>
    <div class="notification-popup-list">
        @forelse($clientNotifications->take(3) as $notification)
            @php
                $isUnread = !($notification->is_read ?? ($notification['is_read'] ?? false));
                $rawType = strtolower($notification['type'] ?? ($notification->type ?? 'system'));
                $icon = match ($rawType) {
                    'report' => 'bi-file-earmark-text',
                    'phase' => 'bi-bar-chart-steps',
                    'milestone', 'timeline' => 'bi-calendar3',
                    'announcement' => 'bi-megaphone',
                    default => 'bi-bell',
                };
                $module = $notification['module'] ?? ($notification->data['module'] ?? ($notification['route'] ?? ($notification->data['route'] ?? 'client.dashboard')));
                $params = $notification['params'] ?? ($notification->data['params'] ?? []);
                $href = route($module, $params);
                $link = route('client.notifications.markReadRedirect', ['id' => $notification->id ?? $notification['id'] ?? 0]) . '?redirect=' . urlencode($href);
            @endphp
            <a href="{{ $link }}" class="notification-item d-flex align-items-start gap-2 text-decoration-none {{ $isUnread ? 'unread' : '' }}" data-notif-id="{{ $notification->id ?? $notification['id'] ?? '' }}" style="color: inherit;">
                <span class="notif-dot mt-2"></span>
                <div class="flex-grow-1">
                    <div class="notification-item-title">{{ $notification['title'] ?? ($notification->title ?? 'Notification') }}</div>
                    <p class="notification-item-text">{{ $notification['message'] ?? ($notification->message ?? 'You have a new update.') }}</p>
                    <span class="notification-item-time">{{ $notification['time'] ?? ($notification['created_at'] ? $notification['created_at']->diffForHumans() : ($notification->created_at ? $notification->created_at->diffForHumans() : 'Just now')) }}</span>
                </div>
            </a>
        @empty
            <div class="text-center py-4 px-3 text-muted" style="font-size: 0.9rem;">
                <i class="bi bi-bell-slash display-6 d-block mb-2"></i>
                No new notifications yet.
            </div>
        @endforelse
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggles = document.querySelectorAll('#sidebarToggle');

    const popup = document.getElementById('notificationPopup');
    const bells = document.querySelectorAll('.notification-toggle-btn');

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const notificationMarkReadUrlTemplate = "{{ route('client.notifications.markRead', ['id' => '__ID__']) }}";

    function closeSidebar() {
        sidebar?.classList.remove('show');
        overlay?.classList.remove('show');
    }

    function toggleSidebar() {
        const isOpen = sidebar?.classList.contains('show');

        sidebar?.classList.toggle('show', !isOpen);
        overlay?.classList.toggle('show', !isOpen);
    }

    function toggleNotifications() {
        popup?.classList.toggle('show');
    }

    toggles.forEach(function (toggle) {
        toggle?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleSidebar();
        });
    });

    overlay?.addEventListener('click', closeSidebar);

    document.querySelectorAll('.sidebar .nav-item').forEach(function (item) {
        item.addEventListener('click', closeSidebar);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
            popup?.classList.remove('show');
        }
    });

    bells.forEach(function (bell) {
        bell?.addEventListener('click', function (event) {
            event.stopPropagation();
            toggleNotifications();
        });

        bell?.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleNotifications();
            }
        });
    });

    document.addEventListener('click', function (event) {
        const clickedBell = Array.from(bells).some(function (bell) {
            return bell?.contains(event.target);
        });

        if (!clickedBell && !popup?.contains(event.target)) {
            popup?.classList.remove('show');
        }
    });

    popup?.addEventListener('click', function (event) {
        const item = event.target.closest('.notification-item');

        if (!item) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const id = item.getAttribute('data-notif-id');
        const href = item.getAttribute('href');

        if (!id || !href) {
            return;
        }

        const markReadUrl = notificationMarkReadUrlTemplate.replace('__ID__', encodeURIComponent(id));

        fetch(markReadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({})
        }).then(function (response) {
            if (!response.ok) {
                console.error('Notification mark-read failed:', response.statusText);
            }
        }).catch(function (error) {
            console.error('Notification mark-read error:', error);
        }).finally(function () {
            window.location = href;
        });
    });
});
</script>
@stack('scripts')
</body>
</html>