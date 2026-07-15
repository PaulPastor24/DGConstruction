@extends('layouts.client')

@section('title', 'My Projects - Client Portal')
@section('mobileTitle', 'My Projects')

@section('content')
<div class="container-fluid p-0">
    
    @include('client.partials.page-header', [
        'eyebrow' => 'Client Portfolio',
        'title' => 'My Projects',
        'description' => 'Track project status, progress, and current construction milestones.',
    ])

    @php
        $totalProjects = $projects->total();
        $activeTrackCount = $projects->getCollection()->filter(fn($summary) => data_get($summary, 'project.status') === 'ongoing')->count();
    @endphp
    
    <div class="row g-4 mb-4 align-items-center">
        <div class="col-12 col-md-4 col-xl-3">
            <div class="project-summary-box">
                <div class="summary-icon bg-mint-container text-success">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div>
                    <span class="summary-label">Total Projects</span>
                    <h3>{{ $totalProjects }}</h3>
                    <span class="summary-subtext">Assigned to your profile</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-xl-3">
            <div class="project-summary-box">
                <div class="summary-icon bg-orange-container text-warning">
                    <i class="bi bi-cone-striped"></i>
                </div>
                <div>
                    <span class="summary-label">Active Track</span>
                    <h3>{{ $activeTrackCount }}</h3>
                    <span class="summary-subtext">Currently under construction</span>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-6 col-xl-6 ms-auto">
            <div class="project-filter-toolbar">
                <form id="projectFilterForm" class="project-filter-form" action="{{ route('client.myprojects') }}" method="GET">
                    <div class="position-relative project-search-field">
                        <i class="bi bi-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control filter-search-input" placeholder="Search projects..." id="projectSearchInput">
                    </div>
                    
                    <div class="project-select-fields-wrapper-group">
                        <select name="status" class="form-select project-filter-select" data-filter-trigger>
                            <option value="">All Status</option>
                            <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        <select name="phase" class="form-select project-filter-select" data-filter-trigger>
                            <option value="">All Phases</option>
                            @foreach($availablePhases as $phase)
                                <option value="{{ $phase }}" {{ request('phase') === $phase ? 'selected' : '' }}>{{ $phase }}</option>
                            @endforeach
                        </select>
                        <select name="completion" class="form-select project-filter-select" data-filter-trigger>
                            <option value="">All Completion</option>
                            <option value="completed" {{ request('completion') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="in_progress" {{ request('completion') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="at_risk" {{ request('completion') === 'at_risk' ? 'selected' : '' }}>At Risk</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="mb-3 mt-5 project-feed-header-canvas-row">
        <h4 class="fw-bold m-0 brand-dark-green-header" style="font-size: 1.4rem; font-family: 'Plus Jakarta Sans', sans-serif;">Project Feed Operations</h4>
        <p class="text-muted m-0" style="font-size: 0.88rem; margin-top: 2px !important;">Real-time summary status logs of construction milestones.</p>
    </div>

    <div id="projectsGallery" class="project-feed-dynamic-3card-layout-matrix">
        @include('client.partials.project-gallery', ['projects' => $projects])
    </div>

    <div class="d-flex justify-content-between align-items-center mt-5 flex-wrap gap-2 result-reporting-footer-bar">
        <span class="text-muted text-sm" id="resultsSummary" style="font-size: 0.88rem; font-weight: 500;">Showing {{ $projects->firstItem() ?? 0 }} to {{ $projects->lastItem() ?? 0 }} of {{ $projects->total() }} active records</span>
    </div>

    <div class="d-flex justify-content-end mt-4 flex-wrap gap-2 project-pagination" id="projectPagination">
        {{ $projects->links('pagination::bootstrap-5') }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('projectFilterForm');
            const searchInput = document.getElementById('projectSearchInput');

            if (!form) {
                return;
            }

            let debounceTimer;
            const submitFilters = () => form.submit();

            form.querySelectorAll('select').forEach(function (field) {
                field.addEventListener('change', submitFilters);
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(submitFilters, 350);
                });
            }
        });
    </script>

</div>

<style>
    /* --- FILTER BAR & METRICS BASE SYSTEM CONFIG --- */
    .project-summary-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
    }
    .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .bg-mint-container { background-color: #e6f7ed; }
    .bg-orange-container { background-color: #fff7ed; }
    .summary-label { font-size: 0.8rem; color: #64748b; font-weight: 500; }
    .summary-subtext { font-size: 0.74rem; color: #94a3b8; display: block; }
    .project-summary-box h3 { font-size: 1.5rem; font-weight: 800; margin: 0.1rem 0; color: #0f172a; }

    /* FIXED NON-FALLING HORIZONTAL CONTROL BAR WORKSPACE IMPLEMENTATION RULES */
    .project-filter-toolbar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 0.6rem 0.75rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
        width: 100%;
    }

    .pagination {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 1rem;
        padding-left: 0;
        list-style: none;
    }
    .pagination .page-item .page-link {
        color: var(--brand-green);
        border-color: var(--brand-green);
        min-width: 44px;
        min-height: 44px;
        border-radius: 0.85rem;
        font-weight: 600;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--brand-green);
        border-color: var(--brand-green);
        color: #ffffff;
    }
    .pagination .page-item .page-link:hover {
        background-color: rgba(42, 64, 40, 0.1);
        color: var(--brand-green);
    }
    .pagination .page-item.disabled .page-link {
        color: #94a3b8;
        background-color: transparent;
        border-color: #d1d5db;
        cursor: not-allowed;
    }
    .project-filter-form {
        display: flex;
        flex-wrap: nowrap;
        gap: 12px;
        align-items: center;
        width: 100%;
    }
    .project-search-field {
        flex: 0 1 320px;
        min-width: 240px;
        max-width: 360px;
        width: 100%;
    }
    .filter-search-input {
        width: 100%;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        font-size: 0.9rem;
        height: 44px;
        transition: all 0.2s ease;
    }
    .filter-search-input:focus {
        background-color: #ffffff;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
    .project-select-fields-wrapper-group {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        align-items: center;
        justify-content: flex-start;
        flex: 1 1 auto;
        min-width: 0;
    }
    .project-filter-select {
        width: 150px;
        min-width: 120px;
        height: 44px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0 0.9rem;
    }
    .project-filter-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
    @media (max-width: 992px) {
        .project-search-field,
        .project-filter-select {
            flex: 1 1 100%;
            width: 100%;
        }
        .project-select-fields-wrapper-group {
            justify-content: stretch;
        }
    }
    .project-filter-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    /* --- MAXIMUM 3 CARDS IN ONE ROW OPTIMIZED GRID ARCHITECTURE FRAMEWORK --- */
    .project-feed-dynamic-3card-layout-matrix {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr)); /* Configured explicitly to 3 columns maximum on desktop screens */
        gap: 28px; /* High fidelity spacing grid matrices */
        margin-left: 0;
        margin-right: 0;
        width: 100%;
    }

    /* --- THE REBUILT PREMIUM CARD ITEM TILE --- */
    .project-dashboard-tile {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        position: relative;
        height: 100%;
        width: 100%;
    }
    .project-dashboard-tile:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.05);
    }
    .project-dashboard-cover {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        background: #f1f5f9;
        overflow: hidden;
    }
    .project-dashboard-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .project-dashboard-cover-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0) 70%, rgba(15, 23, 42, 0.02) 100%);
        pointer-events: none;
    }
    .project-dashboard-status {
        position: absolute;
        top: 14px;
        left: 14px;
        z-index: 5;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 0.35rem 0.75rem;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 4px 8px rgba(15, 23, 42, 0.06);
    }
    .status-indicator-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
        background-color: currentColor;
    }
    .status-on-track { background-color: #e6f7ed !important; color: #10b981 !important; }
    .status-completed { background-color: #f1f5f9 !important; color: #475569 !important; }
    .status-delayed { background-color: #fef2f2 !important; color: #ef4444 !important; }
    .status-planning { background-color: #eff6ff !important; color: #2563eb !important; }

    .project-dashboard-content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .project-dashboard-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }
    .project-dashboard-title {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.35rem; /* Re-proportioned visual size balance text configurations */
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #0f172a;
        line-height: 1.25;
    }
    .project-dashboard-progress-percentage-group {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        flex-shrink: 0;
    }
    .progress-context-lbl {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        font-weight: 700;
    }
    .project-dashboard-progress-percentage {
        font-size: 1.25rem;
        font-weight: 700;
        color: #10b981;
    }
    .project-dashboard-progress-container {
        width: 100%;
        margin-bottom: 18px;
    }
    .project-dashboard-progress-track {
        width: 100%;
        height: 6px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
    }
    .project-dashboard-progress-bar-fill {
        display: block;
        height: 100%;
        background-color: #10b981;
        border-radius: 999px;
    }

    .project-dashboard-meta-row {
        display: grid;
        grid-template-columns: 1fr; /* Clean stacking parameters inside 3-column rows */
        gap: 10px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
        margin-top: auto;
    }
    .project-dashboard-meta-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .project-dashboard-meta-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .project-dashboard-meta-value-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
    }
    .meta-icon-container {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        flex-shrink: 0;
    }
    .phase-icon-bg { background-color: #e6f7ed; color: #10b981; }
    .date-icon-bg { background-color: #f1f5f9; color: #64748b; }
    .location-icon-bg { background-color: #f1f5f9; color: #64748b; }

    .project-dashboard-meta-value {
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .font-weight-bold-css { font-weight: 700 !important; color: #0f172a !important; }
    .project-dashboard-action-wrapper {
        display: flex;
        justify-content: flex-end;
        padding-top: 6px;
    }
    .project-dashboard-button-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        padding: 0 1rem;
        background: #10b981;
        border: 1px solid #10b981;
        color: #ffffff;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .project-dashboard-button-link:hover {
        background: #059669;
        border-color: #059669;
    }

    /* --- THE MASTERCLASS ENHANCED EXECUTIVE MODAL ENGINE ARCHITECTURE --- */
    .project-command-modal .modal-dialog {
        max-width: 1060px;
    }
    .project-command-shell {
        background: #ffffff;
        border: none;
        border-radius: 24px;
        box-shadow: 0 30px 80px -15px rgba(15, 23, 42, 0.2);
        position: relative;
    }
    .project-command-modal-close-trigger {
        position: absolute;
        top: 24px;
        right: 24px;
        z-index: 120;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .project-command-modal-close-trigger:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        transform: rotate(90deg);
    }

    .project-command-body {
        padding: 44px;
        max-height: 84vh;
        overflow-y: auto;
    }
    .project-command-header {
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .project-command-header-badge-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .project-command-phase-text-badge {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background-color: #f1f5f9;
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
    }
    .project-command-modal-title {
        margin: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 2.4rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #0f172a;
        line-height: 1.15;
    }

    /* Modal Dashboard Premium Horizontal Row Metrics Panels */
    .project-command-summary-panel-matrix {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 36px;
        width: 100%;
    }
    .command-panel-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 120px;
        transition: all 0.2s ease;
    }
    .card-highlight-border {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.05);
    }
    .command-panel-lbl {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 8px;
    }
    .command-panel-large-display-val {
        font-size: 2.2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .command-panel-bar-track {
        width: 100%;
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: auto;
    }
    .command-panel-horizontal-split {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 4px;
    }
    .command-panel-circle-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .icon-phase-cyan { background-color: #eff6ff; color: #2563eb; }
    .icon-health-green { background-color: #e6f7ed; color: #10b981; }
    .icon-date-green { background-color: #e6f7ed; color: #10b981; }

    .command-panel-split-copy {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .command-panel-medium-display-val {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Asymmetric Proportional Columns Split Setup Workspace */
    .project-command-dual-grid-split {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 36px;
        align-items: start;
        border-top: 1px solid #f1f5f9;
        padding-top: 32px;
    }
    .project-command-section-label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    .font-monospace-lbl {
        font-family: monospace;
        font-weight: 700;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        padding: 0.35rem 0.6rem;
    }

    .project-command-media-card {
        width: 100%;
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        aspect-ratio: 16 / 9;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }
    .project-command-media-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-carousel-bullets-indicator {
        position: absolute;
        bottom: 14px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
    }
    .media-carousel-bullets-indicator .bullet-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.4);
    }
    .media-carousel-bullets-indicator .bullet-dot.active {
        background-color: #10b981;
        width: 16px;
        border-radius: 4px;
    }

    /* Informational Profile Grid Parameters Matrix */
    .project-command-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .project-command-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }
    .summary-item-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .bg-location-tint { background-color: #fff1f2; color: #f43f5e; }
    .bg-phase-tint { background-color: #eff6ff; color: #3b82f6; }
    .bg-date-tint { background-color: #f0fdf4; color: #22c55e; }
    .bg-date-check-tint { background-color: #fdf2f8; color: #ec4899; }
    .bg-manager-tint { background-color: #f5f3ff; color: #8b5cf6; }
    .bg-supervisor-tint { background-color: #f0fdfa; color: #14b8a6; }

    .summary-item-inner-copy {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .project-command-field-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .project-command-field-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Bottom Section Updates Framework Wrapper */
    .project-command-fullwidth-update-wrap {
        border-top: 1px solid #f1f5f9;
        margin-top: 32px;
        padding-top: 32px;
        width: 100%;
    }
    .project-command-update-card {
        display: flex;
        align-items: flex-start;
        gap: 18px;
        padding: 24px;
        background-color: #fdf6ec; /* Specialized tint coloring highlights update cards */
        border: 1px solid #f5ebb3;
        border-radius: 16px;
    }
    .project-command-update-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background-color: #e6f7ed;
        color: #10b981;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .project-command-update-header-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #b45309;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
        display: block;
    }
    .project-command-update-text-paragraph {
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.6;
        color: #451a03;
        font-weight: 600;
    }
    .project-command-update-meta {
        display: inline-block;
        margin-top: 10px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #d97706;
    }

    .project-command-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        padding: 24px 44px 36px 44px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .project-command-button-secondary {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        padding: 0.65rem 1.5rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        min-height: 44px;
        transition: all 0.2s ease;
    }
    .project-command-button-secondary:hover {
        background: #f8fafc;
        color: #1e293b;
    }
    .project-command-button-primary {
        background: #10b981;
        border: 1px solid #10b981;
        color: #ffffff;
        padding: 0.65rem 1.5rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.18);
        transition: all 0.2s ease;
    }
    .project-command-button-primary:hover {
        background: #059669;
        border-color: #059669;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.24);
    }

    /* --- RESPONSIVE WORKSPACE MEDIA BREAKPOINTS --- */
    
    /* Screen sizes from 1200px to 1440px widths */
    @media (max-width: 1400px) {
        .project-filter-toolbar {
            max-width: 100%;
        }
        .project-filter-form {
            flex-wrap: wrap; /* Gracefully scale down spacing boundaries on medium laptops */
        }
        .project-search-field {
            flex: 1 1 100%;
            max-width: 100%;
        }
        .project-select-fields-wrapper-group {
            width: 100%;
            justify-content: space-between;
        }
        .project-filter-select {
            flex: 1;
            width: auto;
        }
    }

    /* Tablet Platform Layout Viewports */
    @media (max-width: 1200px) {
        .project-feed-dynamic-3card-layout-matrix {
            grid-template-columns: repeat(2, minmax(0, 1fr)); /* 2-Columns fallback safe layout for tablet spaces */
            gap: 20px;
        }
        .project-command-summary-panel-matrix {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .project-command-dual-grid-split {
            grid-template-columns: 1fr;
            gap: 32px;
        }
    }

    @media (max-width: 992px) {
        .project-command-body { padding: 32px 24px; }
        .project-command-footer { padding: 16px 24px 24px 24px; }
    }

    /* Mobile Platform Layout Viewports */
    @media (max-width: 767px) {
        .project-feed-dynamic-3card-layout-matrix {
            grid-template-columns: 1fr; /* Crisp clear single stack feed execution row flow lines */
            gap: 16px;
        }
        .project-select-fields-wrapper-group {
            flex-direction: column;
            width: 100%;
            gap: 8px;
        }
        .project-filter-select {
            width: 100%;
        }
        
        .project-dashboard-meta-row {
            grid-template-columns: 1fr;
        }
        
        /* Modal Structure Complete Fullscreen Conversions */
        .project-command-modal .modal-dialog {
            margin: 0;
            max-width: 100%;
        }
        .project-command-shell {
            border-radius: 0;
            min-height: 100vh;
        }
        .project-command-body {
            padding: 24px 16px;
        }
        .project-command-modal-title {
            font-size: 1.8rem;
            padding-right: 32px;
        }
        .project-command-summary-panel-matrix {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .project-command-summary-grid {
            grid-template-columns: 1fr;
        }
        
        .project-command-footer {
            flex-direction: column-reverse;
            padding: 16px;
            gap: 10px;
        }
        .project-command-button-secondary,
        .project-command-button-primary {
            width: 100%;
            text-align: center;
        }
    }

    /* --- DECORATORS OVERRIDES --- */
    .empty-gallery-state-container {
        background-color: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 4rem 2rem !important;
    }
    .empty-state-icon-canvas { font-size: 3rem; color: #94a3b8; }
    .context-empty-title-css { color: #334155; font-family: 'Plus Jakarta Sans', sans-serif; }
    .result-reporting-footer-bar { border-top: 1px solid #e2e8f0; padding-top: 18px; }
    .project-pagination { display: flex; justify-content: flex-end; flex-wrap: wrap; }
    .project-pagination .pagination { justify-content: flex-end; margin-top: 0; }
    @media (max-width: 767px) {
        .project-pagination { justify-content: center; }
        .project-pagination .pagination { justify-content: center; }
    }
</style>
@endsection