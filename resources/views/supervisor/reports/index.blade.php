@extends('layouts.supervisor')

@section('title', 'Accomplishment Reports - Supervisor Workspace')
@section('page_title', 'Accomplishment Reports')

@push('styles')
    <style>
        :root {
            --cms-green-dark: #2a4028;
            --cms-green-light: #e8efe0;
            --cms-green-muted: rgba(42, 64, 40, 0.12);
            --cms-text-muted: #64748B;
        }

        .report-filter-card, .metric-card, .main-report-card {
            border-radius: 12px;
            border: 1px solid var(--cms-green-muted);
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .metric-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .table-report th {
            background-color: var(--cms-green-light) !important;
            color: var(--cms-green-dark);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
        }

        .table-report td {
            padding: 16px;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-pill {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            display: inline-block;
        }

        /* Drawer Customizations */
        .report-drawer {
            width: 450px !important;
            border-left: 1px solid var(--cms-green-muted);
        }

        .drawer-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--cms-green-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--cms-green-muted);
            padding-bottom: 0.5rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .img-thumbnail-grid {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 6px;
        }

        .more-images-badge {
            width: 65px;
            height: 65px;
            background: #f0f0f0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
            color: #555;
        }

        /* Timeline steps */
        .timeline-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 1rem;
        }
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }
        .timeline-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        .timeline-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 0.8rem;
        }
        .timeline-step.active .timeline-icon {
            border-color: #166534;
            background: #166534;
            color: #fff;
        }
        .timeline-step.current .timeline-icon {
            border-color: #ffc107;
            background: #ffc107;
            color: #fff;
        }

        /* REDESIGNED MODAL STYLES (MATCHING SCREENSHOT) */
        .custom-form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.4rem;
        }
        
        .modal-custom-input {
            border-radius: 6px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 8px 12px !important;
            font-size: 0.88rem !important;
            color: #334155 !important;
            background-color: #fff !important;
            transition: all 0.2s ease-in-out;
        }

        .modal-custom-input:focus {
            border-color: #166534 !important;
            box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.15) !important;
            outline: none;
        }

        .upload-drop-zone {
            border: 1px dashed #cbd5e1 !important;
            border-radius: 6px;
            background-color: #fafafa;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 135px;
        }

        .upload-drop-zone:hover {
            background-color: #F1F5F9;
            border-color: #166534 !important;
        }

        .modal-footer-btn-cancel {
            font-size: 0.88rem;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            color: #475569;
            background-color: #f1f5f9;
            border: none;
            transition: all 0.2s;
        }
        .modal-footer-btn-cancel:hover {
            background-color: #e2e8f0;
        }

        .modal-footer-btn-submit {
            font-size: 0.88rem;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            background-color: #166534;
            border: none;
            color: #fff;
            transition: all 0.2s;
        }
        .modal-footer-btn-submit:hover {
            background-color: #14532D;
        }

        .preview-file-chip {
            background: #F1F5F9;
            border: 1px solid rgba(22, 101, 52, 0.15);
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #166534;
        }
    </style>
@endpush

