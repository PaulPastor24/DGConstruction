@extends('layouts.admin')

@section('title', 'Attendance Report Preview')
@section('page_title', 'Attendance Report Preview')

@section('content')
@php
    $records = $records ?? collect();
    $date = $date ?? \Carbon\Carbon::today();
@endphp

<div class="attendance-page">
    <div class="attendance-print-header" aria-hidden="true">
        <h1>Attendance Report Preview</h1>
        <p>Date: {{ $date->format('F d, Y') }}</p>
        <p class="text-muted">Review the records below before sending the report to your email.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success') || session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if(session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: @json(session('success')),
                        confirmButtonColor: '#198754'
                    });
                @endif

                @if(session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: @json(session('error')),
                        confirmButtonColor: '#dc3545'
                    });
                @endif
            });
        </script>
    @endif

    <section class="attendance-filter-card mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1">Report Summary</h5>
                <p class="text-muted mb-0">
                    Total records: <strong>{{ $records->count() }}</strong>
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.attendance') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Back to Attendance
                </a>

                @if($records->count() > 0)
                    <form method="POST" action="{{ route('admin.attendance.send-report') }}" onsubmit="return confirm('Send this attendance report to your email?');">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-envelope"></i>
                            Send Report to My Email
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="attendance-panel">
        <div class="attendance-toolbar">
            <div class="attendance-toolbar-title">
                <span class="attendance-toolbar-icon">
                    <i class="bi bi-card-checklist"></i>
                </span>

                <div>
                    <h2>Attendance Records</h2>
                    <p>These are the exact records that will be included in the email report.</p>
                </div>
            </div>
        </div>

        <div class="attendance-table-wrapper">
            <table class="attendance-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Worker</th>
                        <th>Project / Deployment</th>
                        <th>Schedule</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>OT</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($records as $record)
                        @php
                            $worker = $record->display_worker ?? $record->worker ?? $record->deployment?->worker;
                            $project = $record->display_project ?? $record->deployment?->project;

                            $firstName = $worker?->first_name ?? '';
                            $lastName = $worker?->last_name ?? '';

                            $workerName = trim($firstName . ' ' . $lastName);

                            if ($workerName === '') {
                                $workerName = $worker?->full_name ?? $worker?->name ?? 'Unknown Worker';
                            }

                            $projectName = $project?->project_name ?? $project?->name ?? 'No Project';
                            $scheduleStart = $worker?->schedule_start ?? '07:00';
                            $scheduleEnd = $worker?->schedule_end ?? '17:00';
                            $status = strtolower($record->status ?? 'unknown');

                            $statusClass = match ($status) {
                                'present' => 'status-present',
                                'absent' => 'status-absent',
                                'late' => 'status-late',
                                'half_day', 'half day' => 'status-half-day',
                                default => 'status-default',
                            };

                            $statusLabel = match ($status) {
                                'half_day' => 'Half Day',
                                default => ucwords(str_replace('_', ' ', $status)),
                            };

                            $overtimeMinutes = (int) ($record->overtime_minutes ?? 0);
                            $overtimeLabel = $overtimeMinutes > 0
                                ? ('OT ' . intdiv($overtimeMinutes, 60) . 'h ' . ($overtimeMinutes % 60) . 'm')
                                : '—';
                        @endphp

                        <tr>
                            <td data-label="Worker">
                                <div class="worker-info">
                                    <div class="worker-name">
                                        {{ $workerName }}
                                    </div>
                                </div>
                            </td>

                            <td data-label="Project / Deployment">
                                <span class="project-name">
                                    {{ $projectName }}
                                </span>
                            </td>

                            <td data-label="Schedule">
                                {{ \Carbon\Carbon::parse($scheduleStart)->format('g:i A') }} - {{ \Carbon\Carbon::parse($scheduleEnd)->format('g:i A') }}
                            </td>

                            <td data-label="Time In">
                                {{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '—' }}
                            </td>

                            <td data-label="Time Out">
                                {{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '—' }}
                            </td>

                            <td data-label="Status">
                                <span class="attendance-status {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td data-label="OT">
                                @if($overtimeMinutes > 0)
                                    <span class="attendance-status status-default">
                                        {{ $overtimeLabel }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="attendance-empty">
                                    <i class="bi bi-calendar-x"></i>
                                    <strong>No attendance records found</strong>
                                    <span>There are no attendance records for the selected date.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
