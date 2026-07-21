@extends('layouts.client')

@section('title', 'Project Timeline - Client View')
@section('mobileTitle', 'Timeline')

@section('content')
<div class="container-fluid p-0">
    @include('client.partials.page-header', [
        'eyebrow' => 'Project Flow',
        'title' => 'Timeline',
        'description' => 'Monitor milestone timelines and project phase evolution across your active sites.',
    ])

    @if(isset($projectsWithStats) && count($projectsWithStats) > 0)
        <div class="timeline-toolbar-row gap-3 mb-4">
            <!-- LEFT: Project selection dropdown, equal with the summary cards -->
            <div class="timeline-project-dropdown-card d-flex flex-column justify-content-center">
                <label for="clientTimelineProjectSelect" class="tpd-label"><i class="bi bi-building me-1"></i>Project</label>
                <div class="tpd-select-wrap">
                    <select id="clientTimelineProjectSelect" class="tpd-select" onchange="switchClientTimeline(this.value)">
                        @foreach($projectsWithStats as $project)
                            @php
                                $ddId = data_get($project, 'id');
                                $ddActive = (string) $ddId === (string) ($selectedProjectId ?? data_get($projectsWithStats->first(), 'id'));
                            @endphp
                            <option value="{{ $ddId }}"{{ $ddActive ? ' selected' : '' }}>{{ data_get($project, 'name', 'Project') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="summary-metric-card">
                <div class="summary-icon-box bg-emerald-light">
                    <i class="bi bi-folder-fill text-emerald-dg"></i>
                </div>
                <div class="summary-card-copy">
                    <span class="summary-card-label">Monitored Contracts</span>
                    <h4 class="summary-card-value brand-dark-green-header">{{ count($projectsWithStats) }}</h4>
                </div>
            </div>
            <div class="summary-card-divider"></div>
            <div class="summary-metric-card">
                <div class="summary-icon-box bg-emerald-light">
                    <i class="bi bi-clock-history text-emerald-dg"></i>
                </div>
                <div class="summary-card-copy">
                    <span class="summary-card-label">Active Deployments</span>
                    <h4 class="summary-card-value brand-dark-green-header">{{ count($projectsWithStats) }}</h4>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="d-flex flex-column gap-4 timeline-feed-container">
                    @foreach($projectsWithStats as $project)
                        @php
                            $cardId = data_get($project, 'id');
                            $cardActive = (string) $cardId === (string) ($selectedProjectId ?? data_get($projectsWithStats->first(), 'id'));
                        @endphp
                        <article class="timeline-project-card-wrapper client-timeline-panel {{ $cardActive ? '' : 'is-hidden' }}" id="project-timeline-{{ $cardId }}">
                            <header class="timeline-card-header-block">
                                <div class="timeline-header-meta-group">
                                    <span class="timeline-eyebrow-badge">Project Scope</span>
                                    <h3 class="timeline-project-title brand-dark-green-header">{{ data_get($project, 'name', 'Project') }}</h3>
                                </div>
                                <div class="timeline-badge-status-container">
                                    <span class="timeline-status-badge badge-on-track">
                                        <span class="badge-dot-indicator em-dot"></span>On Track
                                    </span>
                                </div>
                            </header>

                            <div class="timeline-metrics-subgrid">
                                <div class="timeline-metric-tile">
                                    <div class="metric-icon-canvas"><i class="bi bi-calendar3"></i></div>
                                    <div class="metric-copy-block">
                                        <span class="metric-label">Start Date</span>
                                        <strong class="metric-value">{{ optional(data_get($project, 'startDate'))->format('M d, Y') ?? 'TBD' }}</strong>
                                    </div>
                                </div>
                                <div class="timeline-metric-tile">
                                    <div class="metric-icon-canvas"><i class="bi bi-calendar-check"></i></div>
                                    <div class="metric-copy-block">
                                        <span class="metric-label">Target End Date</span>
                                        <strong class="metric-value">{{ optional(data_get($project, 'targetEndDate'))->format('M d, Y') ?? 'TBD' }}</strong>
                                    </div>
                                </div>
                                <div class="timeline-metric-tile">
                                    <div class="metric-icon-canvas"><i class="bi bi-shield-check"></i></div>
                                    <div class="metric-copy-block">
                                        <span class="metric-label">Schedule Health</span>
                                        <strong class="metric-value text-emerald-dg">On Track</strong>
                                    </div>
                                </div>
                                <div class="timeline-metric-tile">
                                    <div class="metric-icon-canvas"><i class="bi bi-hourglass-split"></i></div>
                                    <div class="metric-copy-block">
                                        <span class="metric-label">Days Remaining</span>
                                        <strong class="metric-value">{{ optional(data_get($project, 'targetEndDate'))->startOfDay() ? max(0, (int) now()->startOfDay()->diffInDays(optional(data_get($project, 'targetEndDate'))->startOfDay(), false)) : 0 }} Days</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-progress-section">
                                <div class="progress-meta-label-row">
                                    <span class="progress-lbl-context">Workflow Progress</span>
                                    <span class="progress-percentage-numeric text-emerald-dg">{{ round((float) data_get($project, 'progress', 0)) }}%</span>
                                </div>
                                <div class="workflow-progress-rail-wrap">
                                    <div class="progress-track-rail">
                                        <span class="progress-fill-bar" style="width: {{ (float) data_get($project, 'progress', 0) }}%;"></span>

                                        @php
                                            $railMilestones = collect(data_get($project, 'milestones', []))
                                                ->filter(function ($milestone) {
                                                    return !empty(data_get($milestone, 'milestone_name')) && data_get($milestone, 'marker_percent') !== null;
                                                })
                                                ->sortBy('marker_percent')
                                                ->values();
                                        @endphp
                                        @if($railMilestones->isNotEmpty())
                                            <div class="milestone-track">
                                                @foreach($railMilestones as $milestone)
                                                    @php
                                                        $milestoneName = data_get($milestone, 'milestone_name', 'Milestone');
                                                        $milestoneStartLabel = data_get($milestone, 'start_date') ? \Carbon\Carbon::parse(data_get($milestone, 'start_date'))->format('M d, Y') : 'TBD';
                                                        $milestoneEndLabel = data_get($milestone, 'end_date') ? \Carbon\Carbon::parse(data_get($milestone, 'end_date'))->format('M d, Y') : 'TBD';
                                                        $milestoneFlagClass = data_get($milestone, 'is_completed') ? 'phase-milestone-marker completed' : (data_get($milestone, 'is_delayed') ? 'phase-milestone-marker delayed' : 'phase-milestone-marker');
                                                    @endphp
                                                    <div class="milestone-marker-wrapper" style="left: {{ data_get($milestone, 'marker_percent') }}%;">
                                                        <button type="button" class="{{ $milestoneFlagClass }}" aria-label="Milestone: {{ $milestoneName }}">
                                                            <i class="bi bi-flag-fill"></i>
                                                        </button>
                                                        <div class="milestone-info-card" role="tooltip">
                                                            <div class="milestone-info-title">{{ $milestoneName }}</div>
                                                            <div class="milestone-info-row">
                                                                <span>Start</span>
                                                                <strong>{{ $milestoneStartLabel }}</strong>
                                                            </div>
                                                            <div class="milestone-info-row">
                                                                <span>End</span>
                                                                <strong>{{ $milestoneEndLabel }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="timeline-gantt-section mb-4">
                                <div class="phases-heading-block mb-3">
                                    <span class="phases-title-label">Project Gantt Chart</span>
                                </div>
                                <div class="client-gantt-view">
                                    @php
                                        $phasesForGantt = collect(data_get($project, 'phases', []));
                                        $projectStart = \Carbon\Carbon::parse(data_get($project, 'startDate')) ?? now()->subMonths(1);
                                        $projectEnd = \Carbon\Carbon::parse(data_get($project, 'targetEndDate')) ?? now()->addMonths(3);
                                        if (!$projectStart || !$projectEnd || !$projectEnd->gt($projectStart)) {
                                            $phaseStarts = $phasesForGantt->pluck('start')->filter();
                                            $phaseEnds = $phasesForGantt->pluck('end')->filter();
                                            if ($phaseStarts->isNotEmpty() && $phaseEnds->isNotEmpty()) {
                                                $projectStart = \Carbon\Carbon::parse($phaseStarts->min());
                                                $projectEnd = \Carbon\Carbon::parse($phaseEnds->max());
                                            }
                                        }
                                        $totalDays = max(1, $projectStart->diffInDays($projectEnd, false));
                                        $months = [];
                                        $currentMonth = $projectStart->copy()->firstOfMonth();
                                        $monthDayOffsets = [];
                                        while ($currentMonth->lte($projectEnd)) {
                                            $monthStartOffset = max(0, $projectStart->diffInDays($currentMonth->copy()->firstOfMonth(), false));
                                            $monthDayOffsets[] = [
                                                'label' => strtoupper($currentMonth->format('F Y')),
                                                'days' => $currentMonth->daysInMonth,
                                                'startOffset' => $monthStartOffset,
                                                'monthStart' => $currentMonth->copy(),
                                            ];
                                            $currentMonth->addMonth();
                                        }
                                        $todayOffset = $projectStart->diffInDays(now(), false);
                                        $todayPercent = ($todayOffset / $totalDays) * 100;
                                        $showTodayMarker = $todayPercent >= 0 && $todayPercent <= 100;
                                    @endphp

                                    <div class="desktop-gantt-chart">
                                        <div class="gantt-table-wrapper" data-gantt-scroll>
                                            <table class="gantt-chart-table">
                                                <thead>
                                                    <tr class="gantt-info-header-row">
                                                        <th class="gantt-col-phase sticky-col">Phases</th>
                                                        <th class="gantt-col-date sticky-col">Start</th>
                                                        <th class="gantt-col-date sticky-col">End</th>
                                                        <th class="gantt-col-progress sticky-col">Progress</th>
                                                        @foreach($monthDayOffsets as $mIndex => $month)
                                                            <th class="gantt-col-month {{ $mIndex % 2 === 1 ? 'gantt-month-alt' : '' }}" colspan="{{ $month['days'] }}">{{ $month['label'] }}</th>
                                                        @endforeach
                                                    </tr>
                                                    <tr class="gantt-day-header-row">
                                                        <th class="gantt-col-phase sticky-col"></th>
                                                        <th class="gantt-col-date sticky-col"></th>
                                                        <th class="gantt-col-date sticky-col"></th>
                                                        <th class="gantt-col-progress sticky-col"></th>
                                                        @foreach($monthDayOffsets as $mIndex => $month)
                                                            @for($d = 1; $d <= $month['days']; $d++)
                                                                @php
                                                                    $cellDate = $month['monthStart']->copy()->addDays($d - 1);
                                                                    $isToday = $cellDate->isSameDay(now());
                                                                @endphp
                                                                <th class="gantt-day-cell {{ $mIndex % 2 === 1 ? 'gantt-month-alt' : '' }} {{ $isToday ? 'is-today' : '' }}">{{ $d }}</th>
                                                            @endfor
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($phasesForGantt as $phase)
                                                        @php
                                                            $phaseStart = \Carbon\Carbon::parse(data_get($phase, 'start'));
                                                            $phaseEnd = \Carbon\Carbon::parse(data_get($phase, 'end'));
                                                            $phaseProgress = (float) data_get($phase, 'progress', 0);
                                                            $phaseName = data_get($phase, 'name', data_get($phase, 'phase_name', 'Phase'));
                                                            $phaseCode = data_get($phase, 'phase_code', 'Phase');
                                                            $displayStatus = data_get($phase, 'display_status', data_get($phase, 'status', 'planning'));
                                                            $startOffset = max(0, $projectStart->diffInDays($phaseStart, false));
                                                            $endOffset = max(0, $projectStart->diffInDays($phaseEnd, false));
                                                            $leftPercent = ($startOffset / $totalDays) * 100;
                                                            $widthPercent = max(1, (($endOffset - $startOffset) / $totalDays) * 100);
                                                            $barClass = match(true) {
                                                                $displayStatus === 'completed' => 'gantt-bar completed',
                                                                $displayStatus === 'in-progress' => 'gantt-bar in-progress',
                                                                $displayStatus === 'delayed' => 'gantt-bar delayed',
                                                                default => 'gantt-bar',
                                                            };
                                                        @endphp
                                                        <tr class="gantt-phase-row">
                                                            <td class="gantt-col-phase sticky-col">{{ $phaseName }}</td>
                                                            <td class="gantt-col-date sticky-col">{{ $phaseStart ? $phaseStart->format('Y-m-d') : 'TBD' }}</td>
                                                            <td class="gantt-col-date sticky-col">{{ $phaseEnd ? $phaseEnd->format('Y-m-d') : 'TBD' }}</td>
                                                            <td class="gantt-col-progress sticky-col">{{ round($phaseProgress) }}%</td>
                                                            <td class="gantt-col-timeline" colspan="{{ collect($monthDayOffsets)->sum(fn($m) => $m['days']) }}">
                                                                <div class="gantt-timeline-cell" style="--gantt-total-days: {{ $totalDays }};">
                                                                    @foreach($monthDayOffsets as $mIndex => $month)
                                                                        @if($mIndex > 0)
                                                                            <div class="gantt-month-divider" style="left: {{ ($month['startOffset'] / $totalDays) * 100 }}%;"></div>
                                                                        @endif
                                                                    @endforeach
                                                                    @if($showTodayMarker)
                                                                        <div class="gantt-today-marker" style="left: {{ $todayPercent }}%;" title="Today"></div>
                                                                    @endif
                                                                    <div class="{{ $barClass }}" style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%;" title="{{ $phaseName }} &middot; {{ round($phaseProgress) }}% complete">
                                                                        <div class="gantt-bar-fill" style="width: {{ min(100, max(0, $phaseProgress)) }}%;"></div>
                                                                        <span class="gantt-bar-label">{{ $phaseName }}</span>
                                                                    </div>
                                                                    @foreach(data_get($phase, 'milestones', []) as $milestone)
                                                                        @php
                                                                            $milestoneStart = \Carbon\Carbon::parse(data_get($milestone, 'start_date') ?? data_get($milestone, 'start'));
                                                                            $milestoneOffset = max(0, $projectStart->diffInDays($milestoneStart, false));
                                                                            $milestoneLeft = ($milestoneOffset / $totalDays) * 100;
                                                                            $milestoneClass = data_get($milestone, 'is_completed') ? 'gantt-milestone completed' : (data_get($milestone, 'is_delayed') ? 'gantt-milestone delayed' : 'gantt-milestone');
                                                                            $milestoneName = data_get($milestone, 'milestone_name', 'Milestone');
                                                                        @endphp
                                                                        <div class="{{ $milestoneClass }}" style="left: {{ $milestoneLeft }}%;" title="{{ $milestoneName }}">
                                                                            <i class="bi bi-flag-fill"></i>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mobile-gantt-view">
                                        @php
                                            $phasesForGantt = collect(data_get($project, 'phases', []));
                                        @endphp
                                        @foreach($phasesForGantt as $phase)
                                            @php
                                                $phaseStart = \Carbon\Carbon::parse(data_get($phase, 'start'));
                                                $phaseEnd = \Carbon\Carbon::parse(data_get($phase, 'end'));
                                                $phaseProgress = (float) data_get($phase, 'progress', 0);
                                                $phaseName = data_get($phase, 'name', data_get($phase, 'phase_name', 'Phase'));
                                                $phaseCode = data_get($phase, 'phase_code', 'Phase');
                                                $displayStatus = data_get($phase, 'display_status', data_get($phase, 'status', 'planning'));
                                                $statusLabel = ucwords(str_replace('-', ' ', $displayStatus));
                                                $startLabel = $phaseStart ? $phaseStart->format('M d, Y') : 'TBD';
                                                $endLabel = $phaseEnd ? $phaseEnd->format('M d, Y') : 'TBD';
                                                $mStartOffset = max(0, $projectStart->diffInDays($phaseStart, false));
                                                $mEndOffset = max(0, $projectStart->diffInDays($phaseEnd, false));
                                                $mLeftPercent = ($mStartOffset / $totalDays) * 100;
                                                $mWidthPercent = max(2, (($mEndOffset - $mStartOffset) / $totalDays) * 100);
                                                $mDurationDays = max(1, $phaseStart->diffInDays($phaseEnd) + 1);
                                            @endphp
                                            <div class="mobile-gantt-card status-accent-{{ $displayStatus }}">
                                                <div class="mobile-gantt-head">
                                                    <div>
                                                        <h4 class="mobile-gantt-title">{{ $phaseName }}</h4>
                                                        <span class="mobile-gantt-code">{{ $phaseCode }}</span>
                                                    </div>
                                                    <span class="status-pill-badge {{ $displayStatus }}">{{ $statusLabel }}</span>
                                                </div>
                                                <div class="mobile-gantt-meta">
                                                    <div class="mobile-gantt-field">
                                                        <span class="mobile-gantt-label">Start</span>
                                                        <span class="mobile-gantt-value">{{ $startLabel }}</span>
                                                    </div>
                                                    <div class="mobile-gantt-field">
                                                        <span class="mobile-gantt-label">End</span>
                                                        <span class="mobile-gantt-value">{{ $endLabel }}</span>
                                                    </div>
                                                    <div class="mobile-gantt-field">
                                                        <span class="mobile-gantt-label">Duration</span>
                                                        <span class="mobile-gantt-value">{{ $mDurationDays }} {{ Str::plural('day', $mDurationDays) }}</span>
                                                    </div>
                                                </div>
                                                <div class="mobile-gantt-mini-timeline" aria-hidden="true">
                                                    <div class="mobile-mini-track">
                                                        @if($showTodayMarker)
                                                            <div class="mobile-mini-today" style="left: {{ $todayPercent }}%;"></div>
                                                        @endif
                                                        <div class="mobile-mini-bar {{ $displayStatus }}" style="left: {{ $mLeftPercent }}%; width: {{ $mWidthPercent }}%;"></div>
                                                        @foreach(data_get($phase, 'milestones', []) as $milestone)
                                                            @php
                                                                $mMilestoneStart = \Carbon\Carbon::parse(data_get($milestone, 'start_date') ?? data_get($milestone, 'start'));
                                                                $mMilestoneOffset = max(0, $projectStart->diffInDays($mMilestoneStart, false));
                                                                $mMilestoneLeft = ($mMilestoneOffset / $totalDays) * 100;
                                                                $mMilestoneClass = data_get($milestone, 'is_completed') ? 'completed' : (data_get($milestone, 'is_delayed') ? 'delayed' : '');
                                                            @endphp
                                                            <div class="mobile-mini-milestone {{ $mMilestoneClass }}" style="left: {{ $mMilestoneLeft }}%;"></div>
                                                        @endforeach
                                                    </div>
                                                    <div class="mobile-mini-caption">Project timeline position</div>
                                                </div>
                                                <div class="mobile-gantt-progress-row">
                                                    <div class="mobile-gantt-track">
                                                        <span class="mobile-gantt-fill" style="width: {{ $phaseProgress }}%;"></span>
                                                    </div>
                                                    <span class="mobile-gantt-percent">{{ round($phaseProgress) }}%</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="gantt-status-guide">
                                <div class="status-guide-title">Milestone Bar Guide</div>
                                <div class="status-guide-list">
                                    <div class="status-guide-item"><span class="status-guide-swatch" style="background:#10b981;"></span><span class="status-guide-label">Completed</span></div>
                                    <div class="status-guide-item"><span class="status-guide-swatch" style="background:#3b82f6;"></span><span class="status-guide-label">In Progress</span></div>
                                    <div class="status-guide-item"><span class="status-guide-swatch" style="background:#94a3b8;"></span><span class="status-guide-label">Upcoming</span></div>
                                    <div class="status-guide-item"><span class="status-guide-swatch" style="background:#ef4444;"></span><span class="status-guide-label">Delayed</span></div>
                                    <div class="status-guide-item"><span class="status-guide-swatch status-guide-swatch-today"></span><span class="status-guide-label">Today</span></div>
                                </div>
                            </div>

                            <footer class="timeline-phases-footer-wrapper">
                                <div class="phases-heading-block mb-3">
                                    <span class="phases-title-label">Structural Phases Evolution</span>
                                </div>
                                
                                <div class="modern-phases-pipeline">
                                    @foreach(data_get($project, 'phases', []) as $index => $phase)
                                        @php
                                            $displayStatus = data_get($phase, 'display_status', data_get($phase, 'status', 'planning'));
                                            $isCompleted = $displayStatus === 'completed';
                                            $isCurrent = $displayStatus === 'in-progress';
                                        @endphp
                                        <div class="phase-pipeline-node {{ $isCurrent ? 'node-active' : ($isCompleted ? 'node-completed' : 'node-pending') }}">
                                            @if(!$loop->last)
                                                <div class="pipeline-connector-line {{ $isCompleted && data_get($project, 'phases', [])[$index + 1] && data_get($project, 'phases', [])[$index + 1]['display_status'] === 'completed' ? 'connector-filled' : '' }}"></div>
                                            @endif

                                            <div class="pipeline-status-circle">
                                                @if($isCompleted)
                                                    <i class="bi bi-check-lg"></i>
                                                @elseif($isCurrent)
                                                    <span class="pulse-core-dot"></span>
                                                @else
                                                    <span class="inner-idle-dot"></span>
                                                @endif
                                            </div>

                                            <div class="pipeline-node-card">
                                                <div class="node-meta-top">
                                                    <span class="node-index-label">Phase 0{{ $loop->iteration }}</span>
                                                    <span class="node-status-text">
                                                        {{ $isCompleted ? 'Completed' : ($isCurrent ? 'Active Phase' : 'Pending') }}
                                                    </span>
                                                </div>
                                                <h5 class="node-phase-title">{{ data_get($phase, 'phase_name', 'Phase') }}</h5>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </footer>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="timeline-empty-state-card text-center py-5">
            <div class="empty-state-icon-canvas mb-3"><i class="bi bi-calendar-x"></i></div>
            <h5 class="fw-bold mb-2 brand-dark-green-header">No Active Timelines</h5>
            <p class="text-muted mb-0">There are no operational milestone tracking flows tied to your current portal contracts.</p>
        </div>
    @endif
</div>

<style>
    /* --- D&G CONSTRUCTION ENTERPRISE DESIGN TOKENS --- */
    :root {
        --dg-primary-green: #10b981;
        --dg-dark-slate: #0f172a;
        --dg-muted-gray: #64748b;
        --dg-border-color: #e2e8f0;
        --dg-light-bg: #f8fafc;
        --dg-radius-lg: 16px;
        --dg-font-stack: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* TYPOGRAPHY ANCHOR ENFORCEMENT */
    .brand-dark-green-header {
        color: var(--dg-dark-slate) !important;
        font-family: var(--dg-font-stack);
    }
    .text-emerald-dg { color: var(--dg-primary-green) !important; }

    /* METRIC OVERVIEW STATS GRID PANELS (FULL PAGE SPREAD) */
    .timeline-summary-panel-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        align-items: stretch;
        background: #ffffff;
        border: 1px solid var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        padding: 16px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.01);
    }
    .summary-metric-card {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1 1 0;
        min-width: 0;
        padding: 12px;
        border: 1px solid #f1f5f9;
        border-radius: 14px;
        background: #f8fafc;
    }
    .summary-card-divider {
        display: none;
    }
    .summary-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .bg-emerald-light { background-color: #e6f7ed; }
    
    .summary-card-copy {
        display: flex;
        flex-direction: column;
    }
    .summary-card-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--dg-muted-gray);
    }
    .summary-card-value {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    /* FEED CARD TILES INTERFACE STYLE (WIDE FORMAT) */
    .timeline-project-card-wrapper {
        background: #ffffff;
        border: 1px solid var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        padding: 32px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.01);
        display: flex;
        flex-direction: column;
        gap: 28px;
    }

    .timeline-card-header-block {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        border-bottom: 1px solid var(--dg-border-color);
        padding-bottom: 18px;
    }
    .timeline-header-meta-group {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .timeline-eyebrow-badge {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dg-muted-gray);
    }
    .timeline-project-title {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    
    .timeline-status-badge {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-on-track {
        background-color: #e6f7ed;
        color: #10b981;
        border: 1px solid #a7f3d0;
    }
    .badge-dot-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .em-dot { background-color: var(--dg-primary-green); }

    /* METRIC MATRIX GRID SCALING */
    .timeline-metrics-subgrid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
    }
    .timeline-metric-tile {
        background-color: var(--dg-light-bg);
        border: 1px solid var(--dg-border-color);
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .metric-icon-canvas {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background-color: #ffffff;
        border: 1px solid var(--dg-border-color);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--dg-muted-gray);
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .metric-copy-block {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .metric-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--dg-muted-gray);
        letter-spacing: 0.02em;
    }
    .metric-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* TIMELINE PROGRESS SLIDER COMPONENTS */
    .timeline-progress-section {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .progress-meta-label-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .progress-lbl-context {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--dg-muted-gray);
    }
    .progress-percentage-numeric {
        font-size: 1.1rem;
        font-weight: 800;
    }
    .progress-track-rail {
        width: 100%;
        height: 10px;
        background-color: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-fill-bar {
        display: block;
        height: 100%;
        background-color: var(--dg-primary-green);
        border-radius: 999px;
    }

    /* ==========================================================================
       🆕 IMPROVED PIPELINE PHASES TRACKER (PREMIUM UI/UX DESIGN)
       ========================================================================== */
    .timeline-phases-footer-wrapper {
        border-top: 1px solid var(--dg-border-color);
        padding-top: 24px;
        display: flex;
        flex-direction: column;
    }
    .phases-title-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--dg-muted-gray);
        font-weight: 700;
    }
    
    /* Horizontal Pipeline Framework Setup */
    .modern-phases-pipeline {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        width: 100%;
        padding-top: 10px;
    }
    
    .phase-pipeline-node {
        position: relative;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    /* Global Connector Lines */
    .pipeline-connector-line {
        position: absolute;
        top: 16px;
        left: 32px;
        width: calc(100% - 16px);
        height: 3px;
        background-color: #e2e8f0;
        z-index: 1;
    }
    .pipeline-connector-line.connector-filled {
        background-color: var(--dg-primary-green);
    }

    /* Status Node Indicator Rings */
    .pipeline-status-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 2px solid #cbd5e1;
        z-index: 2;
        margin-bottom: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Meta Cards attached to Nodes */
    .pipeline-node-card {
        background-color: var(--dg-light-bg);
        border: 1px solid var(--dg-border-color);
        border-radius: 12px;
        padding: 12px 14px;
        width: 100%;
        transition: all 0.25s ease;
    }
    .node-meta-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .node-index-label {
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--dg-muted-gray);
    }
    .node-status-text {
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: var(--dg-muted-gray);
    }
    .node-phase-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    /* Node State Modifier Injections */
    /* 1. Completed Nodes Style */
    .node-completed .pipeline-status-circle {
        background-color: var(--dg-primary-green);
        border-color: var(--dg-primary-green);
        color: #ffffff;
        font-size: 0.9rem;
    }
    .node-completed .pipeline-node-card {
        background-color: #f0fdf4;
        border-color: #bbf7d0;
    }
    .node-completed .node-status-text { color: #16a34a; }

    /* 2. Active/Current Nodes Style */
    .node-active .pipeline-status-circle {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
    }
    .node-active .pulse-core-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #3b82f6;
        animation: activePulse 2s infinite ease-in-out;
    }
    .node-active .pipeline-node-card {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.05);
    }
    .node-active .node-status-text { color: #2563eb; font-weight: 800; }
    .node-active .node-phase-title { color: var(--dg-dark-slate); }

    /* 3. Idle / Pending Internal Dots */
    .node-pending .inner-idle-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #94a3b8;
    }

    @keyframes activePulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.15); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }

    /* EMPTY INFRASTRUCTURE CONTAINER DECORATOR */
    .timeline-empty-state-card {
        background-color: #ffffff;
        border: 1px dashed var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        padding: 4rem 2rem;
    }
    .empty-state-icon-canvas { font-size: 2.5rem; color: var(--dg-muted-gray); }

    /* ==========================================================================
       🆕 TIMELINE TOOLBAR ROW: PROJECT DROPDOWN + SUMMARY CARDS (EQUAL, ONE ROW)
       ========================================================================== */
    .timeline-toolbar-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
        align-items: stretch;
    }
    .timeline-toolbar-row > .summary-metric-card {
        flex: 1 1 0;
        min-width: 0;
    }

    /* Project selection dropdown card (sits on the left, equal to the summary cards) */
    .timeline-project-dropdown-card {
        background: #ffffff;
        border: 1px solid var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        padding: 12px 16px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.01);
        min-width: 0;
    }
    .tpd-label {
        display: flex;
        align-items: center;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--dg-muted-gray);
        margin-bottom: 6px;
    }
    .tpd-select-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .tpd-select {
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        background-color: #f1f5f9;
        border: 1px solid rgba(22, 101, 52, 0.15);
        color: #166534;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.5rem 2rem 0.5rem 0.9rem;
        border-radius: 10px;
        cursor: pointer;
        font-family: inherit;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23166534' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.7rem center;
        background-size: 10px 12px;
    }
    .tpd-select:focus {
        border-color: #166534;
        box-shadow: 0 0 0 0.25rem rgba(22, 101, 52, 0.15);
        background-color: #ffffff;
        outline: none;
    }

    .client-timeline-panel.is-hidden {
        display: none;
    }

    /* WORKFLOW PROGRESS BAR + MILESTONE FLAG MARKERS (mirrors supervisor timeline) */
    .workflow-progress-rail-wrap {
        position: relative;
        margin-top: 6px;
    }
    .progress-track-rail {
        position: relative;
        height: 10px;
        margin-top: 34px;
        overflow: visible;
    }
    .milestone-track {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 100%;
        height: 28px;
        z-index: 3;
        pointer-events: none;
        overflow: visible;
    }
    .milestone-marker-wrapper {
        position: absolute;
        bottom: 0;
        transform: translateX(-50%);
        display: inline-flex;
        align-items: flex-end;
        justify-content: center;
        pointer-events: auto;
        z-index: 20;
    }
    .phase-milestone-marker {
        position: relative;
        transform: translateY(0);
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
        transform: translateY(-5px);
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
        bottom: calc(100% + 12px);
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

    /* --- RESPONSIVE WORKSPACE SCALING --- */
    @media (max-width: 1199px) {
        .timeline-toolbar-row {
            grid-template-columns: 1fr 1fr;
        }
        .timeline-project-dropdown-card {
            grid-column: 1 / -1;
        }
        .timeline-metrics-subgrid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .modern-phases-pipeline {
            flex-direction: column;
            gap: 20px;
        }
        .phase-pipeline-node {
            width: 100%;
            flex-direction: row;
            align-items: center;
            gap: 16px;
        }
        .pipeline-connector-line {
            left: 15px;
            top: 32px;
            width: 2px;
            height: calc(100% + 8px);
        }
        .pipeline-status-circle {
            margin-bottom: 0;
        }
    }

    @media (max-width: 991px) {
        .timeline-toolbar-row {
            grid-template-columns: 1fr 1fr;
        }
        .timeline-project-dropdown-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 767px) {
        .timeline-toolbar-row {
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .timeline-project-dropdown-card {
            grid-column: 1 / -1;
            margin-bottom: 0;
        }
        .timeline-toolbar-row .summary-metric-card {
            padding: 10px 12px;
            gap: 10px;
        }
        .timeline-toolbar-row .summary-icon-box {
            width: 38px;
            height: 38px;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .timeline-toolbar-row .summary-card-label {
            font-size: 0.68rem;
            line-height: 1.2;
        }
        .timeline-toolbar-row .summary-card-value {
            font-size: 1.15rem;
            line-height: 1.1;
        }
        .timeline-summary-panel-row {
            flex-direction: column;
            align-items: stretch;
            gap: 16px;
            padding: 16px;
        }
        .summary-card-divider {
            width: 100%;
            height: 1px;
        }
        .timeline-project-card-wrapper {
            padding: 20px;
            gap: 20px;
        }
        .timeline-card-header-block {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .timeline-metrics-subgrid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .timeline-project-title {
            font-size: 1.35rem;
        }
    }

    /* Gantt Chart Styles - desktop table + mobile cards */
    .timeline-gantt-section {
        background: #ffffff;
        border: 1px solid var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        padding: 20px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.01);
    }

    .desktop-gantt-chart {
        display: block;
    }

    .mobile-gantt-view {
        display: none;
    }

    .gantt-table-wrapper {
        position: relative;
        overflow: auto;
        max-height: 520px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
    }

    .gantt-table-wrapper.is-scrollable-right::after {
        content: '';
        position: sticky;
        top: 0;
        right: 0;
        float: right;
        width: 28px;
        height: 100%;
        margin-right: -28px;
        pointer-events: none;
        background: linear-gradient(to right, rgba(15, 23, 42, 0), rgba(15, 23, 42, 0.08));
    }

    .gantt-chart-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.8rem;
        min-width: 900px;
    }

    .gantt-chart-table thead th {
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 10px;
        text-align: center;
        font-weight: 700;
        color: #1e293b;
        background: #ffffff;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 3;
    }

    .gantt-info-header-row th {
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        top: 0;
    }

    .gantt-day-header-row th {
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 8px 4px;
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 600;
        top: 34px;
    }

    .gantt-col-month {
        text-align: center;
        font-weight: 800;
        color: #1e293b;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
    }

    .gantt-col-month.gantt-month-alt,
    .gantt-day-cell.gantt-month-alt {
        background: #f8fafc;
    }

    .gantt-day-cell {
        width: 18px;
        font-size: 0.65rem;
        color: #94a3b8;
        font-weight: 600;
        text-align: center;
        padding: 4px 2px;
    }

    .gantt-day-cell.is-today {
        background: rgba(16, 185, 129, 0.14);
        color: #0f766e;
        font-weight: 800;
        border-radius: 4px 4px 0 0;
    }

    /* Sticky left-hand info columns so phase/date/progress stay visible while
       scrolling horizontally through the day grid. */
    .sticky-col {
        position: sticky;
        z-index: 2;
        background: inherit;
    }

    .gantt-col-phase.sticky-col { left: 0; width: 180px; min-width: 180px; box-shadow: 1px 0 0 #e2e8f0; }
    .gantt-col-date.sticky-col:nth-of-type(2) { left: 180px; width: 100px; min-width: 100px; }
    .gantt-col-date.sticky-col:nth-of-type(3) { left: 280px; width: 100px; min-width: 100px; }
    .gantt-col-progress.sticky-col { left: 380px; width: 90px; min-width: 90px; box-shadow: 1px 0 0 #e2e8f0; }

    thead .sticky-col {
        z-index: 4;
    }

    .gantt-phase-row:nth-child(even) td {
        background: #fbfdfc;
    }

    .gantt-phase-row td {
        border-bottom: 1px solid #f1f5f9;
        padding: 14px 10px;
        vertical-align: middle;
        color: #1e293b;
        font-weight: 600;
        white-space: nowrap;
        background: #ffffff;
    }

    .gantt-phase-row:hover td {
        background: #f0fdf6;
    }

    .gantt-col-phase {
        font-weight: 700;
        color: #0f172a;
        text-align: left;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gantt-col-date {
        color: #64748b;
        font-weight: 500;
        font-size: 0.78rem;
    }

    .gantt-col-progress {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.85rem;
    }

    .gantt-col-timeline {
        position: relative;
        width: 9999px;
        min-width: 700px;
        padding: 18px 6px !important;
    }

    .gantt-timeline-cell {
        position: relative;
        height: 40px;
        background: repeating-linear-gradient(
            to right,
            transparent,
            transparent calc(100% / var(--gantt-total-days, 365) * 1 - 0.5px),
            #f8fafc calc(100% / var(--gantt-total-days, 365) * 1 - 0.5px),
            #f8fafc calc(100% / var(--gantt-total-days, 365) * 1)
        );
        border-radius: 8px;
    }

    .gantt-month-divider {
        position: absolute;
        top: -4px;
        bottom: -4px;
        width: 1px;
        background: #e2e8f0;
        z-index: 1;
    }

    .gantt-today-marker {
        position: absolute;
        top: -4px;
        bottom: -4px;
        width: 2px;
        background: #10b981;
        z-index: 4;
        border-radius: 2px;
    }

    .gantt-today-marker::before {
        content: 'TODAY';
        position: absolute;
        top: -16px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.55rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        color: #10b981;
        white-space: nowrap;
    }

    .gantt-bar {
        position: absolute;
        top: 6px;
        height: 28px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .gantt-bar-fill {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        background: #94a3b8;
        border-radius: inherit;
        transition: width 0.2s ease;
    }

    .gantt-bar.completed .gantt-bar-fill {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .gantt-bar.in-progress .gantt-bar-fill {
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
    }

    .gantt-bar.delayed .gantt-bar-fill {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }

    .gantt-bar:hover {
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);
        transform: translateY(-1px);
    }

    .gantt-bar-label {
        position: relative;
        z-index: 2;
        font-size: 0.72rem;
        font-weight: 800;
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        padding: 0 12px;
        line-height: 28px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .gantt-milestone {
        position: absolute;
        top: 50%;
        width: 16px;
        height: 16px;
        background: #f59e0b;
        border: 2px solid #ffffff;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        z-index: 3;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        cursor: pointer;
        display: grid;
        place-items: center;
        padding: 0;
        font-size: 0;
    }

    .gantt-milestone.completed {
        background: #10b981;
    }

    .gantt-milestone.delayed {
        background: #ef4444;
    }

    .gantt-milestone:hover {
        transform: translate(-50%, -50%) scale(1.2);
    }

    .gantt-milestone i {
        font-size: 8px;
        color: #ffffff;
        line-height: 1;
    }

    .status-pill-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.34rem 0.7rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .status-pill-badge.completed { background: #E8F5E9; color: #2E7D32; }
    .status-pill-badge.in-progress { background: #E3F2FD; color: #1565C0; }
    .status-pill-badge.planning,
    .status-pill-badge.upcoming { background: #FFF8E1; color: #B7791F; }
    .status-pill-badge.delayed { background: #FDECEC; color: #C62828; }
    .status-pill-badge.pending { background: #F3F4F6; color: #6B7280; }

    .gantt-status-guide {
        margin-top: 12px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
    }

    .gantt-status-guide .status-guide-title {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 10px;
    }

    .gantt-status-guide .status-guide-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .gantt-status-guide .status-guide-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 10px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
    }

    .gantt-status-guide .status-guide-swatch {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06);
    }

    .gantt-status-guide .status-guide-swatch-today {
        width: 3px;
        height: 14px;
        border-radius: 2px;
        background: #10b981;
        box-shadow: none;
    }

    .gantt-status-guide .status-guide-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* Mobile Gantt card status accent + mini-timeline */
    .mobile-gantt-card {
        border-left: 4px solid #94a3b8;
    }

    .mobile-gantt-meta {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }

    .mobile-gantt-card.status-accent-completed { border-left-color: #10b981; }
    .mobile-gantt-card.status-accent-in-progress { border-left-color: #3b82f6; }
    .mobile-gantt-card.status-accent-delayed { border-left-color: #ef4444; }
    .mobile-gantt-card.status-accent-planning,
    .mobile-gantt-card.status-accent-pending { border-left-color: #94a3b8; }

    .mobile-gantt-mini-timeline {
        margin: 4px 0 14px;
    }

    .mobile-mini-track {
        position: relative;
        height: 8px;
        border-radius: 999px;
        background: #f1f5f9;
        margin-bottom: 6px;
    }

    .mobile-mini-bar {
        position: absolute;
        top: 0;
        bottom: 0;
        border-radius: 999px;
        background: #94a3b8;
    }

    .mobile-mini-bar.completed { background: linear-gradient(90deg, #10b981, #34d399); }
    .mobile-mini-bar.in-progress { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .mobile-mini-bar.delayed { background: linear-gradient(90deg, #ef4444, #f87171); }

    .mobile-mini-today {
        position: absolute;
        top: -3px;
        bottom: -3px;
        width: 2px;
        background: #10b981;
        border-radius: 2px;
        z-index: 2;
    }

    .mobile-mini-milestone {
        position: absolute;
        top: 50%;
        width: 8px;
        height: 8px;
        background: #f59e0b;
        border: 1.5px solid #ffffff;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
        z-index: 3;
    }

    .mobile-mini-milestone.completed { background: #10b981; }
    .mobile-mini-milestone.delayed { background: #ef4444; }

    .mobile-mini-caption {
        font-size: 0.66rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
    }

    @media (max-width: 991px) {
        .desktop-gantt-chart {
            display: none !important;
        }
        .mobile-gantt-view {
            display: flex !important;
            flex-direction: column !important;
            gap: 16px !important;
        }

        .mobile-gantt-card {
            padding: 18px !important;
            border-radius: 20px !important;
        }

        .mobile-gantt-head {
            gap: 14px !important;
            margin-bottom: 16px !important;
            padding-bottom: 14px !important;
        }

        .mobile-gantt-title {
            font-size: 17px !important;
            line-height: 1.35 !important;
        }

        .mobile-gantt-meta {
            gap: 12px !important;
            margin-bottom: 16px !important;
        }

        .mobile-gantt-field {
            padding: 12px 14px !important;
            border-radius: 16px !important;
        }

        .mobile-gantt-label {
            font-size: 10px !important;
            letter-spacing: 0.1em !important;
            margin-bottom: 6px !important;
        }

        .mobile-gantt-value {
            font-size: 13.5px !important;
            line-height: 1.4 !important;
        }

        .mobile-gantt-progress-row {
            gap: 12px !important;
        }

        .mobile-gantt-track {
            height: 12px !important;
        }

        .mobile-gantt-percent {
            font-size: 13px !important;
            min-width: 44px !important;
        }

        .status-pill-badge {
            padding: 0.38rem 0.8rem !important;
            font-size: 0.78rem !important;
            border-radius: 999px !important;
        }

        .gantt-status-guide {
            padding: 18px !important;
            border-radius: 18px !important;
        }

        .gantt-status-guide .status-guide-item {
            padding: 8px 12px !important;
            border-radius: 12px !important;
        }

        .gantt-status-guide .status-guide-swatch {
            width: 14px !important;
            height: 14px !important;
        }

        .gantt-status-guide .status-guide-label {
            font-size: 0.85rem !important;
        }
    }

    @media (max-width: 480px) {
        .mobile-gantt-card {
            padding: 16px !important;
        }

        .mobile-gantt-title {
            font-size: 16px !important;
        }

        .mobile-gantt-value {
            font-size: 13px !important;
        }

        .mobile-gantt-percent {
            font-size: 12px !important;
            min-width: 40px !important;
        }

        .mobile-gantt-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .mobile-gantt-meta .mobile-gantt-field:nth-child(3) {
            grid-column: 1 / -1;
        }
    }

    @media (min-width: 992px) {
        .desktop-gantt-chart {
            display: block !important;
        }
        .mobile-gantt-view {
            display: none !important;
        }
    }

</style>

@push('scripts')
<script>
    /**
     * Switches the visible timeline panel when a project is chosen from the
     * toolbar dropdown. All project panels are rendered up-front, so switching
     * is instant (no page reload) - mirroring the supervisor timeline approach.
     */
    function switchClientTimeline(selectedProjectId) {
        // Show only the timeline panel for the selected project (all panels are
        // rendered up-front so the project dropdown can switch between them).
        document.querySelectorAll('.client-timeline-panel').forEach(function (panel) {
            panel.classList.add('is-hidden');
        });
        const target = document.getElementById('project-timeline-' + selectedProjectId);
        if (target) {
            target.classList.remove('is-hidden');
        }

        // Keep the selection reflected in the URL (so a reload keeps the same project)
        // and in the session, matching the rest of the Client portal behaviour.
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('project_id', selectedProjectId);
            window.history.replaceState({}, '', url.toString());
        } catch (err) {
            // Non-fatal - the active panel still switched on screen.
        }

        fetch("{{ route('client.dashboard.project.select', ['project' => '__ID__']) }}".replace('__ID__', encodeURIComponent(selectedProjectId)), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).catch(function () {
            // Non-fatal - selection survives via URL/localStorage/session elsewhere.
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Keep the milestone flag tooltips working when triggered via keyboard focus.
        document.querySelectorAll('.milestone-marker-wrapper').forEach(function (wrapper) {
            wrapper.addEventListener('mouseenter', function () {
                wrapper.classList.add('is-active');
            });
            wrapper.addEventListener('mouseleave', function () {
                wrapper.classList.remove('is-active');
            });
        });

        // Show a right-edge shadow on the Gantt table while there's more to
        // scroll horizontally, and hide it once fully scrolled.
        document.querySelectorAll('[data-gantt-scroll]').forEach(function (wrapper) {
            function updateScrollShadow() {
                const canScrollMore = wrapper.scrollWidth - wrapper.clientWidth - wrapper.scrollLeft > 4;
                wrapper.classList.toggle('is-scrollable-right', canScrollMore);
            }
            updateScrollShadow();
            wrapper.addEventListener('scroll', updateScrollShadow, { passive: true });
            window.addEventListener('resize', updateScrollShadow);
        });

        // Auto-center the horizontal scroll on today's marker so the current
        // date is visible on load instead of the project's start date.
        document.querySelectorAll('.gantt-today-marker').forEach(function (marker) {
            const wrapper = marker.closest('[data-gantt-scroll]');
            if (!wrapper) return;
            const wrapperRect = wrapper.getBoundingClientRect();
            const markerRect = marker.getBoundingClientRect();
            const markerRelativeLeft = (markerRect.left - wrapperRect.left) + wrapper.scrollLeft;
            const target = markerRelativeLeft - (wrapper.clientWidth / 2);
            wrapper.scrollLeft = Math.max(0, target);
        });
    });
</script>
@endpush
@endsection 