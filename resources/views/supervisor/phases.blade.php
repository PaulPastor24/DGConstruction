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

    $activePhase = $primaryPhase;
    $scheduleHealth = ($activePhase && $activePhase->status === 'delayed') ? 'DELAYED' : 'ON TRACK';
    $scheduleHealthClass = $scheduleHealth === 'ON TRACK' ? 'health-on-track' : 'health-delayed';
@endphp

@section('content')
<div class="phases-container">
    
    {{-- Top Metrics Bar --}}
    <div class="metrics-row">
        <div class="metric-card project-selector-card">
            <span class="metric-label">Project</span>
            <div class="project-dropdown-trigger" id="projectDropdownBtn">
                <span class="project-name" id="selectedProjectName">{{ $primaryProject?->project_name ?? 'Select Project' }}</span>
                <i class="bi bi-chevron-down dropdown-arrow"></i>
            </div>
            
            {{-- Dropdown Menu --}}
            <div class="project-dropdown-menu" id="projectDropdownMenu" style="display: none;">
                @forelse($assignedProjects as $project)
                    <a href="#" class="dropdown-item" data-project-id="{{ $project->project_id }}" data-project-name="{{ $project->project_name }}">
                        {{ $project->project_name }}
                    </a>
                @empty
                    <div class="dropdown-empty">No projects assigned</div>
                @endforelse
            </div>
        </div>

        <div class="metric-card progress-metric-card">
            <span class="metric-label">Overall Progress</span>
            <div class="metric-value-large" id="overallProgressValue">{{ $overallProgress }}%</div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="overallProgressBar" style="width: {{ $overallProgress }}%"></div>
            </div>
        </div>

        <div class="metric-card phase-metric-card">
            <span class="metric-label">Current Phase</span>
            <div class="current-phase-highlight">
                <div class="phase-icon-wrapper">
                    <i class="bi bi-buildings"></i>
                </div>
                <div class="phase-text-group">
                    <span class="phase-title-text" id="currentPhaseName">{{ $activePhase?->phase_name ?? 'None' }} (Current)</span>
                    <span class="phase-subtext" id="currentPhaseStatus">{{ $activePhase ? ($activePhase->status === 'in_progress' ? 'In Progress' : ucfirst(str_replace('_', ' ', $activePhase->status))) : 'No phase' }}</span>
                </div>
            </div>
        </div>

        <div class="metric-card health-metric-card">
            <span class="metric-label">Schedule Health</span>
            <div class="health-status-wrapper {{ $scheduleHealthClass }}" id="scheduleHealthWrapper">
                <div class="pulse-icon-container">
                    <svg class="pulse-svg" viewBox="0 0 50 30">
                        <path class="pulse-path" d="M0,15 L15,15 L20,5 L25,25 L30,12 L33,18 L36,15 L50,15" fill="none" stroke-width="2"/>
                    </svg>
                </div>
                <span class="health-text" id="scheduleHealthText"><i class="bi bi-plus"></i> {{ $scheduleHealth }}</span>
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
                <a href="{{ route('supervisor.timeline') }}?project_id={{ $primaryProject?->project_id ?? '' }}" class="btn-action-outline"><i class="bi bi-calendar3"></i> View Timeline</a>
            </div>
        </div>

        {{-- Filters and Search --}}
        <div class="filters-section" style="max-width: 420px; gap: 10px;">
            <input type="text" id="searchPhases" class="filter-input" placeholder="Search phases by name..." style="max-width: 220px;">
            <select id="statusFilter" class="filter-select" style="max-width: 160px;">
                <option value="">All Status</option>
                <option value="not_started">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="delayed">Delayed</option>
            </select>
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
                <tbody id="phasesTableBody">
                    @forelse($projectPhases as $phase)
                        @php
                            $isPhaseActive = $phase->phase_id == optional($activePhase)->phase_id;
                            
                            $iconClass = match(true) {
                                str_contains(strtolower($phase->phase_name), 'planning') => 'bi-file-earmark-text',
                                str_contains(strtolower($phase->phase_name), 'preparation') => 'bi-truck',
                                str_contains(strtolower($phase->phase_name), 'structural') => 'bi-buildings',
                                str_contains(strtolower($phase->phase_name), 'masonry') => 'bi-grid-3x3-gap',
                                str_contains(strtolower($phase->phase_name), 'finishing') => 'bi-paint-bucket',
                                default => 'bi-check2-circle'
                            };
                        @endphp
                        <tr class="{{ $isPhaseActive ? 'row-active-highlight' : '' }}" data-phase-id="{{ $phase->phase_id }}" data-phase-status="{{ $phase->status }}" data-phase-name="{{ strtolower($phase->phase_name) }}">
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
                                    <span class="progress-percent-value">{{ round((float) ($phase->completion_percentage ?? 0), 0) }}%</span>
                                    <div class="table-progress-track">
                                        <div class="table-progress-fill" style="width: {{ round((float) ($phase->completion_percentage ?? 0), 0) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="{{ $statusClass($phase->status) }}">{{ $statusLabel($phase->status) }}</span>
                            </td>
                            <td class="date-cell-text">{{ $formatDate($phase->planned_start_date) }}</td>
                            <td class="date-cell-text">{{ $formatDate($phase->planned_end_date) }}</td>
                            <td style="text-align: center;">
                                <button class="action-view-row-btn view-phase-details" data-phase-id="{{ $phase->phase_id }}" title="View phase" aria-label="View phase">
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
        
        {{-- Pagination --}}
        <div class="table-footer-pagination">
            <span class="pagination-summary-text">
                Showing {{ $projectPhases->firstItem() ?? 1 }} to {{ $projectPhases->lastItem() ?? $projectPhases->count() }} of {{ $projectPhases->total() }} phases
            </span>
            <div class="pagination-controls">
                @if($projectPhases->onFirstPage())
                    <button class="pag-btn" disabled><i class="bi bi-chevron-left"></i></button>
                @else
                    <a href="{{ $projectPhases->previousPageUrl() }}&project_id={{ $primaryProject?->project_id }}" class="pag-btn"><i class="bi bi-chevron-left"></i></a>
                @endif
                
                @for($i = 1; $i <= $projectPhases->lastPage(); $i++)
                    @if($i == $projectPhases->currentPage())
                        <button class="pag-btn pag-btn-active">{{ $i }}</button>
                    @else
                        <a href="{{ $projectPhases->url($i) }}&project_id={{ $primaryProject?->project_id }}" class="pag-btn">{{ $i }}</a>
                    @endif
                @endfor
                
                @if($projectPhases->hasMorePages())
                    <a href="{{ $projectPhases->nextPageUrl() }}&project_id={{ $primaryProject?->project_id }}" class="pag-btn"><i class="bi bi-chevron-right"></i></a>
                @else
                    <button class="pag-btn" disabled><i class="bi bi-chevron-right"></i></button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Phase Details Modal --}}
