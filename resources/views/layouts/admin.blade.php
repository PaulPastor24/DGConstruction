<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
>

<title>
    @yield('title', 'Admin Dashboard')
</title>

<link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="{{ asset('css/admin.css') }}"
>

@stack('styles')

<style>
    #sidebarToggle {
        border: 0;
        background: transparent;
        color: inherit;
        font-size: 24px;
    }
</style>

</head>

<body>
<div class="app">

<aside class="sidebar">

    <div class="sidebar-logo">

        <div class="logo-badge">

            <div
                style="
                    width: 65px;
                    height: 65px;
                    border-radius: 50%;
                    overflow: hidden;
                "
            >
                <img
                    src="{{ asset('images/image.png') }}"
                    alt="D&G Logo"
                    style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        transform: scale(1.25);
                    "
                >
            </div>

            <div>

                <div class="logo-text">
                    D&G Dev't Corp.
                </div>

                <div class="logo-sub">
                    Construction Management System
                </div>

            </div>

        </div>

    </div>

    <nav class="sidebar-nav">

        <div class="nav-section-label">
            Overview
        </div>

        <a
            href="{{ route('admin.dashboard') }}"
            class="nav-item
                {{ request()->routeIs('admin.dashboard')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Management Dashboard</span>
        </a>

        <div class="nav-section-label">
            Monitoring & Progress
        </div>

        <a
            href="{{ route('admin.timeline') }}"
            class="nav-item
                {{ request()->routeIs('admin.timeline')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-bar-chart-steps"></i>
            <span>Project Timeline</span>
        </a>

        <a
            href="{{ route('admin.reports.index') }}"
            class="nav-item
                {{ request()->routeIs('admin.reports.*')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-file-earmark-text"></i>
            <span>Progress Report</span>
        </a>

        <a
            href="{{ route('admin.phases') }}"
            class="nav-item
                {{ request()->routeIs('admin.phases')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-kanban"></i>
            <span>Phase Management</span>
        </a>

        <div class="nav-section-label">
            Materials & Attendance
        </div>

        <a
            href="{{ route('admin.attendance') }}"
            class="nav-item
                {{ request()->routeIs('admin.attendance')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-person-badge"></i>
            <span>Worker Attendance</span>
        </a>

        <a
            href="{{ route('admin.inventory') }}"
            class="nav-item
                {{ request()->routeIs('admin.inventory*')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-box-seam"></i>
            <span>Materials & Inventory</span>
        </a>

        <div class="nav-section-label">
            Management
        </div>

        <a
            href="{{ route('admin.projects.index') }}"
            class="nav-item
                {{ request()->routeIs('admin.projects.*')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-building"></i>
            <span>Project Management</span>
        </a>

        <a
            href="{{ route('admin.users.index') }}"
            class="nav-item
                {{ request()->routeIs('admin.users.*')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-people"></i>
            <span>User Management</span>
        </a>

        <div class="nav-section-label">
            System
        </div>

        <a
            href="{{ route('admin.alerts') }}"
            class="nav-item
                {{ request()->routeIs('admin.alerts*')
                    ? 'active'
                    : '' }}"
        >
            <i class="bi bi-bell"></i>
            <span>Alerts</span>
        </a>

        <form
            id="logout-form"
            action="{{ route('logout') }}"
            method="POST"
            style="display: none;"
        >
            @csrf
        </form>

        <a
            href="{{ route('logout') }}"
            class="nav-item"
            onclick="
                event.preventDefault();
                document
                    .getElementById('logout-form')
                    .submit();
            "
        >
            <i class="bi bi-box-arrow-right"></i>
            <span>Sign Out</span>
        </a>

    </nav>

    <div class="sidebar-footer">

        <div class="blueprint-watermark"></div>

        <div class="sidebar-slogan">
            We Build Trust.
        </div>

    </div>

</aside>

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>

<div class="main">

    <div class="topbar">

        <div class="topbar-left">

            <button
                type="button"
                id="sidebarToggle"
                class="d-lg-none"
                aria-label="Open navigation"
            >
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-title">
                @yield(
                    'page_title',
                    'Management Dashboard'
                )
            </div>

        </div>

        <div class="topbar-right">

            <span class="me-3">
                {{ now()->format('D, d M Y') }}
            </span>

        </div>

    </div>

    <div class="content">
        @yield('content')
    </div>

</div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>

<script>
    const sidebar =
        document.querySelector('.sidebar');

    const sidebarToggle =
        document.getElementById('sidebarToggle');

    const sidebarOverlay =
        document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar?.classList.toggle('show');
        sidebarOverlay?.classList.toggle('show');
    }

    function closeSidebar() {
        sidebar?.classList.remove('show');
        sidebarOverlay?.classList.remove('show');
    }

    sidebarToggle?.addEventListener(
        'click',
        toggleSidebar
    );

    sidebarOverlay?.addEventListener(
        'click',
        closeSidebar
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        }
    );
</script>

@stack('scripts')

</body>
</html>
