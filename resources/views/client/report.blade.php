@extends('layouts.client')

@section('title', 'Client Reports')

@section('content')
<div class="container-fluid p-0">
    @include('client.partials.page-header', [
        'eyebrow' => 'Project Documentation',
        'title' => 'Reports',
        'description' => 'Review accomplishment reports, approval status, and the latest project updates shared by your team.',
    ])

    <section class="report-filter-card p-3 mb-4">
        <form method="GET" id="filterForm" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-muted">Project</label>
                <select name="project_id" id="projectSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" {{ request('project_id') == '' ? 'selected' : '' }}>All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->project_id }}" {{ request('project_id') == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-muted">Construction Phase</label>
                <select name="phase_id" id="phaseSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Phases</option>
                    @foreach($projectPhases as $phase)
                        <option value="{{ $phase->phase_id }}" {{ request('phase_id') == $phase->phase_id ? 'selected' : '' }}>{{ $phase->phase_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold text-muted">Status</label>
                <select name="status" id="statusSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Returned</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold text-muted">Report Date</label>
                <input type="date" name="report_date" id="reportDateInput" value="{{ request('report_date') }}" class="form-control form-control-sm" onchange="this.form.submit()" />
            </div>
        </form>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="report-summary-widget">
                <div class="widget-icon bg-success-subtle text-success">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <span class="widget-label">Total Reports</span>
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="report-summary-widget">
                <div class="widget-icon bg-warning-subtle text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <span class="widget-label">Pending Review</span>
                    <h3>{{ $stats['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="report-summary-widget">
                <div class="widget-icon bg-primary-subtle text-primary">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <span class="widget-label">Approved</span>
                    <h3>{{ $stats['approved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="report-summary-widget">
                <div class="widget-icon bg-danger-subtle text-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <span class="widget-label">Returned</span>
                    <h3>{{ $stats['rejected'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <section class="report-main-panel mb-4">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--brand-green);">Accomplishment Reports</h5>
                <div class="text-muted small">Showing the latest updates for your projects</div>
            </div>
            <div class="badge rounded-pill bg-success-subtle text-success px-3 py-2">{{ $reports->total() }} records</div>
        </div>

        <div class="table-responsive">
            <table class="table report-custom-table align-middle mb-0">
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
                    @forelse($reports as $report)
                        @php
                            $status = $report->approval_status ?? 'pending';
                            $pillClass = match($status) {
                                'approved' => 'bg-success-subtle text-success',
                                'rejected' => 'bg-danger-subtle text-danger',
                                default => 'bg-warning-subtle text-warning'
                            };
                        @endphp
                        @php
                            $siteImages = is_array($report->site_images ?? null) ? $report->site_images : [];
                            $detailPayload = [
                                'id' => $report->report_id,
                                'report_id' => 'RPT-2026-' . str_pad($report->report_id, 4, '0', STR_PAD_LEFT),
                                'title' => $report->report_title ?? 'Report Details',
                                'status' => $status,
                                'status_label' => ucfirst($status),
                                'project' => optional($report->project)->project_name ?? 'N/A',
                                'phase' => optional($report->phase)->phase_name ?? 'N/A',
                                'submitted_by' => optional($report->submittedBy)->name ?? 'Supervisor',
                                'reviewed_by' => optional($report->reviewedBy)->name ?? '-',
                                'submitted_at' => optional($report->report_date)->format('M d, Y h:i A') ?? 'N/A',
                                'created_at' => optional($report->created_at)->format('M d, Y'),
                                'review_date' => optional($report->reviewed_at)->format('M d, Y') ?? 'Reviewed',
                                'approval_date' => optional($report->approved_at)->format('M d, Y') ?? optional($report->rejected_at)->format('M d, Y') ?? 'Pending',
                                'report_text' => $report->report_text ?? 'No description was provided for this report.',
                                'site_images' => array_map(fn ($image) => asset('storage/' . $image), $siteImages),
                                'submitted_initial' => strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)),
                                'status_class' => $pillClass,
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->report_date)->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ optional($report->report_date)->format('h:i A') ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ optional($report->project)->project_name ?? 'Unknown Project' }}</div>
                                <div class="text-muted small">{{ optional($report->project)->location ?? 'Project update' }}</div>
                            </td>
                            <td>
                                <span class="text-dark fw-semibold">{{ optional($report->phase)->phase_name ?? 'Unassigned Phase' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-pill">{{ strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.85rem;">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                        <div class="text-muted" style="font-size:0.75rem;">Supervisor</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-pill {{ $pillClass }}">{{ $status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <button class="btn btn-sm btn-light border report-action-btn js-report-view-btn" type="button" data-report-details='@json($detailPayload)' title="View details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" data-report-id="{{ $report->report_id }}" class="btn btn-sm btn-light border report-action-btn report-export-link" title="Export PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No reports match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

<div class="p-3 bg-light d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 border-top">
            <div class="small text-muted">Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports</div>
            <div>{{ $reports->appends(request()->only(['project_id', 'phase_id', 'status', 'report_date']))->links('pagination::bootstrap-5') }}</div>
        </div>
    </section>
</div>

<style>
    .report-filter-card, .report-summary-widget, .report-main-panel {
        border-radius: 18px;
        border: 1px solid rgba(42, 64, 40, 0.08);
        background: #fff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
    }

    .report-summary-widget {
        padding: 1.2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .widget-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    .widget-label {
        font-size: 0.78rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .pagination {
        display: flex !important;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 1rem;
        padding-left: 0;
        list-style: none;
    }
    .pagination .page-item {
        display: inline-block;
    }
    .pagination .page-item .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--brand-green);
        border-color: var(--brand-green);
        min-width: 44px;
        min-height: 44px;
        border-radius: 0.85rem;
        font-weight: 600;
        transition: background-color 0.2s ease, color 0.2s ease;
        padding: 0.75rem 0.95rem;
        background-color: #fff;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--brand-green);
        border-color: var(--brand-green);
        color: #ffffff;
    }
    .pagination .page-item .page-link:hover {
        background-color: rgba(42, 64, 40, 0.1);
        color: var(--brand-green);
    }
    .pagination .page-item.disabled .page-link {
        color: #94a3b8;
        background-color: transparent;
        border-color: #d1d5db;
        cursor: not-allowed;
    }

    .report-summary-widget h3 {
        margin: 0.15rem 0 0;
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-primary);
    }

    .report-custom-table th {
        background-color: var(--brand-mint) !important;
        color: var(--brand-green);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 16px;
    }

    .report-custom-table td {
        padding: 16px;
        vertical-align: middle;
        font-size: 0.88rem;
    }

    .avatar-pill {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--brand-green);
        color: #fff;
        font-size: 0.8rem;
        font-weight: 700;
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

    .report-action-btn {
        color: var(--brand-green);
        border-color: rgba(42, 64, 40, 0.12);
    }

    .report-action-btn:hover {
        color: var(--brand-green);
        background-color: rgba(42, 64, 40, 0.06);
        border-color: rgba(42, 64, 40, 0.18);
    }

    .report-action-btn i,
    .report-export-btn i {
        color: var(--brand-green) !important;
    }

    .report-export-btn i {
        color: #fff !important;
    }

    .report-export-btn {
        background-color: var(--brand-green);
        border: none;
        color: #fff;
    }

    .report-export-btn:hover {
        background-color: var(--brand-green-dark, #1a754b);
        color: #fff;
    }

    .report-detail-modal .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }

    .report-detail-modal .modal-header,
    .report-detail-modal .modal-footer {
        background: #fff;
    }

    .drawer-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--brand-green);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid rgba(42, 64, 40, 0.08);
        padding-bottom: 0.5rem;
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
    }

    .timeline-row {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-top: 1rem;
    }

    .timeline-row::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 15%;
        right: 15%;
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
        border-color: var(--brand-green);
        background: var(--brand-green);
        color: #fff;
    }

    .timeline-step.current .timeline-icon {
        border-color: #f59e0b;
        background: #f59e0b;
        color: #fff;
    }

    .report-detail-panel-card {
        border: 1px solid rgba(42, 64, 40, 0.1);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        opacity: 0;
        max-height: 0;
        transform: translateY(10px);
        pointer-events: none;
        transition: all 220ms ease;
    }

    .report-detail-panel-card.is-open {
        opacity: 1;
        max-height: 3000px;
        transform: translateY(0);
        pointer-events: auto;
    }

    .report-detail-panel-card.is-switching .report-detail-panel-body {
        opacity: 0;
        transform: translateY(8px);
    }

    .report-detail-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid rgba(42, 64, 40, 0.08);
        background: linear-gradient(135deg, #f8fcf8 0%, #ffffff 100%);
    }

    .report-detail-panel-eyebrow {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--brand-green);
    }

    .report-detail-panel-body {
        padding: 1.1rem 1.15rem 1.2rem;
        transition: opacity 180ms ease, transform 180ms ease;
    }

    .report-detail-empty-state {
        display: grid;
        justify-items: center;
        text-align: center;
        gap: 0.5rem;
        padding: 1rem 0 0.2rem;
        color: var(--text-muted);
    }

    .avatar-pill-large {
        width: 46px;
        height: 46px;
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .report-drawer {
            width: 100% !important;
        }
    }


    /* --- MOBILE: clean accomplishment report cards --- */
    .report-mobile-list {
        display: none;
    }

    @media (max-width: 767.98px) {
        .report-filter-card {
            margin-bottom: 0.9rem !important;
        }

        .report-main-panel {
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid rgba(42, 64, 40, 0.08);
            background: #ffffff;
        }

        .report-main-panel > .table-responsive {
            display: none !important;
        }

        .report-main-panel > .p-3.border-bottom {
            padding: 1rem !important;
            align-items: flex-start !important;
        }

        .report-main-panel h5 {
            font-size: 1.05rem;
            line-height: 1.2;
        }

        .report-mobile-list {
            display: grid !important;
            gap: 12px;
            padding: 12px;
            background: linear-gradient(180deg, #fbfdfb 0%, #ffffff 100%);
        }

        .report-mobile-card {
            border: 1px solid #e2ebe4;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.045);
            padding: 14px;
            overflow: hidden;
        }

        .report-mobile-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eef3ef;
        }

        .report-mobile-date-block {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .report-mobile-label,
        .report-mobile-info-item span {
            display: block;
            color: #60708a;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: 0.08em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .report-mobile-date-block strong {
            margin-top: 4px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.25;
        }

        .report-mobile-date-block small,
        .report-mobile-info-item small {
            margin-top: 2px;
            color: #718096;
            font-size: 11.5px;
            line-height: 1.25;
        }

        .report-mobile-card-header .status-pill {
            flex: 0 0 auto;
            max-width: 124px;
            white-space: normal;
            text-align: center;
            line-height: 1.15;
        }

        .report-mobile-title {
            padding: 13px 0 11px;
            color: #111827;
            font-size: 15px;
            font-weight: 800;
            line-height: 1.35;
            word-break: normal;
            overflow-wrap: anywhere;
        }

        .report-mobile-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .report-mobile-info-item {
            min-width: 0;
            padding: 10px 11px;
            border: 1px solid #edf3ee;
            border-radius: 14px;
            background: #fbfdfb;
        }

        .report-mobile-info-wide {
            grid-column: 1 / -1;
        }

        .report-mobile-info-item strong {
            display: block;
            margin-top: 5px;
            color: #10271b;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.35;
            word-break: normal;
            overflow-wrap: anywhere;
        }

        .report-mobile-actions {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 9px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #eef3ef;
        }

        .report-mobile-view-btn,
        .report-mobile-download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 42px;
            border-radius: 13px;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .report-mobile-view-btn {
            border: 1px solid #2a4028;
            background: #2a4028;
            color: #ffffff;
        }

        .report-mobile-view-btn:hover,
        .report-mobile-view-btn:focus {
            background: #223721;
            color: #ffffff;
        }

        .report-mobile-download-btn {
            min-width: 78px;
            border: 1px solid #dce7df;
            background: #f7fbf8;
            color: #2a4028;
        }

        .report-mobile-download-btn:hover,
        .report-mobile-download-btn:focus {
            background: #edf7ef;
            color: #2a4028;
        }

        .report-mobile-empty {
            display: grid;
            place-items: center;
            gap: 4px;
            min-height: 160px;
            padding: 24px;
            border: 1px dashed #dbe8df;
            border-radius: 18px;
            color: #64748b;
            text-align: center;
        }

        .report-mobile-empty i {
            font-size: 1.8rem;
            color: #8fae85;
        }

        .report-mobile-empty strong {
            color: #10271b;
            font-size: 0.95rem;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            const controls = filterForm.querySelectorAll('select, input[type="date"]');
            let debounceTimer;

            const submitFilters = () => filterForm.submit();

            controls.forEach(control => {
                control.addEventListener('change', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(submitFilters, 220);
                });

                if (control.type === 'date') {
                    control.addEventListener('input', () => {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(submitFilters, 220);
                    });
                }
            });
        }

        const panel = document.getElementById('reportDetailPanel');
        const title = document.getElementById('reportDetailTitle');
        const body = document.getElementById('reportDetailBody');
        const closeButton = document.getElementById('reportDetailCloseBtn');

        if (panel && title && body) {
            panel.classList.remove('is-open');
            const emptyStateMarkup = `
                <div class="report-detail-empty-state">
                    <div class="avatar-pill avatar-pill-large">?</div>
                    <div class="fw-semibold text-dark">Choose a report to inspect its full details.</div>
                    <div class="text-muted small">The selected record will open here with a smooth transition.</div>
                </div>
            `;

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const renderDetails = (payload) => {
                const status = payload.status || 'pending';
                const statusClass = payload.status_class || (status === 'approved' ? 'bg-success-subtle text-success' : status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
                const siteImages = Array.isArray(payload.site_images) ? payload.site_images : [];
                const imagesMarkup = siteImages.length > 0 ? `
                    <div class="drawer-section-title">Site Images</div>
                    <div class="d-flex gap-2 flex-wrap">
                        ${siteImages.slice(0, 3).map((image) => `<img src="${escapeHtml(image)}" class="img-thumbnail" alt="Site image" style="width: 110px; height: 78px; object-fit: cover; border-radius: 10px;">`).join('')}
                    </div>
                ` : '';

                body.innerHTML = `
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold">Report ID</div>
                            <div class="fw-bold">${escapeHtml(payload.report_id || 'N/A')}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold">Approval Status</div>
                            <span class="status-pill ${escapeHtml(statusClass)}">${escapeHtml(status)}</span>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold">Project</div>
                            <div class="fw-bold">${escapeHtml(payload.project || 'N/A')}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold">Construction Phase</div>
                            <div class="fw-bold">${escapeHtml(payload.phase || 'N/A')}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold">Submitted By</div>
                            <div class="fw-bold">${escapeHtml(payload.submitted_by || 'Supervisor')}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-bold">Reviewed By</div>
                            <div class="fw-bold">${escapeHtml(payload.reviewed_by || '-')}</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 my-3 p-3 bg-light rounded-3">
                        <div class="avatar-pill">${escapeHtml(payload.submitted_initial || 'S')}</div>
                        <div>
                            <div class="fw-bold text-dark mb-0" style="font-size:0.9rem;">${escapeHtml(payload.submitted_by || 'Supervisor')}</div>
                            <div class="text-muted" style="font-size:0.75rem;">Submitted by site supervisor</div>
                        </div>
                    </div>

                    <div class="drawer-section-title mt-0">Construction Accomplishment</div>
                    <div class="p-3 bg-light rounded-3 text-muted small" style="white-space: pre-line; line-height: 1.6;">${escapeHtml(payload.report_text || 'No description was provided for this report.')}</div>

                    ${imagesMarkup}

                    <div class="drawer-section-title">Approval Timeline</div>
                    <div class="timeline-row">
                        <div class="timeline-step active">
                            <div class="timeline-icon"><i class="bi bi-check"></i></div>
                            <div class="fw-bold small">Submitted</div>
                            <div class="text-muted small">${escapeHtml(payload.created_at || 'N/A')}</div>
                        </div>
                        <div class="timeline-step ${status !== 'pending' ? 'active' : 'current'}">
                            <div class="timeline-icon">${status === 'pending' ? '<i class="bi bi-clock"></i>' : '<i class="bi bi-check"></i>'}</div>
                            <div class="fw-bold small">Review</div>
                            <div class="text-muted small">${status === 'pending' ? 'Awaiting review' : escapeHtml(payload.review_date || 'Reviewed')}</div>
                        </div>
                        <div class="timeline-step ${status === 'approved' ? 'active' : (status === 'rejected' ? 'active' : '')}">
                            <div class="timeline-icon"><i class="bi bi-circle"></i></div>
                            <div class="fw-bold small">${status === 'approved' ? 'Approved' : (status === 'rejected' ? 'Returned' : 'Finalized')}</div>
                            <div class="text-muted small">${escapeHtml(payload.approval_date || 'Pending')}</div>
                        </div>
                    </div>
                `;
            };

            const openDetails = (payload) => {
                if (!payload) {
                    return;
                }

                title.textContent = payload.title || 'Report Details';
                panel.classList.remove('is-open');
                panel.classList.add('is-switching');
                setTimeout(() => {
                    renderDetails(payload);
                    panel.classList.add('is-open');
                    panel.classList.remove('is-switching');
                }, 140);
            };

            document.querySelectorAll('.js-report-view-btn').forEach((button) => {
                button.addEventListener('click', function () {
                    const payload = this.dataset.reportDetails ? JSON.parse(this.dataset.reportDetails) : null;
                    if (payload) {
                        openDetails(payload);
                    }
                });
            });

            closeButton?.addEventListener('click', () => {
                panel.classList.remove('is-open');
                panel.classList.remove('is-switching');
                title.textContent = 'Select a report';
                body.innerHTML = emptyStateMarkup;
            });
        }

        document.querySelectorAll('.report-export-link, .report-export-btn').forEach(link => {
            link.addEventListener('click', function (event) {
                event.preventDefault();

                const url = this.href;
                const reportId = this.dataset.reportId || 'report';

                if (window.Swal) {
                    Swal.fire({
                        title: 'Exporting Report',
                        html: 'Preparing your accomplishment report...',
                        didOpen: () => Swal.showLoading(),
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });
                }

                fetch(url, {
                    headers: { Accept: 'text/html,application/pdf,*/*' }
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(text || 'Failed to export report');
                            });
                        }

                        const contentDisposition = response.headers.get('content-disposition') || '';
                        const fileNameMatch = contentDisposition.match(/filename="?([^";]+)"?/);
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

                        if (window.Swal) {
                            Swal.fire({
                                title: 'Export Complete',
                                text: 'Your report is downloading now.',
                                icon: 'success',
                                confirmButtonColor: '#1a754b'
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Export Failed',
                                text: error.message || 'Unable to export the report at this time.',
                                icon: 'error',
                                confirmButtonColor: '#1a754b'
                            });
                        }
                    });
            });
        });
    });
</script>
@endpush