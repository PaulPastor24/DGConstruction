@extends('layouts.client')

@section('title', 'Client Reports')
@section('mobileTitle', 'Reports')

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
                    <option value="" {{ $activeProjectId === null ? 'selected' : '' }}>All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->project_id }}" {{ $activeProjectId == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
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
        <div class="col-6 col-sm-6 col-xl-3">
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
        <div class="col-6 col-sm-6 col-xl-3">
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
        <div class="col-6 col-sm-6 col-xl-3">
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
        <div class="col-6 col-sm-6 col-xl-3">
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
        <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-0" style="color: var(--brand-green);">Accomplishment Reports</h5>
                <div class="text-muted small">
                    @if($activeProjectId !== null)
                        Showing reports for <span class="fw-semibold text-dark">{{ optional($selectedProject)->project_name }}</span>
                    @else
                        Showing the latest updates across all your projects
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="badge rounded-pill bg-success-subtle text-success px-3 py-2">{{ $reports->total() }} records</div>
            </div>
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
                                'approval_remarks' => $report->approval_remarks ?? 'No remarks',
                                'site_images' => array_map(fn ($image) => asset('storage/' . $image), $siteImages),
                                'submitted_initial' => strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)),
                                'status_class' => $pillClass,
                                'download_url' => route('client.reports.downloadPdf', $report->report_id),
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
            @php
                $paginationParams = request()->only(['phase_id', 'status', 'report_date']);
                if ($activeProjectId !== null) {
                    $paginationParams['project_id'] = $activeProjectId;
                }
            @endphp
            <div>{{ $reports->appends($paginationParams)->links('pagination::bootstrap-5') }}</div>
        </div>
    </section>
</div>

