@extends('layouts.client')

@section('title', 'Client Portal - Project Progress Dashboard')

@section('content')
<div class="client-dashboard">
    <section class="client-shell">
        <div class="client-header row g-3 align-items-stretch">
            <div class="col-12 col-xl-8">
                <div class="client-hero">
                    <p class="eyebrow">Project Client</p>
                    <h1>{{ optional($projects->first())->project_name ?? 'Project Monitoring Portal' }}</h1>
                    <div class="hero-meta">
                        <span><i class="bi bi-layers"></i> {{ optional($currentPhases->first())->phase_name ?? 'Current phase' }}</span>
                        <span><i class="bi bi-calendar-range"></i> {{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="client-highlight">
                    <span class="small-label">Overall Progress</span>
                    <h2>{{ $stats['overall_completion'] }}%</h2>
                    <div class="progress custom-progress">
                        <div class="progress-bar" style="width: {{ $stats['overall_completion'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-grid row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <span class="stat-icon"><i class="bi bi-building"></i></span>
                    <div>
                        <div class="stat-value">{{ $stats['total_projects'] }}</div>
                        <div class="stat-label">Project Status</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <span class="stat-icon"><i class="bi bi-graph-up-arrow"></i></span>
                    <div>
                        <div class="stat-value">{{ $stats['overall_completion'] }}%</div>
                        <div class="stat-label">Overall Progress</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <span class="stat-icon"><i class="bi bi-arrow-repeat"></i></span>
                    <div>
                        <div class="stat-value">{{ $stats['ongoing_projects'] }}</div>
                        <div class="stat-label">Current Phase</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card alert-card">
                    <span class="stat-icon"><i class="bi bi-exclamation-triangle"></i></span>
                    <div>
                        <div class="stat-value">{{ $stats['delayed_milestones_count'] }}</div>
                        <div class="stat-label">Alerts</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="panel-card">
                    <div class="panel-head">
                        <h5><i class="bi bi-graph-up"></i> Project Progress</h5>
                        <span>{{ $stats['completed_projects'] }} completed</span>
                    </div>
                    <div class="panel-body">
                        @foreach ($projectSummaries->take(4) as $summary)
                            <div class="project-panel-item">
                                <div class="project-panel-top">
                                    <div>
                                        <h6>{{ $summary['project']->project_name }}</h6>
                                        <small>{{ $summary['project']->project_location }}</small>
                                    </div>
                                    <span>{{ $summary['completion'] }}%</span>
                                </div>
                                <div class="progress custom-progress">
                                    <div class="progress-bar" style="width: {{ $summary['completion'] }}%"></div>
                                </div>
                                @if ($summary['current_phase'])
                                    <div class="phase-badge">
                                        <i class="bi bi-arrow-right-circle"></i> {{ $summary['current_phase']->phase_name }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel-card mt-4">
                    <div class="panel-head">
                        <h5><i class="bi bi-signpost-split"></i> Timeline Roadmap</h5>
                    </div>
                    <div class="panel-body roadmap-list">
                        @foreach ($upcomingMilestones->take(5) as $milestone)
                            <div class="roadmap-item">
                                <div class="roadmap-icon"><i class="bi bi-calendar2-week"></i></div>
                                <div>
                                    <h6>{{ $milestone->milestone_name }}</h6>
                                    <small>{{ $milestone->phase->phase_name }}</small>
                                </div>
                                <span>{{ $milestone->planned_date->format('M d') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="panel-card alert-panel">
                    <div class="panel-head">
                        <h5><i class="bi bi-exclamation-circle"></i> Alerts</h5>
                    </div>
                    <div class="panel-body">
                        @if ($delayedMilestones->isNotEmpty())
                            @foreach ($delayedMilestones->take(3) as $milestone)
                                <div class="alert-item">
                                    <strong>{{ $milestone->milestone_name }}</strong>
                                    <small>{{ $milestone->phase->project->project_name }}</small>
                                    <span>Delayed</span>
                                </div>
                            @endforeach
                        @else
                            <p class="mb-0">No active alerts at this time.</p>
                        @endif
                    </div>
                </div>

                <div class="panel-card mt-4">
                    <div class="panel-head">
                        <h5><i class="bi bi-bell"></i> Recent Updates</h5>
                    </div>
                    <div class="panel-body update-list">
                        @foreach ($recentReports->take(4) as $report)
                            <div class="update-item">
                                <div>
                                    <h6>{{ $report->project->project_name }}</h6>
                                    <small>{{ $report->phase->phase_name }}</small>
                                </div>
                                <span>{{ optional($report->created_at)->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.client-dashboard {
    padding: 0.25rem 0 1rem;
}

.client-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.client-hero,
.client-highlight,
.stat-card,
.panel-card {
    border-radius: 18px;
}

.client-hero {
    background: linear-gradient(135deg, #003366 0%, #336699 100%);
    color: #fff;
    padding: 1.5rem;
    box-shadow: 0 12px 28px rgba(0, 51, 102, 0.18);
}

.client-hero h1 {
    margin: 0;
    font-size: clamp(1.8rem, 3vw, 2.5rem);
    font-weight: 800;
}

.hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-top: 0.8rem;
    font-size: 0.92rem;
    opacity: 0.95;
}

.client-highlight {
    background: #fff;
    border: 1px solid #d7e7f5;
    padding: 1.25rem;
    height: 100%;
}

.client-highlight h2 {
    margin: 0.35rem 0;
    font-size: 2.5rem;
    font-weight: 800;
    color: #003366;
}

.stats-grid .stat-card {
    background: #fff;
    border: 1px solid #d7e7f5;
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
    background: #eef7ff;
    color: #003366;
    font-size: 1.15rem;
}

.stat-value {
    font-size: 1.7rem;
    font-weight: 800;
    color: #15304d;
}

.stat-label {
    font-size: 0.82rem;
    color: #5b6b7f;
}

.alert-card .stat-icon,
.alert-card .stat-value {
    color: #b42318;
}

.alert-card .stat-icon {
    background: #fff4f1;
}

.panel-card {
    background: #fff;
    border: 1px solid #d7e7f5;
    box-shadow: 0 10px 22px rgba(0, 51, 102, 0.05);
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 1rem 1rem 0.75rem;
    border-bottom: 1px solid #eef6fb;
}

.panel-head h5 {
    margin: 0;
    font-weight: 700;
    color: #15304d;
}

.panel-head span {
    font-size: 0.9rem;
    color: #5b6b7f;
}

.panel-body {
    padding: 1rem;
}

.project-panel-item + .project-panel-item {
    margin-top: 1rem;
}

.project-panel-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.45rem;
}

.project-panel-top h6,
.roadmap-item h6,
.update-item h6,
.alert-item strong {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
}

.project-panel-top small,
.roadmap-item small,
.update-item small,
.alert-item small,
.project-panel-top span,
.roadmap-item span,
.update-item span {
    color: #5b6b7f;
}

.phase-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.6rem;
    background: #eef7ff;
    color: #003366;
    padding: 0.4rem 0.65rem;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
}

.custom-progress {
    height: 10px;
    background: #eaf3fb;
}

.custom-progress .progress-bar {
    background: linear-gradient(90deg, #6699CC 0%, #99CCFF 100%);
}

.roadmap-list,
.update-list {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.roadmap-item,
.update-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.7rem;
}

.roadmap-icon {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #eef7ff;
    color: #003366;
}

.alert-panel {
    background: linear-gradient(180deg, #fff9f6 0%, #fff4f1 100%);
}

.alert-item {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.7rem 0;
    border-bottom: 1px solid #f7e0d7;
}

.alert-item:last-child { border-bottom: 0; }

@media (max-width: 768px) {
    .client-hero { padding: 1.1rem; }
    .stats-grid .stat-card { min-height: 96px; }
    .roadmap-item,
    .update-item {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endsection