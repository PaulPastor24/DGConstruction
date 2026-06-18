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
                    <div class="user-name{{ Auth::user()->name ?? 'Engr. Admin' }}"></div>
                    <div class="user-role">{{ Auth::user()->title ?? 'Project Engineer' }}</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title" id="pageTitle">@yield('page_title', 'Management Dashboard')</div>
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
@stack('scripts')
</body>
</html>