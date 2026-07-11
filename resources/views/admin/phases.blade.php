@extends('layouts.admin')

@section('title', 'Phase Management - D&G Construction Monitor')
@section('page_title', 'Phase Management')

@push('styles')
<!-- Custom Styles to precise match UI colors and spacing -->
<style>
    .bg-forest-green { background-color: var(--brand-green) !important; color: var(--surface) !important; }
    .btn-forest-green { background-color: var(--brand-green) !important; color: var(--surface) !important; border: 1px solid var(--brand-green); }
    .btn-forest-green:hover { background-color: var(--brand-dark) !important; color: var(--surface) !important; }
    .text-forest-green { color: var(--brand-green) !important; }
    .bg-forest-light { background-color: rgba(54,82,51,0.08) !important; color: var(--brand-green) !important; }
    
    .status-completed { background-color: #e6f6ee !important; color: #16a34a !important; border: 1px solid #d1fae5; }
    .status-inprogress { background-color: #eff6ff !important; color: #2563eb !important; border: 1px solid #dbeafe; }
    .status-pending { background-color: #f1f5f9 !important; color: #64748b !important; border: 1px solid #e2e8f0; }
    .status-delayed { background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fee2e2; }

    .metric-card {
        border: 1px solid rgba(4, 90, 51, 0.12);
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: linear-gradient(135deg, #ffffff 0%, #f8fcf9 100%);
        box-shadow: 0 12px 24px rgba(15, 32, 21, 0.06);
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(15, 32, 21, 0.1);
    }
    .metric-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(4, 90, 51, 0.08) !important;
        color: #045a33 !important;
        border: 1px solid rgba(4, 90, 51, 0.14);
    }
    .metric-icon i { color: #045a33 !important; }
    .phase-table {
        width: 100%;
        min-width: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.92rem;
        text-align: left;
        background: transparent;
        table-layout: fixed;
        margin-bottom: 0;
    }
    .phase-table th,
    .phase-table td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        line-height: 1.35;
        word-break: break-word;
        white-space: normal;
        font-family: 'DM Sans', sans-serif;
    }
    .phase-table th {
        background: #f8fafc;
        font-weight: 700;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.76rem;
    }
    .phase-table td {
        border-bottom: 1px solid #eef2f7;
        color: #334155;
        background: #ffffff;
        font-size: 0.92rem;
    }
    .phase-table th:first-child,
    .phase-table td:first-child {
        width: 64px;
        min-width: 64px;
        padding-left: 0.9rem;
        padding-right: 0.9rem;
    }
    .phase-table th:nth-child(2),
    .phase-table td:nth-child(2) {
        width: 24%;
        min-width: 210px;
    }
    .phase-table th:nth-child(3),
    .phase-table td:nth-child(3),
    .phase-table th:nth-child(4),
    .phase-table td:nth-child(4) {
        width: 17.5%;
        min-width: 175px;
    }
    .phase-table th:nth-child(5),
    .phase-table td:nth-child(5) {
        width: 108px;
        min-width: 108px;
        text-align: center;
    }
    .phase-table th:nth-child(6),
    .phase-table td:nth-child(6) {
        width: 104px;
        min-width: 104px;
        text-align: center;
    }
    .phase-table th:last-child,
    .phase-table td:last-child {
        width: 110px;
        min-width: 110px;
        text-align: center;
        padding-left: 1rem;
        padding-right: 1.35rem;
    }
    .phase-table .action-cell {
        text-align: center;
        padding-right: 1.35rem;
    }
    .phase-table td .phase-name-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
        line-height: 1.35;
    }
    .phase-table td .phase-name-meta {
        color: #64748b;
        font-size: 0.775rem;
    }
    .schedule-stack {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.3;
    }
    .schedule-chip {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }
    .schedule-separator {
        color: #cbd5e1;
        font-size: 0.7rem;
    }
    .progress-cell {
        display: inline-flex;
        min-width: 0;
        gap: 0.35rem;
        justify-content: center;
        align-items: center;
        margin: 0 auto;
        width: fit-content;
        max-width: 100%;
    }
    .progress-cell .progress {
        min-width: 58px;
        max-width: 90px;
        flex: 1 1 auto;
        height: 5px;
    }
    .progress-value {
        min-width: 28px;
        text-align: right;
        font-size: 0.72rem;
        color: #0f172a;
        font-weight: 700;
        line-height: 1;
    }
    .phase-table tbody tr:hover { background-color: #f8fcf9; }
    .phase-table .table-responsive { border-radius: 16px; }
    .order-badge { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 50%; }
    .table-pagination-strip {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        margin-top: 1rem;
        padding: 0.9rem 1rem;
        width: 100%;
        box-sizing: border-box;
    }
    .pagination-bar {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
    }
    .pagination-button {
        padding: 0.5rem 0.85rem;
        min-width: 2.4rem;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .pagination-button:not(.active):hover:not(.disabled) {
        background: #f3f4f6;
        border-color: #cbd5e1;
    }
    .pagination-button.active {
        background: #166534;
        border-color: #166534;
        color: #ffffff;
    }
    .pagination-button.active:hover:not(.disabled) {
        background: #134e4a;
        border-color: #134e4a;
    }
    .pagination-button.disabled {
        cursor: not-allowed;
        opacity: 0.55;
        background: #f9fafb;
        border-color: #e5e7eb;
    }
    .pagination-summary {
        color: #64748b;
        font-size: 0.9rem;
    }
    .admin-data-card { border: 1px solid rgba(28, 107, 67, 0.12); border-radius: 18px; box-shadow: 0 16px 40px rgba(15, 32, 21, 0.06); overflow: hidden; }
    .admin-data-header { background: linear-gradient(135deg, #f4fcf6 0%, #ffffff 100%); border-bottom: 1px solid rgba(28, 107, 67, 0.08); }
    
    .sidebar-card { border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0; }
    .sidebar-header-bg { background: linear-gradient(135deg, #045a33 0%, #034426 100%); min-height: 60px; position: relative; }
    .sidebar-icon-floating { position: absolute; bottom: -20px; left: 20px; background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }

    #pg-phases {
        --color-primary: #0f2015;
        --color-subtitle: #5f6f66;
        --border-color: #dfe7e0;
        --bg-light: #f4f7f3;
        --theme-accent: #0f2015;
        --theme-accent-soft: #e8efe9;
        --theme-accent-strong: #173a25;
        --theme-accent-bright: #1c6b43;
        --theme-accent-deep: #123c26;
        color: var(--color-primary);
        width: 100%;
    }
    #pg-phases *, #pg-phases *::before, #pg-phases *::after {
        box-sizing: border-box;
    }
    #pg-phases .phase-table,
    #pg-phases .phase-table th,
    #pg-phases .phase-table td,
    #pg-phases .details-sidebar-card,
    #pg-phases .phase-detail-content,
    #pg-phases .phase-detail-empty-state,
    #pg-phases .phase-detail-content .text-dark,
    #pg-phases .phase-detail-content .text-secondary,
    #pg-phases .phase-detail-content .text-muted,
    #pg-phases .details-sidebar-card .sidebar-title {
        font-family: 'DM Sans', sans-serif !important;
    }
    #pg-phases .phase-detail-content .details-section-title {
        font-family: 'DM Sans', sans-serif !important;
    }

    .reports-header {
        margin-bottom: 1.5rem;
    }

    /* ===== Workspace Split Layout ===== */
    .workspace-layout {
        display: flex;
        align-items: flex-start;
        gap: 0;
        width: 100%;
        min-width: 0;
        transition: gap 0.35s ease;
    }
    .workspace-layout.is-panel-open {
        gap: 1.25rem;
    }
    .workspace-left-col {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        min-width: 0;
        width: 100%;
        margin: 0 auto;
    }

    /* Right Side: Sticky Details Panel */
    .details-sidebar-card {
        background: #f8fafc;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 28px;
        padding: 0;
        width: 0;
        max-width: 360px;
        min-width: 0;
        flex: 0 0 0;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateX(24px);
        position: sticky;
        top: 1.5rem;
        box-shadow: 0 28px 90px rgba(15, 32, 21, 0.08);
        transition: width 0.35s ease, flex-basis 0.35s ease, padding 0.35s ease, opacity 0.25s ease, transform 0.35s ease;
        align-self: start;
    }
    .details-sidebar-card.is-open {
        width: 360px;
        flex: 0 0 360px;
        padding: 1.75rem;
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0);
    }
    .phase-detail-content {
        min-height: 100%;
        overflow-y: auto;
        transition: opacity 0.2s ease, transform 0.25s ease;
    }
    .phase-detail-content.is-switching {
        opacity: 0;
        transform: translateY(10px);
    }
    .details-sidebar-card::before {
        content: '';
        display: block;
        height: 5px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(134, 239, 172, 0.95), rgba(34, 197, 94, 0.95));
        margin-bottom: 1rem;
    }
    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .sidebar-label {
        margin: 0 0 0.35rem 0 !important;
        font-size: 0.72rem !important;
        letter-spacing: 0.12em !important;
        text-transform: uppercase !important;
        font-weight: 700 !important;
        color: #16a34a !important;
        font-family: 'DM Sans', sans-serif !important;
    }
    .sidebar-title {
        font-size: 1.18rem !important;
        font-weight: 600 !important;
        margin: 0 !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
        max-width: 18rem;
        font-family: 'DM Sans', sans-serif !important;
    }
    .btn-close-sidebar {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 1.1rem;
    }

    @media (max-width: 1400px) {
        .workspace-layout {
            flex-wrap: wrap;
        }
        .details-sidebar-card {
            position: static;
            max-width: 100%;
        }
        .details-sidebar-card.is-open {
            width: 100%;
            flex: 1 1 100%;
        }
    }

    .phase-detail-empty-state {
        display: grid;
        justify-items: center;
        text-align: center;
        gap: 0.5rem;
        padding: 1rem 0 0.2rem;
        color: #64748b;
    }

    .avatar-pill-large {
        width: 46px;
        height: 46px;
        font-size: 1rem;
    }
    .reports-header h4 {
        font-family: 'Syne', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.25rem 0;
        color: var(--theme-accent);
        letter-spacing: 0.01em;
    }
    .reports-header p {
        color: var(--color-subtitle);
        margin: 0;
        font-size: 0.875rem;
    }

    .project-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-color: #f8fafc;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4.22 6.22a.75.75 0 0 1 1.06 0L8 9.94l2.72-3.72a.75.75 0 1 1 1.06 1.06l-3.25 4.44a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.7rem center;
        background-size: 0.9rem;
        color: #0f172a;
        min-width: 220px;
        padding: 0.55rem 2.1rem 0.55rem 0.85rem;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.03);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .project-select:hover {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
    }
    .project-select:focus {
        box-shadow: 0 0 0 3px rgba(4, 90, 51, 0.12);
        border-color: rgba(4, 90, 51, 0.25);
        outline: none;
    }

    body {
        overflow-y: auto !important;
    }

    .content {
        overflow: visible !important;
        overflow-x: hidden !important;
        min-height: auto !important;
    }

    #pg-phases {
        padding-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<div id="pg-phases">
    <div class="reports-header">
        <h4>Phase Management</h4>
        <p>Create, organize, and monitor construction phases for each project.</p>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-forest-light rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-building fs-5 text-forest-green"></i>
                </div>
                <div>
                    <label class="text-muted d-block uppercase tracking-wider mb-1" style="font-size: 10px; font-weight: 600; letter-spacing: 0.08em;">Select Project</label>
                    <select class="fw-semibold text-dark project-select" onchange="window.location='{{ route('admin.phases') }}?project_id='+this.value" style="width: auto; font-size: 15px;">
                        @foreach($projects as $projectOption)
                            <option value="{{ $projectOption->project_id }}" {{ ($selectedProject && $selectedProject->project_id == $projectOption->project_id) ? 'selected' : '' }}>
                                {{ $projectOption->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.phases.export.pdf', ['project_id' => $selectedProject?->project_id]) }}" class="btn btn-light bg-white border text-secondary px-3 d-flex align-items-center gap-2 fw-medium btn-sm rounded-2">
                    <i class="bi bi-download"></i> Export PDF
                </a>
                <button id="openPhaseModalBtn" class="btn btn-forest-green px-3 d-flex align-items-center gap-2 fw-medium btn-sm rounded-2" type="button">
                    <i class="bi bi-plus-lg"></i> Add New Phase
                </button>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
        <div class="col">
            <div class="card h-100 bg-white metric-card p-3 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-layers fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Total Phases</span>
                        <h3 id="totalPhasesCount" class="mb-0 fw-bold text-dark">{{ $stats['total'] }}</h3>
                        <span class="text-muted" style="font-size: 11px;">All phases in this project</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-white metric-card p-3 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-play-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">In Progress</span>
                        <h3 id="inProgressPhasesCount" class="mb-0 fw-bold text-dark">{{ $stats['in_progress'] }}</h3>
                        <span class="text-muted" style="font-size: 11px;">Currently ongoing phases</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-white metric-card p-3 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Completed</span>
                        <h3 id="completedPhasesCount" class="mb-0 fw-bold text-dark">{{ $stats['completed'] }}</h3>
                        <span class="text-muted" style="font-size: 11px;">Successfully completed</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-white metric-card p-3 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Pending</span>
                        <h3 id="pendingPhasesCount" class="mb-0 fw-bold text-dark">{{ $stats['pending'] }}</h3>
                        <span class="text-muted" style="font-size: 11px;">Not yet started</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 bg-white metric-card p-3 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="metric-icon">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Delayed</span>
                        <h3 id="delayedPhasesCount" class="mb-0 fw-bold text-dark">{{ $stats['delayed'] }}</h3>
                        <span class="text-muted" style="font-size: 11px;">Behind schedule</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="workspace-layout" id="phasesWorkspaceLayout">
        <div class="workspace-left-col">

            <div class="card border-0 shadow-sm p-3 mb-0 rounded-3 bg-white">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4 position-relative">
                        <i class="bi bi-search text-muted position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                        <input id="phaseSearchInput" type="text" class="form-control ps-5 rounded-2 bg-light border-0 py-2 text-muted" placeholder="Search phase name..." style="font-size: 13px;">
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <select id="phaseStatusFilter" class="form-select rounded-2 bg-light border-0 py-2 text-muted" style="font-size: 13px;">
                            <option value="all">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="in_progress">In Progress</option>
                            <option value="delayed">Delayed</option>
                            <option value="not_started">Pending</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2">
                        <select id="phaseProgressFilter" class="form-select rounded-2 bg-light border-0 py-2 text-muted" style="font-size: 13px;">
                            <option value="all">All Progress</option>
                            <option value="0-24">0% - 24%</option>
                            <option value="25-49">25% - 49%</option>
                            <option value="50-74">50% - 74%</option>
                            <option value="75-100">75% - 100%</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-4 col-md-4 ms-auto">
                        <div class="d-flex align-items-center justify-content-md-end gap-2">
                            <span class="text-muted text-nowrap" style="font-size: 12px;">Sort By</span>
                            <select id="phaseSortSelect" class="form-select rounded-2 bg-light border-0 py-2 text-dark fw-medium" style="font-size: 13px; max-width: 180px;">
                                <option value="order_asc">Phase Order (Asc)</option>
                                <option value="order_desc">Phase Order (Desc)</option>
                                <option value="name_asc">Name (A-Z)</option>
                                <option value="name_desc">Name (Z-A)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 admin-data-card bg-white">
                <div class="card-header bg-white border-0 py-3 px-4 admin-data-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 15px;">Construction Phases</h5>
                            <p class="text-muted mb-0 small">Track each phase, status, and completion progress</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 phase-table">
                        <thead>
                            <tr>
                                <th class="ps-4 text-center">Order</th>
                                <th>Phase Name</th>
                                <th class="text-center">Planned Schedule</th>
                                <th class="text-center">Actual Schedule</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th class="action-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="phaseTableBody" style="font-size: 13px;">
                            @forelse($phases as $phase)
                                @php
                                    $statusClass = match($phase->status) {
                                        'completed' => 'status-completed',
                                        'in_progress' => 'status-inprogress',
                                        'delayed' => 'status-delayed',
                                        default => 'status-pending',
                                    };
                                    $statusLabel = match($phase->status) {
                                        'completed' => 'Completed',
                                        'in_progress' => 'In Progress',
                                        'delayed' => 'Delayed',
                                        'not_started' => 'Pending',
                                        default => ucfirst(str_replace('_', ' ', $phase->status)),
                                    };
                                    $progressValue = (float) ($phase->completion_percentage ?? 0);
                                    $progressClass = $progressValue >= 100 ? 'bg-success' : ($phase->status === 'in_progress' ? 'bg-primary' : ($phase->status === 'delayed' ? 'bg-warning' : 'bg-secondary'));
                                    $startDate = optional($phase->planned_start_date)->format('M d, Y') ?? 'Not set';
                                    $endDate = optional($phase->planned_end_date)->format('M d, Y') ?? 'Not set';
                                    $actualStartDate = optional($phase->actual_start_date)->format('M d, Y') ?? 'Not started';
                                    $actualEndDate = optional($phase->actual_end_date)->format('M d, Y') ?? 'In progress';
                                    $milestonesPayload = $phase->milestones->map(function ($milestone) {
                                        $milestoneStatus = $milestone->is_completed ? 'Completed' : ($milestone->is_delayed ? 'Delayed' : 'Pending');
                                        $milestoneClass = $milestone->is_completed ? 'text-success' : ($milestone->is_delayed ? 'text-danger' : 'text-muted');
                                        return [
                                            'name' => $milestone->milestone_name,
                                            'status' => $milestoneStatus,
                                            'class' => $milestoneClass,
                                            'icon' => $milestone->is_completed ? 'bi-check-circle-fill text-success' : ($milestone->is_delayed ? 'bi-exclamation-triangle-fill text-danger' : 'bi-circle text-muted'),
                                        ];
                                    })->values();
                                    $detailPayload = [
                                        'phase_id' => $phase->phase_id,
                                        'project_id' => $phase->project_id,
                                        'phase_name' => $phase->phase_name,
                                        'status' => $phase->status,
                                        'status_label' => $statusLabel,
                                        'status_class' => $statusClass,
                                        'phase_order' => $phase->phase_order,
                                        'planned_start_date' => $startDate,
                                        'planned_end_date' => $endDate,
                                        'planned_start_date_raw' => optional($phase->planned_start_date)->format('Y-m-d') ?? '',
                                        'planned_end_date_raw' => optional($phase->planned_end_date)->format('Y-m-d') ?? '',
                                        'actual_start_date' => $actualStartDate,
                                        'actual_end_date' => $actualEndDate,
                                        'actual_start_date_raw' => optional($phase->actual_start_date)->format('Y-m-d') ?? '',
                                        'actual_end_date_raw' => optional($phase->actual_end_date)->format('Y-m-d') ?? '',
                                        'completion_percentage' => number_format($progressValue, 0),
                                        'completion_percentage_raw' => $progressValue,
                                        'progress_value' => number_format($progressValue, 0),
                                        'progress_percent' => min(100, max(0, $progressValue)),
                                        'progress_bar_class' => $progressClass,
                                        'description' => $phase->description ?? 'No description available for this phase yet.',
                                        'milestones' => $milestonesPayload,
                                        'updated_at' => optional($phase->updated_at)->format('M d, Y h:i A') ?? 'Not available',
                                        'project_name' => optional($phase->project)->project_name ?? optional($selectedProject)->project_name ?? 'N/A',
                                    ];
                                @endphp
                                <tr data-phase-row="true" data-phase-id="{{ $phase->phase_id }}" data-phase-name="{{ strtolower($phase->phase_name) }}" data-phase-status="{{ $phase->status }}" data-phase-progress="{{ (float) ($phase->completion_percentage ?? 0) }}" data-phase-order="{{ (int) $phase->phase_order }}" data-phase-title="{{ strtolower($phase->phase_name . ' ' . ($phase->project->project_name ?? '')) }}">
                                    <td class="ps-4 text-center">
                                        <div class="order-badge bg-forest-light text-forest-green mx-auto">{{ $phase->phase_order }}</div>
                                    </td>
                                    <td>
                                        <span class="phase-name-title d-block">{{ $phase->phase_name }}</span>
                                        <small class="phase-name-meta d-block text-truncate" style="max-width: 180px;">{{ $phase->project->project_name ?? 'Project phase' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="schedule-stack">
                                            <span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> {{ $startDate }}</span>
                                            <span class="schedule-separator"><i class="bi bi-arrow-right"></i></span>
                                            <span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> {{ $endDate }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="schedule-stack">
                                            <span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> {{ $actualStartDate }}</span>
                                            <span class="schedule-separator"><i class="bi bi-arrow-right"></i></span>
                                            <span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> {{ $actualEndDate }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center progress-cell">
                                            <div class="progress flex-grow-1" style="background-color: #f1f5f9; border-radius: 4px;">
                                                <div class="progress-bar {{ $progressClass }}" style="width: {{ min(100, max(0, $progressValue)) }}%;"></div>
                                            </div>
                                            <span class="progress-value">{{ number_format($progressValue, 0) }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <span class="badge {{ $statusClass }} px-2 py-1 rounded-pill fw-medium" style="font-size: 11px;">{{ $statusLabel }}</span>
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        <button class="btn btn-sm btn-light border p-1 text-primary rounded js-phase-edit-btn" type="button" title="Edit" data-phase-edit='@json($detailPayload)'><i class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-light border p-1 text-success rounded js-phase-view-btn" type="button" title="View" data-phase-details='@json($detailPayload)'><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-light border p-1 text-danger rounded js-phase-delete-btn" type="button" title="Delete" data-phase-delete-url="{{ route('admin.phases.destroy', ['project' => $phase->project_id, 'phase' => $phase->phase_id]) }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="phaseTableEmptyState">
                                    <td colspan="7" class="text-center py-5 text-muted">No phases found for this project yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top-0 py-3">
                    <div class="table-pagination-strip">
                        <div id="phaseResultsCount" class="pagination-summary">
                            Showing {{ $phases->firstItem() ?? 0 }} to {{ $phases->lastItem() ?? 0 }} of {{ $phases->total() }} phase(s)
                        </div>
                        <div class="pagination-bar">
                            <a class="pagination-button {{ $phases->onFirstPage() ? 'disabled' : '' }}" href="{{ $phases->previousPageUrl() ?? '#' }}" aria-label="Previous page">‹</a>
                            @for ($page = 1; $page <= $phases->lastPage(); $page++)
                                <a class="pagination-button {{ $page == $phases->currentPage() ? 'active' : '' }}" href="{{ $phases->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endfor
                            <a class="pagination-button {{ $phases->hasMorePages() ? '' : 'disabled' }}" href="{{ $phases->nextPageUrl() ?? '#' }}" aria-label="Next page">›</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Details Sidebar -->
        <div class="details-sidebar-card" id="phaseDetailsSidebarCard" aria-hidden="true">
            <div id="phaseDetailContent" class="phase-detail-content">
                <div class="phase-detail-empty-state">
                    <div class="avatar-pill avatar-pill-large">?</div>
                    <div class="fw-semibold text-dark">Choose a phase to inspect its full details.</div>
                    <div class="text-muted small">The selected record will open here with a smooth transition.</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Create / Edit Phase Modal (Description Input Removed) -->
    <div class="modal fade" id="phaseModal" tabindex="-1" aria-labelledby="phaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 620px;">
            <div class="modal-content border-0 shadow-lg rounded-4" style="background: #ffffff;">
                <form id="phaseForm" method="post" action="{{ route('admin.phases.store') }}" data-create-route="{{ route('admin.phases.store') }}" data-update-route-template="{{ route('admin.phases.update', ['project' => '__PROJECT__', 'phase' => '__PHASE__']) }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="phaseFormMethod">
                    <input type="hidden" name="project_id" value="{{ optional($selectedProject)->project_id ?? '' }}" id="projectIdInput">
                    <input type="hidden" name="phase_id" id="phaseIdInput">
                    
                    <!-- Modal Header -->
                    <div class="modal-header border-0 px-4 pt-4 pb-2">
                        <div>
                            <h4 class="modal-title fw-bold text-dark mb-1" id="phaseModalLabel" style="font-size: 1.35rem; letter-spacing: -0.01em;">Add New Construction Phase</h4>
                            <p class="text-muted mb-0" id="phaseModalSubtitle" style="font-size: 0.85rem;">Create a new construction phase for the selected project.</p>
                        </div>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.85rem;"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body px-4 py-3">
                        <!-- Section 1: Phase Information -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 py-1 px-2 rounded bg-forest-light border-0" style="width: fit-content;">
                                <i class="bi bi-file-earmark-text fw-bold text-forest-green small"></i>
                                <span class="fw-bold text-forest-green text-uppercase tracking-wider" id="sectionLabelInfo" style="font-size: 0.68rem; letter-spacing: 0.08em;">PHASE INFORMATION</span>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-12 col-md-7">
                                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size: 0.8rem;">Phase Name <span class="text-danger">*</span></label>
                                    <input name="phase_name" id="phaseNameInput" class="form-control px-3 shadow-none bg-white border" placeholder="Enter phase name" style="height: 44px; border-radius: 8px; font-size: 0.88rem;" required>
                                </div>
                                <div class="col-12 col-md-5">
                                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size: 0.8rem;">Phase Order <span class="text-danger">*</span></label>
                                    <input name="phase_order" id="phaseOrderInput" type="number" min="1" class="form-control px-3 shadow-none bg-white border" placeholder="Enter order number" style="height: 44px; border-radius: 8px; font-size: 0.88rem;" required>
                                    <div class="form-text mt-1 text-muted" style="font-size: 0.75rem;">Set the sequence of this phase</div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- Section 2: Schedule -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3 py-1 px-2 rounded bg-forest-light border-0" style="width: fit-content;">
                                <i class="bi bi-calendar3 fw-bold text-forest-green small"></i>
                                <span class="fw-bold text-forest-green text-uppercase tracking-wider" style="font-size: 0.68rem; letter-spacing: 0.08em;">SCHEDULE</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size: 0.8rem;">Planned Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="planned_start_date" id="plannedStartDateInput" class="form-control px-3 shadow-none bg-white border" style="height: 44px; border-radius: 8px; font-size: 0.88rem;" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size: 0.8rem;">Planned End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="planned_end_date" id="plannedEndDateInput" class="form-control px-3 shadow-none bg-white border" style="height: 44px; border-radius: 8px; font-size: 0.88rem;" required>
                                </div>
                                <div class="col-12 col-md-6" id="actualStartDateColumn" style="display: none;">
                                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size: 0.8rem;">Actual Start Date</label>
                                    <input type="date" name="actual_start_date" id="actualStartDateInput" class="form-control px-3 shadow-none bg-white border" style="height: 44px; border-radius: 8px; font-size: 0.88rem;" />
                                </div>
                                <div class="col-12 col-md-6" id="actualEndDateColumn" style="display: none;">
                                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size: 0.8rem;">Actual End Date</label>
                                    <input type="date" name="actual_end_date" id="actualEndDateInput" class="form-control px-3 shadow-none bg-white border" style="height: 44px; border-radius: 8px; font-size: 0.88rem;" />
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1 fw-semibold text-secondary" id="durationLabel" style="font-size: 0.8rem;">Duration</label>
                                    <input type="text" id="durationDisplayInput" class="form-control px-3 shadow-none border-0 text-dark fw-medium" value="0 days" style="height: 44px; border-radius: 8px; font-size: 0.88rem; background-color: #f1f5f9;" readonly disabled>
                                    <div class="form-text mt-1 text-muted" id="durationSubtext" style="font-size: 0.75rem;">Duration will be calculated automatically</div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- Section 3: Status Layer -->
                        <div class="mb-2" id="phaseStatusSection" style="display: none;">
                            <div class="d-flex align-items-center gap-2 mb-3 py-1 px-2 rounded bg-forest-light border-0" style="width: fit-content;">
                                <i class="bi bi-flag fw-bold text-forest-green small"></i>
                                <span class="fw-bold text-forest-green text-uppercase tracking-wider" id="sectionLabelStatus" style="font-size: 0.68rem; letter-spacing: 0.08em;">STATUS</span>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label mb-1 fw-semibold text-secondary" style="font-size: 0.8rem;">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="phaseStatusInput" class="form-select px-3 shadow-none bg-white border" style="height: 44px; border-radius: 8px; font-size: 0.88rem;" required>
                                        <option value="not_started">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="delayed">Delayed</option>
                                    </select>
                                    <div class="form-text mt-1 text-muted" id="statusSubtext" style="font-size: 0.75rem;">Set the current status of this phase</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3" id="completionVisibleRow" style="display: none;">
                            <div class="col-12">
                                <label class="form-label mb-2 fw-semibold text-secondary" style="font-size: 0.8rem;">Current Progress</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 12px; border-radius: 8px; background-color: #f1f5f9;">
                                            <div id="completionPercentageBar" class="progress-bar bg-success" role="progressbar" style="width: 0%; border-radius: 8px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span id="completionPercentageLabel" class="fw-semibold text-dark">0%</span>
                                            <span class="text-muted" style="font-size: 0.75rem;">Progress is automatically updated based on approved accomplishment reports.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-light border px-4 shadow-sm fw-medium rounded-2" data-bs-dismiss="modal" style="height: 40px; font-size: 0.88rem; background: #ffffff; color: #334155;">Cancel</button>
                        <button type="submit" id="phaseFormSubmitBtn" class="btn btn-forest-green px-4 shadow-sm fw-medium rounded-2 d-flex align-items-center gap-2" style="height: 40px; font-size: 0.88rem;">
                            <i class="bi bi-file-earmark-plus" id="submitBtnIcon"></i> <span id="submitBtnText">Save Phase</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const phaseFlashSuccess = @json(session('success'));
        const phaseFlashSuccessTitle = @json(session('success_title'));
        const phaseFlashError = @json($errors->first());

        if (phaseFlashSuccess) {
            Swal.fire({
                title: phaseFlashSuccessTitle || 'Success',
                text: phaseFlashSuccess,
                icon: 'success',
                confirmButtonColor: '#045a33',
            });
        } else if (phaseFlashError) {
            Swal.fire({
                title: 'Validation error',
                text: phaseFlashError,
                icon: 'warning',
                confirmButtonColor: '#045a33',
            });
        }
        const workspaceLayout = document.getElementById('phasesWorkspaceLayout');
        const detailsSidebarCard = document.getElementById('phaseDetailsSidebarCard');
        const detailsPanel = document.getElementById('phaseDetailContent');
        const searchInput = document.getElementById('phaseSearchInput');
        const statusFilter = document.getElementById('phaseStatusFilter');
        const progressFilter = document.getElementById('phaseProgressFilter');
        const sortSelect = document.getElementById('phaseSortSelect');
        const phaseRows = Array.from(document.querySelectorAll('#phaseTableBody > tr[data-phase-row="true"]'));
        const phaseTableBody = document.getElementById('phaseTableBody');
        const emptyStateRow = document.getElementById('phaseTableEmptyState');
        const resultsCount = document.getElementById('phaseResultsCount');

        if (!workspaceLayout || !detailsSidebarCard || !detailsPanel) {
            return;
        }

        const FALLBACK_MARKUP = `
            <div class="phase-detail-empty-state">
                <div class="avatar-pill avatar-pill-large">?</div>
                <div class="fw-semibold text-dark">Choose a phase to inspect its full details.</div>
                <div class="text-muted small">The selected record will open here with a smooth transition.</div>
            </div>
        `;

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');

        function setDetailsPanelOpen(isOpen) {
            workspaceLayout.classList.toggle('is-panel-open', isOpen);
            detailsSidebarCard.classList.toggle('is-open', isOpen);
            detailsSidebarCard.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        function beginDetailsTransition() {
            detailsPanel.classList.remove('is-switching');
            void detailsPanel.offsetWidth;
            detailsPanel.classList.add('is-switching');
        }

        function finishDetailsTransition() {
            window.requestAnimationFrame(() => {
                detailsPanel.classList.remove('is-switching');
            });
        }

        function closeDetailsPanel() {
            setDetailsPanelOpen(false);
            detailsPanel.innerHTML = FALLBACK_MARKUP;
        }

        function applyPhaseFilters() {
            const searchTerm = (searchInput?.value || '').trim().toLowerCase();
            const statusValue = statusFilter?.value || 'all';
            const progressValue = progressFilter?.value || 'all';
            const sortValue = sortSelect?.value || 'order_asc';

            let visibleRows = phaseRows.filter((row) => {
                const name = (row.dataset.phaseName || '').toLowerCase();
                const title = (row.dataset.phaseTitle || '').toLowerCase();
                const status = row.dataset.phaseStatus || '';
                const progress = Number(row.dataset.phaseProgress || 0);

                const matchesSearch = !searchTerm || name.includes(searchTerm) || title.includes(searchTerm);
                const matchesStatus = statusValue === 'all' || status === statusValue;
                let matchesProgress = true;

                if (progressValue !== 'all') {
                    const [min, max] = progressValue.split('-').map(Number);
                    matchesProgress = progress >= min && progress <= max;
                }

                return matchesSearch && matchesStatus && matchesProgress;
            });

            if (sortValue === 'order_desc') {
                visibleRows.sort((a, b) => Number(b.dataset.phaseOrder || 0) - Number(a.dataset.phaseOrder || 0));
            } else if (sortValue === 'name_asc') {
                visibleRows.sort((a, b) => (a.dataset.phaseName || '').localeCompare(b.dataset.phaseName || ''));
            } else if (sortValue === 'name_desc') {
                visibleRows.sort((a, b) => (b.dataset.phaseName || '').localeCompare(a.dataset.phaseName || ''));
            } else {
                visibleRows.sort((a, b) => Number(a.dataset.phaseOrder || 0) - Number(b.dataset.phaseOrder || 0));
            }

            if (phaseTableBody) {
                const fragment = document.createDocumentFragment();
                visibleRows.forEach((row) => {
                    row.style.display = '';
                    fragment.appendChild(row);
                });

                if (emptyStateRow) {
                    emptyStateRow.style.display = visibleRows.length > 0 ? 'none' : '';
                    if (visibleRows.length === 0) {
                        fragment.appendChild(emptyStateRow);
                    }
                }

                phaseTableBody.replaceChildren(fragment);
            }

            if (resultsCount) {
                const projectName = '{{ optional($selectedProject)->project_name ?? "this project" }}';
                resultsCount.textContent = `Showing ${visibleRows.length} phase(s) for ${projectName}`;
            }
        }

        function renderPhaseDetails(payload) {
            const milestonesMarkup = Array.isArray(payload.milestones) && payload.milestones.length > 0
                ? payload.milestones.map((milestone) => `
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-medium text-dark">${escapeHtml(milestone.name)}</span>
                        <span class="${escapeHtml(milestone.class || 'text-muted')} d-flex align-items-center gap-1">
                            ${escapeHtml(milestone.status || 'Pending')}
                            <i class="bi ${escapeHtml(milestone.icon || 'bi-circle text-muted')}"></i>
                        </span>
                    </div>
                `).join('')
                : '<p class="text-muted mb-0" style="font-size: 12px;">No milestones recorded for this phase yet.</p>';

            detailsPanel.innerHTML = `
                <div class="sidebar-header">
                    <div>
                        <p class="sidebar-label">Phase Details</p>
                        <h2 class="sidebar-title">${escapeHtml(payload.phase_name || 'Phase details')}</h2>
                    </div>
                    <button class="btn-close-sidebar js-close-phase-panel" type="button"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <span class="badge ${escapeHtml(payload.status_class || 'status-pending')} px-2 py-1 rounded" style="font-size: 11px;">${escapeHtml(payload.status_label || 'Pending')}</span>
                </div>

                <div class="d-flex flex-column gap-2.5 mb-4" style="font-size: 13px;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-list-ol me-2"></i>Phase Order</span>
                        <span class="fw-bold text-dark">${escapeHtml(payload.phase_order || '—')}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Planned Start Date</span>
                        <span class="fw-semibold text-dark">${escapeHtml(payload.planned_start_date || 'Not set')}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-calendar-check me-2"></i>Planned End Date</span>
                        <span class="fw-semibold text-dark">${escapeHtml(payload.planned_end_date || 'Not set')}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-calendar2-week me-2"></i>Actual Start Date</span>
                        <span class="fw-semibold text-dark">${escapeHtml(payload.actual_start_date || 'Not started')}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-calendar2-check me-2"></i>Actual End Date</span>
                        <span class="fw-semibold text-dark">${escapeHtml(payload.actual_end_date || 'In progress')}</span>
                    </div>
                    <div class="d-flex flex-column pt-1">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted"><i class="bi bi-speedometer2 me-2"></i>Progress</span>
                            <span class="fw-bold text-dark">${escapeHtml(payload.progress_value || '0')}%</span>
                        </div>
                        <div class="progress" style="height: 6px; background-color: #e2e8f0; border-radius: 4px;">
                            <div class="progress-bar ${escapeHtml(payload.progress_bar_class || 'bg-success')}" style="width: ${escapeHtml(payload.progress_percent || '0')}%;"></div>
                        </div>
                    </div>
                </div>

                <hr class="text-muted opacity-25">

                <div class="mb-4">
                    <h6 class="details-section-title text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Description</h6>
                    <p class="text-secondary mb-0" style="font-size: 12px; line-height: 1.6;">${escapeHtml(payload.description || 'No description available for this phase yet.')}</p>
                </div>

                <hr class="text-muted opacity-25">

                <div class="mb-4">
                    <h6 class="details-section-title text-uppercase text-muted fw-bold mb-3" style="font-size: 10px; letter-spacing: 0.5px;">Associated Milestones (${escapeHtml(String(Array.isArray(payload.milestones) ? payload.milestones.length : 0))})</h6>
                    <div class="d-flex flex-column gap-2" style="font-size: 12px;">${milestonesMarkup}</div>
                </div>

                <hr class="text-muted opacity-25">

                <div class="d-flex flex-column gap-2" style="font-size: 11px;">
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px;">Updated</span>
                        <span class="text-dark fw-medium"><i class="bi bi-clock me-1"></i> ${escapeHtml(payload.updated_at || 'Not available')}</span>
                    </div>
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px;">Project</span>
                        <span class="text-dark fw-medium"><i class="bi bi-building me-1"></i> ${escapeHtml(payload.project_name || 'N/A')}</span>
                    </div>
                </div>
            `;

            finishDetailsTransition();
        }

        const phaseModalEl = document.getElementById('phaseModal');
        const phaseModalLabel = document.getElementById('phaseModalLabel');
        const phaseForm = document.getElementById('phaseForm');
        const phaseFormMethod = document.getElementById('phaseFormMethod');
        const selectedProjectId = '{{ optional($selectedProject)->project_id ?? "" }}';
        const createPhaseUrl = phaseForm?.dataset.createRoute || '{{ route("admin.phases.store") }}';
        const updateRouteTemplate = phaseForm?.dataset.updateRouteTemplate || '{{ route("admin.phases.update", ["project" => "__PROJECT__", "phase" => "__PHASE__"]) }}';
        const phaseModalInstance = phaseModalEl ? new bootstrap.Modal(phaseModalEl, { backdrop: 'static' }) : null;
        const phaseIdInput = document.getElementById('phaseIdInput');
        const phaseNameInput = document.getElementById('phaseNameInput');
        const phaseOrderInput = document.getElementById('phaseOrderInput');
        const phaseStatusInput = document.getElementById('phaseStatusInput');
        const plannedStartDateInput = document.getElementById('plannedStartDateInput');
        const plannedEndDateInput = document.getElementById('plannedEndDateInput');
        const actualStartDateInput = document.getElementById('actualStartDateInput');
        const actualEndDateInput = document.getElementById('actualEndDateInput');
        const actualStartDateColumn = document.getElementById('actualStartDateColumn');
        const actualEndDateColumn = document.getElementById('actualEndDateColumn');
        const completionVisibleRow = document.getElementById('completionVisibleRow');
        const completionPercentageBar = document.getElementById('completionPercentageBar');
        const completionPercentageLabel = document.getElementById('completionPercentageLabel');
        let previousStatusValue = null;
        let currentPhaseStatus = null;
        let activeCompletionValue = 0;

        function clearPhaseFormErrors() {
            phaseForm.querySelectorAll('.form-control, .form-select').forEach((control) => {
                control.classList.remove('is-invalid');
            });
        }

        function renderPhaseFormErrors(errors) {
            clearPhaseFormErrors();

            const firstError = Object.values(errors || {}).flat().find(Boolean);
            if (firstError) {
                Swal.fire({
                    title: 'Validation error',
                    text: firstError,
                    icon: 'warning',
                    confirmButtonColor: '#045a33',
                });
            }

            Object.entries(errors || {}).forEach(([field, messages]) => {
                const control = phaseForm.querySelector(`[name="${field}"]`);
                if (control) {
                    control.classList.add('is-invalid');
                }
            });
        }

        function submitPhaseForm(event) {
            event.preventDefault();

            clearPhaseFormErrors();

            const projectInput = document.getElementById('projectIdInput');
            const projectId = (projectInput?.value || selectedProjectId || '').toString();
            const phaseId = (phaseIdInput?.value || '').toString();

            if (projectInput && !projectInput.value) {
                projectInput.value = projectId;
            }

            const isEdit = phaseFormMethod.value === 'PUT';
            const method = isEdit ? 'PUT' : 'POST';
            phaseFormMethod.value = method;

            if (isEdit && projectId && phaseId) {
                phaseForm.setAttribute('action', updateRouteTemplate.replace('__PROJECT__', encodeURIComponent(projectId)).replace('__PHASE__', encodeURIComponent(phaseId)));

                // Perform AJAX update for edits
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const formData = new FormData(phaseForm);
                formData.set('_method', 'PUT');

                fetch(phaseForm.getAttribute('action'), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                })
                .then(async (res) => {
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const firstError = payload?.errors ? Object.values(payload.errors).flat()[0] : payload.message || 'Unable to update phase.';
                        Swal.fire({ title: 'Validation error', text: firstError, icon: 'warning', confirmButtonColor: '#045a33' });
                        return;
                    }

                    if (payload.success) {
                        const updated = payload.phase;
                        updatePhaseRow(updated);
                        updateDashboardCounters();

                        if (payload.auto_completed) {
                            Swal.fire({ title: 'Phase Completed', text: 'The phase has automatically been marked as Completed because progress reached 100%.', icon: 'success', confirmButtonColor: '#045a33' });
                        } else {
                            Swal.fire({ title: 'Success', text: payload.message || 'Phase updated successfully.', icon: 'success', confirmButtonColor: '#045a33' });
                        }

                        phaseModalInstance.hide();
                    }
                })
                .catch((err) => {
                    Swal.fire({ title: 'Unexpected Server Error', text: err.message || 'An unexpected error occurred.', icon: 'error', confirmButtonColor: '#045a33' });
                });
            } else {
                phaseForm.setAttribute('action', createPhaseUrl);
                // Non-AJAX create: submit normally to keep existing flow and server-side flash handling
                phaseForm.submit();
            }
        }

        function openPhaseModal(mode, payload = null) {
            if (!phaseModalInstance) {
                return;
            }

            const isEdit = mode === 'edit';
            phaseForm.reset();

            const projectIdForAction = (payload?.project_id || selectedProjectId || '').toString();
            document.getElementById('projectIdInput').value = projectIdForAction;
            
            phaseModalLabel.textContent = isEdit ? 'Edit Construction Phase' : 'Add New Construction Phase';
            document.getElementById('phaseModalSubtitle').textContent = isEdit ? 'Update the details of this construction phase.' : 'Create a new construction phase for the selected project.';
            const phaseStatusSection = document.getElementById('phaseStatusSection');
            if (phaseStatusSection) {
                phaseStatusSection.style.display = isEdit ? 'block' : 'none';
            }
            document.getElementById('statusSubtext').textContent = isEdit ? 'Set the current status of this phase' : 'Set the current status of this phase';
            document.getElementById('durationLabel').textContent = isEdit ? 'Duration (Calculated)' : 'Duration';
            document.getElementById('durationSubtext').textContent = isEdit ? '' : 'Duration will be calculated automatically';
            
            if (isEdit && projectIdForAction && payload?.phase_id) {
                phaseForm.setAttribute('action', updateRouteTemplate.replace('__PROJECT__', encodeURIComponent(projectIdForAction)).replace('__PHASE__', encodeURIComponent(payload.phase_id)));
            } else {
                phaseForm.setAttribute('action', createPhaseUrl);
            }
            phaseFormMethod.value = isEdit ? 'PUT' : 'POST';
            
            phaseIdInput.value = payload?.phase_id || '';
            phaseNameInput.value = payload?.phase_name || '';
            phaseOrderInput.value = payload?.phase_order || '';
            phaseStatusInput.value = payload?.status || 'not_started';
            currentPhaseStatus = payload?.status || null;
            plannedStartDateInput.value = payload?.planned_start_date_raw || '';
            plannedEndDateInput.value = payload?.planned_end_date_raw || '';
            actualStartDateInput.value = payload?.actual_start_date_raw || '';
            actualEndDateInput.value = payload?.actual_end_date_raw || '';
            const completionValue = Number(payload?.completion_percentage_raw ?? 0);
            activeCompletionValue = completionValue;
            if (completionPercentageBar) {
                completionPercentageBar.style.width = `${completionValue}%`;
                completionPercentageBar.setAttribute('aria-valuenow', String(completionValue));
            }
            if (completionPercentageLabel) {
                completionPercentageLabel.textContent = `${Math.round(completionValue)}%`;
            }

            const submitBtnText = document.getElementById('submitBtnText');
            const submitBtnIcon = document.getElementById('submitBtnIcon');
            if (isEdit) {
                submitBtnText.textContent = 'Save Changes';
                submitBtnIcon.className = 'bi bi-floppy';
            } else {
                submitBtnText.textContent = 'Save Phase';
                submitBtnIcon.className = 'bi bi-file-earmark-plus';
            }

            clearPhaseFormErrors();
            calculateDuration();

            // Show/hide completion visible row and lock status for completed phases
            if (completionVisibleRow) {
                completionVisibleRow.style.display = isEdit ? 'flex' : 'none';
            }

            if (actualStartDateColumn) {
                actualStartDateColumn.style.display = isEdit ? 'block' : 'none';
            }
            if (actualEndDateColumn) {
                actualEndDateColumn.style.display = isEdit ? 'block' : 'none';
            }

            if (phaseStatusInput) {
                previousStatusValue = phaseStatusInput.value;
            }

            phaseModalInstance.show();
        }

        function calculateDuration() {
            const startVal = plannedStartDateInput.value;
            const endVal = plannedEndDateInput.value;
            const displayField = document.getElementById('durationDisplayInput');
            
            if (startVal && endVal) {
                const start = new Date(startVal);
                const end = new Date(endVal);
                const diffTime = end - start;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (!isNaN(diffDays) && diffDays >= 0) {
                    displayField.value = `${diffDays} days`;
                    return;
                }
            }
            displayField.value = '0 days';
        }

        plannedStartDateInput.addEventListener('change', calculateDuration);
        plannedEndDateInput.addEventListener('change', calculateDuration);

        function updateCompletionDisplay(value) {
            const percentage = Math.min(100, Math.max(0, Number(value || 0)));
            if (completionPercentageBar) {
                completionPercentageBar.style.width = `${percentage}%`;
                completionPercentageBar.setAttribute('aria-valuenow', String(percentage));
            }
            if (completionPercentageLabel) {
                completionPercentageLabel.textContent = `${Math.round(percentage)}%`;
            }
        }

        if (phaseStatusInput) {
            phaseStatusInput.addEventListener('change', function (ev) {
                const newVal = ev.target.value;
                const currentProgress = Number(activeCompletionValue || 0);

                if (newVal === 'completed' && currentProgress < 100) {
                    Swal.fire({
                        title: 'Cannot Complete Phase',
                        text: 'The phase progress must reach 100% before it can be marked as Completed.',
                        icon: 'warning',
                        confirmButtonColor: '#045a33',
                    });
                    ev.target.value = previousStatusValue || 'in_progress';
                    return;
                }

                if (currentPhaseStatus === 'completed' && newVal !== 'completed') {
                    Swal.fire({
                        title: 'Phase Locked',
                        text: 'Completed phases cannot be reverted to another status.',
                        icon: 'warning',
                        confirmButtonColor: '#045a33',
                    });
                    ev.target.value = 'completed';
                    return;
                }

                previousStatusValue = ev.target.value;
            });
        }

        if (phaseForm) {
            phaseForm.addEventListener('submit', submitPhaseForm);
            phaseForm.addEventListener('input', clearPhaseFormErrors);
            phaseForm.addEventListener('change', clearPhaseFormErrors);
        }

        function mapStatusClassAndLabel(status) {
            switch (status) {
                case 'completed': return { class: 'status-completed', label: 'Completed' };
                case 'in_progress': return { class: 'status-inprogress', label: 'In progress' };
                case 'delayed': return { class: 'status-delayed', label: 'Delayed' };
                default: return { class: 'status-pending', label: 'Pending' };
            }
        }

        function updateDashboardCounters() {
            const rows = Array.from(document.querySelectorAll('#phaseTableBody > tr[data-phase-row="true"]')).filter((row) => row.style.display !== 'none');
            const counts = {
                total: rows.length,
                completed: 0,
                in_progress: 0,
                pending: 0,
                delayed: 0,
            };

            rows.forEach((row) => {
                const status = row.dataset.phaseStatus || '';
                if (status === 'completed') counts.completed += 1;
                else if (status === 'in_progress') counts.in_progress += 1;
                else if (status === 'delayed') counts.delayed += 1;
                else if (status === 'not_started') counts.pending += 1;
            });

            const totalEl = document.getElementById('totalPhasesCount');
            const inProgressEl = document.getElementById('inProgressPhasesCount');
            const completedEl = document.getElementById('completedPhasesCount');
            const pendingEl = document.getElementById('pendingPhasesCount');
            const delayedEl = document.getElementById('delayedPhasesCount');

            if (totalEl) totalEl.textContent = counts.total;
            if (inProgressEl) inProgressEl.textContent = counts.in_progress;
            if (completedEl) completedEl.textContent = counts.completed;
            if (pendingEl) pendingEl.textContent = counts.pending;
            if (delayedEl) delayedEl.textContent = counts.delayed;
        }

        function progressBarClass(percent) {
            const p = Number(percent || 0);
            if (p >= 100) return 'bg-success';
            if (p >= 75) return 'bg-info';
            if (p >= 40) return 'bg-warning';
            return 'bg-secondary';
        }

        function updatePhaseRow(phase) {
            try {
                const row = document.querySelector(`tr[data-phase-id="${phase.phase_id}"]`);
                if (!row) return;

                row.dataset.phaseName = (phase.phase_name || '').toLowerCase();
                row.dataset.phaseStatus = phase.status || '';
                row.dataset.phaseProgress = String(phase.completion_percentage || 0);
                row.dataset.phaseOrder = String(phase.phase_order || 0);
                row.dataset.phaseTitle = ((phase.phase_name || '') + ' ' + (phase.project_name || '')).toLowerCase();

                const orderBadge = row.querySelector('.order-badge');
                if (orderBadge) orderBadge.textContent = phase.phase_order;

                const nameTitle = row.querySelector('.phase-name-title');
                if (nameTitle) nameTitle.textContent = phase.phase_name;

                const nameMeta = row.querySelector('.phase-name-meta');
                if (nameMeta) nameMeta.textContent = phase.project_name || 'Project phase';

                const scheduleCells = row.querySelectorAll('.schedule-stack');
                if (scheduleCells && scheduleCells.length >= 2) {
                    scheduleCells[0].innerHTML = `<span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> ${phase.planned_start_date || '—'}</span><span class="schedule-separator"><i class="bi bi-arrow-right"></i></span><span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> ${phase.planned_end_date || '—'}</span>`;
                    scheduleCells[1].innerHTML = `<span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> ${phase.actual_start_date || '—'}</span><span class="schedule-separator"><i class="bi bi-arrow-right"></i></span><span class="schedule-chip"><i class="bi bi-calendar3 me-1"></i> ${phase.actual_end_date || '—'}</span>`;
                }

                const progressCell = row.querySelector('.progress-cell');
                if (progressCell) {
                    const progressBar = progressCell.querySelector('.progress-bar');
                    const progressValue = progressCell.querySelector('.progress-value');
                    const percent = Math.min(100, Math.max(0, Number(phase.completion_percentage || 0)));
                    if (progressBar) {
                        progressBar.style.width = `${percent}%`;
                        progressBar.className = `progress-bar ${progressBarClass(percent)}`;
                    }
                    if (progressValue) progressValue.textContent = `${Math.round(percent)}%`;
                }

                const statusCell = row.querySelector('.action-cell').previousElementSibling;
                if (statusCell) {
                    const badge = statusCell.querySelector('.badge');
                    if (badge) {
                        const map = mapStatusClassAndLabel(phase.status);
                        badge.className = `${map.class} px-2 py-1 rounded-pill fw-medium`;
                        badge.textContent = map.label;
                    }
                }

                // Update edit/view button payloads
                const editBtn = row.querySelector('.js-phase-edit-btn');
                const viewBtn = row.querySelector('.js-phase-view-btn');
                if (editBtn) {
                    const payload = JSON.stringify({
                        phase_id: phase.phase_id,
                        project_id: phase.project_id,
                        phase_name: phase.phase_name,
                        phase_order: phase.phase_order,
                        planned_start_date_raw: phase.planned_start_date_raw,
                        planned_end_date_raw: phase.planned_end_date_raw,
                        actual_start_date_raw: phase.actual_start_date_raw,
                        actual_end_date_raw: phase.actual_end_date_raw,
                        completion_percentage_raw: phase.completion_percentage,
                        status: phase.status,
                    });
                    editBtn.dataset.phaseEdit = payload;
                }
                if (viewBtn) {
                    viewBtn.dataset.phaseDetails = editBtn?.dataset.phaseEdit || '';
                }
            } catch (e) {
                // quietly fail
                console.error('Failed to update phase row', e);
            }
        }

        document.body.addEventListener('click', function (event) {
            const deleteBtn = event.target.closest('.js-phase-delete-btn');
            if (!deleteBtn) {
                return;
            }

            event.preventDefault();
            const deleteUrl = deleteBtn.dataset.phaseDeleteUrl;
            if (!deleteUrl) {
                return;
            }

            Swal.fire({
                title: 'Delete phase?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it',
            }).then(async (result) => {
                if (!result.isConfirmed) {
                    return;
                }

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const formData = new FormData();
                    formData.set('_token', csrfToken);
                    formData.set('_method', 'DELETE');

                    const response = await fetch(deleteUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(payload.message || 'Unable to delete phase.');
                    }

                    Swal.fire({
                        title: 'Deleted',
                        text: payload.message || 'Phase deleted successfully.',
                        icon: 'success',
                        confirmButtonColor: '#045a33',
                    }).then(() => {
                        window.location.reload();
                    });
                } catch (error) {
                    Swal.fire({
                        title: 'Delete failed',
                        text: error.message || 'The phase could not be deleted.',
                        icon: 'error',
                        confirmButtonColor: '#045a33',
                    });
                }
            });
        });

        const openPhaseModalBtn = document.getElementById('openPhaseModalBtn');
        if (openPhaseModalBtn) {
            openPhaseModalBtn.addEventListener('click', () => openPhaseModal('create'));
        }

        document.body.addEventListener('click', function (event) {
            const editBtn = event.target.closest('.js-phase-edit-btn');
            if (editBtn) {
                const payload = editBtn.dataset.phaseEdit ? JSON.parse(editBtn.dataset.phaseEdit) : null;
                if (payload) {
                    openPhaseModal('edit', payload);
                }
            }
        });

        function openPanel(payload) {
            const wasAlreadyOpen = detailsSidebarCard.classList.contains('is-open');
            setDetailsPanelOpen(true);
            beginDetailsTransition();

            if (wasAlreadyOpen) {
                setTimeout(() => {
                    renderPhaseDetails(payload);
                }, 200);
            } else {
                requestAnimationFrame(() => {
                    renderPhaseDetails(payload);
                });
            }
        }

        [searchInput, statusFilter, progressFilter, sortSelect].forEach((control) => {
            if (control) {
                control.addEventListener('input', applyPhaseFilters);
                control.addEventListener('change', applyPhaseFilters);
            }
        });

        document.body.addEventListener('click', function (event) {
            if (event.target.closest('.js-close-phase-panel')) {
                closeDetailsPanel();
            }
        });

        document.body.addEventListener('click', function (event) {
            const viewBtn = event.target.closest('.js-phase-view-btn');
            if (viewBtn) {
                const payload = viewBtn.dataset.phaseDetails ? JSON.parse(viewBtn.dataset.phaseDetails) : null;
                if (payload) {
                    openPanel(payload);
                }
            }
        });

        closeDetailsPanel();
        applyPhaseFilters();
    });
</script>
@endpush
@endsection