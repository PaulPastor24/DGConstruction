@extends('layouts.admin')

@section('title', 'Worker Attendance')
@section('page_title', 'Worker Attendance')

@push('styles')

<style>
    :root {
        --attendance-ink: #10271b;
        --attendance-muted: #7f8c84;
        --attendance-line: #e8eee9;
        --attendance-surface: #ffffff;
        --attendance-soft: #f7f9f7;
        --attendance-accent: #6f8b67;
        --attendance-accent-dark: #173824;
        --attendance-success-bg: #e5f5e8;
        --attendance-success-text: #23713f;
        --attendance-warning-bg: #fff0d5;
        --attendance-warning-text: #a76508;
        --attendance-danger-bg: #fde7e7;
        --attendance-danger-text: #b63c3c;
        --attendance-shadow: 0 14px 34px rgba(16, 39, 27, 0.07);
    }

    .attendance-page {
        width: 100%;
        padding: 6px 0 24px;
    }

    .attendance-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 28px;
    }

    .attendance-title-wrap {
        min-width: 0;
    }

    .attendance-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: var(--attendance-accent);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .attendance-title {
        margin: 0;
        color: var(--attendance-ink);
        font-family: 'Syne', sans-serif;
        font-size: clamp(28px, 2vw, 40px);
        font-weight: 700;
        letter-spacing: -0.8px;
    }

    .attendance-subtitle {
        margin: 8px 0 0;
        color: var(--attendance-muted);
        font-size: 14px;
        line-height: 1.7;
    }

    .attendance-date-control {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        min-width: 190px;
        padding: 13px 16px;
        border: 1px solid var(--attendance-line);
        border-radius: 16px;
        background: var(--attendance-surface);
        box-shadow: 0 8px 24px rgba(16, 39, 27, 0.06);
        color: #506057;
        font-size: 13px;
        white-space: nowrap;
    }

    .attendance-date-control .date-icon {
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 10px;
        background: #edf3ee;
        color: var(--attendance-accent-dark);
    }

    .attendance-date-control .date-copy {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 2px;
    }

    .attendance-date-control .date-label {
        color: #96a198;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }

    .attendance-date-control .date-value {
        color: #33443a;
        font-size: 13px;
        font-weight: 600;
    }

    .attendance-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 26px;
    }

    .attendance-stat-card {
        position: relative;
        min-height: 142px;
        overflow: hidden;
        padding: 24px;
        border: 1px solid var(--attendance-line);
        border-radius: 18px;
        background: var(--attendance-surface);
        box-shadow: var(--attendance-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .attendance-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(16, 39, 27, 0.10);
    }

    .attendance-stat-card::after {
        position: absolute;
        right: -22px;
        bottom: -40px;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(111, 139, 103, 0.06);
        content: '';
    }

    .attendance-stat-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .attendance-stat-label {
        color: #6f7d74;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.1px;
        text-transform: uppercase;
    }

    .attendance-stat-icon {
        position: relative;
        z-index: 1;
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 14px;
        background: linear-gradient(145deg, #f1f6f1, #e7eee8);
        color: var(--attendance-accent-dark);
        font-size: 21px;
    }

    .attendance-stat-value {
        position: relative;
        z-index: 1;
        margin-top: 14px;
        color: var(--attendance-ink);
        font-family: 'Syne', sans-serif;
        font-size: 36px;
        font-weight: 700;
        letter-spacing: -1px;
    }

    .attendance-panel {
        overflow: hidden;
        border: 1px solid var(--attendance-line);
        border-radius: 20px;
        background: var(--attendance-surface);
        box-shadow: var(--attendance-shadow);
    }

    .attendance-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        border-bottom: 1px solid var(--attendance-line);
    }

    .attendance-toolbar-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .attendance-toolbar-icon {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 12px;
        background: #edf3ee;
        color: var(--attendance-accent-dark);
        font-size: 17px;
    }

    .attendance-toolbar h2 {
        margin: 0;
        color: var(--attendance-ink);
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 700;
    }

    .attendance-toolbar p {
        margin: 4px 0 0;
        color: #98a39c;
        font-size: 12px;
    }

    .attendance-search {
        position: relative;
        width: min(360px, 100%);
    }

    .attendance-search i {
        position: absolute;
        top: 50%;
        left: 15px;
        color: #88958d;
        transform: translateY(-50%);
    }

    .attendance-search input {
        width: 100%;
        height: 44px;
        padding: 0 16px 0 43px;
        border: 1px solid #dfe6e1;
        border-radius: 14px;
        outline: none;
        background: #fbfcfb;
        color: #25372c;
        font-size: 13px;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease,
            background 0.2s ease;
    }

    .attendance-search input:focus {
        border-color: #8ea289;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(111, 139, 103, 0.12);
    }

    .attendance-table-wrapper {
        overflow-x: auto;
    }

    .attendance-table {
        width: 100%;
        min-width: 1180px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .attendance-table thead th {
        padding: 15px 18px;
        border-bottom: 1px solid var(--attendance-line);
        background: #fafbfa;
        color: #78857d;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .attendance-table tbody td {
        padding: 17px 18px;
        border-bottom: 1px solid #edf1ee;
        color: #4c5b52;
        font-size: 12px;
        vertical-align: middle;
    }

    .attendance-table tbody tr {
        transition: background 0.18s ease;
    }

    .attendance-table tbody tr:hover {
        background: #fbfcfb;
    }

    .attendance-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .worker-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 180px;
    }

    .worker-avatar {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid #dfe7e1;
        border-radius: 50%;
        background: linear-gradient(145deg, #eef4ef, #e3ebe5);
        color: #35523d;
        font-size: 12px;
        font-weight: 700;
    }

    .worker-name {
        color: #17291f;
        font-size: 13px;
        font-weight: 700;
    }

    .worker-secondary {
        margin-top: 3px;
        color: #98a29c;
        font-size: 11px;
    }

    .project-name {
        color: #425148;
        font-weight: 500;
    }

    .attendance-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        padding: 6px 11px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .status-present {
        background: var(--attendance-success-bg);
        color: var(--attendance-success-text);
    }

    .status-absent {
        background: var(--attendance-danger-bg);
        color: var(--attendance-danger-text);
    }

    .status-late,
    .status-half-day {
        background: var(--attendance-warning-bg);
        color: var(--attendance-warning-text);
    }

    .status-default {
        background: #edf1ee;
        color: #66746b;
    }

    .biometric-state {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }

    .biometric-state i {
        font-size: 14px;
    }

    .biometric-verified {
        color: #367548;
        font-weight: 600;
    }

    .biometric-unverified {
        color: #8b9690;
    }

    .recorded-by {
        color: #4a594f;
        font-weight: 500;
        white-space: nowrap;
    }

    .remarks-cell {
        display: inline-block;
        max-width: 180px;
        overflow: hidden;
        color: #657169;
        text-overflow: ellipsis;
        vertical-align: middle;
        white-space: nowrap;
    }

    .row-actions {
        display: inline-grid;
        width: 32px;
        height: 32px;
        place-items: center;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #7f8b84;
        cursor: pointer;
        transition:
            background 0.18s ease,
            color 0.18s ease;
    }

    .row-actions:hover {
        background: #edf3ee;
        color: var(--attendance-accent-dark);
    }

    .attendance-empty {
        padding: 72px 20px;
        color: #89958e;
        text-align: center;
    }

    .attendance-empty i {
        display: grid;
        width: 56px;
        height: 56px;
        margin: 0 auto 14px;
        place-items: center;
        border-radius: 16px;
        background: #edf3ee;
        color: #56705d;
        font-size: 24px;
    }

    .attendance-empty strong {
        display: block;
        margin-bottom: 4px;
        color: #34463b;
        font-size: 14px;
    }

    .attendance-empty span {
        font-size: 12px;
    }

    @media (max-width: 1200px) {
        .attendance-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .attendance-page {
            padding-top: 0;
        }

        .attendance-hero,
        .attendance-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .attendance-date-control {
            width: 100%;
        }

        .attendance-search {
            width: 100%;
        }
    }

    @media (max-width: 560px) {
        .attendance-stat-grid {
            grid-template-columns: 1fr;
        }

        .attendance-stat-card {
            min-height: 126px;
        }

        .attendance-title {
            font-size: 27px;
        }
    }
</style>

@endpush

@section('content')
@php
$presentCount = $logs
->filter(
fn ($log) =>
strtolower($log->status ?? '') === 'present'
)
->count();

$absentCount = $logs
    ->filter(
        fn ($log) =>
            strtolower($log->status ?? '') === 'absent'
    )
    ->count();

$lateCount = $logs
    ->filter(
        fn ($log) =>
            in_array(
                strtolower($log->status ?? ''),
                ['late', 'half_day', 'half day'],
                true
            )
    )
    ->count();

$totalCount = $logs->count();

@endphp

<div class="attendance-page">

<div class="attendance-hero">

    <div class="attendance-title-wrap">

        <div class="attendance-eyebrow">
            <i class="bi bi-people"></i>
            Workforce Monitoring
        </div>

        <h1 class="attendance-title">
            Worker Attendance
        </h1>

        <p class="attendance-subtitle">
            Monitor worker time-in, time-out, biometric
            verification and daily attendance records.
        </p>

    </div>

    <div class="attendance-date-control">

        <span class="date-icon">
            <i class="bi bi-calendar3"></i>
        </span>

        <span class="date-copy">

            <span class="date-label">
                Current date
            </span>

            <span class="date-value">
                {{ now()->format('F d, Y') }}
            </span>

        </span>

        <i class="bi bi-chevron-down"></i>

    </div>

</div>

<div class="attendance-stat-grid">

    <article class="attendance-stat-card">

        <div class="attendance-stat-header">

            <span class="attendance-stat-label">
                Total Records
            </span>

            <span class="attendance-stat-icon">
                <i class="bi bi-people"></i>
            </span>

        </div>

        <div class="attendance-stat-value">
            {{ number_format($totalCount) }}
        </div>

    </article>

    <article class="attendance-stat-card">

        <div class="attendance-stat-header">

            <span class="attendance-stat-label">
                Present
            </span>

            <span class="attendance-stat-icon">
                <i class="bi bi-person-check"></i>
            </span>

        </div>

        <div class="attendance-stat-value">
            {{ number_format($presentCount) }}
        </div>

    </article>

    <article class="attendance-stat-card">

        <div class="attendance-stat-header">

            <span class="attendance-stat-label">
                Late / Half Day
            </span>

            <span class="attendance-stat-icon">
                <i class="bi bi-clock-history"></i>
            </span>

        </div>

        <div class="attendance-stat-value">
            {{ number_format($lateCount) }}
        </div>

    </article>

    <article class="attendance-stat-card">

        <div class="attendance-stat-header">

            <span class="attendance-stat-label">
                Absent
            </span>

            <span class="attendance-stat-icon">
                <i class="bi bi-person-x"></i>
            </span>

        </div>

        <div class="attendance-stat-value">
            {{ number_format($absentCount) }}
        </div>

    </article>

</div>

<section class="attendance-panel">

    <div class="attendance-toolbar">

        <div class="attendance-toolbar-title">

            <span class="attendance-toolbar-icon">
                <i class="bi bi-card-checklist"></i>
            </span>

            <div>

                <h2>
                    Attendance Records
                </h2>

                <p>
                    Review attendance, deployment,
                    verification and supervisor details.
                </p>

            </div>

        </div>

        <div class="attendance-search">

            <i class="bi bi-search"></i>

            <input
                type="search"
                id="attendanceSearch"
                placeholder="Search worker, project or status..."
                autocomplete="off"
                aria-label="Search attendance records"
            >

        </div>

    </div>

    <div class="attendance-table-wrapper">

        <table
            class="attendance-table"
            id="attendanceTable"
        >

            <thead>

                <tr>
                    <th>Worker</th>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Biometric</th>
                    <th>Recorded By</th>
                    <th>Remarks</th>
                    <th aria-label="Actions"></th>
                </tr>

            </thead>

            <tbody>

                @forelse($logs as $log)

                    @php
                        $deployment = $log->deployment;

                        $worker = $deployment?->worker;

                        $project = $deployment?->project;

                        $firstName =
                            $worker?->first_name ?? '';

                        $lastName =
                            $worker?->last_name ?? '';

                        $workerName = trim(
                            $firstName . ' ' . $lastName
                        );

                        if ($workerName === '') {
                            $workerName =
                                $worker?->full_name
                                ?? $worker?->name
                                ?? 'Unknown Worker';
                        }

                        $workerPosition =
                            $worker?->position
                            ?? $worker?->job_title
                            ?? $worker?->trade
                            ?? 'Worker';

                        $projectName =
                            $project?->project_name
                            ?? $project?->name
                            ?? 'No Project';

                        $initials = strtoupper(
                            mb_substr(
                                $firstName,
                                0,
                                1
                            )
                            .
                            mb_substr(
                                $lastName,
                                0,
                                1
                            )
                        );

                        if ($initials === '') {
                            $initials = strtoupper(
                                mb_substr(
                                    $workerName,
                                    0,
                                    2
                                )
                            );
                        }

                        $status = strtolower(
                            $log->status ?? 'unknown'
                        );

                        $statusClass = match ($status) {
                            'present' =>
                                'status-present',

                            'absent' =>
                                'status-absent',

                            'late' =>
                                'status-late',

                            'half_day',
                            'half day' =>
                                'status-half-day',

                            default =>
                                'status-default',
                        };

                        $statusLabel = match ($status) {
                            'half_day' =>
                                'Half Day',

                            default =>
                                ucwords(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $status
                                    )
                                ),
                        };

                        $recordedByName = trim(
                            (
                                $log->recordedBy?->first_name
                                ?? ''
                            )
                            .
                            ' '
                            .
                            (
                                $log->recordedBy?->last_name
                                ?? ''
                            )
                        );

                        if ($recordedByName === '') {
                            $recordedByName =
                                $log->recordedBy?->name
                                ?? 'Unknown User';
                        }
                    @endphp

                    <tr>

                        <td>

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

                        <td>

                            <span class="project-name">
                                {{ $projectName }}
                            </span>

                        </td>

                        <td>

                            @if($log->log_date)

                                {{
                                    \Carbon\Carbon::parse(
                                        $log->log_date
                                    )->format('M d, Y')
                                }}

                            @else

                                —

                            @endif

                        </td>

                        <td>

                            @if($log->time_in)

                                {{
                                    \Carbon\Carbon::parse(
                                        $log->time_in
                                    )->format('h:i A')
                                }}

                            @else

                                —

                            @endif

                        </td>

                        <td>

                            @if($log->time_out)

                                {{
                                    \Carbon\Carbon::parse(
                                        $log->time_out
                                    )->format('h:i A')
                                }}

                            @else

                                —

                            @endif

                        </td>

                        <td>

                            <span
                                class="attendance-status {{ $statusClass }}"
                            >
                                {{ $statusLabel }}
                            </span>

                        </td>

                        <td>

                            @if($log->biometric_matched)

                                <span
                                    class="biometric-state biometric-verified"
                                >

                                    <i class="bi bi-check-circle"></i>

                                    Verified

                                </span>

                            @else

                                <span
                                    class="biometric-state biometric-unverified"
                                >

                                    <i class="bi bi-dash-circle"></i>

                                    Not Verified

                                </span>

                            @endif

                        </td>

                        <td>

                            <span class="recorded-by">
                                {{ $recordedByName }}
                            </span>

                        </td>

                        <td>

                            <span
                                class="remarks-cell"
                                title="{{ $log->remarks ?? 'No remarks' }}"
                            >
                                {{ $log->remarks ?? '—' }}
                            </span>

                        </td>

                        <td>

                            <button
                                type="button"
                                class="row-actions"
                                aria-label="Attendance record options"
                                title="More options"
                            >
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="10">

                            <div class="attendance-empty">

                                <i class="bi bi-calendar-x"></i>

                                <strong>
                                    No attendance records found
                                </strong>

                                <span>
                                    Attendance records will appear
                                    here once they are submitted.
                                </span>

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
        const attendanceSearch =
            document.getElementById('attendanceSearch');

        const attendanceRows =
            document.querySelectorAll(
                '#attendanceTable tbody tr'
            );

        attendanceSearch?.addEventListener(
            'input',
            function () {
                const keyword =
                    this.value
                        .toLowerCase()
                        .trim();

                attendanceRows.forEach(function (row) {
                    const searchableText =
                        row.textContent.toLowerCase();

                    row.style.display =
                        searchableText.includes(keyword)
                            ? ''
                            : 'none';
                });
            }
        );
    });
</script>

@endpush
