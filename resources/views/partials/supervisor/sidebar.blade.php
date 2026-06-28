<aside class="sidebar" id="supervisorSidebar">
    <div class="sidebar-logo">
        <div class="logo-badge">
            <div class="logo-icon">DG</div>
            <div>
                <div class="logo-text">D&G Monitor</div>
                <div class="logo-sub">Field Operations</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Supervisor navigation">
        <div class="nav-section-label">Core</div>
        <a class="nav-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}" href="{{ route('supervisor.dashboard') }}">
            <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
        </a>
        <a class="nav-item {{ request()->routeIs('supervisor.timeline') ? 'active' : '' }}" href="{{ route('supervisor.timeline') }}">
            <i class="bi bi-calendar3"></i><span>Timeline</span>
        </a>
        <a class="nav-item {{ request()->routeIs('supervisor.phases') ? 'active' : '' }}" href="{{ route('supervisor.phases') }}">
            <i class="bi bi-diagram-3"></i><span>Phases</span>
        </a>
        <a class="nav-item {{ request()->routeIs('supervisor.attendance') ? 'active' : '' }}" href="{{ route('supervisor.attendance') }}">
            <i class="bi bi-person-check"></i><span>Attendance</span>
        </a>
        <a class="nav-item {{ request()->routeIs('supervisor.materials') ? 'active' : '' }}" href="{{ route('supervisor.materials') }}">
            <i class="bi bi-box-seam"></i><span>Materials</span>
        </a>
        <a class="nav-item {{ request()->routeIs('supervisor.reports') ? 'active' : '' }}" href="{{ route('supervisor.reports') }}">
            <i class="bi bi-file-earmark-text"></i><span>Reports</span>
        </a>
        <a class="nav-item {{ request()->routeIs('supervisor.notifications') ? 'active' : '' }}" href="{{ route('supervisor.notifications') }}">
            <i class="bi bi-bell"></i><span>Notifications</span>
        </a>

        <div class="nav-section-label">Account</div>
        <a class="nav-item {{ request()->routeIs('supervisor.profile') ? 'active' : '' }}" href="{{ route('supervisor.profile') }}">
            <i class="bi bi-person-circle"></i><span>Profile</span>
        </a>
        <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
        <a class="nav-item" href="#" onclick="event.preventDefault(); if(confirm('Are you sure you want to sign out?')) document.getElementById('logout-form-sidebar').submit();">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <div class="user-name">{{ auth()->user()->name ?? 'Supervisor' }}</div>
                <div class="user-role">Site Supervisor</div>
            </div>
        </div>
    </div>
</aside>
