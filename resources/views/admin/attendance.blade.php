@extends('layouts.admin')

@section('title', 'Worker Attendance')
@section('page_title', 'Worker Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}?v={{ time() }}">

    <style>
        /* Mobile alignment polish for Attendance Records empty state and toolbar */
        @media (max-width: 768px) {
            .attendance-page {
                width: 100%;
                max-width: 100%;
                margin: 0 auto;
            }

            .attendance-panel,
            .attendance-issues-panel {
                width: 100%;
                overflow: hidden;
            }

            .attendance-toolbar {
                align-items: stretch !important;
                gap: 16px !important;
            }

            .attendance-toolbar-title {
                display: grid !important;
                grid-template-columns: 42px minmax(0, 1fr) !important;
                align-items: center !important;
                gap: 12px !important;
                width: 100% !important;
            }

            .attendance-toolbar-title h2,
            .attendance-toolbar-title p {
                text-align: left !important;
            }

            .attendance-toolbar-icon {
                width: 42px !important;
                height: 42px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                flex: 0 0 42px !important;
            }

            .attendance-toolbar-actions {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 10px !important;
                width: 100% !important;
            }

            .attendance-search,
            .btn-print {
                width: 100% !important;
            }

            .attendance-table-wrapper {
                width: 100% !important;
                overflow-x: hidden !important;
                border-radius: 0 0 18px 18px !important;
            }

            .attendance-table {
                width: 100% !important;
                min-width: 0 !important;
                table-layout: fixed !important;
            }

            .attendance-table tbody {
                width: 100% !important;
            }

            .attendance-table tbody tr:not([data-attendance-row]) {
                display: block !important;
                width: 100% !important;
            }

            .attendance-table tbody tr:not([data-attendance-row]) td {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
                border: 0 !important;
            }

            .attendance-empty {
                min-height: 300px !important;
                width: min(100%, 310px) !important;
                margin: 0 auto !important;
                padding: 42px 18px !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
            }

            .attendance-empty i {
                width: 58px !important;
                height: 58px !important;
                margin: 0 auto 14px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 18px !important;
            }

            .attendance-empty strong,
            .attendance-empty span {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
            }

            .issues-empty {
                display: flex !important;
                align-items: center !important;
                justify-content: flex-start !important;
                gap: 10px !important;
                width: 100% !important;
                text-align: left !important;
            }
        }

        @media (max-width: 420px) {
            .attendance-empty {
                width: min(100%, 280px) !important;
                min-height: 280px !important;
                padding-inline: 12px !important;
            }

            .attendance-toolbar-title {
                grid-template-columns: 38px minmax(0, 1fr) !important;
                gap: 10px !important;
            }

            .attendance-toolbar-icon {
                width: 38px !important;
                height: 38px !important;
                flex-basis: 38px !important;
            }
        }
    </style>
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
        const SILENT_RELOAD_INTERVAL = 5000; // 5 seconds for real-time admin attendance monitoring
        const ATTENDANCE_PAGE_SELECTOR = '.attendance-page';
        const FILTER_FORM_SELECTOR = '.attendance-filter-form';

        let activeStatFilter = 'all';
        let isSilentReloading = false;
        let silentReloadTimer = null;

        const filterLabels = {
            all: 'All Records',
            present: 'Present Workers',
            late: 'Late / Half Day Workers',
            absent: 'Absent Workers',
            'missing-timeout': 'Workers Missing Time Out',
            'break-exceeded': 'Workers With Break Exceeded'
        };

        function getAttendanceRows() {
            return Array.from(document.querySelectorAll('#attendanceTable tbody tr[data-attendance-row="1"]'));
        }

        function getAttendanceSearch() {
            return document.getElementById('attendanceSearch');
        }

        function getStatFilterCards() {
            return Array.from(document.querySelectorAll('.stat-filter-card'));
        }

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
            const attendanceSearch = getAttendanceSearch();
            const tableFilterNote = document.getElementById('tableFilterNote');
            const tableFilterLabel = document.getElementById('tableFilterLabel');

            const keyword = attendanceSearch
                ? attendanceSearch.value.toLowerCase().trim()
                : '';

            getAttendanceRows().forEach(function (row) {
                const searchableText = row.textContent.toLowerCase();
                const matchesSearch = searchableText.includes(keyword);
                const matchesStat = rowMatchesStatFilter(row);

                row.style.display = matchesSearch && matchesStat ? '' : 'none';
            });

            getStatFilterCards().forEach(function (card) {
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

        function captureFilterFormInitialValues() {
            const filterForm = document.querySelector(FILTER_FORM_SELECTOR);

            if (!filterForm) {
                return;
            }

            filterForm.querySelectorAll('input, select, textarea').forEach(function (field) {
                field.dataset.initialValue = field.value ?? '';
            });
        }

        function filterFormHasUnsavedChanges() {
            const filterForm = document.querySelector(FILTER_FORM_SELECTOR);

            if (!filterForm) {
                return false;
            }

            const fields = filterForm.querySelectorAll('input, select, textarea');

            for (const field of fields) {
                const initialValue = field.dataset.initialValue ?? field.defaultValue ?? '';
                const currentValue = field.value ?? '';

                if (initialValue !== currentValue) {
                    return true;
                }
            }

            return false;
        }

        function userIsEditing() {
            const active = document.activeElement;

            if (!active) {
                return false;
            }

            return (
                active.tagName === 'INPUT' ||
                active.tagName === 'TEXTAREA' ||
                active.tagName === 'SELECT' ||
                active.isContentEditable
            );
        }

        function modalIsOpen() {
            return document.querySelector('.modal.show') !== null;
        }

        function shouldSkipSilentReload() {
            return (
                isSilentReloading ||
                document.hidden ||
                modalIsOpen() ||
                userIsEditing() ||
                filterFormHasUnsavedChanges()
            );
        }

        function setLiveRefreshStatus(message, type = 'muted') {
            let badge = document.getElementById('attendanceLiveRefreshStatus');
            const toolbarActions = document.querySelector('.attendance-toolbar-actions');

            if (!toolbarActions) {
                return;
            }

            if (!badge) {
                badge = document.createElement('span');
                badge.id = 'attendanceLiveRefreshStatus';
                badge.className = 'attendance-live-refresh-status';
                badge.style.fontSize = '12px';
                badge.style.fontWeight = '700';
                badge.style.whiteSpace = 'nowrap';
                badge.style.display = 'inline-flex';
                badge.style.alignItems = 'center';
                badge.style.gap = '6px';
                badge.style.color = '#64748b';
                toolbarActions.prepend(badge);
            }

            const icon = type === 'success'
                ? 'bi-arrow-repeat'
                : (type === 'error' ? 'bi-wifi-off' : 'bi-broadcast');

            badge.innerHTML = `<i class="bi ${icon}"></i> ${message}`;
            badge.style.color = type === 'error' ? '#dc2626' : (type === 'success' ? '#166534' : '#64748b');
        }

        async function silentReloadAttendancePage() {
            if (shouldSkipSilentReload()) {
                return;
            }

            const currentPage = document.querySelector(ATTENDANCE_PAGE_SELECTOR);

            if (!currentPage) {
                return;
            }

            const quickSearchValue = getAttendanceSearch()?.value || '';
            const currentScrollY = window.scrollY;

            try {
                isSilentReloading = true;
                setLiveRefreshStatus('Updating...', 'muted');

                const response = await fetch(window.location.href, {
                    method: 'GET',
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Silent-Attendance-Reload': 'true'
                    },
                    cache: 'no-store',
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Unable to refresh attendance records.');
                }

                const html = await response.text();
                const parser = new DOMParser();
                const newDocument = parser.parseFromString(html, 'text/html');
                const newPage = newDocument.querySelector(ATTENDANCE_PAGE_SELECTOR);

                if (!newPage) {
                    throw new Error('Attendance content was not found in the response.');
                }

                currentPage.replaceWith(newPage);

                const refreshedSearch = getAttendanceSearch();

                if (refreshedSearch) {
                    refreshedSearch.value = quickSearchValue;
                }

                captureFilterFormInitialValues();
                applyTableFilters();

                window.scrollTo({
                    top: currentScrollY,
                    behavior: 'instant'
                });

                setLiveRefreshStatus('Live updating', 'success');
                document.dispatchEvent(new CustomEvent('adminAttendanceSilentReloadComplete'));
            } catch (error) {
                console.warn('Admin attendance silent reload skipped:', error);
                setLiveRefreshStatus('Live update paused', 'error');
            } finally {
                isSilentReloading = false;
            }
        }

        document.addEventListener('input', function (event) {
            if (event.target && event.target.id === 'attendanceSearch') {
                applyTableFilters();
            }
        });

        document.addEventListener('click', function (event) {
            const statCard = event.target.closest('.stat-filter-card');

            if (statCard) {
                activeStatFilter = statCard.dataset.statFilter || 'all';
                applyTableFilters();

                document.getElementById('attendanceTable')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                return;
            }

            const clearButton = event.target.closest('#clearTableFilter');

            if (clearButton) {
                activeStatFilter = 'all';

                const attendanceSearch = getAttendanceSearch();

                if (attendanceSearch) {
                    attendanceSearch.value = '';
                }

                applyTableFilters();
            }
        });

        captureFilterFormInitialValues();
        applyTableFilters();
        setLiveRefreshStatus('Live updating', 'success');

        if (silentReloadTimer) {
            clearInterval(silentReloadTimer);
        }

        silentReloadTimer = setInterval(silentReloadAttendancePage, SILENT_RELOAD_INTERVAL);
    });
</script>
@endpush