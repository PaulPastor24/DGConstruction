@extends('layouts.supervisor')

@section('title', 'Supervisor Dashboard - Field Operations Command')

@section('content')
<div class="supervisor-dashboard">
    <section class="dashboard-shell">
        <div class="dashboard-top row g-3 align-items-stretch">
            <div class="col-12 col-xl-8">
                <div class="hero-panel">
                    <div>
                        <p class="eyebrow">Site Supervisor</p>
                        <h1>Field Operations Command</h1>
                    </div>
                    <div class="hero-meta">
                        <span><i class="bi bi-geo-alt-fill"></i> {{ optional($assignedProjects->first())->project_location ?? 'Assigned Site' }}</span>
                        <span><i class="bi bi-calendar-event"></i> {{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="project-focus-card">
                    <p class="small-label">Current Assigned Project</p>
                    <h3>{{ optional($assignedProjects->first())->project_name ?? 'No Project Assigned' }}</h3>
                    <div class="phase-pill">
                        <i class="bi bi-cone-striped"></i> {{ optional($currentPhases->first())->phase_name ?? 'No active phase' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-grid row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card green-card">
                    <div><span class="stat-icon"><i class="bi bi-briefcase-fill"></i></span></div>
                    <div>
                        <div class="stat-value">{{ $stats['total_projects'] }}</div>
                        <div class="stat-label">Assigned Projects</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card yellow-card">
                    <div><span class="stat-icon"><i class="bi bi-lightning-charge-fill"></i></span></div>
                    <div>
                        <div class="stat-value">{{ $stats['current_phases'] }}</div>
                        <div class="stat-label">Current Phase</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card accent-card">
                    <div><span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span></div>
                    <div>
                        <div class="stat-value">{{ $stats['average_completion'] }}%</div>
                        <div class="stat-label">Completion</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card warning-card">
                    <div><span class="stat-icon"><i class="bi bi-file-earmark-text"></i></span></div>
                    <div>
                        <div class="stat-value">{{ $stats['pending_reports'] }}</div>
                        <div class="stat-label">Pending Reports</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="quick-actions mb-4">
            <a href="{{ route('supervisor.reports') }}" class="action-btn primary-btn">
                <i class="bi bi-plus-circle"></i>
                Submit Accomplishment Report
            </a>
            <a href="{{ route('supervisor.timeline') }}" class="action-btn secondary-btn">
                <i class="bi bi-calendar3"></i>
                View Timeline
            </a>
            <a href="{{ route('supervisor.reports') }}" class="action-btn tertiary-btn">
                <i class="bi bi-clock-history"></i>
                Report History
            </a>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="panel-card">
                    <div class="panel-head">
                        <h5><i class="bi bi-speedometer2"></i> Project Progress</h5>
                        <span>{{ $stats['average_completion'] }}% Overall</span>
                    </div>
                    <div class="panel-body">
                        @foreach ($assignedProjects->take(3) as $project)
                            <div class="progress-group">
                                <div class="progress-top">
                                    <span>{{ $project->project_name }}</span>
                                    <span>{{ round($project->phases->avg('completion_percentage') ?? 0, 1) }}%</span>
                                </div>
                                <div class="progress custom-progress">
                                    <div class="progress-bar" style="width: {{ round($project->phases->avg('completion_percentage') ?? 0, 1) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel-card mt-4">
                    <div class="panel-head">
                        <h5><i class="bi bi-calendar2-week"></i> Timeline Overview</h5>
                    </div>
                    <div class="panel-body timeline-panel">
                        @foreach ($upcomingMilestones->take(4) as $milestone)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div>
                                    <h6>{{ $milestone->milestone_name }}</h6>
                                    <small>{{ $milestone->phase->project->project_name }} • {{ $milestone->phase->phase_name }}</small>
                                </div>
                                <span>{{ $milestone->planned_date->format('M d') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="panel-card highlight-card">
                    <div class="panel-head">
                        <h5><i class="bi bi-flag-fill"></i> Current Milestone</h5>
                    </div>
                    <div class="panel-body">
                        @if ($upcomingMilestones->isNotEmpty())
                            <h4>{{ $upcomingMilestones->first()->milestone_name }}</h4>
                            <p>{{ $upcomingMilestones->first()->phase->project->project_name }}</p>
                            <div class="milestone-date">
                                <i class="bi bi-calendar-check"></i> {{ $upcomingMilestones->first()->planned_date->format('d M Y') }}
                            </div>
                        @else
                            <p class="mb-0">No upcoming milestones available</p>
                        @endif
                    </div>
                </div>

                <div class="panel-card mt-4">
                    <div class="panel-head">
                        <h5><i class="bi bi-bell-fill"></i> Recent Activity</h5>
                    </div>
                    <div class="panel-body activity-list">
                        @foreach ($pendingReports->take(4) as $report)
                            <div class="activity-item">
                                <span class="activity-badge">Report</span>
                                <div>
                                    <strong>{{ $report->project->project_name }}</strong>
                                    <small>{{ $report->phase->phase_name }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.supervisor-dashboard {
    padding: 0.25rem 0 1rem;
}

.dashboard-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.hero-panel,
.project-focus-card,
.stat-card,
.panel-card,
.action-btn {
    border-radius: 18px;
}

.hero-panel {
    background: linear-gradient(135deg, #2E7D32 0%, #7CB342 100%);
    color: #fff;
    padding: 1.5rem;
    box-shadow: 0 12px 28px rgba(46, 125, 50, 0.18);
}

.hero-panel h1 {
    margin: 0;
    font-size: clamp(1.8rem, 3vw, 2.4rem);
    font-weight: 800;
}

.eyebrow,
.small-label {
    margin: 0 0 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.72rem;
    opacity: 0.8;
}

.hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-top: 0.8rem;
    font-size: 0.9rem;
    opacity: 0.95;
}

.project-focus-card {
    background: #fff;
    border: 1px solid #e7edd7;
    padding: 1.25rem;
    height: 100%;
}

.project-focus-card h3 {
    margin: 0;
    font-weight: 700;
    color: #183a1d;
}

.phase-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 0.8rem;
    background: #FFF8E1;
    color: #2E7D32;
    padding: 0.45rem 0.8rem;
    border-radius: 999px;
    font-size: 0.9rem;
    font-weight: 600;
}

.stats-grid .stat-card {
    background: #fff;
    border: 1px solid #eef5dd;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.9rem;
    min-height: 112px;
}

.stat-icon {
    width: 52px;
    height: 52px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.2rem;
    background: #f5f8ec;
    color: #2E7D32;
}

.stat-value {
    font-size: 1.7rem;
    font-weight: 800;
    color: #1f2937;
}

.stat-label {
    font-size: 0.82rem;
    color: #6b7280;
}

.green-card .stat-icon { background: #e8f5e9; color: #2E7D32; }
.yellow-card .stat-icon { background: #fff8d6; color: #FDD835; }
.accent-card .stat-icon { background: #f4f7dc; color: #C0CA33; }
.warning-card .stat-icon { background: #fff6d6; color: #d4a300; }

.quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.8rem 1rem;
    font-weight: 700;
    text-decoration: none;
}

.primary-btn {
    background: #2E7D32;
    color: #fff;
}

.secondary-btn {
    background: #FDD835;
    color: #1f2937;
}

.tertiary-btn {
    background: #fff;
    color: #2E7D32;
    border: 1px solid #dfe9cf;
}

.panel-card {
    background: #fff;
    border: 1px solid #eef5dd;
    box-shadow: 0 8px 18px rgba(21, 48, 21, 0.04);
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1rem 1rem 0.75rem;
    border-bottom: 1px solid #f1f5ea;
}

.panel-head h5 {
    margin: 0;
    font-weight: 700;
    color: #1f2937;
}

.panel-head span {
    font-size: 0.9rem;
    color: #6b7280;
}

.panel-body {
    padding: 1rem;
}

.progress-group + .progress-group {
    margin-top: 1rem;
}

.progress-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.45rem;
    font-weight: 600;
    font-size: 0.92rem;
}

.custom-progress {
    height: 10px;
    background: #eef5dd;
}

.custom-progress .progress-bar {
    background: linear-gradient(90deg, #C0CA33 0%, #FDD835 100%);
}

.timeline-panel {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.timeline-item {
    display: grid;
    grid-template-columns: 12px 1fr auto;
    gap: 0.7rem;
    align-items: center;
}

.timeline-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #C0CA33;
    box-shadow: 0 0 0 4px #f5f7d9;
}

.timeline-item h6,
.activity-item strong {
    margin: 0;
    font-size: 0.95rem;
}

.timeline-item small,
.activity-item small,
.activity-item p {
    color: #6b7280;
}

.highlight-card {
    background: linear-gradient(180deg, #fffef3 0%, #fff8cc 100%);
}

.highlight-card h4 {
    margin: 0;
    color: #2E7D32;
    font-weight: 800;
}

.milestone-date {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: #fff;
    padding: 0.5rem 0.8rem;
    border-radius: 999px;
    font-size: 0.9rem;
    color: #2E7D32;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 0.7rem;
}

.activity-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e8f5e9;
    color: #2E7D32;
    padding: 0.3rem 0.5rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
}

@media (max-width: 768px) {
    .hero-panel { padding: 1.1rem; }
    .stats-grid .stat-card { min-height: 96px; }
    .quick-actions { flex-direction: column; }
    .action-btn { width: 100%; justify-content: center; }
}
</style>
@endsection