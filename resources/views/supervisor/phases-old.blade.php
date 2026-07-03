@extends('layouts.supervisor')

@section('title', 'Construction Phases - Supervisor View')
@section('page_title', 'Construction Phases')

@php
    $formatDate = function ($value) {
        if (empty($value)) {
            return 'Pending';
        }
        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y');
        } catch (\Exception $e) {
            return 'Pending';
        }
    };

    $statusLabel = function ($status) {
        return match ($status) {
            'in_progress' => 'IN PROGRESS',
            'completed' => 'COMPLETED',
            'delayed' => 'DELAYED',
            'not_started' => 'PENDING',
            default => strtoupper(str_replace('_', ' ', (string) $status)),
        };
    };

    $statusClass = function ($status) {
        return match ($status) {
            'in_progress' => 'status-badge status-in-progress',
            'completed' => 'status-badge status-completed',
            'delayed' => 'status-badge status-delayed',
            default => 'status-badge status-pending',
        };
    };

    $projectPhases = $primaryProject ? $primaryProject->phases->sortBy('phase_order')->values() : collect();
    $activePhase = $primaryPhase ?: ($projectPhases->firstWhere('status', 'in_progress') ?? $projectPhases->first());
    
    $overallProgress = $projectPhases->isNotEmpty()
        ? round((float) $projectPhases->avg('completion_percentage'), 0)
        : 0;

    $scheduleHealth = $activePhase && $activePhase->status === 'delayed' ? 'DELAYED' : 'ON TRACK';
    $scheduleHealthClass = $scheduleHealth === 'ON TRACK' ? 'health-on-track' : 'health-delayed';
@endphp

