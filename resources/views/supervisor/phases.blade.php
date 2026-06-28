@extends('layouts.supervisor')

@section('title', 'Construction Phases - Supervisor View')
@section('page_title', 'Construction Phases')

@php
    $formatDate = function ($value) {
        if (empty($value)) {
            return 'Pending';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M j, Y');
        } catch (\Exception $e) {
            return 'Pending';
        }
    };

    $statusLabel = function ($status) {
        return match ($status) {
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'delayed' => 'Delayed',
            'not_started' => 'Planned',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    };

    $statusClass = function ($status) {
        return match ($status) {
            'in_progress' => 'phase-badge is-active',
            'completed' => 'phase-badge is-complete',
            'delayed' => 'phase-badge is-delayed',
            default => 'phase-badge',
        };
    };

    $projectPhases = $primaryProject ? $primaryProject->phases->sortBy('phase_order')->values() : collect();
    $activePhase = $primaryPhase ?: ($projectPhases->firstWhere('status', 'in_progress') ?? $projectPhases->first());
    $completedPhases = $projectPhases->where('status', 'completed')->count();
    $remainingPhases = max(0, $projectPhases->count() - $completedPhases);
    $overallProgress = $projectPhases->isNotEmpty()
        ? round((float) $projectPhases->avg('completion_percentage'), 1)
        : 0;
    $currentStatus = $activePhase ? $statusLabel($activePhase->status) : 'No active phase';
    $activeMilestones = $activePhase ? $activePhase->milestones()->orderBy('planned_date')->get() : collect();
    // Build ordered list of open (not completed) milestones
    $openMilestones = $activeMilestones->where('is_completed', false)->sortBy('planned_date')->values();
    // Current milestone prefers the earliest non-delayed open milestone
    $currentMilestone = $openMilestones->firstWhere('is_delayed', false) ?? $openMilestones->first();
    // Next milestone is the next item in the ordered open list after the current milestone
    $nextMilestone = null;
    if ($currentMilestone) {
        $index = $openMilestones->search(fn($m) => $m->milestone_id == $currentMilestone->milestone_id);
        if ($index !== false) {
            $nextMilestone = $openMilestones->get($index + 1) ?? null;
        }
    } else {
        // If no current milestone (none non-delayed), pick the second open milestone as next if present
        $nextMilestone = $openMilestones->get(1) ?? null;
    }
    $activeMilestoneCount = $activeMilestones->where('is_completed', false)->count();
    $remainingDaysValue = $activePhase && $activePhase->planned_end_date
        ? (int) round(now()->diffInDays($activePhase->planned_end_date, false))
        : null;
    $remainingDaysLabel = $remainingDaysValue === null
        ? 'Pending'
        : ($remainingDaysValue < 0
            ? 'Overdue by ' . abs($remainingDaysValue) . ' day' . (abs($remainingDaysValue) === 1 ? '' : 's')
            : $remainingDaysValue . ' day' . ($remainingDaysValue === 1 ? '' : 's'));
    $currentMilestoneLabel = $currentMilestone?->milestone_name ?? 'No milestone has been assigned by the Engineer yet.';
    $nextMilestoneLabel = $nextMilestone?->milestone_name ?? 'No milestone has been assigned by the Engineer yet.';
    $overdueMilestones = $activeMilestones->filter(function ($milestone) {
        return ! $milestone->is_completed && $milestone->planned_date && $milestone->planned_date->lt(now());
    })->count();
    $nextMilestoneDays = $nextMilestone && $nextMilestone->planned_date
        ? max(0, now()->diffInDays($nextMilestone->planned_date, false))
        : null;
    $scheduleHealth = $activePhase && $activePhase->status === 'delayed'
        ? 'Delayed'
        : ($overdueMilestones > 0 ? 'Delayed' : 'On Schedule');
    $scheduleInsight = $activePhase && $activePhase->status === 'delayed'
        ? 'Delayed by ' . ($remainingDaysValue !== null ? abs($remainingDaysValue) + 1 : '1') . ' day' . (($remainingDaysValue ?? 0) > 0 ? 's' : '')
        : ($overdueMilestones > 0
            ? 'Overdue milestone' . ($overdueMilestones > 1 ? 's' : '') . ' need attention'
            : ($nextMilestoneDays !== null && $nextMilestoneDays <= 7
                ? 'Next milestone due in ' . $nextMilestoneDays . ' day' . ($nextMilestoneDays === 1 ? '' : 's')
                : 'No overdue milestones'));
@endphp

@section('content')
<div class="phase-page-shell">
    @if ($primaryProject && $activePhase)
        <div class="section-card phase-hero-card">
            <div class="section-card-body">
                <div class="phase-hero-header">
                    <div>
                        <div class="eyebrow">Current Active Phase</div>
                        <div class="page-title">{{ $activePhase->phase_name }}</div>
                        <div class="page-subtitle">{{ $primaryProject->project_name }} • {{ $primaryProject->project_location ?? 'Location pending' }}</div>
                    </div>
                    <div class="phase-hero-badges">
                        <span class="{{ $statusClass($activePhase->status) }}">{{ $currentStatus }}</span>
                        <span class="phase-info-pill"><i class="bi bi-clipboard2-check"></i> {{ $completedPhases }}/{{ $projectPhases->count() }} completed</span>
                    </div>
                </div>

                <div class="phase-hero-progress">
                    <div class="phase-hero-progress-copy">
                        <span class="phase-progress-label">Phase Completion</span>
                        <span class="phase-progress-value">{{ (float) ($activePhase->completion_percentage ?? 0) }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ (float) ($activePhase->completion_percentage ?? 0) }}%"></div>
                    </div>
                </div>

                <div class="phase-hero-grid">
                    <div class="phase-detail-item">
                        <div class="phase-detail-label">Planned Start</div>
                        <div class="phase-detail-value">{{ $formatDate($activePhase->planned_start_date) }}</div>
                    </div>
                    <div class="phase-detail-item">
                        <div class="phase-detail-label">Planned End</div>
                        <div class="phase-detail-value">{{ $formatDate($activePhase->planned_end_date) }}</div>
                    </div>
                    <div class="phase-detail-item">
                        <div class="phase-detail-label">Actual Start</div>
                        <div class="phase-detail-value">{{ $formatDate($activePhase->actual_start_date) }}</div>
                    </div>
                    <div class="phase-detail-item">
                        <div class="phase-detail-label">Estimated Remaining</div>
                        <div class="phase-detail-value">{{ $remainingDaysLabel }}</div>
                    </div>
                    <div class="phase-detail-item phase-detail-item-wide">
                        <div class="phase-detail-label">Current Milestone</div>
                        <div class="phase-detail-value">{{ $currentMilestoneLabel }}</div>
                    </div>
                    <div class="phase-detail-item phase-detail-item-wide">
                        <div class="phase-detail-label">Next Milestone</div>
                        <div class="phase-detail-value">{{ $nextMilestoneLabel }}</div>
                    </div>
                </div>

                <div class="phase-insight-banner">
                    <div class="phase-insight-banner-text">
                        <div class="phase-insight-title">Schedule insight</div>
                        <div class="phase-insight-copy">{{ $scheduleInsight }}</div>
                    </div>
                    <span class="phase-info-pill"><i class="bi bi-lightning-charge"></i> {{ $scheduleHealth }}</span>
                </div>

                <div class="phase-hero-actions">
                    <a href="{{ route('supervisor.timeline') }}" class="phase-action-btn phase-action-btn-primary">View Timeline</a>
                    <a href="{{ route('supervisor.reports') }}" class="phase-action-btn phase-action-btn-secondary">Submit Daily Report</a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-xl-2">
                <div class="section-card h-100 phase-summary-card">
                    <div class="section-card-body">
                        <div class="phase-summary-label">Overall Progress</div>
                        <div class="phase-summary-value">{{ $overallProgress }}%</div>
                        <div class="phase-summary-meta">Project-wide delivery health</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <div class="section-card h-100 phase-summary-card">
                    <div class="section-card-body">
                        <div class="phase-summary-label">Completed Phases</div>
                        <div class="phase-summary-value">{{ $completedPhases }}</div>
                        <div class="phase-summary-meta">Delivered in the sequence</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <div class="section-card h-100 phase-summary-card">
                    <div class="section-card-body">
                        <div class="phase-summary-label">Remaining Phases</div>
                        <div class="phase-summary-value">{{ $remainingPhases }}</div>
                        <div class="phase-summary-meta">Still active or pending</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="section-card h-100 phase-summary-card">
                    <div class="section-card-body">
                        <div class="phase-summary-label">Active Milestones</div>
                        <div class="phase-summary-value">{{ $activeMilestoneCount }}</div>
                        <div class="phase-summary-meta">Open items driving today’s work</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="section-card h-100 phase-summary-card">
                    <div class="section-card-body">
                        <div class="phase-summary-label">Schedule Health</div>
                        <div class="phase-summary-value">{{ $scheduleHealth }}</div>
                        <div class="phase-summary-meta">Derived from the live phase status</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card mt-4">
            <div class="section-card-body">
                <div class="phase-section-heading">
                    <div class="phase-section-title-group">
                        <div class="phase-section-title">Construction Progress</div>
                    </div>
                    <span class="phase-info-pill"><i class="bi bi-eye"></i> Monitoring only</span>
                </div>

                <div class="phase-timeline-list">
                    @foreach ($projectPhases as $phase)
                        @php
                            $phaseMilestones = $phase->milestones()->orderBy('planned_date')->get();
                            $phaseCurrentMilestone = $phaseMilestones->where('is_completed', false)->where('is_delayed', false)->sortBy('planned_date')->first();
                            $phaseNextMilestone = $phaseMilestones->where('is_completed', false)->sortBy('planned_date')->first();
                            $isActivePhase = $phase->phase_id == optional($activePhase)->phase_id;
                            $phaseDescription = 'Monitor delivery progress and milestone readiness for ' . strtolower($phase->phase_name) . '.';
                            $phaseHealthStatus = 'On Schedule';
                            $phaseHealthTone = 'is-safe';
                            $phaseHealthCopy = 'No delays detected.';
                            $phaseHealthIcon = 'bi bi-check2-circle';

                            if ($phase->status === 'completed') {
                                $phaseHealthStatus = 'Completed';
                                $phaseHealthTone = 'is-complete';
                                $phaseHealthCopy = 'This phase has been completed successfully.';
                                $phaseHealthIcon = 'bi bi-check-circle-fill';
                            } else {
                                $plannedEndDate = $phase->planned_end_date ? \Carbon\Carbon::parse($phase->planned_end_date) : null;
                                $actualEndDate = $phase->actual_end_date ? \Carbon\Carbon::parse($phase->actual_end_date) : null;
                                $phaseIsDelayed = $phase->status === 'delayed'
                                    || ($plannedEndDate && $actualEndDate && $actualEndDate->gt($plannedEndDate))
                                    || ($plannedEndDate && now()->gt($plannedEndDate) && ((float) ($phase->completion_percentage ?? 0)) < 100);

                                if ($phaseIsDelayed) {
                                    $phaseHealthStatus = 'Delayed';
                                    $phaseHealthTone = 'is-delayed';
                                    $phaseHealthCopy = 'Phase has exceeded the planned completion date.';
                                    $phaseHealthIcon = 'bi bi-exclamation-triangle';
                                } elseif ($plannedEndDate && now()->diffInDays($plannedEndDate, false) <= 7 && ((float) ($phase->completion_percentage ?? 0)) < 100) {
                                    $phaseHealthStatus = 'Attention Required';
                                    $phaseHealthTone = 'is-attention';
                                    $phaseHealthCopy = 'Current phase is approaching its target completion date.';
                                    $phaseHealthIcon = 'bi bi-clock-history';
                                }
                            }
                        @endphp

                        <div class="phase-timeline-item {{ $isActivePhase ? 'is-open is-current' : '' }} {{ $phase->status === 'completed' ? 'is-complete' : '' }} {{ $phase->status === 'delayed' ? 'is-upcoming' : '' }}">
                            <div class="phase-timeline-toggle" role="button" tabindex="0" data-target="phase-panel-{{ $phase->phase_id }}" aria-expanded="{{ $isActivePhase ? 'true' : 'false' }}">
                                <div class="phase-timeline-main">
                                    <span class="phase-timeline-marker">{{ $phase->status === 'completed' ? '✓' : ($isActivePhase ? '▶' : '○') }}</span>
                                    <div class="phase-timeline-copy">
                                        <div class="phase-timeline-name-row">
                                            <span class="phase-timeline-name">{{ $phase->phase_name }}</span>
                                            @if ($isActivePhase)
                                                <span class="phase-current-badge">Current Phase</span>
                                            @endif
                                        </div>
                                        <span class="phase-timeline-meta">{{ $statusLabel($phase->status) }} • {{ (float) ($phase->completion_percentage ?? 0) }}%</span>
                                    </div>
                                </div>
                                <div class="phase-timeline-side">
                                    <span class="phase-timeline-progress-mini" aria-hidden="true">
                                        <span class="phase-timeline-progress-mini-fill" style="width: {{ (float) ($phase->completion_percentage ?? 0) }}%"></span>
                                    </span>
                                    <span class="{{ $statusClass($phase->status) }}">{{ $statusLabel($phase->status) }}</span>
                                </div>
                            </div>
                            <div class="phase-timeline-footer">
                                <span class="phase-timeline-date">{{ $formatDate($phase->planned_start_date) }} – {{ $formatDate($phase->planned_end_date) }}</span>
                                <span class="phase-timeline-link">View Details</span>
                            </div>

                            <div class="phase-timeline-panel" id="phase-panel-{{ $phase->phase_id }}" style="display: {{ $isActivePhase ? 'block' : 'none' }};">
                                <div class="phase-timeline-progress">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="phase-progress-label">Progress</span>
                                        <span class="phase-progress-value">{{ (float) ($phase->completion_percentage ?? 0) }}%</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: {{ (float) ($phase->completion_percentage ?? 0) }}%"></div>
                                    </div>
                                </div>

                                <div class="phase-details-stack mt-3">
                                    <div class="phase-detail-card phase-detail-card-main">
                                        <div class="phase-detail-card-head">
                                            <div>
                                                <div class="phase-detail-title">Phase Overview</div>
                                            </div>
                                        </div>
                                        <div class="phase-detail-value">{{ $phaseDescription }}</div>
                                    </div>

                                    <div class="phase-detail-grid">
                                        <div class="phase-detail-card">
                                            <div class="phase-detail-card-head">
                                                <div>
                                                    <div class="phase-detail-title">Project Schedule</div>
                                                </div>
                                            </div>
                                            <div class="phase-schedule-timeline">
                                                <div class="phase-schedule-block">
                                                    <div class="phase-schedule-label">Planned</div>
                                                    <div class="phase-schedule-line"><span class="phase-schedule-dot"></span><span>{{ $formatDate($phase->planned_start_date) }}</span></div>
                                                    <div class="phase-schedule-line is-end"><span class="phase-schedule-dot is-end"></span><span>{{ $formatDate($phase->planned_end_date) }}</span></div>
                                                </div>
                                                <div class="phase-schedule-block">
                                                    <div class="phase-schedule-label">Actual</div>
                                                    <div class="phase-schedule-line"><span class="phase-schedule-dot"></span><span>{{ $formatDate($phase->actual_start_date) }}</span></div>
                                                    <div class="phase-schedule-line is-end"><span class="phase-schedule-dot is-end"></span><span>{{ $formatDate($phase->actual_end_date) }}</span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="phase-detail-card">
                                            <div class="phase-detail-card-head">
                                                <div>
                                                    <div class="phase-detail-title">Milestone Tracking</div>
                                                </div>
                                            </div>
                                            <div class="phase-milestone-stack">
                                                @if ($phaseCurrentMilestone)
                                                    <div class="phase-milestone-item">
                                                        <div class="phase-milestone-label">Current Milestone</div>
                                                        <div class="phase-milestone-name">{{ $phaseCurrentMilestone->milestone_name }}</div>
                                                        <div class="phase-milestone-meta">{{ $phaseCurrentMilestone->is_delayed ? 'Delayed' : 'In progress' }} • {{ $formatDate($phaseCurrentMilestone->planned_date) }}</div>
                                                    </div>
                                                @else
                                                    <div class="phase-empty-card">
                                                        <div class="phase-empty-icon"><i class="bi bi-flag"></i></div>
                                                        <div>No milestone has been assigned by the Engineer yet.</div>
                                                    </div>
                                                @endif

                                                @if ($phaseNextMilestone)
                                                    <div class="phase-milestone-item">
                                                        <div class="phase-milestone-label">Upcoming Milestone</div>
                                                        <div class="phase-milestone-name">{{ $phaseNextMilestone->milestone_name }}</div>
                                                        <div class="phase-milestone-meta">Target date • {{ $formatDate($phaseNextMilestone->planned_date) }}</div>
                                                    </div>
                                                @else
                                                    <div class="phase-empty-card">
                                                        <div class="phase-empty-icon"><i class="bi bi-calendar2-week"></i></div>
                                                        <div>No upcoming milestone scheduled.</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="phase-health-card {{ $phaseHealthTone }}">
                                        <div class="phase-health-head">
                                            <div class="phase-detail-card-icon"><i class="{{ $phaseHealthIcon }}"></i></div>
                                            <div>
                                                <div class="phase-detail-label">Schedule Health</div>
                                                <div class="phase-health-title">{{ $phaseHealthStatus }}</div>
                                            </div>
                                        </div>
                                        <div class="phase-health-copy">{{ $phaseHealthCopy }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="section-card mt-4">
            <div class="section-card-body">
                <div class="phase-section-heading phase-section-heading-inline">
                    <div class="phase-section-title-group">
                        <div class="phase-section-title">Active Delivery Milestones</div>
                    </div>
                </div>

                @if ($activeMilestones->isNotEmpty())
                    <div class="milestone-grid">
                        @foreach ($activeMilestones as $milestone)
                            @php
                                $milestoneStatus = $milestone->is_completed ? 'Completed' : ($milestone->is_delayed ? 'Delayed' : 'Active');
                                $milestoneClasses = $milestone->is_completed ? 'milestone-card is-complete' : ($milestone->is_delayed ? 'milestone-card is-delayed' : 'milestone-card is-active');
                            @endphp

                            <div class="{{ $milestoneClasses }}">
                                <div class="milestone-top">
                                    <span class="milestone-marker">{{ $milestone->is_completed ? '✓' : ($milestone->is_delayed ? '!' : '●') }}</span>
                                    <span class="phase-badge {{ $milestone->is_completed ? 'is-complete' : ($milestone->is_delayed ? 'is-delayed' : 'is-active') }}">{{ $milestoneStatus }}</span>
                                </div>
                                <div class="milestone-name">{{ $milestone->milestone_name }}</div>
                                <div class="milestone-meta">Target date • {{ $formatDate($milestone->planned_date) }}</div>
                                <div class="phase-hero-progress mt-3">
                                    <div class="phase-hero-progress-copy">
                                        <span class="phase-progress-label">Completion</span>
                                        <span class="phase-progress-value">{{ $milestone->is_completed ? '100%' : '0%' }}</span>
                                    </div>
                                    <div class="progress-track">
                                        <div class="progress-fill" style="width: {{ $milestone->is_completed ? '100' : '0' }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="dashboard-empty-state">
                        <div class="dashboard-empty-icon"><i class="bi bi-flag"></i></div>
                        <div>No milestone has been assigned by the Engineer yet.</div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="section-card">
            <div class="section-card-body">
                <div class="dashboard-empty-state">
                    <div class="dashboard-empty-icon"><i class="bi bi-clipboard2-check"></i></div>
                    <div>No construction phase data is available for your assigned projects yet.</div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .phase-page-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .phase-hero-card {
        background: linear-gradient(135deg, #ffffff 0%, #f6fcf8 100%);
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(9, 96, 86, 0.05);
        padding: 1rem;
    }

    .phase-hero-header,
    .phase-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding-bottom: 0.45rem;
        margin-bottom: 0.2rem;
        border-bottom: 1px solid rgba(9, 96, 86, 0.08);
    }

    .phase-section-heading-inline {
        padding-bottom: 0.2rem;
        margin-bottom: 0.7rem;
    }

    .phase-section-title-group {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .phase-section-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        flex-shrink: 0;
        margin-top: 0.15rem;
    }

    .phase-section-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.08rem;
        font-weight: 700;
        color: var(--supervisor-text);
        line-height: 1.2;
        letter-spacing: 0.01em;
    }

    .phase-hero-badges,
    .phase-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        align-items: center;
    }

    .phase-info-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 0.7rem;
        border-radius: 999px;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .phase-hero-progress {
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        margin-top: 1rem;
    }

    .phase-hero-progress-copy {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .phase-progress-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--supervisor-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .phase-progress-value {
        font-family: 'Syne', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--supervisor-primary);
    }

    .phase-hero-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 1rem;
    }

    .phase-insight-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.8rem;
        margin-top: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 14px;
        background: rgba(9, 96, 86, 0.06);
        border: 1px solid rgba(9, 96, 86, 0.1);
    }

    .phase-insight-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-bottom: 0.2rem;
    }

    .phase-insight-copy {
        font-size: 0.94rem;
        font-weight: 600;
        color: var(--supervisor-text);
    }

    .phase-detail-item {
        background: linear-gradient(135deg, #fcfdfc 0%, #f4f8f6 100%);
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-radius: 16px;
        padding: 0.8rem 0.9rem;
        min-height: 74px;
        box-shadow: 0 6px 16px rgba(9, 96, 86, 0.04);
    }

    .phase-detail-item-wide {
        grid-column: span 2;
    }

    .phase-detail-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-bottom: 0.3rem;
    }

    .phase-detail-value {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--supervisor-text);
        line-height: 1.4;
    }

    .phase-summary-card {
        background: linear-gradient(135deg, #ffffff 0%, #f6fcf8 100%);
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-left: 4px solid var(--supervisor-accent);
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(9, 96, 86, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .phase-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(9, 96, 86, 0.08);
    }

    .phase-summary-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--supervisor-muted);
    }

    .phase-summary-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--supervisor-primary);
        margin-top: 0.15rem;
        line-height: 1.1;
    }

    .phase-summary-meta {
        font-size: 0.86rem;
        color: var(--supervisor-muted);
        margin-top: 0.2rem;
    }

    .phase-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        white-space: nowrap;
        box-shadow: inset 0 0 0 1px rgba(9, 96, 86, 0.06);
    }

    .phase-badge.is-active {
        background: rgba(9, 96, 86, 0.16);
        color: var(--supervisor-primary-deep);
    }

    .phase-badge.is-complete {
        background: rgba(130, 219, 114, 0.18);
        color: #2f6b3c;
    }

    .phase-badge.is-delayed {
        background: rgba(255, 193, 7, 0.18);
        color: #9a6100;
    }

    .phase-timeline-list {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        margin-top: 1rem;
    }

    .phase-timeline-item {
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-radius: 18px;
        background: linear-gradient(135deg, #ffffff 0%, #f6fcf8 100%);
        overflow: hidden;
        transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        box-shadow: 0 8px 22px rgba(9, 96, 86, 0.04);
    }

    .phase-timeline-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(9, 96, 86, 0.08);
    }

    .phase-timeline-item.is-open {
        border-color: rgba(9, 96, 86, 0.16);
        box-shadow: 0 14px 28px rgba(9, 96, 86, 0.08);
    }

    .phase-timeline-item.is-current {
        box-shadow: 0 14px 28px rgba(9, 96, 86, 0.08);
    }

    .phase-timeline-item.is-complete {
        opacity: 0.95;
    }

    .phase-timeline-item.is-upcoming {
        opacity: 0.9;
    }

    .phase-timeline-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 1rem 1.05rem;
        background: transparent;
        border: none;
        text-align: left;
        color: var(--supervisor-text);
        cursor: pointer;
    }

    .phase-timeline-marker {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        font-weight: 700;
        flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(9, 96, 86, 0.06);
    }

    .phase-timeline-main {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        flex: 1;
        min-width: 0;
    }

    .phase-timeline-copy {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }

    .phase-timeline-name-row {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .phase-current-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.28rem 0.55rem;
        border-radius: 999px;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .phase-timeline-side {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        flex-shrink: 0;
    }

    .phase-timeline-progress-mini {
        width: 96px;
        height: 6px;
        border-radius: 999px;
        background: rgba(9, 96, 86, 0.08);
        overflow: hidden;
        flex-shrink: 0;
    }

    .phase-timeline-progress-mini-fill {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--supervisor-secondary), var(--supervisor-accent));
    }

    .phase-timeline-name {
        font-family: 'Syne', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--supervisor-primary);
    }

    .phase-timeline-meta {
        font-size: 0.85rem;
        color: var(--supervisor-muted);
    }

    .phase-timeline-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.8rem;
        padding: 0 1.05rem 1rem;
        color: var(--supervisor-muted);
        font-size: 0.84rem;
    }

    .phase-timeline-link {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--supervisor-primary);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .phase-timeline-panel {
        padding: 0 1.05rem 1.05rem;
    }

    .phase-details-stack {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .phase-detail-card {
        background: linear-gradient(135deg, #fcfdfc 0%, #f4f8f6 100%);
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-radius: 16px;
        padding: 0.9rem 1rem;
        box-shadow: 0 6px 16px rgba(9, 96, 86, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .phase-detail-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(9, 96, 86, 0.06);
    }

    .phase-detail-card-main {
        background: linear-gradient(135deg, #fcfdfc 0%, #f4f8f6 100%);
    }

    .phase-detail-card-head {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        margin-bottom: 0.7rem;
    }

    .phase-detail-card-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        flex-shrink: 0;
    }

    .phase-detail-title {
        font-size: 0.94rem;
        font-weight: 700;
        color: var(--supervisor-text);
    }

    .phase-detail-grid {
        display: grid;
        gap: 0.8rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .phase-schedule-timeline {
        display: grid;
        gap: 0.7rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .phase-schedule-block {
        padding: 0.75rem 0.8rem;
        border-radius: 12px;
        background: #fbfdfb;
        border: 1px solid rgba(9, 96, 86, 0.06);
    }

    .phase-schedule-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-bottom: 0.5rem;
    }

    .phase-schedule-line {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.86rem;
        color: var(--supervisor-text);
        margin-bottom: 0.3rem;
    }

    .phase-schedule-line.is-end {
        margin-bottom: 0;
    }

    .phase-schedule-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--supervisor-secondary);
        box-shadow: 0 0 0 4px rgba(77, 160, 120, 0.12);
        flex-shrink: 0;
    }

    .phase-schedule-dot.is-end {
        background: var(--supervisor-primary);
    }

    .phase-milestone-stack {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .phase-milestone-item,
    .phase-empty-card {
        padding: 0.75rem 0.8rem;
        border-radius: 12px;
        background: #fbfdfb;
        border: 1px solid rgba(9, 96, 86, 0.06);
    }

    .phase-milestone-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-bottom: 0.25rem;
    }

    .phase-milestone-name {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--supervisor-text);
        margin-bottom: 0.25rem;
    }

    .phase-milestone-meta {
        font-size: 0.82rem;
        color: var(--supervisor-muted);
    }

    .phase-empty-card {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: var(--supervisor-muted);
    }

    .phase-empty-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        flex-shrink: 0;
    }

    .phase-health-card {
        padding: 0.9rem 1rem;
        border-radius: 14px;
        border: 1px solid rgba(9, 96, 86, 0.08);
        background: #fbfdfb;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .phase-health-card.is-safe {
        background: rgba(130, 219, 114, 0.12);
        border-color: rgba(130, 219, 114, 0.24);
    }

    .phase-health-card.is-attention {
        background: rgba(255, 193, 7, 0.12);
        border-color: rgba(255, 193, 7, 0.24);
    }

    .phase-health-card.is-delayed {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.2);
    }

    .phase-health-card.is-complete {
        background: rgba(9, 96, 86, 0.08);
        border-color: rgba(9, 96, 86, 0.14);
    }

    .phase-health-head {
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .phase-health-title {
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--supervisor-text);
    }

    .phase-health-copy {
        font-size: 0.9rem;
        color: var(--supervisor-text);
        line-height: 1.45;
    }

    .milestone-grid {
        display: grid;
        gap: 0.9rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 1rem;
    }

    .milestone-card {
        background: linear-gradient(135deg, #ffffff 0%, #f6fcf8 100%);
        border: 1px solid rgba(9, 96, 86, 0.08);
        border-radius: 18px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        box-shadow: 0 8px 20px rgba(9, 96, 86, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .milestone-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(9, 96, 86, 0.08);
    }

    .milestone-card.is-active {
        border-color: rgba(9, 96, 86, 0.2);
        box-shadow: 0 10px 24px rgba(9, 96, 86, 0.06);
    }

    .milestone-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
    }

    .milestone-marker {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        font-weight: 700;
    }

    .milestone-name {
        font-family: 'Syne', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: var(--supervisor-text);
    }

    .milestone-meta {
        font-size: 0.85rem;
        color: var(--supervisor-muted);
    }

    .phase-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.8rem 1.15rem;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .phase-action-btn:hover {
        transform: translateY(-1px);
    }

    .phase-action-btn-primary {
        background: var(--supervisor-primary);
        color: #fff;
        box-shadow: 0 8px 18px rgba(9, 96, 86, 0.16);
    }

    .phase-action-btn-primary:hover {
        background: var(--supervisor-primary-deep);
        color: #fff;
        box-shadow: 0 10px 20px rgba(9, 96, 86, 0.18);
    }

    .phase-action-btn-secondary {
        background: transparent;
        border: 1px solid rgba(9, 96, 86, 0.2);
        color: var(--supervisor-primary);
    }

    .phase-action-btn-secondary:hover {
        background: rgba(9, 96, 86, 0.08);
        color: var(--supervisor-primary);
        box-shadow: 0 8px 16px rgba(9, 96, 86, 0.08);
    }

    @media (max-width: 1024px) {
        .phase-hero-grid,
        .milestone-grid {
            grid-template-columns: 1fr;
        }

        .phase-detail-item-wide {
            grid-column: span 1;
        }
    }

    @media (max-width: 768px) {
        .phase-hero-header,
        .phase-section-heading {
            flex-direction: column;
        }

        .phase-hero-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .phase-insight-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .phase-timeline-toggle {
            flex-direction: column;
            align-items: flex-start;
        }

        .phase-timeline-side {
            width: 100%;
            justify-content: space-between;
        }

        .phase-timeline-footer {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll('.phase-timeline-toggle');

        function openPanel(toggle) {
            const targetId = toggle.getAttribute('data-target');
            const targetPanel = document.getElementById(targetId);

            if (!targetPanel) {
                return;
            }

            document.querySelectorAll('.phase-timeline-panel').forEach(function (panel) {
                panel.style.display = 'none';
            });

            document.querySelectorAll('.phase-timeline-item').forEach(function (item) {
                item.classList.remove('is-open');
            });

            targetPanel.style.display = 'block';
            toggle.closest('.phase-timeline-item').classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                openPanel(this);
            });

            toggle.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openPanel(this);
                }
            });
        });
    });
</script>
@endsection
