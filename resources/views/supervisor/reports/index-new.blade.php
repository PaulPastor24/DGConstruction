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
            box-shadow: 0 4px 12px rgba(9, 96, 86, 0.03);
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
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-pill-approved {
            background: #DCFCE7;
            color: #15803D;
        }
        .status-pill-pending {
            background: #F1F5F9;
            color: #64748B;
        }
        .status-pill-warning {
            background: #FEF3C7;
            color: #D97706;
        }
        .status-pill-error {
            background: #FEE2E2;
            color: #DC2626;
        }

        /* Report Details Modal Layout */
        .report-details-modal .modal-dialog {
            max-width: 1080px;
        }

        .report-detail-card,
        .report-detail-sidebar {
            border-radius: 16px;
            border: 1px solid rgba(9, 96, 86, 0.12);
            background: #ffffff;
            box-shadow: 0 18px 42px rgba(9, 96, 86, 0.06);
        }

        .report-detail-card {
            padding: 2rem;
        }

        .report-detail-sidebar {
            background: #F8FAFC;
            border-color: rgba(22, 101, 52, 0.12);
        }

        .report-detail-sidebar .img-thumbnail-grid {
            width: 100%;
            max-width: 108px;
            height: 88px;
            min-width: 88px;
        }

        .report-detail-sidebar .more-images-badge {
            width: auto;
            min-width: 108px;
            background: #F1F5F9;
            color: #166534;
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

        /* Progress Steps Timeline */
        .timeline-container {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 1rem;
            padding: 0 0.75rem;
        }
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 20%;
            right: 20%;
            height: 2px;
            background: #d9e5dd;
            z-index: 1;
        }
        .timeline-step {
            text-align: center;
            position: relative;
            z-index: 2;
            flex: 1;
            min-width: 0;
        }
        .timeline-step:first-child {
            text-align: left;
        }
        .timeline-step:last-child {
            text-align: right;
        }
        .timeline-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #d9e5dd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 0.9rem;
        }
        .timeline-step.active .timeline-icon {
            border-color: #166534;
            background: #166534;
            color: #fff;
        }
        .timeline-step.current .timeline-icon {
            border-color: #ffc107;
            background: #fff;
            color: #ffc107;
        }

        /* ========================================== */
        /* MODAL REDESIGN CLASSES FROM UI IMAGE SPEC  */
        /* ========================================== */
        .cms-modal .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        .cms-modal .modal-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 24px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .cms-modal .modal-title {
            color: #1e293b;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .cms-modal .modal-subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 2px;
        }

        .cms-modal .modal-body {
            padding: 24px;
            background-color: #ffffff;
        }

        .cms-form-section-header {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--cms-green-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 14px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cms-form-section-header::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background-color: #f1f5f9;
        }

        .cms-form-group {
            margin-bottom: 18px;
        }

        .cms-form-label {
            display: block;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }

        .cms-form-control {
            width: 100%;
            padding: 9px 12px;
            font-size: 0.9rem;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #1e293b;
            transition: all 0.15s ease;
        }

        .cms-form-control:focus {
            outline: none;
            border-color: var(--cms-green-dark);
            box-shadow: 0 0 0 3px rgba(9, 96, 86, 0.12);
        }

        .cms-form-control:disabled {
            background-color: #f8fafc;
            color: #94a3b8;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        #modal_phase_id,
        #modal_project_id {
            pointer-events: auto !important;
            cursor: pointer !important;
        }

        .cms-form-control::placeholder {
            color: #94a3b8;
        }

        /* Drag & Drop Area from Image spec */
        .cms-file-upload-zone {
            border: 2px dashed #cbd5e1;
            background-color: #f8fafc;
            border-radius: 14px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 220px;
            display: grid;
            place-items: center;
        }

        .cms-file-upload-zone:hover,
        .cms-file-upload-zone.dragover {
            border-color: var(--cms-green-dark);
            background-color: var(--cms-green-light);
        }

        .cms-file-upload-icon {
            font-size: 1.9rem;
            color: #64748b;
            margin-bottom: 10px;
        }

        #uploadPromptText {
            width: 100%;
        }

        .cms-file-upload-zone.has-images #uploadPromptText {
            display: none;
        }

        .cms-file-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 12px;
            width: 100%;
            margin-top: 18px;
        }

        .cms-file-preview-thumb {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(15, 66, 42, 0.08);
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(15, 66, 42, 0.06);
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cms-file-preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .cms-file-preview-thumb .preview-label {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 8px 10px;
            background: rgba(15, 66, 42, 0.75);
            color: #f8fafc;
            font-size: 0.72rem;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cms-modal .modal-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .btn-cms-secondary {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 0.88rem;
            transition: all 0.15s;
        }

        .btn-cms-secondary:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .btn-cms-primary {
            background-color: var(--cms-green-dark);
            color: #ffffff;
            border: none;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 6px;
            font-size: 0.88rem;
            transition: all 0.15s;
        }

        .btn-cms-primary:hover {
            background-color: #074740;
            color: #ffffff;
        }

        .preview-file-chip-new {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #475569;
        }
    </style>
@endpush

@section('content')
@php
    $totalCount = $reports->total() ?? 0;
    $pendingCount = $reports->where('approval_status', 'pending')->count(); 
    $approvedCount = $reports->where('approval_status', 'approved')->count();
    $rejectedCount = $reports->where('approval_status', 'rejected')->count();
@endphp

<section class="report-filter-card p-3 mb-4">
    <form id="filterForm" method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">Project</label>
            <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="" {{ request('project_id') === null || request('project_id') === '' ? 'selected' : '' }}>All Projects</option>
                @foreach($assignedProjects as $project)
                    <option value="{{ $project->project_id }}" {{ request('project_id') == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">Construction Phase</label>
            <select name="phase_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Phases</option>
                @foreach($filterPhases as $phase)
                    <option value="{{ $phase->phase_id }}" {{ request('phase_id') == $phase->phase_id ? 'selected' : '' }}>{{ $phase->phase_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2">
            <label class="form-label small fw-bold text-muted">Approval Status</label>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Returned</option>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small fw-bold text-muted">Report Date</label>
            <input type="date" name="report_date" value="{{ request('report_date') }}" class="form-control form-control-sm" onchange="this.form.submit()" />
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
                <h4 class="mb-0 fw-bold">{{ $totalCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">All time</span>
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
                <h4 class="mb-0 fw-bold">{{ $pendingCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $totalCount > 0 ? round(($pendingCount/$totalCount)*100, 2) : 0 }}% of total</span>
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
                <h4 class="mb-0 fw-bold">{{ $approvedCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $totalCount > 0 ? round(($approvedCount/$totalCount)*100, 2) : 0 }}% of total</span>
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
                <h4 class="mb-0 fw-bold">{{ $rejectedCount }}</h4>
                <span class="text-muted" style="font-size: 0.75rem;">{{ $totalCount > 0 ? round(($rejectedCount/$totalCount)*100, 2) : 0 }}% of total</span>
            </div>
        </div>
    </div>
</div>

<section class="main-report-card p-0 overflow-hidden mb-4">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0" style="color: var(--cms-green-dark) !important;">Accomplishment Reports</h5>
        <button class="btn btn-sm btn-success" style="background-color: var(--cms-green-dark); border: none;" data-bs-toggle="modal" data-bs-target="#createReportModal" {{ $assignedProjects->isEmpty() ? 'disabled' : '' }}>
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
                        <td colspan="6" class="text-center py-4 text-muted">No configuration records matched your parameters.</td>
                    </tr>
                @else
                    @foreach($reports as $report)
                        @php
                            $status = $report->approval_status ?? 'pending';
                            $pillClass = match ($status) {
                                'approved' => 'status-pill status-pill-approved',
                                'rejected' => 'status-pill status-pill-error',
                                default => 'status-pill status-pill-pending',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->report_date)->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ optional($report->report_date)->format('h:i A') ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->project)->project_name ?? 'Unknown' }}</div>
                                <div class="text-muted small">Building Construction</div>
                            </td>
                            <td>
                                <span class="text-dark fw-semibold">{{ optional($report->phase)->phase_name ?? 'General Phase' }}</span>
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
                                    <button class="btn btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#reportDetailsModal-{{ $report->report_id }}" style="background: white; color: var(--cms-green-dark); transition: all 0.2s ease;" onmouseover="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1.2)';" onmouseout="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1)';">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm download-report-btn" data-report-id="{{ $report->report_id }}" style="background: white; color: var(--cms-green-dark); transition: all 0.2s ease;" onmouseover="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1.2)';" onmouseout="this.style.color='var(--cms-green-dark)'; this.style.transform='scale(1)';">
                                        <i class="bi bi-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @php
                            $siteImages = is_array($report->site_images) ? $report->site_images : [];
                            $siteImageUrls = collect($siteImages)
                                ->map(function ($path) {
                                    if (!$path) {
                                        return null;
                                    }
                                    return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                                })
                                ->filter()
                                ->values();
                            $timelineStatus = $status === 'approved' ? 'active' : ($status === 'rejected' ? 'active' : 'current');
                        @endphp
                        <div class="modal fade report-details-modal" id="reportDetailsModal-{{ $report->report_id }}" tabindex="-1" aria-labelledby="reportDetailsModalLabel-{{ $report->report_id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header" style="background: #ffffff; border-bottom: 2px solid var(--cms-green-dark);">
                                        <div>
                                            <h5 class="modal-title fw-bold" id="reportDetailsModalLabel-{{ $report->report_id }}" style="color: var(--cms-green-dark);">Report Details</h5>
                                            <div class="text-muted small">A complete summary of the selected accomplishment report.</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4">
                                        <div class="row gx-4 gy-4">
                                            <div class="col-12 col-xl-7">
                                                <div class="report-detail-card p-4">
                                                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-4 p-3 rounded-3" style="background: #fff;">
                                                        <div>
                                                            <div class="small text-uppercase text-muted" style="font-weight: 600;">Report ID</div>
                                                            <div class="fw-bold text-dark" style="font-size: 1.1rem;">RPT-2026-{{ str_pad($report->report_id, 4, '0', STR_PAD_LEFT) }}</div>
                                                        </div>
                                                        <div class="text-sm-end">
                                                            <div class="small text-uppercase text-muted" style="font-weight: 600;">Approval Status</div>
                                                            <span class="status-pill {{ $pillClass }} p-2 mt-1 d-inline-block">{{ $status }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mb-4 small">
                                                        <div class="col-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Project</div>
                                                            <div class="text-dark">{{ optional($report->project)->project_name ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Construction Phase</div>
                                                            <div class="text-dark">{{ optional($report->phase)->phase_name ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Report Date</div>
                                                            <div class="text-dark">{{ optional($report->report_date)->format('M d, Y h:i A') ?? 'N/A' }}</div>
                                                        </div>
                                                        <div class="col-6 p-3 rounded" style="background: #f9fafb;">
                                                            <div class="fw-semibold text-muted mb-1">Submitted By</div>
                                                            <div class="text-dark">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 rounded-3 mb-4" style="white-space: pre-line; line-height: 1.7; background: #f9fafb;">
                                                        <div class="fw-bold mb-2" style="color: var(--cms-green-dark);">Construction Accomplishment</div>
                                                        <p class="mb-0 text-dark small">{{ $report->report_text ?? 'No description logs reported.' }}</p>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-12 col-md-6">
                                                            <div class="p-3 rounded-3" style="background: #f9fafb;">
                                                                <div class="fw-semibold text-muted mb-1">Reviewed By</div>
                                                                <div class="text-dark">{{ optional($report->reviewedBy)->name ?? 'Pending review' }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <div class="p-3 rounded-3" style="background: #f9fafb;">
                                                                <div class="fw-semibold text-muted mb-1">Approved By</div>
                                                                <div class="text-dark">{{ optional($report->approvedBy)->name ?? 'Pending approval' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="p-3 rounded-3" style="background: #f9fafb;">
                                                        <div class="fw-semibold text-muted mb-1">Approval Remarks</div>
                                                        <div class="text-dark small">{{ $report->approval_remarks ?? 'No remarks' }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 col-xl-5">
                                                <div class="report-detail-sidebar p-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="fw-bold" style="color: var(--cms-green-dark);">Site Images</div>
                                                        <div class="small text-muted">{{ $siteImageUrls->count() }} uploaded</div>
                                                    </div>
                                                    @if($siteImageUrls->isEmpty())
                                                        <div class="text-muted small border rounded-3 p-3" style="background: #f9fafb;">No site images were attached to this report.</div>
                                                    @else
                                                        <div class="d-flex flex-wrap gap-2 mb-4">
                                                            @foreach($siteImageUrls->take(4) as $imageUrl)
                                                                <a href="{{ $imageUrl }}" target="_blank" rel="noopener" class="img-thumbnail-grid d-flex align-items-center justify-content-center overflow-hidden p-0" style="background: #f9fafb; border: 2px solid #e5e7eb; width: 72px; height: 72px;">
                                                                    <img src="{{ $imageUrl }}" alt="Site image" class="w-100 h-100 object-fit-cover">
                                                                </a>
                                                            @endforeach
                                                            @if($siteImageUrls->count() > 4)
                                                                <div class="more-images-badge d-flex align-items-center justify-content-center" style="background: #f9fafb; border: 2px solid #e5e7eb; color: #6b7280;">+{{ $siteImageUrls->count() - 4 }} more</div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="fw-bold mb-3" style="color: var(--cms-green-dark);">Approval Timeline</div>
                                                    <div class="timeline-container small px-1">
                                                        <div class="timeline-step active">
                                                            <div class="timeline-icon"><i class="bi bi-check"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Submitted</div>
                                                        </div>
                                                        <div class="timeline-step {{ $status !== 'pending' ? 'active' : 'current' }}">
                                                            <div class="timeline-icon"><i class="bi bi-clock"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Under Review</div>
                                                        </div>
                                                        <div class="timeline-step {{ $status === 'approved' ? 'active' : '' }}">
                                                            <div class="timeline-icon"><i class="bi bi-circle"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Approved</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-center" style="padding-top: 2rem; margin-top: 2rem; border-top: 2px solid var(--cms-green-muted);">
                                                    <button class="btn btn-cms-primary download-report-btn" data-report-id="{{ $report->report_id }}">
                                                        <i class="bi bi-download me-2"></i> Download PDF
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div class="p-3 bg-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 border-top">
        <div class="small text-muted">
            Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports
        </div>
        <div>
            @if($reports->hasPages())
                {{ $reports->appends(request()->only(['project_id', 'phase_id', 'status', 'report_date', 'report_date_from', 'report_date_to', 'search']))->links('pagination::bootstrap-5') }}
            @else
                <nav aria-label="Report pagination" class="pagination">
                    <span class="page-item active"><span class="page-link">1</span></span>
                </nav>
            @endif
        </div>
    </div>
</section>

<div class="modal fade cms-modal" id="createReportModal" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="createReportModalLabel">Create Accomplishment Report</h5>
                    <p class="cms-modal-subtitle modal-subtitle">Fill out the form below to document and submit daily construction progress.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="createReportForm" action="{{ route('supervisor.reports.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    
                    <div class="cms-form-section-header">Project Context</div>
                    <div class="row">
                                <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_project_id" class="cms-form-label">Project Assignment <span class="text-danger">*</span></label>
                            <select name="project_id" id="modal_project_id" class="form-select cms-form-control" required>
                                @if($assignedProjects->isEmpty())
                                    <option value="" selected disabled>No assigned projects available</option>
                                @else
                                    <option value="" disabled>Select assigned project...</option>
                                    @foreach($assignedProjects as $project)
                                        <option value="{{ $project->project_id }}" {{ optional($modalProject ?? $selectedProject)->project_id == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_phase_id" class="cms-form-label">Construction Phase <span class="text-danger">*</span></label>
                            <select name="phase_id" id="modal_phase_id" class="form-select cms-form-control" required>
                                <option value="" selected disabled>{{ $projectPhases->isEmpty() ? 'Select project first...' : 'Select construction phase...' }}</option>
                                @foreach($projectPhases as $phase)
                                    <option value="{{ $phase->phase_id }}">{{ $phase->phase_name }}</option>
                                @endforeach
                            </select>
                            @if($projectPhases->isEmpty())
                                <div class="text-muted small mt-1">No phases are available for the selected project.</div>
                            @endif
                        </div>
                        <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_report_date" class="cms-form-label">Report Date <span class="text-danger">*</span></label>
                            <input type="date" name="report_date" id="modal_report_date" class="cms-form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6 cms-form-group">
                            <label for="modal_report_text" class="cms-form-label">Accomplishment Summary <span class="text-danger">*</span></label>
                            <textarea name="report_text" id="modal_report_text" rows="4" class="cms-form-control" placeholder="Enter the accomplishment report text for this project and phase." required></textarea>
                        </div>
                    </div>

                    <div class="cms-form-section-header">Upload Site Images <span class="text-muted">(Optional)</span></div>
                    <div class="row">
                        <div class="col-12 cms-form-group">
                            <div id="imageUploadZone" class="cms-file-upload-zone" onclick="document.getElementById('modal_report_images').click()">
                                <div id="uploadPromptText">
                                    <div class="cms-file-upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Click to upload or drag files here</h6>
                                    <p class="text-muted mb-0 small">Supports PNG, JPG, JPEG, WEBP formats up to 5MB per image.</p>
                                </div>
                                <div id="selectedImagesContainer" class="cms-file-preview-grid"></div>
                                <input type="file" name="site_images[]" id="modal_report_images" class="d-none" multiple accept="image/png,image/jpeg,image/jpg,image/webp" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cms-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-cms-primary d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-check"></i> Submit Accomplishment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalProjectSelect = document.getElementById('modal_project_id');
        const modalPhaseSelect = document.getElementById('modal_phase_id');
        const imageInput = document.getElementById('modal_report_images');
        const previewContainer = document.getElementById('selectedImagesContainer');
        const filterForm = document.getElementById('filterForm');
        const createReportForm = document.getElementById('createReportForm');

        const phasesApiRouteTemplate = '{{ route('supervisor.api.reports.phases', ['project_id' => 'PROJECT_ID']) }}';
        const phasePlaceholder = '<option value="" selected disabled>Select construction phase...</option>';
        const loadingPlaceholder = '<option value="" selected disabled>Loading phases...</option>';
        const errorPlaceholder = '<option value="" disabled>Error loading phases.</option>';
        const emptyPlaceholder = '<option value="" disabled>No phases available.</option>';

        function enablePhaseSelect() {
            modalPhaseSelect.disabled = false;
            modalPhaseSelect.removeAttribute('disabled');
            modalPhaseSelect.style.pointerEvents = 'auto';
            modalPhaseSelect.style.cursor = 'pointer';
        }

        function disablePhaseSelect() {
            modalPhaseSelect.disabled = true;
            modalPhaseSelect.setAttribute('disabled', 'disabled');
            modalPhaseSelect.style.pointerEvents = 'none';
            modalPhaseSelect.style.cursor = 'not-allowed';
        }

        function renderPhaseOptions(phases) {
            modalPhaseSelect.innerHTML = phasePlaceholder;
            if (!phases || phases.length === 0) {
                modalPhaseSelect.innerHTML = emptyPlaceholder;
                enablePhaseSelect();
                return;
            }
            phases.forEach(phase => {
                const option = document.createElement('option');
                option.value = phase.phase_id;
                option.textContent = phase.phase_name;
                modalPhaseSelect.appendChild(option);
            });
            enablePhaseSelect();
        }

        function loadProjectPhases(projectId) {
            modalPhaseSelect.innerHTML = loadingPlaceholder;
            enablePhaseSelect();

            const endpoint = phasesApiRouteTemplate.replace('PROJECT_ID', encodeURIComponent(projectId));

            fetch(endpoint, {
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Phase load failed');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && Array.isArray(data.phases)) {
                        renderPhaseOptions(data.phases);
                        return;
                    }
                    modalPhaseSelect.innerHTML = errorPlaceholder;
                    enablePhaseSelect();
                })
                .catch(() => {
                    modalPhaseSelect.innerHTML = errorPlaceholder;
                    enablePhaseSelect();
                });
        }

        function initializePhaseDropdown() {
            if (modalProjectSelect && modalProjectSelect.value) {
                loadProjectPhases(modalProjectSelect.value);
            } else {
                modalPhaseSelect.innerHTML = emptyPlaceholder;
                enablePhaseSelect();
            }
        }

        if (modalProjectSelect) {
            modalProjectSelect.addEventListener('change', function() {
                const projectId = this.value;
                if (!projectId) {
                    modalPhaseSelect.innerHTML = emptyPlaceholder;
                    enablePhaseSelect();
                    return;
                }
                loadProjectPhases(projectId);
            });
        }

        const createReportModal = document.getElementById('createReportModal');
        if (createReportModal) {
            createReportModal.addEventListener('shown.bs.modal', function () {
                initializePhaseDropdown();
            });
        }

        initializePhaseDropdown();

        let selectedFiles = [];
        const uploadZone = document.getElementById('imageUploadZone');
        const uploadPromptText = document.getElementById('uploadPromptText');

        function renderImagePreviews(files) {
            previewContainer.innerHTML = '';
            if (!files || files.length === 0) {
                uploadZone.classList.remove('has-images');
                return;
            }

            uploadZone.classList.add('has-images');
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                const previewThumb = document.createElement('div');
                previewThumb.className = 'cms-file-preview-thumb';

                const img = document.createElement('img');
                img.alt = file.name;
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);

                const label = document.createElement('div');
                label.className = 'preview-label';
                label.textContent = file.name;

                previewThumb.appendChild(img);
                previewThumb.appendChild(label);
                previewContainer.appendChild(previewThumb);
            });
        }

        function updateImageInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            imageInput.files = dataTransfer.files;
            renderImagePreviews(selectedFiles);
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    return;
                }
                const exists = selectedFiles.some(existing => existing.name === file.name && existing.size === file.size && existing.type === file.type);
                if (!exists) {
                    selectedFiles.push(file);
                }
            });
            updateImageInputFiles();
        }

        imageInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        if (uploadZone) {
            uploadZone.addEventListener('dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.add('dragover');
            });
            uploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.add('dragover');
            });
            uploadZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.remove('dragover');
            });
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.remove('dragover');
                if (e.dataTransfer && e.dataTransfer.files.length) {
                    handleFiles(e.dataTransfer.files);
                }
            });
        }

        if (createReportForm) {
            createReportForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!modalProjectSelect.value) {
                    Swal.fire({
                        title: 'Project Required',
                        text: 'You must select an assigned project before submitting a report.',
                        icon: 'warning',
                        confirmButtonColor: '#166534',
                        customClass: { confirmButton: 'btn-cms-primary' },
                        buttonsStyling: false,
                    });
                    return;
                }

                if (modalPhaseSelect.disabled || !modalPhaseSelect.value) {
                    Swal.fire({
                        title: 'Phase Required',
                        text: 'Please select a construction phase for this project before submitting.',
                        icon: 'warning',
                        confirmButtonColor: '#166534',
                        customClass: { confirmButton: 'btn-cms-primary' },
                        buttonsStyling: false,
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Submission',
                    text: 'Submit this accomplishment report for review?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#166534',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        confirmButton: 'btn-cms-primary',
                        cancelButton: 'btn-cms-secondary'
                    },
                    buttonsStyling: false,
                }).then(result => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    const formData = new FormData(createReportForm);
                    Swal.fire({
                        title: 'Submitting report...',
                        html: 'Please wait while your report is uploaded.',
                        didOpen: () => Swal.showLoading(),
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });

                    fetch(createReportForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: formData,
                    })
                        .then(async response => {
                            const data = await response.json().catch(() => null);
                            if (!response.ok) {
                                let message = 'Server error while submitting the report.';
                                if (data) {
                                    if (data.message) {
                                        message = data.message;
                                    } else if (data.errors) {
                                        message = Object.values(data.errors).flat().join(' ');
                                    }
                                }
                                throw { message };
                            }
                            return data;
                        })
                        .then(data => {
                            Swal.fire({
                                title: 'Report Submitted',
                                text: 'Your accomplishment report was submitted successfully.',
                                icon: 'success',
                                confirmButtonColor: '#166534',
                            }).then(() => {
                                window.location.reload();
                            });
                        })
                        .catch(error => {
                            const message = (error && (error.message || error.error || 'Something went wrong.')) || 'Something went wrong.';
                            Swal.fire({
                                title: 'Submission Failed',
                                text: message,
                                icon: 'error',
                                confirmButtonColor: '#c92a2a',
                            });
                        });
                });
            });
        }

        if (filterForm) {
            const filterControls = filterForm.querySelectorAll('select[name="project_id"], select[name="phase_id"], select[name="status"], input[name="report_date"]');
            filterControls.forEach(control => {
                control.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        }
    });
</script>
@endpush