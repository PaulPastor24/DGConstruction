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
                                    <button class="btn btn-sm btn-light border report-action-btn" type="button" data-bs-toggle="modal" data-bs-target="#reportModal-{{ $report->report_id }}" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" data-report-id="{{ $report->report_id }}" class="btn btn-sm btn-light border report-action-btn report-export-link" title="Export PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade report-detail-modal" id="reportModal-{{ $report->report_id }}" tabindex="-1" aria-labelledby="reportModalLabel-{{ $report->report_id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 760px;">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                    <div class="modal-header border-0 px-4 pt-4 pb-2" style="background: #fff;">
                                        <div>
                                            <h5 class="modal-title fw-bold" id="reportModalLabel-{{ $report->report_id }}" style="color: var(--brand-green); font-size: 1.2rem; letter-spacing: -0.02em;">
                                                Report Details
                                            </h5>
                                            <p class="text-muted small mb-0">{{ optional($report->report_date)->format('M d, Y h:i A') ?? 'N/A' }}</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" data-report-id="{{ $report->report_id }}" class="btn btn-sm btn-success report-export-btn" title="Export PDF">
                                                <i class="bi bi-download"></i> Export PDF
                                            </a>
                                        </div>
                                    </div>
                                    <div class="modal-body px-4 py-3">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <div class="text-muted small fw-bold">Report ID</div>
                                                <div class="fw-bold">RPT-2026-{{ str_pad($report->report_id, 4, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted small fw-bold">Approval Status</div>
                                                <span class="status-pill {{ $pillClass }}">{{ $status }}</span>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted small fw-bold">Project</div>
                                                <div class="fw-bold">{{ optional($report->project)->project_name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted small fw-bold">Construction Phase</div>
                                                <div class="fw-bold">{{ optional($report->phase)->phase_name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted small fw-bold">Submitted By</div>
                                                <div class="fw-bold">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-muted small fw-bold">Reviewed By</div>
                                                <div class="fw-bold">{{ optional($report->reviewedBy)->name ?? '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2 my-3 p-3 bg-light rounded-3">
                                            <div class="avatar-pill">{{ strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)) }}</div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0" style="font-size:0.9rem;">{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                                <div class="text-muted" style="font-size:0.75rem;">Submitted by site supervisor</div>
                                            </div>
                                        </div>

                                        <div class="drawer-section-title mt-0">Construction Accomplishment</div>
                                        <div class="p-3 bg-light rounded-3 text-muted small" style="white-space: pre-line; line-height: 1.6;">
                                            {{ $report->report_text ?? 'No description was provided for this report.' }}
                                        </div>

                                        @php($siteImages = is_array($report->site_images ?? null) ? $report->site_images : [])
                                        @if(count($siteImages) > 0)
                                            <div class="drawer-section-title">Site Images</div>
                                            <div class="d-flex gap-2 flex-wrap">
                                                @foreach(array_slice($siteImages, 0, 3) as $image)
                                                    <img src="{{ asset('storage/' . $image) }}" class="img-thumbnail" alt="Site image" style="width: 110px; height: 78px; object-fit: cover; border-radius: 10px;">
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="drawer-section-title">Approval Timeline</div>
                                        <div class="timeline-row">
                                            <div class="timeline-step active">
                                                <div class="timeline-icon"><i class="bi bi-check"></i></div>
                                                <div class="fw-bold small">Submitted</div>
                                                <div class="text-muted small">{{ optional($report->created_at)->format('M d, Y') }}</div>
                                            </div>
                                            <div class="timeline-step {{ $status !== 'pending' ? 'active' : 'current' }}">
                                                <div class="timeline-icon">
                                                    @if($status === 'pending')<i class="bi bi-clock"></i>@else<i class="bi bi-check"></i>@endif
                                                </div>
                                                <div class="fw-bold small">Review</div>
                                                <div class="text-muted small">{{ $status === 'pending' ? 'Awaiting review' : (optional($report->reviewed_at)->format('M d, Y') ?? 'Reviewed') }}</div>
                                            </div>
                                            <div class="timeline-step {{ $status === 'approved' ? 'active' : ($status === 'rejected' ? 'active' : '') }}">
                                                <div class="timeline-icon"><i class="bi bi-circle"></i></div>
                                                <div class="fw-bold small">{{ $status === 'approved' ? 'Approved' : ($status === 'rejected' ? 'Returned' : 'Finalized') }}</div>
                                                <div class="text-muted small">{{ optional($report->approved_at)->format('M d, Y') ?? optional($report->rejected_at)->format('M d, Y') ?? 'Pending' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 px-4 pb-4 pt-2" style="background: #fff;">

                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    @media (max-width: 768px) {
        .report-drawer {
            width: 100% !important;
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