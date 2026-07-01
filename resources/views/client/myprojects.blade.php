@extends('layouts.client')

@section('title', 'My Projects - Client Portal')


@section('content')
@section('pageHeaderLabel', 'OVERVIEW')
@section('pageHeaderTitle', 'My Projects')
@section('pageHeaderCopy', 'Your active projects and current construction status at a glance.')

<div class="container-fluid p-0">

    @php
        $totalProjects = count($projectSummaries ?? []);
        $activeTrackCount = collect($projectSummaries ?? [])
            ->filter(fn($summary) => data_get($summary, 'project.status') === 'ongoing')
            ->count();
    @endphp
    <div class="row g-4 mb-5 align-items-center">
        <div class="col-12 col-md-4 col-lg-3">
            <div class="project-summary-box">
                <div class="summary-icon bg-mint-container text-success">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div>
                    <span class="summary-label">Total Projects</span>
                    <h3>{{ $totalProjects }}</h3>
                    <span class="summary-subtext">Assigned to your profile</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <div class="project-summary-box">
                <div class="summary-icon bg-orange-container text-warning">
                    <i class="bi bi-cone-striped"></i>
                </div>
                <div>
                    <span class="summary-label">Active Track</span>
                    <h3>{{ $activeTrackCount }}</h3>
                    <span class="summary-subtext">Currently under construction</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-6 ms-auto">
            <form action="{{ route('client.myprojects') }}" method="GET" class="d-flex flex-column flex-md-row gap-2 justify-content-md-end align-items-stretch">
                <div class="position-relative flex-grow-1" style="max-width: 320px; min-width: 0;">
                    <i class="bi bi-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control filter-search-input" placeholder="Search project logs...">
                </div>
                <button type="submit" class="btn btn-filter-action"><i class="bi bi-search me-2"></i>Search</button>
            </form>
        </div>
    </div>

    <div class="mb-4">
        <h4 class="fw-bold m-0" style="font-size: 1.1rem; color: var(--text-primary);">Project Feed</h4>
    </div>

    <div class="d-flex flex-column gap-4">
        @forelse($projectSummaries ?? [] as $summary)
            @php 
                $projectItem = $summary['project'];
                $percent = $summary['completion'] ?? 0;
                $phaseName = optional($summary['current_phase'])->phase_name ?? 'Structural Groundwork';
            @endphp
            
            <div class="project-item-card project-detail-trigger">
                <div class="row g-0 align-items-stretch">
                    <div class="col-12 col-md-4 col-lg-3 img-wrapper-frame">
                        <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=500&q=80" alt="Construction structural rendering" class="project-card-thumb">
                    </div>
                    
                    <div class="col-12 col-md-8 col-lg-9 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="project-card-name m-0">{{ $projectItem->project_name }}</h5>
                                    <span class="badge-status-pill {{ $percent == 100 ? 'status-completed' : 'status-progress' }}">
                                    {{ $percent == 100 ? 'COMPLETED' : 'IN PROGRESS' }}
                                </span>
                                </div>
                                <button class="btn btn-options-menu"><i class="bi bi-three-dots-vertical"></i></button>
                            </div>
                            
                            <p class="text-muted text-sm mt-1 mb-2">{{ $projectItem->description ?? 'Construction project delivery and milestones' }}</p>
                            <p class="text-muted text-sm m-0"><i class="bi bi-geo-alt me-1"></i> {{ $projectItem->project_location ?? $projectItem->location ?? 'Unspecified site' }}</p>
                        
                            <div class="row mt-4 g-3 pt-2 border-top" style="border-color: #f1f5f9 !important;">
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-calendar-event me-1"></i> Start Date</span>
                                    <span class="card-meta-val">{{ $projectItem->start_date ? date('M d, Y', strtotime($projectItem->start_date)) : 'TBD' }}</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-calendar-check me-1"></i> Target End</span>
                                    <span class="card-meta-val">{{ $projectItem->target_end_date ? date('M d, Y', strtotime($projectItem->target_end_date)) : 'TBD' }}</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-person me-1"></i> Manager</span>
                                    <span class="card-meta-val">{{ optional($projectItem->engineer)->name ?? 'Unassigned' }}</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-person-check me-1"></i> Supervisor</span>
                                    <span class="card-meta-val">{{ optional($projectItem->activeSupervisor)->name ?? 'Not assigned' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-end mt-4 pt-2 g-3">
                            <div class="col-12 col-sm-7 col-md-6 col-lg-5">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="progress-lbl-text">Overall Completion</span>
                                    <span class="progress-pct-text">{{ $percent }}%</span>
                                </div>
                                <div class="progress" style="height: 6px; background-color: #e2e8f0; border-radius: 999px;">
                                    <div class="progress-bar bg-success" style="width: {{ $percent }}%; border-radius: 999px;"></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-5 col-md-6 col-lg-7 d-flex justify-content-sm-between align-items-end flex-wrap gap-2">
                                <div>
                                    <span class="progress-lbl-text d-block mb-1">Current Active Stage</span>
                                    <span class="fw-bold text-dark text-sm">{{ $phaseName }}</span>
                                </div>
                                <button type="button" class="btn btn-view-project text-sm font-semibold px-3 py-2 rounded-xl project-detail-trigger" data-bs-toggle="modal" data-bs-target="#projectModal-{{ $projectItem->project_id }}">View Details <i class="bi bi-chevron-right ms-1 text-xs"></i></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="projectModal-{{ $projectItem->project_id }}" tabindex="-1" aria-labelledby="projectModalLabel-{{ $projectItem->project_id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0">
                            <div>
                                <h5 class="modal-title fw-bold" id="projectModalLabel-{{ $projectItem->project_id }}">{{ $projectItem->project_name }}</h5>
                                <p class="text-muted small mb-0">Database-backed project details and status snapshot</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="border rounded-3 p-3">
                                        <div class="text-muted small text-uppercase">Project status</div>
                                        <div class="fw-bold">{{ $percent == 100 ? 'Completed' : 'In progress' }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="border rounded-3 p-3">
                                        <div class="text-muted small text-uppercase">Overall completion</div>
                                        <div class="fw-bold text-success">{{ $percent }}%</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="border rounded-3 p-3">
                                        <div class="text-muted small text-uppercase">Current phase</div>
                                        <div class="fw-bold">{{ $phaseName }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="border rounded-3 p-3">
                                        <div class="text-muted small text-uppercase">Assigned manager</div>
                                        <div class="fw-bold">{{ optional($projectItem->engineer)->name ?? 'Unassigned' }}</div>
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
            
        @empty
            <div class="project-item-card">
                <div class="row g-0 align-items-stretch">
                    <div class="col-12 col-md-4 col-lg-3 img-wrapper-frame">
                        <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=500&q=80" alt="Building frame rendering" class="project-card-thumb">
                    </div>
                    <div class="col-12 col-md-8 col-lg-9 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="project-card-name m-0">No assigned projects yet</h5>
                                    <span class="badge-status-pill status-progress">EMPTY</span>
                                </div>
                                <button class="btn btn-options-menu" disabled><i class="bi bi-three-dots-vertical"></i></button>
                            </div>
                            <p class="text-muted text-sm mt-1 mb-2">You do not have any project assignments at this time.</p>
                            <p class="text-muted text-sm m-0"><i class="bi bi-geo-alt me-1"></i> Check back later or contact support.</p>
                        
                            <div class="row mt-4 g-2 pt-2 border-top" style="border-color: #f1f5f9 !important;">
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-calendar-event me-1"></i> Start Date</span>
                                    <span class="card-meta-val">-</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-calendar-check me-1"></i> Est. Completion</span>
                                    <span class="card-meta-val">-</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-person me-1"></i> PM Engineer</span>
                                    <span class="card-meta-val">-</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="card-meta-label"><i class="bi bi-person-check me-1"></i> Site Lead</span>
                                    <span class="card-meta-val">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-end mt-4 pt-2 g-3">
                            <div class="col-12 col-sm-7 col-md-6 col-lg-5">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="progress-lbl-text">Overall progress</span>
                                    <span class="progress-pct-text">0%</span>
                                </div>
                                <div class="progress" style="height: 6px; background-color: #e2e8f0; border-radius: 999px;">
                                    <div class="progress-bar bg-success" style="width: 0%; border-radius: 999px;"></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-5 col-md-6 col-lg-7 d-flex justify-content-sm-between align-items-end flex-wrap gap-2">
                                <div>
                                    <span class="progress-lbl-text d-block mb-1">Current Phase</span>
                                    <span class="fw-bold text-dark text-sm">N/A</span>
                                </div>
                                <button class="btn btn-view-project text-sm font-semibold px-3 py-2 rounded-xl" disabled>View Project <i class="bi bi-chevron-right ms-1 text-xs"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-2">
        <span class="text-muted text-sm">Showing active records</span>
        <nav>
            <ul class="pagination m-0 gap-1">
                <li class="page-item"><a class="page-link pagination-arrow" href="#"><i class="bi bi-chevron-left"></i></a></li>
                <li class="page-item active"><a class="page-link pagination-number" href="#">1</a></li>
                <li class="page-item"><a class="page-link pagination-arrow" href="#"><i class="bi bi-chevron-right"></i></a></li>
            </ul>
        </nav>
    </div>

</div>

<style>
    /* --- METRIC CARD HOVERS --- */
    .project-summary-box {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .bg-mint-container { background-color: #e6f7ed; }
    .bg-orange-container { background-color: #fff7ed; }
    .summary-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }
    .summary-subtext { font-size: 0.74rem; color: var(--text-muted); display: block; }
    .project-summary-box h3 { font-size: 1.5rem; font-weight: 800; margin: 0.1rem 0; color: var(--text-primary); }

    /* --- FILTER SYSTEM INPUT CONTROLS --- */
    .filter-search-input {
        background-color: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0.85rem 1rem 0.85rem 2.75rem;
        font-size: 0.94rem;
        min-width: 0;
    }
    .btn-filter-action {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 0.85rem 1.5rem;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        min-width: 140px;
    }
    @media (max-width: 767px) {
        .filter-search-input,
        .btn-filter-action {
            width: 100%;
        }
        .filter-search-input {
            padding-left: 2.75rem;
        }
        .btn-filter-action {
            justify-content: center;
        }
        .project-summary-box {
            flex-direction: column;
            align-items: stretch;
        }
    }

    /* --- HORIZONTAL PROJECT TRACKING DISPLAY CARDS --- */
    .project-item-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.005);
    }
    .img-wrapper-frame { position: relative; min-height: 220px; }
    .project-card-thumb { width: 100%; height: 100%; object-fit: cover; position: absolute; top:0; left:0; }
    
    .project-card-name { font-size: 1.2rem; font-weight: 800; color: var(--text-primary); }
    .badge-status-pill {
        font-size: 0.68rem;
        font-weight: 800;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        letter-spacing: 0.02em;
    }
    .status-progress { background-color: #e6f7ed; color: #16a34a; }
    .status-completed { background-color: #eff6ff; color: #2563eb; }

    .btn-options-menu { border:0; background: transparent; color: var(--text-muted); padding: 0.25rem; font-size: 1.1rem; }
    
    .card-meta-label { display: block; font-size: 0.74rem; color: var(--text-muted); font-weight: 500; margin-bottom: 0.1rem; }
    .card-meta-val { display: block; font-size: 0.82rem; font-weight: 700; color: #334155; }

    .progress-lbl-text { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; }
    .progress-pct-text { font-size: 0.85rem; font-weight: 700; color: var(--text-primary); }
    
    .btn-view-project {
        background-color: #fff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-view-project:hover { background-color: #f8fafc; border-color: #94a3b8; }

    /* --- PAGINATION SYMBOLS --- */
    .pagination-arrow, .pagination-number {
        border-radius: 8px !important;
        border: 1px solid #cbd5e1;
        color: var(--text-primary);
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
    .pagination .page-item.active .pagination-number {
        background-color: #013220;
        border-color: #013220;
        color: #fff;
    }
    .text-sm { font-size: 0.85rem; }
    .text-xs { font-size: 0.75rem; }
    .rounded-xl { border-radius: 12px !important; }
</style>
@endsection