<div id="phaseDetailsModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalPhaseTitle">Phase Details</h2>
            <button class="modal-close-btn" id="modalCloseBtn">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="modal-loading">
                <div class="spinner"></div>
                <p>Loading phase details...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button id="modalCloseActionBtn" class="btn-modal-close">Close</button>
        </div>
    </div>
</div>

<style>
    :root {
        --ui-bg-surface: #FFFFFF;
        --ui-bg-app: #F8FAFC;
        --ui-border-color: #E2E8F0;
        --ui-text-main: #1E293B;
        --ui-text-muted: #64748B;
        --ui-theme-green: #2a4028;
        --ui-theme-green-light: #e8efe0;
        
        --status-comp-bg: #e8efe0;
        --status-comp-txt: #365233;
        --status-prog-bg: #DBEAFE;
        --status-prog-txt: #2563EB;
        --status-pend-bg: #F1F5F9;
        --status-pend-txt: #64748B;
        --status-delay-bg: #FEE2E2;
        --status-delay-txt: #DC2626;
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
        position: relative;
    }

    .metric-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--ui-text-muted);
        margin-bottom: 0.5rem;
    }

    .project-selector-card {
        position: relative;
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
        transition: transform 0.2s;
    }

    .project-dropdown-trigger:hover .dropdown-arrow {
        transform: rotate(180deg);
    }

    .project-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--ui-bg-surface);
        border: 1px solid var(--ui-border-color);
        border-radius: 8px;
        margin-top: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 100;
        overflow: hidden;
    }

    .dropdown-item {
        display: block;
        padding: 0.75rem 1rem;
        color: var(--ui-text-main);
        text-decoration: none;
        border-bottom: 1px solid var(--ui-border-color);
        transition: background 0.2s;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-item:hover {
        background: var(--ui-theme-green-light);
        color: var(--ui-theme-green);
        font-weight: 600;
    }

    .dropdown-empty {
        padding: 1rem;
        text-align: center;
        color: var(--ui-text-muted);
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
        transition: width 0.3s ease;
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

    .health-delayed .health-text {
        background: var(--status-delay-bg);
        color: var(--status-delay-txt);
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
        transition: background 0.2s;
    }

    .btn-action-solid:hover {
        background: #08493f;
    }

    /* Filters Section */
    .filters-section {
        display: flex;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--ui-border-color);
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-input {
        flex: 1;
        min-width: 200px;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--ui-border-color);
        border-radius: 8px;
        font-size: 0.85rem;
        color: var(--ui-text-main);
    }

    .filter-input::placeholder {
        color: var(--ui-text-muted);
    }

    .filter-input:focus {
        outline: none;
        border-color: var(--ui-theme-green);
        box-shadow: 0 0 0 2px rgba(11, 96, 84, 0.1);
    }

    .filter-select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--ui-border-color);
        border-radius: 8px;
        font-size: 0.85rem;
        color: var(--ui-text-main);
        background: var(--ui-bg-surface);
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--ui-theme-green);
    }

    .btn-filter-reset {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--ui-border-color);
        background: transparent;
        color: var(--ui-text-muted);
        border-radius: 8px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-filter-reset:hover {
        border-color: var(--ui-theme-green);
        color: var(--ui-theme-green);
        background: var(--ui-theme-green-light);
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

    /* Status Badges */
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

    .status-delayed {
        background: var(--status-delay-bg);
        color: var(--status-delay-txt);
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

    /* Pagination */
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
        text-decoration: none;
        transition: all 0.2s;
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

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content {
        background: var(--ui-bg-surface);
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid var(--ui-border-color);
    }

    .modal-header h2 {
        font-size: 1.25rem;
        color: var(--ui-text-main);
        margin: 0;
    }

    .modal-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--ui-text-muted);
        cursor: pointer;
        transition: color 0.2s;
    }

    .modal-close-btn:hover {
        color: var(--ui-text-main);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .spinner {
        border: 3px solid var(--ui-border-color);
        border-top: 3px solid var(--ui-theme-green);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .phase-details {
        display: grid;
        gap: 1rem;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        background: var(--ui-bg-app);
        border-radius: 8px;
    }

    .detail-label {
        font-weight: 600;
        color: var(--ui-text-muted);
    }

    .detail-value {
        color: var(--ui-text-main);
        font-weight: 500;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid var(--ui-border-color);
        display: flex;
        justify-content: flex-end;
    }

    .btn-modal-close {
        padding: 0.5rem 1rem;
        background: var(--ui-border-color);
        border: none;
        border-radius: 8px;
        color: var(--ui-text-main);
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-modal-close:hover {
        background: var(--ui-theme-green);
        color: white;
    }

    /* Responsive Design */
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
        .filters-section {
            flex-direction: column;
        }
        .filter-input,
        .filter-select {
            width: 100%;
        }
    }

    /* ======================================================================
       SUPERVISOR PHASES MOBILE POLISH
       Converts cramped tables into readable phase cards and reduces whitespace.
       ====================================================================== */
    @media (max-width: 820px) {
        .phases-container {
            gap: 14px !important;
            padding: 0 !important;
        }

        .metrics-row {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }

        .metric-card {
            min-height: 98px !important;
            padding: 13px !important;
            border-radius: 18px !important;
            box-shadow: 0 10px 24px rgba(15, 32, 21, 0.045) !important;
        }

        .project-selector-card {
            grid-column: 1 / -1 !important;
        }

        .metric-label {
            font-size: 10px !important;
            font-weight: 800 !important;
            letter-spacing: 0.07em !important;
            text-transform: uppercase !important;
            margin-bottom: 7px !important;
        }

        .project-name,
        .metric-value-large,
        .phase-title-text {
            font-size: 17px !important;
            line-height: 1.15 !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        .current-phase-highlight {
            align-items: flex-start !important;
        }

        .phase-icon-wrapper {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            border-radius: 12px !important;
        }

        .health-status-wrapper {
            align-items: center !important;
            gap: 8px !important;
        }

        .health-text {
            width: 100% !important;
            text-align: center !important;
            border-radius: 999px !important;
            padding: 7px 10px !important;
        }

        .table-section-container {
            border-radius: 20px !important;
            overflow: hidden !important;
            box-shadow: 0 12px 28px rgba(15, 32, 21, 0.055) !important;
        }

        .table-section-header {
            padding: 16px !important;
            gap: 12px !important;
        }

        .section-main-title {
            font-size: 18px !important;
            line-height: 1.2 !important;
        }

        .section-subtitle {
            font-size: 12px !important;
            line-height: 1.4 !important;
        }

        .header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            width: 100% !important;
            gap: 10px !important;
        }

        .btn-action-outline,
        .btn-action-solid {
            justify-content: center !important;
            width: 100% !important;
            min-height: 42px !important;
            border-radius: 13px !important;
            padding: 10px 8px !important;
            font-size: 12px !important;
            font-weight: 800 !important;
        }

        .filters-section {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            width: 100% !important;
            max-width: none !important;
            padding: 12px 16px !important;
            gap: 10px !important;
            border-top: 1px solid #edf2ee !important;
            border-bottom: 1px solid #edf2ee !important;
        }

        .filter-input,
        .filter-select {
            width: 100% !important;
            max-width: none !important;
            min-width: 0 !important;
            min-height: 42px !important;
            border-radius: 13px !important;
            font-size: 16px !important;
        }

        .table-responsive {
            overflow: visible !important;
            border: 0 !important;
        }

        .phases-data-table,
        .phases-data-table thead,
        .phases-data-table tbody,
        .phases-data-table tr,
        .phases-data-table th,
        .phases-data-table td {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .phases-data-table thead {
            display: none !important;
        }

        .phases-data-table tbody {
            display: grid !important;
            gap: 14px !important;
            padding: 14px !important;
            background: #fbfdfb !important;
        }

        .phases-data-table tbody tr[data-phase-id] {
            position: relative !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
            padding: 16px !important;
            border: 1px solid #dfece4 !important;
            border-radius: 20px !important;
            background: #ffffff !important;
            box-shadow: 0 10px 24px rgba(15, 32, 21, 0.045) !important;
        }

        .phases-data-table tbody tr.row-active-highlight {
            background: linear-gradient(180deg, #ffffff 0%, #f8fff9 100%) !important;
            border-color: #cfe4d3 !important;
        }

        .phases-data-table tbody tr[data-phase-id] td {
            padding: 0 !important;
            border: 0 !important;
            background: transparent !important;
            font-size: 13px !important;
            line-height: 1.35 !important;
        }

        .phases-data-table tbody tr[data-phase-id] td:nth-child(1) {
            position: absolute !important;
            top: 16px !important;
            left: 16px !important;
            width: auto !important;
            z-index: 2 !important;
        }

        .phases-data-table tbody tr[data-phase-id] td:nth-child(2) {
            grid-column: 1 / -1 !important;
            padding-left: 42px !important;
            padding-right: 0 !important;
            padding-bottom: 14px !important;
            margin-bottom: 2px !important;
            border-bottom: 1px solid #edf3ef !important;
        }

        .phases-data-table .phase-name-cell {
            align-items: flex-start !important;
            gap: 10px !important;
        }

        .phases-data-table .phase-cell-icon {
            display: none !important;
        }

        .phases-data-table .phase-name-string {
            display: block !important;
            color: #102015 !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            line-height: 1.28 !important;
            letter-spacing: -0.01em !important;
        }

        .phases-data-table .current-inline-tag {
            display: inline-block !important;
            margin-left: 4px !important;
            color: #166534 !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
        }

        .phases-data-table tbody tr[data-phase-id] td:nth-child(3) {
            display: none !important;
        }

        .phases-data-table tbody tr[data-phase-id] td:nth-child(4),
        .phases-data-table tbody tr[data-phase-id] td:nth-child(5),
        .phases-data-table tbody tr[data-phase-id] td:nth-child(6),
        .phases-data-table tbody tr[data-phase-id] td:nth-child(7) {
            min-height: 74px !important;
            padding: 11px !important;
            border: 1px solid #e7efe9 !important;
            border-radius: 16px !important;
            background: #fbfefb !important;
        }

        .phases-data-table tbody tr[data-phase-id] td:nth-child(4)::before,
        .phases-data-table tbody tr[data-phase-id] td:nth-child(5)::before,
        .phases-data-table tbody tr[data-phase-id] td:nth-child(6)::before,
        .phases-data-table tbody tr[data-phase-id] td:nth-child(7)::before {
            display: block !important;
            margin-bottom: 7px !important;
            color: #64748b !important;
            font-size: 9.5px !important;
            font-weight: 900 !important;
            letter-spacing: 0.075em !important;
            line-height: 1.2 !important;
            text-transform: uppercase !important;
        }

        .phases-data-table tbody tr[data-phase-id] td:nth-child(4)::before { content: 'Progress'; }
        .phases-data-table tbody tr[data-phase-id] td:nth-child(5)::before { content: 'Status'; }
        .phases-data-table tbody tr[data-phase-id] td:nth-child(6)::before { content: 'Start'; }
        .phases-data-table tbody tr[data-phase-id] td:nth-child(7)::before { content: 'End'; }

        .progress-cell-wrapper {
            max-width: none !important;
            gap: 7px !important;
        }

        .progress-percent-value {
            font-size: 18px !important;
            font-weight: 900 !important;
        }

        .table-progress-track {
            height: 7px !important;
        }

        .status-badge {
            max-width: 100% !important;
            border-radius: 999px !important;
            padding: 7px 10px !important;
            white-space: normal !important;
            font-size: 10.5px !important;
            line-height: 1.1 !important;
        }

        .date-cell-text {
            white-space: normal !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            color: #172033 !important;
        }

        .phases-data-table tbody tr[data-phase-id] td:nth-child(8) {
            grid-column: 1 / -1 !important;
            display: flex !important;
            justify-content: flex-end !important;
            padding-top: 2px !important;
        }

        .action-view-row-btn {
            width: 42px !important;
            height: 42px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid #dfece4 !important;
            border-radius: 13px !important;
            color: #365233 !important;
            background: #ffffff !important;
        }

        .empty-table-state {
            padding: 28px 16px !important;
            text-align: center !important;
            white-space: normal !important;
        }

        .table-footer-pagination {
            display: grid !important;
            grid-template-columns: 1fr !important;
            justify-items: center !important;
            gap: 12px !important;
            padding: 14px 16px !important;
        }

        .pagination-summary-text {
            text-align: center !important;
            font-size: 12px !important;
        }

        .modal-content {
            width: calc(100vw - 24px) !important;
            max-height: 84vh !important;
            border-radius: 20px !important;
        }

        .detail-row {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 4px !important;
        }
    }

    @media (max-width: 390px) {
        .metrics-row,
        .filters-section {
            grid-template-columns: 1fr !important;
        }

        .phases-data-table tbody tr[data-phase-id] {
            grid-template-columns: 1fr !important;
        }
    }

</style>

{{-- Include SweetAlert2 --}}
@if (!View::exists('layouts.includes.sweetalert2'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Project Dropdown
    const projectDropdownBtn = document.getElementById('projectDropdownBtn');
    const projectDropdownMenu = document.getElementById('projectDropdownMenu');
    const selectedProjectName = document.getElementById('selectedProjectName');
    const projectItems = document.querySelectorAll('.dropdown-item');

    projectDropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        projectDropdownMenu.style.display = projectDropdownMenu.style.display === 'none' ? 'block' : 'none';
    });

    projectItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const projectId = item.dataset.projectId;
            const projectName = item.dataset.projectName;
            
            // Show loading
            Swal.fire({
                title: 'Switching Project',
                html: 'Loading project data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
                allowEscapeKey: false
            });

            // Redirect with project_id
            window.location.href = `{{ route('supervisor.phases') }}?project_id=${projectId}`;
        });
    });

    // Close dropdown on outside click
    document.addEventListener('click', () => {
        projectDropdownMenu.style.display = 'none';
    });

    // View Details Modal
    const viewDetailsButtons = document.querySelectorAll('.view-phase-details');
    const phaseDetailsModal = document.getElementById('phaseDetailsModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalCloseActionBtn = document.getElementById('modalCloseActionBtn');
    const modalBody = document.getElementById('modalBody');

    viewDetailsButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const phaseId = btn.dataset.phaseId;
            
            // Show modal with loading state
            phaseDetailsModal.style.display = 'flex';
            modalBody.innerHTML = `
                <div class="modal-loading">
                    <div class="spinner"></div>
                    <p>Loading phase details...</p>
                </div>
            `;

            // Fetch phase details
            fetch(`{{ route('supervisor.api.phases.details', ':id') }}`.replace(':id', phaseId))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const phase = data.phase;
                        document.getElementById('modalPhaseTitle').textContent = phase.name;
                        
                        // Build modal body with management controls when allowed
                        const canManage = data.can_manage === true;

                        modalBody.innerHTML = `
                            <div class="phase-details">
                                <div class="detail-row">
                                    <span class="detail-label">Phase Order:</span>
                                    <span class="detail-value">#${phase.order}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">${phase.status.toUpperCase().replace('_', ' ')}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Progress:</span>
                                    <span class="detail-value">${Math.round(phase.completion_percentage)}%</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Project:</span>
                                    <span class="detail-value">${phase.project_name}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Planned Start:</span>
                                    <span class="detail-value">${phase.planned_start_date}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Planned End:</span>
                                    <span class="detail-value">${phase.planned_end_date}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Actual Start:</span>
                                    <span class="detail-value">${phase.actual_start_date}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Actual End:</span>
                                    <span class="detail-value">${phase.actual_end_date}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Milestones:</span>
                                    <span class="detail-value">${phase.completed_milestones}/${phase.milestones_count} completed</span>
                                </div>
                                ${canManage ? `
                                <div class="detail-row">
                                    <span class="detail-label">Current Progress:</span>
                                    <span class="detail-value">${Math.round(phase.completion_percentage)}%</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Change Status:</span>
                                    <span class="detail-value">
                                        <select id="modalStatusSelect" style="padding:6px;border-radius:6px;border:1px solid #e6ece9;">
                                            <option value="not_started" ${phase.status === 'not_started' ? 'selected' : ''}>Pending</option>
                                            <option value="in_progress" ${phase.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                            <option value="completed" ${phase.status === 'completed' ? 'selected' : ''}>Completed</option>
                                        </select>
                                        <button id="changeStatusBtn" class="btn-action-outline" style="margin-left:8px;">Change</button>
                                    </span>
                                </div>
                                ` : ''}
                            </div>
                        `;

                        // Wire up management actions if allowed
                        if (canManage) {
                            const changeStatusBtn = document.getElementById('changeStatusBtn');
                            const statusSelect = document.getElementById('modalStatusSelect');

                            changeStatusBtn.addEventListener('click', function() {
                                const selected = statusSelect.value;
                                // Confirmation
                                Swal.fire({
                                    title: 'Change phase status? ',
                                    text: 'This action will update the phase status.',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, change',
                                    confirmButtonColor: '#166534'
                                }).then(result => {
                                    if (!result.isConfirmed) return;
                                    Swal.fire({ title: 'Updating status', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                                    fetch(`{{ url('/supervisor/api/phases') }}/${phase.id}/update-status`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({ status: selected })
                                    }).then(r => r.json()).then(resp => {
                                        if (resp.success) {
                                            Swal.fire({ title: 'Updated', text: 'Phase status updated.', icon: 'success', confirmButtonColor: '#166534' });
                                            // Update row badge and current phase UI
                                            document.querySelectorAll(`[data-phase-id='${phase.id}'] .status-badge`).forEach(el => el.textContent = selected === 'in_progress' ? 'IN PROGRESS' : (selected === 'not_started' ? 'PENDING' : 'COMPLETED'));
                                            // Update overall progress
                                            if (resp.overallProgress !== undefined) {
                                                document.getElementById('overallProgressValue').textContent = resp.overallProgress + '%';
                                                document.getElementById('overallProgressBar').style.width = resp.overallProgress + '%';
                                            }
                                            // Update notification badge
                                            const nb2 = document.getElementById('notif-badge');
                                            if (nb2) {
                                                const val2 = parseInt(nb2.textContent || '0', 10) || 0;
                                                nb2.textContent = val2 + 1;
                                            } else {
                                                const link2 = document.querySelector('.topbar-icon');
                                                if (link2) {
                                                    const span2 = document.createElement('span');
                                                    span2.id = 'notif-badge';
                                                    span2.className = 'position-absolute';
                                                    span2.style.cssText = 'top:6px; right:6px; width:14px; height:14px; background:#166534; border-radius:999px; display:inline-block; border:2px solid #fff; font-size:0.7rem; line-height:10px; text-align:center; color:#fff;';
                                                    span2.textContent = '1';
                                                    link2.appendChild(span2);
                                                }
                                            }
                                            // Update current phase status text
                                            if (document.getElementById('currentPhaseName') && document.querySelector(`[data-phase-id='${phase.id}']`).classList.contains('row-active-highlight')) {
                                                document.getElementById('currentPhaseStatus').textContent = selected === 'in_progress' ? 'In Progress' : (selected === 'not_started' ? 'Pending' : 'Completed');
                                            }
                                        } else {
                                            Swal.fire({ title: 'Error', text: resp.message || 'Failed to update status', icon: 'error' });
                                        }
                                    }).catch(() => {
                                        Swal.fire({ title: 'Error', text: 'Request failed', icon: 'error' });
                                    });
                                });
                            });
                        }
                    } else {
                        modalBody.innerHTML = '<p style="color: red;">Error loading phase details</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = '<p style="color: red;">Error loading phase details</p>';
                });
        });
    });

    modalCloseBtn.addEventListener('click', () => {
        phaseDetailsModal.style.display = 'none';
    });

    modalCloseActionBtn.addEventListener('click', () => {
        phaseDetailsModal.style.display = 'none';
    });

    phaseDetailsModal.addEventListener('click', (e) => {
        if (e.target === phaseDetailsModal) {
            phaseDetailsModal.style.display = 'none';
        }
    });

    // Search and Filter
    const searchPhases = document.getElementById('searchPhases');
    const statusFilter = document.getElementById('statusFilter');
    const phasesTableBody = document.getElementById('phasesTableBody');

    function applyFilters() {
        const searchTerm = searchPhases.value.toLowerCase();
        const statusValue = statusFilter.value;
        const rows = phasesTableBody.querySelectorAll('tr[data-phase-id]');

        rows.forEach(row => {
            const phaseName = row.dataset.phaseName || '';
            const phaseStatus = row.dataset.phaseStatus || '';
            
            const matchesSearch = phaseName.includes(searchTerm);
            const matchesStatus = !statusValue || phaseStatus === statusValue;
            
            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchPhases.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
});
</script>

@endsection