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
        <button class="topbar-icon" type="button" aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </button>
        <div class="topbar-date">{{ now()->format('D, M d, Y') }}</div>
    </div>
</header>
