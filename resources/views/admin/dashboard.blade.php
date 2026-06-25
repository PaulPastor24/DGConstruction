@extends('layouts.admin')

@section('title', 'Admin Dashboard - D&G Construction Monitor')
@section('page_title', 'Management Dashboard')

@section('topbar_actions')
@endsection 

@section('content')
<div class="page active" id="pg-dashboard">
    
    <div class="stat-grid">
        <div class="stat-card" style="--accent-color: var(--accent);">
            <div class="stat-label">Active Projects</div>
            <div class="stat-value">{{ $stats['active_projects'] ?? 0 }}</div>
            <div class="stat-change up">{{ $stats['projects_change_label'] ?? '' }}</div>
        </div>
        <div class="stat-card" style="--accent-color: var(--green);">
            <div class="stat-label">On-Track Projects</div>
            <div class="stat-value">{{ $stats['on_track_projects'] ?? 0 }}</div>
            <div class="stat-change up">{{ $stats['completion_rate_label'] ?? '' }}</div>
        </div>
        <div class="stat-card" style="--accent-color: var(--blue);">
            <div class="stat-label">Total Workforce</div>
            <div class="stat-value">{{ $stats['total_workforce'] ?? 0 }}</div>
            <div class="stat-change">Across all project sites</div>
        </div>
        <div class="stat-card" style="--accent-color: var(--red);">
            <div class="stat-label">Pending Reports</div>
            <div class="stat-value">{{ $stats['pending_reports'] ?? 0 }}</div>
            <div class="stat-change down">Requires admin review</div>
        </div>
    </div>

    <div class="col-7-5">
        <div>
            <div class="section-header">
                <div class="section-title">Active Projects</div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <a href="{{ route('admin.projects.create') }}" class="topbar-btn primary {{ request()->routeIs('admin.projects.create') ? 'active' : '' }}" style="padding: 6px 12px; font-size: 12px; text-decoration: none; display: inline-block;">
                        + New Project
                    </a>
                    
                    <a href="{{ route('admin.projects.index') }}" class="section-link {{ request()->routeIs('admin.projects.index') ? 'active' : '' }}">
                        View All
                    </a>
                </div>
            </div>
            
            <div style="display:grid; gap:14px;">
                @forelse($activeProjects as $project)
                    <div class="proj-card" onclick="window.location.href='{{ route('admin.projects.show', $project->id) }}'">
                        <div class="proj-card-top">
                            <div>
                                <div class="proj-name">{{ $project->name }}</div>
                                <div class="proj-location">{{ $project->location }}</div>
                            </div>
                            <div class="status-badge {{ Str::slug($project->status_label) }}">
                                {{ $project->status_label }}
                            </div>
                        </div>
                        <div class="proj-phase">Current Phase: <span class="phase-tag">{{ $project->current_phase }}</span></div>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill {{ $project->progress_color_class }}" style="width:{{ $project->progress_percentage }}%"></div>
                        </div>
                        <div class="proj-meta">
                            <span>{{ $project->progress_percentage }}% complete</span>
                            <span>Due: {{ \Carbon\Carbon::parse($project->target_end_date)->format('M Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="card p-4 text-center text-muted">
                        No active construction projects monitoring currently.
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="card mb-0">
                <div class="card-header">
                    <div class="card-title">Workforce Summary</div>
                    <a href="{{ route('admin.attendance') }}" class="section-link">Details</a>
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 14px;">
                    <div style="flex:1; background: rgba(34,197,94,0.08); border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-family:var(--heading); font-size:20px; font-weight:800; color:var(--green);">{{ $attendance['present'] ?? 0 }}</div>
                        <div style="font-size:11px; color:var(--muted);">Present</div>
                    </div>
                    <div style="flex:1; background: rgba(239,68,68,0.08); border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-family:var(--heading); font-size:20px; font-weight:800; color:var(--red);">{{ $attendance['absent'] ?? 0 }}</div>
                        <div style="font-size:11px; color:var(--muted);">Absent</div>
                    </div>
                    <div style="flex:1; background: rgba(245,166,35,0.08); border-radius:8px; padding:12px; text-align:center;">
                        <div style="font-family:var(--heading); font-size:20px; font-weight:800; color:var(--yellow);">{{ $attendance['late'] ?? 0 }}</div>
                        <div style="font-size:11px; color:var(--muted);">Late</div>
                    </div>
                </div>

                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill green" style="width:{{ $attendance['rate'] ?? 0 }}%"></div>
                </div>
                <div style="font-size:11px; color:var(--muted); margin-top:4px;">{{ $attendance['rate'] ?? 0 }}% attendance rate today</div>
            </div>
        </div>
    </div>
</div>
@endsection