@extends('layouts.admin')

@section('title', 'Worker Attendance')
@section('page_title', 'Worker Attendance')

@push('styles')
    {{-- Pulling the externalized style sheet cleanly from resources/css directory --}}
    @vite(['resources/css/admin-attendance.css'])
@endpush

@section('content')
@php
$presentCount = $logs
    ->filter(fn ($log) => strtolower($log->status ?? '') === 'present')
    ->count();

$absentCount = $logs
    ->filter(fn ($log) => strtolower($log->status ?? '') === 'absent')
    ->count();

$lateCount = $logs
    ->filter(fn ($log) => in_array(strtolower($log->status ?? ''), ['late', 'half_day', 'half day'], true))
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
            <h1 class="attendance-title">Worker Attendance</h1>
            <p class="attendance-subtitle">
                Monitor worker time-in, time-out, biometric verification and daily attendance records.
            </p>
        </div>

        <div class="attendance-date-control">
            <span class="date-icon"><i class="bi bi-calendar3"></i></span>
            <span class="date-copy">
                <span class="date-label">Current date</span>
                <span class="date-value">{{ now()->format('F d, Y') }}</span>
            </span>
            <i class="bi bi-chevron-down"></i>
        </div>
    </div>

    <!-- Biometric Control Station Node Overlay -->
    <section class="biometric-station">
        <div class="bio-layout">
            <div class="bio-details">
                <h3><i class="bi bi-fingerprint"></i> Biometric Hardware Node</h3>
                <p>Register administrative security keys or simulate authentication verification on mobile browsers.</p>
            </div>
            <div class="bio-actions">
                <button type="button" class="btn-bio btn-bio-register" id="btnRegisterPasskey">
                    <i class="bi bi-shield-plus"></i> Register Admin Biometrics
                </button>
                <button type="button" class="btn-bio btn-bio-auth" id="btnAuthenticatePasskey">
                    <i class="bi bi-patch-check"></i> Test Auth Verify
                </button>
            </div>
        </div>
    </section>

    <div class="attendance-stat-grid">
        <article class="attendance-stat-card">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Total Records</span>
                <span class="attendance-stat-icon"><i class="bi bi-people"></i></span>
            </div>
            <div class="attendance-stat-value">{{ number_format($totalCount) }}</div>
        </article>

        <article class="attendance-stat-card">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Present</span>
                <span class="attendance-stat-icon"><i class="bi bi-person-check"></i></span>
            </div>
            <div class="attendance-stat-value">{{ number_format($presentCount) }}</div>
        </article>

        <article class="attendance-stat-card">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Late / Half Day</span>
                <span class="attendance-stat-icon"><i class="bi bi-clock-history"></i></span>
            </div>
            <div class="attendance-stat-value">{{ number_format($lateCount) }}</div>
        </article>

        <article class="attendance-stat-card">
            <div class="attendance-stat-header">
                <span class="attendance-stat-label">Absent</span>
                <span class="attendance-stat-icon"><i class="bi bi-person-x"></i></span>
            </div>
            <div class="attendance-stat-value">{{ number_format($absentCount) }}</div>
        </article>
    </div>

    <section class="attendance-panel">
        <div class="attendance-toolbar">
            <div class="attendance-toolbar-title">
                <span class="attendance-toolbar-icon"><i class="bi bi-card-checklist"></i></span>
                <div>
                    <h2>Attendance Records</h2>
                    <p>Review attendance, deployment, verification and supervisor details.</p>
                </div>
            </div>
            <div class="attendance-search">
                <i class="bi bi-search"></i>
                <input type="search" id="attendanceSearch" placeholder="Search worker, project or status..." autocomplete="off" aria-label="Search attendance records">
            </div>
        </div>

        <div class="attendance-table-wrapper">
            <table class="attendance-table" id="attendanceTable">
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

                            $firstName = $worker?->first_name ?? '';
                            $lastName = $worker?->last_name ?? '';
                            $workerName = trim($firstName . ' ' . $lastName);

                            if ($workerName === '') {
                                $workerName = $worker?->full_name ?? $worker?->name ?? 'Unknown Worker';
                            }

                            $workerPosition = $worker?->position ?? $worker?->job_title ?? $worker?->trade ?? 'Worker';
                            $projectName = $project?->project_name ?? $project?->name ?? 'No Project';

                            $initials = strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
                            if ($initials === '') {
                                $initials = strtoupper(mb_substr($workerName, 0, 2));
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
                        @endphp

                        <tr>
                            <td>
                                <div class="worker-info">
                                    <div class="worker-avatar">{{ $initials ?: 'W' }}</div>
                                    <div>
                                        <div class="worker-name">{{ $workerName }}</div>
                                        <div class="worker-secondary">{{ $workerPosition }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="project-name">{{ $projectName }}</span></td>
                            <td>{{ $log->log_date ? \Carbon\Carbon::parse($log->log_date)->format('M d, Y') : '—' }}</td>
                            <td>{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '—' }}</td>
                            <td>{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '—' }}</td>
                            <td><span class="attendance-status {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                @if($log->biometric_matched)
                                    <span class="biometric-state biometric-verified">
                                        <i class="bi bi-check-circle"></i> Verified
                                    </span>
                                @else
                                    <span class="biometric-state biometric-unverified">
                                        <i class="bi bi-dash-circle"></i> Not Verified
                                    </span>
                                @endif
                            </td>
                            <td><span class="recorded-by">{{ $recordedByName }}</span></td>
                            <td><span class="remarks-cell" title="{{ $log->remarks ?? 'No remarks' }}">{{ $log->remarks ?? '—' }}</span></td>
                            <td>
                                <button type="button" class="row-actions" aria-label="Attendance record options" title="More options">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="attendance-empty">
                                    <i class="bi bi-calendar-x"></i>
                                    <strong>No attendance records found</strong>
                                    <span>Attendance records will appear here once they are submitted.</span>
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
        // --- Live Local UI Table Search ---
        const attendanceSearch = document.getElementById('attendanceSearch');
        const attendanceRows = document.querySelectorAll('#attendanceTable tbody tr');

        attendanceSearch?.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            attendanceRows.forEach(function (row) {
                const searchableText = row.textContent.toLowerCase();
                row.style.display = searchableText.includes(keyword) ? '' : 'none';
            });
        });

        // --- Spatie WebAuthn Passkeys Interfacing ---
        const registerBtn = document.getElementById('btnRegisterPasskey');
        const authBtn = document.getElementById('btnAuthenticatePasskey');

        if (typeof window.browserSupportsWebAuthn === 'function' && !window.browserSupportsWebAuthn()) {
            registerBtn.disabled = true;
            authBtn.disabled = true;
            console.warn('Biometrics disabled. Secure layer environment context requires an HTTPS (Ngrok) connection.');
        }

        registerBtn?.addEventListener('click', async () => {
            try {
                const response = await fetch('/passkeys/register/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const options = await response.json();
                const credential = await window.startRegistration(options);

                const submitResponse = await fetch('/passkeys/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(credential)
                });

                if (submitResponse.ok) {
                    alert('Success: Fingerprint passkey linked successfully to this account!');
                } else {
                    alert('Registration payload rejected by server database criteria validation.');
                }
            } catch (error) {
                console.error(error);
                alert('Biometric hardware operation timed out or was terminated.');
            }
        });

        authBtn?.addEventListener('click', async () => {
            try {
                const response = await fetch('/passkeys/login/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const options = await response.json();
                const credential = await window.startAuthentication(options);

                const submitResponse = await fetch('/passkeys/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(credential)
                });

                if (submitResponse.ok) {
                    alert('Passkey Identification Success: Biometric token verification complete!');
                }
            } catch (error) {
                console.error(error);
                alert('Verification process terminated.');
            }
        });
    });
</script>
@endpush