@extends('layouts.admin')

@section('title', 'Phase Management - D&G Construction Monitor')
@section('page_title', 'Phase Management')

@section('content')
<div class="page active" id="pg-phases">

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title h6 mb-0">Incoming Site Supervisor Reports</div>
            @if(isset($pendingReports) && count($pendingReports) > 0)
                <span class="badge bg-warning-soft text-warning px-2 py-1 rounded" style="font-size: 12px; background-color: rgba(245,158,11,0.1); color: #d97706 !important;">
                    {{ count($pendingReports) }} Field Updates Awaiting Engineer Review
                </span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4" style="padding: 12px 16px;">Assigned Project</th>
                        <th style="padding: 12px 16px;">Assigned Site Supervisor</th>
                        <th style="padding: 12px 16px;">Log Submission Date</th>
                        <th style="padding: 12px 16px;">Reported Physical Progress</th>
                        <th style="padding: 12px 16px;">Recommended Phase Transition</th>
                        <th class="pe-4 text-end" style="padding: 12px 16px; width: 200px;">Admin Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReports ?? [] as $report)
                        <tr>
                            <td class="ps-4 fw-bold text-dark" style="padding: 16px;">
                                {{ $report->project->project_name ?? 'Unassigned Asset' }}
                                <small class="d-block text-muted fw-normal" style="font-size: 11px; margin-top: 2px;">
                                    📍 Location: {{ $report->project->project_location ?? 'Pending' }}
                                </small>
                            </td>
                            <td style="padding: 16px;">
                                <div class="fw-medium text-dark">{{ $report->supervisor->name ?? $report->user->name ?? 'Assigned Supervisor' }}</div>
                                <small style="font-size: 10px;" class="text-uppercase tracking-wider text-muted">On-Site Operations</small>
                            </td>
                            <td style="padding: 16px; color: var(--muted);">
                                {{ \Carbon\Carbon::parse($report->period_date)->format('M d, Y') }}
                                <small class="d-block text-muted" style="font-size: 11px;">Field Accomplishment Log</small>
                            </td>
                            <td style="padding: 16px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; min-width: 80px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden; display: flex;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $report->estimated_completion }}%; background-color: #2563eb;" aria-valuenow="{{ $report->estimated_completion }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $report->estimated_completion }}%</span>
                                </div>
                            </td>
                            <td style="padding: 16px;">
                                <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1 rounded fw-medium" style="font-size: 11px; color: #2563eb !important;">
                                    {{ $report->recommended_phase }}
                                </span>
                            </td>
                            <td class="pe-4 text-end" style="padding: 16px;">
                                <div class="d-inline-flex gap-2">
                                    <form action="{{ route('admin.reports.approve', $report->report_id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success px-3 d-flex align-items-center gap-1" style="font-size: 12px; padding: 4px 12px;">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.reports.revise', $report->report_id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary px-3" style="font-size: 12px; padding: 4px 12px; background-color: #f8fafc;">
                                            Request Revision
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-5 text-center text-muted">
                                <div class="py-3">
                                    <p class="mb-0">All clear. No pending progress logs submitted by site supervisors require validation.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title h6 mb-0">Project Control Room Ledger &mdash; Recent Phase Determinations</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light text-uppercase text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4" style="padding: 12px 16px;">Log Timestamp</th>
                        <th style="padding: 12px 16px;">Target Asset</th>
                        <th style="padding: 12px 16px;">Action Event Parameters</th>
                        <th class="pe-4" style="padding: 12px 16px;">Authorized Signatory</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditLogs ?? [] as $log)
                        <tr>
                            <td class="ps-4 text-muted" style="padding: 16px;">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y - H:i') }}
                            </td>
                            <td style="padding: 16px; color: var(--muted);" class="fw-medium text-dark">
                                {{ $log->project->project_name ?? 'N/A' }}
                            </td>
                            <td style="padding: 16px;">
                                @if($log->action_type === 'approved')
                                    <span class="badge px-2 py-1 rounded fw-medium" style="font-size: 12px; background-color: rgba(34,197,94,0.1); color: #16a34a;">
                                        ⚙️ Phase Confirmed: {{ $log->phase_name }}
                                    </span>
                                @else
                                    <span class="badge px-2 py-1 rounded fw-medium" style="font-size: 12px; background-color: rgba(239,68,68,0.1); color: #dc2626;">
                                        ⚠️ Revision Requested by Owner
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-muted" style="padding: 16px;">
                                <div class="text-dark fw-medium">{{ $log->user->name ?? 'Engr. Admin' }}</div>
                                <small style="font-size: 10px;" class="text-muted text-uppercase">Project Owner & Admin</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-5 text-center text-muted">
                                <div class="py-3">
                                    <p class="mb-0">No phase adjustments or validation workflows logged by the Project Owner yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection