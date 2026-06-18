<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - D&G Construction Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-primary: #11141a;
            --bg-secondary: #171b26;
            --border-color: #262c3d;
            --accent-color: #ffb300; /* Warm warning yellow for field ops */
        }
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg-primary);
            color: #ffffff;
        }
        .heading-syne {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
        }
        .sidebar {
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            min-height: 100vh;
        }
        .nav-link-custom {
            color: #a0aec0;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            border-bottom: 1px solid rgba(38, 44, 61, 0.3);
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            color: #ffffff;
            background-color: rgba(255, 179, 0, 0.05);
            border-left: 4px solid var(--accent-color);
        }
        .card-custom {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .form-input-custom {
            background-color: var(--bg-primary);
            border: 1px solid var(--border-color);
            color: white;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 0.875rem;
            width: 100%;
        }
        .form-input-custom:focus {
            background-color: var(--bg-primary);
            color: white;
            border-color: var(--accent-color);
            box-shadow: none;
            outline: none;
        }
        .status-dot {
            height: 8px;
            width: 8px;
            background-color: var(--accent-color);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px var(--accent-color);
        }
    </style>
</head>
<body>

<div class="container-fluid m-0 p-0">
    <div class="row g-0">
        <div class="col-md-3 col-lg-2 sidebar p-0 d-flex flex-column justify-content-between">
            <div>
                <div class="p-4 border-bottom border-secondary d-flex align-items-center gap-2">
                    <div class="text-dark rounded px-2 py-1 heading-syne fw-bold" style="background-color: var(--accent-color);">SV</div>
                    <span class="heading-syne tracking-wider text-uppercase fs-6">CoreConstruct</span>
                </div>
                <div class="nav flex-column">
                    <a href="#" class="nav-link-custom active"><i class="bi bi-shield-check"></i> Field Operations</a>
                    <a href="#" class="nav-link-custom"><i class="bi bi-person-bounding-box"></i> Biometric Inputs</a>
                    <a href="#" class="nav-link-custom"><i class="bi bi-truck"></i> Logistics Tracker</a>
                </div>
            </div>
            
            <div class="p-3 border-top border-secondary">
                <div class="mb-3 px-2">
                    <p class="m-0 small text-white fw-bold">{{ $user->name }}</p>
                    <p class="m-0 small text-muted text-uppercase font-mono tracking-tighter" style="font-size: 10px;">Field Supervisor</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 py-2">
                        <i class="bi bi-box-arrow-left"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4" style="max-height: 100vh; overflow-y: auto;">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
                <div>
                    <h1 class="heading-syne fs-2 m-0" style="color: var(--accent-color);">Field Operations Command</h1>
                    <p class="text-muted small m-0">On-site biometric logging interfaces, attendance summaries, and daily logistics tracking.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-dot"></span>
                    <span class="text-muted small font-mono">SUPERVISOR_NODE_ACTIVE</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card-custom">
                        <span class="text-muted small text-uppercase">Labor Attendance</span>
                        <h2 class="heading-syne my-2 text-warning">42 <span class="fs-6 text-muted">Checked In</span></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom">
                        <span class="text-muted small text-uppercase">Active Deliveries</span>
                        <h2 class="heading-syne my-2 text-info">8 <span class="fs-6 text-muted">Hauling Trucks</span></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom">
                        <span class="text-muted small text-uppercase">Incident Index</span>
                        <h2 class="heading-syne my-2 text-success">0 Clear</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card-custom">
                        <h4 class="heading-syne fs-5 mb-3">Daily Construction Report Logger</h4>
                        <div class="mb-3">
                            <label class="text-muted small mb-1 uppercase">Field Activity Entries</label>
                            <textarea rows="4" class="form-input-custom" placeholder="Document structure pouring progression, rebar structural bindings, or logistics bottlenecks..."></textarea>
                        </div>
                        <button class="btn btn-warning w-100 fw-bold text-dark btn-sm py-2">Publish Field Logs</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-custom">
                        <h4 class="heading-syne fs-5 mb-3">Biometric Video Capture Node</h4>
                        <div class="bg-dark rounded d-flex flex-column align-items-center justify-content-center text-center p-4 border border-secondary" style="height: 165px; background-color: #0b0d12 !important;">
                            <i class="bi bi-camera-video fs-2 text-muted mb-2"></i>
                            <span class="text-muted small fw-mono">SCANNER_NODE_STANDBY</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>