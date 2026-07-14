<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Syne', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        #sidebarToggle { border: 0; background: transparent; color: inherit; font-size: 24px; }
        .sidebar { display: flex !important; flex-direction: column !important; }
        .sidebar-logo { flex-shrink: 0 !important; }
        .sidebar-nav { flex: 1 1 auto !important; min-height: 0 !important; overflow-x: hidden !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; }
        .sidebar-footer { margin-top: auto !important; flex-shrink: 0 !important; }
    </style>
</head>
<body>
    <div class="app">
        @include('partials.admin.sidebar')
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="main">
            @include('partials.admin.topbar')
            <div class="content">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            function updateToggleVisibility() {
                if (window.innerWidth <= 1024) {
                    if (sidebarToggle) sidebarToggle.style.display = 'inline-flex';
                } else {
                    if (sidebarToggle) sidebarToggle.style.display = 'none';
                    if (sidebar) sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                }
            }

            updateToggleVisibility();
            window.addEventListener('resize', updateToggleVisibility);

            if (sidebarToggle && sidebar && overlay) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });

                document.querySelectorAll('.sidebar .nav-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    });
                });

                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                });
            }

            const profileToggle = document.getElementById('profileDropdownToggle');
            const profileMenu = document.getElementById('profileDropdownMenu');

            if (profileToggle && profileMenu) {
                profileToggle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    profileMenu.classList.toggle('show');
                });

                document.addEventListener('click', function(event) {
                    if (!profileMenu.contains(event.target) && event.target !== profileToggle) {
                        profileMenu.classList.remove('show');
                    }
                });

                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') {
                        profileMenu.classList.remove('show');
                    }
                });
            }

            const logoutButtonTopbar = document.getElementById('logoutButtonTopbar');
            const logoutFormTopbar = document.getElementById('logout-form-topbar');

            if (logoutButtonTopbar && logoutFormTopbar) {
                logoutButtonTopbar.addEventListener('click', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Sign out?',
                        text: 'Are you sure you want to log out?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2a4028',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, log out',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            logoutFormTopbar.submit();
                        }
                    });
                });
            }

            const logoutButtonSidebar = document.getElementById('logoutButtonSidebar');
            const logoutFormSidebar = document.getElementById('logout-form-sidebar');

            if (logoutButtonSidebar && logoutFormSidebar) {
                logoutButtonSidebar.addEventListener('click', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Sign out?',
                        text: 'Are you sure you want to log out?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2a4028',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, log out',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            logoutFormSidebar.submit();
                        }
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
