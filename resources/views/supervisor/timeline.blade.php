@extends('layouts.supervisor')

@section('title', 'Project Timeline - Supervisor View')

@section('content')
<div class="container-fluid p-0">
    @if(isset($projectsWithStats) && count($projectsWithStats) > 0)
        <div class="d-flex flex-column gap-4">
            @foreach($projectsWithStats as $index => $project)
                @php
                    $projectId = data_get($project, 'id');
                    $projectName = data_get($project, 'name', 'Project');
                    $projectProgress = (float) data_get($project, 'progress', 0);
                    $projectTargetEnd = data_get($project, 'targetEndDate');
                    $phases = data_get($project, 'phases', []);
                    
                    // Filter or find the currently running phase dynamically for the top hero component
                    $currentPhase = collect($phases)->first(function($p) {
                        return in_array(strtolower(data_get($p, 'status')), ['ongoing', 'in_progress', 'current']);
                    }) ?? collect($phases)->first();

                    $projectLocation = data_get($project, 'location', 'Location pending');
                    $projectSupervisors = collect(data_get($project, 'supervisors', []))->pluck('name')->filter()->implode(', ');
                    $projectSupervisors = $projectSupervisors ?: 'Unassigned';
                    
                    $currentPhaseName = data_get($currentPhase, 'name', 'No Active Phase');
                    $currentPhaseProgress = (float) data_get($currentPhase, 'progress', 0);
                    $currentPhaseStart = data_get($currentPhase, 'start');
                    $currentPhaseEnd = data_get($currentPhase, 'end');
                @endphp

                <div class="project-timeline-wrapper" id="project-panel-{{ $projectId }}" style="{{ $selectedProjectId == $projectId ? '' : 'display: none;' }}">
                    
                    <div class="dashboard-panel mb-2">
                        <div class="row align-items-center mb-3">
                            <div class="col">
                                <span class="panel-eyebrow">Current Active Phase</span>
                                <h1 class="panel-main-title mt-1">{{ $currentPhaseName }} <span class="text-muted font-weight-normal">(Current)</span></h1>
                                <div class="panel-subtext">{{ $projectSupervisors }} • {{ $projectLocation }}</div>
                            </div>
                            <div class="col-auto d-flex gap-3 align-items-center flex-wrap">
                                
                                <div class="project-selector-dropdown-wrapper">
                                    <div class="project-selector-icon" aria-hidden="true">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <select class="form-select project-theme-select" onchange="window.location.href='{{ route('supervisor.timeline') }}?project_id=' + this.value">
                                        @foreach($projectsWithStats as $dropdownProject)
                                            <option value="{{ data_get($dropdownProject, 'id') }}" {{ data_get($dropdownProject, 'id') == $selectedProjectId ? 'selected' : '' }}>
                                                {{ data_get($dropdownProject, 'name', 'Project') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <span class="badge-status-pill in-progress">In Progress</span>
                                <span class="badge-status-pill completed-count">
                                    <i class="bi bi-check2-square me-1"></i> 
                                    {{ collect($phases)->where('status', 'completed')->count() }}/{{ count($phases) }} Completed
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="progress-label-text">Phase Completion</span>
                                <span class="progress-value-text">{{ number_format($currentPhaseProgress, 0) }}%</span>
                            </div>
                            <div class="progress custom-progress-track">
                                <div class="progress-bar custom-progress-fill" role="progressbar" style="width: {{ $currentPhaseProgress }}%;" aria-valuenow="{{ $currentPhaseProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="meta-metric-card">
                                    <div class="meta-card-label"><i class="bi bi-calendar-plus me-1"></i> Planned Start</div>
                                    <div class="meta-card-value">{{ $currentPhaseStart ? \Carbon\Carbon::parse($currentPhaseStart)->format('M d, Y') : 'Pending' }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="meta-metric-card">
                                    <div class="meta-card-label"><i class="bi bi-calendar-minus me-1"></i> Planned End</div>
                                    <div class="meta-card-value">{{ $currentPhaseEnd ? \Carbon\Carbon::parse($currentPhaseEnd)->format('M d, Y') : 'Pending' }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="meta-metric-card">
                                    <div class="meta-card-label"><i class="bi bi-person-check me-1"></i> Actual Start</div>
                                    <div class="meta-card-value">{{ $currentPhaseStart ? \Carbon\Carbon::parse($currentPhaseStart)->format('M d, Y') : 'Pending' }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="meta-metric-card">
                                    <div class="meta-card-label"><i class="bi bi-hourglass-split me-1"></i> Estimated Remaining</div>
                                    <div class="meta-card-value">0 days</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 align-items-center pt-2 border-top">
                            <div class="col-12 col-md-7">
                                <div class="schedule-insight-box">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="insight-icon-shell"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                        <div>
                                            <div class="insight-title">Schedule Insight: <span class="text-amber-deep fw-bold">Attention Required</span></div>
                                            <div class="insight-desc">Overdue milestone needs review</div>
                                        </div>
                                        <span class="badge-alert-pill delay-bg ms-auto">Delayed</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-5 d-flex gap-2 justify-content-md-end">
                                <a href="{{ route('supervisor.phases') }}" class="btn btn-timeline-primary"><i class="bi bi-kanban me-2"></i> View Phase Timeline</a>
                                <a href="{{ route('supervisor.reports') }}" class="btn btn-timeline-outline"><i class="bi bi-file-earmark-text me-2"></i> Submit Daily Report</a>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4 col-xl-2.4 custom-col-five">
                            <div class="kpi-panel-card">
                                <span class="kpi-label">Overall Progress</span>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <h3 class="kpi-value mb-0">{{ number_format($projectProgress, 1) }}%</h3>
                                    <div class="radial-progress-dummy" style="--value: {{ $projectProgress }}"></div>
                                </div>
                                <span class="kpi-subtext text-muted mt-2 d-block">Project-wide delivery health</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2.4 custom-col-five">
                            <div class="kpi-panel-card">
                                <span class="kpi-label">Completed Phases</span>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="kpi-icon-success"><i class="bi bi-check-circle-fill"></i></div>
                                    <h3 class="kpi-value mb-0">{{ collect($phases)->where('status', 'completed')->count() }}</h3>
                                </div>
                                <span class="kpi-subtext text-muted mt-2 d-block">Delivered in sequence</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-xl-2.4 custom-col-five">
                            <div class="kpi-panel-card">
                                <span class="kpi-label">Remaining Phases</span>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="kpi-icon-warning"><i class="bi bi-hourglass-top"></i></div>
                                    <h3 class="kpi-value mb-0">{{ collect($phases)->whereIn('status', ['upcoming', 'planned', 'not_started', 'delayed'])->count() }}</h3>
                                </div>
                                <span class="kpi-subtext text-muted mt-2 d-block">Still active or pending</span>
                            </div>
                        </div>
                        @php
                            $activeMilestoneCount = collect(data_get($project, 'activeMilestones', []))->count();
                            $delayedMilestoneCount = collect(data_get($project, 'activeMilestones', []))->where('is_delayed', true)->count();
                            $scheduleHealthLabel = $delayedMilestoneCount > 0 ? 'Delayed' : 'On Track';
                            $scheduleHealthClass = $delayedMilestoneCount > 0 ? 'text-amber-deep' : 'text-success';
                        @endphp
                        <div class="col-6 col-md-6 col-xl-2.4 custom-col-five">
                            <div class="kpi-panel-card">
                                <span class="kpi-label">Active Milestones</span>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="kpi-icon-info"><i class="bi bi-flag-fill"></i></div>
                                    <h3 class="kpi-value mb-0">{{ $activeMilestoneCount }}</h3>
                                </div>
                                <span class="kpi-subtext text-muted mt-2 d-block">Driving today's work</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-2.4 custom-col-five">
                            <div class="kpi-panel-card">
                                <span class="kpi-label">Schedule Health</span>
                                <div class="mt-2">
                                    <h3 class="kpi-value {{ $scheduleHealthClass }} mb-0">{{ $scheduleHealthLabel }}</h3>
                                </div>
                                <span class="kpi-subtext text-muted mt-2 d-block">Based on phase milestones</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 align-items-stretch">
                        <div class="col-12 col-lg-6 d-flex">
                            <div class="dashboard-panel w-100 d-flex flex-column construction-progress-panel">
                                <div class="construction-progress-header mb-3">
                                    <div>
                                        <span class="construction-progress-eyebrow">Phase Roadmap</span>
                                        <h3 class="panel-section-title mb-0">Construction Progress Overview</h3>
                                    </div>
                                    <span class="construction-progress-count">{{ count($phases) }} phases</span>
                                </div>
                                <p class="construction-progress-subtitle">Monitor each construction phase, milestone marker, status, and completion progress in one compact view.</p>

                                <div class="d-flex flex-column flex-grow-1 justify-content-around py-2 dynamic-timeline-stepper">
                                    @foreach($phases as $phase)
                                        @php
                                            $pStatus = strtolower(data_get($phase, 'status', 'upcoming'));
                                            $pProgress = (float) data_get($phase, 'progress', 0);
                                            $phaseMilestones = collect(data_get($phase, 'milestones', []))->filter(function ($milestone) {
                                                return !empty(data_get($milestone, 'name'));
                                            });
                                            
                                            $borderClass = match($pStatus) {
                                                'completed' => 'timeline-phase-completed',
                                                'ongoing', 'in_progress', 'current' => 'timeline-phase-current',
                                                default => 'timeline-phase-upcoming'
                                            };

                                            $statusLabel = match($pStatus) {
                                                'completed' => '<span class="badge-status-pill compl-bg">Completed</span>',
                                                'ongoing', 'in_progress', 'current' => '<span class="badge-status-pill active-bg">In Progress</span>',
                                                default => '<span class="badge-status-pill plan-bg">Planned</span>'
                                            };
                                        @endphp

                                        <div class="timeline-phase-card-item {{ $borderClass }}">
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h4 class="stepper-phase-name mb-1">{{ data_get($phase, 'name') }}</h4>
                                                        <div class="stepper-phase-dates">
                                                            <i class="bi bi-calendar3 me-1"></i>
                                                            {{ data_get($phase, 'start') ? \Carbon\Carbon::parse(data_get($phase, 'start'))->format('M d, Y') : 'TBD' }} - 
                                                            {{ data_get($phase, 'end') ? \Carbon\Carbon::parse(data_get($phase, 'end'))->format('M d, Y') : 'TBD' }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <span class="stepper-percentage fw-bold text-dark">{{ number_format($pProgress, 0) }}%</span>
                                                        {!! $statusLabel !!}
                                                    </div>
                                                </div>
                                                <div class="timeline-progress-shell">
                                                    @if($phaseMilestones->isNotEmpty())
                                                        <div class="milestone-track">
                                                            @foreach($phaseMilestones as $milestone)
                                                                @php
                                                                    $milestoneName = data_get($milestone, 'name', 'Milestone');
                                                                    $phaseStartValue = data_get($phase, 'start');
                                                                    $phaseEndValue = data_get($phase, 'end');
                                                                    $milestoneDateValue = data_get($milestone, 'start_date');
                                                                    $milestoneLabel = $milestoneDateValue ? \Carbon\Carbon::parse($milestoneDateValue)->format('M d, Y') : 'TBD';
                                                                    $phaseStartDate = $phaseStartValue ? \Carbon\Carbon::parse($phaseStartValue) : null;
                                                                    $phaseEndDate = $phaseEndValue ? \Carbon\Carbon::parse($phaseEndValue) : null;
                                                                    $milestoneDate = $milestoneDateValue ? \Carbon\Carbon::parse($milestoneDateValue) : null;
                                                                    $markerPercent = 0;
                                                                    if ($phaseStartDate && $phaseEndDate && $milestoneDate && $phaseEndDate->gt($phaseStartDate)) {
                                                                        $totalDays = max(1, $phaseStartDate->diffInDays($phaseEndDate, false));
                                                                        $elapsedDays = $phaseStartDate->diffInDays($milestoneDate, false);
                                                                        $markerPercent = max(0, min(100, round(($elapsedDays / $totalDays) * 100, 1)));
                                                                    }
                                                                    $milestoneActualDateValue = data_get($milestone, 'end_date');
                                                                    $milestoneActualLabel = $milestoneActualDateValue ? \Carbon\Carbon::parse($milestoneActualDateValue)->format('M d, Y') : null;
                                                                    $milestoneFlagClass = data_get($milestone, 'is_completed') ? 'phase-milestone-marker completed' : (data_get($milestone, 'is_delayed') ? 'phase-milestone-marker delayed' : 'phase-milestone-marker');
                                                                @endphp
                                                                <div class="milestone-marker-wrapper" style="left: {{ $markerPercent }}%;">
                                                                    <button type="button" class="{{ $milestoneFlagClass }}" aria-label="Milestone: {{ $milestoneName }}">
                                                                        <i class="bi bi-flag-fill"></i>
                                                                    </button>
                                                                    <div class="milestone-info-card" role="tooltip">
                                                                        <div class="milestone-info-title">{{ $milestoneName }}</div>
                                                                        <div class="milestone-info-row">
                                                                            <span>Start</span>
                                                                            <strong>{{ $milestoneLabel }}</strong>
                                                                        </div>
                                                                        <div class="milestone-info-row">
                                                                            <span>End</span>
                                                                            <strong>{{ $milestoneActualLabel ?? 'TBD' }}</strong>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <div class="progress" style="height: 6px; background-color: #f1f5f9; border-radius: 999px;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $pProgress }}%; background: linear-gradient(90deg, #4DA078, #82DB72);" aria-valuenow="{{ $pProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6 d-flex flex-column gap-4">
                            <div class="dashboard-panel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="panel-section-title mb-0">Active Milestones</h3>

                                </div>
                                
                                @php
                                $activeMilestones = collect(data_get($project, 'activeMilestones', []))
                                    ->sortBy('start_date')
                                    ->take(2);
                            @endphp
                            <div class="row g-3">
                                @if($activeMilestones->isEmpty())
                                    <div class="col-12">
                                        <div class="dashboard-empty-state p-4 rounded-3 border border-dashed text-center">
                                            <div class="mb-2 text-muted">
                                                <i class="bi bi-info-circle"></i>
                                            </div>
                                            <div class="fw-semibold">No upcoming milestones available.</div>
                                            <div class="small text-muted">Milestones will appear here once they are scheduled for this project.</div>
                                        </div>
                                    </div>
                                @else
                                    @foreach($activeMilestones as $milestone)
                                        @php
                                            $plannedDate = data_get($milestone, 'start_date');
                                            $daysRemaining = $plannedDate ? \Carbon\Carbon::parse($plannedDate)->diffInDays(now(), false) : null;
                                            $statusText = data_get($milestone, 'is_delayed') ? 'Delayed' : (data_get($milestone, 'is_completed') ? 'Completed' : 'Upcoming');
                                            $statusClass = data_get($milestone, 'is_delayed') ? 'border-warning-left' : 'border-primary-left';
                                            $progressValue = data_get($milestone, 'progress', 0) ?: 0;
                                        @endphp
                                        <div class="col-12 col-sm-6">
                                            <div class="milestone-sub-card {{ $statusClass }}">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <i class="bi bi-flag-fill text-success"></i>
                                                    <div class="milestone-card-title">{{ data_get($milestone, 'name') }}</div>
                                                </div>
                                                <div class="milestone-target-text mb-3">{{ data_get($milestone, 'phase_name') }} • {{ $plannedDate ? \Carbon\Carbon::parse($plannedDate)->format('M d, Y') : 'TBD' }}</div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Days Remaining</span>
                                                    <span class="fw-bold text-dark small">{{ $daysRemaining !== null ? max(0, $daysRemaining) : 'TBD' }}</span>
                                                </div>
                                                <div class="progress mt-1" style="height: 5px; background-color: #f1f5f9; border-radius: 999px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $progressValue }}%;" aria-valuenow="{{ $progressValue }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="badge-status-pill active-bg d-inline-block mt-3">{{ $statusText }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            </div>

                            <div class="row g-3 flex-grow-1">
                                <div class="col-12 col-md-6">
                                    <div class="dashboard-panel h-100">
                                        <h3 class="panel-section-title mb-3">Project Schedule</h3>
                                        
                                        <div class="schedule-box-segment mb-3">
                                            <div class="schedule-segment-header">Planned Dates</div>
                                            <div class="d-flex flex-column gap-2 mt-2">
                                                <div>
                                                    <span class="dot-indicator green-bg"></span>
                                                    <span class="date-label text-muted">Start Date:</span>
                                                    <span class="date-val fw-bold text-dark ms-1">{{ $currentPhaseStart ? \Carbon\Carbon::parse($currentPhaseStart)->format('M d, Y') : 'Pending' }}</span>
                                                </div>
                                                <div>
                                                    <span class="dot-indicator green-bg"></span>
                                                    <span class="date-label text-muted">End Date:</span>
                                                    <span class="date-val fw-bold text-dark ms-1">{{ $currentPhaseEnd ? \Carbon\Carbon::parse($currentPhaseEnd)->format('M d, Y') : 'Pending' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="schedule-box-segment">
                                            <div class="schedule-segment-header">Actual Dates</div>
                                            <div class="d-flex flex-column gap-2 mt-2">
                                                <div>
                                                    <span class="dot-indicator blue-bg"></span>
                                                    <span class="date-label text-muted">Actual Start:</span>
                                                    <span class="date-val fw-bold text-dark ms-1">{{ $currentPhaseStart ? \Carbon\Carbon::parse($currentPhaseStart)->format('M d, Y') : 'Pending' }}</span>
                                                </div>
                                                <div>
                                                    <span class="dot-indicator blue-bg"></span>
                                                    <span class="date-label text-muted">Actual End:</span>
                                                    <span class="date-val fw-bold text-muted ms-1">—</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="dashboard-panel h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <h3 class="panel-section-title mb-1">Upcoming Milestone</h3>
                                            <span class="text-muted small d-block mb-3">Next deadline target tracking</span>
                                            
                                            <div class="upcoming-milestone-panel-strip p-3 border rounded-3 mb-3 d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h4 class="fw-bold text-dark small mb-1">{{ $projectName }} - Milestone 2</h4>
                                                    <span class="text-muted small">In progress • Apr 25, 2026</span>
                                                </div>
                                                <i class="bi bi-chevron-right text-muted"></i>
                                            </div>
                                        </div>

                                        <div class="alert-countdown-banner p-3 rounded-3 mt-auto">
                                            <div class="d-flex align-items-center gap-2 text-amber-deep fw-bold">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                                <span>25 days left</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <div class="timeline-empty-state-card text-center py-5 shadow-sm bg-white">
            <div class="empty-state-icon-canvas mb-3 text-muted">
                <i class="bi bi-folder2-open display-4"></i>
            </div>
            <h3 class="brand-dark-slate h5 mb-2 fw-bold">No Construction Timelines Registered</h3>
            <p class="text-muted mx-auto px-3" style="max-width: 360px;">There are currently no active construction contract structures assigned under your monitoring profile.</p>
        </div>
    @endif
</div>

<style>
    /* --- SYSTEM UI STYLES --- */
    
    .dashboard-panel {
        background-color: #ffffff;
        border: 1px solid rgba(22, 101, 52, 0.12);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .project-selector-dropdown-wrapper {
        min-width: 220px;
        position: relative;
        display: flex;
        align-items: center;
    }

    .project-selector-icon {
        position: absolute;
        left: 10px;
        color: #166534;
        pointer-events: none;
        z-index: 2;
    }
    
    .project-theme-select {
        background-color: #F1F5F9 !important;
        border: 1px solid rgba(22, 101, 52, 0.15) !important;
        color: #166534 !important;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.45rem 2rem 0.45rem 2.2rem;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23166534' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        background-size: 10px 12px !important;
    }

    .project-theme-select:focus {
        border-color: #166534 !important;
        box-shadow: 0 0 0 0.25rem rgba(22, 101, 52, 0.15) !important;
        background-color: #ffffff !important;
    }

    .panel-eyebrow {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #6b7280;
        letter-spacing: 0.06em;
        display: block;
    }

    .panel-main-title {
        color: #2a4028;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 1.65rem;
    }

    .panel-subtext {
        font-size: 0.88rem;
        color: #6b7280;
    }

    /* BADGES AND STATUS PILLS */
    .badge-status-pill {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 6px 14px;
        border-radius: 30px;
        letter-spacing: 0.02em;
    }
    .badge-status-pill.in-progress {
        background-color: #e8efe0;
        color: #365233;
    }
    .badge-status-pill.completed-count {
        background-color: #f3f4f6;
        color: #373737;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .badge-status-pill.compl-bg { background-color: #d1fae5; color: #065f46; text-transform: none; font-size: 0.78rem;}
    .badge-status-pill.active-bg { background-color: #DCFCE7; color: #15803D; text-transform: none; font-size: 0.78rem;}
    .badge-status-pill.plan-bg { background-color: #f3f4f6; color: #4b5563; text-transform: none; font-size: 0.78rem;}

    /* CUSTOM PROGRESS LINE STYLE PACKS */
    .progress-label-text { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #373737; letter-spacing: 0.04em; }
    .progress-value-text { font-size: 1.15rem; font-weight: 800; color: #2a4028; font-family: 'Syne', sans-serif;}
    .custom-progress-track { height: 10px; border-radius: 999px; background-color: #E2E8F0; overflow: hidden; }
    .custom-progress-fill { background: linear-gradient(90deg, #365233, #8ea886); border-radius: 999px; }

    /* CORE GRID META METRIC BOX SYSTEM */
    .meta-metric-card {
        background: #fdfdfd;
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-radius: 12px;
        padding: 14px;
    }
    .meta-card-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.02em; }
    .meta-card-value { font-size: 0.95rem; font-weight: 700; color: #373737; margin-top: 4px; }

    /* AMBER / WARM ORANGE SCHEDULE INSIGHT BOX */
    .schedule-insight-box {
        background-color: #fffbeb;
        border: 1px solid #fef3c7;
        border-radius: 12px;
        padding: 12px 16px;
    }
    .insight-icon-shell { color: #d97706; font-size: 1.2rem; }
    .insight-title { font-size: 0.88rem; color: #451a03; font-weight: 500; }
    .insight-desc { font-size: 0.8rem; color: #78350f; }
    .text-amber-deep { color: #d97706 !important; }
    .badge-alert-pill.delay-bg { background-color: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; padding: 4px 12px; border-radius: 30px;}

    /* BUTTON ACTION SET SCHEMES */
    .btn-timeline-primary { background-color: #2a4028; color: #ffffff; border-radius: 10px; font-weight: 600; font-size: 0.88rem; padding: 10px 18px; border: none; transition: all 0.2s;}
    .btn-timeline-primary:hover { background-color: #365233; color: #fff; }
    .btn-timeline-outline { background-color: transparent; color: #2a4028; border: 1px solid rgba(42, 64, 40, 0.2); border-radius: 10px; font-weight: 600; font-size: 0.88rem; padding: 10px 18px; transition: all 0.2s;}
    .btn-timeline-outline:hover { background-color: rgba(42, 64, 40, 0.05); color: #2a4028; }

    /* HIGH LEVEL STATUS BOARDS PILLS */
    .custom-col-five { flex: 0 0 100%; max-width: 100%; }
    @media (min-width: 576px) { .custom-col-five { flex: 0 0 50%; max-width: 50%; } }
    @media (min-width: 768px) { .custom-col-five { flex: 0 0 33.333%; max-width: 33.333%; } }
    @media (min-width: 1200px) { .custom-col-five { flex: 0 0 20%; max-width: 20%; } }

    .kpi-panel-card {
        background-color: #ffffff;
        border: 1px solid rgba(9, 96, 86, 0.12);
        border-radius: 14px;
        padding: 16px;
        height: 100%;
        box-shadow: 0 2px 12px rgba(0,0,0,0.01);
    }
    .kpi-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.04em; }
    .kpi-value { font-size: 1.45rem; font-weight: 700; color: #166534; font-family: 'Syne', sans-serif; }
    .kpi-subtext { font-size: 0.78rem; line-height: 1.3; }
    
    .kpi-icon-success { color: #16A34A; font-size: 1.25rem; }
    .kpi-icon-warning { color: #f59e0b; font-size: 1.25rem; }
    .kpi-icon-info { color: #166534; font-size: 1.25rem; }

    .radial-progress-dummy {
        width: 34px; height: 34px; border-radius: 50%;
        background: conic-gradient(#4DA078 calc(var(--value) * 1%), #ebf2ee 0);
        position: relative;
    }
    .radial-progress-dummy::after {
        content: ''; position: absolute; inset: 4px; background: white; border-radius: 50%;
    }

    /* STEPPER PACKS & VERTICAL RECTANGLE ALIGNMENTS */
    .panel-section-title { font-size: 1rem; font-weight: 700; color: #373737; font-family: 'Syne', sans-serif; }
    .dynamic-timeline-stepper { position: relative; width: 100%; }
    
    /* Phase Rectangle Cards Stylings */
    .timeline-phase-card-item {
        background-color: #fafafa;
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        width: 100%;
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: visible;
        z-index: 1;
    }
    .timeline-phase-card-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    
    /* Phase specific border accents */
    .timeline-phase-completed { border-left: 5px solid #16A34A; }
    .timeline-phase-current { border-left: 5px solid #166534; background-color: #f8faf9; }
    .timeline-phase-upcoming { border-left: 5px solid #cbd5e1; }

    .stepper-phase-name { font-size: 0.95rem; font-weight: 700; color: #373737; }
    .stepper-phase-dates { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
    .stepper-percentage { font-size: 0.95rem; }

    .timeline-progress-shell {
        position: relative;
        padding-top: 1.35rem;
        margin-top: 0.15rem;
        overflow: visible;
        z-index: 1;
    }
    .milestone-track {
        position: absolute;
        inset: 0 0 auto 0;
        height: 32px;
        z-index: 3;
        pointer-events: none;
        overflow: visible;
    }
    .milestone-marker-wrapper {
        position: absolute;
        top: 0;
        transform: translateX(-50%);
        display: inline-flex;
        align-items: flex-start;
        justify-content: center;
        pointer-events: auto;
        z-index: 20;
    }
    .phase-milestone-marker {
        position: relative;
        transform: translateY(-2px);
        min-width: 28px;
        height: 24px;
        padding: 0 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #166534;
        border: 1px solid rgba(22, 101, 52, 0.16);
        box-shadow: 0 10px 20px rgba(34, 197, 94, 0.16);
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .phase-milestone-marker:hover,
    .phase-milestone-marker:focus-visible {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(34, 197, 94, 0.22);
    }
    .phase-milestone-marker i {
        font-size: 0.74rem;
        line-height: 1;
    }
    .phase-milestone-marker.completed {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #15803d;
        border-color: rgba(21, 128, 61, 0.16);
    }
    .phase-milestone-marker.delayed {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #b91c1c;
        border-color: rgba(185, 28, 28, 0.16);
    }
    .phase-milestone-marker::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid #d1fae5;
        opacity: 0.95;
    }
    .phase-milestone-marker.completed::after {
        border-top-color: #bbf7d0;
    }
    .phase-milestone-marker.delayed::after {
        border-top-color: #fecaca;
    }
    .milestone-info-card {
        position: absolute;
        bottom: calc(100% + 10px);
        left: 50%;
        transform: translateX(-50%) translateY(-2px);
        min-width: 210px;
        padding: 0.7rem 0.8rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fcf8 100%);
        border: 1px solid rgba(22, 101, 52, 0.16);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: all 0.2s ease;
        z-index: 9999;
        white-space: normal;
    }
    .milestone-marker-wrapper:hover .milestone-info-card,
    .milestone-marker-wrapper:focus-within .milestone-info-card {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }
    .milestone-info-card::before {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 10px;
        height: 10px;
        background: #ffffff;
        border-left: 1px solid rgba(22, 101, 52, 0.16);
        border-bottom: 1px solid rgba(22, 101, 52, 0.16);
        rotate: 45deg;
    }
    .milestone-info-title {
        font-size: 0.82rem;
        font-weight: 800;
        color: #166534;
        margin-bottom: 0.4rem;
    }
    .milestone-info-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.74rem;
        color: #64748b;
        margin-top: 0.2rem;
    }
    .milestone-info-row strong {
        color: #0f172a;
        font-weight: 700;
        text-align: right;
    }


    /* MILESTONE CARDS */
    .milestone-sub-card {
        background: #ffffff;
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-radius: 12px;
        padding: 16px;
        height: 100%;
    }
    .border-success-left { border-left: 4px solid #16A34A; }
    .border-primary-left { border-left: 4px solid #166534; }
    .milestone-card-title { font-size: 0.88rem; font-weight: 700; color: #373737; line-height: 1.3; }
    .milestone-target-text { font-size: 0.78rem; color: #6b7280; }

    /* SCHEDULE BLOCKS */
    .schedule-box-segment {
        background: #fdfdfd;
        border: 1px solid rgba(9, 96, 86, 0.06);
        border-radius: 10px;
        padding: 12px 14px;
    }
    .schedule-segment-header { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid rgba(0,0,0,0.03); padding-bottom: 4px; }
    .dot-indicator { width: 6px; height: 6px; border-radius: 50%; display: inline-block; vertical-align: middle; }
    .dot-indicator.green-bg { background-color: #16A34A; }
    .dot-indicator.blue-bg { background-color: #166534; }
    .date-label { font-size: 0.82rem; }
    .date-val { font-size: 0.82rem; }

    .upcoming-milestone-panel-strip { background-color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.06) !important; transition: background 0.2s; cursor: pointer;}
    .upcoming-milestone-panel-strip:hover { background-color: #f9fafb; }
    
    /* AMBER BANNERS */
    .alert-countdown-banner { background-color: #fff7ed; border: 1px dashed #ffedd5; }
    .alert-countdown-banner span { font-size: 0.88rem; }

    /* ======================================================================
       MOBILE POLISH: Construction Progress Overview
       Makes the phase progress cards cleaner, less cramped, and more balanced
       inside the Capacitor / phone view without changing the desktop layout.
       ====================================================================== */

    .construction-progress-panel {
        overflow: visible;
    }

    .construction-progress-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .construction-progress-eyebrow {
        display: block;
        margin-bottom: 0.25rem;
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .construction-progress-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: max-content;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: #f0f8f2;
        border: 1px solid rgba(22, 101, 52, 0.14);
        color: #166534;
        font-size: 0.74rem;
        font-weight: 800;
        line-height: 1;
    }

    .construction-progress-subtitle {
        margin: -0.35rem 0 1rem;
        color: #64748b;
        font-size: 0.82rem;
        line-height: 1.45;
    }

    @media (max-width: 768px) {
        .construction-progress-panel {
            padding: 18px 14px !important;
            border-radius: 20px !important;
            background:
                radial-gradient(circle at top right, rgba(22, 101, 52, 0.05), transparent 38%),
                #ffffff !important;
        }

        .construction-progress-header {
            align-items: center;
            margin-bottom: 0.45rem !important;
        }

        .construction-progress-subtitle {
            margin-bottom: 0.85rem;
            font-size: 0.76rem;
        }

        .construction-progress-count {
            padding: 0.32rem 0.68rem;
            font-size: 0.68rem;
        }

        .dynamic-timeline-stepper {
            gap: 0.75rem !important;
            justify-content: flex-start !important;
            padding: 0 !important;
        }

        .timeline-phase-card-item {
            position: relative;
            display: block !important;
            width: 100%;
            padding: 14px 14px 13px 16px !important;
            border: 1px solid rgba(22, 101, 52, 0.12) !important;
            border-left: 0 !important;
            border-radius: 18px !important;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdfb 100%) !important;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.045);
            overflow: visible;
        }

        .timeline-phase-card-item::before {
            content: "";
            position: absolute;
            left: 0;
            top: 12px;
            bottom: 12px;
            width: 4px;
            border-radius: 0 999px 999px 0;
            background: #cbd5e1;
        }

        .timeline-phase-completed::before {
            background: #16a34a;
        }

        .timeline-phase-current::before {
            background: #166534;
        }

        .timeline-phase-upcoming::before {
            background: #cbd5e1;
        }

        .timeline-phase-card-item:hover {
            transform: none !important;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.045) !important;
        }

        .timeline-phase-card-item > .w-100 > .d-flex.justify-content-between.align-items-start {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: start !important;
            margin-bottom: 0.55rem !important;
        }

        .timeline-phase-card-item > .w-100 > .d-flex.justify-content-between.align-items-start > div:first-child {
            min-width: 0;
        }

        .timeline-phase-card-item > .w-100 > .d-flex.justify-content-between.align-items-start > div:last-child {
            display: flex !important;
            flex-direction: column;
            align-items: flex-end !important;
            justify-content: flex-start !important;
            gap: 0.38rem !important;
            min-width: 82px;
        }

        .stepper-phase-name {
            margin: 0 0 0.28rem !important;
            color: #0f172a;
            font-size: 0.9rem !important;
            font-weight: 800;
            line-height: 1.22;
            letter-spacing: -0.01em;
        }

        .stepper-phase-dates {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            max-width: 100%;
            padding: 0.24rem 0.55rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e5edf0;
            color: #64748b;
            font-size: 0.68rem !important;
            font-weight: 700;
            line-height: 1.2;
        }

        .stepper-phase-dates i {
            color: #166534;
        }

        .stepper-percentage {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 52px;
            padding: 0.28rem 0.55rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e5edf0;
            color: #0f172a !important;
            font-size: 0.76rem !important;
            font-weight: 900 !important;
            line-height: 1;
        }

        .timeline-phase-card-item .badge-status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 86px;
            padding: 0.35rem 0.55rem;
            font-size: 0.66rem !important;
            font-weight: 900;
            line-height: 1.1;
            text-align: center;
            white-space: normal;
        }

        .timeline-progress-shell {
            margin-top: 0.3rem !important;
            padding-top: 1.5rem !important;
        }

        .timeline-progress-shell .progress {
            height: 7px !important;
            border-radius: 999px !important;
            background-color: #eef4f0 !important;
            overflow: hidden;
        }

        .timeline-progress-shell .progress-bar {
            border-radius: 999px !important;
        }

        .milestone-track {
            height: 28px !important;
        }

        .milestone-marker-wrapper {
            top: 0 !important;
        }

        .phase-milestone-marker {
            min-width: 24px !important;
            height: 22px !important;
            padding: 0 6px !important;
            border-radius: 999px !important;
            box-shadow: 0 8px 16px rgba(34, 197, 94, 0.12) !important;
        }

        .phase-milestone-marker i {
            font-size: 0.68rem !important;
        }

        .phase-milestone-marker::after {
            bottom: -5px !important;
            border-left-width: 5px !important;
            border-right-width: 5px !important;
            border-top-width: 5px !important;
        }

        .milestone-info-card {
            display: none !important;
        }
    }

    @media (max-width: 390px) {
        .construction-progress-panel {
            padding: 16px 12px !important;
        }

        .timeline-phase-card-item {
            padding: 13px 12px 12px 15px !important;
        }

        .timeline-phase-card-item > .w-100 > .d-flex.justify-content-between.align-items-start {
            grid-template-columns: minmax(0, 1fr) 76px;
            gap: 0.55rem;
        }

        .timeline-phase-card-item > .w-100 > .d-flex.justify-content-between.align-items-start > div:last-child {
            min-width: 76px;
        }

        .stepper-phase-name {
            font-size: 0.84rem !important;
        }

        .stepper-phase-dates {
            font-size: 0.64rem !important;
            padding: 0.22rem 0.45rem;
        }

        .timeline-phase-card-item .badge-status-pill {
            min-width: 76px;
            padding-inline: 0.45rem;
            font-size: 0.61rem !important;
        }
    }

</style>

@push('scripts')
<script>
    /**
     * Toggles layout visibility seamlessly when a new project selection event occurs.
     */
    function switchProjectTimeline(selectedProjectId) {
        // Hide all active view wrappers
        document.querySelectorAll('.project-timeline-wrapper').forEach(function(wrapper) {
            wrapper.style.display = 'none';
        });

        // Display targeted canvas section matching the key ID 
        const targetElement = document.getElementById('project-panel-' + selectedProjectId);
        if (targetElement) {
            targetElement.style.display = 'block';
            
            // Sync current index state to matching selectors within alternative branches if initialized
            const synchronizerSelects = targetElement.querySelectorAll('.project-theme-select');
            synchronizerSelects.forEach(select => select.value = selectedProjectId);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.milestone-marker-wrapper').forEach(function (wrapper) {
            wrapper.addEventListener('mouseenter', function () {
                wrapper.classList.add('is-active');
            });
            wrapper.addEventListener('mouseleave', function () {
                wrapper.classList.remove('is-active');
            });
        });
    });
</script>
@endpush
@endsection