@section('content')
<div class="phases-container">
    
    {{-- Top Metrics Bar --}}
    <div class="metrics-row">
        <div class="metric-card project-selector-card">
            <span class="metric-label">Project</span>
            <div class="project-dropdown-trigger">
                <span class="project-name">{{ $primaryProject?->project_name ?? 'Select Project' }}</span>
                <i class="bi bi-chevron-down dropdown-arrow"></i>
            </div>
        </div>

        <div class="metric-card progress-metric-card">
            <span class="metric-label">Overall Progress</span>
            <div class="metric-value-large">{{ $overallProgress }}%</div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: {{ $overallProgress }}%"></div>
            </div>
        </div>

        <div class="metric-card phase-metric-card">
            <span class="metric-label">Current Phase</span>
            <div class="current-phase-highlight">
                <div class="phase-icon-wrapper">
                    <i class="bi bi-buildings"></i>
                </div>
                <div class="phase-text-group">
                    <span class="phase-title-text">{{ $activePhase?->phase_name ?? 'None' }} (Current)</span>
                    <span class="phase-subtext">In Progress</span>
                </div>
            </div>
        </div>

        <div class="metric-card health-metric-card">
            <span class="metric-label">Schedule Health</span>
            <div class="health-status-wrapper {{ $scheduleHealthClass }}">
                <div class="pulse-icon-container">
                    <svg class="pulse-svg" viewBox="0 0 50 30">
                        <path class="pulse-path" d="M0,15 L15,15 L20,5 L25,25 L30,12 L33,18 L36,15 L50,15" fill="none" stroke-width="2"/>
                    </svg>
                </div>
                <span class="health-text"><i class="bi bi-plus"></i> {{ $scheduleHealth }}</span>
            </div>
        </div>
    </div>

    {{-- Construction Phases Table Section --}}
    <div class="table-section-container">
        <div class="table-section-header">
            <div class="header-left">
                <h2 class="section-main-title">Construction Phases</h2>
                <p class="section-subtitle">These phases are based on the construction_phases table.</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('supervisor.timeline') }}" class="btn-action-outline"><i class="bi bi-calendar3"></i> View Timeline</a>
                <button class="btn-action-solid"><i class="bi bi-download"></i> Export PDF</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="phases-data-table">
                <thead>
                    <tr>
                        <th style="width: 10%">PHASE ORDER</th>
                        <th style="width: 20%">PHASE NAME</th>
                        <th style="width: 25%">DESCRIPTION</th>
                        <th style="width: 15%">PROGRESS</th>
                        <th style="width: 12%">STATUS</th>
                        <th style="width: 10%">START DATE</th>
                        <th style="width: 10%">END DATE</th>
                        <th style="width: 8%; text-align: center;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectPhases as $phase)
                        @php
                            $isPhaseActive = $phase->phase_id == optional($activePhase)->phase_id;
                            
                            // Map generic text dynamic icons based on phase features/name
                            $iconClass = match(true) {
                                str_contains(strtolower($phase->phase_name), 'planning') => 'bi-file-earmark-text',
                                str_contains(strtolower($phase->phase_name), 'preparation') => 'bi-truck',
                                str_contains(strtolower($phase->phase_name), 'structural') => 'bi-buildings',
                                str_contains(strtolower($phase->phase_name), 'masonry') => 'bi-grid-3x3-gap',
                                str_contains(strtolower($phase->phase_name), 'finishing') => 'bi-paint-bucket',
                                default => 'bi-check2-circle'
                            };
                        @endphp
                        <tr class="{{ $isPhaseActive ? 'row-active-highlight' : '' }}">
                            <td>
                                <span class="order-badge">{{ $phase->phase_order }}</span>
                            </td>
                            <td>
                                <div class="phase-name-cell">
                                    <div class="phase-cell-icon">
                                        <i class="bi {{ $iconClass }}"></i>
                                    </div>
                                    <span class="phase-name-string {{ $isPhaseActive ? 'text-active-theme' : '' }}">
                                        {{ $phase->phase_name }} @if($isPhaseActive) <span class="current-inline-tag">(Current)</span> @endif
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="description-text-cell">
                                    Monitor delivery progress and milestone readiness for {{ strtolower($phase->phase_name) }}.
                                </span>
                            </td>
                            <td>
                                <div class="progress-cell-wrapper">
                                    <span class="progress-percent-value">{{ (float) ($phase->completion_percentage ?? 0) }}%</span>
                                    <div class="table-progress-track">
                                        <div class="table-progress-fill" style="width: {{ (float) ($phase->completion_percentage ?? 0) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="{{ $statusClass($phase->status) }}">{{ $statusLabel($phase->status) }}</span>
                            </td>
                            <td class="date-cell-text">{{ $formatDate($phase->planned_start_date) }}</td>
                            <td class="date-cell-text">{{ $formatDate($phase->planned_end_date) }}</td>
                            <td style="text-align: center;">
                                <button class="action-view-row-btn" title="View details" aria-label="View row items">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-table-state">
                                <i class="bi bi-patch-exclamation"></i> No phases assigned to this project yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="table-footer-pagination">
            <span class="pagination-summary-text">Showing 1 to {{ $projectPhases->count() }} of {{ $projectPhases->count() }} phases</span>
            <div class="pagination-controls">
                <button class="pag-btn" disabled><i class="bi bi-chevron-left"></i></button>
                <button class="pag-btn pag-btn-active">1</button>
                <button class="pag-btn" disabled><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --ui-bg-surface: #ffffff;
        --ui-bg-app: #f8faf9;
        --ui-border-color: #edf2f0;
        --ui-text-main: #1a2521;
        --ui-text-muted: #687973;
        --ui-theme-green: #0b6054;
        --ui-theme-green-light: #e8f5f1;
        
        --status-comp-bg: #eaf7ed;
        --status-comp-txt: #1e6133;
        --status-prog-bg: #eef9f5;
        --status-prog-txt: #0c695c;
        --status-pend-bg: #f1f4f3;
        --status-pend-txt: #5a6561;
    }

    body {
        background-color: var(--ui-bg-app);
        color: var(--ui-text-main);
    }

    .phases-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding: 0.5rem;
    }

    /* Top Metrics Bar Design */
    .metrics-row {
        display: grid;
        grid-template-columns: 1.2fr 1.2fr 1.4fr 1.1fr;
        gap: 1.25rem;
    }

    .metric-card {
        background: var(--ui-bg-surface);
        border: 1px solid var(--ui-border-color);
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 110px;
    }

    .metric-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--ui-text-muted);
        margin-bottom: 0.5rem;
    }

    .project-dropdown-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        padding-top: 0.25rem;
    }

    .project-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--ui-text-main);
    }

    .dropdown-arrow {
        color: var(--ui-text-muted);
        font-size: 1.1rem;
    }

    .metric-value-large {
        font-size: 2rem;
        font-weight: 700;
        color: var(--ui-text-main);
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background: #eef2f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: var(--ui-theme-green);
        border-radius: 999px;
    }

    .current-phase-highlight {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .phase-icon-wrapper {
        width: 42px;
        height: 42px;
        background: var(--ui-theme-green-light);
        color: var(--ui-theme-green);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .phase-text-group {
        display: flex;
        flex-direction: column;
    }

    .phase-title-text {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--ui-theme-green);
    }

    .phase-subtext {
        font-size: 0.8rem;
        color: var(--ui-text-muted);
    }

    .health-status-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.2rem;
    }

    .health-text {
        font-size: 0.85rem;
        font-weight: 700;
        padding: 0.35rem 0.65rem;
        border-radius: 6px;
    }

    .health-on-track .health-text {
        background: var(--status-comp-bg);
        color: var(--status-comp-txt);
    }

    .pulse-icon-container {
        width: 45px;
        height: 25px;
    }

    .pulse-path {
        stroke: #22c55e;
        fill: none;
        stroke-dasharray: 100;
        animation: pulseMock 4s linear infinite;
    }

    @keyframes pulseMock {
        0% { stroke-dashoffset: 200; }
        100% { stroke-dashoffset: 0; }
    }

    /* Core Data Table View Section */
    .table-section-container {
        background: var(--ui-bg-surface);
        border: 1px solid var(--ui-border-color);
        border-radius: 12px;
        padding: 1.5rem 0 0 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .table-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1.5rem 1.25rem 1.5rem;
    }

    .section-main-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--ui-text-main);
        margin: 0 0 0.25rem 0;
    }

    .section-subtitle {
        font-size: 0.85rem;
        color: var(--ui-text-muted);
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-action-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1rem;
        border: 1px solid #dcdfdc;
        background: transparent;
        color: #38423f;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-action-outline:hover {
        background: #f4f6f5;
        color: #38423f;
    }

    .btn-action-solid {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1rem;
        border: none;
        background: var(--ui-theme-green);
        color: #ffffff;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-action-solid:hover {
        background: #08493f;
    }

    /* Table Architecture */
    .phases-data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .phases-data-table th {
        background: #fbfcfa;
        border-top: 1px solid var(--ui-border-color);
        border-bottom: 1px solid var(--ui-border-color);
        padding: 0.85rem 1.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--ui-text-muted);
        letter-spacing: 0.03em;
    }

    .phases-data-table td {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid var(--ui-border-color);
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .row-active-highlight {
        background-color: #fafdfc;
    }

    .order-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1f4f2;
        color: #4a5451;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .row-active-highlight .order-badge {
        background: var(--ui-theme-green-light);
        color: var(--ui-theme-green);
    }

    .phase-name-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .phase-cell-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f4f7f5;
        color: var(--ui-text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }

    .row-active-highlight .phase-cell-icon {
        background: var(--ui-theme-green-light);
        color: var(--ui-theme-green);
    }

    .phase-name-string {
        font-weight: 600;
        color: var(--ui-text-main);
    }

    .text-active-theme {
        color: var(--ui-theme-green);
        font-weight: 700;
    }

    .current-inline-tag {
        font-size: 0.85rem;
        font-weight: 500;
        margin-left: 0.25rem;
    }

    .description-text-cell {
        color: var(--ui-text-muted);
        font-size: 0.85rem;
        line-height: 1.4;
        display: block;
    }

    .progress-cell-wrapper {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        max-width: 140px;
    }

    .progress-percent-value {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--ui-text-main);
    }

    .table-progress-track {
        width: 100%;
        height: 6px;
        background: #edf0ee;
        border-radius: 4px;
    }

    .table-progress-fill {
        height: 100%;
        background: var(--ui-theme-green);
        border-radius: 4px;
    }

    /* Status Badges Matching the Mockup */
    .status-badge {
        display: inline-block;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .status-completed {
        background: var(--status-comp-bg);
        color: var(--status-comp-txt);
    }

    .status-in-progress {
        background: var(--status-prog-bg);
        color: var(--status-prog-txt);
    }

    .status-pending {
        background: var(--status-pend-bg);
        color: var(--status-pend-txt);
    }

    .date-cell-text {
        color: var(--ui-text-main);
        font-weight: 500;
        white-space: nowrap;
    }

    .action-view-row-btn {
        background: transparent;
        border: none;
        color: #92a19c;
        font-size: 1.1rem;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        transition: color 0.2s;
    }

    .action-view-row-btn:hover {
        color: var(--ui-theme-green);
    }

    /* Minimalist Pagination Component */
    .table-footer-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--ui-border-color);
    }

    .pagination-summary-text {
        font-size: 0.85rem;
        color: var(--ui-text-muted);
    }

    .pagination-controls {
        display: flex;
        gap: 0.35rem;
    }

    .pag-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--ui-border-color);
        background: #ffffff;
        border-radius: 6px;
        color: var(--ui-text-main);
        font-size: 0.85rem;
        cursor: pointer;
    }

    .pag-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pag-btn-active {
        background: var(--ui-theme-green);
        color: #ffffff;
        border-color: var(--ui-theme-green);
        font-weight: 600;
    }

    .empty-table-state {
        text-align: center;
        color: var(--ui-text-muted);
        padding: 3rem !important;
    }

    /* Screen Breakpoints */
    @media (max-width: 1100px) {
        .metrics-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .metrics-row {
            grid-template-columns: 1fr;
        }
        .table-section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>
@endsection