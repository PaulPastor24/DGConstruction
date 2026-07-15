<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-logo">
        <div class="logo-badge">
            <div class="logo-icon">
                <img src="{{ asset('images/image.png') }}" alt="D&G Logo">
            </div>
            <div>
                <div class="logo-text">D&G Dev't Corp.</div>
                <div class="logo-sub">Construction Management System</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Admin navigation">
        <div class="nav-section-label">Overview</div>
        <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i><span>Management Dashboard</span>
        </a>
        <div class="nav-section-label">Monitoring & Progress</div>
        <a class="nav-item {{ request()->routeIs('admin.timeline') ? 'active' : '' }}" href="{{ route('admin.timeline') }}">
            <i class="bi bi-bar-chart-steps"></i><span>Project Milestones</span>
        </a>
        <a class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
            <i class="bi bi-file-earmark-text"></i><span>Progress Report</span>
        </a>
        <a class="nav-item {{ request()->routeIs('admin.phases') ? 'active' : '' }}" href="{{ route('admin.phases') }}">
            <i class="bi bi-kanban"></i><span>Phase Management</span>
        </a>
        <div class="nav-section-label">Materials & Attendance</div>
        <a class="nav-item {{ request()->routeIs('admin.attendance') ? 'active' : '' }}" href="{{ route('admin.attendance') }}">
            <i class="bi bi-person-badge"></i><span>Worker Attendance</span>
        </a>
        <a class="nav-item {{ request()->routeIs('admin.inventory*') ? 'active' : '' }}" href="{{ route('admin.inventory') }}">
            <i class="bi bi-box-seam"></i><span>Materials & Inventory</span>
        </a>
        <div class="nav-section-label">Management</div>
        <a class="nav-item {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}" href="{{ route('admin.projects.index') }}">
            <i class="bi bi-building"></i><span>Project Management</span>
        </a>
        <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
            <i class="bi bi-people"></i><span>User Management</span>
        </a>
        <div class="nav-section-label">System</div>
        <a class="nav-item {{ request()->routeIs('admin.alerts*') ? 'active' : '' }}" href="{{ route('admin.alerts') }}">
            <i class="bi bi-bell"></i><span>Alerts</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
    </div>
</aside>
