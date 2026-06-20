@extends('layouts.client')

@section('title', 'Project Timeline - Client View')
@section('page_title', 'My Project Timeline')

@push('styles')
<style>
    :root {
        --client-primary: #003366;
        --client-secondary: #336699;
        --client-accent: #6699CC;
        --client-light: #99CCFF;
        --client-bg: #CCFFFF;
        --client-surface: #fff;
        --client-border: #d8e7f5;
        --client-muted: #5b6b7f;
        --client-text: #15304d;
    }

    #pg-timeline {
        padding-bottom: 1.5rem;
    }

    .timeline-summary {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .summary-card {
        flex: 1;
        min-width: 200px;
        background: linear-gradient(135deg, #f5fbff 0%, #eef7ff 100%);
        border: 1px solid var(--client-border);
        border-radius: 16px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 8px 18px rgba(0, 51, 102, 0.06);
    }

    .summary-card.active { background: linear-gradient(135deg, #f5fbff 0%, #ecf6ff 100%); }
    .summary-card.completed { background: linear-gradient(135deg, #f0f9ff 0%, #e4f1ff 100%); }

    .summary-icon {
        font-size: 24px;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border-radius: 12px;
        color: var(--client-primary);
    }

    .summary-content h4 {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 13px;
        margin: 0 0 0.25rem 0;
        color: var(--client-text);
    }

    .summary-content p { margin: 0; font-size: 12px; color: var(--client-muted); }

    .timeline-container {
        background: var(--client-surface);
        border-radius: 18px;
        border: 1px solid var(--client-border);
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(0, 51, 102, 0.06);
    }

    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 1.25rem;
        padding: 1.5rem;
    }

    .project-card {
        background: #fff;
        border: 1px solid var(--client-border);
        border-radius: 16px;
        padding: 1.2rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .project-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--client-secondary), var(--client-light));
    }

    .project-card:hover {
        box-shadow: 0 10px 22px rgba(0, 51, 102, 0.08);
        border-color: var(--client-accent);
        transform: translateY(-2px);
    }

    .project-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.9rem;
        gap: 1rem;
    }

    .project-title {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 16px;
        color: var(--client-text);
    }

    .project-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.7rem;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .project-status-badge.planning { background: #eef4ff; color: var(--client-primary); }
    .project-status-badge.ongoing { background: #eef7ff; color: var(--client-secondary); }
    .project-status-badge.completed { background: #eef9ff; color: var(--client-primary); }

    .project-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-size: 13px;
        color: var(--client-muted);
    }

    .meta-item { display: flex; align-items: center; gap: 0.5rem; }

    .project-progress-section {
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #f7fbff 0%, #eef7ff 100%);
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid var(--client-border);
    }

    .progress-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--client-primary);
        margin-bottom: 0.75rem;
        letter-spacing: 0.05em;
    }

    .progress-bar-container {
        background: #eaf3fb;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 0.75rem;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--client-secondary), var(--client-light));
        transition: width 0.4s ease;
    }

    .progress-percentage {
        font-size: 13px;
        font-weight: 700;
        color: var(--client-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .progress-percentage::before {
        content: '•';
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--client-secondary);
        color: white;
        border-radius: 50%;
        font-size: 10px;
    }

    .phases-list { border-top: 1px solid var(--client-border); padding-top: 0.9rem; }
    .phases-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--client-muted); letter-spacing: 0.05em; margin-bottom: 0.75rem; }
    .phase-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; font-size: 12px; transition: all 0.2s ease; }
    .phase-item:hover { padding-left: 0.5rem; color: var(--client-text); font-weight: 500; }
    .phase-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; background: var(--client-accent); }
    .phase-dot.completed { background: var(--client-secondary); }
    .phase-dot.in-progress { background: var(--client-primary); }
    .phase-dot.planning { background: var(--client-light); }
    .phase-name { color: var(--client-muted); }

    .no-projects {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        color: var(--client-muted);
        text-align: center;
        flex-direction: column;
        gap: 1rem;
    }

    .no-projects i { font-size: 64px; opacity: 0.2; color: var(--client-primary); }
    .no-projects p { font-size: 14px; margin: 0; }
    .no-projects strong { display: block; color: var(--client-text); margin-bottom: 0.5rem; font-size: 16px; }

    @media (max-width: 1024px) {
        .projects-grid { grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }
        .timeline-summary { flex-direction: column; }
        .summary-card { min-width: unset; }
    }

    @media (max-width: 768px) {
        .projects-grid { grid-template-columns: 1fr; padding: 1rem; }
        .project-header { flex-direction: column; align-items: flex-start; }
        .project-status-badge { align-self: flex-start; }
        .timeline-summary { gap: 1rem; }
        .summary-card { padding: 1rem; }
        .summary-icon { font-size: 22px; width: 42px; height: 42px; }
        .summary-content h4 { font-size: 12px; }
        .summary-content p { font-size: 11px; }
    }
