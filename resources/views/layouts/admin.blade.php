<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @yield('title', 'Admin Dashboard')
    </title>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    @stack('styles')

    <style>
        /* Global body font to ensure consistent typography across views */
        body {
            font-family: 'Plus Jakarta Sans', 'Syne', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        #sidebarToggle {
            border: 0;
            background: transparent;
            color: inherit;
            font-size: 24px;
        }

        .sidebar {
            display: flex !important;
            flex-direction: column !important;
        }

        .sidebar-logo {
            flex-shrink: 0 !important;
        }

        .sidebar-nav {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        .sidebar-footer {
            margin-top: auto !important;
            flex-shrink: 0 !important;
        }
    </style>

</head>

<body>
    <div class="app">

        <aside class="sidebar">

            <div class="sidebar-logo">

                <div class="logo-badge">

                    <div
                        style="
                    width: 65px;
                    height: 65px;
                    border-radius: 50%;
                    overflow: hidden;
                ">
                        <img src="{{ asset('images/image.png') }}" alt="D&G Logo"
                            style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        transform: scale(1.25);
                    ">
                    </div>

                    <div>

                        <div class="logo-text">
                            D&G Dev't Corp.
                        </div>

                        <div class="logo-sub">
                            Construction Management System
                        </div>

                    </div>

                </div>

            </div>

            <nav class="sidebar-nav">

                <div class="nav-section-label">
                    Overview
                </div>

                <a href="{{ route('admin.dashboard') }}"
                    class="nav-item
                {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Management Dashboard</span>
                </a>

                <div class="nav-section-label">
                    Monitoring & Progress
                </div>

                <a href="{{ route('admin.timeline') }}"
                    class="nav-item
                {{ request()->routeIs('admin.timeline') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-steps"></i>
                    <span>Project Timeline</span>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                    class="nav-item
                {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Progress Report</span>
                </a>

                <a href="{{ route('admin.phases') }}"
                    class="nav-item
                {{ request()->routeIs('admin.phases') ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i>
                    <span>Phase Management</span>
                </a>

                <div class="nav-section-label">
                    Materials & Attendance
                </div>

                <a href="{{ route('admin.attendance') }}"
                    class="nav-item
                {{ request()->routeIs('admin.attendance') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Worker Attendance</span>
                </a>

                <a href="{{ route('admin.inventory') }}"
                    class="nav-item
                {{ request()->routeIs('admin.inventory*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Materials & Inventory</span>
                </a>

                <div class="nav-section-label">
                    Management
                </div>

                <a href="{{ route('admin.projects.index') }}"
                    class="nav-item
                {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i>
                    <span>Project Management</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="nav-item
                {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>User Management</span>
                </a>

                <div class="nav-section-label">
                    System
                </div>

                <a href="{{ route('admin.alerts') }}"
                    class="nav-item
                {{ request()->routeIs('admin.alerts*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i>
                    <span>Alerts</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>

                <a href="{{ route('logout') }}" class="nav-item"
                    onclick="
                event.preventDefault();
                document
                    .getElementById('logout-form')
                    .submit();
            ">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign Out</span>
                </a>

            </nav>

            <div class="sidebar-footer">

                <div class="blueprint-watermark"></div>

                <div class="sidebar-slogan">
                    We Build Trust.
                </div>

            </div>

        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="main">

            <div class="topbar">

                <div class="topbar-left">

                    <button type="button" id="sidebarToggle" class="d-lg-none" aria-label="Open navigation">
                        <i class="bi bi-list"></i>
                    </button>

                    <div class="topbar-title">
                        @yield('page_title', 'Management Dashboard')
                    </div>

                </div>

                <div class="topbar-right">

                    <span class="me-3">
                        {{ now()->format('D, d M Y') }}
                    </span>

                </div>

            </div>

            <div class="content">
                <div id="silentReloadContent">
                    @yield('content')
                </div>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggles = document.querySelectorAll('#sidebarToggle');

        const popup = document.getElementById('notificationPopup');
        const bells = document.querySelectorAll('.notification-toggle-btn');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const notificationMarkReadUrlTemplate = "{{ route('client.notifications.markRead', ['id' => '__ID__']) }}";

        const SILENT_RELOAD_INTERVAL = 7000;
        const CONTENT_SELECTOR = '#silentReloadContent';

        let isSilentReloading = false;

        function closeSidebar() {
            sidebar?.classList.remove('show');
            overlay?.classList.remove('show');
        }

        function toggleSidebar() {
            const isOpen = sidebar?.classList.contains('show');

            sidebar?.classList.toggle('show', !isOpen);
            overlay?.classList.toggle('show', !isOpen);
        }

        function toggleNotifications() {
            popup?.classList.toggle('show');
        }

        function initializeBootstrapComponents(root = document) {
            root.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (element) {
                if (!bootstrap.Popover.getInstance(element)) {
                    new bootstrap.Popover(element);
                }
            });

            root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
                if (!bootstrap.Tooltip.getInstance(element)) {
                    new bootstrap.Tooltip(element);
                }
            });
        }

        function captureInitialFormValues(root = document) {
            root.querySelectorAll('input, textarea, select').forEach(function (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.dataset.initialChecked = field.checked ? '1' : '0';
                } else {
                    field.dataset.initialValue = field.value ?? '';
                }
            });
        }

        function userIsTyping() {
            const active = document.activeElement;

            if (!active) {
                return false;
            }

            return (
                active.tagName === 'INPUT' ||
                active.tagName === 'TEXTAREA' ||
                active.tagName === 'SELECT' ||
                active.isContentEditable
            );
        }

        function modalIsOpen() {
            return document.querySelector('.modal.show') !== null;
        }

        function hasDirtyFormInput() {
            const fields = document.querySelectorAll('input, textarea, select');

            for (const field of fields) {
                if (
                    field.type === 'hidden' ||
                    field.type === 'submit' ||
                    field.type === 'button' ||
                    field.type === 'reset'
                ) {
                    continue;
                }

                if (field.type === 'checkbox' || field.type === 'radio') {
                    const initialChecked = field.dataset.initialChecked ?? (field.defaultChecked ? '1' : '0');
                    const currentChecked = field.checked ? '1' : '0';

                    if (initialChecked !== currentChecked) {
                        return true;
                    }
                } else {
                    const initialValue = field.dataset.initialValue ?? field.defaultValue ?? '';
                    const currentValue = field.value ?? '';

                    if (initialValue !== currentValue) {
                        return true;
                    }
                }
            }

            return false;
        }

        async function silentReloadContent() {
            const currentContent = document.querySelector(CONTENT_SELECTOR);

            if (!currentContent) {
                return;
            }

            if (
                isSilentReloading ||
                document.hidden ||
                userIsTyping() ||
                modalIsOpen() ||
                hasDirtyFormInput()
            ) {
                return;
            }

            try {
                isSilentReloading = true;

                const currentScrollY = window.scrollY;

                const response = await fetch(window.location.href, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Silent-Reload': 'true'
                    },
                    cache: 'no-store',
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    return;
                }

                const html = await response.text();
                const parser = new DOMParser();
                const newDocument = parser.parseFromString(html, 'text/html');
                const newContent = newDocument.querySelector(CONTENT_SELECTOR);

                if (!newContent) {
                    return;
                }

                currentContent.innerHTML = newContent.innerHTML;

                captureInitialFormValues(currentContent);
                initializeBootstrapComponents(currentContent);

                window.scrollTo({
                    top: currentScrollY,
                    behavior: 'instant'
                });

                document.dispatchEvent(new CustomEvent('silentReloadComplete'));
            } catch (error) {
                console.warn('Silent reload skipped:', error);
            } finally {
                isSilentReloading = false;
            }
        }

        toggles.forEach(function (toggle) {
            toggle?.addEventListener('click', toggleSidebar);
        });

        overlay?.addEventListener('click', closeSidebar);

        document.querySelectorAll('.sidebar .nav-item').forEach(function (item) {
            item.addEventListener('click', closeSidebar);
        });

        bells.forEach(function (bell) {
            bell?.addEventListener('click', function (event) {
                event.stopPropagation();
                toggleNotifications();
            });

            bell?.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleNotifications();
                }
            });
        });

        document.addEventListener('click', function (event) {
            const clickedBell = Array.from(bells).some(function (bell) {
                return bell?.contains(event.target);
            });

            if (!clickedBell && !popup?.contains(event.target)) {
                popup?.classList.remove('show');
            }
        });

        popup?.addEventListener('click', function (event) {
            const item = event.target.closest('.notification-item');

            if (!item) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const id = item.getAttribute('data-notif-id');
            const href = item.getAttribute('href');

            if (!id || !href) {
                return;
            }

            const markReadUrl = notificationMarkReadUrlTemplate.replace('__ID__', encodeURIComponent(id));

            fetch(markReadUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({})
            }).then(function (response) {
                if (!response.ok) {
                    console.error('Notification mark-read failed:', response.statusText);
                }
            }).catch(function (error) {
                console.error('Notification mark-read error:', error);
            }).finally(function () {
                window.location = href;
            });
        });

        initializeBootstrapComponents();
        captureInitialFormValues();

        setInterval(silentReloadContent, SILENT_RELOAD_INTERVAL);
    });
    </script>
    @stack('scripts')

</body>

</html>
