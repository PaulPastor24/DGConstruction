<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'D&G Construction Monitor')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .heading-syne {
            font-family: 'Syne', sans-serif;
        }
        .sidebar {
            background-color: #eefaf4;
            min-height: 100vh;
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            padding: 24px;
            border-right: 1px solid #e2ebd9;
        }
        .main-content {
            margin-left: 260px;
            padding: 40px;
        }
        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: #495057;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }
        .nav-link-custom:hover {
            background-color: rgba(25, 135, 84, 0.05);
            color: #198754;
        }
        .nav-link-custom.active {
            background-color: #d1f2e1;
            color: #198754;
            font-weight: 600;
        }
        .section-title-muted {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6c757d;
            margin-top: 24px;
            margin-bottom: 12px;
            padding-left: 16px;
        }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column justify-content-between">
        <div>
            <div class="mb-4 px-2">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/image.png') }}" alt="D&G" style="width: 32px; height: 32px; object-fit: contain;">
                    <div>
                        <div class="heading-syne text-success fw-bold m-0" style="font-size: 16px; color: #198754 !important;">Client Portal</div>
                    </div>
                </div>
                <div class="text-muted mt-1" style="font-size: 11px; font-weight: 500;">D&G Dev't Corporation</div>
            </div>

            <div class="section-title-muted">Overview</div>
            <a href="{{ route('client.dashboard') }}" class="nav-link-custom {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                Project Status
            </a>
            <a href="{{ route('client.timeline') }}" class="nav-link-custom {{ request()->routeIs('client.timeline') ? 'active' : '' }}">
                Timeline & Phases
            </a>

            <div class="section-title-muted">Updates</div>
            <a href="{{ route('client.updates') }}" class="nav-link-custom {{ request()->routeIs('client.updates') ? 'active' : '' }}">
                Site Updates
            </a>

            <div class="section-title-muted">System</div>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link-custom text-danger">
                Sign Out
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>

        <div class="d-flex align-items-center gap-3 pt-3 border-top" style="border-color: #e2ebd9 !important;">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                {{ substr(Auth::user()->name ?? 'CU', 0, 2) }}
            </div>
            <div>
                <h6 class="m-0 fw-bold text-dark" style="font-size: 13px;">{{ Auth::user()->name ?? 'Client User' }}</h6>
                <small class="text-muted" style="font-size: 11px;">Client Access</small>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium" style="font-size: 12px;">
                PROJECT STATUS
            </span>
            <div class="d-flex align-items-center gap-3">
                <small class="text-muted fw-medium">{{ date('D, d M Y') }}</small>
                <a href="{{ route('client.timeline') }}" class="btn btn-success btn-sm px-3 py-2 fw-bold heading-syne" style="border-radius: 6px; background-color: #198754; border: none; font-size: 12px;">
                    View Timeline
                </a>
            </div>
        </div>
        
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>