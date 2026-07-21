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
    });
</script>
@endpush
@endsection