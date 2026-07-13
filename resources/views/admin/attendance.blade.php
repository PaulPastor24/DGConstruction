@extends('layouts.admin')

@section('title', 'Worker Attendance')
@section('page_title', 'Worker Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $logs = $logs ?? collect();

    $filters = $filters ?? [
        'date' => now()->toDateString(),
        'project_id' => '',
        'status' => '',
        'biometric' => '',
        'search' => '',
    ];

    $projects = $projects ?? collect();

    $stats = $stats ?? [
        'total' => $logs->count(),
        'present' => $logs->filter(fn ($log) => strtolower($log->status ?? '') === 'present')->count(),
        'late' => $logs->filter(fn ($log) => in_array(strtolower($log->status ?? ''), ['late', 'half_day', 'half day'], true))->count(),
        'absent' => $logs->filter(fn ($log) => strtolower($log->status ?? '') === 'absent')->count(),
        'missing_timeout' => 0,
        'break_exceeded' => 0,
        'verified' => $logs->filter(fn ($log) => (bool) $log->biometric_matched)->count(),
    ];

    $issues = $issues ?? collect();
@endphp

<div class="attendance-page">

    <div class="attendance-hero">
        <div class="attendance-title-wrap">
            <div class="attendance-eyebrow">
                <i class="bi bi-clipboard-data"></i>
                Admin Workforce Monitoring
            </div>

            <h1 class="attendance-title">Worker Attendance</h1>

            <p class="attendance-subtitle">
                Review daily worker attendance, late records, absences, break logs, biometric verification, and supervisor-submitted entries.
            </p>
        </div>
    </div>

    <section class="attendance-filter-card">
        <form method="GET" action="{{ route('admin.attendance') }}" class="attendance-filter-form">

            <div class="filter-group">
                <label for="date">
                    <i class="bi bi-calendar3"></i>
                    Date
                </label>

                <input type="date"
                       id="date"
                       name="date"
                       value="{{ $filters['date'] ?? '' }}">
            </div>

            <div class="filter-group">
                <label for="project_id">
                    <i class="bi bi-building"></i>
                    Project
                </label>

                <select id="project_id" name="project_id">
                    <option value="">All Projects</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->project_id }}"
                            @selected((string) ($filters['project_id'] ?? '') === (string) $project->project_id)>
                            {{ $project->project_name ?? $project->name ?? 'Unnamed Project' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="status">
                    <i class="bi bi-funnel"></i>
                    Status
                </label>

                <select id="status" name="status">
                    <option value="">All Status</option>
                    <option value="present" @selected(($filters['status'] ?? '') === 'present')>Present</option>
                    <option value="late" @selected(($filters['status'] ?? '') === 'late')>Late / Half Day</option>
                    <option value="absent" @selected(($filters['status'] ?? '') === 'absent')>Absent</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="biometric">
                    <i class="bi bi-fingerprint"></i>
                    Biometric
                </label>

                <select id="biometric" name="biometric">
                    <option value="">All</option>
                    <option value="verified" @selected(($filters['biometric'] ?? '') === 'verified')>Verified</option>
                    <option value="unverified" @selected(($filters['biometric'] ?? '') === 'unverified')>Not Verified</option>
                </select>
            </div>

            <div class="filter-group filter-search-wide">
                <label for="search">
                    <i class="bi bi-search"></i>
                    Search
                </label>

                <input type="search"
                       id="search"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Search worker, project, status, remarks...">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter-primary">
                    <i class="bi bi-funnel"></i>
                    Apply
                </button>

                <a href="{{ route('admin.attendance') }}" class="btn-filter-secondary">
                    <i class="bi bi-x-circle"></i>
                    Clear
                </a>
            </div>
        </form>
    </section>

    <div class="attendance-stat-grid">
        <button type="button" class="attendance-stat-card stat-filter-card" data-stat-filter="all">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Total Records</span>
                <span class="attendance-stat-icon">
                    <i class="bi bi-people"></i>
                </span>
            </div>

            <div class="attendance-stat-value">
                {{ number_format($stats['total'] ?? 0) }}
            </div>
        </button>

        <button type="button" class="attendance-stat-card stat-filter-card" data-stat-filter="present">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Present</span>
                <span class="attendance-stat-icon">
                    <i class="bi bi-person-check"></i>
                </span>
            </div>

            <div class="attendance-stat-value">
                {{ number_format($stats['present'] ?? 0) }}
            </div>
        </button>

        <button type="button" class="attendance-stat-card stat-filter-card" data-stat-filter="late">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Late / Half Day</span>
                <span class="attendance-stat-icon">
                    <i class="bi bi-clock-history"></i>
                </span>
            </div>

            <div class="attendance-stat-value">
                {{ number_format($stats['late'] ?? 0) }}
            </div>
        </button>

        <button type="button" class="attendance-stat-card stat-filter-card" data-stat-filter="absent">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Absent</span>
                <span class="attendance-stat-icon">
                    <i class="bi bi-person-x"></i>
                </span>
            </div>

            <div class="attendance-stat-value">
                {{ number_format($stats['absent'] ?? 0) }}
            </div>
        </button>

        <button type="button" class="attendance-stat-card stat-filter-card" data-stat-filter="missing-timeout">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Missing Time Out</span>
                <span class="attendance-stat-icon">
                    <i class="bi bi-box-arrow-right"></i>
                </span>
            </div>

            <div class="attendance-stat-value">
                {{ number_format($stats['missing_timeout'] ?? 0) }}
            </div>
        </button>

        <button type="button" class="attendance-stat-card stat-filter-card" data-stat-filter="break-exceeded">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Break Exceeded</span>
                <span class="attendance-stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </span>
            </div>

            <div class="attendance-stat-value">
                {{ number_format($stats['break_exceeded'] ?? 0) }}
            </div>
        </button>
    </div>

    <section class="attendance-issues-panel">
        <div class="issues-header">
            <div>
                <h2>Attendance Issues / Exceptions</h2>
                <p>Records that may need admin review.</p>
            </div>

            <span class="issues-count">
                {{ $issues->count() }} flagged
            </span>
        </div>

        @if($issues->count() > 0)
            <div class="issues-list">
                @foreach($issues as $issue)
                    @php
                        $issueWorker = $issue->display_worker ?? $issue->worker ?? $issue->deployment?->worker;
                        $issueProject = $issue->display_project ?? $issue->deployment?->project;

                        $issueWorkerName = trim(($issueWorker?->first_name ?? '') . ' ' . ($issueWorker?->last_name ?? ''));

                        if ($issueWorkerName === '') {
                            $issueWorkerName = $issueWorker?->full_name ?? $issueWorker?->name ?? 'Unknown Worker';
                        }

                        $issueProjectName = $issueProject?->project_name ?? $issueProject?->name ?? 'No Project';
                        $issueStatus = strtolower($issue->status ?? '');

                        $issueReason = 'Needs review';

                        if ($issueStatus === 'absent') {
                            $issueReason = 'Absent / no scan recorded';
                        } elseif (in_array($issueStatus, ['late', 'half_day', 'half day'], true)) {
                            $issueReason = 'Late or half-day attendance';
                        } elseif ($issue->time_in && !$issue->time_out) {
                            $issueReason = 'Missing time out';
                        } elseif (!$issue->biometric_matched) {
                            $issueReason = 'Biometric not verified';
                        }

                        if ($issue->break_out && $issue->break_in) {
                            try {
                                $issueDate = $issue->log_date
                                    ? \Carbon\Carbon::parse($issue->log_date)->toDateString()
                                    : now()->toDateString();

                                $breakOut = \Carbon\Carbon::parse($issueDate . ' ' . $issue->break_out);
                                $breakIn = \Carbon\Carbon::parse($issueDate . ' ' . $issue->break_in);

                                if ($breakOut->diffInMinutes($breakIn, false) > 60) {
                                    $issueReason = 'Break exceeded 1 hour';
                                }
                            } catch (\Throwable $error) {
                                // Keep default issue reason.
                            }
                        }
                    @endphp

                    <div class="issue-item">
                        <div class="issue-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <div class="issue-body">
                            <strong>{{ $issueWorkerName }}</strong>
                            <span>{{ $issueReason }} • {{ $issueProjectName }}</span>
                        </div>

                        <div class="issue-date">
                            {{ $issue->log_date ? \Carbon\Carbon::parse($issue->log_date)->format('M d') : '—' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="issues-empty">
                <i class="bi bi-check2-circle"></i>
                No attendance issues found for the selected filter.
            </div>
        @endif
    </section>

    <section class="attendance-panel">
        <div class="attendance-toolbar">
            <div class="attendance-toolbar-title">
                <span class="attendance-toolbar-icon">
                    <i class="bi bi-card-checklist"></i>
                </span>

                <div>
                    <h2>Attendance Records</h2>
                    <p>Tap a summary card above to show the matching workers.</p>
                </div>
            </div>

            <div class="attendance-toolbar-actions">
                <div class="attendance-search">
                    <i class="bi bi-search"></i>

                    <input type="search"
                           id="attendanceSearch"
                           placeholder="Quick search current table..."
                           autocomplete="off"
                           aria-label="Search attendance records">
                </div>

                <button type="button" class="btn-print" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                    Print
                </button>
            </div>
        </div>

        <div class="table-filter-note d-none" id="tableFilterNote">
            <span>
                Showing:
                <strong id="tableFilterLabel">All Records</strong>
            </span>

            <button type="button" id="clearTableFilter">
                Clear table filter
            </button>
        </div>

        <div class="attendance-table-wrapper">
            <table class="attendance-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Worker</th>
                        <th>Project</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Break Out</th>
                        <th>Break In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Biometric</th>
                        <th>Recorded By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        @php
                            $worker = $log->display_worker ?? $log->worker ?? $log->deployment?->worker;
                            $project = $log->display_project ?? $log->deployment?->project;

                            $firstName = $worker?->first_name ?? '';
                            $lastName = $worker?->last_name ?? '';

                            $workerName = trim($firstName . ' ' . $lastName);

                            if ($workerName === '') {
                                $workerName = $worker?->full_name ?? $worker?->name ?? 'Unknown Worker';
                            }

                            $workerPosition = $worker?->position ?? $worker?->job_title ?? $worker?->trade ?? 'Worker';
                            $projectName = $project?->project_name ?? $project?->name ?? 'No Project';

                            $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));

                            if ($initials === '') {
                                $initials = strtoupper(substr($workerName, 0, 2));
                            }

                            $status = strtolower($log->status ?? 'unknown');

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

                            $recordedByName = trim(($log->recordedBy?->first_name ?? '') . ' ' . ($log->recordedBy?->last_name ?? ''));

                            if ($recordedByName === '') {
                                $recordedByName = $log->recordedBy?->name ?? 'Unknown User';
                            }

                            $missingTimeout = $log->time_in
                                && !$log->time_out
                                && in_array($status, ['present', 'late', 'half_day', 'half day'], true);

                            $breakExceeded = false;

                            if ($log->break_out && $log->break_in) {
                                try {
                                    $rowDate = $log->log_date
                                        ? \Carbon\Carbon::parse($log->log_date)->toDateString()
                                        : now()->toDateString();

                                    $breakOutTime = \Carbon\Carbon::parse($rowDate . ' ' . $log->break_out);
                                    $breakInTime = \Carbon\Carbon::parse($rowDate . ' ' . $log->break_in);

                                    $breakExceeded = $breakOutTime->diffInMinutes($breakInTime, false) > 60;
                                } catch (\Throwable $error) {
                                    $breakExceeded = false;
                                }
                            }
                        @endphp

                        <tr
                            data-attendance-row="1"
                            data-status="{{ $status }}"
                            data-missing-timeout="{{ $missingTimeout ? '1' : '0' }}"
                            data-break-exceeded="{{ $breakExceeded ? '1' : '0' }}"
                        >
                            <td data-label="Worker">
                                <div class="worker-info">
                                    <div class="worker-avatar">
                                        {{ $initials ?: 'W' }}
                                    </div>

                                    <div>
                                        <div class="worker-name">
                                            {{ $workerName }}
                                        </div>

                                        <div class="worker-secondary">
                                            {{ $workerPosition }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Project">
                                <span class="project-name">
                                    {{ $projectName }}
                                </span>
                            </td>

                            <td data-label="Date">
                                {{ $log->log_date ? \Carbon\Carbon::parse($log->log_date)->format('M d, Y') : '—' }}
                            </td>

                            <td data-label="Time In">
                                {{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '—' }}
                            </td>

                            <td data-label="Break Out">
                                {{ $log->break_out ? \Carbon\Carbon::parse($log->break_out)->format('h:i A') : '—' }}
                            </td>

                            <td data-label="Break In">
                                {{ $log->break_in ? \Carbon\Carbon::parse($log->break_in)->format('h:i A') : '—' }}
                            </td>

                            <td data-label="Time Out">
                                {{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '—' }}
                            </td>

                            <td data-label="Status">
                                <span class="attendance-status {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td data-label="Biometric">
                                @if($log->biometric_matched)
                                    <span class="biometric-state biometric-verified">
                                        <i class="bi bi-check-circle"></i>
                                        Verified
                                    </span>
                                @else
                                    <span class="biometric-state biometric-unverified">
                                        <i class="bi bi-dash-circle"></i>
                                        Not Verified
                                    </span>
                                @endif
                            </td>

                            <td data-label="Recorded By">
                                <span class="recorded-by">
                                    {{ $recordedByName }}
                                </span>
                            </td>

                            <td data-label="Remarks">
                                <span class="remarks-cell" title="{{ $log->remarks ?? 'No remarks' }}">
                                    {{ $log->remarks ?? '—' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="attendance-empty">
                                    <i class="bi bi-calendar-x"></i>
                                    <strong>No attendance records found</strong>
                                    <span>Attendance records will appear here once supervisors submit worker logs.</span>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const attendanceSearch = document.getElementById('attendanceSearch');
        const attendanceRows = document.querySelectorAll('#attendanceTable tbody tr[data-attendance-row="1"]');
        const statFilterCards = document.querySelectorAll('.stat-filter-card');
        const tableFilterNote = document.getElementById('tableFilterNote');
        const tableFilterLabel = document.getElementById('tableFilterLabel');
        const clearTableFilter = document.getElementById('clearTableFilter');

        let activeStatFilter = 'all';

        const filterLabels = {
            all: 'All Records',
            present: 'Present Workers',
            late: 'Late / Half Day Workers',
            absent: 'Absent Workers',
            'missing-timeout': 'Workers Missing Time Out',
            'break-exceeded': 'Workers With Break Exceeded'
        };

        function rowMatchesStatFilter(row) {
            const status = row.dataset.status || '';

            if (activeStatFilter === 'all') {
                return true;
            }

            if (activeStatFilter === 'present') {
                return status === 'present';
            }

            if (activeStatFilter === 'late') {
                return ['late', 'half_day', 'half day'].includes(status);
            }

            if (activeStatFilter === 'absent') {
                return status === 'absent';
            }

            if (activeStatFilter === 'missing-timeout') {
                return row.dataset.missingTimeout === '1';
            }

            if (activeStatFilter === 'break-exceeded') {
                return row.dataset.breakExceeded === '1';
            }

            return true;
        }

        function applyTableFilters() {
            const keyword = attendanceSearch
                ? attendanceSearch.value.toLowerCase().trim()
                : '';

            attendanceRows.forEach(function (row) {
                const searchableText = row.textContent.toLowerCase();
                const matchesSearch = searchableText.includes(keyword);
                const matchesStat = rowMatchesStatFilter(row);

                row.style.display = matchesSearch && matchesStat ? '' : 'none';
            });

            statFilterCards.forEach(function (card) {
                card.classList.toggle(
                    'active-stat-filter',
                    card.dataset.statFilter === activeStatFilter
                );
            });

            if (activeStatFilter === 'all') {
                tableFilterNote?.classList.add('d-none');
            } else {
                tableFilterNote?.classList.remove('d-none');

                if (tableFilterLabel) {
                    tableFilterLabel.textContent = filterLabels[activeStatFilter] || 'Filtered Records';
                }
            }
        }

        attendanceSearch?.addEventListener('input', function () {
            applyTableFilters();
        });

        statFilterCards.forEach(function (card) {
            card.addEventListener('click', function () {
                activeStatFilter = this.dataset.statFilter || 'all';
                applyTableFilters();

                document.getElementById('attendanceTable')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        clearTableFilter?.addEventListener('click', function () {
            activeStatFilter = 'all';

            if (attendanceSearch) {
                attendanceSearch.value = '';
            }

            applyTableFilters();
        });
    });
</script>
@endpush