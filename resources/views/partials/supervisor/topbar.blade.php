<header class="topbar">
    <div class="topbar-left">
        <button class="menu-button" id="sidebarToggle" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <div class="eyebrow">Supervisor Console</div>
            <div class="topbar-breadcrumb">@yield('page_title', 'Operations Overview')</div>
        </div>
    </div>

    <div class="topbar-right">
        <a class="topbar-icon" href="{{ route('supervisor.notifications') }}" aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </a>
        <div class="topbar-date">{{ now()->format('D, M d, Y') }}</div>
    </div>
</header>
