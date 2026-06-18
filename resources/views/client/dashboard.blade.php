<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Portal - D&G Construction Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-primary: #11141a;
            --bg-secondary: #171b26;
            --border-color: #262c3d;
            --accent-color: #00b0ff; /* Clean dynamic blue for corporate interface */
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
            background-color: rgba(0, 176, 255, 0.05);
            border-left: 4px solid var(--accent-color);
        }
        .card-custom {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .status-dot {
            height: 8px;
            width: 8px;
            background-color: var(--accent-color);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px var(--accent-color);
        }
        .timeline-track {
            border-left: 2px solid var(--border-color);
            padding-left: 20px;
            position: relative;
        }
        .timeline-dot {
            position: absolute;
            left: -6px;
            top: 6px;
            height: 10px;
            width: 10px;
            border-radius: 50%;
            background-color: var(--border-color);
        }
        .timeline-dot.active {
            background-color: var(--accent-color);
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
                    <div class="text-dark rounded px-2 py-1 heading-syne fw-bold" style="background-color: var(--accent-color);">CL</div>
                    <span class="heading-syne tracking-wider text-uppercase fs-6">CoreConstruct</span>
                </div>
                <div class="nav flex-column">
                    <a href="#" class="nav-link-custom active"><i class="bi bi-eye"></i> Project Progress</a>
                    <a href="#" class="nav-link-custom"><i class="bi bi-file-earmark-bar-graph"></i> Financial Status</a>
                </div>
            </div>
            
            <div class="p-3 border-top border-secondary">
                <div class="mb-3 px-2">
                    <p class="m-0 small text-white fw-bold">{{ $user->name }}</p>
                    <p class="m-0 small text-muted text-uppercase font-mono tracking-tighter" style="font-size: 10px;">Corporate Client</p>
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
                    <h1 class="heading-syne fs-2 m-0" style="color: var(--accent-color);">Project Progress Portal</h1>
                    <p class="text-muted small m-0">Transparent milestone oversight, construction metrics, and timeline transparency verification.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-dot"></span>
                    <span class="text-muted small font-mono">PORTAL_SECURE</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card-custom">
                        <span class="text-muted small text-uppercase">Overall Progress</span>
                        <h2 class="heading-syne my-2 text-info">68% <span class="fs-6 text-muted">Complete</span></h2>
                        <div class="progress bg-dark mt-2" style="height: 4px;">
                            <div class="progress-bar" style="width: 68%; background-color: var(--accent-color);"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom">
                        <span class="text-muted small text-uppercase">Next Critical Phase</span>
                        <h2 class="heading-syne my-2 fs-4 text-white pt-1">Slab Pouring - Level 4</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-custom">
                        <span class="text-muted small text-uppercase">Disbursement Status</span>
                        <h2 class="heading-syne my-2 text-success">Clear</h2>
                    </div>
                </div>
            </div>

            <div class="card-custom">
                <h4 class="heading-syne fs-5 mb-4">Development Milestones</h4>
                <div class="ms-2">
                    <div class="timeline-track pb-4">
                        <span class="timeline-dot active"></span>
                        <h5 class="fs-6 text-info m-0 fw-bold">Foundation Structural Matrix Completed</h5>
                        <p class="text-muted small m-0">Excavation logs fully validated by Project Engineering Office.</p>
                    </div>
                    <div class="timeline-track">
                        <span class="timeline-dot"></span>
                        <h5 class="fs-6 text-white m-0">Vertical Structural Framing (In Progress)</h5>
                        <p class="text-muted small m-0">Ongoing concrete operations monitored via system sensors.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>