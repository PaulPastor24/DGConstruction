@extends('layouts.admin')

@section('title', 'Admin Dashboard - D&G Construction Monitor')
@section('page_title', 'Management Dashboard')

@section('content')
<div class="dashboard-container">

    <!-- TOP ROW: Banner & Quick Actions -->
    <div class="top-row-grid">
        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="hero-content">
                <div class="hero-title">Building<br><span>Better Futures.</span></div>
                <div class="hero-subtitle">Efficient management today,<br>stronger structures tomorrow.</div>
                <a href="{{ route('admin.projects.index') }}" class="hero-btn">
                    View Projects <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-card">
            <div class="qa-header">Quick Actions</div>
            <div class="qa-grid">
                <a href="{{ route('admin.projects.create') }}" class="qa-item">
                    <div class="qa-icon"><i class="bi bi-file-earmark-plus"></i></div>
                    New<br>Project
                </a>
                <a href="{{ route('admin.inventory') }}" class="qa-item">
                    <div class="qa-icon"><i class="bi bi-box-seam"></i></div>
                    Add<br>Material
                </a>
                <a href="{{ route('admin.attendance') }}" class="qa-item">
                    <div class="qa-icon"><i class="bi bi-person-plus"></i></div>
                    Add<br>Worker
                </a>
                <a href="{{ route('admin.reports.index') }}" class="qa-item">
                    <div class="qa-icon"><i class="bi bi-bar-chart"></i></div>
                    Generate<br>Report
                </a>
            </div>
        </div>
    </div>

    <!-- STATS ROW -->
    <div class="stats-grid-4">
        <div class="stat-box">
            <div class="stat-icon-wrap"><i class="bi bi-building"></i></div>
            <div class="stat-info">
                <div class="stat-label-top">Active Projects</div>
                <div class="stat-number">{{ $stats['active_projects'] ?? 0 }}</div>
                <div class="stat-subtext link">View all &rarr;</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-label-top">Total Workers</div>
                <div class="stat-number">{{ $stats['total_workforce'] ?? 0 }}</div>
                <div class="stat-subtext up"><i class="bi bi-arrow-up-short"></i> +12 this week</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap"><i class="bi bi-layers"></i></div>
            <div class="stat-info">
                <div class="stat-label-top">Materials in Stock</div>
                <div class="stat-number">{{ $stats['inventory_count'] ?? 0 }}</div>
                <div class="stat-subtext link"><span style="color:#10b981;">●</span> On track</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap"><i class="bi bi-clipboard-data"></i></div>
            <div class="stat-info">
                <div class="stat-label-top">Pending Reports</div>
                <div class="stat-number">{{ $stats['pending_reports'] ?? 0 }}</div>
                <div class="stat-subtext link">View reports &rarr;</div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW: Projects List & Sidebar Cards -->
    <div class="bottom-row-grid">

        <!-- Left: Recent Projects -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title">Recent Projects</div>
                <a href="{{ route('admin.projects.index') }}" class="dash-card-link">View all projects &rarr;</a>
            </div>

            <div class="project-list">
                @forelse($activeProjects ?? [] as $project)
                    <div class="project-list-item" onclick="window.location.href='{{ route('admin.projects.show', $project->id) }}'" style="cursor: pointer;">
                        <img src="{{ $project->image ?? 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=150&q=80' }}" alt="Project" class="proj-thumb" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=150&q=80';">

                        <div class="proj-details">
                            <div class="proj-title">{{ $project->name }}</div>
                            <div class="proj-sub">{{ $project->current_phase ?? 'Phase 1 - Construction' }}</div>
                        </div>

                        <div class="proj-progress-wrapper">
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill {{ $project->progress_color_class ?? 'blue' }}" style="width: {{ $project->progress_percentage }}%"></div>
                            </div>
                            <div class="proj-percent">{{ $project->progress_percentage }}%</div>
                        </div>

                        <div>
                            @if(($project->progress_percentage ?? 0) >= 80)
                                <div class="proj-badge on-track">On Track</div>
                            @elseif(($project->progress_percentage ?? 0) < 40)
                                <div class="proj-badge delayed">Delayed</div>
                            @else
                                <div class="proj-badge in-progress">In Progress</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <!-- Fallback UI Mock Data -->
                    <div class="project-list-item">
                        <img src="https://images.unsplash.com/photo-1541888086225-f6740f9e8af5?w=150&q=80" alt="Greenview" class="proj-thumb">
                        <div class="proj-details">
                            <div class="proj-title">Greenview Residences</div>
                            <div class="proj-sub">Phase 2 - Construction</div>
                        </div>
                        <div class="proj-progress-wrapper">
                            <div class="progress-bar-bg"><div class="progress-bar-fill green" style="width: 85%"></div></div>
                            <div class="proj-percent">85%</div>
                        </div>
                        <div><div class="proj-badge on-track">On Track</div></div>
                    </div>

                    <div class="project-list-item">
                        <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=150&q=80" alt="Skyline" class="proj-thumb">
                        <div class="proj-details">
                            <div class="proj-title">Skyline Tower</div>
                            <div class="proj-sub">Structural Works</div>
                        </div>
                        <div class="proj-progress-wrapper">
                            <div class="progress-bar-bg"><div class="progress-bar-fill green" style="width: 65%"></div></div>
                            <div class="proj-percent">65%</div>
                        </div>
                        <div><div class="proj-badge in-progress">In Progress</div></div>
                    </div>

                    <div class="project-list-item">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=150&q=80" alt="Riverside" class="proj-thumb">
                        <div class="proj-details">
                            <div class="proj-title">Riverside Phase 2</div>
                            <div class="proj-sub">Finishing Works</div>
                        </div>
                        <div class="proj-progress-wrapper">
                            <div class="progress-bar-bg"><div class="progress-bar-fill orange" style="width: 35%"></div></div>
                            <div class="proj-percent">35%</div>
                        </div>
                        <div><div class="proj-badge delayed">Delayed</div></div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column: Chart & Recent Reports -->
        <div>
            <!-- Overall Progress Donut -->
            <div class="dash-card" style="margin-bottom: 24px;">
                <div class="dash-card-header">
                    <div class="dash-card-title">Overall Progress</div>
                    <div class="dash-card-link" style="display:flex; align-items:center; gap:4px;">This Month <i class="bi bi-chevron-down"></i></div>
                </div>

                <div class="donut-chart-container">
                    <div class="donut-chart" style="background: conic-gradient(#4d7c53 0% {{ $overallProgress['percentage'] }}%, #f3f4f6 {{ $overallProgress['percentage'] }}% 100%);">
                        <div class="donut-inner">{{ $overallProgress['percentage'] }}%</div>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item">
                            <div class="legend-title"><div class="legend-dot green"></div> On Track</div>
                            <div class="legend-sub">{{ $overallProgress['on_track'] }} Projects</div>
                        </div>
                        <div class="legend-item" style="margin-bottom: 0;">
                            <div class="legend-title"><div class="legend-dot orange"></div> Delayed</div>
                            <div class="legend-sub">{{ $overallProgress['delayed'] }} Projects</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">Recent Reports</div>
                    <a href="{{ route('admin.reports.index') }}" class="dash-card-link">View all reports &rarr;</a>
                </div>

                <div class="reports-list">
                    @forelse($recentReports ?? [] as $report)
                        <div class="report-list-item" onclick="window.location.href='{{ route('admin.reports.index') }}'" style="cursor: pointer;">
                            <div class="report-info">
                                <div class="report-title">{{ $report->title }}</div>
                                <div class="report-meta">{{ $report->project_name }} &middot; {{ $report->phase_name }}</div>
                            </div>
                            <div class="report-meta-right">
                                <span class="report-badge {{ $report->status_class }}">{{ $report->status_label }}</span>
                                <div class="report-date">{{ $report->submitted_at }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="report-list-item">
                            <div class="report-info">
                                <div class="report-title">No recent reports</div>
                                <div class="report-meta">Accomplishment reports will appear here once submitted by supervisors.</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
