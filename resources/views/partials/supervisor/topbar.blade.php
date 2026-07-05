<header class="topbar">
    <div class="topbar-left">
        <button class="menu-button" id="sidebarToggle" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <div class="eyebrow">Supervisor</div>
            <div class="topbar-breadcrumb">@yield('page_title', 'Operations Overview')</div>
        </div>
    </div>

    <div class="topbar-right">
        <a class="topbar-icon position-relative enhanced-icon" href="{{ route('supervisor.notifications') }}" aria-label="Notifications" title="Notifications">
            <i class="bi bi-bell"></i>
            @if(isset($supervisorUnreadCount) && $supervisorUnreadCount > 0)
                <span id="notif-badge" class="position-absolute" style="top:6px; right:6px; width:14px; height:14px; background:#198754; border-radius:999px; display:inline-block; border:2px solid #fff; font-size:0.7rem; line-height:10px; text-align:center; color:#fff;">{{ $supervisorUnreadCount }}</span>
            @endif
        </a>

        <div class="profile-dropdown-wrapper position-relative">
            <button class="topbar-icon enhanced-icon profile-toggle" type="button" id="profileDropdownToggle" aria-label="Profile menu" title="Profile">
                <i class="bi bi-person-circle"></i>
            </button>
            
            <div class="profile-dropdown-menu" id="profileDropdownMenu">
                <div class="profile-card">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="profile-info">
                        <div class="profile-name">{{ auth()->user()->name ?? 'Supervisor' }}</div>
                        <div class="profile-role">Site Supervisor</div>
                    </div>
                </div>
                <div class="profile-divider"></div>
                <a href="{{ route('supervisor.profile') }}" class="profile-menu-item">
                    <i class="bi bi-person"></i> Profile
                </a>
                <form id="logout-form-topbar" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
                <button type="button" class="profile-menu-item profile-logout" id="logoutButtonTopbar">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </div>
        </div>

        <div class="topbar-date">{{ now()->format('D, M d, Y') }}</div>
    </div>
</header>
