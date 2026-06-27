@extends('layouts.admin')

@section('title', 'Progress Reports - D&G Construction Monitor')
@section('page_title', 'Progress Reports')

@section('content')
<div class="page active" id="pg-reports">

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card p-3 border-start border-warning border-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 10px;">Awaiting Review</small>
                <div class="h3 fw-black my-1 text-dark">{{ $queueCount['awaiting_review'] ?? 0 }}</div>
                <small class="text-muted" style="font-size: 11px;">Submitted by supervisors today</small>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card p-3 border-start border-primary border-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 10px;">In Review</small>
                <div class="h3 fw-black my-1 text-dark">{{ $queueCount['in_review'] ?? 0 }}</div>
                <small class="text-muted" style="font-size: 11px;">Assigned to admin reviewers</small>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card p-3 border-start border-success border-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 10px;">Approved</small>
                <div class="h3 fw-black my-1 text-dark">{{ $queueCount['approved'] ?? 0 }}</div>
                <small class="text-success" style="font-size: 11px;">This week</small>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card p-3 border-start border-danger border-3">
                <small class="text-uppercase text-muted fw-bold" style="font-size: 10px;">Needs Revision</small>
                <div class="h3 fw-black my-1 text-dark">{{ $queueCount['needs_revision'] ?? 0 }}</div>
                <small class="text-danger" style="font-size: 11px;">Returned to supervisor</small>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title h6 mb-0">Supervisor Submissions Queue</div>
            @if(isset($submissions) && count($submissions) > 0)
                <span class="badge bg-warning-soft text-warning px-2 py-1 rounded" style="font-size: 12px; background-color: rgba(245,158,11,0.1); color: #d97706 !important;">
                    {{ count($submissions) }} Pending
                </span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4" style="padding: 12px 16px;">Project</th>
                        <th style="padding: 12px 16px;">Supervisor</th>
                        <th style="padding: 12px 16px;">Submitted</th>
                        <th style="padding: 12px 16px;">Phase</th>
                        <th style="padding: 12px 16px;">Status</th>
                        <th class="pe-4 text-end" style="padding: 12px 16px; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions ?? [] as $sub)
                        <tr class="{{ (isset($selectedReport) && $selectedReport->id == $sub->id) ? 'table-active' : '' }}">
                            <td class="ps-4 fw-bold text-dark" style="padding: 14px 16px;">{{ $sub->project_name }}</td>
                            <td style="padding: 14px 16px; color: var(--muted);">{{ $sub->supervisor_name }}</td>
                            <td style="padding: 14px 16px; color: var(--muted);">{{ \Carbon\Carbon::parse($sub->submitted_at)->format('M d, h:i A') }}</td>
                            <td style="padding: 14px 16px;">
                                <span class="badge bg-light text-dark border px-2 py-1 rounded fw-normal" style="font-size: 11px;">
                                    {{ $sub->phase_name }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px;">
                                @if($sub->status === 'Awaiting Review')
                                    <span class="badge px-2 py-1 rounded fw-medium" style="font-size: 11px; background-color: rgba(245,158,11,0.08); color: #d97706;">Awaiting Review</span>
                                @else
                                    <span class="badge px-2 py-1 rounded fw-medium" style="font-size: 11px; background-color: rgba(59,130,246,0.08); color: #2563eb;">In Review</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end" style="padding: 14px 16px;">
                                <a href="?report_id={{ $sub->id }}" class="btn btn-sm btn-light border px-3" style="font-size: 12px; padding: 4px 12px;">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-5 text-center text-muted">
                                <p class="mb-0">No supervisor accomplishment logs or verification submittals pending in queue.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($selectedReport))
        <div class="row match-height">
            
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title h6 mb-0">Selected Report Details</div>
                        <span class="badge bg-secondary-soft text-dark px-2 py-1 rounded" style="font-size: 11px; background-color: rgba(0,0,0,0.05);">{{ $selectedReport->project_name }}</span>
                    </div>
                    <div class="card-body d-flex flex-column gap-3" style="font-size: 13px;">
                        <div class="d-flex justify-content-between pb-2 border-bottom">
                            <span class="text-muted">Supervisor</span>
                            <span class="fw-bold text-dark">{{ $selectedReport->supervisor_fullname }}</span>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom">
                            <span class="text-muted">Report Period</span>
                            <span class="text-dark">{{ $selectedReport->period_range }}</span>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom">
                            <span class="text-muted">Current Phase</span>
                            <span class="text-dark fw-medium">{{ $selectedReport->phase_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between pb-2 border-bottom">
                            <span class="text-muted">Completion</span>
                            <span class="text-success fw-bold">{{ $selectedReport->completion_percentage }}%</span>
                        </div>
                        
                        <div class="mt-2 flex-grow-1">
                            <div class="p-3 bg-light rounded border text-secondary" style="font-size: 13px; line-height: 1.5; min-height: 100px;">
                                {{ $selectedReport->notes_summary }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="card-title h6 mb-0">Evidence & Review Decision</div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between gap-4">
                        
                        <div class="d-flex flex-column gap-2">
                            @forelse($selectedReport->attachments ?? [] as $file)
                                <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light" style="font-size: 12px;">
                                    <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                        <span class="text-muted">📄</span>
                                        <a href="{{ asset($file->file_path) }}" target="_blank" class="text-dark fw-medium text-truncate text-decoration-none hover-underline">
                                            {{ $file->file_name }}
                                        </a>
                                    </div>
                                    <span class="text-muted flex-shrink-0" style="font-size: 11px;">{{ $file->file_size_readable }}</span>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3 border border-dashed rounded">
                                    <small>No attached image verification files or PDF logs provided.</small>
                                </div>
                            @endforelse
                        </div>

                        <form action="{{ route('admin.reports.evaluate', $selectedReport->id) }}" method="POST" class="m-0 border-top pt-3">
                            @csrf
                            <div class="mb-3">
                                <label for="reviewer_notes" class="form-label fw-bold text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.3px;">Reviewer Notes</label>
                                <textarea class="form-textarea form-control" id="reviewer_notes" name="reviewer_notes" rows="3" placeholder="Add findings, required revisions, or approval notes..." style="font-size: 13px;" required></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" name="decision" value="revision" class="btn btn-outline-secondary px-4" style="font-size: 13px; background-color: #f8fafc;">
                                    Request Revision
                                </button>
                                <button type="submit" name="decision" value="approve" class="btn btn-success px-4" style="font-size: 13px; background-color: #16a34a; border-color: #16a34a;">
                                    Approve Report
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    @else
        <div class="card p-4 text-center text-muted">
            <small>Select an active supervisor log row from the queue grid above to isolate item logs, review evidence files, and execute deployment approvals.</small>
        </div>
    @endif

</div>
@endsection