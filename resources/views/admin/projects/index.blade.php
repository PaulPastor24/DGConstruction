@extends('layouts.admin')

@section('title', 'Project Management - D&G Construction Monitor')
@section('page_title', 'Project Management')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        --status-progress-bg: #dbeafe;
        --status-progress-text: #2563eb;
        --status-hold-bg: #fee2e2;
        --status-hold-text: #b91c1c;
        --status-completed-bg: #dcfce7;
        --status-completed-text: #15803d;
        --status-planning-bg: #fef3c7;
        --status-planning-text: #b45309;
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

    .dashboard-header-row .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(22, 101, 52, 0.12);
        filter: brightness(0.98);
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
        justify-content: space-between;
        flex-wrap: wrap;
        box-shadow: 0 6px 18px rgba(22, 101, 52, 0.03);
        margin-bottom: 20px;
    }

    .filter-toolbar-panel .filter-form-row {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        width: 100%;
    }

    .search-input-container {
        position: relative;
        flex: 1 1 280px;
        max-width: 320px;
        min-width: 220px;
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

    /* Projects page mobile responsive */
    @media (max-width: 991.98px) {
        .metrics-row-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .metrics-row-grid > *:nth-child(5),
        .metrics-row-grid > *:nth-child(6),
        .metrics-row-grid > *:nth-child(7),
        .metrics-row-grid > *:nth-child(8) {
            grid-column: auto;
        }
        .filter-toolbar-panel .filter-form-row {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }
        .search-input-container {
            grid-column: 1 / -1 !important;
            max-width: 100% !important;
        }
        .filter-dropdown-select {
            max-width: 100% !important;
            width: 100% !important;
        }
        .filter-actions-right {
            grid-column: 1 / -1 !important;
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }
        .proj-thumb {
            width: 40px;
            height: 40px;
        }
    }

    .filter-dropdown-select {
        width: 150px;
        min-width: 140px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        height: 38px;
        background-color: #fff;
    }

    .filter-actions-right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
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

    .dg-custom-table th.project-col { width: 34%; }
    .dg-custom-table th.supervisor-col { width: 18%; }
    .dg-custom-table th.status-col { width: 12%; }
    .dg-custom-table th.progress-col { width: 16%; }
    .dg-custom-table th.duration-col { width: 12%; }
    .dg-custom-table th.actions-col { width: 8%; }

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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.34rem 0.7rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        text-transform: capitalize;
        border: 1px solid transparent;
    }

    .status-pill.in-progress { background-color: var(--status-progress-bg); color: var(--status-progress-text); }
    .status-pill.on-hold { background-color: var(--status-hold-bg); color: var(--status-hold-text); }
    .status-pill.completed { background-color: var(--status-completed-bg); color: var(--status-completed-text); }
    .status-pill.planning { background-color: var(--status-planning-bg); color: var(--status-planning-text); }

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

    .dg-bar-fill.completed-fill {
        background-color: #16a34a;
    }

    .dg-bar-fill.on-hold-fill {
        background-color: #dc2626;
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
        justify-content: center;
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
        gap: 12px;
        border-top: 1px solid #e5e7eb;
        font-size: 13px;
        color: #4b5563;
        flex-wrap: wrap;
    }

    /* Dynamic Side Details Container Panel layout style */
    .right-details-sidebar-panel {
        width: 0;
        max-width: 0;
        min-height: 0;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        overflow: hidden;
        background: white;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: sticky;
        top: 24px;
        flex: 0 0 auto;
        transform: translateX(24px);
        transition: width 0.3s cubic-bezier(0.2, 0.8, 0.2, 1),
                    max-width 0.3s cubic-bezier(0.2, 0.8, 0.2, 1),
                    padding 0.3s ease,
                    opacity 0.24s ease,
                    transform 0.3s cubic-bezier(0.2, 0.8, 0.2, 1),
                    border-color 0.24s ease,
                    box-shadow 0.24s ease;
    }

    .right-details-sidebar-panel.is-visible {
        width: 380px;
        max-width: 380px;
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        overflow: visible;
        padding: 24px;
        border-color: #e5e7eb;
        transform: translateX(0);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .right-details-sidebar-panel.is-refreshing .sidebar-header-flex,
    .right-details-sidebar-panel.is-refreshing .sidebar-field-group,
    .right-details-sidebar-panel.is-refreshing .summary-box-card-grid,
    .right-details-sidebar-panel.is-refreshing .sidebar-actions-footer-row {
        animation: projectSidebarContentIn 0.22s cubic-bezier(0.2, 0.8, 0.2, 1) both;
    }

    .right-details-sidebar-panel.is-refreshing .sidebar-field-group { animation-delay: 0.04s; }
    .right-details-sidebar-panel.is-refreshing .summary-box-card-grid { animation-delay: 0.08s; }
    .right-details-sidebar-panel.is-refreshing .sidebar-actions-footer-row { animation-delay: 0.12s; }

    @keyframes projectSidebarContentIn {
        0% { opacity: 0; transform: translateY(6px); }
        100% { opacity: 1; transform: translateY(0); }
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
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: nowrap;
        gap: 8px;
        margin-top: 24px;
        overflow-x: auto;
    }

    .sidebar-actions-footer-row > * {
        display: inline-flex;
        flex: 0 0 auto;
    }

    .sidebar-actions-footer-row .btn {
        font-size: 12px;
        padding: 8px 10px;
        font-weight: 500;
        text-align: center;
        border-radius: 10px;
        min-width: 96px;
        justify-content: center;
        white-space: nowrap;
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
        font-size: 0.82rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }

    .modal-custom-input {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.92rem;
        color: #334155;
        background-color: #ffffff;
        transition: all 0.2s ease-in-out;
        min-height: 44px;
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
        border-radius: 10px;
        transition: all 0.2s ease;
        min-height: 44px;
    }

    .modal-btn-cancel:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }

    .modal-btn-submit {
        background: linear-gradient(135deg, #065f46, #047857);
        color: #ffffff;
        font-weight: 700;
        border-radius: 10px;
        border: none;
        transition: all 0.2s ease;
        min-height: 44px;
        box-shadow: 0 8px 20px rgba(6, 95, 70, 0.16);
    }

    .modal-btn-submit:hover {
        background: linear-gradient(135deg, #064e3b, #065f46);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .info-callout-box {
        background: linear-gradient(135deg, #f0fdf4, #f8fafc);
        border: 1px solid #dcfce7;
        border-radius: 10px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
    }

    .custom-close-btn {
        font-size: 0.9rem;
        opacity: 0.7;
    }

    .modal-section-card {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .modal-section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 12px;
        letter-spacing: 0.01em;
    }

    .modal-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .modal-grid-1 {
        display: grid;
        gap: 14px;
    }

    @media (max-width: 768px) {
        .modal-grid-2 {
            grid-template-columns: 1fr;
        }
    }

    .project-image-zoom-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.9);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        z-index: 9999;
    }
    .project-image-zoom-overlay.open {
        display: flex;
    }
    .project-image-zoom-card {
        max-width: 95%;
        max-height: 95%;
        border-radius: 18px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 40px 120px rgba(0,0,0,0.4);
    }
    .project-image-zoom-card img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        max-height: 85vh;
        background: #000;
    }
    .project-image-zoom-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: none;
        background: rgba(255,255,255,0.15);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .view-as-mobile-btn {
        display: none;
    }
    @media (max-width: 991.98px) {
        .view-as-mobile-btn {
            display: inline-flex;
        }
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
    background-color: var(--status-progress-bg, #fef3c7);
    color: var(--status-progress-text, #b45309);
}

.mi-page.inventory-green-theme .status-pill.on-hold {
    background-color: var(--status-hold-bg, #fee2e2);
    color: var(--status-hold-text, #b91c1c);
}

.mi-page.inventory-green-theme .status-pill.completed {
    background-color: var(--status-completed-bg, #dcfce7);
    color: var(--status-completed-text, #15803d);
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
            <form id="project-filters-form" method="GET" action="{{ route('admin.projects.index') }}" class="filter-form-row">
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
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>

                <select name="client" class="form-select filter-dropdown-select form-select-sm">
                    <option value="">All Clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->client_id }}" {{ request('client') == $client->client_id ? 'selected' : '' }}>
                            {{ $client->user->name ?? 'Unknown Client' }}
                        </option>
                    @endforeach
                </select>

                <select name="supervisor" class="form-select filter-dropdown-select form-select-sm">
                    <option value="">All Supervisors</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->user_id }}" {{ request('supervisor') == $supervisor->user_id ? 'selected' : '' }}>
                            {{ $supervisor->name }}
                        </option>
                    @endforeach
                </select>

                <select name="sort_by" class="form-select filter-dropdown-select form-select-sm" aria-label="Sort projects">
                    <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="name_asc" {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>Title A-Z</option>
                    <option value="name_desc" {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>Title Z-A</option>
                </select>

                <div class="filter-actions-right">
                    <button type="button" class="btn btn-dg-primary px-3 py-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addProjectModal" style="min-width: 140px; transition: all 0.2s ease-in-out; font-size: 13px;">
                        <i class="bi bi-plus"></i> New Project
                    </button>
                    <button type="button" class="btn px-3 py-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#projectArchivesModal" style="min-width: 140px; border: 1px solid #c8e6c9; color:#166534; background:#f6fff7; box-shadow: none; transition: all 0.2s ease-in-out; font-size: 13px;">
                        <i class="bi bi-archive"></i> Project Archives
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Presentation Table Card[cite: 3] -->
        @include('admin.projects.partials.table', ['projects' => $projects])


    </div>

    <!-- Right Custom Slider-Like Detail Information Meta Panel Component -->
    <div class="right-details-sidebar-panel" id="projectDetailsSidebar" aria-hidden="true">
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
                    <div class="cell-val" id="sidePhases">0</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-flag"></i> Milestones</div>
                    <div class="cell-val" id="sideMilestones">0</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-file-earmark-text"></i> Reports</div>
                    <div class="cell-val" id="sideReports" style="color: #15803d;">0</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-box-seam"></i> Materials</div>
                    <div class="cell-val" id="sideMaterials">0</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-people"></i> Attendance</div>
                    <div class="cell-val" id="sideAttendance">0</div>
                </div>
                <div class="summary-stat-cell-item">
                    <div class="cell-lbl"><i class="bi bi-hourglass-split"></i> Days Left</div>
                    <div class="cell-val" id="sideDaysLeft">0</div>
                </div>
            </div>
        </div>

        <div class="sidebar-actions-footer-row">
            <button type="button" id="sideEditProjectBtn" class="btn btn-sm" style="border:1px solid #bfdbfe; color:#1d4ed8; background:#eff6ff;"><i class="bi bi-pencil"></i> Edit Project</button>
            <form id="sideArchiveForm" action="{{ route('admin.projects.archive', ['project' => '__PROJECT_ID__']) }}" method="POST" class="d-inline project-action-form" data-project-confirm="archive" data-confirm-title="Archive Project?" data-confirm-text="This project will be removed from the Active Project list. All construction history, reports, phases, milestones, attendance, and materials will remain available." data-confirm-button="Archive" data-cancel-button="Cancel">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm" style="border:1px solid #c8e6c9; color:#166534; background:#f6fff7;"><i class="bi bi-archive"></i> Archive</button>
            </form>
            <form id="sideDeleteForm" action="{{ route('admin.projects.destroy', ['project' => '__PROJECT_ID__']) }}" method="POST" class="d-inline project-action-form" data-project-confirm="delete" data-confirm-title="Delete Project?" data-confirm-text="This project has no construction records. This action is permanent and cannot be undone." data-confirm-button="Delete" data-cancel-button="Cancel">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm px-3 py-2" style="border:1px solid #fecaca; color:#b91c1c; background:#fff7f7;"><i class="bi bi-trash"></i> Delete</button>
            </form>
        </div>
    </div>

</div>

<div class="modal fade" id="projectArchivesModal" tabindex="-1" aria-labelledby="projectArchivesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius: 16px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);">
            <div class="modal-header border-0 pb-2 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="projectArchivesModalLabel" style="font-size: 1.25rem;">Archived Projects</h5>
                    <p class="mb-0 text-muted small">Review archived projects and restore them when needed.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2 px-4 pb-4">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search</label>
                        <input type="text" id="projectArchiveSearch" class="form-control" placeholder="Search archived projects">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Client</label>
                        <select id="projectArchiveClientFilter" class="form-select">
                            <option value="">All Clients</option>
                            @foreach(($archives ?? collect())->filter(fn ($archive) => !empty($archive->client_id))->map(fn ($archive) => $archive->client)->filter()->unique('client_id')->sortBy(fn ($client) => $client->company_name ?? '')->values() as $client)
                                @php
                                    $clientOptionLabel = trim((string) ($client->company_name ?? ''));
                                    if ($clientOptionLabel === '' || strtolower($clientOptionLabel) === 'd&g construction corp') {
                                        $clientOptionLabel = 'Client';
                                    }
                                @endphp
                                <option value="{{ $client->client_id }}">{{ $clientOptionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Engineer</label>
                        <select id="projectArchiveEngineerFilter" class="form-select">
                            <option value="">All Engineers</option>
                            @foreach(($archives ?? collect())->filter(fn ($archive) => !empty($archive->engineer_id))->map(fn ($archive) => $archive->engineer)->filter()->unique('user_id')->sortBy(fn ($engineer) => $engineer->full_name ?? $engineer->name ?? '')->values() as $engineer)
                                @php
                                    $engineerOptionLabel = trim((string) ($engineer->full_name ?? $engineer->name ?? ''));
                                    if ($engineerOptionLabel === '' || strtolower($engineerOptionLabel) === 'lead engineer') {
                                        $engineerOptionLabel = 'Engineer';
                                    }
                                @endphp
                                <option value="{{ $engineer->user_id }}">{{ $engineerOptionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive rounded-3 border overflow-hidden">
                    <table class="table align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th>Project</th>
                                <th>Location</th>
                                <th>Client</th>
                                <th>Engineer</th>
                                <th>Timeline</th>
                                <th>Archived At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="projectArchivesTableBody">
                            @forelse ($archives ?? collect() as $archive)
                                @php
                                    $archiveClientLabel = trim((string) ($archive->client?->company_name ?? ''));
                                    if ($archiveClientLabel === '' || strtolower($archiveClientLabel) === 'd&g construction corp') {
                                        $archiveClientLabel = null;
                                    }

                                    $archiveClientContact = trim((string) ($archive->client?->user?->name ?? ''));
                                    if ($archiveClientContact === '' || strtolower($archiveClientContact) === 'd&g construction corp') {
                                        $archiveClientContact = null;
                                    }

                                    $archiveEngineerLabel = trim((string) ($archive->engineer?->full_name ?: $archive->engineer?->name ?? ''));
                                    if ($archiveEngineerLabel === '' || strtolower($archiveEngineerLabel) === 'lead engineer') {
                                        $archiveEngineerLabel = null;
                                    }

                                    $archiveEngineerEmail = trim((string) ($archive->engineer?->email ?? ''));
                                    if ($archiveEngineerEmail === '' || strtolower($archiveEngineerEmail) === 'lead engineer') {
                                        $archiveEngineerEmail = null;
                                    }
                                @endphp
                                <tr class="archive-row"
                                    data-client-id="{{ $archive->client_id ?: '' }}"
                                    data-engineer-id="{{ $archive->engineer_id ?: '' }}"
                                    data-search="{{ strtolower(($archive->project_name ?? '') . ' ' . ($archive->project_location ?: '') . ' ' . ($archive->client?->company_name ?: '') . ' ' . ($archive->client?->user?->first_name ?: '') . ' ' . ($archive->client?->user?->last_name ?: '') . ' ' . ($archive->engineer?->first_name ?: '') . ' ' . ($archive->engineer?->last_name ?: '') . ' ' . ($archive->engineer?->name ?: '')) }}">
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $archive->project_name }}</div>
                                        <div class="small text-muted">#{{ $archive->project_id }}</div>
                                    </td>
                                    <td>{{ $archive->project_location ?: optional($archive->project)->location ?: optional($archive->project)->project_location ?: '—' }}</td>
                                    <td>
                                        <div class="fw-semibold small text-dark">{{ $archiveClientLabel }}</div>
                                        <div class="small text-muted">{{ $archiveClientContact }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small text-dark">{{ $archiveEngineerLabel }}</div>
                                        <div class="small text-muted">{{ $archiveEngineerEmail }}</div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">Start: {{ $archive->start_date ? $archive->start_date->format('M d, Y') : '—' }}</div>
                                        <div class="small text-muted">Target: {{ $archive->target_end_date ? $archive->target_end_date->format('M d, Y') : '—' }}</div>
                                        <div class="small text-muted">Actual: {{ $archive->actual_end_date ? $archive->actual_end_date->format('M d, Y') : '—' }}</div>
                                    </td>
                                    <td>{{ $archive->archived_at ? $archive->archived_at->format('M d, Y H:i') : '—' }}</td>
                                    <td>
                                        @if($archive->project)
                                            <form action="{{ route('admin.projects.restore', $archive->project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="restore" data-confirm-title="Restore Project?" data-confirm-text="This project will be moved back to the Active Project list." data-confirm-button="Restore" data-cancel-button="Cancel">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm" style="border-color:#c8e6c9; color:#166534; background:#f6fff7;">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled>Unavailable</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="projectArchivesEmptyState">
                                    <td colspan="7" class="text-center text-muted py-4">No archived projects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <div class="text-muted small" id="projectArchivesPaginationSummary"></div>
                    <nav aria-label="Archived projects pagination">
                        <ul class="pagination pagination-sm mb-0" id="projectArchivesPagination" style="display:flex; gap:4px;"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!--     ADD NEW PROJECT INPUT FORM MODAL       -->
<!-- ========================================== -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 px-2 py-1" style="border-radius: 16px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);">
            <div class="modal-header border-0 pb-2 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="addProjectModalLabel" style="font-size: 1.25rem;">Add New Project</h5>
                    <p class="mb-0 text-muted small">Fill in the project details to start planning and tracking work.</p>
                </div>
                <button type="button" class="btn-close custom-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2 px-4 pb-4">
                @if($errors->any())
                    <div class="alert alert-danger border-0 rounded-3 mb-3" role="alert">
                        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following issues:</div>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-section-card">
                        <h6 class="modal-section-title">Project Information</h6>
                        <div class="modal-grid-1">
                            <div>
                                <label for="modal_project_name" class="form-label modal-custom-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control modal-custom-input w-100 @error('project_name') is-invalid @enderror"
                                       id="modal_project_name"
                                       name="project_name"
                                       placeholder="Enter project name"
                                       value="{{ old('project_name') }}"
                                       required>
                                @error('project_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="modal_description" class="form-label modal-custom-label">Project Description</label>
                                <textarea class="form-control modal-custom-input w-100 @error('description') is-invalid @enderror"
                                          id="modal_description"
                                          name="description"
                                          rows="3"
                                          placeholder="Enter project description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="modal_project_image" class="form-label modal-custom-label">Project Cover Image</label>
                                <input type="file"
                                       class="form-control modal-custom-input w-100 @error('project_image') is-invalid @enderror"
                                       id="modal_project_image"
                                       name="project_image"
                                       accept="image/png,image/jpeg,image/jpg,image/webp">
                                <div class="form-text small text-muted mt-1">Optional. Upload a photo of the project site (JPG, PNG or WEBP, max 5MB).</div>
                                @error('project_image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="modal-grid-2">
                                <div>
                                    <label for="modal_project_location" class="form-label modal-custom-label">Project Location <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control modal-custom-input w-100 @error('project_location') is-invalid @enderror"
                                           id="modal_project_location"
                                           name="project_location"
                                           placeholder="Enter project location"
                                           value="{{ old('project_location') }}"
                                           required>
                                    @error('project_location')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="modal_client_id" class="form-label modal-custom-label">Client Name <span class="text-danger">*</span></label>
                                    <select class="form-select modal-custom-input w-100 @error('client_id') is-invalid @enderror"
                                            id="modal_client_id"
                                            name="client_id"
                                            required>
                                        <option value="" disabled selected hidden>Select client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->client_id }}" {{ old('client_id') == $client->client_id ? 'selected' : '' }}>
                                                {{ $client->user->name ?? 'Unknown Client User' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-section-card">
                        <h6 class="modal-section-title">Project Details</h6>
                        <div class="modal-grid-2">
                            <div>
                                <label for="modal_start_date" class="form-label modal-custom-label">Planned Start Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control modal-custom-input w-100 @error('start_date') is-invalid @enderror"
                                       id="modal_start_date"
                                       name="start_date"
                                       value="{{ old('start_date') }}"
                                       required>
                                <div class="form-text small text-muted mt-1">Planned date for kickoff.</div>
                                @error('start_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="modal_target_end_date" class="form-label modal-custom-label">Planned End Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control modal-custom-input w-100 @error('target_end_date') is-invalid @enderror"
                                       id="modal_target_end_date"
                                       name="target_end_date"
                                       value="{{ old('target_end_date') }}"
                                       required>
                                <div class="form-text small text-muted mt-1">Target schedule for completion.</div>
                                @error('target_end_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-grid-2 mt-3">
                            <div>
                                <label for="modal_actual_end_date" class="form-label modal-custom-label">Actual End Date</label>
                                <input type="date"
                                       class="form-control modal-custom-input w-100 @error('actual_end_date') is-invalid @enderror"
                                       id="modal_actual_end_date"
                                       name="actual_end_date"
                                       value="{{ old('actual_end_date') }}">
                                <div class="form-text small text-muted mt-1">Actual date once the project is finished.</div>
                                @error('actual_end_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
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
                                @error('supervisor_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-grid-2 mt-3">
                            <div>
                                <label for="modal_time_in" class="form-label modal-custom-label">Site Time In</label>
                                <input type="time"
                                       class="form-control modal-custom-input w-100 @error('time_in') is-invalid @enderror"
                                       id="modal_time_in"
                                       name="time_in"
                                       value="{{ old('time_in') }}">
                                <div class="form-text small text-muted mt-1">Daily attendance start time for this project.</div>
                                @error('time_in')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="modal_time_out" class="form-label modal-custom-label">Site Time Out</label>
                                <input type="time"
                                       class="form-control modal-custom-input w-100 @error('time_out') is-invalid @enderror"
                                       id="modal_time_out"
                                       name="time_out"
                                       value="{{ old('time_out') }}">
                                <div class="form-text small text-muted mt-1">Daily attendance end time for this project.</div>
                                @error('time_out')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-none">
                            <label for="modal_status" class="form-label modal-custom-label">Project Status <span class="text-danger">*</span></label>
                            <select class="form-select modal-custom-input w-100" id="modal_status" name="status" required>
                                <option value="planning" selected>Pending</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3">
                        <div class="info-callout-box d-flex align-items-start gap-2 p-3 flex-grow-1">
                            <i class="bi bi-info-circle text-success" style="font-size: 1.1rem; line-height: 1;"></i>
                            <p class="mb-0 small text-dark" style="font-size: 0.78rem; line-height: 1.4;">
                                After creating the project, you can add construction phases and timeline milestones.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn modal-btn-cancel py-2 px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modal-btn-submit py-2 px-3">Create Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!--   EDIT PROJECT MODAL                      -->
<!-- ========================================== -->
<div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 px-2 py-1" style="border-radius: 16px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.16);">
            <div class="modal-header border-0 pb-2 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="editProjectModalLabel" style="font-size: 1.25rem;">Edit Project Details</h5>
                    <p class="mb-0 text-muted small">Update project details, timeline, and status while keeping the workflow consistent.</p>
                </div>
                <button type="button" class="btn-close custom-close-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-2 px-4 pb-4">
                <form action="{{ old('project_id', session('edit_project_id')) ? route('admin.projects.update', ['project' => old('project_id', session('edit_project_id'))]) : route('admin.projects.index') }}" method="POST" id="editProjectForm" class="edit-project-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="project_id" id="editProjectId" value="{{ old('project_id', session('edit_project_id', '')) }}">

                    <div class="modal-section-card">
                        <h6 class="modal-section-title">Project Information</h6>
                        <div class="modal-grid-1">
                            <div>
                                <label for="edit_project_name" class="form-label modal-custom-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control modal-custom-input w-100 @error('project_name') is-invalid @enderror"
                                       id="edit_project_name"
                                       name="project_name"
                                       value="{{ old('project_name', '') }}"
                                       placeholder="Enter project name"
                                       required>
                                @error('project_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="edit_description" class="form-label modal-custom-label">Project Description</label>
                                <textarea class="form-control modal-custom-input w-100 @error('description') is-invalid @enderror"
                                          id="edit_description"
                                          name="description"
                                          rows="3"
                                          placeholder="Enter project description">{{ old('description', '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="edit_project_image" class="form-label modal-custom-label">Project Cover Image</label>
                                <input type="file"
                                       class="form-control modal-custom-input w-100 @error('project_image') is-invalid @enderror"
                                       id="edit_project_image"
                                       name="project_image"
                                       accept="image/png,image/jpeg,image/jpg,image/webp">
                                <div class="form-text small text-muted mt-1">Optional. Replace the current cover image (JPG, PNG or WEBP, max 5MB).</div>
                                <div id="editProjectImagePreview" class="mt-2 d-none">
                                    <img src="" alt="Current project image" class="img-thumbnail" style="max-height: 96px;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="editRemoveImage">
                                        <label class="form-check-label small text-muted" for="editRemoveImage">Remove current image</label>
                                    </div>
                                </div>
                                @error('project_image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="modal-grid-2">
                                <div>
                                    <label for="edit_project_location" class="form-label modal-custom-label">Project Location <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control modal-custom-input w-100 @error('project_location') is-invalid @enderror"
                                           id="edit_project_location"
                                           name="project_location"
                                           value="{{ old('project_location', '') }}"
                                           placeholder="Enter project location"
                                           required>
                                    @error('project_location')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="edit_client_id" class="form-label modal-custom-label">Client Name <span class="text-danger">*</span></label>
                                    <select class="form-select modal-custom-input w-100 @error('client_id') is-invalid @enderror"
                                            id="edit_client_id"
                                            name="client_id"
                                            required>
                                        <option value="" disabled selected hidden>Select client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->client_id }}" {{ old('client_id', '') == $client->client_id ? 'selected' : '' }}>
                                                {{ $client->user->name ?? 'Unknown Client User' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-section-card">
                        <h6 class="modal-section-title">Project Details</h6>
                        <div class="modal-grid-2">
                            <div>
                                <label for="edit_start_date" class="form-label modal-custom-label">Planned Start Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control modal-custom-input w-100 @error('start_date') is-invalid @enderror"
                                       id="edit_start_date"
                                       name="start_date"
                                       value="{{ old('start_date', '') }}"
                                       required>
                                <div class="form-text small text-muted mt-1">Planned date for kickoff.</div>
                                @error('start_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="edit_target_end_date" class="form-label modal-custom-label">Planned End Date <span class="text-danger">*</span></label>
                                <input type="date"
                                       class="form-control modal-custom-input w-100 @error('target_end_date') is-invalid @enderror"
                                       id="edit_target_end_date"
                                       name="target_end_date"
                                       value="{{ old('target_end_date', '') }}"
                                       required>
                                <div class="form-text small text-muted mt-1">Target schedule for completion.</div>
                                @error('target_end_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-grid-2 mt-3">
                            <div>
                                <label for="edit_actual_end_date" class="form-label modal-custom-label">Actual End Date</label>
                                <input type="date"
                                       class="form-control modal-custom-input w-100 @error('actual_end_date') is-invalid @enderror"
                                       id="edit_actual_end_date"
                                       name="actual_end_date"
                                       value="{{ old('actual_end_date', '') }}">
                                <div class="form-text small text-muted mt-1">Actual date once the project is finished.</div>
                                @error('actual_end_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="edit_supervisor_id" class="form-label modal-custom-label">Assigned Supervisor <span class="text-danger">*</span></label>
                                <select class="form-select modal-custom-input w-100 @error('supervisor_id') is-invalid @enderror"
                                        id="edit_supervisor_id"
                                        name="supervisor_id">
                                    <option value="" disabled selected hidden>Select supervisor</option>
                                    @foreach($supervisors as $supervisor)
                                        <option value="{{ $supervisor->user_id }}" {{ old('supervisor_id', '') == $supervisor->user_id ? 'selected' : '' }}>
                                            {{ $supervisor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supervisor_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-grid-2 mt-3">
                            <div>
                                <label for="edit_time_in" class="form-label modal-custom-label">Site Time In</label>
                                <input type="time"
                                       class="form-control modal-custom-input w-100 @error('time_in') is-invalid @enderror"
                                       id="edit_time_in"
                                       name="time_in"
                                       value="{{ old('time_in', '') }}">
                                <div class="form-text small text-muted mt-1">Daily attendance start time for this project.</div>
                                @error('time_in')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label for="edit_time_out" class="form-label modal-custom-label">Site Time Out</label>
                                <input type="time"
                                       class="form-control modal-custom-input w-100 @error('time_out') is-invalid @enderror"
                                       id="edit_time_out"
                                       name="time_out"
                                       value="{{ old('time_out', '') }}">
                                <div class="form-text small text-muted mt-1">Daily attendance end time for this project.</div>
                                @error('time_out')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="edit_status" class="form-label modal-custom-label">Project Status <span class="text-danger">*</span></label>
                            <select class="form-select modal-custom-input w-100 @error('status') is-invalid @enderror" id="edit_status" name="status" required>
                                <option value="planning" {{ old('status', '') == 'planning' ? 'selected' : '' }}>Planning</option>
                                <option value="ongoing" {{ old('status', '') == 'ongoing' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ old('status', '') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="on_hold" {{ old('status', '') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            </select>
                            <div class="form-text small text-muted mt-1" id="editStatusHelpText">Choose the project’s current lifecycle state. Completed projects cannot be moved back to planning or in progress.</div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn modal-btn-cancel py-2 px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modal-btn-submit py-2 px-3">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!--   PROJECT DETAILS MODAL (AJAX-loaded)      -->
<!-- ========================================== -->
<div class="modal fade project-details-modal" id="projectDetailsModal" tabindex="-1" aria-labelledby="projectDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <div id="projectDetailsModalBody">
                    <div class="pd-loading-state">
                        <div class="spinner-border" role="status" style="color: var(--mi-accent, #198754);"></div>
                        <p class="mt-3 mb-0">Loading project details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if((session('show_create_success_modal') || session('show_success_modal')) && isset($newProject) && $newProject)
    @php
        $psActiveSupervisor = $newProject->active_supervisor;
        $psStart = $newProject->start_date;
        $psTargetEnd = $newProject->target_end_date;
        $psActualEnd = $newProject->actual_end_date;
        $psDuration = $psStart && $psTargetEnd ? max(1, $psStart->diffInDays($psTargetEnd) + 1) : 0;
        $psCompletedPhases = $newProject->phases()->where('status', 'completed')->count();
        $psApprovedReports = $newProject->reports()->where('approval_status', 'approved')->count();
        $psMaterialsCount = $newProject->projectMaterials()->count();
        $psAttendanceCount = $newProject->attendanceLogs()->count();
        $psCompletionReady = $newProject->phase_count > 0
            && $psCompletedPhases === $newProject->phase_count
            && $psApprovedReports > 0
            && $psMaterialsCount > 0
            && $psAttendanceCount > 0;
        $psSetupReady = $newProject->setup_status === 'ready_for_construction';
    @endphp
    <div class="modal fade project-success-modal" id="projectSuccessModal" tabindex="-1" aria-labelledby="projectSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">

                    <div class="ps-header">
                        <div class="ps-header-icon"><i class="bi bi-check-lg"></i></div>
                        <div class="ps-header-text">
                            <h3 id="projectSuccessModalLabel">New Project Created Successfully!</h3>
                            <p>Your project has been added to the system.</p>
                        </div>
                    </div>

                    <div class="ps-body">

                        <div class="ps-overview-card">
                            <div class="ps-overview-top">
                                <div class="ps-overview-identity">
                                    <div class="ps-overview-icon"><i class="bi bi-building"></i></div>
                                    <div>
                                        <div class="ps-overview-name">{{ $newProject->project_name }}</div>
                                        <div class="ps-overview-id">ID: #{{ $newProject->project_id }}</div>
                                    </div>
                                </div>
                                <div class="ps-overview-status">
                                    <span class="ps-field-label">Status</span>
                                    <span class="ps-status-pill {{ $newProject->status === 'on_hold' ? 'warning' : 'success' }}">
                                        <i class="bi bi-{{ $newProject->status === 'on_hold' ? 'pause-circle' : 'check-circle' }}"></i>
                                        {{ $newProject->workflow_status_label ?? ucfirst($newProject->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="ps-detail-grid">
                                <div>
                                    <div class="ps-field-label">Description</div>
                                    <div class="ps-field-value">{{ $newProject->description ?: 'Not provided' }}</div>
                                </div>
                                <div>
                                    <div class="ps-field-label">Start Date</div>
                                    <div class="ps-field-value"><i class="bi bi-calendar3"></i> {{ $psStart ? $psStart->format('M d, Y') : 'Not set' }}</div>
                                </div>
                                <div>
                                    <div class="ps-field-label">Target End Date</div>
                                    <div class="ps-field-value"><i class="bi bi-calendar3"></i> {{ $psTargetEnd ? $psTargetEnd->format('M d, Y') : 'Not set' }}</div>
                                </div>
                                <div>
                                    <div class="ps-field-label">Created</div>
                                    <div class="ps-field-value"><i class="bi bi-calendar3"></i> {{ $newProject->created_at ? $newProject->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                                </div>
                                <div>
                                    <div class="ps-field-label">Location</div>
                                    <div class="ps-field-value">{{ $newProject->location ?? 'Not specified' }}</div>
                                </div>
                                <div>
                                    <div class="ps-field-label">Actual End Date</div>
                                    <div class="ps-field-value {{ $psActualEnd ? '' : 'ps-muted-danger' }}">
                                        <i class="bi bi-clock-history"></i> {{ $psActualEnd ? $psActualEnd->format('M d, Y') : 'Not completed' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="ps-field-label">Duration</div>
                                    <div class="ps-field-value"><i class="bi bi-clock-history"></i> {{ $psDuration > 0 ? $psDuration . ' days' : 'Not available' }}</div>
                                </div>
                                <div>
                                    <div class="ps-field-label">Last Updated</div>
                                    <div class="ps-field-value"><i class="bi bi-calendar3"></i> {{ $newProject->updated_at ? $newProject->updated_at->format('M d, Y h:i A') : 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="ps-actions-grid">
                            <a href="{{ route('admin.phases', ['project_id' => $newProject->project_id]) }}" class="ps-action-card">
                                <p class="ps-action-title"><i class="bi bi-plus-lg"></i>Add Phases</p>
                                <p class="ps-action-copy">Define construction phases</p>
                            </a>
                            @if($newProject->phase_count > 0)
                                <a href="{{ route('admin.timeline') }}?project_id={{ $newProject->project_id }}" class="ps-action-card" id="ps-timeline-link">
                                    <p class="ps-action-title"><i class="bi bi-calendar3"></i>Add Timeline</p>
                                    <p class="ps-action-copy">Create milestones &amp; schedule</p>
                                </a>
                            @else
                                <a href="#" class="ps-action-card" id="ps-timeline-link">
                                    <p class="ps-action-title"><i class="bi bi-calendar3"></i>Add Timeline</p>
                                    <p class="ps-action-copy">Create milestones &amp; schedule</p>
                                </a>
                            @endif
                        </div>

                        <div>
                            <div class="ps-section-head">
                                <h6>Project Setup</h6>
                                <span class="ps-readonly-pill">Read Only</span>
                            </div>
                            <div class="ps-mini-grid">
                                <div class="ps-mini-card">
                                    <div class="ps-mini-icon success"><i class="bi bi-check-lg"></i></div>
                                    <div class="ps-mini-label">Project Created</div>
                                    <span class="ps-status-pill success"><i class="bi bi-check-circle"></i> Ready</span>
                                </div>
                                <div class="ps-mini-card">
                                    <div class="ps-mini-icon {{ $psActiveSupervisor ? 'success' : 'warning' }}">
                                        <i class="bi bi-{{ $psActiveSupervisor ? 'check-lg' : 'exclamation-lg' }}"></i>
                                    </div>
                                    <div class="ps-mini-label">Supervisor Assigned</div>
                                    <span class="ps-status-pill {{ $psActiveSupervisor ? 'success' : 'warning' }}">
                                        <i class="bi bi-{{ $psActiveSupervisor ? 'check-circle' : 'exclamation-circle' }}"></i>
                                        {{ $psActiveSupervisor ? 'Assigned' : 'Pending' }}
                                    </span>
                                </div>
                                <div class="ps-mini-card">
                                    <div class="ps-mini-icon neutral"><i class="bi bi-layers"></i></div>
                                    <div class="ps-mini-label">Construction Phases</div>
                                    <div class="ps-mini-value">{{ $newProject->phase_count }} Current Count</div>
                                </div>
                                <div class="ps-mini-card">
                                    <div class="ps-mini-icon neutral"><i class="bi bi-flag"></i></div>
                                    <div class="ps-mini-label">Timeline Milestones</div>
                                    <div class="ps-mini-value">{{ $newProject->milestone_count }} Current Count</div>
                                </div>
                                @unless($psSetupReady)
                                    <div class="ps-mini-card ps-mini-alert">
                                        <i class="bi bi-exclamation-triangle-fill" style="color:#ea580c;"></i>
                                        <span class="ps-mini-value" style="color:#c2410c;">Setup Required</span>
                                    </div>
                                @endunless
                            </div>
                            <p class="ps-note">This card is read only and reflects the current configuration state.</p>
                        </div>

                    </div>

                    <div class="ps-footer">
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-dg-primary btn-sm">Close</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    /* ===== Project Created Successfully modal ===== */
    .project-success-modal .modal-dialog,
    .project-details-modal .modal-dialog {
        max-width: 900px;
    }

    .project-success-modal .modal-dialog {
        animation: psSuccessModalIn 0.42s cubic-bezier(0.2, 0.9, 0.25, 1) both;
        transform-origin: center center;
    }

    .project-success-modal .modal-content,
    .project-details-modal .modal-content {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.18);
    }

    .project-success-modal .modal-content {
        animation: psSuccessCardIn 0.45s cubic-bezier(0.2, 0.9, 0.25, 1) both;
        animation-delay: 0.05s;
    }

    .project-success-modal .ps-header-icon {
        animation: psSuccessIconPop 0.5s ease both;
        animation-delay: 0.12s;
    }

    .project-success-modal .ps-overview-card,
    .project-success-modal .ps-action-card,
    .project-success-modal .ps-mini-card {
        animation: psSuccessFadeUp 0.35s ease both;
    }

    .project-success-modal .ps-overview-card { animation-delay: 0.15s; }
    .project-success-modal .ps-action-card { animation-delay: 0.2s; }
    .project-success-modal .ps-mini-card { animation-delay: 0.25s; }

    @keyframes psSuccessModalIn {
        0% { opacity: 0; transform: translateY(12px) scale(0.98); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes psSuccessCardIn {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    @keyframes psSuccessIconPop {
        0% { opacity: 0; transform: scale(0.7); }
        70% { transform: scale(1.05); }
        100% { opacity: 1; transform: scale(1); }
    }

    @keyframes psSuccessFadeUp {
        0% { opacity: 0; transform: translateY(8px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .project-success-modal .modal-body,
    .project-details-modal .modal-body {
        padding: 0;
        max-height: 85vh;
        overflow-y: auto;
    }

    .ps-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 28px 32px 10px;
        background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
    }

    .ps-header-icon {
        flex: 0 0 auto;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #dcfce7;
        color: #16a34a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .ps-header-text {
        flex: 1;
        min-width: 0;
    }

    .ps-header-text h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 3px;
    }

    .ps-header-text p {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0;
    }

    .ps-close {
        flex: 0 0 auto;
        margin-left: auto;
    }

    .ps-body {
        padding: 20px 32px 28px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Loading / error states for AJAX-loaded modal content */
    .pd-loading-state,
    .pd-error-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 90px 24px;
        color: #64748b;
    }

    .pd-error-state i {
        color: #dc2626;
    }

    /* Project overview card */
    .ps-overview-card {
        border: 1px solid var(--mi-border, #e2e8f0);
        border-radius: 14px;
        padding: 20px;
        background: #fff;
    }

    .ps-overview-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 18px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--mi-border, #e2e8f0);
    }

    .ps-overview-identity {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ps-overview-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--mi-accent, #198754);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .ps-overview-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.25;
    }

    .ps-overview-id {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .ps-overview-status {
        text-align: right;
        flex: 0 0 auto;
    }

    .ps-overview-status .ps-field-label {
        display: block;
        margin-bottom: 4px;
    }

    .ps-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px 20px;
    }

    .ps-field-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 4px;
    }

    .ps-field-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 5px;
        line-height: 1.35;
    }

    .ps-field-value.ps-muted-danger {
        color: #dc2626;
        font-weight: 600;
    }

    .ps-field-value i {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    /* Quick action cards */
    .ps-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-actions-grid.pd-actions-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .ps-action-card {
        border: 1px solid var(--mi-border, #e2e8f0);
        border-radius: 14px;
        padding: 16px 18px;
        text-align: left;
        background: #fff;
        text-decoration: none;
        display: block;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
    }

    .ps-action-card:hover {
        border-color: var(--mi-accent, #198754);
        box-shadow: 0 8px 20px rgba(22, 101, 52, 0.1);
        transform: translateY(-1px);
    }

    .ps-action-card i {
        color: var(--mi-accent, #198754);
        font-size: 1.05rem;
        margin-right: 7px;
    }

    .ps-action-title {
        font-size: 0.86rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px;
    }

    .ps-action-copy {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0;
        line-height: 1.4;
    }

    /* Section headers */
    .ps-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .ps-section-head h6 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .ps-readonly-pill {
        font-size: 0.64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        background: #f1f5f9;
        border-radius: 999px;
        padding: 3px 9px;
    }

    /* Mini status cards */
    .ps-mini-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-mini-grid.cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .ps-mini-card {
        border: 1px solid var(--mi-border, #e2e8f0);
        border-radius: 12px;
        padding: 14px;
        background: #fff;
    }

    .ps-mini-card.ps-mini-alert {
        grid-column: 1 / -1;
        background: #fff7ed;
        border-color: #fed7aa;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ps-mini-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        margin-bottom: 8px;
    }

    .ps-mini-icon.success {
        background: #dcfce7;
        color: #16a34a;
    }

    .ps-mini-icon.neutral {
        background: #f1f5f9;
        color: #64748b;
    }

    .ps-mini-icon.warning {
        background: #ffedd5;
        color: #ea580c;
    }

    .ps-mini-label {
        font-size: 0.74rem;
        color: #64748b;
        margin-bottom: 5px;
    }

    .ps-mini-value {
        font-size: 0.82rem;
        font-weight: 700;
        color: #0f172a;
    }

    .ps-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        margin-top: 4px;
    }

    .ps-status-pill.success {
        background: #dcfce7;
        color: #166534;
    }

    .ps-status-pill.warning {
        background: #fef3c7;
        color: #b45309;
    }

    .ps-note {
        font-size: 0.72rem;
        color: #94a3b8;
        margin: 8px 2px 0;
        line-height: 1.4;
    }

    /* Project team */
    .ps-team-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .ps-team-card {
        border: 1px solid var(--mi-border, #e2e8f0);
        border-radius: 12px;
        padding: 12px 10px;
        text-align: center;
        background: #fff;
    }

    .ps-team-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #f0fdf4;
        color: var(--mi-accent, #198754);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        margin: 0 auto 6px;
    }

    .ps-team-role {
        font-size: 0.64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 3px;
    }

    .ps-team-name {
        font-size: 0.78rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
        word-break: break-word;
    }

    .ps-team-sub {
        font-size: 0.68rem;
        color: #94a3b8;
        line-height: 1.3;
        word-break: break-word;
    }

    /* Footer */
    .ps-footer {
        border-top: 1px solid var(--mi-border, #e2e8f0);
        padding: 18px 32px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (max-width: 767.98px) {
        .ps-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ps-mini-grid,
        .ps-mini-grid.cols-3 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ps-actions-grid.pd-actions-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 480px) {
        .ps-header,
        .ps-body,
        .ps-footer {
            padding-left: 18px;
            padding-right: 18px;
        }

        .ps-detail-grid {
            grid-template-columns: 1fr;
        }

        .ps-actions-grid,
        .ps-actions-grid.pd-actions-grid {
            grid-template-columns: 1fr;
        }

        .ps-team-grid {
            grid-template-columns: 1fr;
        }

        .ps-mini-grid,
        .ps-mini-grid.cols-3 {
            grid-template-columns: 1fr;
        }

        .ps-footer .btn {
            width: 100%;
        }
    }



/* =======================================================================
   Capacitor Mobile Project Management Patch
   ======================================================================= */
@media (max-width: 576px) {
    .mi-page.inventory-green-theme.main-dashboard-container {
        display: block !important;
        padding: 0 0 18px !important;
        overflow-x: hidden !important;
    }

    .mi-page.inventory-green-theme .left-dashboard-content {
        width: 100% !important;
        min-width: 0 !important;
    }

    .mi-page.inventory-green-theme .dashboard-header-row {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) !important;
        gap: 12px !important;
    }

    .mi-page.inventory-green-theme .dashboard-title-area h2 {
        max-width: 100% !important;
        white-space: normal !important;
        font-size: 1.7rem !important;
        line-height: 1.08 !important;
    }

    .mi-page.inventory-green-theme .dashboard-header-row > .d-flex {
        display: grid !important;
        grid-template-columns: 1fr !important;
        width: 100% !important;
    }

    .mi-page.inventory-green-theme .dashboard-header-row .btn {
        width: 100% !important;
        min-width: 0 !important;
        justify-content: center !important;
    }

    .mi-page.inventory-green-theme .metrics-row-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 10px !important;
        margin-top: 14px !important;
        margin-bottom: 16px !important;
    }

    .mi-page.inventory-green-theme .metric-card-box {
        align-items: flex-start !important;
        min-height: 108px !important;
        padding: 13px !important;
        gap: 10px !important;
    }

    .mi-page.inventory-green-theme .metric-icon-wrapper {
        width: 38px !important;
        height: 38px !important;
        flex: 0 0 38px !important;
        border-radius: 12px !important;
    }

    .mi-page.inventory-green-theme .metric-info-text .stat-lbl,
    .mi-page.inventory-green-theme .metric-info-text .stat-sub {
        font-size: 10px !important;
        line-height: 1.35 !important;
    }

    .mi-page.inventory-green-theme .metric-info-text .stat-num {
        font-size: 1.35rem !important;
    }

    .mi-page.inventory-green-theme .filter-toolbar-panel {
        padding: 13px !important;
        border-radius: 16px !important;
    }

    .mi-page.inventory-green-theme .filter-toolbar-panel .filter-form-row {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }

    .mi-page.inventory-green-theme .search-input-container,
    .mi-page.inventory-green-theme .filter-dropdown-select,
    .mi-page.inventory-green-theme .filter-actions-right {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        margin-left: 0 !important;
    }

    .mi-page.inventory-green-theme .table-responsive {
        overflow-x: hidden !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table,
    .mi-page.inventory-green-theme .dg-custom-table thead,
    .mi-page.inventory-green-theme .dg-custom-table tbody,
    .mi-page.inventory-green-theme .dg-custom-table tr,
    .mi-page.inventory-green-theme .dg-custom-table td {
        display: block !important;
        width: 100% !important;
        min-width: 0 !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table {
        min-width: 0 !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table thead {
        display: none !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table tbody tr {
        margin: 0 0 12px !important;
        padding: 13px !important;
        border: 1px solid #e5ece7 !important;
        border-radius: 16px !important;
        background: #ffffff !important;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05) !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table tbody td {
        display: grid !important;
        grid-template-columns: 86px minmax(0, 1fr) !important;
        align-items: start !important;
        gap: 10px !important;
        padding: 8px 0 !important;
        border: 0 !important;
        text-align: left !important;
        font-size: 12px !important;
        word-break: normal !important;
        overflow-wrap: anywhere !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table tbody td::before {
        color: #64748b !important;
        font-size: 9px !important;
        font-weight: 800 !important;
        letter-spacing: 0.08em !important;
        line-height: 1.25 !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table tbody td:nth-child(1)::before { content: 'Project'; }
    .mi-page.inventory-green-theme .dg-custom-table tbody td:nth-child(2)::before { content: 'Supervisor'; }
    .mi-page.inventory-green-theme .dg-custom-table tbody td:nth-child(3)::before { content: 'Status'; }
    .mi-page.inventory-green-theme .dg-custom-table tbody td:nth-child(4)::before { content: 'Progress'; }
    .mi-page.inventory-green-theme .dg-custom-table tbody td:nth-child(5)::before { content: 'Duration'; }
    .mi-page.inventory-green-theme .dg-custom-table tbody td:nth-child(6)::before { content: 'Actions'; }

    .mi-page.inventory-green-theme .dg-custom-table tbody td:first-child {
        display: block !important;
        padding-bottom: 12px !important;
        margin-bottom: 6px !important;
        border-bottom: 1px solid #eef2f7 !important;
    }

    .mi-page.inventory-green-theme .dg-custom-table tbody td:first-child::before {
        display: none !important;
    }

    .mi-page.inventory-green-theme .project-title-bold,
    .mi-page.inventory-green-theme .project-subtext-muted,
    .mi-page.inventory-green-theme .project-date-badge {
        white-space: normal !important;
        max-width: 100% !important;
        word-break: normal !important;
        overflow-wrap: anywhere !important;
    }

    .mi-page.inventory-green-theme .supervisor-cell-info {
        align-items: flex-start !important;
    }

    .mi-page.inventory-green-theme .custom-progress-container {
        width: 100% !important;
    }

    .mi-page.inventory-green-theme .action-buttons-flex {
        justify-content: flex-start !important;
        flex-wrap: wrap !important;
    }

    .mi-page.inventory-green-theme .table-pagination-footer-bar {
        align-items: flex-start !important;
        flex-direction: column !important;
    }

    .mi-page.inventory-green-theme .right-details-sidebar-panel,
    .mi-page.inventory-green-theme .right-details-sidebar-panel.is-visible {
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        margin-top: 16px !important;
    }
}

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addProjectModalEl = document.getElementById('addProjectModal');
    const addProjectForm = document.querySelector('#addProjectModal form');

    window.projectArchiveVisibleRows = [];
    window.projectArchiveCurrentPage = 1;

    window.renderArchivedProjectsPage = function (pageNumber) {
        const pageSize = 10;
        const visibleRows = window.projectArchiveVisibleRows || [];
        const totalPages = visibleRows.length > 0 ? Math.ceil(visibleRows.length / pageSize) : 1;
        const safePage = Math.min(Math.max(parseInt(pageNumber, 10) || 1, 1), totalPages);
        window.projectArchiveCurrentPage = safePage;

        visibleRows.forEach(function (row, index) {
            const shouldShow = index >= (safePage - 1) * pageSize && index < safePage * pageSize;
            row.style.display = shouldShow ? '' : 'none';
        });

        const paginationEl = document.getElementById('projectArchivesPagination');
        const summaryEl = document.getElementById('projectArchivesPaginationSummary');
        const emptyState = document.getElementById('projectArchivesEmptyState');

        if (emptyState) {
            emptyState.style.display = visibleRows.length ? 'none' : '';
        }

        if (paginationEl) {
            paginationEl.innerHTML = '';
            if (visibleRows.length === 0) {
                const item = document.createElement('li');
                item.className = 'page-item disabled';
                item.innerHTML = '<span class="page-link" style="color:#198754;">1</span>';
                paginationEl.appendChild(item);
            } else {
                const prevItem = document.createElement('li');
                prevItem.className = 'page-item' + (safePage === 1 ? ' disabled' : '');
                prevItem.innerHTML = '<a class="page-link" href="#" tabindex="-1" style="color:#198754;" aria-label="Previous page" onclick="event.preventDefault(); window.renderArchivedProjectsPage(' + (safePage - 1) + ');"><i class="bi bi-chevron-left"></i></a>';
                paginationEl.appendChild(prevItem);

                for (let page = 1; page <= totalPages; page++) {
                    const item = document.createElement('li');
                    item.className = 'page-item' + (page === safePage ? ' active' : '');
                    item.innerHTML = '<a class="page-link" href="#" style="' + (page === safePage ? 'background-color:#198754; border-color:#198754; color:#fff;' : 'color:#198754;') + '" onclick="event.preventDefault(); window.renderArchivedProjectsPage(' + page + ');">' + page + '</a>';
                    paginationEl.appendChild(item);
                }

                const nextItem = document.createElement('li');
                nextItem.className = 'page-item' + (safePage === totalPages ? ' disabled' : '');
                nextItem.innerHTML = '<a class="page-link" href="#" style="color:#198754;" aria-label="Next page" onclick="event.preventDefault(); window.renderArchivedProjectsPage(' + (safePage + 1) + ');"><i class="bi bi-chevron-right"></i></a>';
                paginationEl.appendChild(nextItem);
            }
        }

        if (summaryEl) {
            const start = visibleRows.length ? (safePage - 1) * pageSize + 1 : 0;
            const end = Math.min(safePage * pageSize, visibleRows.length);
            summaryEl.textContent = visibleRows.length ? 'Showing ' + start + '–' + end + ' of ' + visibleRows.length + ' archived projects' : 'No archived projects match the current filters';
        }
    };

    window.filterArchivedProjects = function () {
        const searchValue = (document.getElementById('projectArchiveSearch')?.value || '').trim().toLowerCase();
        const clientValue = document.getElementById('projectArchiveClientFilter')?.value || '';
        const engineerValue = document.getElementById('projectArchiveEngineerFilter')?.value || '';
        const rows = Array.from(document.querySelectorAll('#projectArchivesTableBody .archive-row'));

        window.projectArchiveVisibleRows = rows.filter(function (row) {
            const rowText = (row.getAttribute('data-search') || '').toLowerCase();
            const matchesSearch = !searchValue || rowText.includes(searchValue);
            const matchesClient = !clientValue || (row.getAttribute('data-client-id') || '') === clientValue;
            const matchesEngineer = !engineerValue || (row.getAttribute('data-engineer-id') || '') === engineerValue;
            return matchesSearch && matchesClient && matchesEngineer;
        });

        rows.forEach(function (row) {
            row.style.display = 'none';
        });

        window.projectArchiveCurrentPage = 1;
        window.renderArchivedProjectsPage(1);
    };

    const projectArchiveSearch = document.getElementById('projectArchiveSearch');
    if (projectArchiveSearch) {
        projectArchiveSearch.addEventListener('input', function () {
            window.filterArchivedProjects();
        });
    }

    ['projectArchiveClientFilter', 'projectArchiveEngineerFilter'].forEach(function (id) {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function () {
                window.filterArchivedProjects();
            });
        }
    });

    window.filterArchivedProjects();
    const editProjectModalEl = document.getElementById('editProjectModal');
    const editProjectForm = document.getElementById('editProjectForm');
    const editProjectIdInput = document.getElementById('editProjectId');
    const editStartDateInput = document.getElementById('edit_start_date');
    const editTargetEndDateInput = document.getElementById('edit_target_end_date');
    const editActualEndDateInput = document.getElementById('edit_actual_end_date');
    const startDateInput = document.getElementById('modal_start_date');
    const targetEndDateInput = document.getElementById('modal_target_end_date');
    const actualEndDateInput = document.getElementById('modal_actual_end_date');

    if (addProjectModalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const addProjectModal = window.bootstrap.Modal.getOrCreateInstance(addProjectModalEl);

        if (window.location.search.includes('error') || document.querySelector('#addProjectModal .alert-danger')) {
            addProjectModal.show();
        }

        addProjectModalEl.addEventListener('hidden.bs.modal', function () {
            if (addProjectForm) {
                addProjectForm.reset();
            }
        });
    }

    function validateProjectDates() {
        if (!startDateInput || !targetEndDateInput || !actualEndDateInput) return true;

        const startDate = startDateInput.value;
        const targetEndDate = targetEndDateInput.value;
        const actualEndDate = actualEndDateInput.value;
        let isValid = true;

        if (startDate && targetEndDate && targetEndDate < startDate) {
            targetEndDateInput.setCustomValidity('Planned end date must be on or after the planned start date.');
            isValid = false;
        } else {
            targetEndDateInput.setCustomValidity('');
        }

        if (startDate && actualEndDate) {
            if (actualEndDate < startDate) {
                actualEndDateInput.setCustomValidity('Actual end date cannot be before the planned start date.');
                isValid = false;
            } else if (targetEndDate && actualEndDate > targetEndDate) {
                actualEndDateInput.setCustomValidity('Actual end date cannot be after the planned end date.');
                isValid = false;
            } else {
                actualEndDateInput.setCustomValidity('');
            }
        } else {
            actualEndDateInput.setCustomValidity('');
        }

        return isValid;
    }

    [startDateInput, targetEndDateInput, actualEndDateInput].filter(Boolean).forEach(function (input) {
        input.addEventListener('change', validateProjectDates);
        input.addEventListener('input', validateProjectDates);
    });

    if (addProjectForm) {
        addProjectForm.addEventListener('submit', function (event) {
            if (!validateProjectDates()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    function validateEditProjectDates() {
        if (!editStartDateInput || !editTargetEndDateInput || !editActualEndDateInput) return true;

        const startDate = editStartDateInput.value;
        const targetEndDate = editTargetEndDateInput.value;
        const actualEndDate = editActualEndDateInput.value;
        let isValid = true;

        if (startDate && targetEndDate && targetEndDate < startDate) {
            editTargetEndDateInput.setCustomValidity('Planned end date must be on or after the planned start date.');
            isValid = false;
        } else {
            editTargetEndDateInput.setCustomValidity('');
        }

        if (startDate && actualEndDate) {
            if (actualEndDate < startDate) {
                editActualEndDateInput.setCustomValidity('Actual end date cannot be before the planned start date.');
                isValid = false;
            } else if (targetEndDate && actualEndDate > targetEndDate) {
                editActualEndDateInput.setCustomValidity('Actual end date cannot be after the planned end date.');
                isValid = false;
            } else {
                editActualEndDateInput.setCustomValidity('');
            }
        } else {
            editActualEndDateInput.setCustomValidity('');
        }

        return isValid;
    }

    [editStartDateInput, editTargetEndDateInput, editActualEndDateInput].filter(Boolean).forEach(function (input) {
        input.addEventListener('change', validateEditProjectDates);
        input.addEventListener('input', validateEditProjectDates);
    });

    document.querySelectorAll('.project-action-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            const action = form.getAttribute('data-project-confirm');
            if (!action || !window.Swal) return;

            event.preventDefault();
            const title = form.getAttribute('data-confirm-title') || 'Confirm Action';
            const text = form.getAttribute('data-confirm-text') || 'Please confirm this action.';
            const confirmText = form.getAttribute('data-confirm-button') || 'Continue';
            const cancelText = form.getAttribute('data-cancel-button') || 'Cancel';

            Swal.fire({
                title: title,
                text: text,
                icon: action === 'delete' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonColor: action === 'delete' ? '#b91c1c' : '#166534',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                allowOutsideClick: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    function buildAdminProjectsUrl(projectId) {
        if (!projectId) return '{{ route('admin.projects.index') }}';

        const currentPath = window.location.pathname.replace(/\/+$/, '');
        const projectsSuffix = '/admin/projects';
        let basePath = '';

        if (currentPath.endsWith(projectsSuffix)) {
            basePath = currentPath.slice(0, -projectsSuffix.length) || '';
        } else if (currentPath.includes(projectsSuffix + '/')) {
            basePath = currentPath.slice(0, currentPath.indexOf(projectsSuffix));
        } else if (currentPath.includes('/admin/projects')) {
            basePath = currentPath.slice(0, currentPath.indexOf('/admin/projects'));
        } else {
            basePath = currentPath;
        }

        const normalizedBasePath = basePath && basePath !== '/' ? basePath : '';
        return `${normalizedBasePath}${projectsSuffix}/${projectId}`;
    }

    function syncEditProjectFormAction(projectId) {
        if (!editProjectForm) return;

        if (projectId) {
            editProjectForm.action = buildAdminProjectsUrl(projectId);
        } else {
            editProjectForm.action = '{{ route('admin.projects.index') }}';
        }
    }

    if (editProjectForm) {
        editProjectForm.addEventListener('submit', function (event) {
            const projectId = editProjectIdInput && editProjectIdInput.value ? editProjectIdInput.value : '';
            syncEditProjectFormAction(projectId);

            if (!validateEditProjectDates()) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            if (editStartDateInput) editStartDateInput.setCustomValidity('');
            if (editTargetEndDateInput) editTargetEndDateInput.setCustomValidity('');
            if (editActualEndDateInput) editActualEndDateInput.setCustomValidity('');
        });
    }

    const editProjectIdFromSession = @json(session('edit_project_id', ''));
    if (editProjectForm && editProjectIdInput && editProjectIdFromSession) {
        editProjectIdInput.value = editProjectIdFromSession;
        syncEditProjectFormAction(editProjectIdFromSession);
    }

    if (editProjectForm && editProjectIdInput && editProjectIdInput.value) {
        syncEditProjectFormAction(editProjectIdInput.value);
    }

    const flashSuccessMessage = @json(session('success'));
    const flashErrorMessage = @json(session('error'));
    const flashInfoMessage = @json(session('info'));
    const flashSuccessTitle = @json(session('success_title'));
    const flashErrorTitle = @json(session('error_title', 'Project update failed'));
    const showCreateSuccessModal = @json((bool) session('show_create_success_modal', false));
    const showEditProjectModal = @json((bool) session('show_edit_project_modal', false));

    if (window.Swal && !showCreateSuccessModal && (flashSuccessMessage || flashErrorMessage || flashInfoMessage)) {
        Swal.fire({
            title: flashErrorMessage ? (flashErrorTitle || 'Project update failed') : (flashInfoMessage ? 'Update notice' : (flashSuccessTitle || 'Success')),
            text: flashErrorMessage || flashSuccessMessage || flashInfoMessage,
            icon: flashErrorMessage ? 'error' : (flashInfoMessage ? 'info' : 'success'),
            confirmButtonColor: '#166534',
            allowOutsideClick: false
        });
    }

    // Only reopen the Edit modal when the server explicitly requests it for
    // a failed edit attempt. Successful saves should close it.
    if (editProjectModalEl && showEditProjectModal && editProjectIdFromSession && flashErrorMessage && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        window.bootstrap.Modal.getOrCreateInstance(editProjectModalEl).show();
    }

    // Auto-open the "Project Created Successfully" modal (shown over the projects table)
    const projectSuccessModalEl = document.getElementById('projectSuccessModal');
    if (projectSuccessModalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
        const projectSuccessModal = new window.bootstrap.Modal(projectSuccessModalEl, { backdrop: 'static', keyboard: false });
        projectSuccessModal.show();

        projectSuccessModalEl.addEventListener('shown.bs.modal', function () {
            projectSuccessModalEl.classList.add('is-animated');
        });

        const psTimelineLink = document.getElementById('ps-timeline-link');
        if (psTimelineLink) {
            psTimelineLink.addEventListener('click', function (event) {
                const hasPhases = {{ isset($newProject) && $newProject && $newProject->phase_count > 0 ? 'true' : 'false' }};
                if (!hasPhases) {
                    event.preventDefault();
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Construction Phases Required',
                            text: 'Please create construction phases before adding timeline milestones.',
                            icon: 'warning',
                            confirmButtonColor: '#166534'
                        });
                    }
                }
            });
        }
    }

    const filterForm = document.getElementById('project-filters-form');
    const searchInput = filterForm?.querySelector('input[name="search"]');
    const statusSelect = filterForm?.querySelector('select[name="status"]');
    const clientSelect = filterForm?.querySelector('select[name="client"]');
    const supervisorSelect = filterForm?.querySelector('select[name="supervisor"]');
    const sortSelect = filterForm?.querySelector('select[name="sort_by"]');

    let searchTimer;

    async function loadProjectsTable(url) {
        if (!url) return;

        if (searchInput) {
            searchInput.classList.add('loading');
        }

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Network response was not ok');
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTableCard = doc.querySelector('.table-container-card');
            const currentTableCard = document.querySelector('.table-container-card');
            if (newTableCard && currentTableCard) {
                currentTableCard.replaceWith(newTableCard);
            }

            try { history.replaceState(null, '', url); } catch (e) { /* ignore */ }

            bindViewButtons();
            attachProjectsPaginationHandlers();
        } catch (err) {
            console.error('AJAX table update failed, falling back to full submit', err);
            if (filterForm) {
                filterForm.submit();
            }
        } finally {
            if (searchInput) {
                searchInput.classList.remove('loading');
            }
        }
    }

    async function ajaxSubmitFilters() {
        if (!filterForm) return;
        const params = new URLSearchParams(new FormData(filterForm));
        const url = filterForm.action + '?' + params.toString();
        await loadProjectsTable(url);
    }

    function submitFilters() {
        // kept for compatibility
        return ajaxSubmitFilters();
    }

    function handleProjectsPaginationClick(event) {
        const href = this.getAttribute('href');
        if (!href || href === '#') return;
        event.preventDefault();
        event.stopPropagation();
        loadProjectsTable(href);
    }

    function attachProjectsPaginationHandlers() {
        document.querySelectorAll('.table-container-card .pagination a, .table-container-card .table-pagination-footer-bar a').forEach(function(link) {
            link.removeEventListener('click', handleProjectsPaginationClick);
            link.addEventListener('click', handleProjectsPaginationClick);
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            ajaxSubmitFilters();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                ajaxSubmitFilters();
            }, 800);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                ajaxSubmitFilters();
            }
        });
    }

    [statusSelect, clientSelect, supervisorSelect, sortSelect].filter(Boolean).forEach(function(select) {
        select.addEventListener('change', function() {
            ajaxSubmitFilters();
        });
    });

    attachProjectsPaginationHandlers();

    // Bind view/details panel buttons - extracted so we can re-run after AJAX updates
    function bindViewButtons() {
        const viewBtns = document.querySelectorAll('.trigger-details-panel');
        viewBtns.forEach(btn => {
            btn.removeEventListener('click', openSidebarFromButton);
            btn.addEventListener('click', openSidebarFromButton);
        });
    }

    function populateEditProjectModal(project, supervisorId, triggerBtn) {
        const form = document.getElementById('editProjectForm');
        const modalEl = document.getElementById('editProjectModal');
        const projectIdInput = document.getElementById('editProjectId');
        const projectNameInput = document.getElementById('edit_project_name');
        const projectLocationInput = document.getElementById('edit_project_location');
        const descriptionInput = document.getElementById('edit_description');
        const clientSelect = document.getElementById('edit_client_id');
        const startDateInput = document.getElementById('edit_start_date');
        const targetEndDateInput = document.getElementById('edit_target_end_date');
        const actualEndDateInput = document.getElementById('edit_actual_end_date');
        const timeInInput = document.getElementById('edit_time_in');
        const timeOutInput = document.getElementById('edit_time_out');
        const supervisorSelect = document.getElementById('edit_supervisor_id');
        const statusSelect = document.getElementById('edit_status');
        const modalTitle = document.getElementById('editProjectModalLabel');

        if (!form || !modalEl || !project) return;

        const explicitLocation = triggerBtn ? (triggerBtn.getAttribute('data-project-location') || '') : '';
        const projectLocationValue = explicitLocation || project.project_location || project.location || '';
        const currentStatus = String(project.status || 'planning').toLowerCase();

        form.action = buildAdminProjectsUrl(project.project_id);
        if (projectIdInput) projectIdInput.value = project.project_id || '';
        if (projectNameInput) projectNameInput.value = project.project_name || '';
        if (projectLocationInput) projectLocationInput.value = projectLocationValue;
        if (descriptionInput) descriptionInput.value = project.description || '';

        const editImagePreview = document.getElementById('editProjectImagePreview');
        const editImagePreviewImg = editImagePreview ? editImagePreview.querySelector('img') : null;
        const editRemoveImageInput = document.getElementById('editRemoveImage');
        if (editImagePreview && editImagePreviewImg) {
            if (project.project_image) {
                editImagePreviewImg.src = '/storage/' + String(project.project_image).replace(/^\/+/, '');
                editImagePreview.classList.remove('d-none');
            } else {
                editImagePreviewImg.src = '';
                editImagePreview.classList.add('d-none');
            }
        }
        if (editRemoveImageInput) editRemoveImageInput.checked = false;

        if (clientSelect) clientSelect.value = project.client_id || '';
        if (startDateInput) startDateInput.value = project.start_date ? project.start_date.split('T')[0] : '';
        if (targetEndDateInput) targetEndDateInput.value = project.target_end_date ? project.target_end_date.split('T')[0] : '';
        if (actualEndDateInput) actualEndDateInput.value = project.actual_end_date ? project.actual_end_date.split('T')[0] : '';
        if (timeInInput) timeInInput.value = project.time_in || '';
        if (timeOutInput) timeOutInput.value = project.time_out || '';
        if (supervisorSelect) supervisorSelect.value = supervisorId || '';
        if (statusSelect) {
            Array.from(statusSelect.options).forEach(function(option) {
                option.disabled = false;
            });

            const helpText = document.getElementById('editStatusHelpText');
            if (helpText) {
                helpText.textContent = 'Choose the project’s current lifecycle state. Completed projects cannot be moved back to planning or in progress.';
            }

            if (currentStatus === 'archived') {
                statusSelect.value = 'planning';
                Array.from(statusSelect.options).forEach(function(option) {
                    option.disabled = true;
                });
                statusSelect.disabled = true;
                if (helpText) helpText.textContent = 'Archived projects are read-only and cannot be edited.';
            } else if (currentStatus === 'completed') {
                Array.from(statusSelect.options).forEach(function(option) {
                    if (option.value !== 'completed') {
                        option.disabled = true;
                    }
                });
                statusSelect.value = 'completed';
                if (helpText) helpText.textContent = 'This project is already completed. Only the completion record can be updated.';
            } else if (currentStatus === 'ongoing') {
                Array.from(statusSelect.options).forEach(function(option) {
                    if (option.value === 'planning') {
                        option.disabled = true;
                    }
                });
                statusSelect.value = 'ongoing';
                if (helpText) helpText.textContent = 'Projects in progress can remain in progress or be marked on hold/completed.';
            } else {
                Array.from(statusSelect.options).forEach(function(option) {
                    if (option.value === 'planning') {
                        option.disabled = false;
                    }
                });
                statusSelect.value = currentStatus || 'planning';
                if (helpText) helpText.textContent = 'Planning projects can move forward to in progress. Completion is only allowed after the project is already in progress.';
            }
        }
        if (modalTitle) modalTitle.textContent = 'Edit Project';

        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            const modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }
    }

    function openSidebarFromButton() {
        const btn = this;
        const project = JSON.parse(btn.getAttribute('data-project-json'));
        const projectId = project.project_id;

        if (window.innerWidth <= 991.98 && projectId) {
            openProjectDetailsModal(projectId);
            return;
        }

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
        const sidePhases = document.getElementById('sidePhases');
        const sideMilestones = document.getElementById('sideMilestones');
        const sideReports = document.getElementById('sideReports');
        const sideMaterials = document.getElementById('sideMaterials');
        const sideAttendance = document.getElementById('sideAttendance');
        const sideDaysLeft = document.getElementById('sideDaysLeft');
        const sideEditProjectBtn = document.getElementById('sideEditProjectBtn');
        const sideArchiveForm = document.getElementById('sideArchiveForm');
        const sideDeleteForm = document.getElementById('sideDeleteForm');

        const supervisorName = btn.getAttribute('data-supervisor-name');
        const supervisorId = btn.getAttribute('data-supervisor-id');
        const clientName = btn.getAttribute('data-client-name');
        const status = btn.getAttribute('data-status');
        const startDate = btn.getAttribute('data-start-date');
        const targetEndDate = btn.getAttribute('data-target-end-date');
        const daysLeft = btn.getAttribute('data-days-left');
        const pct = btn.getAttribute('data-pct');
        const phases = btn.getAttribute('data-phases');
        const milestones = btn.getAttribute('data-milestones');
        const reports = btn.getAttribute('data-reports');
        const materials = btn.getAttribute('data-materials');
        const attendance = btn.getAttribute('data-attendance');

        if (sideProjectName) sideProjectName.textContent = project.project_name || '';
        if (sideProjectLocation) sideProjectLocation.textContent = project.project_location || project.location || '';
        if (sideDescription) sideDescription.textContent = project.description || 'No description provided.';
        if (sideSupervisorName) sideSupervisorName.textContent = supervisorName;
        if (sideClientName) sideClientName.textContent = clientName;
        if (sidePlannedDates) {
            const plannedStart = startDate ? new Date(startDate + 'T00:00:00') : null;
            const plannedEnd = targetEndDate ? new Date(targetEndDate + 'T00:00:00') : null;
            const formatDate = (value) => value ? value.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Not set';
            sidePlannedDates.textContent = plannedStart && plannedEnd ? `${formatDate(plannedStart)} - ${formatDate(plannedEnd)}` : (plannedStart ? formatDate(plannedStart) : (plannedEnd ? formatDate(plannedEnd) : 'Not set'));
        }
        if (sidePhases) sidePhases.textContent = phases ?? '0';
        if (sideMilestones) sideMilestones.textContent = milestones ?? '0';
        if (sideReports) sideReports.textContent = reports ?? '0';
        if (sideMaterials) sideMaterials.textContent = materials ?? '0';
        if (sideAttendance) sideAttendance.textContent = attendance ?? '0';
        if (sideDaysLeft) sideDaysLeft.textContent = daysLeft ?? '0';
        if (sideProgressPctText) sideProgressPctText.textContent = pct + '%';
        if (sideProgressBarFill) sideProgressBarFill.style.width = pct + '%';

        const normalizedSidebarStatus = String(status || project.status || 'planning').toLowerCase();

        if (sideProjectStatus) {
            if (normalizedSidebarStatus === 'ongoing' || normalizedSidebarStatus === 'in_progress' || normalizedSidebarStatus === 'inprogress' || normalizedSidebarStatus === 'active') {
                sideProjectStatus.textContent = 'In Progress';
                sideProjectStatus.className = 'status-pill in-progress';
            } else if (normalizedSidebarStatus === 'on_hold' || normalizedSidebarStatus === 'pending') {
                sideProjectStatus.textContent = 'On Hold';
                sideProjectStatus.className = 'status-pill on-hold';
            } else if (normalizedSidebarStatus === 'planning' || normalizedSidebarStatus === 'not_started' || normalizedSidebarStatus === 'paused' || normalizedSidebarStatus === 'delayed') {
                sideProjectStatus.textContent = 'Planning';
                sideProjectStatus.className = 'status-pill planning';
            } else if (normalizedSidebarStatus === 'completed' || normalizedSidebarStatus === 'complete' || normalizedSidebarStatus === 'finished') {
                sideProjectStatus.textContent = 'Completed';
                sideProjectStatus.className = 'status-pill completed';
            } else if (normalizedSidebarStatus === 'archived') {
                sideProjectStatus.textContent = 'Archived';
                sideProjectStatus.className = 'status-pill completed';
            } else {
                sideProjectStatus.textContent = 'Planning';
                sideProjectStatus.className = 'status-pill completed';
            }
        }

        if (sideEditProjectBtn) {
            if (normalizedSidebarStatus === 'archived') {
                sideEditProjectBtn.style.display = 'none';
            } else {
                sideEditProjectBtn.style.display = 'inline-flex';
                sideEditProjectBtn.onclick = function(e) {
                    e.preventDefault();
                    populateEditProjectModal(project, supervisorId, btn);
                };
            }
        }

        if (sideArchiveForm) sideArchiveForm.style.display = normalizedSidebarStatus === 'archived' ? 'none' : 'inline-flex';
        if (sideDeleteForm) sideDeleteForm.style.display = normalizedSidebarStatus === 'archived' ? 'none' : 'inline-flex';
        if (sideArchiveForm) {
            const archiveAction = sideArchiveForm.getAttribute('action');
            sideArchiveForm.setAttribute('action', archiveAction.replace('__PROJECT_ID__', project.project_id));
        }
        if (sideDeleteForm) {
            const deleteAction = sideDeleteForm.getAttribute('action');
            sideDeleteForm.setAttribute('action', deleteAction.replace('__PROJECT_ID__', project.project_id));
        }

        if (sidebar) {
            sidebar.classList.remove('is-refreshing');
            void sidebar.offsetWidth;
            sidebar.classList.add('is-visible', 'is-refreshing');
            sidebar.setAttribute('aria-hidden', 'false');
            window.clearTimeout(sidebar._refreshTimer);
            sidebar._refreshTimer = window.setTimeout(function () {
                sidebar.classList.remove('is-refreshing');
            }, 220);
        }
    }

    // Initial bind
    bindViewButtons();

    const sidebar = document.getElementById('projectDetailsSidebar');
    function closeProjectDetailsSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('is-visible', 'is-refreshing');
        sidebar.setAttribute('aria-hidden', 'true');
    }

    const closeBtn = document.getElementById('closeSidebarBtn');
    if (closeBtn) closeBtn.addEventListener('click', closeProjectDetailsSidebar);
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
    const sideEditProjectBtn = document.getElementById('sideEditProjectBtn');

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', closeProjectDetailsSidebar);
    }

    // Opens the full Project Details modal, loaded via AJAX from the show() route.
    // Reused both from the sidebar's "View Details" button and can be called directly with an id.
    async function openProjectDetailsModal(projectId) {
        const modalEl = document.getElementById('projectDetailsModal');
        const bodyEl = document.getElementById('projectDetailsModalBody');
        if (!modalEl || !bodyEl || !projectId) return;

        bodyEl.innerHTML = `
            <div class="pd-loading-state">
                <div class="spinner-border" role="status" style="color: var(--mi-accent, #198754);"></div>
                <p class="mt-3 mb-0">Loading project details...</p>
            </div>`;

        let modalInstance = null;
        if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
            modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }

        try {
            const res = await fetch(`/admin/projects/${projectId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('Request failed with status ' + res.status);
            const html = await res.text();
            bodyEl.innerHTML = html;
        } catch (err) {
            console.error('Failed to load project details', err);
            bodyEl.innerHTML = `
                <div class="pd-error-state">
                    <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                    Unable to load project details right now. Please try again.
                </div>`;
        }
    }

});
</script>
@endpush