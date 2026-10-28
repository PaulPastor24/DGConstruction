<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/D&G.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/D&G.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/D&G.png') }}">
    <meta name="msapplication-TileImage" content="{{ asset('images/D&G.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ time() }}">
    @stack('styles')
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Syne', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        #sidebarToggle { border: 0; background: transparent; color: inherit; font-size: 24px; }
        .sidebar { display: flex !important; flex-direction: column !important; }
        .sidebar-logo { flex-shrink: 0 !important; }
        .sidebar-nav { flex: 1 1 auto !important; min-height: 0 !important; overflow-x: hidden !important; overflow-y: auto !important; -webkit-overflow-scrolling: touch !important; }
        .sidebar-footer { margin-top: auto !important; flex-shrink: 0 !important; }
        .attendance-nav-group > summary { cursor: pointer; list-style: none; }
        .attendance-nav-group > summary::-webkit-details-marker { display: none; }
        .attendance-nav-chevron { margin-left: auto; font-size: 12px; transition: transform .15s; }
        .attendance-nav-group[open] .attendance-nav-chevron { transform: rotate(180deg); }
        .attendance-nav-children {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transform: translateY(-6px);
        }
        .attendance-nav-group[open] .attendance-nav-children {
            max-height: none;
            opacity: 1;
            transform: translateY(0);
        }
        .attendance-nav-group.is-animating .attendance-nav-children {
            transition: max-height .25s ease, opacity .2s ease, transform .25s ease;
        }
        .attendance-nav-children { margin: 4px 12px 8px 28px; }
        .attendance-nav-children-inner { padding-left: 8px; border-left: 1px solid rgba(255,255,255,.18); }
        .attendance-nav-children .nav-item { font-size: 12px; padding: 10px 12px; border-radius: 10px; }
        @media (prefers-reduced-motion: reduce) {
            .attendance-nav-chevron,
            .attendance-nav-children { transition: none; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/numbers.css') }}?v={{ filemtime(public_path('css/numbers.css')) }}">
    @include('partials.system-loading-head')
</head>
<body class="{{ request()->is('admin/timeline*') ? 'page-admin-timeline' : '' }} {{ request()->is('admin/users*') ? 'page-admin-users' : '' }} {{ request()->is('admin/inventory*') ? 'page-admin-inventory' : '' }} {{ request()->is('admin/reports*') ? 'page-admin-reports' : '' }} {{ request()->is('admin/phases*') ? 'page-admin-phases' : '' }}">
    @include('partials.system-loading')
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

            const attendanceGroup = document.querySelector('.attendance-nav-group');
            const attendanceToggle = attendanceGroup && attendanceGroup.querySelector(':scope > summary');
            const attendanceChildren = attendanceGroup && attendanceGroup.querySelector(':scope > .attendance-nav-children');

            if (attendanceGroup && attendanceToggle && attendanceChildren) {
                let expanded = attendanceGroup.open;
                let closeTimer;
                const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                attendanceToggle.setAttribute('aria-expanded', String(expanded));

                function finishAttendanceTransition() {
                    window.clearTimeout(closeTimer);
                    if (!expanded) attendanceGroup.open = false;
                    attendanceChildren.style.maxHeight = '';
                    attendanceGroup.classList.remove('is-animating');
                }

                function scheduleAttendanceTransitionEnd() {
                    closeTimer = window.setTimeout(finishAttendanceTransition, reducedMotion ? 0 : 280);
                }

                attendanceToggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    expanded = !expanded;
                    attendanceToggle.setAttribute('aria-expanded', String(expanded));
                    window.clearTimeout(closeTimer);
                    attendanceGroup.classList.add('is-animating');

                    if (expanded) {
                        attendanceGroup.open = true;
                        attendanceChildren.style.maxHeight = '0px';
                        requestAnimationFrame(function() {
                            if (expanded) {
                                attendanceChildren.style.maxHeight = `${attendanceChildren.scrollHeight}px`;
                                scheduleAttendanceTransitionEnd();
                            }
                        });
                    } else {
                        attendanceChildren.style.maxHeight = `${attendanceChildren.scrollHeight}px`;
                        requestAnimationFrame(function() {
                            attendanceChildren.style.maxHeight = '0px';
                            scheduleAttendanceTransitionEnd();
                        });
                    }
                });

                attendanceChildren.addEventListener('transitionend', function(event) {
                    if (event.propertyName === 'max-height' && attendanceGroup.classList.contains('is-animating')) {
                        finishAttendanceTransition();
                    }
                });
            }

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

        @if(session('login_success'))
            Swal.fire({
                icon: 'success',
                title: 'Welcome!',
                text: 'You have successfully logged in.',
                confirmButtonColor: '#198754'
            });
        @endif
    </script>
    @stack('scripts')
@include('partials.user-presence')
</body>
</html>
