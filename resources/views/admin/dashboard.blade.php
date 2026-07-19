@extends('layouts.admin')

@section('title', 'Admin Dashboard - D&G Construction Monitor')
@section('page_title', 'Management Dashboard')

@push('styles')
<style>
    .project-list-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s ease;
    }
    .project-list-item:hover {
        background-color: #f8fafc;
    }
    .project-list-item:last-child {
        border-bottom: none;
    }
    .proj-thumb {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
        background: #eef2f7;
    }
    .proj-details {
        flex: 1 1 auto;
        min-width: 0;
    }
    .proj-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .proj-sub {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 2px;
    }
    .proj-progress-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 140px;
    }
    .progress-bar-bg {
        width: 100%;
        height: 6px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
        background-color: #10b981;
    }
    .progress-bar-fill.blue { background-color: #2563eb; }
    .progress-bar-fill.green { background-color: #16a34a; }
    .progress-bar-fill.orange { background-color: #f59e0b; }
    .proj-percent {
        font-size: 0.8rem;
        font-weight: 700;
        color: #334155;
        min-width: 36px;
        text-align: right;
    }
    .proj-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .proj-badge.on-track {
        background-color: #e6f6ee;
        color: #16a34a;
        border: 1px solid #d1fae5;
    }
    .proj-badge.delayed {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
    }
    .proj-badge.in-progress {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
    }
    .proj-badge.planning {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .proj-badge.completed {
        background-color: #e6f6ee;
        color: #16a34a;
        border: 1px solid #d1fae5;
    }
    .proj-badge.on-hold {
        background-color: #fff7ed;
        color: #ea580c;
        border: 1px solid #ffedd5;
    }
    .proj-badge.archived {
        background-color: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

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
                    @php
                        $statusClass = match(strtolower((string) ($project->status_label ?? 'Planning'))) {
                            'completed' => 'completed',
                            'ongoing', 'in progress' => 'in-progress',
                            'on hold' => 'on-hold',
                            'archived' => 'archived',
                            default => 'planning',
                        };
                    @endphp
                    <div class="project-list-item" data-project-id="{{ $project->id }}" data-project-name="{{ $project->name }}" data-project-location="{{ $project->location }}" data-project-status="{{ $project->status_label }}" data-project-phase="{{ $project->current_phase ?? 'N/A' }}" data-project-progress="{{ $project->progress_percentage ?? 0 }}" data-project-image="{{ $project->image ?? '' }}" style="cursor: pointer;" onclick="openProjectDetailModal(this)">
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
                            <div class="proj-badge {{ $statusClass }}">{{ $project->status_label ?? 'Planning' }}</div>
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

    <!-- Project Detail Modal -->
    <div class="modal fade" id="projectDetailModal" tabindex="-1" aria-labelledby="projectDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4" style="background: #ffffff;">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-1" id="projectDetailModalLabel" style="font-size: 1.35rem; letter-spacing: -0.01em;">Project Details</h4>
                        <p class="text-muted mb-0" id="projectDetailModalSubtitle" style="font-size: 0.85rem;">Overview of the selected project.</p>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.85rem;"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon"><i class="bi bi-building"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Project</div>
                                    <div class="fw-bold text-dark" id="modalProjectName">--</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon"><i class="bi bi-geo-alt"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Location</div>
                                    <div class="fw-bold text-dark" id="modalProjectLocation">--</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon"><i class="bi bi-flag"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</div>
                                    <div class="fw-bold text-dark" id="modalProjectStatus">--</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon"><i class="bi bi-diagram-3"></i></div>
                                <div>
                                    <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Current Phase</div>
                                    <div class="fw-bold text-dark" id="modalProjectPhase">--</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="metric-icon"><i class="bi bi-speedometer2"></i></div>
                                <div class="flex-grow-1">
                                    <div class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Progress</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <div class="progress flex-grow-1" style="height: 10px; background-color: #f1f5f9; border-radius: 8px;">
                                            <div id="modalProjectProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%; border-radius: 8px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-bold text-dark" id="modalProjectProgressText">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-light border px-4 shadow-sm fw-medium rounded-2" data-bs-dismiss="modal" style="height: 40px; font-size: 0.88rem; background: #ffffff; color: #334155;">Close</button>
                    <a href="{{ route('admin.projects.index') }}" id="viewFullProjectBtn" class="btn btn-success px-4 shadow-sm fw-medium rounded-2 d-flex align-items-center gap-2" style="height: 40px; font-size: 0.88rem; text-decoration: none;">
                        <i class="bi bi-folder2-open"></i> View Full Project
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function openProjectDetailModal(el) {
        const projectId = el.getAttribute('data-project-id');
        const projectName = el.getAttribute('data-project-name') || 'Untitled Project';
        const projectLocation = el.getAttribute('data-project-location') || 'No location';
        const projectStatus = el.getAttribute('data-project-status') || 'Planning';
        const projectPhase = el.getAttribute('data-project-phase') || 'N/A';
        const projectProgress = el.getAttribute('data-project-progress') || '0';
        const projectImage = el.getAttribute('data-project-image') || '';

        const modalEl = document.getElementById('projectDetailModal');
        if (!modalEl) return;

        document.getElementById('modalProjectName').textContent = projectName;
        document.getElementById('modalProjectLocation').textContent = projectLocation;
        document.getElementById('modalProjectStatus').textContent = projectStatus;
        document.getElementById('modalProjectPhase').textContent = projectPhase;
        document.getElementById('modalProjectProgressText').textContent = projectProgress + '%';
        document.getElementById('modalProjectProgressBar').style.width = projectProgress + '%';
        document.getElementById('modalProjectProgressBar').setAttribute('aria-valuenow', projectProgress);

        const progressBar = document.getElementById('modalProjectProgressBar');
        progressBar.className = 'progress-bar ' + (projectProgress >= 80 ? 'bg-success' : (projectProgress < 40 ? 'bg-warning' : 'bg-primary'));

        const viewFullProjectBtn = document.getElementById('viewFullProjectBtn');
        if (viewFullProjectBtn && projectId) {
            viewFullProjectBtn.href = '/admin/projects';
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endpush
