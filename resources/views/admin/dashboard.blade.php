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
                <div class="stat-number">{{ $stats['active_projects'] ?? 8 }}</div>
                <div class="stat-subtext link">View all &rarr;</div>
            </div>
        </div>
        
        <div class="stat-box">
            <div class="stat-icon-wrap"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-label-top">Total Workers</div>
                <div class="stat-number">{{ $stats['total_workforce'] ?? 128 }}</div>
                <div class="stat-subtext up"><i class="bi bi-arrow-up-short"></i> +12 this week</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap"><i class="bi bi-layers"></i></div>
            <div class="stat-info">
                <div class="stat-label-top">Materials in Stock</div>
                <div class="stat-number">{{ $stats['inventory_count'] ?? 245 }}</div>
                <div class="stat-subtext link"><span style="color:#10b981;">●</span> On track</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon-wrap"><i class="bi bi-clipboard-data"></i></div>
            <div class="stat-info">
                <div class="stat-label-top">Pending Reports</div>
                <div class="stat-number">{{ $stats['pending_reports'] ?? 5 }}</div>
                <div class="stat-subtext link">View reports &rarr;</div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ROW: Projects List & Sidebar Charts -->
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
                        <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=150&q=80" alt="Project" class="proj-thumb">
                        
                        <div class="proj-details">
                            <div class="proj-title">{{ $project->name }}</div>
                            <div class="proj-sub">{{ $project->current_phase ?? 'Phase 1 - Construction' }}</div>
                        </div>

                        <div class="proj-progress-wrapper">
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill {{ $project->progress_percentage >= 50 ? 'green' : 'yellow' }}" style="width: {{ $project->progress_percentage }}%"></div>
                            </div>
                            <div class="proj-percent">{{ $project->progress_percentage }}%</div>
                        </div>

                        <div>
                            @if($project->progress_percentage >= 50)
                                <div class="proj-badge on-track">On Track</div>
                            @else
                                <div class="proj-badge delayed">Delayed</div>
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
                        <div><div class="proj-badge on-track">On Track</div></div>
                    </div>

                    <div class="project-list-item">
                        <img src="https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=150&q=80" alt="Riverside" class="proj-thumb">
                        <div class="proj-details">
                            <div class="proj-title">Riverside Phase 2</div>
                            <div class="proj-sub">Finishing Works</div>
                        </div>
                        <div class="proj-progress-wrapper">
                            <div class="progress-bar-bg"><div class="progress-bar-fill yellow" style="width: 45%"></div></div>
                            <div class="proj-percent">45%</div>
                        </div>
                        <div><div class="proj-badge delayed">Delayed</div></div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column: Chart & Schedule -->
        <div>
            <!-- Overall Progress Donut -->
            <div class="dash-card" style="margin-bottom: 24px;">
                <div class="dash-card-header">
                    <div class="dash-card-title">Overall Progress</div>
                    <div class="dash-card-link" style="display:flex; align-items:center; gap:4px;">This Month <i class="bi bi-chevron-down"></i></div>
                </div>

                <div class="donut-chart-container">
                    <div class="donut-chart">
                        <div class="donut-inner">72%</div>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item">
                            <div class="legend-title"><div class="legend-dot green"></div> On Track</div>
                            <div class="legend-sub">6 Projects</div>
                        </div>
                        <div class="legend-item" style="margin-bottom: 0;">
                            <div class="legend-title"><div class="legend-dot yellow"></div> Delayed</div>
                            <div class="legend-sub">2 Projects</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <div class="dash-card-title">Today's Schedule</div>
                    <a href="#" class="dash-card-link">View calendar</a>
                </div>

                <div class="schedule-list">
                    <div class="schedule-item">
                        <div class="schedule-time">8:00 AM</div>
                        <div class="schedule-icon green"><i class="bi bi-clipboard-check"></i></div>
                        <div class="schedule-info">
                            <div class="schedule-title">Site Inspection</div>
                            <div class="schedule-loc">Greenview Residences</div>
                        </div>
                    </div>

                    <div class="schedule-item">
                        <div class="schedule-time">10:30 AM</div>
                        <div class="schedule-icon orange"><i class="bi bi-truck"></i></div>
                        <div class="schedule-info">
                            <div class="schedule-title">Material Delivery</div>
                            <div class="schedule-loc">Skyline Tower</div>
                        </div>
                    </div>

                    <div class="schedule-item">
                        <div class="schedule-time">1:00 PM</div>
                        <div class="schedule-icon blue"><i class="bi bi-file-earmark-text"></i></div>
                        <div class="schedule-info">
                            <div class="schedule-title">Accomplishment Review</div>
                            <div class="schedule-loc">Riverside Phase 2</div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

</div>
@endsection