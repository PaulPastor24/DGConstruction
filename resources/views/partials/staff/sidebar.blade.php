<aside class="sidebar" id="staffSidebar">
    <div class="sidebar-logo"><div class="logo-badge"><div class="logo-icon"><img src="{{ asset('images/image.png') }}" alt="D&amp;G Logo"></div><div><div class="logo-text">D&amp;G Dev't Corp.</div><div class="logo-sub">Staff Workspace</div></div></div></div>
    <nav class="sidebar-nav" aria-label="Staff navigation">
        <div class="nav-section-label">Overview</div>
        <a class="nav-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}"><i class="bi bi-grid-1x2-fill"></i><span>Staff Dashboard</span></a>
        <div class="nav-section-label">Operations</div>
        <a class="nav-item {{ request()->routeIs('staff.timeline') ? 'active' : '' }}" href="{{ route('staff.timeline') }}"><i class="bi bi-bar-chart-steps"></i><span>Project Milestones</span></a>
        <a class="nav-item {{ request()->routeIs('staff.reports.*') ? 'active' : '' }}" href="{{ route('staff.reports.index') }}"><i class="bi bi-file-earmark-text"></i><span>Progress Reports</span></a>
        <a class="nav-item {{ request()->routeIs('staff.attendance') ? 'active' : '' }}" href="{{ route('staff.attendance') }}"><i class="bi bi-person-badge"></i><span>Attendance</span></a>
        <a class="nav-item {{ request()->routeIs('staff.inventory*') ? 'active' : '' }}" href="{{ route('staff.inventory') }}"><i class="bi bi-box-seam"></i><span>Materials &amp; Inventory</span></a>
        <div class="nav-section-label">Projects</div>
        <a class="nav-item {{ request()->routeIs('staff.projects.*') ? 'active' : '' }}" href="{{ route('staff.projects.index') }}"><i class="bi bi-building"></i><span>Project Overview</span></a>
    </nav>
    <div class="sidebar-footer"><div class="user-card"><div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}</div><div class="user-info"><div class="user-name">{{ auth()->user()->name ?? 'Staff' }}</div><div class="user-role">Office Staff</div></div></div></div>
</aside>