{{-- Report Details Modal (replaces the old inline panel) --}}
<div class="modal fade report-detail-modal" id="reportDetailsModal" tabindex="-1" aria-labelledby="reportDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header report-modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="reportDetailsModalLabel">Report Details</h5>
                    <div class="text-muted small">A complete summary of the selected accomplishment report.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4" id="reportDetailsModalBody"></div>
            <div class="modal-footer report-modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="reportModalDownloadBtn" class="btn report-modal-download-btn" data-report-id="">
                    <i class="bi bi-download me-1"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
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

    /* ---- Report Details Modal (theme-matched, inspired by the supervisor view) ---- */
    .report-detail-modal .modal-dialog {
        max-width: 1080px;
    }

    .report-detail-modal .modal-content {
        border-radius: 20px;
        overflow: hidden;
        border: none;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    }

    .report-modal-header {
        background: #fff;
        border-bottom: 2px solid var(--brand-green);
        padding: 1.1rem 1.4rem;
    }

    .report-modal-header .modal-title {
        color: var(--brand-green);
        font-family: var(--font-brand);
    }

    .report-modal-footer {
        background: #fff;
        border-top: 1px solid rgba(42, 64, 40, 0.08);
        padding: 1rem 1.4rem;
    }

    .report-modal-download-btn {
        background-color: var(--brand-green);
        border: none;
        color: #fff;
        border-radius: 12px;
        padding: 0.6rem 1.1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: background-color 0.2s ease, transform 0.15s ease;
    }

    .report-modal-download-btn:hover {
        background-color: #1a754b;
        color: #fff;
        transform: translateY(-1px);
    }

    .report-detail-card,
    .report-detail-sidebar {
        border-radius: 16px;
        border: 1px solid rgba(42, 64, 40, 0.12);
        background: #fff;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.05);
    }

    .report-detail-card {
        padding: 1.75rem;
    }

    .report-detail-sidebar {
        background: #f8faf8;
        border-color: rgba(42, 64, 40, 0.1);
        padding: 1.25rem;
    }

    .report-modal-meta-head {
        background: #f8faf8;
        border: 1px solid rgba(42, 64, 40, 0.08);
    }

    .report-modal-meta {
        background: #f8faf8;
        border: 1px solid rgba(42, 64, 40, 0.06);
    }

    .report-modal-text {
        background: #f8faf8;
        border: 1px solid rgba(42, 64, 40, 0.06);
    }

    .drawer-section-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--brand-green);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid rgba(42, 64, 40, 0.12);
        padding-bottom: 0.5rem;
        margin-top: 1.4rem;
        margin-bottom: 0.75rem;
    }

    .report-modal-thumb {
        width: 96px;
        height: 74px;
        min-width: 96px;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        background: #f9fafb;
        overflow: hidden;
        padding: 0;
        text-decoration: none;
    }

    .report-modal-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .report-modal-thumb-more {
        width: 96px;
        height: 74px;
        min-width: 96px;
        border-radius: 12px;
        background: #eef2ee;
        border: 2px solid #e5e7eb;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .report-modal-empty-img {
        font-size: 0.85rem;
        color: var(--text-muted);
        background: #f8faf8;
        border: 1px dashed rgba(42, 64, 40, 0.18);
        border-radius: 12px;
        padding: 1rem;
    }

    .timeline-container {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-top: 1rem;
        padding: 0 0.5rem;
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        top: 18px;
        left: 18%;
        right: 18%;
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

    .timeline-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #d9e5dd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 0.9rem;
        color: #94a3b8;
    }

    .timeline-step.active .timeline-icon {
        border-color: var(--brand-green);
        background: var(--brand-green);
        color: #fff;
    }

    .timeline-step.current .timeline-icon {
        border-color: #f59e0b;
        background: #fff;
        color: #f59e0b;
    }

    @media (max-width: 768px) {
        .report-detail-modal .modal-dialog {
            margin: 0.5rem;
        }
    }

    /* --- MOBILE: 2x2 summary stat cards + readable report table --- */
    @media (max-width: 575px) {
        .report-summary-widget {
            padding: 0.85rem;
            gap: 0.65rem;
        }
        .report-summary-widget .widget-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
            border-radius: 12px;
        }
        .report-summary-widget .widget-label {
            font-size: 0.62rem;
        }
        .report-summary-widget h3 {
            font-size: 1.1rem;
        }
        .report-custom-table {
            font-size: 0.8rem;
        }
        .report-custom-table th {
            font-size: 0.64rem;
            padding: 10px 12px;
            white-space: nowrap;
        }
        .report-custom-table td {
            padding: 12px;
            white-space: nowrap;
        }
        .report-custom-table td .avatar-pill {
            width: 28px;
            height: 28px;
            font-size: 0.72rem;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        function buildReportModalBody(payload) {
            const status = payload.status || 'pending';
            const statusClass = payload.status_class || (status === 'approved' ? 'bg-success-subtle text-success' : status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
            const siteImages = Array.isArray(payload.site_images) ? payload.site_images : [];

            const imagesMarkup = siteImages.length > 0
                ? `<div class="d-flex flex-wrap gap-2 mb-1">
                        ${siteImages.slice(0, 4).map((image) => `<a href="${escapeHtml(image)}" target="_blank" rel="noopener" class="report-modal-thumb"><img src="${escapeHtml(image)}" alt="Site image"></a>`).join('')}
                        ${siteImages.length > 4 ? `<div class="report-modal-thumb-more">+${siteImages.length - 4}</div>` : ''}
                   </div>`
                : `<div class="report-modal-empty-img">No site images were attached to this report.</div>`;

            const timelineStep = (cls, icon, label, sub) => `
                <div class="timeline-step ${cls}">
                    <div class="timeline-icon">${icon}</div>
                    <div class="fw-bold" style="font-size:0.75rem;">${escapeHtml(label)}</div>
                    <div class="text-muted small">${escapeHtml(sub)}</div>
                </div>`;

            return `
                <div class="row gx-4 gy-4">
                    <div class="col-12 col-xl-7">
                        <div class="report-detail-card">
                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-4 p-3 rounded-3 report-modal-meta-head">
                                <div>
                                    <div class="small text-uppercase text-muted" style="font-weight:600;">Report ID</div>
                                    <div class="fw-bold text-dark" style="font-size:1.1rem;">${escapeHtml(payload.report_id || 'N/A')}</div>
                                </div>
                                <div class="text-sm-end">
                                    <div class="small text-uppercase text-muted" style="font-weight:600;">Approval Status</div>
                                    <span class="status-pill ${escapeHtml(statusClass)} p-2 mt-1 d-inline-block">${escapeHtml(status)}</span>
                                </div>
                            </div>

                            <div class="row g-3 mb-4 small">
                                <div class="col-6 p-3 rounded report-modal-meta"><div class="fw-semibold text-muted mb-1">Project</div><div class="text-dark">${escapeHtml(payload.project || 'N/A')}</div></div>
                                <div class="col-6 p-3 rounded report-modal-meta"><div class="fw-semibold text-muted mb-1">Construction Phase</div><div class="text-dark">${escapeHtml(payload.phase || 'N/A')}</div></div>
                                <div class="col-6 p-3 rounded report-modal-meta"><div class="fw-semibold text-muted mb-1">Report Date</div><div class="text-dark">${escapeHtml(payload.submitted_at || 'N/A')}</div></div>
                                <div class="col-6 p-3 rounded report-modal-meta"><div class="fw-semibold text-muted mb-1">Submitted By</div><div class="text-dark">${escapeHtml(payload.submitted_by || 'Supervisor')}</div></div>
                            </div>

                            <div class="p-4 rounded-3 mb-4 report-modal-text">
                                <div class="fw-bold mb-2" style="color: var(--brand-green);">Construction Accomplishment</div>
                                <p class="mb-0 text-dark small" style="white-space: pre-line; line-height:1.7;">${escapeHtml(payload.report_text || 'No description was provided for this report.')}</p>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6"><div class="p-3 rounded-3 report-modal-meta"><div class="fw-semibold text-muted mb-1">Reviewed By</div><div class="text-dark">${escapeHtml(payload.reviewed_by || 'Pending review')}</div></div></div>
                                <div class="col-12 col-md-6"><div class="p-3 rounded-3 report-modal-meta"><div class="fw-semibold text-muted mb-1">Approved By</div><div class="text-dark">${escapeHtml(payload.approved_by || 'Pending approval')}</div></div></div>
                            </div>

                            <div class="p-3 rounded-3 report-modal-meta">
                                <div class="fw-semibold text-muted mb-1">Approval Remarks</div>
                                <div class="text-dark small">${escapeHtml(payload.approval_remarks || 'No remarks')}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-5">
                        <div class="report-detail-sidebar">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-bold" style="color: var(--brand-green);">Site Images</div>
                                <div class="small text-muted">${siteImages.length} uploaded</div>
                            </div>
                            ${imagesMarkup}
                        </div>

                        <div class="report-detail-sidebar mt-3">
                            <div class="fw-bold mb-3" style="color: var(--brand-green);">Approval Timeline</div>
                            <div class="timeline-container small px-1">
                                ${timelineStep('active', '<i class="bi bi-check"></i>', 'Submitted', payload.created_at || 'N/A')}
                                ${timelineStep(status !== 'pending' ? 'active' : 'current', status === 'pending' ? '<i class="bi bi-clock"></i>' : '<i class="bi bi-check"></i>', 'Under Review', status === 'pending' ? 'Awaiting review' : (payload.review_date || 'Reviewed'))}
                                ${timelineStep(status === 'approved' ? 'active' : (status === 'rejected' ? 'active' : ''), '<i class="bi bi-circle"></i>', status === 'approved' ? 'Approved' : (status === 'rejected' ? 'Returned' : 'Finalized'), payload.approval_date || 'Pending')}
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        function showReportModal(payload) {
            if (!payload) return;
            const modalEl = document.getElementById('reportDetailsModal');
            if (!modalEl) return;

            const titleEl = document.getElementById('reportDetailsModalLabel');
            const bodyEl = document.getElementById('reportDetailsModalBody');
            const dlBtn = document.getElementById('reportModalDownloadBtn');

            if (titleEl) titleEl.textContent = payload.title || 'Report Details';
            if (bodyEl) bodyEl.innerHTML = buildReportModalBody(payload);
            if (dlBtn && payload.download_url) {
                dlBtn.href = payload.download_url;
                dlBtn.dataset.reportId = payload.id;
            }

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function triggerReportDownload(url, reportId) {
            if (!url) return;
            reportId = reportId || 'report';

            if (window.Swal) {
                Swal.fire({ title: 'Exporting Report', html: 'Preparing your accomplishment report...', didOpen: () => Swal.showLoading(), allowOutsideClick: false, allowEscapeKey: false });
            }

            fetch(url, { headers: { Accept: 'text/html,application/pdf,*/*' } })
                .then(response => {
                    if (!response.ok) return response.text().then(text => { throw new Error(text || 'Failed to export report'); });
                    const cd = response.headers.get('content-disposition') || '';
                    const m = cd.match(/filename="?([^";]+)"?/);
                    const fileName = m ? m[1] : `report_${reportId}.pdf`;
                    return response.blob().then(blob => ({ blob, fileName }));
                })
                .then(({ blob, fileName }) => {
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl; a.download = fileName;
                    document.body.appendChild(a); a.click(); a.remove();
                    URL.revokeObjectURL(blobUrl);
                    if (window.Swal) Swal.fire({ title: 'Export Complete', text: 'Your report is downloading now.', icon: 'success', confirmButtonColor: '#1a754b' });
                })
                .catch(error => {
                    console.error(error);
                    if (window.Swal) Swal.fire({ title: 'Export Failed', text: error.message || 'Unable to export the report at this time.', icon: 'error', confirmButtonColor: '#1a754b' });
                });
        }

        // Event delegation so the eye / download handlers survive the layout's 15s
        // silent reload (which swaps #silentReloadContent via innerHTML, dropping
        // any per-element listeners that were attached on the original load).
        document.addEventListener('click', function (event) {
            const viewBtn = event.target.closest('.js-report-view-btn');
            if (viewBtn) {
                let payload = null;
                try { payload = viewBtn.dataset.reportDetails ? JSON.parse(viewBtn.dataset.reportDetails) : null; } catch (e) { payload = null; }
                showReportModal(payload);
                return;
            }

            const rowDl = event.target.closest('.report-export-link');
            if (rowDl) {
                event.preventDefault();
                triggerReportDownload(rowDl.href, rowDl.dataset.reportId || 'report');
                return;
            }

            const modalDl = event.target.closest('#reportModalDownloadBtn');
            if (modalDl) {
                event.preventDefault();
                triggerReportDownload(modalDl.href, modalDl.dataset.reportId || 'report');
            }
        });
    });
</script>
@endpush