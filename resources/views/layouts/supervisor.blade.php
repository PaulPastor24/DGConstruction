<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Supervisor Portal - D&G Construction Monitor')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supervisor.css') }}">
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
                width: 260px;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                overflow-y: auto;
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
            
            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }
            
            .topbar {
                padding: 0 20px;
            }

            #sidebarToggle {
                background: transparent;
                border: 1px solid #dee2e6 !important;
                color: #495057;
                padding: 0.4rem 0.6rem !important;
                border-radius: 0.375rem;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
            }
        }
    </style>
</head>
<body>

<div class="app">
    <aside class="sidebar d-flex flex-column justify-content-between">
        <div>
            <div class="p-4">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/image.png') }}" alt="D&G" style="width: 32px; height: 32px; object-fit: contain;">
                    <div>
                        <div class="heading-syne text-success fw-bold m-0" style="font-size: 16px; color: var(--accent-color) !important;">Supervisor</div>
                    </div>
                </div>
                <div class="text-muted mt-1" style="font-size: 11px; font-weight: 500;">D&G Dev't Corporation</div>
            </div>

            <nav class="sidebar-nav">
                <div class="sidebar-heading">Site Operations</div>
                <a href="{{ route('supervisor.timeline') }}" class="nav-link-custom {{ request()->routeIs('supervisor.timeline') ? 'active' : '' }}">
                    Project Timeline
                </a>
                <a href="{{ route('supervisor.dashboard') }}" class="nav-link-custom {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                    Submit Report
                </a>

                <div class="sidebar-heading">Workforce & Materials</div>
                <a href="{{ route('supervisor.attendance') }}" class="nav-link-custom {{ request()->routeIs('supervisor.attendance') ? 'active' : '' }}">
                    Group Attendance
                </a>
                <a href="{{ route('supervisor.materials') }}" class="nav-link-custom {{ request()->routeIs('supervisor.materials') ? 'active' : '' }}">
                    Material Tracking
                </a>

                <div class="sidebar-heading">System</div>
                
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a href="#" class="nav-link-custom text-danger" onclick="event.preventDefault(); if(confirm('Are you sure you want to sign out?')) document.getElementById('logout-form').submit();">
                    Sign Out
                </a>
            </nav>
        </div>

        <div class="p-3 border-top bg-light-subtle">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 12px;">
                    CL
                </div>
                <div class="overflow-hidden">
                    <div class="text-dark fw-bold text-truncate" style="font-size: 13px; line-height: 1.2;">{{ Auth::user()->name ?? 'Contractor Lead' }}</div>
                    <div class="text-muted text-truncate" style="font-size: 11px;">Project Supervisor</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-content">
        <header class="topbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button id="sidebarToggle" class="d-lg-none">
                    <i class="bi bi-list" style="font-size: 1.25rem;"></i>
                </button>
                <h5 class="heading-syne m-0 text-dark fw-bold" style="font-size: 16px;">
                    @yield('page_title', 'Supervisor Portal')
                </h5>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <span style="font-size:12px; color:var(--text-muted)">{{ now()->format('D, d M Y') }}</span>
                <div class="position-relative bg-light rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; cursor: pointer;">
                    <i class="bi bi-bell text-secondary"></i>
                </div>
                @yield('topbar_actions')
            </div>
        </header>

        <main class="p-4">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
            
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    });
</script>
@stack('scripts')
</body>
</html>