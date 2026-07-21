@extends('layouts.admin')

@section('title', 'Reports - D&G Construction Monitor')
@section('page_title', 'Reports')

@push('styles')
<style>
    /* Page-level scroll fix: ensure the page can always scroll vertically
       even if a parent layout wrapper constrains height/overflow. */
    html, body {
        height: auto !important;
        min-height: 100%;
        overflow-y: auto !important;
    }
    .content, .main-content, .app-content, .content-wrapper, main {
        overflow-y: auto !important;
        height: auto !important;
        max-height: none !important;
    }

    #pg-reports {
        --color-primary: var(--brand-dark);
        --color-subtitle: #5f6f66;
        --border-color: var(--border);
        --bg-light: var(--bg-page);
        --theme-accent: var(--brand-dark);
        --theme-accent-soft: var(--brand-accent-soft);
        --theme-accent-strong: var(--brand-dark);
        --theme-accent-bright: var(--brand-green);
        --theme-accent-deep: var(--brand-dark);

        /* Status Colors */
        --status-pending-bg: #fff7e6;
        --status-pending-text: #b7791f;
        --status-approved-bg: #e8f6eb;
        --status-approved-text: #2f6b3d;
        --status-rejected-bg: #fdeceb;
        --status-rejected-text: #b23a3a;

        font-family: 'DM Sans', sans-serif;
        color: var(--color-primary);
        padding: 1.5rem;
        background-color: var(--bg-light);
        width: 100%;
        min-height: 100%;
        overflow-y: visible;
    }

    #pg-reports *, #pg-reports *::before, #pg-reports *::after {
        box-sizing: border-box;
    }

    /* Header Section */
    .reports-header {
        margin-bottom: 1.5rem;
    }
    .reports-header h1 {
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

    /* 5-Column Summary Grid */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .summary-card {
        background: linear-gradient(180deg, #ffffff 0%, #f5faf3 100%);
        border: 1px solid rgba(28, 107, 67, 0.14);
        border-radius: 22px;
        padding: 1.2rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 24px 50px rgba(15, 32, 21, 0.08);
        min-height: 112px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 28px 65px rgba(15, 32, 21, 0.12);
    }
    .summary-info .label {
        font-size: 0.78rem;
        color: var(--color-subtitle);
        font-weight: 600;
        margin-bottom: 0.45rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .summary-info .value {
        font-family: 'Syne', sans-serif;
        font-size: 2rem;
        font-weight: 600;
        color: var(--theme-accent-strong);
        line-height: 1.05;
        margin-bottom: 0.35rem;
    }
    .summary-info .subtext {
        font-size: 0.78rem;
        color: #64748b;
    }
    .summary-icon {
        display: flex;
        width: 45px;
        height: 45px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: #045a33 !important;
        background-color: rgba(4, 90, 51, 0.08) !important;
        border-radius: 50%;
        border: 1px solid rgba(4, 90, 51, 0.14);
    }
    .summary-icon i { color: #045a33 !important; }
    .summary-card.total .summary-icon { background-color: rgba(4, 90, 51, 0.08) !important; color: #045a33 !important; }
    .summary-card.pending .summary-icon { background-color: rgba(4, 90, 51, 0.08) !important; color: #045a33 !important; }
    .summary-card.approved .summary-icon { background-color: rgba(4, 90, 51, 0.08) !important; color: #045a33 !important; }
    .summary-card.rejected .summary-icon { background-color: rgba(4, 90, 51, 0.08) !important; color: #045a33 !important; }
    .summary-card.total { border-left: 4px solid var(--theme-accent); }
    .summary-card.pending { border-left: 4px solid var(--theme-accent-bright); }
    .summary-card.approved { border-left: 4px solid var(--status-approved-text); }
    .summary-card.rejected { border-left: 4px solid var(--theme-accent-deep); }

    /* Filters Toolbar */
    .filters-card {
        background: linear-gradient(135deg, #ffffff 0%, #f9fdf9 100%);
        border: 1px solid rgba(223, 231, 224, 0.95);
        border-radius: 16px;
        padding: 1rem 1.1rem;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }
    .filters-card::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        background: linear-gradient(90deg, var(--theme-accent-bright), var(--theme-accent));
    }
    /* Milestone-style toolbar (copied from timeline) */
    .top-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: 0.9rem;
        padding: 0.9rem 1rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fcf8 100%);
        border: 1px solid rgba(226,232,240,0.9);
        box-shadow: 0 12px 35px rgba(15, 23, 42, 0.06);
        margin: 0.25rem 0;
    }
    .toolbar-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-width: 160px;
    }
    .toolbar-group.search-group {
        min-width: 260px;
        max-width: 520px;
        flex: 0 1 520px;
    }
    .toolbar-input, .toolbar-select {
        width: 100%;
        border: 1px solid rgba(226,232,240,0.9);
        border-radius: 12px;
        padding: 0.6rem 0.9rem;
        background: #fff;
        color: var(--color-primary);
        font-size: 0.95rem;
        outline: none;
    }
    .toolbar-input:focus, .toolbar-select:focus { border-color: var(--theme-accent-bright); box-shadow: 0 0 0 4px rgba(28,107,67,0.06); }
    .toolbar-actions { display:flex; align-items:center; gap:0.75rem; margin-left:auto; }
    .btn-ghost, .btn-primary {
        border: 1px solid rgba(226,232,240,0.9);
        border-radius: 12px;
        padding: 0.6rem 0.9rem;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-ghost { background: #fff; color: var(--color-primary); }
    .btn-primary { background: var(--theme-accent); color: #fff; border-color: var(--theme-accent); }
    .btn-primary i { margin-right: 6px; }
    .filters-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
        margin: 0 auto;
        flex-wrap: nowrap;
        width: 100%;
        padding: 0.25rem 0;
    }
    /* compact filter groups */
    .filter-group { flex: 0 0 auto; }
    .filter-group label { display: none; }
    .filter-control {
        width: 100%;
        max-width: 100%;
        height: 36px;
        padding: 0.35rem 2rem 0.35rem 0.65rem;
        background-color: #f6faf7;
        border: 1px solid rgba(223,231,224,0.9);
        border-radius: 10px;
        font-size: 0.86rem;
        font-weight: 600;
        color: var(--color-primary);
        cursor: pointer;
        transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.12s ease, transform 0.08s ease;
        -webkit-appearance: none; appearance: none;
    }
    .filter-control:focus { box-shadow: 0 8px 20px rgba(15,32,21,0.06); transform: translateY(-1px); }
    .select-wrap { position: relative; display: inline-block; }
    .select-wrap::after {
        content: '\25BE';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: var(--theme-accent-strong);
        font-size: 0.86rem;
        opacity: 0.95;
    }
    .search-wrapper { position: relative; display:flex; align-items:center; }
    .search-wrapper i { position: absolute; left: 12px; color: var(--theme-accent); opacity: 0.75; font-size:1.05rem; }
    .search-input {
        width: 100%;
        height: 44px;
        padding: 0.45rem 2.8rem 0.45rem 2.6rem;
        border: 1px solid rgba(223,231,224,0.9);
        border-radius: 12px;
        font-size: 1rem;
        color: var(--color-primary);
        box-shadow: 0 10px 30px rgba(15,32,21,0.04);
    }
    .search-input:focus { outline: none; border-color: var(--theme-accent-bright); box-shadow: 0 12px 32px rgba(15,32,21,0.07); }
    .search-clear { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(15,32,21,0.04); border: none; color: #475569; font-size: 1.05rem; cursor: pointer; display: none; padding: 6px 8px; border-radius: 8px; }
    .search-clear.show { display: inline-flex; }
    .filters-actions { display:flex; gap:0.6rem; align-items:center; flex:0 0 40%; justify-content:flex-end; }
    .filters-actions .filter-group { display:flex; align-items:center; gap:0.5rem; }
    .filter-group { min-width: 120px; }
    .btn-export {
        height: 40px;
        padding: 0 1rem;
        background: linear-gradient(135deg, var(--theme-accent-bright), var(--theme-accent));
        color: #ffffff !important;
        border: none;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 700;
        display: inline-flex; align-items:center; gap:0.6rem; cursor:pointer; white-space:nowrap;
        box-shadow: 0 8px 24px rgba(16,124,71,0.18);
        transition: background 0.15s ease, transform 0.12s ease, box-shadow 0.12s ease; align-self:center;
    }
    .btn-export i { font-size: 1.05rem; }
    .btn-export:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(16,124,71,0.22); }

    /* Workspace Split Layout Layout */
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
    /* Left column groups the filter toolbar + table so they always share
       the exact same width, and both start at the same top edge as the
       sidebar on the right (no stray whitespace above Report Details). */
    .workspace-left-col {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        min-width: 0;
        width: 100%;
        margin: 0 auto;
    }

    /* Left Side: Table Area */
    .table-container-card {
        background: #ffffff;
        border: 1px solid rgba(28, 107, 67, 0.12);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 16px 40px rgba(15, 32, 21, 0.06);
        width: 100%;
        min-width: 0;
    }
    .card-table-header {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid rgba(28, 107, 67, 0.08);
        background: linear-gradient(135deg, #f4fcf6 0%, #ffffff 100%);
    }
    .card-table-title {
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
        letter-spacing: 0.01em;
        color: #134e2b;
    }
    .reports-table {
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
    .reports-table th,
    .reports-table td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        line-height: 1.35;
        word-break: break-word;
        white-space: normal;
    }
    .reports-table th {
        background: #f8fafc;
        font-weight: 700;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.76rem;
    }
    .reports-table td {
        border-bottom: 1px solid #eef2f7;
        color: #334155;
        background: #ffffff;
        font-size: 0.92rem;
    }
    .reports-table th:first-child,
    .reports-table td:first-child {
        width: 110px;
        min-width: 110px;
        white-space: nowrap;
        padding-left: 1.1rem;
        padding-right: 1.1rem;
    }
    .reports-table th:nth-child(2),
    .reports-table td:nth-child(2) {
        width: 24%;
        min-width: 180px;
    }
    .reports-table th:nth-child(3),
    .reports-table td:nth-child(3),
    .reports-table th:nth-child(4),
    .reports-table td:nth-child(4) {
        width: 14%;
        min-width: 120px;
    }
    .reports-table th:nth-child(5),
    .reports-table td:nth-child(5) {
        width: 13%;
        min-width: 120px;
        padding-left: 0.85rem;
        padding-right: 0.85rem;
    }
    .reports-table th:nth-child(6),
    .reports-table td:nth-child(6),
    .reports-table th:nth-child(7),
    .reports-table td:nth-child(7) {
        width: 10%;
        min-width: 100px;
        white-space: nowrap;
        padding-left: 0.8rem;
        padding-right: 0.8rem;
    }
    .reports-table th:nth-child(8),
    .reports-table td:nth-child(8) {
        width: 96px;
        text-align: center;
        white-space: nowrap;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .reports-table tbody tr {
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .reports-table tbody tr:hover {
        background: #f8fcf9;
        transform: translateY(-1px);
        box-shadow: inset 0 0 0 1px rgba(28, 107, 67, 0.06);
    }
    .reports-table tbody tr.active-row {
        background-color: #ecf8ee;
    }

    /* Table Badges & Cells */
    .cell-bold { font-weight: 600; color: #0f172a; }
    .cell-muted { color: #64748b; font-size: 0.775rem; }
    
    .user-cell {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .user-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 96px;
        padding: 0.38rem 0.95rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        text-transform: capitalize;
        border: 1px solid transparent;
    }
    .status-pill.pending { background: #fff7e4; color: #9c6a1a; border-color: #f3dfb7; }
    .status-pill.approved { background: #e6f9ea; color: #196d34; border-color: #cce7d4; }
    .status-pill.rejected { background: #ffe6e7; color: #a82f32; border-color: #f3c6c8; }

    /* Inline action row icons - green theme */
    .action-icons-group {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        justify-content: center;
        width: 100%;
        min-width: 0;
    }
    .btn-icon-action {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid rgba(28, 107, 67, 0.12);
        background: #f5fbf3;
        color: #1e4331;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        font-size: 1.05rem;
        transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 8px 20px rgba(15, 32, 21, 0.06);
        flex-shrink: 0;
    }
    .btn-icon-action:hover {
        background: #e2f4de;
        border-color: #9ed7a7;
        color: #0f2f18;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(15, 32, 21, 0.08);
    }
    .btn-icon-action:focus-visible {
        outline: 2px solid rgba(28, 107, 67, 0.25);
        outline-offset: 2px;
    }
    .btn-icon-action i {
        font-size: 1.05rem;
    }
    .btn-icon-action.success-hook {
        background: #e7f7eb;
        border-color: #c7e7d0;
        color: #1c6b43;
    }
    .btn-icon-action.success-hook:hover {
        color: #ffffff;
        border-color: #1c6b43;
        background: #1c6b43;
    }
    .btn-icon-action.danger-hook {
        background: #fff1f2;
        border-color: #f5c2c7;
        color: #9a2a31;
    }
    .btn-icon-action.danger-hook:hover {
        color: #ffffff;
        border-color: #c92a2a;
        background: #c92a2a;
    }
    .btn-icon-action[href] {
        background: #eef7fb;
        border-color: #b9dcee;
        color: #196b8c;
    }
    .btn-icon-action[href]:hover {
        background: #1d6fa5;
        border-color: #1d6fa5;
        color: #ffffff;
    }

    /* Pagination Strip */
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
    }
    .pagination-button:not(.active):hover:not(:disabled) {
        background: #f3f4f6;
        border-color: #cbd5e1;
    }
    .pagination-button.active {
        background: #166534;
        border-color: #166534;
        color: #ffffff;
    }
    .pagination-button.active:hover:not(:disabled) {
        background: #134e4a;
        border-color: #134e4a;
    }
    .pagination-button:disabled {
        cursor: not-allowed;
        opacity: 0.55;
        background: #f9fafb;
        border-color: #e5e7eb;
    }
    .pagination-summary {
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Right Side: Sidebar Details Panel */
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
    .report-details-panel {
        min-height: 100%;
        overflow-y: auto;
        transition: opacity 0.2s ease, transform 0.25s ease;
    }
    .report-details-panel.is-switching {
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
        margin: 0 0 0.35rem 0;
        font-size: 0.72rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 700;
        color: #16a34a;
    }
    .sidebar-title {
        font-size: 1.18rem;
        font-weight: 800;
        margin: 0;
        color: #0f172a;
        line-height: 1.25;
        max-width: 18rem;
    }
    .btn-close-sidebar {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 1.1rem;
    }

    .sidebar-meta-block {
        margin-bottom: 1rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1rem 1rem 0.9rem;
        box-shadow: 0 15px 35px rgba(15, 23, 42, 0.05);
    }
    .report-id-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .report-id-text {
        font-size: 1rem;
        font-weight: 700;
    }

    /* Data Properties list */
    .prop-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.85rem;
        margin-bottom: 0.75rem;
    }
    .prop-row {
        display: grid;
        grid-template-columns: 112px 1fr;
        gap: 0.15rem;
        font-size: 0.86rem;
        line-height: 1.5;
        align-items: center;
    }
    .prop-label {
        color: #64748b;
        font-weight: 600;
    }
    .prop-val {
        color: #0f172a;
        font-weight: 500;
    }

    /* Summary Segment Dividers */
    .sidebar-section-divider {
        border-top: 1px solid #f1f5f9;
        margin: 0.9rem 0;
    }
    .section-subtitle {
        font-size: 0.825rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.5rem 0;
    }
    .section-p {
        font-size: 0.825rem;
        color: #475569;
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
    }

    /* Progress block bar */
    .progress-metric-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: 0.35rem;
    }
    .progress-bar-track {
        flex: 1;
        height: 10px;
        background: #f8fafc;
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.08);
    }
    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.28);
        transition: width 0.3s ease;
    }
    .progress-pct-label {
        font-size: 0.825rem;
        font-weight: 700;
    }

    /* Image Attachment Grid */
    .attachment-thumbnail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.4rem;
        margin-top: 0.35rem;
    }
    .img-thumb-container {
        aspect-ratio: 1;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        position: relative;
    }
    .img-thumb-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: zoom-in;
        transition: transform 0.2s ease;
    }
    .img-thumb-container img:hover {
        transform: scale(1.03);
    }
    .image-preview-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.9);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        z-index: 9999;
    }
    .image-preview-overlay.open {
        display: flex;
    }
    .image-preview-card {
        max-width: 95%;
        max-height: 95%;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 40px 120px rgba(0, 0, 0, 0.4);
        background: #000000;
        position: relative;
    }
    .image-preview-card img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: contain;
        max-height: 85vh;
        background: #000;
    }
    .image-preview-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.24);
        background: rgba(15, 23, 42, 0.7);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .image-preview-close:hover {
        transform: scale(1.05);
        background: rgba(15, 23, 42, 0.9);
    }
    .more-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Sticky Bottom Form Decision Row */
    .sidebar-actions-footer {
        display: flex;
        gap: 0.8rem;
        margin-top: 1rem;
        justify-content: center;
        align-items: center;
    }
    .sidebar-actions-footer button,
    .sidebar-actions-footer .btn-export-action {
        flex: 0 1 auto;
        min-width: 120px;
        max-width: 150px;
        padding: 0.65rem 1rem;
        min-height: 42px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 14px;
        font-size: 0.88rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        color: #334155;
        background: #f8fafc;
        text-decoration: none;
        transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
    }
    .sidebar-actions-footer button:hover,
    .sidebar-actions-footer .btn-export-action:hover {
        transform: translateY(-1px);
        background: #eff2f7;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
    }
    .sidebar-actions-footer button:focus-visible {
        outline: none;
        box-shadow: 0 0 0 4px rgba(22, 101, 52, 0.18);
    }
    .sidebar-actions-footer button.btn-approve-action {
        background-color: #166534;
        color: #ffffff !important;
        border-color: #166534 !important;
    }
    .sidebar-actions-footer button.btn-approve-action:hover {
        background-color: #134e4a;
        border-color: #134e4a;
    }
    .sidebar-actions-footer button.btn-reject-action {
        background-color: #fee2e2;
        color: #7c1d1d !important;
        border-color: #f6b8b8 !important;
    }
    .sidebar-actions-footer button.btn-reject-action:hover {
        background-color: #fcd4d4;
        border-color: #eeaaaa;
    }

    .sidebar-actions-footer.sidebar-status-note {
        flex: 1 1 100%;
        justify-content: center;
        gap: 0.5rem;
        color: #334155;
        background: #f8fafc;
        border: 1px solid rgba(148, 163, 184, 0.25);
        padding: 0.85rem 1rem;
        border-radius: 14px;
        font-weight: 600;
        min-width: 0;
    }
    .sidebar-actions-footer.sidebar-status-note i {
        color: #166534;
        font-size: 1rem;
    }

    /* Information panel falls back cleanly when no item is selected */
    .sidebar-fallback-state {
        padding: 3rem 1.5rem;
        text-align: center;
        color: #94a3b8;
    }
    .sidebar-fallback-state i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    /* Report Details Modal */
    #reportDetailsModal .modal-content {
        border-radius: 20px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        box-shadow: 0 32px 100px rgba(15, 32, 21, 0.12);
        overflow: hidden;
    }
    #reportDetailsModal .modal-header {
        background: linear-gradient(135deg, #f8fdf9 0%, #ffffff 100%);
        border-bottom: 2px solid var(--cms-green-dark);
        padding: 1.5rem;
    }
    #reportDetailsModal .modal-body {
        background: #ffffff;
        padding: 1.75rem;
    }
    #reportDetailsModal .report-details-panel {
        max-height: none;
        overflow-y: visible;
    }
    #reportDetailsModal .modal-dialog {
        max-width: 1100px;
    }
    #reportDetailsModal .modal-xl {
        max-width: 1100px;
    }

    /* Enhanced Modal Cards */
    .modal-detail-card {
        background: #ffffff;
        border: 1px solid #e8f0eb;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 8px 30px rgba(15, 32, 21, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .modal-detail-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 36px rgba(15, 32, 21, 0.08);
    }

    .modal-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }
    .modal-info-item {
        background: linear-gradient(135deg, #f8faf9 0%, #f1f7f3 100%);
        border: 1px solid #e8f0eb;
        border-radius: 12px;
        padding: 0.9rem 1rem;
        transition: all 0.2s ease;
    }
    .modal-info-item:hover {
        border-color: #c8dccf;
        background: linear-gradient(135deg, #f1f7f3 0%, #e8f0eb 100%);
    }
    .modal-info-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #6b7c72;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.35rem;
    }
    .modal-info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1a2e23;
        word-break: break-word;
    }

    .modal-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--cms-green-dark);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .modal-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, #e8f0eb, transparent);
    }

    .modal-accomplishment-box {
        background: linear-gradient(135deg, #f8fdf9 0%, #f1f7f3 100%);
        border: 1px solid #d4e5d8;
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .modal-accomplishment-box::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #4DA078, #82DB72);
        border-radius: 14px 0 0 14px;
    }

    .modal-sidebar-card {
        background: #ffffff;
        border: 1px solid #e8f0eb;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 6px 24px rgba(15, 32, 21, 0.05);
    }

    .modal-progress-card {
        background: linear-gradient(135deg, #f8faf9 0%, #f1f7f3 100%);
        border: 1px solid #e8f0eb;
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
    }
    .modal-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.6rem;
    }
    .modal-progress-title {
        font-weight: 700;
        color: #1a2e23;
        font-size: 0.9rem;
    }
    .modal-progress-pct {
        font-weight: 800;
        color: var(--cms-green-dark);
        font-size: 1.1rem;
    }

    .modal-image-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.6rem;
    }
    .modal-image-thumb {
        aspect-ratio: 1;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #e8f0eb;
        background: #f8faf9;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .modal-image-thumb:hover {
        border-color: #4DA078;
        transform: scale(1.03);
        box-shadow: 0 8px 20px rgba(15, 32, 21, 0.1);
    }
    .modal-image-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .modal-more-badge {
        aspect-ratio: 1;
        border-radius: 10px;
        background: #f1f5f9;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #6b7280;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .modal-more-badge:hover {
        background: #e8f0eb;
        border-color: #4DA078;
        color: var(--cms-green-dark);
    }

    .modal-timeline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
        padding: 0 0.25rem;
        margin-top: 0.5rem;
    }
    .modal-timeline::before {
        content: '';
        position: absolute;
        top: 16px;
        left: 12%;
        right: 12%;
        height: 3px;
        background: #d4e5d8;
        border-radius: 999px;
        z-index: 1;
    }
    .modal-timeline-step {
        text-align: center;
        position: relative;
        z-index: 2;
        flex: 1;
        min-width: 0;
    }
    .modal-timeline-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #d4e5d8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 0.75rem;
        color: #94a3b8;
        transition: all 0.3s ease;
    }
    .modal-timeline-step.active .modal-timeline-icon {
        border-color: #4DA078;
        background: linear-gradient(135deg, #4DA078, #82DB72);
        color: #fff;
        box-shadow: 0 4px 12px rgba(77, 160, 120, 0.3);
    }
    .modal-timeline-step.current .modal-timeline-icon {
        border-color: #f59e0b;
        background: #fff;
        color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
    }
    .modal-timeline-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .modal-timeline-step.active .modal-timeline-label {
        color: var(--cms-green-dark);
    }

    .modal-action-row {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        padding-top: 1.25rem;
        margin-top: 1.25rem;
        border-top: 1px solid #e8f0eb;
    }
    .btn-modal {
        padding: 0.65rem 1.5rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.88rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-modal-approve {
        background: linear-gradient(135deg, #4DA078, #2a8a5e);
        color: #ffffff;
        box-shadow: 0 6px 20px rgba(42, 138, 94, 0.25);
    }
    .btn-modal-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(42, 138, 94, 0.35);
        background: linear-gradient(135deg, #3d8f68, #227a4f);
    }
    .btn-modal-reject {
        background: #ffffff;
        color: #b23a3a;
        border: 1px solid #f5c2c7;
        box-shadow: 0 4px 12px rgba(15, 32, 21, 0.05);
    }
    .btn-modal-reject:hover {
        background: #fff5f5;
        border-color: #ee4d4d;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(178, 58, 58, 0.12);
    }
    .btn-modal-download {
        background: #ffffff;
        color: var(--cms-green-dark);
        border: 1px solid #d4e5d8;
        box-shadow: 0 4px 12px rgba(15, 32, 21, 0.05);
    }
    .btn-modal-download:hover {
        background: #f8fdf9;
        border-color: #4DA078;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(77, 160, 120, 0.12);
    }

    .modal-status-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.85rem 1rem;
        background: #f8faf9;
        border: 1px solid #e8f0eb;
        border-radius: 12px;
        color: #475569;
        font-weight: 600;
        font-size: 0.85rem;
        margin-top: 1rem;
    }
    .modal-status-note i {
        color: var(--cms-green-dark);
        font-size: 1rem;
    }

    @media (max-width: 768px) {
        .modal-info-grid {
            grid-template-columns: 1fr;
        }
        .modal-image-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .modal-timeline {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .modal-timeline::before {
            display: none;
        }
        .modal-timeline-step {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-align: left;
        }
        .modal-timeline-icon {
            margin: 0;
            flex-shrink: 0;
        }
        .modal-action-row {
            flex-direction: column;
        }
        .btn-modal {
            width: 100%;
            justify-content: center;
        }
    }

    /* Responsive scaling fixes */
    @media (max-width: 1400px) {
        .workspace-layout {
            flex-direction: column;
            align-items: stretch;
        }
        .workspace-layout.is-panel-open {
            gap: 1.25rem;
        }
        .details-sidebar-card {
            position: static;
            max-width: 100%;
            width: 100%;
            flex: 0 0 auto;
            display: none;
        }
        .details-sidebar-card.is-open {
            display: block;
            width: 100%;
            flex: 0 0 auto;
            padding: 1.25rem;
        }
    }
    @media (max-width: 1100px) {
        .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .top-toolbar { flex-direction: column; align-items: stretch; }
        .toolbar-group, .toolbar-group.search-group { width: 100%; min-width: 0; max-width: none; flex: 1 1 100%; }
        .toolbar-actions { margin-left: 0; width: 100%; justify-content: flex-start; }
    }
    @media (max-width: 768px) {
        #pg-reports { padding: 1rem; }
        .reports-header h1 { font-size: 1.4rem; }
        .reports-header p { font-size: 0.82rem; }
        .summary-grid { grid-template-columns: 1fr; }
        .summary-card { min-height: 96px; padding: 1rem; }
        .summary-info .value { font-size: 1.5rem; }
        .top-toolbar { padding: 0.9rem; gap: 0.75rem; }
        .table-container-card { border-radius: 14px; }
        .card-table-header { padding: 0.9rem 1rem; }
        .table-responsive { overflow-x: hidden; }
        .reports-table,
        .reports-table thead,
        .reports-table tbody,
        .reports-table tr,
        .reports-table th,
        .reports-table td {
            display: block;
        }
        .reports-table thead { display: none; }
        .reports-table tbody tr {
            margin-bottom: 0.8rem;
            border: 1px solid rgba(28, 107, 67, 0.12);
            border-radius: 14px;
            padding: 0.85rem;
            background: #ffffff;
            box-shadow: 0 10px 24px rgba(15, 32, 21, 0.05);
        }
        .reports-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.35rem 0;
            border: 0;
        }
        .reports-table td::before {
            content: attr(data-label);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            flex: 0 0 42%;
        }
        .reports-table td > * {
            flex: 1;
            min-width: 0;
        }
        .table-pagination-strip { flex-direction: column; align-items: flex-start; }
        .pagination-bar { width: 100%; overflow-x: auto; }
        .prop-row { grid-template-columns: 1fr; }
        .attachment-thumbnail-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sidebar-actions-footer { flex-direction: column; }
        .sidebar-actions-footer button,
        .sidebar-actions-footer .btn-export-action { width: 100%; max-width: none; }
    }
    @media (max-width: 576px) {
        #pg-reports { padding: 0.8rem; }
        .summary-card { padding: 0.95rem; }
        .attachment-thumbnail-grid { grid-template-columns: 1fr; }
        .reports-table tbody tr { padding: 0.75rem; }
    }


    /* ======================================================================
       SENIOR MOBILE REPORT CARD OVERRIDE
       This intentionally DOES NOT use #pg-reports because the current Blade
       content is not wrapped with that id. It overrides the older compact
       mobile table conversion and makes each report row read like a clean
       detail card, similar to the inventory expense cards.
       ====================================================================== */
    @media (max-width: 900px) {
        .workspace-layout,
        .workspace-left-col,
        .table-container-card,
        .table-responsive {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            overflow: visible !important;
        }

        .table-container-card {
            border-radius: 18px !important;
            background: #ffffff !important;
            border: 1px solid #e3ece5 !important;
            box-shadow: 0 12px 28px rgba(15, 32, 21, 0.06) !important;
        }

        .card-table-header {
            padding: 16px 18px 14px !important;
            background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%) !important;
            border-bottom: 1px solid #e5eee7 !important;
        }

        .card-table-title {
            color: #14532d !important;
            font-family: 'Syne', 'Plus Jakarta Sans', sans-serif !important;
            font-size: 15px !important;
            line-height: 1.2 !important;
        }

        .reports-table,
        .reports-table thead,
        .reports-table tbody,
        .reports-table tr,
        .reports-table th,
        .reports-table td {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .reports-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            table-layout: auto !important;
            background: transparent !important;
            margin: 0 !important;
        }

        .reports-table thead {
            display: none !important;
        }

        .reports-table tbody {
            display: grid !important;
            gap: 14px !important;
            padding: 12px !important;
            background: #fbfdfb !important;
        }

        .reports-table tbody tr {
            position: relative !important;
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 16px 16px 15px !important;
            background: #ffffff !important;
            border: 1px solid #e0eae3 !important;
            border-radius: 18px !important;
            box-shadow: 0 10px 22px rgba(15, 32, 21, 0.045) !important;
            overflow: hidden !important;
        }

        .reports-table tbody tr:hover {
            transform: none !important;
            box-shadow: 0 10px 22px rgba(15, 32, 21, 0.045) !important;
        }

        .reports-table tbody td {
            display: grid !important;
            grid-template-columns: 88px minmax(0, 1fr) !important;
            column-gap: 14px !important;
            align-items: start !important;
            justify-content: initial !important;
            width: 100% !important;
            padding: 7px 0 !important;
            border: 0 !important;
            background: transparent !important;
            color: #243248 !important;
            font-size: 12.5px !important;
            line-height: 1.45 !important;
            text-align: left !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
            white-space: normal !important;
        }

        .reports-table tbody td::before {
            content: attr(data-label) !important;
            display: block !important;
            width: auto !important;
            min-width: 0 !important;
            color: #64748b !important;
            font-size: 9.5px !important;
            font-weight: 800 !important;
            letter-spacing: 0.075em !important;
            line-height: 1.25 !important;
            text-transform: uppercase !important;
            white-space: normal !important;
        }

        .reports-table tbody td > * {
            min-width: 0 !important;
            max-width: 100% !important;
            flex: initial !important;
            white-space: normal !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        /* ID becomes a small chip, not a full row. */
        .reports-table tbody td[data-label="ID"] {
            position: absolute !important;
            top: 14px !important;
            right: 14px !important;
            display: inline-flex !important;
            width: auto !important;
            max-width: none !important;
            min-width: 0 !important;
            padding: 4px 9px !important;
            background: #f0f7f1 !important;
            border: 1px solid #dbeade !important;
            border-radius: 999px !important;
            color: #365233 !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            line-height: 1 !important;
        }

        .reports-table tbody td[data-label="ID"]::before {
            display: none !important;
        }

        /* Title uses the full card width so it does not feel squeezed left. */
        .reports-table tbody td[data-label="Report Title"] {
            display: block !important;
            padding: 0 48px 13px 0 !important;
            margin: 0 0 8px !important;
            border-bottom: 1px solid #edf3ef !important;
        }

        .reports-table tbody td[data-label="Report Title"]::before {
            content: "Report" !important;
            display: block !important;
            margin-bottom: 6px !important;
            color: #64748b !important;
            font-size: 9.5px !important;
            font-weight: 800 !important;
            letter-spacing: 0.075em !important;
            text-transform: uppercase !important;
        }

        .reports-table tbody td[data-label="Report Title"] .cell-bold,
        .reports-table tbody td[data-label="Report Title"] span,
        .reports-table tbody td[data-label="Report Title"] strong {
            display: block !important;
            color: #0f172a !important;
            font-size: 14px !important;
            font-weight: 800 !important;
            line-height: 1.35 !important;
            letter-spacing: -0.01em !important;
        }

        .reports-table tbody td[data-label="Project"],
        .reports-table tbody td[data-label="Phase"],
        .reports-table tbody td[data-label="Supervisor"],
        .reports-table tbody td[data-label="Date"] {
            min-height: 34px !important;
        }

        .reports-table tbody td[data-label="Project"]::before,
        .reports-table tbody td[data-label="Phase"]::before,
        .reports-table tbody td[data-label="Supervisor"]::before,
        .reports-table tbody td[data-label="Date"]::before {
            padding-top: 2px !important;
        }

        .reports-table tbody td[data-label="Project"],
        .reports-table tbody td[data-label="Phase"],
        .reports-table tbody td[data-label="Supervisor"] {
            color: #334155 !important;
            font-weight: 600 !important;
        }

        .reports-table tbody td[data-label="Date"] .cell-bold,
        .reports-table tbody td[data-label="Date"] span {
            color: #111827 !important;
            font-weight: 800 !important;
        }

        .reports-table .user-cell {
            display: block !important;
            width: 100% !important;
        }

        .reports-table tbody td[data-label="Status"] {
            display: grid !important;
            grid-template-columns: 88px minmax(0, 1fr) !important;
            column-gap: 14px !important;
            align-items: center !important;
            padding-top: 13px !important;
            margin-top: 7px !important;
            border-top: 1px solid #edf3ef !important;
        }

        .reports-table .status-pill {
            display: inline-flex !important;
            width: fit-content !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 7px 10px !important;
            border-radius: 999px !important;
            white-space: normal !important;
            text-align: center !important;
            line-height: 1.15 !important;
            font-size: 11px !important;
            font-weight: 800 !important;
        }

        .reports-table tbody td[data-label="Actions"] {
            display: flex !important;
            justify-content: flex-end !important;
            align-items: center !important;
            padding: 10px 0 0 !important;
        }

        .reports-table tbody td[data-label="Actions"]::before {
            display: none !important;
        }

        .reports-table .action-icons-group {
            display: flex !important;
            justify-content: flex-end !important;
            width: 100% !important;
        }

        .reports-table .btn-icon-action {
            width: 40px !important;
            height: 40px !important;
            border-radius: 12px !important;
            background: #f4fbf4 !important;
            border-color: #dceee0 !important;
            color: #234b2f !important;
            box-shadow: none !important;
        }
    }

    @media (max-width: 390px) {
        .reports-table tbody {
            padding: 10px !important;
            gap: 12px !important;
        }

        .reports-table tbody tr {
            padding: 15px 14px !important;
        }

        .reports-table tbody td,
        .reports-table tbody td[data-label="Status"] {
            grid-template-columns: 78px minmax(0, 1fr) !important;
            column-gap: 12px !important;
        }
    }


    /* ======================================================================
       FINAL MOBILE POLISH FOR CAPACITOR / PHONE WEBVIEW
       Purpose: reduce tall white space, make summary cards 2x2, make filters
       compact, and keep report rows readable like clean admin cards.
       ====================================================================== */
    @media (max-width: 920px) {
        html,
        body {
            overflow-x: hidden !important;
        }

        #pg-reports {
            padding: 0.95rem 0.8rem 1.35rem !important;
            background:
                radial-gradient(circle at top right, rgba(22, 101, 52, 0.055), transparent 34%),
                var(--bg-light) !important;
            overflow-x: hidden !important;
        }

        #pg-reports .reports-header {
            margin-bottom: 0.9rem !important;
            padding: 0 0.1rem !important;
        }

        #pg-reports .reports-header h1 {
            font-size: 1.38rem !important;
            line-height: 1.08 !important;
            margin-bottom: 0.35rem !important;
            letter-spacing: -0.03em !important;
        }

        #pg-reports .reports-header p {
            max-width: 95% !important;
            font-size: 0.78rem !important;
            line-height: 1.45 !important;
        }

        #pg-reports .summary-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.72rem !important;
            margin-bottom: 1rem !important;
        }

        #pg-reports .summary-card {
            position: relative !important;
            min-height: 96px !important;
            padding: 0.82rem 0.8rem !important;
            border-radius: 18px !important;
            align-items: flex-start !important;
            box-shadow: 0 12px 28px rgba(15, 32, 21, 0.065) !important;
            border-left-width: 0 !important;
            overflow: hidden !important;
        }

        #pg-reports .summary-card::before {
            content: '' !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            bottom: 0 !important;
            width: 4px !important;
            background: #1f5c36 !important;
            opacity: 0.95 !important;
        }

        #pg-reports .summary-card.pending::before {
            background: #d99b24 !important;
        }

        #pg-reports .summary-card.approved::before {
            background: #198754 !important;
        }

        #pg-reports .summary-card.rejected::before {
            background: #d64545 !important;
        }

        #pg-reports .summary-info {
            min-width: 0 !important;
            width: 100% !important;
            padding-right: 2.45rem !important;
        }

        #pg-reports .summary-info .label {
            max-width: 100% !important;
            margin-bottom: 0.38rem !important;
            color: #506071 !important;
            font-size: 0.62rem !important;
            font-weight: 800 !important;
            line-height: 1.18 !important;
            letter-spacing: 0.055em !important;
            white-space: normal !important;
        }

        #pg-reports .summary-info .value {
            margin-bottom: 0.24rem !important;
            color: #0f172a !important;
            font-size: 1.42rem !important;
            line-height: 1 !important;
            letter-spacing: -0.03em !important;
        }

        #pg-reports .summary-info .subtext {
            color: #748094 !important;
            font-size: 0.66rem !important;
            line-height: 1.28 !important;
        }

        #pg-reports .summary-icon {
            position: absolute !important;
            top: 0.78rem !important;
            right: 0.78rem !important;
            width: 34px !important;
            height: 34px !important;
            font-size: 0.88rem !important;
            border-radius: 12px !important;
            background: rgba(22, 101, 52, 0.075) !important;
        }

        #pg-reports .top-toolbar {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 0.62rem !important;
            margin: 0 0 1rem !important;
            padding: 0.78rem !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            border: 1px solid #e2ebe5 !important;
            box-shadow: 0 12px 28px rgba(15, 32, 21, 0.055) !important;
        }

        #pg-reports .toolbar-group,
        #pg-reports .toolbar-group.search-group {
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            flex: initial !important;
        }

        #pg-reports .toolbar-group.search-group {
            grid-column: 1 / -1 !important;
        }

        #pg-reports .toolbar-input,
        #pg-reports .toolbar-select {
            min-height: 43px !important;
            height: 43px !important;
            padding: 0.6rem 0.78rem !important;
            border-radius: 13px !important;
            border-color: #dfe8e2 !important;
            background: #ffffff !important;
            color: #1e293b !important;
            font-size: 16px !important;
            line-height: 1.15 !important;
            box-shadow: none !important;
        }

        #pg-reports .toolbar-input::placeholder {
            color: #8a96a5 !important;
            font-size: 0.82rem !important;
        }

        #pg-reports .workspace-layout,
        #pg-reports .workspace-left-col {
            gap: 0.9rem !important;
        }

        #pg-reports .table-container-card {
            border-radius: 20px !important;
            box-shadow: 0 14px 30px rgba(15, 32, 21, 0.06) !important;
            overflow: hidden !important;
        }

        #pg-reports .card-table-header {
            padding: 1rem 1rem 0.85rem !important;
        }

        #pg-reports .card-table-title {
            font-size: 0.98rem !important;
        }

        #pg-reports .card-table-header p {
            font-size: 0.74rem !important;
            line-height: 1.25 !important;
        }

        #pg-reports .reports-table tbody {
            padding: 0.82rem !important;
            gap: 0.82rem !important;
            background: #fbfdfb !important;
        }

        #pg-reports .reports-table tbody tr {
            border-radius: 18px !important;
            padding: 1rem !important;
            box-shadow: 0 9px 22px rgba(15, 32, 21, 0.045) !important;
        }

        #pg-reports .reports-table tbody td,
        #pg-reports .reports-table tbody td[data-label="Status"] {
            grid-template-columns: 84px minmax(0, 1fr) !important;
            column-gap: 0.82rem !important;
            padding: 0.42rem 0 !important;
        }

        #pg-reports .reports-table tbody td::before {
            color: #62738a !important;
            font-size: 0.6rem !important;
            letter-spacing: 0.075em !important;
        }

        #pg-reports .reports-table tbody td[data-label="Report Title"] {
            padding-right: 2.85rem !important;
        }

        #pg-reports .reports-table tbody td[data-label="Report Title"] .cell-bold {
            font-size: 0.94rem !important;
            line-height: 1.34 !important;
        }

        #pg-reports .status-pill {
            min-width: 0 !important;
            padding: 0.42rem 0.7rem !important;
            font-size: 0.68rem !important;
        }

        #pg-reports .table-pagination-strip {
            margin-top: 0 !important;
            padding: 0.85rem 1rem 1rem !important;
            background: #ffffff !important;
        }

        #pg-reports .pagination-summary {
            font-size: 0.8rem !important;
        }
    }

    @media (max-width: 380px) {
        #pg-reports {
            padding-left: 0.65rem !important;
            padding-right: 0.65rem !important;
        }

        #pg-reports .summary-grid {
            gap: 0.58rem !important;
        }

        #pg-reports .summary-card {
            padding: 0.75rem 0.68rem !important;
            min-height: 92px !important;
        }

        #pg-reports .summary-info {
            padding-right: 2rem !important;
        }

        #pg-reports .summary-icon {
            width: 30px !important;
            height: 30px !important;
            right: 0.62rem !important;
        }

        #pg-reports .top-toolbar {
            gap: 0.55rem !important;
            padding: 0.68rem !important;
        }

        #pg-reports .toolbar-select,
        #pg-reports .toolbar-input {
            padding-left: 0.65rem !important;
            padding-right: 0.65rem !important;
        }
    }

    /* Image Lightbox */
    .image-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .image-lightbox.is-open {
        display: flex;
    }

    .image-lightbox img {
        max-width: 90%;
        max-height: 85vh;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }

    .image-lightbox-close {
        position: absolute;
        top: 1rem;
        right: 1.5rem;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 1.5rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .image-lightbox-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@section('content')
<div id="pg-reports" class="reports-mobile-admin-page">
    <!-- Title Header -->
    <div class="reports-header">
        <h1>Reports</h1>
        <p>Review, approve, and manage accomplishment reports submitted by supervisors.</p>
    </div>

    <!-- Summary Row Block -->
    <div class="summary-grid">
        <div class="summary-card total">
            <div class="summary-info">
                <div class="label">Total Reports</div>
                <div class="value">{{ $stats['total'] ?? 0 }}</div>
                <div class="subtext">All submitted reports</div>
            </div>
            <div class="summary-icon"><i class="bi bi-file-earmark-text"></i></div>
        </div>
        <div class="summary-card pending">
            <div class="summary-info">
                <div class="label">Pending Review</div>
                <div class="value">{{ $stats['pending'] ?? 0 }}</div>
                <div class="subtext">Awaiting your review</div>
            </div>
            <div class="summary-icon stat-icon-wrap"><i class="bi bi-clock-history"></i></div>
        </div>
        <div class="summary-card approved">
            <div class="summary-info">
                <div class="label">Approved</div>
                <div class="value">{{ $stats['approved'] ?? 0 }}</div>
                <div class="subtext">Reports approved</div>
            </div>
            <div class="summary-icon stat-icon-wrap"><i class="bi bi-check-circle-fill"></i></div>
        </div>
        <div class="summary-card rejected">
            <div class="summary-info">
                <div class="label">Rejected</div>
                <div class="value">{{ $stats['rejected'] ?? 0 }}</div>
                <div class="subtext">Reports rejected</div>
            </div>
            <div class="summary-icon stat-icon-wrap"><i class="bi bi-x-circle-fill"></i></div>
        </div>
    </div>

    <!-- Workspace Grid split layout -->
    <div class="workspace-layout" id="reportsWorkspaceLayout">

        <!-- Left Column: Filter toolbar + Table share identical width -->
        <div class="workspace-left-col">

            <!-- Filter Component Toolbar Box -->
            <div class="top-toolbar">
                <div class="toolbar-group search-group">
                    <input id="reportsSearchInput" type="text" class="toolbar-input" placeholder="Search report title, ID, project, phase, or supervisor..." value="{{ request('search') }}">
                </div>

                <div class="toolbar-group">
                    <select id="projectFilter" class="toolbar-select" name="project_id">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->project_id }}" {{ request('project_id') == $project->project_id ? 'selected' : '' }}>{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="toolbar-group">
                    <select id="phaseFilter" class="toolbar-select" name="phase_id">
                        <option value="">All Phases</option>
                        @foreach($phases as $phase)
                            <option value="{{ $phase->phase_id }}" {{ request('phase_id') == $phase->phase_id ? 'selected' : '' }}>{{ $phase->phase_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="toolbar-group">
                    <select id="supervisorFilter" class="toolbar-select" name="supervisor_id">
                        <option value="">All Supervisors</option>
                        @foreach($supervisors as $supervisor)
                            <option value="{{ $supervisor->user_id }}" {{ request('supervisor_id') == $supervisor->user_id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="toolbar-group">
                    <select id="statusFilter" class="toolbar-select" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
            </div>

        <!-- Left Part Element: Table Core Panel -->
        <div class="table-container-card">
            <div class="card-table-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-table-title" id="reportsListHeading">Reports List ({{ $reports->total() }})</h3>
                    <p class="text-muted mb-0 small">Track submitted reports and their review status</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 reports-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Report Title</th>
                            <th>Project</th>
                            <th>Phase</th>
                            <th>Supervisor</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                        @forelse($reports as $report)
                            <tr data-report-id="{{ $report->report_id }}">
                                <td class="cell-bold" data-label="ID">{{ $report->report_id }}</td>
                                <td data-label="Report Title">
                                    <span class="cell-bold">{{ $report->report_title }}</span>
                                </td>
                                <td data-label="Project">{{ optional($report->project)->project_name ?? 'Unassigned Project' }}</td>
                                <td data-label="Phase">{{ optional($report->phase)->phase_name ?? 'Unassigned Phase' }}</td>
                                <td data-label="Supervisor">
                                    <div class="user-cell">
                                        <span>{{ optional($report->submittedBy)->name ?? 'Unassigned Supervisor' }}</span>
                                    </div>
                                </td>
                                <td data-label="Date"><span class="cell-bold">{{ optional($report->report_date)->format('M d, Y') ?? $report->created_at->format('M d, Y') }}</span></td>
                                <td data-label="Status">
                                    <span class="status-pill {{ $report->status_badge_class }}">{{ $report->status_label }}</span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-icons-group">
                                        <button type="button" class="btn-icon-action js-view-report" data-report-id="{{ $report->report_id }}" title="View Details"><i class="bi bi-eye"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    {{ $reports->count() === 0 ? ($stats['total'] > 0 ? 'No reports match your selected filters.' : 'No accomplishment reports found.') : '' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Toolbar Node Footer -->
            <div class="table-pagination-strip">
                <div id="reportsPaginationInfo" class="pagination-summary">
                    @if($reports->total() > 0)
                        Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} reports
                    @else
                        Showing 0 reports
                    @endif
                </div>
                <div id="reportsPagination" class="pagination-bar">
                    @if($reports->currentPage() > 1)
                        <button type="button" class="pagination-button" data-page="{{ $reports->currentPage() - 1 }}" aria-label="Previous page">‹</button>
                    @endif
                    @foreach(range(1, $reports->lastPage()) as $page)
                        <button type="button" class="pagination-button {{ $page == $reports->currentPage() ? 'active' : '' }}" data-page="{{ $page }}" aria-label="Page {{ $page }}">{{ $page }}</button>
                    @endforeach
                    @if($reports->hasMorePages())
                        <button type="button" class="pagination-button" data-page="{{ $reports->currentPage() + 1 }}" aria-label="Next page">›</button>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.table-container-card -->
        </div>
        <!-- /.workspace-left-col -->
    </div>
    <!-- /.workspace-layout -->

    <!-- Report Details Modal -->
    <div class="modal fade" id="reportDetailsModal" tabindex="-1" aria-labelledby="reportDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f8fdf9 0%, #ffffff 100%); border-bottom: 2px solid var(--cms-green-dark);">
                    <div>
                        <h5 class="modal-title fw-bold" id="reportDetailsModalLabel" style="color: var(--cms-green-dark);">Report Details</h5>
                        <p class="text-muted small mb-0">Review report information and approve progress.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div id="report-details-panel" class="report-details-panel">
                        @if($reports->isNotEmpty())
                            @php $selectedReport = $reports->first(); @endphp
                            @php
                                $reportStatus = $selectedReport->approval_status ?? 'pending';
                                $reportStatusLabel = $selectedReport->status_label ?? 'Pending Review';
                                $reportStatusClass = $selectedReport->status_badge_class ?? 'pending';
                                $reportId = 'RPT-2026-' . str_pad($selectedReport->report_id, 4, '0', STR_PAD_LEFT);
                            @endphp
                            <div class="row gx-4 gy-4">
                                <div class="col-12 col-xl-7">
                                    <div class="modal-detail-card">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-4 p-3 rounded-3" style="background: #ffffff; border: 1px solid #e8f0eb;">
                                            <div>
                                                <div class="modal-section-title mb-1" style="margin-top:0; border:none; padding-bottom:0;">Report ID</div>
                                                <div class="fw-bold text-dark" style="font-size: 1.15rem; color: var(--cms-green-dark);">{{ $reportId }}</div>
                                            </div>
                                            <div class="text-sm-end">
                                                <div class="modal-section-title mb-1" style="margin-top:0; border:none; padding-bottom:0;">Approval Status</div>
                                                <span class="status-pill {{ $reportStatusClass }} p-2 mt-1 d-inline-block">{{ $reportStatusLabel }}</span>
                                            </div>
                                        </div>

                                        <div class="modal-section-title">Project Information</div>
                                        <div class="modal-info-grid mb-4">
                                            <div class="modal-info-item">
                                                <div class="modal-info-label">Project</div>
                                                <div class="modal-info-value">{{ optional($selectedReport->project)->project_name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="modal-info-item">
                                                <div class="modal-info-label">Construction Phase</div>
                                                <div class="modal-info-value">{{ optional($selectedReport->phase)->phase_name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="modal-info-item">
                                                <div class="modal-info-label">Report Date</div>
                                                <div class="modal-info-value">{{ optional($selectedReport->report_date)->format('M d, Y') ?? $selectedReport->created_at->format('M d, Y') }}</div>
                                            </div>
                                            <div class="modal-info-item">
                                                <div class="modal-info-label">Submitted By</div>
                                                <div class="modal-info-value">{{ optional($selectedReport->submittedBy)->name ?? 'Supervisor' }}</div>
                                            </div>
                                        </div>

                                        <div class="modal-section-title">Construction Accomplishment</div>
                                        <div class="modal-accomplishment-box mb-4">
                                            <p class="mb-0 text-dark small" style="white-space: pre-line; line-height: 1.7;">{{ $selectedReport->report_text ?? 'No description logs reported.' }}</p>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-12 col-md-6">
                                                <div class="modal-info-item">
                                                    <div class="modal-info-label">Reviewed By</div>
                                                    <div class="modal-info-value">{{ optional($selectedReport->reviewedBy)->name ?? 'Pending review' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="modal-info-item">
                                                    <div class="modal-info-label">Approved By</div>
                                                    <div class="modal-info-value">{{ optional($selectedReport->approvedBy)->name ?? 'Pending approval' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-info-item" style="background: #ffffff;">
                                            <div class="modal-info-label">Approval Remarks</div>
                                            <div class="modal-info-value">{{ $selectedReport->approval_remarks ?? 'No remarks' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-xl-5">
                                    <div class="modal-sidebar-card mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="fw-bold" style="color: var(--cms-green-dark); font-size: 0.9rem;">Site Images</div>
                                            <div class="small text-muted">{{ count((array) ($selectedReport->site_images ?? [])) }} uploaded</div>
                                        </div>
                                        @php $images = (array) ($selectedReport->site_images ?? []); @endphp
                                        @if(!empty($images))
                                            <div class="modal-image-grid mb-3">
                                                @foreach(array_slice($images, 0, 4) as $image)
                                                    @php $imageUrl = is_string($image) && $image ? asset('storage/' . ltrim($image, '/')) : ''; @endphp
                                                    @if($imageUrl)
                                                        <button type="button" class="modal-image-thumb lightbox-trigger" data-full-image="{{ $imageUrl }}" aria-label="Preview site image">
                                                            <img src="{{ $imageUrl }}" alt="Site image">
                                                        </button>
                                                    @endif
                                                @endforeach
                                                @if(count($images) > 4)
                                                    <div class="modal-more-badge">+{{ count($images) - 4 }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-muted small border rounded-3 p-3 mb-3" style="background: #f8faf9;">No site images were attached to this report.</div>
                                        @endif

                                        <div class="modal-section-title">Approval Timeline</div>
                                        <div class="modal-timeline">
                                            <div class="modal-timeline-step active">
                                                <div class="modal-timeline-icon"><i class="bi bi-check"></i></div>
                                                <div class="modal-timeline-label">Submitted</div>
                                            </div>
                                            <div class="modal-timeline-step {{ $reportStatus !== 'pending' ? 'active' : 'current' }}">
                                                <div class="modal-timeline-icon"><i class="bi bi-clock"></i></div>
                                                <div class="modal-timeline-label">Under Review</div>
                                            </div>
                                            <div class="modal-timeline-step {{ $reportStatus === 'approved' ? 'active' : '' }}">
                                                <div class="modal-timeline-icon"><i class="bi bi-circle"></i></div>
                                                <div class="modal-timeline-label">Approved</div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($selectedReport->status === 'pending')
                                        <div class="modal-action-row">
                                            <button type="button" class="btn-modal btn-modal-reject js-reject-report" data-report-id="{{ $selectedReport->report_id }}">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                            <button type="button" class="btn-modal btn-modal-approve js-approve-report" data-report-id="{{ $selectedReport->report_id }}">
                                                <i class="bi bi-check2"></i> Approve
                                            </button>
                                        </div>
                                    @else
                                        <div class="modal-status-note">
                                            <i class="bi bi-info-circle"></i>
                                            This report has already been {{ $selectedReport->status === 'approved' ? 'approved' : 'rejected' }}.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-file-earmark-text"></i>
                                Select a report from the table queue to preview its core properties and verification parameters.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Lightbox Modal --}}
    <div class="image-lightbox" id="reportImageLightbox" role="dialog" aria-modal="true" aria-label="Image preview">
        <button type="button" class="image-lightbox-close" id="lightboxCloseBtn" aria-label="Close preview">&times;</button>
        <img src="" alt="Site image preview" id="lightboxImage">
    </div>

    <div id="imagePreviewOverlay" class="image-preview-overlay" data-preview-overlay>
        <div class="image-preview-card">
            <img src="" alt="Preview attachment">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('reportsSearchInput');
            const projectFilter = document.getElementById('projectFilter');
            const phaseFilter = document.getElementById('phaseFilter');
            const supervisorFilter = document.getElementById('supervisorFilter');
            const statusFilter = document.getElementById('statusFilter');
            const tableBody = document.getElementById('reportsTableBody');
            const heading = document.getElementById('reportsListHeading');
            const workspaceLayout = document.getElementById('reportsWorkspaceLayout');
            const detailsPanel = document.getElementById('report-details-panel');
            const reportDetailsModal = document.getElementById('reportDetailsModal');
            const summaryValues = document.querySelectorAll('.summary-card .value');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const reportsDataUrl = '{{ route('admin.reports.data') }}';
            const reportsBaseUrl = '{{ url('/admin/reports') }}';
            const detailsBaseUrl = reportsBaseUrl;
            const downloadBaseUrl = reportsBaseUrl;
            let activeReportId = null;
            let debounceTimer;

            function pauseModalFocusTrap() {
                const modal = bootstrap.Modal.getInstance(reportDetailsModal);
                if (modal && modal._focustrap) {
                    modal._focustrap.deactivate();
                }
            }

            function resumeModalFocusTrap() {
                const modal = bootstrap.Modal.getInstance(reportDetailsModal);
                if (modal && modal._focustrap) {
                    modal._focustrap.activate();
                }
            }

            function openImagePreview(imageUrl) {
                const overlay = document.getElementById('imagePreviewOverlay');
                const previewImage = overlay.querySelector('img');
                previewImage.src = imageUrl;
                overlay.classList.add('open');
            }

            function closeImagePreview() {
                const overlay = document.getElementById('imagePreviewOverlay');
                overlay.classList.remove('open');
                const previewImage = overlay.querySelector('img');
                previewImage.src = '';
            }

            function openLightbox(imageUrl) {
                const lightbox = document.getElementById('reportImageLightbox');
                const lightboxImage = document.getElementById('lightboxImage');
                if (!lightbox || !lightboxImage) return;
                lightboxImage.src = imageUrl;
                lightbox.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                const lightbox = document.getElementById('reportImageLightbox');
                if (!lightbox) return;
                lightbox.classList.remove('is-open');
                document.body.style.overflow = '';
                const lightboxImage = document.getElementById('lightboxImage');
                if (lightboxImage) {
                    setTimeout(() => { lightboxImage.src = ''; }, 200);
                }
            }

            function setDetailsPanelOpen(isOpen) {
                workspaceLayout?.classList.toggle('is-panel-open', isOpen);
                if (isOpen && reportDetailsModal && !reportDetailsModal.classList.contains('show')) {
                    const modal = bootstrap.Modal.getOrCreateInstance(reportDetailsModal);
                    modal.show();
                }
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
                activeReportId = null;
                const modal = bootstrap.Modal.getOrCreateInstance(reportDetailsModal);
                modal.hide();
                detailsPanel.innerHTML = `
                    <div class="sidebar-fallback-state">
                        <i class="bi bi-file-earmark-text"></i>
                        Select a report from the table queue to preview its core properties and verification parameters.
                    </div>
                `;
            }

            document.body.addEventListener('click', function (event) {
                const target = event.target;
                if (target.matches('.previewable-image') || target.closest('.lightbox-trigger')) {
                    const imageUrl = target.matches('.previewable-image') ? target.src : (target.closest('.lightbox-trigger')?.dataset?.fullImage || target.closest('.lightbox-trigger')?.querySelector('img')?.src);
                    if (imageUrl) {
                        openLightbox(imageUrl);
                    }
                    return;
                }
                if (target.closest('.btn-close') && target.closest('#reportDetailsModal')) {
                    closeDetailsPanel();
                    return;
                }
                if (target.matches('.image-lightbox-close') || target.matches('#reportImageLightbox')) {
                    closeLightbox();
                }
                if (target.matches('.image-preview-close') || target.matches('#imagePreviewOverlay')) {
                    closeImagePreview();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const lightbox = document.getElementById('reportImageLightbox');
                    if (lightbox?.classList.contains('is-open')) {
                        closeLightbox();
                    }
                }
            });

            function buildQueryParams() {
                const params = new URLSearchParams();
                const projectId = projectFilter?.value || '';
                const phaseId = phaseFilter?.value || '';
                const supervisorId = supervisorFilter?.value || '';
                const status = statusFilter?.value || '';
                const search = searchInput?.value.trim() || '';

                if (projectId) params.set('project_id', projectId);
                if (phaseId) params.set('phase_id', phaseId);
                if (supervisorId) params.set('supervisor_id', supervisorId);
                if (status) params.set('status', status);
                if (search) params.set('search', search);
                if (activePage > 1) params.set('page', activePage);

                return params.toString();
            }

            function loadReports() {
                const query = buildQueryParams();
                const url = `${reportsDataUrl}${query ? '?' + query : ''}`;

                fetch(url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store'
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to load reports');
                        }
                        return response.json();
                    })
                    .then(payload => {
                        updateSummary(payload.stats);
                        renderTable(payload.reports);
                        renderPhaseOptions(payload.phases);
                        renderPagination(payload.pagination);
                        heading.textContent = `Reports List (${payload.pagination.total})`;
                        if (payload.reports.length) {
                            const shouldKeepCurrentSelection = activeReportId && payload.reports.some(report => report.id === activeReportId);
                            if (!shouldKeepCurrentSelection) {
                                activeReportId = null;
                                closeDetailsPanel();
                            } else if (reportDetailsModal?.classList.contains('show')) {
                                loadReportDetails(activeReportId);
                            }
                        } else {
                            activeReportId = null;
                            closeDetailsPanel();
                            detailsPanel.innerHTML = `<div class="sidebar-fallback-state"><i class="bi bi-file-earmark-text"></i>${payload.empty_message}</div>`;
                        }
                    })
                    .catch((error) => {
                        console.error('Report filter load failed:', error);
                        Swal.fire({ title: 'Unable to load reports', text: 'Please refresh and try again.', icon: 'error' });
                    });
            }

            const reportsPaginationInfo = document.getElementById('reportsPaginationInfo');
            const reportsPagination = document.getElementById('reportsPagination');
            const urlParams = new URLSearchParams(window.location.search);
            let activePage = Number(urlParams.get('page')) || 1;

            function updateSummary(stats) {
                const [totalCard, pendingCard, approvedCard, rejectedCard] = summaryValues;
                totalCard.textContent = stats.total ?? 0;
                pendingCard.textContent = stats.pending ?? 0;
                approvedCard.textContent = stats.approved ?? 0;
                rejectedCard.textContent = stats.rejected ?? 0;
            }

            function renderPagination(pagination) {
                if (!pagination || pagination.total <= 0) {
                    reportsPaginationInfo.textContent = 'Showing 0 reports';
                    reportsPagination.innerHTML = '';
                    return;
                }

                activePage = pagination.current_page;
                reportsPaginationInfo.textContent = `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} reports`;

                const buttons = [];
                buttons.push(`<button type="button" class="pagination-button" data-page="${Math.max(1, pagination.current_page - 1)}" ${pagination.current_page === 1 ? 'disabled' : ''} aria-label="Previous page">‹</button>`);

                for (let page = 1; page <= pagination.last_page; page += 1) {
                    buttons.push(`<button type="button" class="pagination-button ${page === pagination.current_page ? 'active' : ''}" data-page="${page}" aria-label="Page ${page}">${page}</button>`);
                }

                buttons.push(`<button type="button" class="pagination-button" data-page="${Math.min(pagination.last_page, pagination.current_page + 1)}" ${pagination.current_page === pagination.last_page ? 'disabled' : ''} aria-label="Next page">›</button>`);

                reportsPagination.innerHTML = buttons.join('');
            }

            function setPage(page) {
                activePage = Number(page) || 1;
            }

            function renderTable(reports) {
                if (!reports.length) {
                    tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4">No accomplishment reports found.</td></tr>`;
                    return;
                }

                tableBody.innerHTML = reports.map(report => `
                    <tr data-report-id="${report.id}">
                        <td class="cell-bold" data-label="ID">${report.report_id}</td>
                        <td data-label="Report Title"><span class="cell-bold">${report.report_title}</span></td>
                        <td data-label="Project">${report.project_name}</td>
                        <td data-label="Phase">${report.phase_name}</td>
                        <td data-label="Supervisor"><div class="user-cell"><span>${report.supervisor_name}</span></div></td>
                        <td data-label="Date"><span class="cell-bold">${report.submitted_at}</span></td>
                        <td data-label="Status"><span class="status-pill ${report.status_class}">${report.status_label}</span></td>
                        <td data-label="Actions">
                            <div class="action-icons-group">
                                <button type="button" class="btn-icon-action js-view-report" data-report-id="${report.id}" title="View Details"><i class="bi bi-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                `).join('');

                tableBody.querySelectorAll('.js-view-report').forEach(button => {
                    button.addEventListener('click', function () {
                        activeReportId = Number(this.dataset.reportId);
                        setDetailsPanelOpen(true);
                        loadReportDetails(activeReportId);
                    });
                });

                tableBody.querySelectorAll('.js-approve-report').forEach(button => {
                    button.addEventListener('click', function () {
                        handleApproval(Number(this.dataset.reportId));
                    });
                });

                tableBody.querySelectorAll('.js-reject-report').forEach(button => {
                    button.addEventListener('click', function () {
                        handleRejection(Number(this.dataset.reportId));
                    });
                });
            }

            function renderPhaseOptions(phases) {
                const currentPhase = phaseFilter.value;
                const options = ['<option value="">All Phases</option>'];
                phases.forEach(phase => {
                    options.push(`<option value="${phase.phase_id}" ${currentPhase == phase.phase_id ? 'selected' : ''}>${phase.phase_name}</option>`);
                });
                phaseFilter.innerHTML = options.join('');
            }

            function loadReportDetails(reportId) {
                setDetailsPanelOpen(true);
                beginDetailsTransition();
                fetch(`${detailsBaseUrl}/${reportId}/details`, { headers: { Accept: 'application/json' } })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Request failed');
                        }
                        return response.json();
                    })
                    .then(payload => {
                        if (!payload.success) return;
                        renderDetailsPanel(payload.report);
                    })
                    .catch(() => {
                        finishDetailsTransition();
                        Swal.fire({ title: 'Unable to load report details', icon: 'error' });
                    });
            }

            function renderDetailsPanel(report) {
                setDetailsPanelOpen(true);
                const attachmentImages = Array.isArray(report.site_images) ? report.site_images.filter(Boolean) : [];
                const attachmentCount = attachmentImages.length;
                const attachmentMarkup = attachmentCount ? `
                    <div class="modal-image-grid mb-3">
                        ${attachmentImages.slice(0, 4).map(image => `
                            <button type="button" class="modal-image-thumb lightbox-trigger" data-full-image="${image}" aria-label="Preview site image">
                                <img src="${image}" alt="Site image">
                            </button>
                        `).join('')}
                        ${attachmentCount > 4 ? `<div class="modal-more-badge">+${attachmentCount - 4}</div>` : ''}
                    </div>
                ` : '<div class="text-muted small border rounded-3 p-3 mb-3" style="background: #f8faf9;">No site images were attached to this report.</div>';

                detailsPanel.innerHTML = `
                    <div class="row gx-4 gy-4">
                        <div class="col-12 col-xl-7">
                            <div class="modal-detail-card">
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-4 p-3 rounded-3" style="background: #ffffff; border: 1px solid #e8f0eb;">
                                    <div>
                                        <div class="modal-section-title mb-1" style="margin-top:0; border:none; padding-bottom:0;">Report ID</div>
                                        <div class="fw-bold text-dark" style="font-size: 1.15rem; color: var(--cms-green-dark);">${report.report_id}</div>
                                    </div>
                                    <div class="text-sm-end">
                                        <div class="modal-section-title mb-1" style="margin-top:0; border:none; padding-bottom:0;">Approval Status</div>
                                        <span class="status-pill ${report.status === 'approved' ? 'approved' : report.status === 'rejected' ? 'rejected' : 'pending'} p-2 mt-1 d-inline-block">${report.status_label || 'Pending Review'}</span>
                                    </div>
                                </div>

                                <div class="modal-section-title">Project Information</div>
                                <div class="modal-info-grid mb-4">
                                    <div class="modal-info-item">
                                        <div class="modal-info-label">Project</div>
                                        <div class="modal-info-value">${report.project_name || 'N/A'}</div>
                                    </div>
                                    <div class="modal-info-item">
                                        <div class="modal-info-label">Construction Phase</div>
                                        <div class="modal-info-value">${report.phase_name || 'N/A'}</div>
                                    </div>
                                    <div class="modal-info-item">
                                        <div class="modal-info-label">Report Date</div>
                                        <div class="modal-info-value">${report.submitted_at || 'N/A'}</div>
                                    </div>
                                    <div class="modal-info-item">
                                        <div class="modal-info-label">Submitted By</div>
                                        <div class="modal-info-value">${report.supervisor_name || 'Supervisor'}</div>
                                    </div>
                                </div>

                                <div class="modal-section-title">Construction Accomplishment</div>
                                <div class="modal-accomplishment-box mb-4">
                                    <p class="mb-0 text-dark small" style="white-space: pre-line; line-height: 1.7;">${report.report_text || 'No description logs reported.'}</p>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <div class="modal-info-item">
                                            <div class="modal-info-label">Reviewed By</div>
                                            <div class="modal-info-value">${report.reviewed_by || 'Pending review'}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="modal-info-item">
                                            <div class="modal-info-label">Approved By</div>
                                            <div class="modal-info-value">${report.approved_by || 'Pending approval'}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-info-item" style="background: #ffffff;">
                                    <div class="modal-info-label">Approval Remarks</div>
                                    <div class="modal-info-value">${report.approval_remarks || 'No remarks'}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="modal-sidebar-card mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="fw-bold" style="color: var(--cms-green-dark); font-size: 0.9rem;">Site Images</div>
                                    <div class="small text-muted">${attachmentCount} uploaded</div>
                                </div>
                                ${attachmentMarkup}

                                <div class="modal-section-title">Approval Timeline</div>
                                <div class="modal-timeline">
                                    <div class="modal-timeline-step active">
                                        <div class="modal-timeline-icon"><i class="bi bi-check"></i></div>
                                        <div class="modal-timeline-label">Submitted</div>
                                    </div>
                                    <div class="modal-timeline-step ${report.status !== 'pending' ? 'active' : 'current' }">
                                        <div class="modal-timeline-icon"><i class="bi bi-clock"></i></div>
                                        <div class="modal-timeline-label">Under Review</div>
                                    </div>
                                    <div class="modal-timeline-step ${report.status === 'approved' ? 'active' : '' }">
                                        <div class="modal-timeline-icon"><i class="bi bi-circle"></i></div>
                                        <div class="modal-timeline-label">Approved</div>
                                    </div>
                                </div>
                            </div>

                            ${report.status === 'pending' ? `
                                <div class="modal-action-row">
                                    <button type="button" class="btn-modal btn-modal-reject js-reject-report" data-report-id="${report.id}">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                    <button type="button" class="btn-modal btn-modal-approve js-approve-report" data-report-id="${report.id}">
                                        <i class="bi bi-check2"></i> Approve
                                    </button>
                                </div>
                            ` : `
                                <div class="modal-status-note">
                                    <i class="bi bi-info-circle"></i>
                                    This report has already been ${report.status === 'approved' ? 'approved' : 'rejected'}.
                                </div>
                            `}
                        </div>
                    </div>
                `;

                detailsPanel.dataset.reportStatus = report.status || 'pending';
                detailsPanel.querySelector('.js-approve-report')?.addEventListener('click', function () {
                    handleApproval(Number(this.dataset.reportId));
                });
                detailsPanel.querySelector('.js-reject-report')?.addEventListener('click', function () {
                    handleRejection(Number(this.dataset.reportId));
                });
                finishDetailsTransition();
            }

            function handleApproval(reportId) {
                const currentStatus = detailsPanel?.dataset.reportStatus;
                if (currentStatus === 'approved') {
                    return Swal.fire({ title: 'Already Approved', text: 'This report is already approved.', icon: 'info' });
                }
                if (currentStatus === 'rejected') {
                    return Swal.fire({ title: 'Cannot Approve', text: 'This report has already been rejected.', icon: 'warning' });
                }

                Swal.fire({
                    title: 'Approve Report?',
                    text: 'This will mark the report as approved and notify the supervisor.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Approve',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#1c6b43',
                    cancelButtonColor: '#6c757d',
                    didOpen: pauseModalFocusTrap
                }).then((result) => {
                    if (!result.isConfirmed) {
                        resumeModalFocusTrap();
                        return;
                    }
                    Swal.fire({ title: 'Approving report...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
                    fetch(`${detailsBaseUrl}/${reportId}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ approval_remarks: '' })
                    })
                        .then(async response => {
                            const payload = await response.json().catch(() => ({}));
                            if (!response.ok || !payload.success) {
                                throw new Error(payload.message || 'Unable to approve report.');
                            }
                            return payload;
                        })
                        .then(payload => {
                            Swal.close();
                            if (payload.auto_completed) {
                                Swal.fire({ title: 'Phase Completed', text: 'The phase has automatically been marked as Completed because progress reached 100%.', icon: 'success', confirmButtonColor: '#1c6b43' });
                            } else {
                                Swal.fire({ title: 'Report Approved', text: payload.message || 'The report has been approved.', icon: 'success', confirmButtonColor: '#1c6b43' });
                            }
                            closeDetailsPanel();
                            loadReports();
                            resumeModalFocusTrap();
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire({ title: 'Approval Failed', text: error.message || 'Unable to complete approval.', icon: 'error' });
                            resumeModalFocusTrap();
                        });
                });
            }

            function handleRejection(reportId) {
                const currentStatus = detailsPanel?.dataset.reportStatus;
                if (currentStatus === 'rejected') {
                    return Swal.fire({ title: 'Already Rejected', text: 'This report is already rejected.', icon: 'info' });
                }
                if (currentStatus === 'approved') {
                    return Swal.fire({ title: 'Cannot Reject', text: 'This report has already been approved.', icon: 'warning' });
                }

                Swal.fire({
                    title: 'Reject Report',
                    input: 'textarea',
                    inputLabel: 'Rejection Remarks',
                    inputPlaceholder: 'Enter the reason for rejection...',
                    inputAttributes: { maxlength: 1000 },
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#b23a3a',
                    cancelButtonColor: '#6c757d',
                    preConfirm: (value) => {
                        if (!value || !value.trim()) {
                            Swal.showValidationMessage('Rejection remarks are required.');
                        }
                    },
                    didOpen: pauseModalFocusTrap
                }).then((result) => {
                    if (!result.isConfirmed) {
                        resumeModalFocusTrap();
                        return;
                    }
                    const remarks = result.value?.trim();
                    Swal.fire({ title: 'Rejecting report...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
                    fetch(`${detailsBaseUrl}/${reportId}/revise`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ approval_remarks: remarks, decision: 'reject' })
                    })
                        .then(async response => {
                            const payload = await response.json().catch(() => ({}));
                            if (!response.ok || !payload.success) {
                                throw new Error(payload.message || 'Unable to reject report.');
                            }
                            return payload;
                        })
                        .then(payload => {
                            Swal.close();
                            Swal.fire({ title: 'Report Rejected', text: payload.message || 'The report has been rejected.', icon: 'success', confirmButtonColor: '#b23a3a' });
                            closeDetailsPanel();
                            loadReports();
                            resumeModalFocusTrap();
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire({ title: 'Rejection Failed', text: error.message || 'Unable to complete rejection.', icon: 'error' });
                            resumeModalFocusTrap();
                        });
                });
            }

            [projectFilter, phaseFilter, supervisorFilter, statusFilter].forEach(control => {
                control?.addEventListener('change', () => {
                    clearTimeout(debounceTimer);
                    activePage = 1;
                    debounceTimer = setTimeout(loadReports, 150);
                });
            });

            searchInput?.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                activePage = 1;
                debounceTimer = setTimeout(loadReports, 300);
            });

            document.addEventListener('click', function (event) {
                const pageButton = event.target.closest('#reportsPagination button[data-page]');
                if (pageButton) {
                    const page = Number(pageButton.dataset.page);
                    if (page && page !== activePage) {
                        setPage(page);
                        loadReports();
                    }
                    return;
                }

                const downloadLink = event.target.closest('a[data-report-id]');
                if (downloadLink) {
                    activeReportId = Number(downloadLink.dataset.reportId);
                }
            });

            closeDetailsPanel();

            // Automatically refresh the report list whenever a filter state exists.
            loadReports();

            // Open report details modal when a report row or view button is clicked
            tableBody.querySelectorAll('.js-view-report').forEach(button => {
                button.addEventListener('click', function () {
                    activeReportId = Number(this.dataset.reportId);
                    loadReportDetails(activeReportId);
                });
            });

            reportDetailsModal?.addEventListener('hidden.bs.modal', function () {
                activeReportId = null;
                detailsPanel.innerHTML = `
                    <div class="sidebar-fallback-state">
                        <i class="bi bi-file-earmark-text"></i>
                        Select a report from the table queue to preview its core properties and verification parameters.
                    </div>
                `;
            });
        });
    </script>
</div>
@endsection