</style>
@endpush

@section('content')
<div class="page active" id="pg-timeline">
    
    @if($projectsWithStats->count() > 0)
        <div class="timeline-summary">
            <div class="summary-card completed">
                <div class="summary-icon">📊</div>
                <div class="summary-content">
                    <h4>Total Projects</h4>
                    <p>{{ $projectsWithStats->count() }} active project{{ $projectsWithStats->count() != 1 ? 's' : '' }}</p>
                </div>
            </div>
            <div class="summary-card active">
                <div class="summary-icon">⚙️</div>
                <div class="summary-content">
                    <h4>In Progress</h4>
                    <p>{{ $projectsWithStats->where('status', 'ongoing')->count() }} project{{ $projectsWithStats->where('status', 'ongoing')->count() != 1 ? 's' : '' }}</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon">✓</div>
                <div class="summary-content">
                    <h4>Average Progress</h4>
                    <p>{{ round($projectsWithStats->avg('progress')) }}% complete</p>
                </div>
            </div>
        </div>
    @endif

    <div class="timeline-container">
        @if($projectsWithStats->count() > 0)
            <div class="projects-grid">
                @foreach($projectsWithStats as $project)
                    <div class="project-card">
                        <div class="project-header">
                            <div>
                                <div class="project-title">{{ $project['name'] }}</div>
                            </div>
                            <span class="project-status-badge {{ strtolower($project['status']) }}">
                                {{ ucfirst($project['status']) }}
                            </span>
                        </div>

                        <div class="project-meta">
                            <div class="meta-item">
                                <i class="bi bi-geo-alt" style="font-size: 12px;"></i>
                                <span>{{ $project['location'] }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-calendar" style="font-size: 12px;"></i>
                                <span>{{ $project['startDate']->format('M d, Y') }} – {{ $project['targetEndDate']->format('M d, Y') }}</span>
                            </div>
                            @if($project['engineer'])
                                <div class="meta-item">
                                    <i class="bi bi-person" style="font-size: 12px;"></i>
                                    <span>Engineer: {{ $project['engineer']->name ?? 'N/A' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="project-progress-section">
                            <div class="progress-label">Overall Progress</div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: {{ $project['progress'] }}%"></div>
                            </div>
                            <div class="progress-percentage">{{ round($project['progress']) }}% Complete</div>
                        </div>

                        <div class="phases-list">
                            <div class="phases-title">Construction Phases</div>
                            @foreach($project['phases'] as $phase)
                                <div class="phase-item">
                                    <span class="phase-dot {{ strtolower($phase->display_status ?? 'planning') }}"></span>
                                    <span class="phase-name">{{ $phase->phase_name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-projects">
                <i class="bi bi-inbox"></i>
                <strong>No Projects Assigned Yet</strong>
                <p>You don't have any active projects assigned to you. Contact your administrator to get started.</p>
            </div>
        @endif
    </div>
</div>
@endsection
