@extends('layouts.supervisor')

@section('title', 'Accomplishment Reports - Supervisor View')
@section('page_title', 'Accomplishment Reports')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Accomplishment Reports</h4>
                <p class="text-muted mb-0 small">Review submitted progress reports for your assigned projects.</p>
            </div>
        </div>

        @if($reports->isEmpty())
            <div class="alert alert-light border">No reports have been submitted yet.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Phase</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Submitted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr>
                                <td>{{ $report->project->project_name ?? 'N/A' }}</td>
                                <td>{{ $report->phase->phase_name ?? 'N/A' }}</td>
                                <td>{{ optional($report->report_date)->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $report->approval_status === 'approved' ? 'success' : ($report->approval_status === 'rejected' ? 'danger' : 'warning') }}-subtle text-{{ $report->approval_status === 'approved' ? 'success' : ($report->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($report->approval_status ?? 'pending') }}
                                    </span>
                                </td>
                                <td>{{ $report->submittedBy->name ?? 'Unknown' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
