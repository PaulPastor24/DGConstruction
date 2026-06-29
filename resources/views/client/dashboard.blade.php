@extends('layouts.client')

@section('title', 'Client Portal - Project Progress Dashboard')

@section('content')

    <div class="mb-4">
       
        <h2 class="fw-extrabold text-dark m-0 mt-1" style="font-size: 1.75rem; font-weight: 800;">Dashboard</h2>
        <p class="text-muted mb-0 mt-1" style="font-size: 0.875rem;">Real-time project progress overview and milestone tracking for your construction portfolio.</p>
        
    </div>

<div class="container-fluid p-0">
    @php
        $primaryProject = $projects->first();
        $primaryProjectName = optional($primaryProject)->project_name ?? 'Project Overview';
        $nextMilestone = optional($upcomingMilestones)->first();
        $currentPhaseName = optional($currentPhases->first())->phase_name ?? 'Phase pending';
        $nextMilestoneName = optional($nextMilestone)->milestone_name ?? 'Milestone pending';
        $overviewSummary = "Project: {$primaryProjectName}. Progress: {$stats['overall_completion']}%. Current phase: {$currentPhaseName}. Next milestone: {$nextMilestoneName}.";
    @endphp
    
    <div class="hero-card mb-4">
        <div class="row align-items-center g-0">
            <div class="col-md-7 p-3 p-lg-4 hero-content">
                <span class="badge-project-status">CURRENT PROJECT</span>
                <h1 class="project-title-text mt-1">{{ $primaryProjectName }}</h1>
                <p class="project-subtitle-text text-muted mb-2">{{ $primaryProject?->project_location ?? 'Construction site summary' }}</p>

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
                <div class="metric-icon-box bg-mint-light"><i class="bi bi-graph-up text-success"></i></div>
                <div>
                    <div class="metric-title">Overall Progress</div>
                    <div class="metric-main-val text-success">{{ $stats['overall_completion'] }}%</div>
                    <div class="metric-sub-text">Project completion</div>
                </div>
            </div>
        </div>
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
                    <div class="metric-main-val {{ $scheduleHealthClass }}" style="font-size: 1.4rem;">{{ $scheduleHealth }}</div>
                    <div class="metric-sub-text">{{ $scheduleHealthNote }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-status-card">
                <div class="metric-icon-box bg-gray-light"><i class="bi bi-flag-fill text-muted"></i></div>
                <div>
                    <div class="metric-title">Next Milestone</div>
                    <div class="metric-main-val text-success" style="font-size: 1.05rem; font-weight:700; line-height:1.2; margin:0.25rem 0;">
                        {{ optional($nextMilestone)->milestone_name ?? 'Next milestone pending' }}
                    </div>
                    <div class="metric-sub-text text-dark fw-semibold">{{ optional($nextMilestone)->planned_date?->format('M d, Y') ?? 'TBD' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="dashboard-ui-panel project-progress-overview-panel project-detail-trigger"
                 data-bs-toggle="popover"
                 data-bs-trigger="hover focus"
                 data-bs-placement="top"
                 data-bs-title="Project Progress Snapshot"
                 data-bs-content="{{ e($overviewSummary) }}">
                <div class="ui-panel-head">
                    <h5 class="ui-panel-title">Project Progress Overview</h5>
                </div>
                <div class="ui-panel-body text-center py-4">
                    <div class="donut-wrapper position-relative mx-auto mb-4">
                        <svg width="180" height="180" viewBox="0 0 42 42" class="donut-svg">
                            <circle class="donut-hole" cx="21" cy="21" r="15.91549430918954" fill="#fff"></circle>
                            <circle class="donut-ring" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#e2e8f0" stroke-width="3"></circle>
                            <circle class="donut-segment" cx="21" cy="21" r="15.91549430918954" fill="transparent" stroke="#22c55e" stroke-width="3" stroke-dasharray="{{ $stats['overall_completion'] }} {{ 100 - $stats['overall_completion'] }}" stroke-dashoffset="25"></circle>
                        </svg>
                        <div class="donut-center-text">
                            <h3>{{ $stats['overall_completion'] }}%</h3>
                            <span>Completed</span>
                        </div>
                    </div>
                    
                    <div class="donut-legend-list text-start px-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><span class="legend-dot bg-success"></span> Completed</span>
                            <strong>{{ $stats['overall_completion'] }}%</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><span class="legend-dot bg-warning"></span> In Progress</span>
                            <strong>{{ number_format(max(0, min(100, 100 - $stats['overall_completion'])), 1) }}%</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><span class="legend-dot bg-secondary"></span> Not Started</span>
                            <strong>{{ optional($upcomingMilestones)->count() }}</strong>
                        </div>
                    </div>
                    
                    <hr class="my-4 border-slate">
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('client.timeline') }}" class="btn btn-outline-secondary w-100 py-2 font-semibold text-sm rounded-xl"><i class="bi bi-calendar-week me-2"></i>View Full Timeline</a>
                        <button type="button" class="btn btn-success w-100 py-2 font-semibold text-sm rounded-xl project-detail-trigger" data-bs-toggle="modal" data-bs-target="#projectOverviewModal"><i class="bi bi-info-circle me-2"></i>Show Details</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
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

        <div class="col-12 col-md-6 col-xl-4">
            <div class="dashboard-ui-panel">
                <div class="ui-panel-head d-flex justify-content-between align-items-center">
                    <h5 class="ui-panel-title">Recent Activity</h5>
                    <a href="{{ route('client.reports') }}" class="view-all-link">View All</a>
                </div>
                <div class="ui-panel-body">
                                @php
                        $activityItems = $recentReports->take(4)->map(function ($report) {
                            return [
                                'title' => optional($report->phase)->phase_name ? ('Report: ' . optional($report->phase)->phase_name) : 'Project report submitted',
                                'subtitle' => Str::limit($report->report_text ?? 'Report details available', 60),
                                'time' => optional($report->report_date)->format('M d, Y') ?? 'Unknown',
                                'author' => optional($report->submittedBy)->name ?? 'Project team',
                                'icon' => 'bi bi-file-earmark-text',
                                'variant' => 'bg-light-green text-dark'
                            ];
                        });
                    @endphp

                    <div class="activity-timeline-container">
                        @forelse($activityItems as $item)
                            <div class="activity-timeline-node">
                                <div class="node-icon {{ $item['variant'] }}"><i class="{{ $item['icon'] }}"></i></div>
                                <div class="node-content">
                                    <div class="d-flex justify-content-between">
                                        <h6>{{ $item['title'] }}</h6>
                                        <span class="node-time">{{ $item['time'] }}</span>
                                    </div>
                                    <p>{{ $item['subtitle'] }} &bull; {{ $item['author'] }}</p>
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

    <div class="row g-3 mt-3">
        <div class="col-12 col-md-6">
            <div class="action-alert-banner py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="alert-banner-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div>
                        <h6 class="m-0 fw-bold">Schedule Insight</h6>
                        <p class="m-0 text-muted text-sm">Some milestones are approaching their target dates.</p>
                    </div>
                </div>
                <a href="{{ route('client.timeline') }}" class="btn btn-white shadow-sm font-semibold rounded-xl px-3 py-2 text-sm">View Timeline</a>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="action-alert-banner py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="alert-banner-icon bg-mint-circle"><i class="bi bi-headset"></i></div>
                    <div>
                        <h6 class="m-0 fw-bold">Need Assistance?</h6>
                        <p class="m-0 text-muted text-sm">Contact your Project Engineer for any clarifications.</p>
                    </div>
                </div>
                <a href="mailto:{{ $primaryProject?->engineer?->email ?? 'support@dgconstruction.com' }}" class="btn btn-outline-dark font-semibold rounded-xl px-3 py-2 text-sm">Message Engineer</a>
            </div>
        </div>
    </div>

</div>

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
                <a href="{{ route('client.timeline') }}" class="btn btn-success">Open timeline</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- HERO CONTAINER ACCENTING --- */
    .hero-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid var(--border-color);
        overflow: hidden;
        min-height: auto;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
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
        margin-bottom: 0.75rem;
        display: inline-flex;
        align-items: center;
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
        margin-bottom: 0.9rem;
        max-width: 640px;
        line-height: 1.5;
    }
    .project-subtitle-text {
        font-size: 0.95rem;
        margin: 0;
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
        clip-path: polygon(12% 0, 100% 0, 100% 100%, 0% 100%);
        height: 100%;
        padding: 0.5rem;
    }
    .hero-structural-image {
        width: auto;
        max-height: 220px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(2,6,23,0.06);
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

    /* --- TIMELINE NODES LISTS --- */
    .activity-timeline-container {
        position: relative;
        padding-left: 1.5rem;
        border-left: 2px solid #e2e8f0;
        margin-left: 0.75rem;
        margin-top: 0.5rem;
    }
    .activity-timeline-node {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .activity-timeline-node:last-child { margin-bottom: 0; }
    .node-icon {
        position: absolute;
        left: calc(-1.5rem - 13px);
        top: 0;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }
    .bg-light-green { background-color: #f0fdf4; color: #16a34a; border: 2px solid #fff; }
    .bg-green-solid { background-color: #22c55e; border: 2px solid #fff; }
    .node-content h6 {
        font-size: 0.88rem;
        font-weight: 700;
        margin: 0 0 0.15rem 0;
    }
    .node-content p {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 0;
    }
    .node-time {
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    /* --- FOOTER CTA BOXES --- */
    .action-alert-banner {
        background-color: #fff;
        border: 1px solid var(--border-color);
        border-radius: 18px;
    }
    .alert-banner-icon {
        width: 40px;
        height: 40px;
        background-color: #fff7ed;
        color: #f97316;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .bg-mint-circle {
        background-color: #f0fdf4;
        color: #16a34a;
    }
    .btn-white {
        background-color: #fff;
        border: 1px solid #cbd5e1;
    }
    .btn-white:hover {
        background-color: #f8fafc;
    }
</style>
@endsection