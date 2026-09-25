<?php $__env->startSection('title', 'Worker Attendance'); ?>
<?php $__env->startSection('page_title', 'Worker Attendance'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/admin-attendance.css')); ?>?v=<?php echo e(time()); ?>">

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

            .attendance-hero {
                align-items: stretch !important;
                gap: 14px !important;
                padding: 18px !important;
            }

            .attendance-date-chip {
                align-self: flex-start !important;
            }

            .attendance-filter-card form.mt-3 {
                align-items: stretch !important;
                flex-direction: column !important;
                gap: 8px !important;
            }

            .attendance-filter-card form.mt-3 .btn.btn-primary {
                width: 100% !important;
            }

            .attendance-filter-card form.mt-3 .text-muted {
                margin-left: 0 !important;
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $logs = $logs ?? collect();
    $scheduleRules = $scheduleRules ?? collect();

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
?>

<div class="attendance-page">
    <div class="attendance-print-header" aria-hidden="true">
        <h1>Attendance Report</h1>
        <p>Date: <?php echo e($filters['date'] ? \Carbon\Carbon::parse($filters['date'])->format('F d, Y') : 'All Dates'); ?></p>
        <?php if(!empty($filters['project_id']) || !empty($filters['status']) || !empty($filters['biometric']) || !empty($filters['search'])): ?>
            <p class="attendance-print-filters">
                Filters:
                <?php echo e($filters['project_id'] ? 'Project selected' : ''); ?>

                <?php echo e($filters['status'] ? 'Status: ' . ucwords(str_replace('_', ' ', $filters['status'])) : ''); ?>

                <?php echo e($filters['biometric'] ? 'Biometric: ' . ucfirst($filters['biometric']) : ''); ?>

                <?php echo e($filters['search'] ? 'Search: ' . $filters['search'] : ''); ?>

            </p>
        <?php endif; ?>
    </div>

    <section class="attendance-hero" aria-labelledby="attendanceOverviewTitle">
        <div class="attendance-title-wrap">
            <span class="attendance-eyebrow"><i class="bi bi-shield-check"></i> Workforce operations</span>
            <h1 class="attendance-title" id="attendanceOverviewTitle">Attendance overview</h1>
            <p class="attendance-subtitle">Review worker presence, exceptions, and verified time records from one operational view.</p>
        </div>
        <div class="attendance-date-chip">
            <i class="bi bi-calendar3"></i>
            <span><?php echo e($filters['date'] ? \Carbon\Carbon::parse($filters['date'])->format('M d, Y') : 'All dates'); ?></span>
        </div>
    </section>

    <?php if(session('success')): ?>
        <div class="alert alert-success" role="alert">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>


    <section class="attendance-filter-card">
        <form method="GET" action="<?php echo e(route('admin.attendance')); ?>" class="attendance-filter-form">

            <div class="filter-group">
                <label for="date">
                    <i class="bi bi-calendar3"></i>
                    Date
                </label>

                <input type="date"
                       id="date"
                       name="date"
                       value="<?php echo e($filters['date'] ?? ''); ?>">
            </div>

            <div class="filter-group">
                <label for="project_id">
                    <i class="bi bi-building"></i>
                    Project
                </label>

                <select id="project_id" name="project_id">
                    <option value="">All Projects</option>

                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($project->project_id); ?>"
                            <?php if((string) ($filters['project_id'] ?? '') === (string) $project->project_id): echo 'selected'; endif; ?>>
                            <?php echo e($project->project_name ?? $project->name ?? 'Unnamed Project'); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="status">
                    <i class="bi bi-funnel"></i>
                    Status
                </label>

                <select id="status" name="status">
                    <option value="">All Status</option>
                    <option value="present" <?php if(($filters['status'] ?? '') === 'present'): echo 'selected'; endif; ?>>Present</option>
                    <option value="late" <?php if(($filters['status'] ?? '') === 'late'): echo 'selected'; endif; ?>>Late / Half Day</option>
                    <option value="absent" <?php if(($filters['status'] ?? '') === 'absent'): echo 'selected'; endif; ?>>Absent</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="biometric">
                    <i class="bi bi-fingerprint"></i>
                    Biometric
                </label>

                <select id="biometric" name="biometric">
                    <option value="">All</option>
                    <option value="verified" <?php if(($filters['biometric'] ?? '') === 'verified'): echo 'selected'; endif; ?>>Verified</option>
                    <option value="unverified" <?php if(($filters['biometric'] ?? '') === 'unverified'): echo 'selected'; endif; ?>>Not Verified</option>
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
                       value="<?php echo e($filters['search'] ?? ''); ?>"
                       placeholder="Search worker, project, status, remarks...">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter-primary">
                    <i class="bi bi-funnel"></i>
                    Apply
                </button>

                <a href="<?php echo e(route('admin.attendance')); ?>" class="btn-filter-secondary">
                    <i class="bi bi-x-circle"></i>
                    Clear
                </a>
            </div>
        </form>

        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-primary attendance-preview-trigger" data-bs-toggle="modal" data-bs-target="#attendancePreviewModal">
                <i class="bi bi-eye"></i>
                Preview Attendance Report
            </button>
            <span class="text-muted small">Review records before sending.</span>
        </div>
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
                <?php echo e(number_format($stats['total'] ?? 0)); ?>

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
                <?php echo e(number_format($stats['present'] ?? 0)); ?>

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
                <?php echo e(number_format($stats['late'] ?? 0)); ?>

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
                <?php echo e(number_format($stats['absent'] ?? 0)); ?>

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
                <?php echo e(number_format($stats['missing_timeout'] ?? 0)); ?>

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
                <?php echo e(number_format($stats['break_exceeded'] ?? 0)); ?>

            </div>
        </button>
    </div>

    <section class="attendance-schedule-panel card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Attendance Schedule Rules</h5>
                    <p class="text-muted mb-0">Set default start, end, and break times by role.</p>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.attendance.schedules.store')); ?>" class="mb-4">
                <?php echo csrf_field(); ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Role</label>
                        <select name="role" class="form-select">
                            <option value="worker">Worker</option>
                            <option value="engineer">Engineer</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="client">Client</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Time In</label>
                        <input type="time" name="start_time" class="form-control" value="07:00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Time Out</label>
                        <input type="time" name="end_time" class="form-control" value="17:00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Break Out</label>
                        <input type="time" name="break_start_time" class="form-control" value="12:00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Break In</label>
                        <input type="time" name="break_end_time" class="form-control" value="13:00">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Save Rule</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Role</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Break Out</th>
                            <th>Break In</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $scheduleRules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <form id="schedule-form-<?php echo e($rule->id); ?>" method="POST" action="<?php echo e(route('admin.attendance.schedules.update', $rule->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                </form>
                                <td>
                                    <input form="schedule-form-<?php echo e($rule->id); ?>" type="text" name="role" class="form-control form-control-sm" value="<?php echo e($rule->role); ?>">
                                </td>
                                <td>
                                    <input form="schedule-form-<?php echo e($rule->id); ?>" type="time" name="start_time" class="form-control form-control-sm" value="<?php echo e(substr((string) $rule->start_time, 0, 5)); ?>">
                                </td>
                                <td>
                                    <input form="schedule-form-<?php echo e($rule->id); ?>" type="time" name="end_time" class="form-control form-control-sm" value="<?php echo e(substr((string) $rule->end_time, 0, 5)); ?>">
                                </td>
                                <td>
                                    <input form="schedule-form-<?php echo e($rule->id); ?>" type="time" name="break_start_time" class="form-control form-control-sm" value="<?php echo e($rule->break_start_time ? substr((string) $rule->break_start_time, 0, 5) : ''); ?>">
                                </td>
                                <td>
                                    <input form="schedule-form-<?php echo e($rule->id); ?>" type="time" name="break_end_time" class="form-control form-control-sm" value="<?php echo e($rule->break_end_time ? substr((string) $rule->break_end_time, 0, 5) : ''); ?>">
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="submit" form="schedule-form-<?php echo e($rule->id); ?>" class="btn btn-sm btn-outline-primary">Update</button>
                                        <form method="POST" action="<?php echo e(route('admin.attendance.schedules.destroy', $rule->id)); ?>" onsubmit="return confirm('Delete this schedule rule?');" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No attendance rules configured yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="attendance-issues-panel attendance-no-print">
        <div class="issues-header">
            <div>
                <h2>Attendance Issues / Exceptions</h2>
                <p>Records that may need admin review.</p>
            </div>

            <span class="issues-count">
                <?php echo e($issues->count()); ?> flagged
            </span>
        </div>

        <?php if($issues->count() > 0): ?>
            <div class="issues-list">
                <?php $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
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
                    ?>

                    <div class="issue-item">
                        <div class="issue-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <div class="issue-body">
                            <strong><?php echo e($issueWorkerName); ?></strong>
                            <span><?php echo e($issueReason); ?> • <?php echo e($issueProjectName); ?></span>
                        </div>

                        <div class="issue-date">
                            <?php echo e($issue->log_date ? \Carbon\Carbon::parse($issue->log_date)->format('M d') : '—'); ?>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="issues-empty">
                <i class="bi bi-check2-circle"></i>
                No attendance issues found for the selected filter.
            </div>
        <?php endif; ?>
    </section>

    <section class="attendance-entry-panel attendance-no-print">
        <section class="attendance-filter-card" style="margin-bottom: 1rem;">
            <div class="issues-header">
                <div>
                    <h2>Admin Attendance Entry</h2>
                    <p>Set exact time in, break, and time out. OT is calculated from the worker schedule.</p>
                </div>
            </div>
            <form method="POST" action="<?php echo e(route('admin.attendance.store')); ?>" style="display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;align-items:end;">
                <?php echo csrf_field(); ?>
                <label>Worker<select name="worker_id" required style="width:100%;"><option value="">Select worker</option><?php $__currentLoopData = $workers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $worker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($worker->worker_id); ?>"><?php echo e($worker->full_name ?? trim(($worker->first_name ?? '').' '.($worker->last_name ?? ''))); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label>Date<input type="date" name="log_date" value="<?php echo e($filters['date'] ?? now()->toDateString()); ?>" required style="width:100%;"></label>
                <label>Time in<input type="time" name="time_in" style="width:100%;"></label>
                <label>Break out<input type="time" name="break_out" style="width:100%;"></label>
                <label>Break in<input type="time" name="break_in" style="width:100%;"></label>
                <label>Time out<input type="time" name="time_out" style="width:100%;"></label>
                <input type="hidden" name="status" value="present">
                <input type="text" name="remarks" placeholder="Remarks" style="grid-column:span 5;">
                <button type="submit" class="btn-filter-primary"><i class="bi bi-plus-circle"></i> Add attendance</button>
            </form>
            <details style="margin-top:12px;">
                <summary style="cursor:pointer;font-weight:600;">Configure worker schedule</summary>
                <form method="POST" action="#" id="workerScheduleForm" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-top:10px;align-items:end;">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <label>Worker
                        <select id="scheduleWorker" required style="width:100%;">
                            <?php $__currentLoopData = $workers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $worker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $workerRole = data_get($worker, 'role', 'worker');
                                    $workerStart = data_get($worker, 'schedule_start', '07:00');
                                    $workerEnd = data_get($worker, 'schedule_end', $workerRole === 'staff' ? '15:00' : '17:00');
                                    $workerBreak = data_get($worker, 'break_minutes', 60);
                                    $workerName = $worker->full_name ?? trim(($worker->first_name ?? '').' '.($worker->last_name ?? ''));
                                ?>
                                <option value="<?php echo e($worker->worker_id); ?>"
                                        data-role="<?php echo e($workerRole); ?>"
                                        data-start="<?php echo e($workerStart); ?>"
                                        data-end="<?php echo e($workerEnd); ?>"
                                        data-break="<?php echo e($workerBreak); ?>">
                                    <?php echo e($workerName); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                    <label>Role<select name="role" id="scheduleRole" style="width:100%;"><option value="staff">Staff</option><option value="worker">Worker</option></select></label>
                    <label>Start<input type="time" name="schedule_start" id="scheduleStart" required style="width:100%;"></label>
                    <label>End<input type="time" name="schedule_end" id="scheduleEnd" required style="width:100%;"></label>
                    <label>Break minutes<input type="number" name="break_minutes" id="scheduleBreak" min="0" max="480" required style="width:100%;"></label>
                    <button type="submit" class="btn-filter-primary">Save schedule</button>
                </form>
            </details>
        </section>
    </section>

    <section class="attendance-panel">
        <div class="attendance-toolbar">
            <div class="attendance-toolbar-title">
                <span class="attendance-toolbar-icon">
                    <i class="bi bi-card-checklist"></i>
                </span>

                <div>
                    <h2>Attendance Records</h2>
                    <p>Tap a summary card above to show the matching workers. Updates automatically every 30 seconds.</p>
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
                        <th>OT</th>
                        <th>Status</th>
                        <th>Biometric</th>
                        <th>Recorded By</th>
                        <th>Remarks</th>
                        <th>Admin</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
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
                            $overtimeLabel = $log->overtime_label ?? '—';

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
                        ?>

                        <tr
                            data-attendance-row="1"
                            data-status="<?php echo e($status); ?>"
                            data-missing-timeout="<?php echo e($missingTimeout ? '1' : '0'); ?>"
                            data-break-exceeded="<?php echo e($breakExceeded ? '1' : '0'); ?>"
                        >
                            <td data-label="Worker">
                                <div class="worker-info">
                                    <div class="worker-avatar">
                                        <?php echo e($initials ?: 'W'); ?>

                                    </div>

                                    <div>
                                        <div class="worker-name">
                                            <?php echo e($workerName); ?>

                                        </div>

                                        <div class="worker-secondary">
                                            <?php echo e($workerPosition); ?>

                                        </div>
                                        <details style="margin-top:6px;">
                                            <summary style="cursor:pointer;font-size:.75rem;">Edit times</summary>
                                            <form method="POST" action="<?php echo e(route('admin.attendance.update', $log)); ?>" style="display:grid;gap:4px;margin-top:6px;min-width:180px;">
                                                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                                <input type="hidden" name="worker_id" value="<?php echo e($log->worker_id); ?>">
                                                <input type="hidden" name="log_date" value="<?php echo e($log->log_date ? \Carbon\Carbon::parse($log->log_date)->format('Y-m-d') : ''); ?>">
                                                <input type="time" name="time_in" value="<?php echo e($log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('H:i') : ''); ?>">
                                                <input type="time" name="break_out" value="<?php echo e($log->break_out ? \Carbon\Carbon::parse($log->break_out)->format('H:i') : ''); ?>">
                                                <input type="time" name="break_in" value="<?php echo e($log->break_in ? \Carbon\Carbon::parse($log->break_in)->format('H:i') : ''); ?>">
                                                <input type="time" name="time_out" value="<?php echo e($log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('H:i') : ''); ?>">
                                                <input type="hidden" name="status" value="<?php echo e($status); ?>">
                                                <input type="text" name="remarks" value="<?php echo e($log->remarks); ?>" placeholder="Remarks">
                                                <button type="submit" class="btn-filter-primary">Save</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.attendance.destroy', $log)); ?>" onsubmit="return confirm('Delete this attendance record?');" style="margin-top:4px;"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit" class="btn-filter-secondary">Delete</button></form>
                                        </details>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Project">
                                <span class="project-name">
                                    <?php echo e($projectName); ?>

                                </span>
                            </td>

                            <td data-label="Date">
                                <?php echo e($log->log_date ? \Carbon\Carbon::parse($log->log_date)->format('M d, Y') : '—'); ?>

                            </td>

                            <td data-label="Time In">
                                <?php echo e($log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '—'); ?>

                            </td>

                            <td data-label="Break Out">
                                <?php echo e($log->break_out ? \Carbon\Carbon::parse($log->break_out)->format('h:i A') : '—'); ?>

                            </td>

                            <td data-label="Break In">
                                <?php echo e($log->break_in ? \Carbon\Carbon::parse($log->break_in)->format('h:i A') : '—'); ?>

                            </td>

                            <td data-label="Time Out">
                                <?php echo e($log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '—'); ?>

                            </td>

                            <td data-label="OT">
                                <?php if(($log->overtime_minutes ?? 0) > 0): ?>
                                    <span class="attendance-status status-default">
                                        <?php echo e($overtimeLabel); ?>

                                    </span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>

                            <td data-label="Status">
                                <span class="attendance-status <?php echo e($statusClass); ?>">
                                    <?php echo e($statusLabel); ?>

                                </span>
                            </td>

                            <td data-label="Biometric">
                                <?php if($log->biometric_matched): ?>
                                    <span class="biometric-state biometric-verified">
                                        <i class="bi bi-check-circle"></i>
                                        Verified
                                    </span>
                                <?php else: ?>
                                    <span class="biometric-state biometric-unverified">
                                        <i class="bi bi-dash-circle"></i>
                                        Not Verified
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Recorded By">
                                <span class="recorded-by">
                                    <?php echo e($recordedByName); ?>

                                </span>
                            </td>

                            <td data-label="Remarks">
                                <span class="remarks-cell" title="<?php echo e($log->remarks ?? 'No remarks'); ?>">
                                    <?php echo e($log->remarks ?? '—'); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="12">
                                <div class="attendance-empty">
                                    <i class="bi bi-calendar-x"></i>
                                    <strong>No attendance records found</strong>
                                    <span>Attendance records will appear here once supervisors submit worker logs.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<div class="modal fade attendance-preview-modal" id="attendancePreviewModal" tabindex="-1" aria-labelledby="attendancePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 px-4 py-3 attendance-preview-header">
                <div>
                    <div class="attendance-preview-kicker">Daily overview</div>
                    <h5 class="modal-title mb-0" id="attendancePreviewModalLabel">Attendance report preview</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="attendance-preview-summary px-4 pt-4 pb-3">
                    <div class="attendance-preview-stat">
                        <span>Total</span>
                        <strong><?php echo e(number_format($stats['total'] ?? 0)); ?></strong>
                    </div>
                    <div class="attendance-preview-stat success">
                        <span>Present</span>
                        <strong><?php echo e(number_format($stats['present'] ?? 0)); ?></strong>
                    </div>
                    <div class="attendance-preview-stat warning">
                        <span>Late / Half Day</span>
                        <strong><?php echo e(number_format($stats['late'] ?? 0)); ?></strong>
                    </div>
                    <div class="attendance-preview-stat danger">
                        <span>Absent</span>
                        <strong><?php echo e(number_format($stats['absent'] ?? 0)); ?></strong>
                    </div>
                </div>

                <div class="attendance-preview-meta px-4 pb-3">
                    <div class="attendance-preview-badge">
                        <i class="bi bi-calendar3"></i>
                        <?php echo e($filters['date'] ? \Carbon\Carbon::parse($filters['date'])->format('F d, Y') : 'All dates'); ?>

                    </div>
                    <?php if(!empty($filters['project_id']) || !empty($filters['status']) || !empty($filters['biometric']) || !empty($filters['search'])): ?>
                        <div class="attendance-preview-filter-list">
                            <?php if(!empty($filters['project_id'])): ?><span>Project filter</span><?php endif; ?>
                            <?php if(!empty($filters['status'])): ?><span><?php echo e(ucfirst(str_replace('_', ' ', $filters['status']))); ?></span><?php endif; ?>
                            <?php if(!empty($filters['biometric'])): ?><span><?php echo e(ucfirst($filters['biometric'])); ?></span><?php endif; ?>
                            <?php if(!empty($filters['search'])): ?><span>Search: <?php echo e($filters['search']); ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="attendance-preview-table-wrap px-4 pb-4">
                    <table class="attendance-preview-table">
                        <thead>
                            <tr>
                                <th>Worker</th>
                                <th>Project</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                                <th>OT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $worker = $log->display_worker ?? $log->worker ?? $log->deployment?->worker;
                                    $project = $log->display_project ?? $log->deployment?->project;
                                    $workerName = trim(($worker?->first_name ?? '') . ' ' . ($worker?->last_name ?? '')) ?: ($worker?->full_name ?? $worker?->name ?? 'Unknown Worker');
                                    $projectName = $project?->project_name ?? $project?->name ?? 'No Project';
                                    $status = strtolower($log->status ?? 'unknown');
                                    $statusLabel = match ($status) {
                                        'half_day' => 'Half Day',
                                        default => ucwords(str_replace('_', ' ', $status)),
                                    };
                                    $statusClass = match ($status) {
                                        'present' => 'status-present',
                                        'absent' => 'status-absent',
                                        'late' => 'status-late',
                                        'half_day', 'half day' => 'status-half-day',
                                        default => 'status-default',
                                    };
                                    $overtimeLabel = $log->overtime_label ?? (($log->overtime_minutes ?? 0) > 0 ? 'OT ' . $log->overtime_minutes . 'm' : '—');
                                ?>
                                <tr>
                                    <td>
                                        <div class="attendance-preview-worker">
                                            <span class="attendance-preview-avatar"><?php echo e(strtoupper(substr(str_replace(' ', '', $workerName), 0, 2)) ?: 'W'); ?></span>
                                            <span><?php echo e($workerName); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo e($projectName); ?></td>
                                    <td><?php echo e($log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '—'); ?></td>
                                    <td><?php echo e($log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '—'); ?></td>
                                    <td><span class="attendance-status <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span></td>
                                    <td><?php echo e($overtimeLabel); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="attendance-empty attendance-preview-empty">
                                            <i class="bi bi-calendar-x"></i>
                                            <strong>No attendance records found</strong>
                                            <span>There are no attendance records for the current filters.</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-0 px-4 py-3 attendance-preview-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                <?php if(($logs->count() ?? 0) > 0): ?>
                    <form method="POST" action="<?php echo e(route('admin.attendance.send-report')); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="date" value="<?php echo e($filters['date'] ?? now()->toDateString()); ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-envelope"></i>
                            Send report to my email
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('workerScheduleForm');
        const worker = document.getElementById('scheduleWorker');
        if (!form || !worker) return;
        const sync = () => {
            const option = worker.options[worker.selectedIndex];
            document.getElementById('scheduleRole').value = option.dataset.role || 'worker';
            document.getElementById('scheduleStart').value = (option.dataset.start || '07:00').slice(0, 5);
            document.getElementById('scheduleEnd').value = (option.dataset.end || '17:00').slice(0, 5);
            document.getElementById('scheduleBreak').value = option.dataset.break || '60';
            form.action = `${<?php echo json_encode(url('/admin/attendance/workers'), 15, 512) ?>}/${option.value}/schedule`;
        };
        worker.addEventListener('change', sync);
        sync();
    })();
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let activeStatFilter = 'all';

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

        applyTableFilters();

        const attendanceRefreshInterval = 30000;
        let hiddenAt = null;

        function attendancePageIsBusy() {
            return Boolean(
                document.activeElement?.matches('input, select, textarea') ||
                document.querySelector('.attendance-table details[open], .attendance-entry-panel details[open]')
            );
        }

        function refreshAttendanceRecords() {
            if (document.hidden || attendancePageIsBusy()) {
                return;
            }

            window.location.reload();
        }

        window.setInterval(refreshAttendanceRecords, attendanceRefreshInterval);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                hiddenAt = Date.now();
                return;
            }

            if (hiddenAt && Date.now() - hiddenAt >= attendanceRefreshInterval) {
                refreshAttendanceRecords();
            }

            hiddenAt = null;
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\resources\views\admin\attendance.blade.php ENDPATH**/ ?>