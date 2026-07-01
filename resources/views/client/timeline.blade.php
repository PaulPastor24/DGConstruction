@extends('layouts.client')

@section('title', 'Project Timeline - Client View')

@section('content')
<div class="container-fluid p-0">
    @include('client.partials.page-header', [
        'eyebrow' => 'Project Flow',
        'title' => 'Timeline',
        'description' => 'Monitor milestone timelines and project phase evolution across your active sites.',
    ])

    @if(isset($projectsWithStats) && count($projectsWithStats) > 0)
        <div class="timeline-summary-panel-row mb-4">
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
                        <article class="timeline-project-card-wrapper" id="project-timeline-{{ data_get($project, 'id') }}">
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
                                <div class="progress-track-rail">
                                    <span class="progress-fill-bar" style="width: {{ (float) data_get($project, 'progress', 0) }}%;"></span>
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

    /* --- RESPONSIVE WORKSPACE SCALING --- */
    @media (max-width: 1199px) {
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

    @media (max-width: 767px) {
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
@endsection