@extends('layouts.supervisor')

@section('title', 'Supervisor Dashboard - Field Operations Command')
@section('page_title', 'Supervisor Dashboard')


@push('styles')
<style>
    /* Supervisor dashboard mobile polish */
    .supervisor-dashboard-page {
        width: 100%;
    }

    .supervisor-project-selector {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .supervisor-project-selector-head {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
    }

    .supervisor-project-selector-head i {
        font-size: 0.85rem;
        color: #166534;
    }

    .supervisor-select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        min-width: 220px;
    }

    .supervisor-select-icon {
        position: absolute;
        left: 12px;
        color: #166534;
        pointer-events: none;
        z-index: 2;
        font-size: 0.9rem;
    }

    .supervisor-project-select {
        width: 100%;
        padding: 0.6rem 2.2rem 0.6rem 2.4rem !important;
        font-size: 0.88rem !important;
        font-weight: 600 !important;
        color: #166534 !important;
        background-color: #F1F5F9 !important;
        border: 1px solid rgba(22, 101, 52, 0.15) !important;
        border-radius: 12px !important;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23166534' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-size: 10px 12px !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
    }

    .supervisor-project-select:focus {
        border-color: #166534 !important;
        box-shadow: 0 0 0 0.2rem rgba(22, 101, 52, 0.12) !important;
        background-color: #ffffff !important;
        outline: none;
    }

    .supervisor-project-select:hover:not(:disabled) {
        border-color: rgba(22, 101, 52, 0.35) !important;
        background-color: #ffffff !important;
    }

    .supervisor-project-select:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #f1f5f9 !important;
    }

    @media (max-width: 820px) {
        .supervisor-dashboard-page {
            gap: 14px !important;
        }

        .supervisor-dashboard-page .page-card,
        .supervisor-dashboard-page .section-card {
            border: 1px solid #e4ece6 !important;
            border-radius: 20px !important;
            background: #ffffff !important;
            box-shadow: 0 12px 28px rgba(15, 32, 21, 0.055) !important;
            overflow: hidden !important;
        }

        .supervisor-dashboard-page .page-hero {
            padding: 16px !important;
            border-radius: 20px !important;
            background: linear-gradient(180deg, #ffffff 0%, #f8fcf8 100%) !important;
        }

        .supervisor-dashboard-page .eyebrow {
            font-size: 10px !important;
            letter-spacing: 0.14em !important;
            color: #60708a !important;
            margin-bottom: 4px !important;
        }

        .supervisor-dashboard-page .page-title {
            font-size: 20px !important;
            line-height: 1.15 !important;
            letter-spacing: -0.02em !important;
            margin-bottom: 6px !important;
        }

        .supervisor-dashboard-page .page-subtitle {
            max-width: 270px !important;
            font-size: 12.5px !important;
            line-height: 1.45 !important;
        }

        .supervisor-dashboard-page #projectSelectorForm,
        .supervisor-dashboard-page #projectSelectorForm select {
            width: 100% !important;
            max-width: 100% !important;
            font-size: 16px !important;
        }

        .supervisor-dashboard-page .page-hero > .d-flex:first-child > .d-flex:last-child {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr auto !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .supervisor-dashboard-page .page-hero .row.g-3 {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
            margin-top: 12px !important;
        }

        .supervisor-dashboard-page .page-hero .row.g-3 > [class*="col-"] {
            width: auto !important;
            max-width: none !important;
            padding: 0 !important;
        }

        .supervisor-dashboard-page .stat-card {
            min-height: 118px !important;
            padding: 13px !important;
            border: 1px solid #e5eee7 !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            box-shadow: 0 10px 22px rgba(15, 32, 21, 0.045) !important;
        }

        .supervisor-dashboard-page .stat-title {
            font-size: 10px !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            letter-spacing: 0.07em !important;
            text-transform: uppercase !important;
            color: #64748b !important;
            margin-bottom: 8px !important;
        }

        .supervisor-dashboard-page .stat-value {
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            color: #101827 !important;
            font-size: 18px !important;
            line-height: 1.12 !important;
            font-weight: 800 !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        .supervisor-dashboard-page .stat-meta,
        .supervisor-dashboard-page .stat-card .small {
            font-size: 11px !important;
            line-height: 1.35 !important;
        }

        .supervisor-dashboard-page .page-hero > .d-flex.flex-wrap {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 10px !important;
            margin-top: 12px !important;
        }

        .supervisor-dashboard-page .page-hero > .d-flex.flex-wrap .btn {
            width: 100% !important;
            min-height: 42px !important;
            justify-content: center !important;
            border-radius: 14px !important;
            padding: 10px 8px !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            white-space: normal !important;
        }

        .supervisor-dashboard-page .page-hero > .d-flex.flex-wrap .btn:first-child {
            grid-column: 1 / -1 !important;
        }

        .supervisor-dashboard-page .dashboard-card-grid {
            gap: 14px !important;
        }

        .supervisor-dashboard-page .section-card-body {
            padding: 16px !important;
        }

        .supervisor-dashboard-page .section-card h5 {
            font-size: 17px !important;
            line-height: 1.25 !important;
            margin-bottom: 4px !important;
        }

        .supervisor-dashboard-page .dashboard-key-value {
            min-height: 58px !important;
            padding: 11px !important;
            border-radius: 15px !important;
            background: #fbfefb !important;
            border: 1px solid #e9f1eb !important;
        }

        .supervisor-dashboard-page .dashboard-icon {
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            border-radius: 12px !important;
        }

        .supervisor-dashboard-page .dashboard-summary-highlight {
            margin-top: 14px !important;
            padding: 14px !important;
            border: 1px solid #dfece3 !important;
            border-radius: 18px !important;
            background: linear-gradient(135deg, #f5fff7 0%, #ffffff 100%) !important;
        }

        .supervisor-dashboard-page .dashboard-kpi {
            font-size: 28px !important;
            line-height: 1 !important;
        }

        .supervisor-dashboard-page .dashboard-info-stack {
            border-top: 1px solid #edf2ee !important;
            margin-top: 12px !important;
        }

        .supervisor-dashboard-page .dashboard-inline-item {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto !important;
            gap: 12px !important;
            align-items: center !important;
            padding: 11px 0 !important;
            border-bottom: 1px solid #eef3ef !important;
        }

        .supervisor-dashboard-page .dashboard-inline-item span:last-child {
            text-align: right !important;
            max-width: 145px !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        .supervisor-dashboard-page .dashboard-action-btn {
            width: 100% !important;
            min-height: 42px !important;
            border-radius: 14px !important;
            font-weight: 800 !important;
            margin-top: 12px !important;
        }

        .supervisor-dashboard-page .activity-item,
        .supervisor-dashboard-page .dashboard-surface {
            border-radius: 16px !important;
            border: 1px solid #e7efe9 !important;
            background: #ffffff !important;
        }
    }

    @media (max-width: 390px) {
        .supervisor-dashboard-page .page-hero .row.g-3 {
            grid-template-columns: 1fr !important;
        }

        .supervisor-dashboard-page .page-hero > .d-flex.flex-wrap {
            grid-template-columns: 1fr !important;
        }

        .supervisor-dashboard-page .page-hero > .d-flex.flex-wrap .btn {
            grid-column: auto !important;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column gap-3 supervisor-dashboard-page">
    <section class="page-card">
        <div class="page-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                        <div>
                            <div class="eyebrow">Good Morning, {{ $user->name ?? 'Supervisor' }}</div>
                            <h1 class="page-title mb-2">Today&apos;s Site Priorities</h1>
                            <p class="page-subtitle mb-0">Operational summary for your assigned construction project.</p>
                        </div>
						<div class="d-flex align-items-center gap-3 flex-wrap">
							<div class="supervisor-project-selector">
								<div class="supervisor-project-selector-head">
									<i class="bi bi-building"></i>
									<span>Active Project</span>
								</div>
								<div class="supervisor-project-selector-field">
									<form id="projectSelectorForm" method="GET" action="{{ route('supervisor.dashboard') }}" class="mb-0 w-100">
										<label for="dashboardProjectId" class="visually-hidden">Project</label>
										<div class="supervisor-select-wrapper">
											<i class="bi bi-building supervisor-select-icon"></i>
											<select id="dashboardProjectId" name="project_id" class="form-select supervisor-project-select" onchange="this.form.submit()" {{ $assignedProjects->isEmpty() ? 'disabled' : '' }}>
												@if($assignedProjects->isEmpty())
													<option value="" selected>No assigned projects</option>
												@else
													@foreach($assignedProjects as $project)
														<option value="{{ $project->project_id }}" {{ optional($primaryProject)->project_id == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
													@endforeach
												@endif
											</select>
										</div>
									</form>
								</div>
							</div>
							<span class="badge rounded-pill badge-soft text-muted fw-semibold">
								<i class="bi bi-calendar3 me-2"></i>{{ now()->format('M d, Y') }}
							</span>
						</div>
                    </div>
            <div class="row g-3 mt-1">
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        @php
                            $attendanceTotal = max(1, $projectWorkersCount ?: 1);
                            $attendancePercent = $projectWorkersCount > 0 ? round(($attendancePresentCount / $attendanceTotal) * 100) : 0;
                            $attendanceStatus = $attendancePresentCount >= $projectWorkersCount ? 'All present' : ($attendancePresentCount > 0 ? 'In progress' : 'Pending');
                        @endphp
                        <div class="stat-title">Workforce Status</div>
                        <div class="stat-value">{{ $attendancePresentCount }}/{{ $projectWorkersCount }}</div>
                        <div class="stat-meta">Present workers</div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis">Biometric {{ $attendancePercent }}%</span>
                        </div>
                        <div class="small text-muted mt-2">{{ $attendancePresentCount >= $projectWorkersCount ? 'All assigned workers are present.' : ($attendancePresentCount > 0 ? 'Attendance is being tracked today.' : 'Attendance is still pending for today.') }}</div>
                    </div>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-title">Current Site Phase</div>
                        <div class="stat-value">{{ $primaryPhase->phase_name ?? 'No active phase' }}</div>
                        <div class="stat-meta">Live site focus</div>
                    </div>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-title">Today&apos;s Site Tasks</div>
                        <div class="stat-value">{{ $pendingTasksCount }}</div>
                        <div class="stat-meta">Pending follow-up items</div>
                    </div>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="stat-card">
                        <div class="stat-title">Upcoming Deadline</div>
                        <div class="stat-value">{{ $upcomingMilestone ? \Carbon\Carbon::parse($upcomingMilestone->start_date)->format('M d') : 'No date' }}</div>
                        <div class="stat-meta">{{ $upcomingMilestone->milestone_name ?? 'No upcoming milestone' }}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                <a href="{{ route('supervisor.attendance') }}" class="btn btn-primary-soft px-4 py-2">
                    <i class="bi bi-person-check me-2"></i>Record Attendance
                </a>
                <a href="{{ route('supervisor.reports', ['project_id' => optional($primaryProject)->project_id]) }}" class="btn btn-outline-soft px-4 py-2">
                    <i class="bi bi-file-earmark-text me-2"></i>Submit Report
                </a>
                <a href="{{ route('supervisor.timeline', ['project_id' => optional($primaryProject)->project_id]) }}" class="btn btn-outline-soft px-4 py-2">
                    <i class="bi bi-calendar3 me-2"></i>Project Milestones
                </a>
            </div>
        </div>
    </section>

    @php
        $projectPhase = $primaryPhase?->phase_name ?? optional($primaryProject->phases->first())->phase_name ?? 'Mobilization';
        $projectTargetDate = optional($primaryProject->target_end_date);
        $daysRemaining = $projectTargetDate ? max(0, $projectTargetDate->diffInDays(now(), false)) : null;
        $projectClient = $primaryProject?->client?->company_name ?? optional($primaryProject?->client?->user)->name ?? 'Not specified';
        $projectLocation = $primaryProject?->location ?? $primaryProject?->project_location ?? 'Not specified';
        $projectEngineer = $primaryProject?->engineer?->name ?? 'Not specified';
        $activeWorkforce = $primaryProject ? max(0, $primaryProject->workers()->count()) : 0;
        $phaseProgress = $primaryPhase ? (float) ($primaryPhase->completion_percentage ?? 0) : (float) ($projectProgress ?? 0);
        $phaseStatus = $primaryPhase?->status ?? 'not_started';
        $phaseStatusLabel = match ($phaseStatus) {
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'delayed' => 'Delayed',
            default => 'Planned'
        };
        $phaseBadgeClass = match ($phaseStatus) {
            'in_progress' => 'bg-success-subtle text-success-emphasis',
            'completed' => 'bg-primary-subtle text-primary-emphasis',
            'delayed' => 'bg-warning-subtle text-warning-emphasis',
            default => 'bg-secondary-subtle text-secondary-emphasis'
        };
        $phaseStart = optional($primaryPhase?->planned_start_date)->format('M d, Y') ?? optional($primaryProject->start_date)->format('M d, Y') ?? 'Pending';
        $phaseEnd = optional($primaryPhase?->planned_end_date)->format('M d, Y') ?? optional($primaryProject->target_end_date)->format('M d, Y') ?? 'Pending';
        $phaseTimeline = $primaryProject ? $primaryProject->phases()->orderBy('phase_order')->orderBy('planned_start_date')->get() : collect();
        $completedPhaseCount = $phaseTimeline->where('status', 'completed')->count();
        $currentActivePhase = $phaseTimeline->firstWhere('status', 'in_progress')?->phase_name ?? $primaryPhase?->phase_name ?? 'Pending';
        $nextUpcomingPhase = $phaseTimeline->first(function ($phaseItem) {
            return $phaseItem->status !== 'completed' && $phaseItem->status !== 'in_progress';
        })?->phase_name ?? 'Pending';
        $upcomingMilestones = \App\Models\Milestone::whereHas('phase', function ($query) use ($primaryProject) {
            $query->where('project_id', $primaryProject->project_id);
        })->where('is_completed', false)->orderBy('start_date')->take(3)->get();
        $activityItems = collect();
        foreach ($pendingReports->take(4) as $report) {
            $activityItems->push([
                'title' => $report->project->project_name ?? 'Project report',
                'meta' => $report->phase->phase_name ?? 'Progress update',
                'time' => $report->created_at->format('h:i A'),
                'icon' => 'bi-file-earmark-text',
                'iconClass' => 'bg-success-subtle text-success',
                'timestamp' => $report->created_at->timestamp,
            ]);
        }
        if ($attendanceRecords->isNotEmpty()) {
            $activityItems->push([
                'title' => 'Attendance captured',
                'meta' => $attendancePresentCount . ' workers checked in today',
                'time' => optional($attendanceRecords->first()->created_at)->format('h:i A') ?? 'Today',
                'icon' => 'bi-person-check',
                'iconClass' => 'bg-primary-subtle text-primary',
                'timestamp' => optional($attendanceRecords->first()->created_at)->timestamp ?? now()->timestamp,
            ]);
        }
        $activityItems = $activityItems->sortByDesc('timestamp')->take(5);
    @endphp

    <div class="row g-3 dashboard-card-grid dashboard-row-equal">
        <div class="col-12 col-lg-6">
            <section class="section-card">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1">Assigned Project</h5>
                            <p class="text-muted mb-0 small">Current assignment and delivery outlook</p>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis">{{ ucfirst($primaryProject->status ?? 'Active') }}</span>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <div class="dashboard-key-value">
                                <span class="dashboard-icon"><i class="bi bi-building"></i></span>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold text-muted text-uppercase">Project</div>
                                    <div class="fw-semibold small">{{ $primaryProject->project_name ?? 'No project assigned' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="dashboard-key-value">
                                <span class="dashboard-icon"><i class="bi bi-person-badge"></i></span>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold text-muted text-uppercase">Client</div>
                                    <div class="fw-semibold small">{{ $projectClient }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="dashboard-key-value">
                                <span class="dashboard-icon"><i class="bi bi-geo-alt"></i></span>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold text-muted text-uppercase">Location</div>
                                    <div class="fw-semibold small">{{ $projectLocation }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="dashboard-key-value">
                                <span class="dashboard-icon"><i class="bi bi-calendar-event"></i></span>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold text-muted text-uppercase">Completion Date</div>
                                    <div class="fw-semibold small">{{ $projectTargetDate ? $projectTargetDate->format('M d, Y') : 'Pending' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-muted text-uppercase">Overall Project Progress</span>
                            <span class="fw-semibold text-success">{{ $projectProgress }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $projectProgress }}%"></div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="dashboard-summary-pill">
                            <i class="bi bi-flag"></i>
                            <span>Current Phase: {{ $projectPhase }}</span>
                        </span>
                        <span class="dashboard-summary-pill">
                            <i class="bi bi-hourglass-split"></i>
                            <span>{{ $daysRemaining !== null ? $daysRemaining . ' days remaining' : 'Pending' }}</span>
                        </span>
                    </div>

                    <a href="{{ route('supervisor.timeline') }}" class="btn btn-outline-soft btn-sm mt-2 dashboard-action-btn">View Project Details</a>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-6">
            <section class="section-card">
                <div class="section-card-body">
                    <div>
                        <h5 class="fw-bold mb-1">Current Site Summary</h5>
                        <p class="text-muted mb-0 small">Immediate phase monitoring and next actions</p>
                    </div>

                    <div class="dashboard-summary-highlight">
                        <div>
                            <div class="small fw-semibold text-muted text-uppercase">Current Phase Progress</div>
                            <div class="dashboard-kpi">{{ round($phaseProgress, 1) }}%</div>
                        </div>
                        <div class="progress-track mt-2">
                            <div class="progress-fill" style="width: {{ round($phaseProgress, 1) }}%"></div>
                        </div>
                    </div>

                    <div class="dashboard-info-stack mt-2">
                        <div class="dashboard-inline-item">
                            <span class="small fw-semibold text-muted text-uppercase">Current Phase</span>
                            <span class="fw-semibold small">{{ $primaryPhase?->phase_name ?? 'No active phase' }}</span>
                        </div>
                        <div class="dashboard-inline-item">
                            <span class="small fw-semibold text-muted text-uppercase">Completed Phases</span>
                            <span class="fw-semibold small">{{ $completedPhaseCount }}/{{ $phaseTimeline->count() }}</span>
                        </div>
                        <div class="dashboard-inline-item">
                            <span class="small fw-semibold text-muted text-uppercase">Next Phase</span>
                            <span class="fw-semibold small">{{ $nextUpcomingPhase }}</span>
                        </div>
                        <div class="dashboard-inline-item">
                            <span class="small fw-semibold text-muted text-uppercase">Estimated Finish Date</span>
                            <span class="fw-semibold small">{{ $phaseEnd }}</span>
                        </div>
                    </div>

                    <a href="{{ route('supervisor.phases') }}" class="btn btn-outline-soft btn-sm mt-2 dashboard-action-btn">View Construction Phases</a>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3 dashboard-card-grid dashboard-row-equal">
        <div class="col-12 col-lg-4">
            <section class="section-card">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1">Daily Report</h5>
                            <p class="text-muted mb-0 small">Keep reporting current</p>
                        </div>
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis">{{ $pendingReports->isNotEmpty() ? 'Pending' : 'Ready' }}</span>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="dashboard-inline-item">
                            <span class="small fw-semibold text-muted text-uppercase">Submission Status</span>
                            <span class="fw-semibold small">{{ $pendingReports->isNotEmpty() ? 'Pending' : 'Ready' }}</span>
                        </div>
                        <div class="dashboard-inline-item">
                            <span class="small fw-semibold text-muted text-uppercase">Deadline</span>
                            <span class="fw-semibold small">5:00 PM</span>
                        </div>
                        <div class="dashboard-inline-item">
                            <span class="small fw-semibold text-muted text-uppercase">Last Submitted</span>
                            <span class="fw-semibold small">{{ $pendingReports->first()?->created_at?->format('M d') ?? now()->format('M d') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('supervisor.reports') }}" class="btn btn-primary-soft btn-sm dashboard-action-btn">Submit Report</a>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-4">
            <section class="section-card">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1">Upcoming Milestones</h5>
                            <p class="text-muted mb-0 small">Near-term checkpoints</p>
                        </div>
                        <span class="badge rounded-pill badge-soft">{{ $upcomingMilestone ? 'Watch' : 'Clear' }}</span>
                    </div>
                    @if($upcomingMilestones->isEmpty())
                        <div class="dashboard-empty-state">
                            <div class="dashboard-empty-icon"><i class="bi bi-check2-circle"></i></div>
                            <div class="small text-muted">No milestones due this week. Everything is on schedule.</div>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach($upcomingMilestones->take(3) as $milestone)
                                <div class="dashboard-surface p-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="dashboard-icon"><i class="bi bi-flag-fill"></i></span>
                                        <div>
                                            <div class="fw-semibold small">{{ $milestone->milestone_name }}</div>
                                            <div class="small text-muted mt-1">{{ optional($milestone->start_date)->format('M d, Y') ?? 'Pending' }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-4">
            <section class="section-card">
                <div class="section-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1">Recent Activity</h5>
                            <p class="text-muted mb-0 small">Latest site updates</p>
                        </div>
                    </div>
                    @if($activityItems->isEmpty())
                        <div class="dashboard-empty-state">
                            <div class="dashboard-empty-icon"><i class="bi bi-bell"></i></div>
                            <div class="small text-muted">No recent site activity.</div>
                        </div>
                    @else
                        <div class="activity-timeline">
                            @foreach($activityItems->take(5) as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon {{ $activity['iconClass'] }}">
                                        <i class="bi {{ $activity['icon'] }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div class="fw-semibold small">{{ $activity['title'] }}</div>
                                            <span class="small text-muted">{{ $activity['time'] }}</span>
                                        </div>
                                        <div class="small text-muted">{{ $activity['meta'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>
@endsection