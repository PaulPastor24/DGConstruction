<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard D&G Construction Monitor')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
    <style>
        @media (max-width: 1024px) {
            .app {
                display: flex;
                flex-direction: column;
            }
            
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: 280px;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                overflow-y: auto;
                background: white;
                box-shadow: 2px 0 8px rgba(0,0,0,0.08);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.4);
                z-index: 1040;
                opacity: 0;
                transition: opacity 0.25s ease;
            }
            
            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
            
            .main {
                width: 100%;
                flex: 1;
            }
            
            .topbar {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
            }

            #sidebarToggle {
                background: transparent;
                border: 1px solid #dee2e6 !important;
                color: #495057;
                padding: 0.4rem 0.6rem !important;
                border-radius: 0.375rem;
                transition: all 0.2s ease;
                min-width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #sidebarToggle:hover {
                background: #f8f9fa;
                color: #212529;
                border-color: #adb5bd !important;
            }

            #sidebarToggle:active {
                transform: scale(0.95);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                max-width: 280px;
            }

            .topbar {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .topbar-title {
                font-size: 0.95rem;
                font-weight: 600;
            }

            .topbar-right {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<div class="app">
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-badge">
                <div class="logo-icon">
                    <img src="{{ asset('images/image.png') }}" alt="D&G">
                </div>
                <div>
                    <div class="logo-text">Admin</div>
                </div>
            </div>
            <div class="logo-sub">D&G Dev't Corporation</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                Management Dashboard
            </a>

            <div class="nav-section-label">Monitoring & Progress</div>
            <a href="{{ route('admin.timeline') }}" class="nav-item {{ request()->routeIs('admin.timeline') ? 'active' : '' }}">
                Project Timeline
            </a>
            <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                Progress Report
                {{-- REMOVED NOTIFICATION BADGE FROM HERE --}}
            </a>
            <a href="{{ route('admin.phases') }}" class="nav-item {{ request()->routeIs('admin.phases') ? 'active' : '' }}">
                Phase Management
            </a>

            <div class="nav-section-label">Materials & Attendance</div>
            <a href="{{ route('admin.attendance') }}" class="nav-item {{ request()->routeIs('admin.attendance') ? 'active' : '' }}">
                Worker Attendance
            </a>
            <a href="{{ route('admin.inventory') }}" class="nav-item {{ request()->routeIs('admin.inventory') ? 'active' : '' }}">
                Materials & Inventory
                {{-- REMOVED NOTIFICATION BADGE FROM HERE --}}
            </a>

            <div class="nav-section-label">Management</div>
            <a href="{{ route('admin.projects.index') }}" class="nav-item {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                Project Management
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                User Management
            </a>

            <div class="nav-section-label">System</div>
            <a href="{{ route('admin.alerts') }}" class="nav-item {{ request()->routeIs('admin.alerts') ? 'active' : '' }}">
                Alerts
                {{-- REMOVED NOTIFICATION BADGE FROM HERE --}}
            </a>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            <div class="nav-item" onclick="event.preventDefault(); if(confirm('Are you sure you want to sign out?')) document.getElementById('logout-form').submit();">
                Sign Out
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar"><i class="bi bi-person-fill"></i></div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name ?? 'Engr. Admin' }}</div>
                    <div class="user-role">{{ Auth::user()->title ?? 'Project Engineer' }}</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main">
        <div class="topbar">
            <div class="topbar-left" style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                <button id="sidebarToggle" class="d-lg-none" style="background: transparent; border: 1px solid #dee2e6; color: #495057; padding: 0.4rem 0.6rem; border-radius: 0.375rem; cursor: pointer; display: none; align-items: center; justify-content: center; width: 36px; height: 36px; transition: all 0.2s ease; flex-shrink: 0;">
                    <i class="bi bi-list" style="font-size: 1.25rem;"></i>
                </button>
                <div class="topbar-title" id="pageTitle" style="font-weight: 500; margin: 0; flex: 1; min-width: 0;">@yield('page_title', 'Management Dashboard')</div>
            </div>
            
            <div class="topbar-right">
                <span style="font-size:12px;color:var(--muted)">{{ now()->format('D, d M Y') }}</span>
                {{-- REMOVED TOPBAR NOTIFICATION BELL ICON CONTAINER FROM HERE --}}
                @yield('topbar_actions')
            </div>
        </div>

        <div class="content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mobile sidebar toggle with responsive behavior
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        // Show/hide toggle button based on screen size
        function updateToggleVisibility() {
            if (window.innerWidth <= 1024) {
                sidebarToggle.style.display = 'flex';
            } else {
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        }

        // Initial check
        updateToggleVisibility();
        
        // Listen for resize events
        window.addEventListener('resize', updateToggleVisibility);
        
        if (sidebarToggle) {
            // Toggle sidebar on button click
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
            
            // Close sidebar when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
            
            // Close sidebar when clicking a nav item
            const navItems = document.querySelectorAll('.sidebar .nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            });
            
            // Close sidebar on escape key
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
