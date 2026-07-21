@extends('layouts.client')

@section('title', 'Client Reports')
@section('mobileTitle', 'Reports')

@section('content')
<div class="container-fluid p-0 client-reports-page">
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
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published to Client</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold text-muted">Report Date</label>
                <input type="date" name="report_date" id="reportDateInput" value="{{ request('report_date') }}" class="form-control form-control-sm" onchange="this.form.submit()" />
            </div>
        </form>
    </section>

    <div class="row g-2 g-md-3 mb-3 mb-md-4 report-summary-row">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="report-summary-widget">
                <div class="widget-icon bg-success-subtle text-success">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <span class="widget-label">Total Published Reports</span>
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="report-summary-widget">
                <div class="widget-icon bg-info-subtle text-info">
                    <i class="bi bi-eye"></i>
                </div>
                <div>
                    <span class="widget-label">Published to Client</span>
                    <h3>{{ $stats['published'] ?? $stats['total'] ?? 0 }}</h3>
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
                            $status = 'published';
                            $pillClass = 'bg-info-subtle text-info';
                            $displayStatus = 'published';
                            $displayPillClass = 'bg-info-subtle text-info';
                        @endphp
                        @php
                            $siteImages = is_array($report->site_images ?? null) ? $report->site_images : [];
                            $adminImages = is_array($report->admin_site_images ?? null) ? $report->admin_site_images : [];
                            $displayImages = !empty($adminImages) ? $adminImages : $siteImages;
                            $detailPayload = [
                                'id' => $report->report_id,
                                'report_id' => 'RPT-2026-' . str_pad($report->report_id, 4, '0', STR_PAD_LEFT),
                                'title' => $report->report_title ?? 'Report Details',
                                'status' => $displayStatus,
                                'status_label' => ucfirst($displayStatus),
                                'project' => optional($report->project)->project_name ?? 'N/A',
                                'phase' => optional($report->phase)->phase_name ?? 'N/A',
                                'submitted_by' => optional($report->submittedBy)->name ?? 'Supervisor',
                                'reviewed_by' => optional($report->reviewedBy)->name ?? '-',
                                'submitted_at' => optional($report->report_date)->format('M d, Y h:i A') ?? 'N/A',
                                'created_at' => optional($report->created_at)->format('M d, Y'),
                                'review_date' => optional($report->reviewed_at)->format('M d, Y') ?? 'Reviewed',
                                'approval_date' => optional($report->approved_at)->format('M d, Y') ?? optional($report->rejected_at)->format('M d, Y') ?? 'Pending',
                                'report_text' => $report->admin_report_text ?? $report->report_text ?? 'No description was provided for this report.',
                                'site_images' => array_map(fn ($image) => asset('storage/' . $image), $displayImages),
                                'admin_explanation' => $report->admin_explanation ?? '',
                                'submitted_initial' => strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)),
                                'status_class' => $displayPillClass,
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
                                    <button class="btn btn-sm btn-light border report-action-btn js-report-view-btn" type="button" data-report-details='@json($detailPayload)' data-modal-target="reportDetailsModal-{{ $report->report_id }}" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" data-report-id="{{ $report->report_id }}" class="btn btn-sm btn-light border report-action-btn report-export-link" title="Export PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade report-details-modal" id="reportDetailsModal-{{ $report->report_id }}" tabindex="-1" aria-labelledby="reportDetailsModalLabel-{{ $report->report_id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header" style="background: #ffffff; border-bottom: 2px solid var(--cms-green-dark, #2a4028);">
                                        <div>
                                            <h5 class="modal-title fw-bold" id="reportDetailsModalLabel-{{ $report->report_id }}" style="color: var(--cms-green-dark, #2a4028);">Report Details</h5>
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
                                                            <div class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $detailPayload['report_id'] }}</div>
                                                        </div>
                                                        <div class="text-sm-end">
                                                            <div class="small text-uppercase text-muted" style="font-weight: 600;">Approval Status</div>
                                                            <span class="status-pill {{ $pillClass }} p-2 mt-1 d-inline-block">{{ $status }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="row g-3 mb-4 small">
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                            <div class="fw-semibold text-muted mb-1">Project</div>
                                                            <div class="text-dark">{{ $detailPayload['project'] }}</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                            <div class="fw-semibold text-muted mb-1">Construction Phase</div>
                                                            <div class="text-dark">{{ $detailPayload['phase'] }}</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                            <div class="fw-semibold text-muted mb-1">Report Date</div>
                                                            <div class="text-dark">{{ $detailPayload['submitted_at'] }}</div>
                                                        </div>
                                                        <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                            <div class="fw-semibold text-muted mb-1">Submitted By</div>
                                                            <div class="text-dark">{{ $detailPayload['submitted_by'] }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="p-4 rounded-3 mb-4" style="white-space: pre-line; line-height: 1.7; background: #f9fafb; border-radius: 14px;">
                                                        <div class="fw-bold mb-2" style="color: var(--cms-green-dark, #2a4028);">Construction Accomplishment</div>
                                                        <p class="mb-0 text-dark small">{{ $detailPayload['report_text'] }}</p>
                                                    </div>

                                                    <div class="row g-3 mb-3">
                                                        <div class="col-12 col-md-6">
                                                            <div class="p-3 rounded-3" style="background: #f9fafb; border-radius: 14px;">
                                                                <div class="fw-semibold text-muted mb-1">Reviewed By</div>
                                                                <div class="text-dark">{{ $detailPayload['reviewed_by'] }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-6">
                                                            <div class="p-3 rounded-3" style="background: #f9fafb; border-radius: 14px;">
                                                                <div class="fw-semibold text-muted mb-1">Approved By</div>
                                                                <div class="text-dark">{{ $detailPayload['status'] === 'approved' ? $detailPayload['reviewed_by'] : 'Pending approval' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="p-3 rounded-3" style="background: #f9fafb; border-radius: 14px;">
                                                        <div class="fw-semibold text-muted mb-1">Admin Remarks</div>
                                                        <div class="text-dark small">{{ $detailPayload['admin_explanation'] ?: ($report->approval_remarks ?: 'No remarks') }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 col-xl-5">
                                                <div class="report-detail-sidebar p-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="fw-bold" style="color: var(--cms-green-dark, #2a4028);">Site Images</div>
                                                        <div class="small text-muted">{{ count($detailPayload['site_images']) }} uploaded</div>
                                                    </div>
                                                    @if(count($detailPayload['site_images']) === 0)
                                                        <div class="text-muted small border rounded-3 p-3" style="background: #f9fafb;">No site images were attached to this report.</div>
                                                    @else
                                                        <div class="d-flex flex-wrap gap-2 mb-4">
                                                            @foreach(array_slice($detailPayload['site_images'], 0, 4) as $imageUrl)
                                                                <div class="img-thumbnail-grid d-flex align-items-center justify-content-center overflow-hidden p-0 client-report-image-preview" style="background: #f9fafb; border: 2px solid #e5e7eb; width: 72px; height: 72px; cursor: pointer;">
                                                                    <img src="{{ $imageUrl }}" alt="Site image" class="w-100 h-100 object-fit-cover">
                                                                </div>
                                                            @endforeach
                                                            @if(count($detailPayload['site_images']) > 4)
                                                                <div class="more-images-badge d-flex align-items-center justify-content-center" style="background: #f9fafb; border: 2px solid #e5e7eb; color: #6b7280;">+{{ count($detailPayload['site_images']) - 4 }} more</div>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="fw-bold mb-3" style="color: var(--cms-green-dark, #2a4028);">Approval Timeline</div>
                                                    <div class="timeline-container small px-1">
                                                        <div class="timeline-step active">
                                                            <div class="timeline-icon"><i class="bi bi-check"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Submitted</div>
                                                        </div>
                                                        <div class="timeline-step {{ $status !== 'pending' ? 'active' : 'current' }}">
                                                            <div class="timeline-icon"><i class="bi bi-clock"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">Under Review</div>
                                                        </div>
                                                        <div class="timeline-step {{ $status === 'approved' ? 'active' : ($status === 'rejected' ? 'active' : '') }}">
                                                            <div class="timeline-icon"><i class="bi bi-circle"></i></div>
                                                            <div class="fw-bold" style="font-size:0.75rem;">{{ $status === 'approved' ? 'Approved' : ($status === 'rejected' ? 'Returned' : 'Finalized') }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex justify-content-center" style="padding-top: 2rem; margin-top: 2rem; border-top: 2px solid rgba(42, 64, 40, 0.12);">
                                                        <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" class="btn btn-cms-primary report-export-link" data-report-id="{{ $report->report_id }}">
                                                            <i class="bi bi-download me-2"></i> Download PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
        <div class="report-mobile-list">
            @forelse($reports as $report)
                @php
                    $status = 'published';
                    $pillClass = 'bg-info-subtle text-info';
                    $displayStatus = 'published';
                    $displayPillClass = 'bg-info-subtle text-info';

                    $siteImages = is_array($report->site_images ?? null) ? $report->site_images : [];
                    $adminImages = is_array($report->admin_site_images ?? null) ? $report->admin_site_images : [];
                    $displayImages = !empty($adminImages) ? $adminImages : $siteImages;
                    $detailPayload = [
                        'id' => $report->report_id,
                        'report_id' => 'RPT-2026-' . str_pad($report->report_id, 4, '0', STR_PAD_LEFT),
                        'title' => $report->report_title ?? 'Report Details',
                        'status' => $displayStatus,
                        'status_label' => ucfirst($displayStatus),
                        'project' => optional($report->project)->project_name ?? 'N/A',
                        'phase' => optional($report->phase)->phase_name ?? 'N/A',
                        'submitted_by' => optional($report->submittedBy)->name ?? 'Supervisor',
                        'reviewed_by' => optional($report->reviewedBy)->name ?? '-',
                        'submitted_at' => optional($report->report_date)->format('M d, Y h:i A') ?? 'N/A',
                        'created_at' => optional($report->created_at)->format('M d, Y'),
                        'review_date' => optional($report->reviewed_at)->format('M d, Y') ?? 'Reviewed',
                        'approval_date' => optional($report->approved_at)->format('M d, Y') ?? optional($report->rejected_at)->format('M d, Y') ?? 'Pending',
                        'report_text' => $report->admin_report_text ?? $report->report_text ?? 'No description was provided for this report.',
                        'site_images' => array_map(fn ($image) => asset('storage/' . $image), $displayImages),
                        'admin_explanation' => $report->admin_explanation ?? '',
                        'submitted_initial' => strtoupper(substr(optional($report->submittedBy)->name ?? 'S', 0, 1)),
                        'status_class' => $displayPillClass,
                    ];
                @endphp

                <article class="report-mobile-card">
                    <div class="report-mobile-topline">
                        <div class="report-mobile-date">
                            <span>Report Date</span>
                            <strong>{{ optional($report->report_date)->format('M d, Y') ?? 'N/A' }}</strong>
                            <small>{{ optional($report->report_date)->format('h:i A') ?? '' }}</small>
                        </div>
                        <span class="status-pill {{ $displayPillClass }}">Published</span>
                    </div>

                    <div class="report-mobile-main">
                        <h3>{{ $report->report_title ?? optional($report->phase)->phase_name ?? 'Accomplishment Report' }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($report->admin_report_text ?? $report->report_text ?? 'Latest accomplishment update submitted by the site supervisor.'), 90) }}</p>
                    </div>

                    <div class="report-mobile-details">
                        <div class="report-mobile-detail-row">
                            <span>Project</span>
                            <strong>{{ optional($report->project)->project_name ?? 'Unknown Project' }}</strong>
                        </div>
                        <div class="report-mobile-detail-row">
                            <span>Phase</span>
                            <strong>{{ optional($report->phase)->phase_name ?? 'Unassigned Phase' }}</strong>
                        </div>
                        <div class="report-mobile-detail-row">
                            <span>Submitted By</span>
                            <strong>{{ optional($report->submittedBy)->name ?? 'Supervisor' }}</strong>
                        </div>
                    </div>

                    <div class="report-mobile-actions">
                        <button class="report-mobile-view js-report-view-btn" type="button" data-report-details='@json($detailPayload)' data-modal-target="reportDetailsModal-{{ $report->report_id }}">
                            <i class="bi bi-eye"></i>
                            View Details
                        </button>
                        <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" data-report-id="{{ $report->report_id }}" class="report-mobile-download report-export-link">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                </article>

                <div class="modal fade report-details-modal" id="reportDetailsModal-{{ $report->report_id }}" tabindex="-1" aria-labelledby="reportDetailsModalLabel-{{ $report->report_id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header" style="background: #ffffff; border-bottom: 2px solid var(--cms-green-dark, #2a4028);">
                                <div>
                                    <h5 class="modal-title fw-bold" id="reportDetailsModalLabel-{{ $report->report_id }}" style="color: var(--cms-green-dark, #2a4028);">Report Details</h5>
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
                                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $detailPayload['report_id'] }}</div>
                                                </div>
                                                <div class="text-sm-end">
                                                    <div class="small text-uppercase text-muted" style="font-weight: 600;">Approval Status</div>
                                                    <span class="status-pill {{ $pillClass }} p-2 mt-1 d-inline-block">{{ $status }}</span>
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-4 small">
                                                <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                    <div class="fw-semibold text-muted mb-1">Project</div>
                                                    <div class="text-dark">{{ $detailPayload['project'] }}</div>
                                                </div>
                                                <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                    <div class="fw-semibold text-muted mb-1">Construction Phase</div>
                                                    <div class="text-dark">{{ $detailPayload['phase'] }}</div>
                                                </div>
                                                <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                    <div class="fw-semibold text-muted mb-1">Report Date</div>
                                                    <div class="text-dark">{{ $detailPayload['submitted_at'] }}</div>
                                                </div>
                                                <div class="col-12 col-sm-6 p-3 rounded" style="background: #f9fafb; border-radius: 14px;">
                                                    <div class="fw-semibold text-muted mb-1">Submitted By</div>
                                                    <div class="text-dark">{{ $detailPayload['submitted_by'] }}</div>
                                                </div>
                                            </div>

                                            <div class="p-4 rounded-3 mb-4" style="white-space: pre-line; line-height: 1.7; background: #f9fafb; border-radius: 14px;">
                                                <div class="fw-bold mb-2" style="color: var(--cms-green-dark, #2a4028);">Construction Accomplishment</div>
                                                <p class="mb-0 text-dark small">{{ $detailPayload['report_text'] }}</p>
                                            </div>

                                            <div class="row g-3 mb-3">
                                                <div class="col-12 col-md-6">
                                                    <div class="p-3 rounded-3" style="background: #f9fafb; border-radius: 14px;">
                                                        <div class="fw-semibold text-muted mb-1">Reviewed By</div>
                                                        <div class="text-dark">{{ $detailPayload['reviewed_by'] }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <div class="p-3 rounded-3" style="background: #f9fafb; border-radius: 14px;">
                                                        <div class="fw-semibold text-muted mb-1">Approved By</div>
                                                        <div class="text-dark">{{ $detailPayload['status'] === 'approved' ? $detailPayload['reviewed_by'] : 'Pending approval' }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-3 rounded-3" style="background: #f9fafb; border-radius: 14px;">
                                                <div class="fw-semibold text-muted mb-1">Admin Remarks</div>
                                                <div class="text-dark small">{{ $detailPayload['admin_explanation'] ?: ($report->approval_remarks ?: 'No remarks') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-xl-5">
                                        <div class="report-detail-sidebar p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="fw-bold" style="color: var(--cms-green-dark, #2a4028);">Site Images</div>
                                                <div class="small text-muted">{{ count($detailPayload['site_images']) }} uploaded</div>
                                            </div>
                                            @if(count($detailPayload['site_images']) === 0)
                                                <div class="text-muted small border rounded-3 p-3" style="background: #f9fafb;">No site images were attached to this report.</div>
                                            @else
                                                <div class="d-flex flex-wrap gap-2 mb-4">
                                                    @foreach(array_slice($detailPayload['site_images'], 0, 4) as $imageUrl)
                                                        <div class="img-thumbnail-grid d-flex align-items-center justify-content-center overflow-hidden p-0 client-report-image-preview" style="background: #f9fafb; border: 2px solid #e5e7eb; width: 72px; height: 72px; cursor: pointer;">
                                                            <img src="{{ $imageUrl }}" alt="Site image" class="w-100 h-100 object-fit-cover">
                                                        </div>
                                                    @endforeach
                                                    @if(count($detailPayload['site_images']) > 4)
                                                        <div class="more-images-badge d-flex align-items-center justify-content-center" style="background: #f9fafb; border: 2px solid #e5e7eb; color: #6b7280;">+{{ count($detailPayload['site_images']) - 4 }} more</div>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="fw-bold mb-3" style="color: var(--cms-green-dark, #2a4028);">Approval Timeline</div>
                                            <div class="timeline-container small px-1">
                                                <div class="timeline-step active">
                                                    <div class="timeline-icon"><i class="bi bi-check"></i></div>
                                                    <div class="fw-bold" style="font-size:0.75rem;">Submitted</div>
                                                </div>
                                                <div class="timeline-step {{ $status !== 'pending' ? 'active' : 'current' }}">
                                                    <div class="timeline-icon"><i class="bi bi-clock"></i></div>
                                                    <div class="fw-bold" style="font-size:0.75rem;">Under Review</div>
                                                </div>
                                                <div class="timeline-step {{ $status === 'approved' ? 'active' : ($status === 'rejected' ? 'active' : '') }}">
                                                    <div class="timeline-icon"><i class="bi bi-circle"></i></div>
                                                    <div class="fw-bold" style="font-size:0.75rem;">{{ $status === 'approved' ? 'Approved' : ($status === 'rejected' ? 'Returned' : 'Finalized') }}</div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-center" style="padding-top: 2rem; margin-top: 2rem; border-top: 2px solid rgba(42, 64, 40, 0.12);">
                                                <a href="{{ route('client.reports.downloadPdf', $report->report_id) }}" class="btn btn-cms-primary report-export-link" data-report-id="{{ $report->report_id }}">
                                                    <i class="bi bi-download me-2"></i> Download PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="report-mobile-empty">
                    <i class="bi bi-file-earmark-text"></i>
                    <strong>No reports found</strong>
                    <span>No accomplishment reports match the current filters.</span>
                </div>
            @endforelse
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

    /* === Bootstrap Report Details Modal (matching supervisor) === */
    .report-details-modal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 18px 42px rgba(42, 64, 40, 0.18);
        background-color: #ffffff;
    }

    .report-details-modal .modal-header {
        background-color: #ffffff;
        border-bottom: 2px solid var(--cms-green-dark, #2a4028);
        padding: 1.1rem 1.25rem;
    }

    .report-details-modal .modal-title {
        color: var(--cms-green-dark, #2a4028);
        font-size: 1.25rem;
        font-weight: 700;
        font-family: 'DM Sans', sans-serif;
    }

    .report-details-modal .modal-body {
        padding: 1.5rem;
        background-color: #ffffff;
    }

    .report-details-modal .modal-footer {
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 1rem 1.25rem;
    }

    .report-detail-card,
    .report-detail-sidebar {
        border-radius: 16px;
        border: 1px solid rgba(42, 64, 40, 0.12);
        background: #ffffff;
        box-shadow: 0 18px 42px rgba(42, 64, 40, 0.06);
    }

    .report-detail-card {
        padding: 2rem;
    }

    .report-detail-sidebar {
        background: #f8fafc;
        border-color: rgba(42, 64, 40, 0.12);
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
        background: #f1f5f9;
        color: #1d321c;
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

    /* Approval timeline */
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
        border-color: #2a4028;
        background: #2a4028;
        color: #fff;
    }
    .timeline-step.current .timeline-icon {
        border-color: #ffc107;
        background: #fff;
        color: #ffc107;
    }

    /* Green download button matching supervisor */
    .btn-cms-primary {
        background-color: var(--cms-green-dark, #2a4028);
        color: #ffffff;
        border: none;
        font-weight: 600;
        padding: 0.6rem 1.4rem;
        border-radius: 10px;
        font-size: 0.9rem;
        transition: all 0.15s;
    }
    .btn-cms-primary:hover,
    .btn-cms-primary:focus {
        background-color: #1d321c;
        color: #ffffff;
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
        display: none;
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
        .client-reports-page h1,
        .client-reports-page h2,
        .client-reports-page h3,
        .client-reports-page h4,
        .client-reports-page h5,
        .client-reports-page h6 {
            font-family: 'DM Sans', sans-serif !important;
        }
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

    /* =====================================================================
       POLISHED MOBILE CLIENT REPORTS UI
       Fixes the missing Accomplishment Reports display on mobile by rendering
       real mobile cards instead of hiding the table without a replacement.
       ===================================================================== */
    .client-reports-page {
        --client-green: var(--brand-green, #2a4028);
        --client-green-dark: var(--brand-green-dark, #1d321c);
        --client-mint: var(--brand-mint, #f3faf4);
        --client-border: rgba(42, 64, 40, 0.10);
    }

    .client-reports-page .report-filter-card {
        background: linear-gradient(135deg, #ffffff 0%, #fbfffb 100%);
    }

    .client-reports-page .report-summary-widget {
        min-height: 92px;
        background: linear-gradient(135deg, #ffffff 0%, #fbfffb 100%);
    }

    .client-reports-page .report-main-panel {
        overflow: hidden;
    }

    .client-reports-page .report-mobile-list {
        display: none;
    }

    @media (max-width: 767.98px) {
        .client-reports-page {
            padding: 0 0 18px !important;
        }

        .client-reports-page .report-filter-card {
            padding: 13px !important;
            margin-bottom: 12px !important;
            border-radius: 18px !important;
        }

        .client-reports-page .report-filter-card form {
            gap: 10px !important;
        }

        .client-reports-page .report-filter-card .row {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        .client-reports-page .report-filter-card .row > [class*="col-"] {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
            padding: 0 !important;
        }

        .client-reports-page .report-filter-card .form-label {
            margin-bottom: 5px !important;
            color: #334155 !important;
            font-size: 11px !important;
            letter-spacing: 0.01em !important;
        }

        .client-reports-page .report-filter-card .form-select,
        .client-reports-page .report-filter-card .form-control {
            min-height: 42px !important;
            border-radius: 10px !important;
            font-size: 13px !important;
            border-color: #dfe8e2 !important;
            box-shadow: none !important;
        }

        .client-reports-page .report-summary-row {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        .client-reports-page .report-summary-row > [class*="col-"] {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
            padding: 0 !important;
        }

        .client-reports-page .report-summary-widget {
            min-height: 104px !important;
            padding: 12px !important;
            border-radius: 17px !important;
            align-items: flex-start !important;
            gap: 10px !important;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.045) !important;
        }

        .client-reports-page .widget-icon {
            width: 38px !important;
            height: 38px !important;
            border-radius: 13px !important;
            font-size: 1rem !important;
        }

        .client-reports-page .widget-label {
            display: block !important;
            font-size: 9.5px !important;
            line-height: 1.25 !important;
            letter-spacing: 0.06em !important;
        }

        .client-reports-page .report-summary-widget h3 {
            font-size: 1.25rem !important;
            line-height: 1 !important;
            margin-top: 6px !important;
        }

        .client-reports-page .report-main-panel {
            border-radius: 19px !important;
            border: 1px solid #dfe9e2 !important;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05) !important;
            margin-bottom: 18px !important;
            background: #ffffff !important;
        }

        .client-reports-page .report-main-panel > .p-3.border-bottom {
            padding: 15px 14px !important;
            align-items: flex-start !important;
            gap: 10px !important;
            background: linear-gradient(135deg, #fbfffb 0%, #ffffff 100%) !important;
        }

        .client-reports-page .report-main-panel h5 {
            font-size: 1.06rem !important;
            line-height: 1.2 !important;
            color: #143b25 !important;
        }

        .client-reports-page .report-main-panel .badge {
            white-space: nowrap !important;
            padding: 7px 10px !important;
            font-size: 11px !important;
        }

        .client-reports-page .report-main-panel > .table-responsive {
            display: none !important;
        }

        .client-reports-page .report-mobile-list {
            display: grid !important;
            gap: 12px !important;
            padding: 12px !important;
            background: #fbfdfb !important;
        }

        .client-reports-page .report-mobile-card {
            padding: 14px !important;
            border-radius: 18px !important;
            border: 1px solid #dfeae3 !important;
            background: #ffffff !important;
            box-shadow: 0 9px 22px rgba(15, 23, 42, 0.045) !important;
        }

        .client-reports-page .report-mobile-topline {
            display: flex !important;
            align-items: flex-start !important;
            justify-content: space-between !important;
            gap: 10px !important;
            padding-bottom: 11px !important;
            border-bottom: 1px solid #edf3ef !important;
        }

        .client-reports-page .report-mobile-date span,
        .client-reports-page .report-mobile-detail-row span {
            display: block !important;
            color: #64748b !important;
            font-size: 9.5px !important;
            font-weight: 800 !important;
            letter-spacing: 0.075em !important;
            line-height: 1.2 !important;
            text-transform: uppercase !important;
        }

        .client-reports-page .report-mobile-date strong {
            display: block !important;
            margin-top: 5px !important;
            color: #0f172a !important;
            font-size: 14px !important;
            line-height: 1.2 !important;
        }

        .client-reports-page .report-mobile-date small {
            display: block !important;
            margin-top: 2px !important;
            color: #7b8794 !important;
            font-size: 11.5px !important;
        }

        .client-reports-page .report-mobile-topline .status-pill {
            max-width: 126px !important;
            text-align: center !important;
            white-space: normal !important;
            line-height: 1.15 !important;
        }

        .client-reports-page .report-mobile-main {
            padding: 12px 0 10px !important;
        }

        .client-reports-page .report-mobile-main h3 {
            margin: 0 0 4px !important;
            color: #111827 !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            line-height: 1.3 !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        .client-reports-page .report-mobile-main p {
            margin: 0 !important;
            color: #64748b !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
        }

        .client-reports-page .report-mobile-details {
            display: grid !important;
            gap: 8px !important;
        }

        .client-reports-page .report-mobile-detail-row {
            display: grid !important;
            grid-template-columns: 92px minmax(0, 1fr) !important;
            gap: 12px !important;
            align-items: start !important;
            padding: 10px 11px !important;
            border: 1px solid #edf3ef !important;
            border-radius: 14px !important;
            background: #fbfdfb !important;
        }

        .client-reports-page .report-mobile-detail-row strong {
            color: #10271b !important;
            font-size: 13px !important;
            line-height: 1.35 !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        .client-reports-page .report-mobile-actions {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) 46px !important;
            gap: 9px !important;
            margin-top: 12px !important;
            padding-top: 12px !important;
            border-top: 1px solid #edf3ef !important;
        }

        .client-reports-page .report-mobile-view,
        .client-reports-page .report-mobile-download {
            min-height: 42px !important;
            border-radius: 13px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            text-decoration: none !important;
        }

        .client-reports-page .report-mobile-view {
            border: 1px solid var(--client-green) !important;
            background: var(--client-green) !important;
            color: #ffffff !important;
        }

        .client-reports-page .report-mobile-download {
            border: 1px solid #dbe8df !important;
            background: #f7fbf8 !important;
            color: var(--client-green) !important;
        }

        .client-reports-page .report-mobile-empty {
            display: grid !important;
            place-items: center !important;
            gap: 5px !important;
            min-height: 150px !important;
            padding: 24px !important;
            border: 1px dashed #dbe8df !important;
            border-radius: 18px !important;
            color: #64748b !important;
            text-align: center !important;
        }

        .client-reports-page .report-main-panel > .p-3.bg-light {
            padding: 13px !important;
            background: #ffffff !important;
        }

        .client-reports-page .pagination {
            margin: 8px 0 0 !important;
        }

        .client-reports-page .pagination .page-item .page-link {
            min-width: 36px !important;
            min-height: 36px !important;
            padding: 0.45rem 0.65rem !important;
            border-radius: 10px !important;
            font-size: 12px !important;
        }

        /* Modal mobile overrides: prevent visibility issues and allow scrolling */
        .report-details-modal .modal-dialog {
            margin: 0.5rem !important;
            max-width: calc(100vw - 1rem) !important;
            width: auto !important;
            z-index: 9999 !important;
            transform: none !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .report-details-modal.show .modal-dialog {
            transform: none !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .report-details-modal {
            z-index: 9999 !important;
            opacity: 1 !important;
        }

        .report-details-modal.show {
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .report-details-modal .modal-backdrop {
            z-index: 9998 !important;
        }

        .report-details-modal .modal-content {
            border-radius: 18px !important;
            max-height: calc(100vh - 1rem) !important;
            display: flex !important;
            flex-direction: column !important;
            background-color: #ffffff !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
        }

        .report-details-modal.show .modal-content {
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
        }

        .report-details-modal .modal-body {
            max-height: calc(100vh - 120px) !important;
            overflow-y: auto !important;
            padding: 0.85rem !important;
            flex: 1 1 auto !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
        }

        .report-details-modal .modal-header {
            padding: 0.85rem 0.85rem !important;
            flex-shrink: 0 !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
        }

        .report-details-modal .modal-header h5,
        .report-details-modal .modal-title {
            font-size: 1rem !important;
        }

        .report-details-modal .modal-footer {
            flex-shrink: 0 !important;
            padding: 0.85rem !important;
        }

        .report-detail-card,
        .report-detail-sidebar {
            padding: 0.85rem !important;
            border-radius: 15px !important;
        }

        .report-detail-card .row.g-3 > [class*="col-"] {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .report-detail-sidebar .img-thumbnail-grid {
            max-width: 80px !important;
            height: 64px !important;
            min-width: 64px !important;
        }

        .report-detail-sidebar .more-images-badge {
            min-width: 80px !important;
        }
    }

    @media (min-width: 768px) and (max-width: 1024px) {
        .client-reports-page .report-filter-card .row {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        .client-reports-page .report-filter-card .row > [class*="col-"] {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
            padding: 0 !important;
        }
    }

    #clientReportImageLightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    #clientReportImageLightbox.is-open {
        display: flex;
    }

    #clientReportImageLightbox img {
        max-width: 90%;
        max-height: 85vh;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }

    #clientLightboxCloseBtn {
        position: absolute;
        top: 1rem;
        right: 1.5rem;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 1.5rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
        z-index: 10000;
    }

    #clientLightboxCloseBtn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .client-report-image-preview img {
        cursor: pointer;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .client-report-image-preview:hover img {
        transform: scale(1.05);
        opacity: 0.9;
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

        const reportModals = document.querySelectorAll('.report-details-modal');
        reportModals.forEach(function (modal) {
            document.body.appendChild(modal);
        });

        document.querySelectorAll('.js-report-view-btn').forEach((button) => {
            button.addEventListener('click', function () {
                const modalId = this.dataset.modalTarget;
                if (!modalId) {
                    return;
                }
                const modalEl = document.getElementById(modalId);
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                    const payload = this.dataset.reportDetails ? JSON.parse(this.dataset.reportDetails) : null;
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });
        });

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

    <div class="image-lightbox" id="clientReportImageLightbox" role="dialog" aria-modal="true" aria-label="Image preview">
        <button type="button" class="image-lightbox-close" id="clientLightboxCloseBtn" aria-label="Close preview">&times;</button>
        <img src="" alt="Site image preview" id="clientLightboxImage">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const clientLightbox = document.getElementById('clientReportImageLightbox');
            const clientLightboxImage = document.getElementById('clientLightboxImage');

            window.openClientLightbox = function (imageUrl) {
                if (!clientLightbox || !clientLightboxImage || !imageUrl || typeof imageUrl !== 'string' || imageUrl.trim() === '') {
                    return;
                }
                clientLightboxImage.src = imageUrl;
                clientLightbox.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            };

            window.closeClientLightbox = function () {
                if (!clientLightbox) return;
                clientLightbox.classList.remove('is-open');
                document.body.style.overflow = '';
                if (clientLightboxImage) {
                    setTimeout(() => { clientLightboxImage.src = ''; }, 200);
                }
            };

            document.getElementById('clientLightboxCloseBtn')?.addEventListener('click', closeClientLightbox);
            clientLightbox?.addEventListener('click', function (event) {
                if (event.target === clientLightbox) {
                    closeClientLightbox();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && clientLightbox?.classList.contains('is-open')) {
                    closeClientLightbox();
                }
            });

            document.body.addEventListener('click', function (event) {
                const target = event.target;
                if (target.matches('.client-report-image-preview') || target.closest('.client-report-image-preview')) {
                    const img = target.matches('img') ? target : target.querySelector('img');
                    const imageUrl = img?.src || target.closest('.client-report-image-preview')?.querySelector('img')?.src;
                    if (imageUrl) {
                        openClientLightbox(imageUrl);
                    }
                }
            });
        });
    </script>
@endpush