@section('content')
<section class="report-filter-card p-3 mb-4">
    <form method="GET" id="filterForm" class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">Project</label>
            <select name="project_id" id="projectSelect" class="form-select form-select-sm">
                <option value="" {{ request()->query->has('project_id') ? (request('project_id') === '' ? 'selected' : '') : (is_null($selectedProject) ? 'selected' : '') }}>All Projects</option>
                @foreach($assignedProjects as $project)
                    <option value="{{ $project->project_id }}" {{ request()->query->has('project_id') ? (request('project_id') == $project->project_id ? 'selected' : '') : ($selectedProject && $selectedProject->project_id == $project->project_id ? 'selected' : '') }}>{{ $project->project_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">Construction Phase</label>
            <select name="phase_id" id="phaseSelect" class="form-select form-select-sm">
                <option value="">All Phases</option>
                @foreach($projectPhases as $phase)
                    <option value="{{ $phase->phase_id }}" {{ request('phase_id') == $phase->phase_id ? 'selected' : '' }}>{{ $phase->phase_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold text-muted">Approval Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Returned</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">Report Date</label>
            <input type="date" name="report_date" value="{{ request('report_date') }}" class="form-control form-control-sm" />
        </div>
        <div class="col-12 col-md-1">
            <button type="submit" class="btn btn-sm btn-success w-100 style-btn d-flex align-items-center justify-content-center gap-1" style="background-color: var(--cms-green-dark); border: none; height: 31px;">
                <i class="bi bi-funnel small"></i> Filter
            </button>
        </div>
    </form>
</section>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-success-subtle text-success">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Total Reports</div>
                <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">Selected project</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-warning-subtle text-warning">
                <i class="bi bi-clock"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Pending Review</div>
                <h4 class="mb-0 fw-bold">{{ $stats['pending'] }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $stats['pending_percent'] }}% of total</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-success-subtle text-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Approved Reports</div>
                <h4 class="mb-0 fw-bold">{{ $stats['approved'] }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $stats['approved_percent'] }}% of total</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="metric-card p-3 d-flex align-items-center gap-3">
            <div class="metric-icon-wrapper bg-danger-subtle text-danger">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold">Rejected Reports</div>
                <h4 class="mb-0 fw-bold">{{ $stats['rejected'] }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $stats['rejected_percent'] }}% of total</span>
            </div>
        </div>
    </div>
</div>

<section class="main-report-card p-0 overflow-hidden mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-success-emphasis" style="color: var(--cms-green-dark) !important;">Accomplishment Reports</h5>
        <button class="btn btn-sm btn-success" style="background-color: var(--cms-green-dark); border: none;" data-bs-toggle="modal" data-bs-target="#createReportModal">
            + New Report
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-report table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Report Date</th>
                    <th>Project</th>
                    <th>Phase</th>
                    <th>Submitted By</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($reports->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No reports match the current filters.</td>
                    </tr>
                @else
                    @foreach($reports as $report)
                        @php
                            $status = $report->approval_status ?? 'pending';
                            $pillClass = $status === 'approved' ? 'bg-success-subtle text-success' : ($status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->report_date)->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ optional($report->report_date)->format('h:i A') ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->project)->project_name ?? 'Unknown Project' }}</div>
                                <div class="text-muted small">Building Construction</div>
                            </td>
                            <td>
                                <span class="text-dark fw-semibold">{{ optional($report->phase)->phase_name ?? 'Unassigned Phase' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-img bg-secondary text-white d-flex align-items-center justify-content-center fw-bold small">
                                        {{ strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.85rem;">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Supervisor</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-pill {{ $pillClass }}">{{ $status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="offcanvas" data-bs-target="#drawer-{{ $report->report_id }}" aria-controls="drawer">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('supervisor.api.reports.downloadPdf', $report->report_id) }}" data-report-id="{{ $report->report_id }}" class="btn btn-sm btn-light border download-report-link" title="Download PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <div class="offcanvas offcanvas-end report-drawer" tabindex="-1" id="drawer-{{ $report->report_id }}" aria-labelledby="drawerLabel">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="fw-bold mb-0 text-success-emphasis" id="drawerLabel">Report Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                
                                <div class="drawer-section-title mt-0">Report Information</div>
                                <div class="row g-3 small mb-2">
                                    <div class="col-6">
                                        <div class="text-muted font-monospace">Report ID</div>
                                        <div class="fw-bold">{{ $report->report_id }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Approval Status</div>
                                        <span class="status-pill {{ $pillClass }} p-1 px-2 d-inline-block m-0">{{ $status }}</span>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Project</div>
                                        <div class="fw-bold">{{ optional($report->project)->project_name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Reviewed By</div>
                                        <div class="fw-bold">{{ optional($report->reviewedBy)->name ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Construction Phase</div>
                                        <div class="fw-bold">{{ optional($report->phase)->phase_name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Approved By</div>
                                        <div class="fw-bold">{{ optional($report->approvedBy)->name ?? '-' }}</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted">Report Date</div>
                                        <div class="fw-bold">{{ optional($report->report_date)->format('M d, Y h:i A') ?? 'N/A' }}</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 my-3 p-2 bg-light rounded">
                                    <div class="avatar-img bg-secondary text-white d-flex align-items-center justify-content-center fw-bold small">
                                        {{ strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0" style="font-size:0.85rem;">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Supervisor</div>
                                    </div>
                                </div>

                                <div class="drawer-section-title">Construction Accomplishment</div>
                                <div class="p-3 bg-light rounded text-muted small" style="white-space: pre-line; line-height: 1.6;">
                                    {{ $report->report_text ?? 'No description items logs entered.' }}
                                </div>

                                <div class="drawer-section-title">Site Images</div>
                                @php $siteImages = is_array($report->site_images) ? $report->site_images : []; @endphp
                                @if(!empty($siteImages))
                                    <div class="d-flex gap-2 flex-wrap">
                                        @foreach(array_slice($siteImages, 0, 3) as $image)
                                            @php $imageUrl = is_string($image) && $image ? asset('storage/' . ltrim($image, '/')) : ''; @endphp
                                            @if($imageUrl)
                                                <button type="button" class="img-thumbnail-grid d-flex align-items-center justify-content-center overflow-hidden p-0 lightbox-trigger" style="background: #f9fafb; border: 2px solid #e5e7eb; width: 72px; height: 72px;" data-full-image="{{ $imageUrl }}" aria-label="Preview site image">
                                                    <img src="{{ $imageUrl }}" alt="Site image" class="w-100 h-100 object-fit-cover">
                                                </button>
                                            @endif
                                        @endforeach
                                        @if(count($siteImages) > 3)
                                            <div class="more-images-badge d-flex align-items-center justify-content-center" style="background: #f9fafb; border: 2px solid #e5e7eb; color: #6b7280;">+{{ count($siteImages) - 3 }} more</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="no-images-message">No uploaded images</div>
                                @endif

                                <div class="drawer-section-title">Approval Timeline</div>
                                <div class="timeline-container small px-2">
                                    <div class="timeline-step active">
                                        <div class="timeline-icon"><i class="bi bi-check"></i></div>
                                        <div class="fw-bold" style="font-size:0.75rem;">Submitted</div>
                                        <div class="text-muted" style="font-size:0.65rem;">{{ optional($report->created_at)->format('M d, Y') }}</div>
                                    </div>
                                    <div class="timeline-step {{ $status !== 'pending' ? 'active' : 'current' }}">
                                        <div class="timeline-icon">
                                            @if($status === 'pending') <i class="bi bi-clock"></i> @else <i class="bi bi-check"></i> @endif
                                        </div>
                                        <div class="fw-bold" style="font-size:0.75rem;">Pending Review</div>
                                        <div class="text-muted" style="font-size:0.65rem;">{{ optional($report->reviewed_at)->format('M d, Y') ?? 'Pending review' }}</div>
                                    </div>
                                    <div class="timeline-step {{ $status === 'approved' ? 'active' : ($status === 'rejected' ? 'active' : '') }}">
                                        <div class="timeline-icon"><i class="bi bi-circle"></i></div>
                                        <div class="fw-bold" style="font-size:0.75rem;">{{ $status === 'approved' ? 'Approved' : ($status === 'rejected' ? 'Rejected' : 'Finalized') }}</div>
                                        <div class="text-muted" style="font-size:0.65rem;">{{ optional($report->approved_at)->format('M d, Y') ?? optional($report->rejected_at)->format('M d, Y') ?? 'Pending' }}</div>
                                    </div>
                                </div>

                            </div>
                            <div class="offcanvas-footer border-top p-3 d-flex gap-2">
                                <a href="{{ route('supervisor.api.reports.downloadPdf', $report->report_id) }}" data-report-id="{{ $report->report_id }}" class="btn btn-sm btn-outline-secondary w-50 d-flex align-items-center justify-content-center gap-1 download-report-link"><i class="bi bi-download"></i> Download PDF</a>
                                <button class="btn btn-sm btn-success w-50 text-white fw-bold" style="background-color: var(--cms-green-dark); border: none;" data-bs-dismiss="offcanvas">Close</button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="p-3 bg-light d-flex justify-content-between align-items-center border-top">
        <div class="small text-muted">
            Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
        </div>
        <div>
            {{ $reports->appends(request()->only(['project_id', 'phase_id', 'status', 'report_date']))->links() }}
        </div>
    </div>
</section>

<div class="modal fade" id="createReportModal" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 850px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            
            <div class="modal-header border-0 px-4 pt-4 pb-2" style="background: #fff;">
                <div>
                    <h5 class="modal-title fw-bold" id="createReportModalLabel" style="color: #2a4028; font-size: 1.35rem; letter-spacing: -0.02em;">
                        Create Accomplishment Report
                    </h5>
                    <p class="text-muted small mb-0">Fill out the field parameters below to submit a new progress report.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="box-shadow: none;"></button>
            </div>

            <form id="createReportForm" action="#" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-3">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="modal_project_id" class="custom-form-label">Project Name</label>
                            <select name="project_id" id="modal_project_id" class="form-select modal-custom-input w-100" required>
                                <option value="" selected disabled>Select Project</option>
                                @foreach($assignedProjects as $project)
                                    <option value="{{ $project->project_id }}">{{ $project->project_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="modal_phase_id" class="custom-form-label">Project Phase</label>
                            <select name="phase_id" id="modal_phase_id" class="form-select modal-custom-input w-100" required disabled>
                                <option value="" selected disabled>Select Phase</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="modal_task_id" class="custom-form-label">Work Progress / Task</label>
                            <select name="task_id" id="modal_task_id" class="form-select modal-custom-input w-100" required>
                                <option value="" selected disabled>Select Task</option>
                                <option value="1">Structural Concrete Pouring</option>
                                <option value="2">Rebar Installation & Bending</option>
                                <option value="3">Masonry Wall Partitioning</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="modal_accomplishment" class="custom-form-label">Accomplishment (%)</label>
                            <input type="number" name="accomplishment_percentage" id="modal_accomplishment" class="form-control modal-custom-input w-100" min="0" max="100" placeholder="e.g. 45" required>
                        </div>
                        <div class="col-md-3">
                            <label for="modal_manpower" class="custom-form-label">Manpower Count</label>
                            <input type="number" name="manpower_count" id="modal_manpower" class="form-control modal-custom-input w-100" min="0" placeholder="e.g. 12" required>
                        </div>
                        <div class="col-md-3">
                            <label for="modal_equipment" class="custom-form-label">Equipment Used</label>
                            <input type="text" name="equipment_used" id="modal_equipment" class="form-control modal-custom-input w-100" placeholder="e.g. Excavator, Concrete Mixer">
                        </div>
                        <div class="col-md-3">
                            <label for="modal_weather" class="custom-form-label">Weather Conditions</label>
                            <select name="weather_condition" id="modal_weather" class="form-select modal-custom-input w-100" required>
                                <option value="" selected disabled>Select Weather</option>
                                <option value="Sunny">Sunny / Clear</option>
                                <option value="Cloudy">Cloudy</option>
                                <option value="Rainy">Rainy</option>
                                <option value="Stormy">Stormy</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="modal_report_text" class="custom-form-label">Remarks & Observations</label>
                            <textarea name="report_text" id="modal_report_text" rows="5" class="form-control modal-custom-input w-100" placeholder="Provide detailed field descriptions, updates, roadblocks, or specific achievements..." style="resize: none; height: 135px;" required></textarea>
                        </div>
                        <div class="col-md-5">
                            <label class="custom-form-label">Site Documentation Images</label>
                            <div class="upload-drop-zone p-3 position-relative" onclick="document.getElementById('modal_report_images').click()">
                                <i class="bi bi-cloud-arrow-up-fill text-muted mb-1" style="font-size: 2.2rem;"></i>
                                <span class="d-block small text-muted fw-semibold mb-0">Drag and drop images here</span>
                                <span class="text-secondary" style="font-size: 0.75rem;">or click to browse local files</span>
                                <input type="file" name="images[]" id="modal_report_images" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" multiple accept="image/*" style="cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div id="selectedImagesContainer" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex justify-content-end gap-2" style="background: #fff;">
                    <button type="button" class="modal-footer-btn-cancel" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="modal-footer-btn-submit">
                        Submit Report
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalProjectSelect = document.getElementById('modal_project_id');
        const modalPhaseSelect = document.getElementById('modal_phase_id');
        const imageInput = document.getElementById('modal_report_images');
        const previewContainer = document.getElementById('selectedImagesContainer');

        // Asynchronous Dependent Dropdown Option Load Pipeline
        if (modalProjectSelect) {
            modalProjectSelect.addEventListener('change', function() {
                const projectId = this.value;
                if (!projectId) return;

                modalPhaseSelect.innerHTML = '<option value="" selected disabled>Loading correspond phases...</option>';
                modalPhaseSelect.disabled = false;

                fetch(`/supervisor/api/projects/${projectId}/phases`)
                    .then(response => response.json())
                    .then(data => {
                        modalPhaseSelect.innerHTML = '<option value="" selected disabled>Select Phase</option>';
                        if (data.length === 0) {
                            modalPhaseSelect.innerHTML = '<option value="" disabled>No active phases identified.</option>';
                            return;
                        }
                        data.forEach(phase => {
                            modalPhaseSelect.innerHTML += `<option value="${phase.phase_id}">${phase.phase_name}</option>`;
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        modalPhaseSelect.innerHTML = '<option value="" disabled>Error loading phases.</option>';
                    });
            });
        }

        // Live Attachment Chip Generation Feature
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                Array.from(this.files).forEach(file => {
                    const chip = document.createElement('span');
                    chip.className = 'preview-file-chip';
                    chip.innerHTML = `<i class="bi bi-image small"></i> ${file.name.substring(0, 18)}${file.name.length > 18 ? '...' : ''}`;
                    previewContainer.appendChild(chip);
                });
            });
        }

        // Action handles for original download scripts block
        document.querySelectorAll('.download-report-link').forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Exporting Report',
                    html: 'Preparing your accomplishment report...',
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                });

                const url = this.href;
                const reportId = this.dataset.reportId || 'report';

                fetch(url, {
                    headers: {
                        'Accept': 'text/html,application/pdf,*/*',
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(text || 'Failed to export report');
                            });
                        }
                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('application/json')) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Unable to export report');
                            });
                        }

                        const contentDisposition = response.headers.get('content-disposition') || '';
                        const fileNameMatch = contentDisposition.match(/filename=\"?([^\";]+)\"?/);
                        const fileName = fileNameMatch ? fileNameMatch[1] : `report_${reportId}.html`;

                        return response.blob().then(blob => ({ blob, fileName }));
                    })
                    .then(({ blob, fileName }) => {
                        const blobUrl = URL.createObjectURL(blob);
                        const downloadLink = document.createElement('a');
                        downloadLink.href = blobUrl;
                        downloadLink.download = fileName;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        downloadLink.remove();
                        URL.revokeObjectURL(blobUrl);

                        Swal.fire({
                            title: 'Export Complete',
                            text: 'Your report is downloading now.',
                            icon: 'success',
                            confirmButtonColor: '#166534'
                        });
                    })
                    .catch(error => {
                        console.error(error);
                        Swal.fire({
                            title: 'Export Failed',
                            text: error.message || 'Unable to export the report at this time.',
                            icon: 'error',
                            confirmButtonColor: '#166534'
                        });
                    });
            });
        });
    });
</script>
@endpush