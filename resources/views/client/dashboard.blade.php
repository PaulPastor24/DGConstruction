@extends('layouts.client')

@section('title', 'Client Portal - Project Progress Dashboard')

@section('content')

    @include('client.partials.page-header', [
        'eyebrow' => 'Client Overview',
        'title' => 'Dashboard',
        'description' => 'Monitor your construction project in real time.',
        'extra' => view('client.partials.project-selector', ['allProjects' => $allProjects, 'primaryProject' => $primaryProject, 'primaryProjectName' => $primaryProjectName])
    ])

<div class="container-fluid p-0">
    @php
        $primaryProject = $primaryProject ?? $projects->first();
        $primaryProjectName = $primaryProjectName ?? optional($primaryProject)->project_name ?? 'No Project Assigned';
        $primaryLocation = trim((string) ($primaryProject?->project_location ?? $primaryProject?->location ?? $primaryProject?->location_address ?? ''));
        $nextMilestone = optional($upcomingMilestones)->first();
        $currentPhaseName = optional($currentPhases->first())->phase_name ?? 'Phase pending';
        $nextMilestoneName = optional($nextMilestone)->milestone_name ?? 'Milestone pending';
        $overviewSummary = "Project: {$primaryProjectName}. Progress: {$stats['overall_completion']}%. Current phase: {$currentPhaseName}. Next milestone: {$nextMilestoneName}.";
    @endphp
    
    <div class="hero-card mb-4">
        <div class="row align-items-center g-0">
            <div class="col-md-7 p-3 p-lg-4 hero-content">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge-project-status">CURRENT PROJECT</span>
                    <span class="project-status-pill {{ $stats['delayed_milestones_count'] > 0 ? 'status-delayed' : 'status-on-track' }}">{{ $stats['delayed_milestones_count'] > 0 ? 'Delayed' : 'On Track' }}</span>
                </div>
                <h1 class="project-title-text mt-1">{{ $primaryProjectName }}</h1>
                <p class="project-subtitle-text text-muted mb-2">{{ $primaryLocation !== '' ? $primaryLocation : 'Location Pending' }}</p>

                <div class="row mt-2 g-2">
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-calendar-check me-1"></i> Project Start</div>
                        <div class="meta-value">{{ $primaryProject?->start_date?->format('M d, Y') ?? 'TBD' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-calendar-x me-1"></i> Est. Completion</div>
                        <div class="meta-value">{{ $primaryProject?->target_end_date?->format('M d, Y') ?? 'TBD' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-person-workspace me-1"></i> Project Manager</div>
                        <div class="meta-value">{{ $primaryProject?->engineer?->name ?? 'Unassigned' }}</div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="meta-label"><i class="bi bi-building-gear me-1"></i> Site Supervisor</div>
                        <div class="meta-value">{{ $primaryProject?->activeSupervisor?->name ?? 'Not assigned' }}</div>
                    </div>
                </div>

                <div class="project-progress-embedded mt-3">
                    <div class="project-progress-header">
                        <span class="project-progress-label">Overall Completion</span>
                        <span class="project-progress-value">{{ $stats['overall_completion'] }}%</span>
                    </div>
                    <div class="project-progress-track" aria-label="Project completion progress bar">
                        <span style="width: {{ $stats['overall_completion'] }}%"></span>
                    </div>
                    <div class="project-progress-meta">
                        <span><i class="bi bi-flag-fill me-1"></i>{{ $currentPhaseName }}</span>
                        <span><i class="bi bi-calendar2-week me-1"></i>{{ optional($nextMilestone)->planned_date?->format('M d, Y') ?? 'TBD' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-5 d-none d-md-flex structural-img-container align-items-center justify-content-center">
                <div class="hero-image-wrap">
                    <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=1600&q=80" alt="Structural Progress Frame" class="hero-structural-image">
                    <div class="hero-image-overlay"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-mint-light"><i class="bi bi-building-gear text-success"></i></div>
                <div>
                    <div class="metric-title">Current Phase</div>
                    <div class="metric-main-val text-success" style="font-size: 1.25rem; font-weight:700; margin: 0.3rem 0;">
                        {{ optional($currentPhases->first())->phase_name ?? 'Structural Works' }}
                    </div>
                    <div class="metric-sub-text">In progress</div>
                </div>
            </div>
        </div>
        @php
            $scheduleHealth = $stats['delayed_milestones_count'] > 0 ? 'At Risk' : 'On Track';
            $scheduleHealthClass = $stats['delayed_milestones_count'] > 0 ? 'text-warning' : 'text-success';
            $scheduleHealthNote = $stats['delayed_milestones_count'] > 0 ? 'Delayed milestones detected' : 'No major delays';
        @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-mint-light"><i class="bi bi-shield-check {{ $scheduleHealthClass }}"></i></div>
                <div>
                    <div class="metric-title">Schedule Health</div>
                    <span class="metric-status-pill {{ $scheduleHealth === 'At Risk' ? 'status-delayed' : 'status-on-track' }}">{{ $scheduleHealth }}</span>
                    <div class="metric-sub-text">{{ $scheduleHealthNote }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-gray-light"><i class="bi bi-flag-fill text-success"></i></div>
                <div>
                    <div class="metric-title">Next Milestone</div>
                    <div class="metric-main-val text-success" style="font-size: 1.05rem; font-weight:700; line-height:1.2; margin:0.25rem 0;">
                        {{ optional($nextMilestone)->milestone_name ?? 'Next milestone pending' }}
                    </div>
                    <div class="metric-sub-text text-dark fw-semibold">{{ optional($nextMilestone)->planned_date?->format('M d, Y') ?? 'TBD' }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-gray-light"><i class="bi bi-file-earmark-check text-success"></i></div>
                <div>
                    <div class="metric-title">Latest Report Status</div>
                    <div class="metric-main-val text-success" style="font-size: 1.25rem; font-weight:700; margin: 0.3rem 0;">
                        {{ $recentReports->first()?->approval_status ?? 'Pending' }}
                    </div>
                    <div class="metric-sub-text">{{ $recentReports->count() > 0 ? 'Last uploaded report' : 'No report submitted' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-7 col-xl-8">
            <div class="dashboard-ui-panel">
                <div class="ui-panel-head d-flex justify-content-between align-items-center">
                    <h5 class="ui-panel-title">Recent Reports</h5>
                    <a href="{{ route('client.reports') }}" class="view-all-link">View All</a>
                </div>
                <div class="ui-panel-body p-0">
                    <div class="report-list-group">
                        @forelse($recentReports as $report)
                            <div class="report-item-row">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="file-icon-frame"><i class="bi bi-file-earmark-text"></i></div>
                                    <div>
                                        <h6>{{ optional($report->phase)->phase_name ?? 'Project Update' }}</h6>
                                        <p>{{ Str::limit($report->report_text ?? 'Report available', 48) }} &bull; {{ optional($report->report_date)->format('M d, Y') ?? 'Unknown' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('client.reports', ['project_id' => $report->project_id]) }}" class="download-icon-btn"><i class="bi bi-download"></i></a>
                            </div>
                        @empty
                            <div class="text-center p-4 text-muted">
                                <div class="mb-2 fs-3"><i class="bi bi-folder-x"></i></div>
                                <p class="m-0">No recent reports are available for this client profile.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-5 col-xl-4">
            <div class="dashboard-ui-panel">
                <div class="ui-panel-head d-flex justify-content-between align-items-center">
                    <h5 class="ui-panel-title">Recent Activity</h5>
                    <a href="{{ route('client.reports') }}" class="view-all-link">View All</a>
                </div>
                <div class="ui-panel-body">
                    <div class="activity-timeline-container">
                        @forelse($activityItems as $item)
                            <div class="activity-timeline-node">
                                <div class="node-icon {{ $item['variant'] }}">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="node-content">
                                    <div class="d-flex justify-content-between align-items-baseline gap-2">
                                        <h6 class="activity-node-title mb-1">{{ $item['title'] }}</h6>
                                        <span class="node-time flex-shrink-0">{{ $item['time'] }}</span>
                                    </div>
                                    <p class="activity-node-desc mb-0">{{ $item['subtitle'] }} &bull; <span class="text-dark fw-medium">{{ $item['author'] }}</span></p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-4 text-muted">
                                <div class="mb-2 fs-3"><i class="bi bi-clock-history"></i></div>
                                <p class="m-0">No recent activities are available yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<a href="mailto:{{ $primaryProject?->engineer?->email ?? 'support@dgconstruction.com' }}" class="support-assistant-widget" aria-label="Get help from the project support team">
    <span class="support-assistant-icon"><i class="bi bi-headset"></i></span>
    <span class="support-assistant-label">Need assistance?</span>
</a>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectorButton = document.getElementById('projectSelectorButton');
        const selectorMenu = document.getElementById('projectSelectorMenu');

        if (selectorButton && selectorMenu) {
            selectorButton.addEventListener('click', function (event) {
                event.stopPropagation();
                const willOpen = selectorMenu.hidden;
                selectorMenu.hidden = !willOpen;
                selectorButton.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            document.addEventListener('click', function (event) {
                if (!selectorButton.contains(event.target) && !selectorMenu.contains(event.target)) {
                    selectorMenu.hidden = true;
                    selectorButton.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
</script>

<div class="modal fade" id="projectOverviewModal" tabindex="-1" aria-labelledby="projectOverviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-bold" id="projectOverviewModalLabel">{{ optional($primaryProject)->project_name ?? 'Project details' }}</h5>
                    <p class="text-muted small mb-0">Live project summary pulled from the database</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Overall progress</div>
                            <div class="fw-bold fs-4 text-success">{{ $stats['overall_completion'] }}%</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Current phase</div>
                            <div class="fw-bold">{{ $currentPhaseName }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Next milestone</div>
                            <div class="fw-bold">{{ $nextMilestoneName }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3">
                            <div class="text-muted small text-uppercase">Assigned manager</div>
                            <div class="fw-bold">{{ optional(optional($primaryProject)->engineer)->name ?? 'Unassigned' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <a href="{{ route('client.timeline') }}" class="project-command-button-primary">View Timeline</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- HERO CONTAINER ACCENTING --- */
    .dashboard-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.15rem 0 0.8rem;
        margin-bottom: 0.2rem;
    }
    .dashboard-page-heading {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .dashboard-page-eyebrow {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #64748b;
    }
    .dashboard-page-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.15;
        margin: 0;
        color: #2a4028;
    }
    .dashboard-page-description {
        margin: 0.2rem 0 0;
        font-size: 0.92rem;
        font-weight: 500;
        color: #64748b;
        max-width: 420px;
    }
    .dashboard-page-tools {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    @media (max-width: 1024px) {
        .dashboard-page-header {
            padding: 0.35rem 0 0.75rem;
            align-items: flex-start;
            flex-direction: column;
        }
        .dashboard-page-tools {
            gap: 0.5rem;
            width: 100%;
            justify-content: space-between;
            flex-wrap: wrap;
        }
    }
    @media (max-width: 576px) {
        .dashboard-page-title {
            font-size: 1.45rem;
        }
        .dashboard-page-description {
            font-size: 0.85rem;
        }
    }
    .project-selector-wrap {
        position: relative;
    }
    .project-selector-button {
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
        min-height: 48px;
        padding: 0.7rem 0.9rem;
        border: 1px solid #dbe4dd;
        background: #ffffff;
        border-radius: 16px;
        font-size: 0.84rem;
        font-weight: 600;
        color: #334155;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        transition: all 0.2s ease;
    }
    .project-selector-button:disabled {
        cursor: not-allowed;
        opacity: 0.7;
        box-shadow: none;
    }
    .project-selector-button:hover,
    .project-selector-button:focus {
        border-color: #2E7D32;
        box-shadow: 0 10px 20px rgba(46, 125, 50, 0.08);
        background: #f8fffb;
    }
    .project-selector-button[aria-expanded="true"] {
        border-color: #2E7D32;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.08);
    }
    .project-selector-button[aria-expanded="true"] .project-selector-caret {
        transform: rotate(180deg);
    }
    .project-selector-icon,
    .project-selector-item-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f0fdf4;
        color: #2E7D32;
        flex-shrink: 0;
    }
    .project-selector-label {
        max-width: 190px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .project-selector-caret {
        transition: transform 0.2s ease;
    }
    .project-selector-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: min(320px, 90vw);
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.14);
        padding: 0.4rem;
        z-index: 30;
        opacity: 0;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .project-selector-menu:not([hidden]) {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    .project-selector-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        padding: 0.7rem 0.8rem;
        border-radius: 12px;
        text-decoration: none;
        color: #334155;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .project-selector-item:hover,
    .project-selector-item.active {
        background: #edf8ef;
        color: #0f3d2e;
    }
    .project-selector-check {
        margin-left: auto;
        font-size: 1.05rem;
        color: #2E7D32;
    }
    .dashboard-date-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.6rem 0.9rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 14px;
        font-size: 0.84rem;
        font-weight: 600;
        color: #334155;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .dashboard-date-pill i {
        color: #16a34a;
    }
    .dashboard-notification-button {
        position: relative;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #334155;
    }
    .dashboard-notification-button:hover {
        background: #f8fafc;
    }
    .dashboard-notification-button.notification-bell-animate {
        animation: bell-ring 1.2s ease-in-out infinite, pulse-soft 1.45s ease-out infinite;
        transform-origin: center top;
        color: #22c55e;
        background: #f0fdf4;
        border-color: #22c55e;
        position: relative;
    }
    .dashboard-notification-button.notification-bell-animate::before {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 999px;
        border: 2px solid rgba(34, 197, 94, 0.28);
        animation: ring-pulse 1.45s ease-out infinite;
        pointer-events: none;
    }
    .dashboard-notification-button.notification-bell-animate .bi-bell {
        color: #22c55e;
        z-index: 1;
    }
    @keyframes bell-ring {
        0%, 100% { transform: rotate(0deg); }
        10% { transform: rotate(12deg); }
        20% { transform: rotate(-10deg); }
        30% { transform: rotate(8deg); }
        40% { transform: rotate(-6deg); }
        50% { transform: rotate(4deg); }
        60% { transform: rotate(-2deg); }
        70% { transform: rotate(2deg); }
        80%, 90% { transform: rotate(0deg); }
    }
    .notification-badge {
        position: absolute;
        top: 4px;
        right: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 0.25rem;
        border: 2px solid #ffffff;
        border-radius: 999px;
        background: #22c55e;
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 700;
        line-height: 1;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
        animation: ping-dot 1.4s ease-out infinite;
    }
    @keyframes ping-dot {
        0% { transform: scale(0.9); opacity: 1; }
        80% { transform: scale(1.65); opacity: 0; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    @keyframes pulse-soft {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.24); }
        70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    @keyframes ring-pulse {
        0% { transform: scale(0.92); opacity: 0.9; }
        70% { transform: scale(1.12); opacity: 0; }
        100% { transform: scale(1.16); opacity: 0; }
    }
    .hero-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        min-height: auto;
        box-shadow: 0 6px 24px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.4rem;
    }
    .hero-card .row {
        min-height: auto;
    }
    .hero-card .col-md-7 {
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
    }
    .badge-project-status {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #16a34a;
        background-color: #f0fdf4;
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
    }
    .project-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.62rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .project-status-pill.status-on-track {
        background: #ecfdf3;
        color: #166534;
    }
    .project-status-pill.status-delayed {
        background: #fffbeb;
        color: #b45309;
    }
    .project-title-text {
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.35rem;
        line-height: 1.1;
    }
    .project-subtitle-text {
        font-size: 0.9rem;
        margin-bottom: 0.9rem;
        max-width: 640px;
        line-height: 1.5;
    }
    .project-progress-embedded {
        background: linear-gradient(180deg, #f8fffb 0%, #f8fafc 100%);
        border: 1px solid #e2f8ea;
        border-radius: 18px;
        padding: 1rem;
    }
    .project-progress-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.55rem;
    }
    .project-progress-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
    }
    .project-progress-value {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .project-progress-track {
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .project-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
    }
    .project-progress-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.65rem;
        font-size: 0.74rem;
        font-weight: 600;
        color: #475569;
    }
    .project-progress-meta span {
        display: inline-flex;
        align-items: center;
    }
    .hero-content { position: relative; z-index: 3; }

    /* Hero CTA buttons */
    .hero-ctas { margin-top: 1rem; display:flex; gap:0.5rem; flex-wrap:wrap; }
    .hero-ctas .btn { padding: 0.6rem 1rem; border-radius: 10px; font-weight:700; }

    /* Hero image wrap + overlay (slide look) */
    .hero-image-wrap { position: relative; width: 100%; height: 100%; overflow: hidden; }
    .hero-image-overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(2,6,23,0.55) 0%, rgba(2,6,23,0.12) 40%, rgba(2,6,23,0.0) 100%); pointer-events: none; }
    .hero-structural-image { width: 130%; height: 100%; object-fit: cover; transform: translateX(10%); display:block; }

    @media (max-width: 991px) {
        .hero-structural-image { width: 150%; transform: translateX(20%); }
        .structural-img-container { display: none !important; }
    }
    .meta-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 0.15rem;
    }
    .meta-value {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
    }
    .structural-img-container {
        position: relative;
        overflow: hidden;
        height: 100%;
        padding: 0.75rem;
        isolation: isolate;
        clip-path: polygon(18% 0, 100% 0, 100% 100%, 0% 100%);
    }
    .hero-image-wrap {
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 240px;
        border-radius: 18px;
        overflow: hidden;
    }
    .hero-image-wrap::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(22, 163, 74, 0.20), transparent 35%, transparent 65%, rgba(15, 23, 42, 0.20));
        z-index: 1;
        pointer-events: none;
    }
    .hero-structural-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(2,6,23,0.08);
        transform: scale(1.02);
    }

    @media (min-width: 1400px) {
        .hero-structural-image { max-height: 300px; }
    }

    /* --- METRIC CARD GRID LOOKS --- */
    .metric-status-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        height: 100%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-status-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
    }
    .metric-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .bg-mint-light { background-color: #f0fdf4; }
    .bg-gray-light { background-color: #f8fafc; }
    .metric-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: capitalize;
    }
    .metric-status-pill {
        display: inline-flex;
        margin-top: 0.2rem;
        padding: 0.3rem 0.55rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .metric-status-pill.status-on-track {
        background: #ecfdf3;
        color: #166534;
    }
    .metric-status-pill.status-delayed {
        background: #fffbeb;
        color: #b45309;
    }
    .metric-main-val {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0.15rem 0;
        line-height: 1.1;
    }
    .metric-sub-text {
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    /* --- REUSABLE UI BOXES --- */
    .dashboard-ui-panel {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        height: 100%;
        box-shadow: 0 4px 16px rgba(0,0,0,0.01);
        display: flex;
        flex-direction: column;
    }
    .ui-panel-head {
        padding: 1.5rem 1.5rem 1rem 1.5rem;
        border-bottom: 0;
    }
    .ui-panel-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }
    .ui-panel-body {
        padding: 0 1.5rem 1.5rem 1.5rem;
        flex-grow: 1;
    }
    .view-all-link {
        font-size: 0.85rem;
        font-weight: 700;
        color: #16a34a;
        text-decoration: none;
    }

    /* --- CHART SYSTEM LOOK --- */
    .donut-wrapper {
        width: 180px;
        height: 180px;
    }
    .donut-center-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }
    .donut-center-text h3 {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
    }
    .donut-center-text span {
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }
    .text-sm { font-size: 0.85rem; }
    .font-semibold { font-weight: 600; }
    .rounded-xl { border-radius: 12px !important; }

    /* --- REPORT LISTS STREAMING --- */
    .report-list-group {
        display: flex;
        flex-direction: column;
    }
    .report-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s ease;
        border-radius: 18px;
        margin-bottom: 0.75rem;
        background: #ffffff;
    }
    .report-item-row:last-child { border-bottom: 0; margin-bottom: 0; }
    .report-item-row:hover { background-color: #f8fafc; }
    .file-icon-frame {
        width: 40px;
        height: 40px;
        background-color: #f1f5f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: var(--text-muted);
    }
    .report-item-row h6 {
        font-size: 0.88rem;
        font-weight: 700;
        margin: 0 0 0.15rem 0;
    }
    .report-item-row p {
        font-size: 0.78rem;
        color: var(--text-muted);
        margin: 0;
    }
    .download-icon-btn {
        color: var(--text-muted);
        font-size: 1.1rem;
        padding: 0.25rem;
    }
    .download-icon-btn:hover { color: var(--text-primary); }

    /* --- TIMELINE NODES LISTS (UNIFIED & CLEANED UP) --- */
    .activity-timeline-container {
        position: relative;
        padding-left: 2rem;
        margin-left: 0.75rem;
        margin-top: 0.75rem;
    }
    /* Creates the unified tracking background line */
    .activity-timeline-container::before {
        content: '';
        position: absolute;
        top: 8px;
        bottom: 8px;
        left: 11px; /* Centers the path behind the 24px wide circle indicator */
        width: 2px;
        background-color: #e2e8f0;
        border-radius: 2px;
    }
    .activity-timeline-node {
        position: relative;
        padding-bottom: 1.75rem;
    }
    .activity-timeline-node:last-child { 
        padding-bottom: 0; 
    }
    /* Fixed overlapping layouts by positioning the node metrics smoothly */
    .node-icon {
        position: absolute;
        left: -2rem;
        top: 2px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        z-index: 2;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px #ffffff; /* Blocks out line under the icon path */
    }
    
    /* Activity node state modifiers */
    .node-icon.bg-light-green { background-color: #f0fdf4; color: #16a34a; border: 1px solid #16a34a; }
    .node-icon.bg-green-solid { background-color: #22c55e; color: #ffffff; border: 1px solid #22c55e; }
    
    /* Elegant variations case handlers if variables use custom colors */
    .node-icon:not(.bg-light-green):not(.bg-green-solid) {
        background-color: #f8fafc;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }

    .node-content {
        padding-left: 0.5rem;
    }
    .activity-node-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
    }
    .activity-node-desc {
        font-size: 0.8rem;
        color: #64748b;
    }
    .node-time {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 500;
    }

    /* --- FOOTER CTA BOXES --- */
    .support-assistant-widget {
        position: fixed;
        right: 2rem;
        bottom: 2rem;
        z-index: 1040;
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.7rem 0.75rem 0.7rem 0.7rem;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        border-radius: 999px;
        box-shadow: 0 14px 30px rgba(22, 163, 74, 0.28);
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .support-assistant-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(22, 163, 74, 0.32);
    }
    .support-assistant-icon {
        width: 56px;
        height: 56px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        font-size: 1.6rem;
        flex-shrink: 0;
    }
    .support-assistant-label {
        font-size: 0.92rem;
        font-weight: 700;
        color: #ffffff;
        white-space: nowrap;
        padding-right: 0.2rem;
    }

    @media (max-width: 768px) {
        .dashboard-page-header {
            align-items: stretch;
            flex-direction: column;
        }
        .dashboard-page-tools {
            width: 100%;
            justify-content: space-between;
        }
        .dashboard-date-pill {
            flex: 1;
            justify-content: center;
        }
        .support-assistant-widget {
            right: 1rem;
            bottom: 1rem;
            padding: 0.55rem;
        }
        .support-assistant-label {
            display: none;
        }
        .support-assistant-icon {
            width: 52px;
            height: 52px;
        }
    }
</style>
@endsection