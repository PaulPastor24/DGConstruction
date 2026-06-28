@extends('layouts.supervisor')

@section('title', 'Accomplishment Reports - Supervisor Workspace')
@section('page_title', 'Accomplishment Reports')

@push('styles')
    <style>
        .report-hero { padding: 1.15rem 1.2rem; }
        .report-search-card { border-radius: 18px; }
        .report-card { border-radius: 18px; border: 1px solid rgba(9,96,86,0.08); background: #fff; box-shadow: 0 10px 24px rgba(9,96,86,0.05); transition: transform 0.2s ease, box-shadow 0.2s ease; height: 100%; }
        .report-card:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(9,96,86,0.08); }
        .report-card .section-card-body { gap: 0.8rem; }
        .report-meta-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.65rem; }
        .report-meta-item { padding: 0.65rem 0.7rem; border-radius: 12px; background: linear-gradient(135deg, #fcfdfc 0%, #f4f8f6 100%); border: 1px solid rgba(9,96,86,0.08); }
        .report-label { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--supervisor-muted); margin-bottom: 0.2rem; }
        .report-description { color: var(--supervisor-muted); font-size: 0.92rem; line-height: 1.5; }
        .report-status-pill { display: inline-flex; align-items: center; padding: 0.4rem 0.75rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; }
        @media (max-width: 767px) {
            .report-meta-grid { grid-template-columns: 1fr; }
            .report-hero .d-flex { gap: 0.8rem; }
            .report-hero .btn { width: 100%; }
        }
    </style>
@endpush

@section('content')
@php
    $today = now();
    $assignedProjectName = optional($assignedProjects->first())->project_name ?? 'No assigned project';
@endphp

<section class="page-card report-hero mb-3">
    <div class="page-card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
        <div>
            <div class="eyebrow">Reporting Workspace</div>
            <h1 class="page-title mb-2">Accomplishment Reports</h1>
            <p class="page-subtitle mb-0">Create, review, and track daily site updates without losing focus on the work.</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <a href="{{ route('supervisor.reports') }}" class="btn btn-primary-soft">Create Report</a>
            <a href="{{ route('supervisor.timeline') }}" class="btn btn-outline-soft">Open Timeline</a>
        </div>
    </div>
</section>

<section class="page-card report-search-card mb-3">
    <div class="page-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label small text-uppercase text-muted">Search</label>
                <input name="search" value="{{ request('search') }}" placeholder="Search project or notes" class="form-control form-control-sm" />
            </div>
            <div class="col-12 col-lg-3">
                <label class="form-label small text-uppercase text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All status</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Returned</option>
                </select>
            </div>
            <div class="col-12 col-lg-3">
                <label class="form-label small text-uppercase text-muted">Project</label>
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All projects</option>
                    @foreach($assignedProjects as $project)
                        <option value="{{ $project->project_id }}" {{ request('project_id') == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2">
                <button type="submit" class="btn btn-outline-soft w-100">Apply</button>
            </div>
        </form>
    </div>
</section>

<section class="section-card">
    <div class="section-card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-1">
            <div>
                <h5 class="fw-bold mb-1">Recent Report History</h5>
                <p class="text-muted small mb-0">Review the latest work completed and keep submissions moving through the review flow.</p>
            </div>
            <div class="small text-muted">Showing {{ $reports->count() }} of {{ $reports->total() }}</div>
        </div>

        @if($reports->isEmpty())
            <div class="empty-state">No reports match the current filters. Adjust the search or status filter to continue.</div>
        @else
            <div class="row g-3">
                @foreach($reports as $report)
                    @php
                        $reviewStatus = $report->approval_status === 'approved' ? 'Reviewed' : ($report->approval_status === 'rejected' ? 'Returned for revision' : 'Pending review');
                        $statusClass = $report->approval_status === 'approved' ? 'bg-success-subtle text-success-emphasis' : ($report->approval_status === 'rejected' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis');
                    @endphp
                    <div class="col-12 col-lg-6">
                        <article class="section-card report-card">
                            <div class="section-card-body">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="eyebrow">Report</div>
                                        <div class="fw-bold">{{ optional($report->report_date)->format('M d, Y') }}</div>
                                    </div>
                                    <span class="report-status-pill {{ $statusClass }}">{{ ucfirst($report->approval_status ?? 'pending') }}</span>
                                </div>

                                <div class="report-meta-grid">
                                    <div class="report-meta-item">
                                        <div class="report-label">Construction Phase</div>
                                        <div class="fw-semibold">{{ optional($report->phase)->phase_name ?? 'Unassigned phase' }}</div>
                                    </div>
                                    <div class="report-meta-item">
                                        <div class="report-label">Progress</div>
                                        <div class="fw-semibold">{{ optional($report->phase)->completion_percentage ?? 'N/A' }}%</div>
                                    </div>
                                    <div class="report-meta-item">
                                        <div class="report-label">Engineer Review</div>
                                        <div class="fw-semibold">{{ $reviewStatus }}</div>
                                    </div>
                                    <div class="report-meta-item">
                                        <div class="report-label">Project</div>
                                        <div class="fw-semibold">{{ optional($report->project)->project_name ?? 'Unknown project' }}</div>
                                    </div>
                                </div>

                                <div class="report-description">{{ \Illuminate\Support\Str::limit($report->report_text, 140) }}</div>

                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-1">
                                    <div class="small text-muted">Submitted by {{ optional($report->submittedBy)->name ?? 'Supervisor' }}</div>
                                    <a href="{{ route('supervisor.reports.show', $report->report_id) }}" class="btn btn-sm btn-outline-soft">View Details</a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="d-flex justify-content-end mt-3">
            {{ $reports->links() }}
        </div>
    </div>
</section>
@endsection
