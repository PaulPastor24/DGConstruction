@extends('layouts.admin')

@section('title', 'Project Management - D&G Construction Monitor')
@section('page_title', 'Project Management')

@push('styles')
<style>
    :root {
        /* shared theme tokens (centralized in public/css/admin.css) */
        --mi-dark: var(--brand-dark);
        --mi-muted: #64748b;
        --mi-border: var(--border);
        --mi-background: var(--bg-page);
        --mi-white: var(--surface);
        --mi-accent: var(--brand-green);
        --mi-accent-soft: var(--brand-accent-soft);
        --mi-accent-hover: var(--brand-green);

        /* compatibility aliases */
        --dg-green: var(--mi-accent);
        --dg-green-hover: var(--mi-accent-hover);
        --dg-green-light: var(--mi-accent-soft);
        --status-progress-bg: var(--mi-accent-soft);
        --status-progress-text: var(--mi-accent);
        --status-hold-bg: #fef3c7;
        --status-hold-text: #d97706;
    }

    body {
        background-color: #f8fafc;
    }

    .main-dashboard-container {
        display: flex;
        gap: 24px;
        padding: 24px;
        align-items: flex-start;
    }

    .left-dashboard-content {
        flex: 1;
        min-width: 0;
    }

    /* Top Layout Headers */
    .dashboard-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 8px;
    }

    .dashboard-title-area h2 {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .dashboard-title-area p {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 0;
    }

    .btn-dg-primary {
        background-color: var(--mi-accent);
        color: var(--mi-white);
        font-weight: 600;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.15s ease-in-out;
        box-shadow: 0 6px 20px rgba(22, 101, 52, 0.08);
    }

    .btn-dg-primary:hover {
        background-color: var(--mi-accent-hover);
        color: var(--mi-white);
    }

    /* Metric Cards Grid */
    .metrics-row-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-top: 20px;
        margin-bottom: 24px;
    }

    .metric-card-box {
        background: var(--mi-white);
        border: 1px solid var(--mi-border);
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 8px 28px rgba(22, 101, 52, 0.04);
    }

    .metric-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .metric-card-box.total .metric-icon-wrapper { background-color: var(--dg-green); color: white; }
    .metric-card-box.progress-active .metric-icon-wrapper { background-color: #065f46; color: white; }
    .metric-card-box.hold .metric-icon-wrapper { background-color: #d97706; color: white; }
    .metric-card-box.completed .metric-icon-wrapper { background-color: #0f5132; color: white; }

    .metric-info-text .stat-num {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .metric-info-text .stat-lbl {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .metric-info-text .stat-sub {
        font-size: 12px;
        color: #6b7280;
    }

    /* Filter Toolbar Panel */
    .filter-toolbar-panel {
        background: var(--mi-white);
        border: 1px solid var(--mi-border);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        padding: 16px;
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 6px 18px rgba(22, 101, 52, 0.03);
    }

    .search-input-container {
        position: relative;
        flex: 1;
        min-width: 240px;
    }

    .search-input-container .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .search-input-container input {
        padding-left: 36px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        height: 38px;
    }

    .filter-dropdown-select {
        width: 180px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        height: 38px;
        background-color: #fff;
    }

    .btn-filter-action {
        border: 1px solid #d1d5db;
        background: white;
        height: 38px;
        padding: 0 16px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #374151;
        font-size: 14px;
    }

    /* Custom Table Styling */
    .table-container-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-top: none;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        overflow: hidden;
    }

    .dg-custom-table {
        margin-bottom: 0;
    }

    .dg-custom-table thead th {
        background-color: #ffffff;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #4b5563;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .dg-custom-table tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
        background: white;
    }

    /* Project Info Elements */
    .project-title-bold {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
    }

    .project-subtext-muted {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 2px;
    }

    .project-date-badge {
        font-size: 11px;
        color: #4b5563;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Custom Badges */
    .status-pill {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .status-pill.in-progress { background-color: var(--status-progress-bg); color: var(--status-progress-text); }
    .status-pill.on-hold { background-color: var(--status-hold-bg); color: var(--status-hold-text); }
    .status-pill.completed { background-color: #e2e8f0; color: #475569; }

    /* Custom Team Layout */
    .supervisor-cell-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .supervisor-avatar-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
    }

    /* Custom Progress Bar Elements */
    .custom-progress-container {
        width: 120px;
    }

    .progress-percent-lbl {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .dg-bar-track {
        height: 6px;
        background-color: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .dg-bar-fill {
        height: 100%;
        background-color: #15803d;
        border-radius: 3px;
    }

    .dg-bar-fill.hold-fill {
        background-color: #d97706;
    }

    .progress-phase-subtitle {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
    }

    /* Duration Row Styling */
    .duration-primary-txt {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
    }

    .duration-secondary-pct {
        font-size: 12px;
        color: #6b7280;
    }

    /* Row Buttons Layout */
    .action-buttons-flex {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-view-action {
        border: 1px solid #d1d5db;
        background: white;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-view-action:hover {
        background-color: #f9fafb;
    }

    .btn-icon-more {
        background: transparent;
        border: none;
        color: #9ca3af;
        padding: 4px;
    }

    /* Table Navigation Pagination Footer Info */
    .table-pagination-footer-bar {
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #e5e7eb;
        font-size: 13px;
        color: #4b5563;
    }

    /* Dynamic Side Details Container Panel layout style */
    .right-details-sidebar-panel {
        width: 380px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: sticky;
        top: 24px;
    }

    .sidebar-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .sidebar-header-flex h3 {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .btn-close-sidebar {
        background: none;
        border: none;
        color: #9ca3af;
        font-size: 20px;
    }

    .sidebar-field-group {
        margin-bottom: 16px;
    }

    .sidebar-field-label {
        font-size: 12px;
        font-weight: 600;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin-bottom: 4px;
    }

    .sidebar-field-value {
        font-size: 14px;
        color: #111827;
    }

    .summary-box-card-grid {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        margin-top: 20px;
    }

    .summary-box-card-grid h4 {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 12px;
    }

    .summary-stats-subgrid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .summary-stat-cell-item {
        margin-bottom: 8px;
    }

    .summary-stat-cell-item .cell-lbl {
        font-size: 11px;
        color: #6b7280;
    }

    .summary-stat-cell-item .cell-val {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .sidebar-actions-footer-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 24px;
    }

    .sidebar-actions-footer-row .btn {
        font-size: 12px;
        padding: 8px 4px;
        font-weight: 500;
        text-align: center;
    }

    /* Lower Section Workflow Layout */
    .workflow-timeline-section-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-top: 24px;
    }

    .workflow-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
    }

    .workflow-steps-flex-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .workflow-step-node-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        flex: 1;
    }

    .step-number-circle {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: var(--dg-green);
        color: white;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .step-node-text-details h5 {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
    }

    .step-node-text-details p {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 0;
        line-height: 1.3;
    }

    .workflow-connector-arrow {
        color: #d1d5db;
        font-size: 16px;
    }

    /* --- Modal Custom View UI Specific Styles --- */
    .modal-custom-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }

    .modal-custom-input {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.9rem;
        color: #334155;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
    }

    .modal-custom-input::placeholder {
        color: #94a3b8;
        opacity: 0.8;
    }

    .modal-custom-input:focus {
        border-color: #064e3b;
        box-shadow: 0 0 0 3px rgba(6, 78, 59, 0.15);
        background-color: #fff;
    }

    .modal-btn-cancel {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        color: #1e293b;
        font-weight: 700;
        border-radius: 8px;
        transition: background-color 0.2s;
    }

    .modal-btn-cancel:hover {
        background-color: #f8fafc;
    }

    .modal-btn-submit {
        background-color: #064e3b;
        color: #ffffff;
        font-weight: 700;
        border-radius: 8px;
        border: none;
        transition: background-color 0.2s;
    }

    .modal-btn-submit:hover {
        background-color: #043e2e;
        color: #ffffff;
    }

    .info-callout-box {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
    }

    .custom-close-btn {
        font-size: 0.85rem;
        opacity: 0.6;
    }
</style>
@endpush

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');

/* Theme overrides to match Materials & Inventory pages (inventory-green-theme) */
.mi-page.inventory-green-theme {
    background: var(--bg-page, #f8fafc);
    color: #0f172a;
    font-family: 'Plus Jakarta Sans', Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    padding: 4px 0 28px;
}

.content {
    background: var(--bg-page, #f8fafc);
}

.mi-page.inventory-green-theme .main-dashboard-container {
    padding: 4px 0 28px;
    background: var(--bg-page, #f8fafc);
}

.mi-page.inventory-green-theme .dashboard-title-area h2,
.mi-page.inventory-green-theme .workflow-section-title,
.mi-page.inventory-green-theme .sidebar-header-flex h3,
.mi-page.inventory-green-theme .metric-info-text .stat-num {
    font-family: 'Syne', 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif;
}

.mi-page.inventory-green-theme .dashboard-title-area h2,
.mi-page.inventory-green-theme .dashboard-title-area h2 * {
    font-size: 28px !important;
    font-weight: 600 !important;
    color: #111827 !important;
    letter-spacing: -0.02em !important;
    margin-bottom: 6px !important;
}

.mi-page.inventory-green-theme .dashboard-title-area p {
    font-size: 14px;
    color: #64748b;
}

/* Cards and metric boxes use same radii, borders and shadows as inventory */
.mi-page.inventory-green-theme .metric-card-box,
.mi-page.inventory-green-theme .filter-toolbar-panel,
.mi-page.inventory-green-theme .table-container-card,
.mi-page.inventory-green-theme .workflow-timeline-section-card,
.mi-page.inventory-green-theme .summary-box-card-grid,
.mi-page.inventory-green-theme .right-details-sidebar-panel {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius-card, 16px);
    box-shadow: var(--shadow-saas, 0 8px 24px rgba(15, 23, 42, 0.06));
}

.mi-page.inventory-green-theme .metric-card-box {
    padding: 20px;
}

.mi-page.inventory-green-theme .metric-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    border: 1px solid rgba(22, 101, 52, 0.14);
    background-color: rgba(22, 101, 52, 0.08);
    color: var(--brand-dark, #2a4028);
    font-size: 20px;
}

.mi-page.inventory-green-theme .metric-card-box.total .metric-icon-wrapper,
.mi-page.inventory-green-theme .metric-card-box.completed .metric-icon-wrapper {
    background-color: rgba(22, 101, 52, 0.08);
    color: var(--brand-dark, #2a4028);
    border-color: rgba(22, 101, 52, 0.14);
}

.mi-page.inventory-green-theme .metric-card-box.progress-active .metric-icon-wrapper {
    background-color: rgba(59, 130, 246, 0.12);
    color: #2563eb;
    border-color: rgba(59, 130, 246, 0.18);
}

.mi-page.inventory-green-theme .metric-card-box.hold .metric-icon-wrapper {
    background-color: rgba(249, 115, 22, 0.12);
    color: #c2410c;
    border-color: rgba(249, 115, 22, 0.18);
}

.mi-page.inventory-green-theme .metric-info-text .stat-num {
    font-size: 24px;
    font-weight: 600;
    color: #0f172a;
    line-height: 1.2;
}

/* Buttons consistent with inventory green theme */
.btn-dg-primary,
.mi-page.inventory-green-theme .btn-dg-primary {
    background-color: var(--brand-green, #365233);
    border-color: var(--brand-green, #365233);
    color: #ffffff;
    font-weight: 700;
    padding: 10px 18px;
    border-radius: var(--radius-btn, 12px);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-saas, 0 8px 24px rgba(15, 23, 42, 0.06));
}

.btn-dg-primary:hover,
.mi-page.inventory-green-theme .btn-dg-primary:hover {
    background-color: var(--brand-green, #365233);
    color: #fff;
}

/* Toolbar inputs/selects match inventory focus */
.mi-page.inventory-green-theme .filter-toolbar-panel .form-control,
.mi-page.inventory-green-theme .filter-toolbar-panel .form-select,
.mi-page.inventory-green-theme .search-input-container input,
.mi-page.inventory-green-theme .filter-dropdown-select {
    border-radius: 10px;
    border: 1px solid #e6edf0;
    height: 40px;
    background-color: #ffffff;
}

.mi-page.inventory-green-theme .search-input-container .search-icon {
    color: #7a8b7f;
}

.mi-page.inventory-green-theme .table-container-card {
    border: 1px solid #e6efe8;
    border-top: 1px solid #e6efe8;
    border-bottom-left-radius: var(--radius-card, 16px);
    border-bottom-right-radius: var(--radius-card, 16px);
    overflow: hidden;
    background: #ffffff;
}

.mi-page.inventory-green-theme .table-responsive {
    background: #ffffff;
}

.mi-page.inventory-green-theme .dg-custom-table {
    background-color: #ffffff;
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 0;
}

.mi-page.inventory-green-theme .dg-custom-table thead th {
    background-color: #f2f8f3 !important;
    color: #4b5563;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #dce8de;
    padding: 14px 16px;
}

.mi-page.inventory-green-theme .dg-custom-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #e8edf1;
    background-color: #ffffff;
    color: #334155;
    vertical-align: middle;
}

.mi-page.inventory-green-theme .dg-custom-table tbody tr:hover td {
    background-color: #f7fcf8;
}

.mi-page.inventory-green-theme .dg-custom-table tbody tr:last-child td {
    border-bottom: 0;
}

/* Sidebar tweaks to match inventory card style */
.mi-page.inventory-green-theme .right-details-sidebar-panel {
    width: 360px;
    padding: 22px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fdf9 100%);
}

.mi-page.inventory-green-theme .sidebar-header-flex h3 {
    font-size: 16px;
    font-weight: 700;
    color: #166534;
}

.mi-page.inventory-green-theme .status-pill.in-progress {
    background-color: var(--status-progress-bg, #d1fae5);
    color: var(--status-progress-text, #166534);
}

.mi-page.inventory-green-theme .status-pill.on-hold {
    background-color: #fff7ed;
    color: #c2410c;
}

.mi-page.inventory-green-theme .status-pill.completed {
    background-color: #eef2ff;
    color: #3730a3;
}

/* Modal buttons and inputs match inventory style */
.mi-page.inventory-green-theme .modal-custom-input {
    border-radius: 10px;
}

.mi-page.inventory-green-theme .modal-btn-submit {
    background-color: var(--brand-green, #365233);
    border-radius: 12px;
    padding: 10px 14px;
    font-weight: 700;
}

.mi-page.inventory-green-theme .modal-btn-cancel {
    border-radius: 12px;
    padding: 10px 14px;
}

/* Progress bars and stat typography */
.mi-page.inventory-green-theme .progress-percent-lbl {
    font-size: 13px;
    font-weight: 800;
}

.mi-page.inventory-green-theme .dg-bar-track {
    height: 8px;
    background-color: #eef2f1;
    border-radius: 999px;
}

.mi-page.inventory-green-theme .dg-bar-fill {
    background-color: var(--brand-green, #365233);
    height: 100%;
    border-radius: 999px;
}

/* Make action buttons consistent */
.mi-page.inventory-green-theme .btn-view-action {
    border-radius: 10px;
    padding: 6px 12px;
    font-weight: 600;
}

/* Responsive adjustments similar to inventory layout */
@media (max-width: 900px) {
    .mi-page.inventory-green-theme .metrics-row-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .mi-page.inventory-green-theme .right-details-sidebar-panel {
        position: static;
        width: 100%;
        margin-top: 16px;
    }
}

</style>
@endpush

@section('content')
<div class="mi-page inventory-green-theme main-dashboard-container">
    
    <!-- Left Main Content Area Grid -->
    <div class="left-dashboard-content">
        
        <!-- Header Top Info Line[cite: 3] -->
        <div class="dashboard-header-row">
            <div class="dashboard-title-area">
                <h2>Project Management</h2>
                <p>Create, manage, and monitor all construction projects.</p>
            </div>
            <!-- Modal trigger action replace standard full layout redirection[cite: 3] -->
            <button type="button" class="btn btn-dg-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                <i class="bi bi-plus"></i> New Project
            </button>
        </div>

        <!-- System Total Metrics Highlight Panels Row[cite: 3] -->
        <div class="metrics-row-grid">
            <div class="metric-card-box total">
                <div class="metric-icon-wrapper"><i class="bi bi-briefcase"></i></div>
                <div class="metric-info-text">
                    <div class="stat-lbl">Total Projects</div>
                    <div class="stat-num">{{ $stats['total'] ?? 8 }}</div>
                    <div class="stat-sub">All Projects</div>
                </div>
            </div>
            <div class="metric-card-box progress-active">
                <div class="metric-icon-wrapper"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="metric-info-text">
                    <div class="stat-lbl">In Progress</div>
                    <div class="stat-num">{{ $stats['ongoing'] ?? 5 }}</div>
                    <div class="stat-sub">Active Projects</div>
                </div>
            </div>
            <div class="metric-card-box hold">
                <div class="metric-icon-wrapper"><i class="bi bi-pause-circle"></i></div>
                <div class="metric-info-text">
                    <div class="stat-lbl">On Hold</div>
                    <div class="stat-num">{{ $stats['on_hold'] ?? 1 }}</div>
                    <div class="stat-sub">Paused Projects</div>
                </div>
            </div>
            <div class="metric-card-box completed">
                <div class="metric-icon-wrapper"><i class="bi bi-check-circle"></i></div>
                <div class="metric-info-text">
                    <div class="stat-lbl">Completed</div>
                    <div class="stat-num">{{ $stats['completed'] ?? 2 }}</div>
                    <div class="stat-sub">Finished Projects</div>
                </div>
            </div>
        </div>

        <!-- Filter Sub-Header Strip Panel Layout[cite: 3] -->
        <div class="filter-toolbar-panel">
            <form id="project-filters-form" method="GET" action="{{ route('admin.projects.index') }}" class="w-100 d-flex gap-2 align-items-center flex-wrap">
                <div class="search-input-container">
                    <i class="bi bi-search search-icon"></i>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search projects...">
                </div>
                
                <select name="status" class="form-select filter-dropdown-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="planning" {{ request('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>In Progress</option>
                    <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>

                <select name="supervisor" class="form-select filter-dropdown-select form-select-sm">
                    <option value="">All Supervisors</option>
                </select>
            </form>
        </div>

        <!-- Data Presentation Table Card[cite: 3] -->
        <div class="table-container-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 dg-custom-table" style="font-size: 13px; min-width: 760px;">
                    <thead class="table-light text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">
                        <tr>
                            <th style="width: 30%">Project</th>
                            <th style="width: 20%">Supervisor</th>
                            <th style="width: 12%">Status</th>
                            <th style="width: 15%">Progress</th>
                            <th style="width: 13%">Duration</th>
                            <th style="width: 10%" class="text-end">Actions</th>
                        </tr>
                    </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="metric-icon-wrapper" style="background-color: {{ $project->status === 'on_hold' ? '#ffc107' : '#198754' }}; color: white; width: 36px; height: 36px;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="project-title-bold">{{ $project->project_name }}</div>
                                        <div class="project-subtext-muted">{{ Str::limit($project->project_location, 35) }}</div>
                                        <div class="project-date-badge">
                                            <i class="bi bi-calendar3"></i> 
                                            {{ $project->start_date ? $project->start_date->format('M d, Y') : '' }} - 
                                            {{ $project->target_end_date ? $project->target_end_date->format('M d, Y') : '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="supervisor-cell-info">
                                    <div class="supervisor-avatar-circle">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="project-title-bold" style="font-size: 13px;">
                                            {{ $project->active_supervisor->name ?? ($project->supervisors->first()->name ?? 'Juan Dela Cruz') }}
                                        </div>
                                        <div class="project-subtext-muted" style="font-size: 11px;">Supervisor</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-pill {{ $project->status === 'ongoing' ? 'in-progress' : ($project->status === 'on_hold' ? 'on-hold' : 'completed') }}">
                                    {{ $project->status === 'ongoing' ? 'In Progress' : ($project->status === 'on_hold' ? 'On Hold' : 'Completed') }}
                                </span>
                            </td>
                            <td>
                                <div class="custom-progress-container">
                                    @php 
                                        $pct = number_format($project->progress_percentage ?? ($project->status === 'completed' ? 100 : ($project->status === 'on_hold' ? 25 : 65)), 0);
                                    @endphp
                                    <div class="progress-percent-lbl">{{ $pct }}%</div>
                                    <div class="dg-bar-track">
                                        <div class="dg-bar-fill {{ $project->status === 'on_hold' ? 'hold-fill' : '' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <div class="progress-phase-subtitle">
                                        {{ $project->status === 'completed' ? 'Completed' : ($project->status === 'on_hold' ? 'Site Preparation' : 'Foundation Phase') }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="duration-primary-txt">
                                        @if($project->status === 'completed')
                                            Completed
                                        @else
                                            {{ $project->start_date ? $project->start_date->diffInDays($project->target_end_date) : 169 }} days left
                                        @endif
                                    </div>
                                    <div class="duration-secondary-pct">({{ $pct }}%)</div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="action-buttons-flex justify-content-end">
                                    <button type="button" class="btn btn-view-action trigger-details-panel" 
                                            data-project-json="{{ json_encode($project) }}"
                                            data-supervisor-name="{{ $project->active_supervisor->name ?? 'Juan Dela Cruz' }}"
                                            data-client-name="{{ $project->client->user->name ?? 'Mr. & Mrs. Reyes' }}"
                                            data-days-total="{{ $project->start_date ? $project->start_date->diffInDays($project->target_end_date) : 169 }}"
                                            data-pct="{{ $pct }}">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <button class="btn-icon-more"><i class="bi bi-three-dots-vertical"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i> No active projects found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>

            <!-- Footer Pagination Info Bar[cite: 3] -->
            <div class="table-pagination-footer-bar">
                <div>Showing 1 to {{ count($projects) }} of {{ count($projects) }} projects</div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-light border px-2"><i class="bi bi-chevron-left"></i></button>
                    <button class="btn btn-sm btn-success px-3">1</button>
                    <button class="btn btn-sm btn-light border px-2"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <!-- Lower Section Project Workflow Horizontal Bar Layout -->
        <div class="workflow-timeline-section-card">
            <div class="workflow-section-title">Project Workflow</div>
            <div class="workflow-steps-flex-row">
                <div class="workflow-step-node-item">
                    <div class="step-number-circle">1</div>
                    <div class="step-node-text-details">
                        <h5>Create Project</h5>
                        <p>Add project details and assign supervisor</p>
                    </div>
                </div>
                <i class="bi bi-chevron-right workflow-connector-arrow"></i>
                <div class="workflow-step-node-item">
                    <div class="step-number-circle">2</div>
                    <div class="step-node-text-details">
                        <h5>Create Phases</h5>
                        <p>Break down project into construction phases</p>
                    </div>
                </div>
                <i class="bi bi-chevron-right workflow-connector-arrow"></i>
                <div class="workflow-step-node-item">
                    <div class="step-number-circle">3</div>
                    <div class="step-node-text-details">
                        <h5>Set Milestones</h5>
                        <p>Define timeline milestones for each phase</p>
                    </div>
                </div>
                <i class="bi bi-chevron-right workflow-connector-arrow"></i>
                <div class="workflow-step-node-item">
                    <div class="step-number-circle">4</div>
                    <div class="step-node-text-details">
                        <h5>Track Progress</h5>
                        <p>Supervisor updates and submits reports</p>
                    </div>
                </div>
                <i class="bi bi-chevron-right workflow-connector-arrow"></i>
                <div class="workflow-step-node-item">
                    <div class="step-number-circle">5</div>
                    <div class="step-node-text-details">
                        <h5>Review & Update</h5>
                        <p>Admin reviews and updates project progress</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Custom Slider-Like Detail Information Meta Panel Component -->
    <div class="right-details-sidebar-panel" id="projectDetailsSidebar" style="display: none;">
        <div class="sidebar-header-flex">
            <h3>Project Details</h3>
            <button type="button" class="btn-close-sidebar" id="closeSidebarBtn">&times;</button>
        </div>

        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="metric-icon-wrapper" style="background-color: var(--dg-green); color: white;">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <h4 class="project-title-bold" id="sideProjectName" style="font-size: 16px; margin-bottom:0;">D&G Residential Building</h4>
                <span class="project-subtext-muted" id="sideProjectLocation">San Pablo City, Laguna</span>
                <div class="mt-1"><span class="status-pill in-progress" id="sideProjectStatus">In Progress</span></div>
            </div>
        </div>

        <div class="sidebar-field-group">
            <div class="sidebar-field-label">Project Description</div>
            <div class="sidebar-field-value" id="sideDescription">Construction of a 2-storey residential building.</div>
        </div>

        <div class="sidebar-field-group">
            <div class="sidebar-field-label">Client Name</div>
            <div class="sidebar-field-value" id="sideClientName">Mr. & Mrs. Reyes</div>
        </div>

        <div class="sidebar-field-group">
            <div class="sidebar-field-label">Planned Duration</div>
            <div class="sidebar-field-value" id="sidePlannedDates">Jul 15, 2026 - Dec 30, 2026</div>
        </div>

        <div class="sidebar-field-group">
            <div class="sidebar-field-label">Assigned Supervisor</div>
            <div class="supervisor-cell-info mt-1">
                <div class="supervisor-avatar-circle"><i class="bi bi-person"></i></div>
                <div>
                    <div class="project-title-bold" id="sideSupervisorName" style="font-size:13px;">Juan Dela Cruz</div>
                    <div class="project-subtext-muted" style="font-size:11px;">Supervisor</div>
                </div>
            </div>
        </div>

        <div class="sidebar-field-group">
            <div class="sidebar-field-label">Overall Progress</div>
            <div class="d-flex align-items-center gap-2 mt-1">
                <strong id="sideProgressPctText">65%</strong>
                <div class="dg-bar-track flex-1">
                    <div class="dg-bar-fill" id="sideProgressBarFill" style="width: 65%"></div>
                </div>
            </div>
        </div>

        <div class="summary-box-card-grid">
            <h4>Quick Summary</h4>
            <div class="summary-stats-subgrid">
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-layers"></i> Phases</div>
                    <div class="cell-val">6</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-flag"></i> Milestones</div>
                    <div class="cell-val">18</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-file-earmark-text"></i> Reports</div>
                    <div class="cell-val" style="color: #15803d;">12</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-box-seam"></i> Materials</div>
                    <div class="cell-val">35</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-people"></i> Attendance</div>
                    <div class="cell-val">42</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-hourglass-split"></i> Days Left</div>
                    <div class="cell-val" id="sideDaysLeft">169</div>
                </div>
            </div>
        </div>

        <div class="sidebar-actions-footer-row">
            <a href="#" id="sideViewDetailsBtn" class="btn btn-outline-secondary">View Details</a>
            <a href="#" id="sideEditProjectBtn" class="btn btn-outline-dark">Edit Project</a>
            <button class="btn btn-outline-danger"><i class="bi bi-archive"></i> Archive</button>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!--     ADD NEW PROJECT INPUT FORM MODAL       -->
<!-- ========================================== -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 px-2 py-1" style="border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            
            <!-- Modal Header Layout Title -->
            <div class="modal-header border-0 pb-0 pt-3">
                <h5 class="modal-title fw-bold text-dark" id="addProjectModalLabel" style="font-size: 1.25rem;">Add New Project</h5>
                <button type="button" class="btn-close custom-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Form Body Context[cite: 1] -->
            <div class="modal-body pt-2">
                <form action="{{ route('admin.projects.store') }}" method="POST">
                    @csrf

                    <!-- Section: Project Information[cite: 1] -->
                    <div class="mb-4">
                        <h6 class="text-secondary fw-bold mb-3" style="font-size: 0.9rem; letter-spacing: 0.3px;">Project Information</h6>
                        
                        <div class="mb-3">
                            <label for="modal_project_name" class="form-label modal-custom-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control modal-custom-input w-100 @error('project_name') is-invalid @enderror" 
                                   id="modal_project_name" 
                                   name="project_name" 
                                   placeholder="Enter project name" 
                                   value="{{ old('project_name') }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="modal_description" class="form-label modal-custom-label">Project Description</label>
                            <textarea class="form-control modal-custom-input w-100 @error('description') is-invalid @enderror" 
                                      id="modal_description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Enter project description">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="modal_project_location" class="form-label modal-custom-label">Project Location <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control modal-custom-input w-100 @error('project_location') is-invalid @enderror" 
                                   id="modal_project_location" 
                                   name="project_location" 
                                   placeholder="Enter project location" 
                                   value="{{ old('project_location') }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="modal_client_id" class="form-label modal-custom-label">Client Name</label>
                            <select class="form-select modal-custom-input w-100 @error('client_id') is-invalid @enderror" 
                                    id="modal_client_id" 
                                    name="client_id">
                                <option value="" disabled selected hidden>Enter client name (optional)</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->client_id }}" {{ old('client_id') == $client->client_id ? 'selected' : '' }}>
                                        {{ $client->user->name ?? 'Unknown Client User' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <!-- Section: Project Details[cite: 1] -->
                    <div class="mb-4">
                        <h6 class="text-secondary fw-bold mb-3" style="font-size: 0.9rem; letter-spacing: 0.3px;">Project Details</h6>

                        <div class="mb-3">
                            <label for="modal_start_date" class="form-label modal-custom-label">Planned Start Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control modal-custom-input w-100 @error('start_date') is-invalid @enderror" 
                                   id="modal_start_date" 
                                   name="start_date" 
                                   value="{{ old('start_date') }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="modal_target_end_date" class="form-label modal-custom-label">Planned End Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control modal-custom-input w-100 @error('target_end_date') is-invalid @enderror" 
                                   id="modal_target_end_date" 
                                   name="target_end_date" 
                                   value="{{ old('target_end_date') }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="modal_supervisor_id" class="form-label modal-custom-label">Assigned Supervisor <span class="text-danger">*</span></label>
                            <select class="form-select modal-custom-input w-100 @error('supervisor_id') is-invalid @enderror" 
                                    id="modal_supervisor_id" 
                                    name="supervisor_id" 
                                    required>
                                <option value="" disabled selected hidden>Select supervisor</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->user_id }}" {{ old('supervisor_id') == $supervisor->user_id ? 'selected' : '' }}>
                                        {{ $supervisor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="modal_status" class="form-label modal-custom-label">Project Status <span class="text-danger">*</span></label>
                            <select class="form-select modal-custom-input w-100" id="modal_status" name="status" required>
                                <option value="planning" selected>Planning</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Actions Bottom Button Block Row -->
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn modal-btn-cancel w-50 py-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modal-btn-submit w-50 py-2">Create Project</button>
                    </div>

                    <!-- Information Guide Light Callout Notice Box Banner -->
                    <div class="info-callout-box d-flex align-items-start gap-2 p-3 mt-3">
                        <i class="bi bi-info-circle text-success" style="font-size: 1.1rem; line-height: 1;"></i>
                        <p class="mb-0 small text-dark" style="font-size: 0.78rem; line-height: 1.4;">
                            After creating the project, you can add construction phases and timeline milestones.
                        </p>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('project-filters-form');
    const searchInput = filterForm?.querySelector('input[name="search"]');
    const statusSelect = filterForm?.querySelector('select[name="status"]');
    const supervisorSelect = filterForm?.querySelector('select[name="supervisor"]');

    let searchTimer;

    async function ajaxSubmitFilters() {
        if (!filterForm) return;
        const params = new URLSearchParams(new FormData(filterForm));
        const url = filterForm.action + '?' + params.toString();

        // Show subtle loading state on the search input
        if (searchInput) {
            searchInput.classList.add('loading');
        }

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Network response was not ok');
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Replace the table-container-card with the new content from server
            const newTableCard = doc.querySelector('.table-container-card');
            const currentTableCard = document.querySelector('.table-container-card');
            if (newTableCard && currentTableCard) {
                currentTableCard.replaceWith(newTableCard);
            }

            // Replace pagination/footer if available (already part of table card in our view)

            // Update URL without reloading
            try { history.replaceState(null, '', url); } catch (e) { /* ignore */ }

            // Re-bind any interactive handlers in replaced content
            bindViewButtons();
        } catch (err) {
            // On failure, fallback to full submit to keep functionality
            console.error('AJAX filter failed, falling back to full submit', err);
            filterForm.submit();
        } finally {
            if (searchInput) {
                searchInput.classList.remove('loading');
            }
        }
    }

    function submitFilters() {
        // kept for compatibility
        return ajaxSubmitFilters();
    }

    if (searchInput) {
        // Increased debounce to 800ms so typing pauses are less likely to trigger a submit
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                // If the user is still focused in the input, do not blur it; AJAX will update table only
                ajaxSubmitFilters();
            }, 800);
        });

        // Prevent form from submitting via Enter (we will handle via AJAX to avoid full reload)
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                ajaxSubmitFilters();
            }
        });
    }

    [statusSelect, supervisorSelect].filter(Boolean).forEach(function(select) {
        select.addEventListener('change', function() {
            // keep focus on the select but update results via AJAX
            ajaxSubmitFilters();
        });
    });

    // Bind view/details panel buttons - extracted so we can re-run after AJAX updates
    function bindViewButtons() {
        const viewBtns = document.querySelectorAll('.trigger-details-panel');
        viewBtns.forEach(btn => {
            btn.removeEventListener('click', openSidebarFromButton);
            btn.addEventListener('click', openSidebarFromButton);
        });
    }

    function openSidebarFromButton() {
        const btn = this;
        const sidebar = document.getElementById('projectDetailsSidebar');
        const sideProjectName = document.getElementById('sideProjectName');
        const sideProjectLocation = document.getElementById('sideProjectLocation');
        const sideProjectStatus = document.getElementById('sideProjectStatus');
        const sideDescription = document.getElementById('sideDescription');
        const sideClientName = document.getElementById('sideClientName');
        const sidePlannedDates = document.getElementById('sidePlannedDates');
        const sideSupervisorName = document.getElementById('sideSupervisorName');
        const sideProgressPctText = document.getElementById('sideProgressPctText');
        const sideProgressBarFill = document.getElementById('sideProgressBarFill');
        const sideDaysLeft = document.getElementById('sideDaysLeft');
        const sideViewDetailsBtn = document.getElementById('sideViewDetailsBtn');
        const sideEditProjectBtn = document.getElementById('sideEditProjectBtn');

        const project = JSON.parse(btn.getAttribute('data-project-json'));
        const supervisorName = btn.getAttribute('data-supervisor-name');
        const clientName = btn.getAttribute('data-client-name');
        const daysTotal = btn.getAttribute('data-days-total');
        const pct = btn.getAttribute('data-pct');

        if (sideProjectName) sideProjectName.textContent = project.project_name;
        if (sideProjectLocation) sideProjectLocation.textContent = project.project_location || (project.location ?? '');
        if (sideDescription) sideDescription.textContent = project.description || 'No description provided.';
        if (sideSupervisorName) sideSupervisorName.textContent = supervisorName;
        if (sideClientName) sideClientName.textContent = clientName;
        if (sideDaysLeft) sideDaysLeft.textContent = daysTotal;
        if (sideProgressPctText) sideProgressPctText.textContent = pct + '%';
        if (sideProgressBarFill) sideProgressBarFill.style.width = pct + '%';

        if (project.status === 'ongoing') {
            if (sideProjectStatus) { sideProjectStatus.textContent = 'In Progress'; sideProjectStatus.className = 'status-pill in-progress'; }
        } else if (project.status === 'on_hold') {
            if (sideProjectStatus) { sideProjectStatus.textContent = 'On Hold'; sideProjectStatus.className = 'status-pill on-hold'; }
        } else {
            if (sideProjectStatus) { sideProjectStatus.textContent = 'Completed'; sideProjectStatus.className = 'status-pill completed'; }
        }

        if (sideViewDetailsBtn) sideViewDetailsBtn.href = `/admin/projects/${project.project_id}`;
        if (sideEditProjectBtn) sideEditProjectBtn.href = `/admin/projects/${project.project_id}/edit`;

        if (sidebar) sidebar.style.display = 'block';
    }

    // Initial bind
    bindViewButtons();

    const closeBtn = document.getElementById('closeSidebarBtn');
    if (closeBtn) closeBtn.addEventListener('click', function() { document.getElementById('projectDetailsSidebar').style.display = 'none'; });

    // Preserve behavior for other existing code that may rely on viewButtons variable
    const sidebar = document.getElementById('projectDetailsSidebar');
    

    const sideClientName = document.getElementById('sideClientName');
    const sidePlannedDates = document.getElementById('sidePlannedDates');
    const sideSupervisorName = document.getElementById('sideSupervisorName');
    const sideProgressPctText = document.getElementById('sideProgressPctText');
    const sideProgressBarFill = document.getElementById('sideProgressBarFill');
    const sideDaysLeft = document.getElementById('sideDaysLeft');
    const sideViewDetailsBtn = document.getElementById('sideViewDetailsBtn');
    const sideEditProjectBtn = document.getElementById('sideEditProjectBtn');

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const project = JSON.parse(this.getAttribute('data-project-json'));
            const supervisorName = this.getAttribute('data-supervisor-name');
            const clientName = this.getAttribute('data-client-name');
            const daysTotal = this.getAttribute('data-days-total');
            const pct = this.getAttribute('data-pct');

            // Inject records parameters dynamically
            sideProjectName.textContent = project.project_name;
            sideProjectLocation.textContent = project.project_location;
            sideDescription.textContent = project.description || 'No description provided.';
            sideSupervisorName.textContent = supervisorName;
            sideClientName.textContent = clientName;
            sideDaysLeft.textContent = daysTotal;
            sideProgressPctText.textContent = pct + '%';
            sideProgressBarFill.style.width = pct + '%';
            
            if(project.status === 'ongoing') {
                sideProjectStatus.textContent = 'In Progress';
                sideProjectStatus.className = 'status-pill in-progress';
            } else if(project.status === 'on_hold') {
                sideProjectStatus.textContent = 'On Hold';
                sideProjectStatus.className = 'status-pill on-hold';
            } else {
                sideProjectStatus.textContent = 'Completed';
                sideProjectStatus.className = 'status-pill completed';
            }

            sideViewDetailsBtn.href = `/admin/projects/${project.project_id}`;
            sideEditProjectBtn.href = `/admin/projects/${project.project_id}/edit`;

            sidebar.style.display = 'block';
        });
    });

    closeBtn.addEventListener('click', function() {
        sidebar.style.display = 'none';
    });
});
</script>
@endpush