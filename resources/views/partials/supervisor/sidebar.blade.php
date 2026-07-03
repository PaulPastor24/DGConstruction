<aside class="sidebar" id="supervisorSidebar">
    <div class="sidebar-logo">
        <div class="logo-badge">
            <div class="logo-icon">
                <img src="{{ asset('images/D&G.png') }}" alt="D&G Logo">
            </div>
            <div>
                <div class="logo-text">D&G Construction</div>
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
