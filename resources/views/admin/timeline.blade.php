@extends('layouts.admin')

@section('title', 'Project Timeline - D&G Construction Monitor')
@section('page_title', 'Project Timeline')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Syne:wght@600;700;800&display=swap');

    #pg-timeline {
        --primary-green: var(--brand-green);
        --secondary-green: var(--brand-accent);
        --light-green: #DCFCE7;
        --background: #F8FAFC;
        --card: #FFFFFF;
        --border: #E2E8F0;
        --primary-text: #1E293B;
        --secondary-text: #64748B;
        --shadow: 0 12px 35px rgba(15, 23, 42, 0.06);
        --radius: 20px;
        color: var(--primary-text);
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: transparent;
        padding-bottom: 1.5rem;
    }

    #pg-timeline * { box-sizing: border-box; }

    .page-header-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.4rem 1.5rem;
        border-radius: var(--radius);
        background: linear-gradient(135deg, #ffffff 0%, #f8fcf8 100%);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        margin-bottom: 1.25rem;
    }

    .eyebrow {
        margin: 0 0 0.35rem;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.16em;
        font-weight: 700;
        color: var(--secondary-green);
    }

    .page-title {
        margin: 0;
        font-family: 'Syne', 'Plus Jakarta Sans', sans-serif;
        font-size: 1.55rem;
        font-weight: 700;
        color: var(--primary-text);
    }

    .page-subtitle {
        margin: 0.25rem 0 0;
        font-size: 0.95rem;
        color: var(--secondary-text);
    }

    .status-pill {
        padding: 0.6rem 0.9rem;
        border-radius: 999px;
        background: var(--light-green);
        color: var(--primary-green);
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .top-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: 0.9rem;
        padding: 1rem 1.2rem;
        border-radius: var(--radius);
        background: var(--card);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        margin-bottom: 1.25rem;
    }

    .toolbar-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        min-width: 170px;
    }

    .toolbar-group.search-group {
        min-width: 300px;
        flex: 0 1 340px;
        max-width: 360px;
    }

    .toolbar-group label {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--secondary-text);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .toolbar-input,
    .toolbar-select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0.5rem 0.7rem;
        background: #fff;
        color: var(--primary-text);
        font-size: 0.85rem;
        outline: none;
    }

    .toolbar-select#projectSelector,
    .toolbar-select#phaseFilterSelector,
    .toolbar-select#statusFilterSelector {
        padding: 0.4rem 2rem 0.4rem 0.7rem;
        font-size: 0.82rem;
        min-width: 0;
        max-width: 100%;
    }

    .toolbar-input:focus,
    .toolbar-select:focus {
        border-color: var(--secondary-green);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
    }

    .toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .btn-ghost,
    .btn-primary {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0.72rem 1rem;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-ghost:hover,
    .btn-primary:hover {
        transform: translateY(-1px);
    }

    .btn-ghost {
        background: #fff;
        color: var(--primary-text);
    }

    .btn-primary {
        background: var(--primary-green);
        color: #fff;
        border-color: var(--primary-green);
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .summary-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 1rem 1.1rem;
        border-radius: 18px;
        background: var(--card);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        min-height: 104px;
    }

    .summary-icon {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 1.15rem;
        background: var(--card-bg);
        color: var(--card-text);
    }

    .summary-count {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--primary-text);
        line-height: 1.1;
    }

    .summary-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--secondary-text);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-top: 0.2rem;
    }

    .timeline-card {
        padding: 1.25rem;
        border-radius: var(--radius);
        background: var(--card);
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
    }

    .milestone-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        z-index: 2200;
        overflow-y: auto;
    }

    /* SweetAlert2 defaults to z-index 1060, which sits BEHIND the modal above (2200).
       Without this override, success/error alerts render but are invisible under the
       modal backdrop, making it look like nothing happened after Save. */
    .swal2-container {
        z-index: 4000 !important;
    }

    .milestone-modal-card {
        width: min(780px, 100%);
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        margin: auto;
        transform: translateY(0);
        animation: milestone-modal-in 220ms ease-out;
    }

    @keyframes milestone-modal-in {
        from { opacity: 0; transform: translateY(10px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .milestone-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.35rem 1.45rem 1rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        background: transparent;
    }

    .milestone-modal-eyebrow {
        margin: 0 0 0.25rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--secondary-green);
    }

    .milestone-modal-title {
        margin: 0;
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary-text);
    }

    .milestone-modal-subtitle {
        margin: 0.3rem 0 0;
        font-size: 0.92rem;
        color: var(--secondary-text);
    }

    .milestone-modal-close {
        width: 40px;
        height: 40px;
        border: 1px solid var(--border);
        border-radius: 50%;
        background: #fff;
        color: var(--primary-text);
        cursor: pointer;
    }

    .milestone-modal-form {
        padding: 1.2rem 1.45rem 1.45rem;
        background: transparent;
    }

    .milestone-modal-section {
        border: 1px solid rgba(226, 232, 240, 0.7);
        border-radius: 16px;
        background: #f8fafc;
        padding: 1rem 1rem 0.95rem;
        margin-bottom: 1rem;
    }

    .milestone-modal-section-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--primary-text);
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        margin-bottom: 0.2rem;
    }

    .milestone-modal-section-copy {
        font-size: 0.84rem;
        color: var(--secondary-text);
        margin: 0 0 0.9rem;
    }

    .milestone-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .milestone-modal-field {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .milestone-modal-field label {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--primary-text);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .milestone-modal-required {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--secondary-green);
        background: rgba(22, 163, 74, 0.1);
        padding: 0.2rem 0.45rem;
        border-radius: 999px;
    }

    .milestone-modal-helper {
        margin: 0;
        font-size: 0.76rem;
        color: var(--secondary-text);
        line-height: 1.5;
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .milestone-modal-field input,
    .milestone-modal-field select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0.78rem 0.9rem;
        background: #fff;
        color: var(--primary-text);
        font-size: 0.92rem;
        outline: none;
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .milestone-modal-field input:focus,
    .milestone-modal-field select:focus {
        border-color: var(--secondary-green);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
    }

    .milestone-modal-status-group {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
        margin-top: 1rem;
    }

    .milestone-modal-status-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #f8fafc;
        padding: 1rem;
        transition: border-color 180ms ease, box-shadow 180ms ease;
    }

    .milestone-modal-status-card:hover {
        border-color: rgba(22, 163, 74, 0.35);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .status-switch-row {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .status-switch {
        display: inline-flex;
        width: 52px;
        height: 30px;
        position: relative;
        border-radius: 999px;
        background: #cbd5e1;
        cursor: pointer;
        transition: background 180ms ease;
    }

    .status-switch input {
        appearance: none;
        position: absolute;
        inset: 0;
        margin: 0;
        cursor: pointer;
        opacity: 0;
        z-index: 2;
    }

    .status-switch-slider {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.16);
        transition: transform 180ms ease;
    }

    .status-switch input:checked + .status-switch-slider {
        transform: translateX(22px);
    }

    .status-switch input:checked ~ .status-switch-slider {
        transform: translateX(22px);
    }

    .status-switch input:checked {
        background: transparent;
    }

    .status-switch-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--primary-text);
    }

    .status-switch-copy {
        font-size: 0.8rem;
        color: var(--secondary-text);
        margin-top: 0.15rem;
    }

    .milestone-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.3rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    .milestone-modal-actions .btn-primary,
    .milestone-modal-actions .btn-ghost {
        min-width: 150px;
        justify-content: center;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .milestone-modal-actions .btn-primary {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
        color: #ffffff !important;
        border: 1px solid #15803d !important;
        box-shadow: 0 12px 24px rgba(22, 101, 52, 0.22) !important;
        font-weight: 800;
    }

    .milestone-modal-actions .btn-primary:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .milestone-modal-actions .btn-primary:focus-visible {
        outline: 3px solid rgba(22, 163, 74, 0.24);
        outline-offset: 2px;
    }

    @media (max-width: 720px) {
        .milestone-modal-grid,
        .milestone-modal-status-group {
            grid-template-columns: 1fr;
        }
    }

    .timeline-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .timeline-card-header-copy {
        flex: 1 1 280px;
        min-width: 0;
    }

    .panel-title {
        margin: 0;
        font-family: 'Syne', 'Plus Jakarta Sans', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-text);
    }

    .panel-subtitle {
        margin: 0.2rem 0 0;
        font-size: 0.9rem;
        color: var(--secondary-text);
    }

    .gantt-toolbar-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        gap: 0.6rem;
        row-gap: 0.6rem;
        margin-left: auto;
        overflow: hidden;
    }

    .gantt-mode-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem;
        row-gap: 0.6rem;
    }

    .gantt-mode-actions.gantt-controls-enter {
        animation: gantt-controls-fade-slide 220ms ease-out both;
    }

    .gantt-mode-actions.gantt-controls-enter .btn-ghost,
    .gantt-mode-actions.gantt-controls-enter .toolbar-select.compact-select {
        animation: gantt-controls-item-pop 220ms ease-out both;
    }

    .gantt-mode-actions.gantt-controls-enter .btn-ghost:nth-of-type(2),
    .gantt-mode-actions.gantt-controls-enter .toolbar-select.compact-select {
        animation-delay: 70ms;
    }

    .gantt-mode-actions.gantt-controls-enter .btn-ghost:nth-of-type(3) {
        animation-delay: 120ms;
    }

    .gantt-mode-actions.gantt-controls-enter .btn-ghost:nth-of-type(4) {
        animation-delay: 170ms;
    }

    .gantt-toolbar-actions .btn-ghost {
        white-space: nowrap;
    }

    .toolbar-select.compact-select {
        min-width: 120px;
    }

    @keyframes gantt-controls-fade-slide {
        from {
            opacity: 0;
            transform: translateY(4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes gantt-controls-item-pop {
        from {
            opacity: 0;
            transform: translateY(6px) scale(0.97);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .view-toggle-group {
        display: inline-flex;
        gap: 0.3rem;
        padding: 0.3rem;
        border-radius: 999px;
        background: #F4F7F8;
        flex-wrap: nowrap;
    }

    .view-toggle-btn {
        border: none;
        border-radius: 999px;
        padding: 0.5rem 0.9rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--secondary-text);
        background: transparent;
        cursor: pointer;
    }

    .view-toggle-btn.active {
        color: #fff;
        background: var(--primary-green);
    }

    .gantt-shell {
        border: 1px solid var(--border);
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, #fcfdfa 0%, #ffffff 100%);
        min-height: 420px;
    }

    .gantt-scroll-shell {
        max-height: 680px;
        overflow: auto;
        border-radius: 14px;
        border: 1px solid #f1f5f9;
        background: #ffffff;
        scrollbar-width: thin;
        scrollbar-color: rgba(22, 101, 52, 0.35) transparent;
    }

    .gantt-scroll-shell::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .gantt-scroll-shell::-webkit-scrollbar-track {
        background: transparent;
    }

    .gantt-scroll-shell::-webkit-scrollbar-thumb {
        background: rgba(22, 101, 52, 0.35);
        border-radius: 999px;
    }

    .gantt-scroll-shell::-webkit-scrollbar-thumb:hover {
        background: rgba(22, 101, 52, 0.5);
    }

    #pg-timeline {
        min-height: 100%;
        padding-bottom: 1.5rem;
    }

    .content {
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    .timeline-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1.25rem;
        align-items: start;
    }

    .timeline-side-panel {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .insight-card {
        padding: 1rem 1.05rem;
        border-radius: 18px;
        border: 1px solid var(--border);
        background: linear-gradient(135deg, #ffffff 0%, #f8fcf8 100%);
        box-shadow: var(--shadow);
    }

    .insight-card-title {
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--secondary-text);
        margin-bottom: 0.8rem;
    }

    .progress-ring {
        width: 110px;
        height: 110px;
        margin: 0 auto 0.95rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: conic-gradient(var(--primary-green) 0 0, #e2e8f0 0 100%);
        position: relative;
        box-shadow: inset 0 0 0 8px rgba(255,255,255,0.9), 0 10px 24px rgba(22, 101, 52, 0.14);
    }

    .progress-ring::after {
        content: '';
        position: absolute;
        inset: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fcf8 100%);
        border-radius: 50%;
        border: 1px solid rgba(22, 101, 52, 0.08);
    }

    .progress-ring span {
        position: relative;
        z-index: 1;
        font-size: 1.18rem;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.02em;
    }

    .metric-list {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .summary-stat-grid {
        display: grid;
        gap: 0.7rem;
    }

    .summary-stat-card {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        padding: 0.75rem 0.8rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fbf8 0%, #f3f8f4 100%);
        border: 1px solid rgba(22, 101, 52, 0.08);
    }

    .summary-stat-icon {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 10px;
        background: rgba(22, 101, 52, 0.1);
        color: var(--primary-green);
        font-size: 0.95rem;
    }

    .summary-stat-body {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .summary-stat-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--secondary-text);
        margin-bottom: 0.18rem;
    }

    .summary-stat-body strong {
        color: var(--primary-text);
        font-weight: 700;
        line-height: 1.3;
        word-break: break-word;
    }

    .status-guide-card {
        padding: 0.9rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #f8fbf8 0%, #ffffff 100%);
        border: 1px solid rgba(22, 101, 52, 0.08);
        box-shadow: var(--shadow);
    }

    .status-guide-title {
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--secondary-text);
        margin-bottom: 0.65rem;
    }

    .status-guide-list {
        display: grid;
        gap: 0.5rem;
    }

    .status-guide-item {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.45rem 0.55rem;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #eef2f7;
    }

    .status-guide-swatch {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06);
    }

    .status-guide-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--primary-text);
    }

    .timeline-table-legend {
        margin-top: 0.9rem;
        padding: 0.95rem 1rem;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .timeline-table-legend-title {
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--secondary-text);
        margin-bottom: 0.7rem;
    }

    .timeline-table-legend-body {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        align-items: center;
    }

    .legend-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.5rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .legend-row strong {
        font-size: 0.82rem;
        color: var(--primary-text);
    }

    .legend-icon {
        width: 30px;
        height: 30px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 50%;
        font-size: 0.9rem;
    }

    .legend-description {
        margin-top: 0.12rem;
        font-size: 0.76rem;
        color: var(--secondary-text);
        line-height: 1.3;
    }

    .status-stack {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .legend-item {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        padding: 0.75rem 0.8rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        font-size: 0.9rem;
        color: var(--secondary-text);
    }

    .legend-item strong {
        color: var(--primary-text);
        font-weight: 700;
    }

    .legend-item small {
        display: block;
        margin-top: 0.2rem;
        line-height: 1.35;
        color: var(--secondary-text);
    }

    .legend-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .legend-item-content {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .legend-topline {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-swath {
        width: 52px;
        height: 8px;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .compact-select {
        width: auto;
        min-width: 120px;
        max-width: 140px;
        height: 42px;
    }

    #dhtmlxGantt {
        width: 100%;
        height: 420px;
        min-height: 420px;
    }

    .gantt-scroll-shell {
        min-height: 420px;
    }

    .table-wrapper {
        overflow-x: auto;
        border: 1px solid var(--border);
        border-radius: 16px;
    }

    .standard-data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .standard-data-table th,
    .standard-data-table td {
        padding: 0.85rem 0.9rem;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
        font-size: 0.9rem;
    }

    .standard-data-table th {
        background: #F8FAFC;
        color: var(--secondary-text);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.74rem;
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
    .status-pill-badge.upcoming { background: #FFF8E1; color: #B7791F; }
    .status-pill-badge.delayed { background: #FDECEC; color: #C62828; }
    .status-pill-badge.pending { background: #F3F4F6; color: #6B7280; }

    .timeline-table-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        margin-top: 1rem;
        padding: 0 0.25rem;
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
        color: #475569;
        font-size: 0.86rem;
        font-weight: 500;
    }

    .btn-icon-table {
        border: 1px solid var(--border);
        border-radius: 10px;
        width: 36px;
        height: 36px;
        background: #fff;
        color: var(--secondary-text);
        cursor: pointer;
    }

    .action-icons-flex {
        display: flex;
        gap: 0.4rem;
    }

    .timeline-empty-state {
        text-align: center;
        padding: 2.2rem 1rem;
        color: var(--secondary-text);
    }

    @media (max-width: 1100px) {
        .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .page-header-card, .timeline-card-header { flex-direction: column; align-items: flex-start; }
        .toolbar-actions { margin-left: 0; }
        .top-toolbar { padding: 1rem; gap: 0.75rem; }
        .toolbar-group { min-width: min(100%, 220px); flex: 1 1 220px; }
        .toolbar-group.search-group { min-width: min(100%, 280px); flex: 1 1 280px; max-width: none; }
        .toolbar-actions { width: 100%; justify-content: flex-start; }
        .toolbar-actions .btn-primary, .toolbar-actions .btn-ghost { flex: 1 1 180px; justify-content: center; }
        .gantt-scroll-shell { max-height: 70vh; }
        .timeline-layout { grid-template-columns: 1fr; }
    }

    @media (max-width: 720px) {
        .summary-grid { grid-template-columns: 1fr; }
        .page-header-card { padding: 1rem 1rem 1.05rem; }
        .page-title { font-size: 1.25rem; }
        .page-subtitle { font-size: 0.88rem; }
        .top-toolbar { align-items: stretch; padding: 0.9rem; }
        .toolbar-group, .toolbar-group.search-group { width: 100%; min-width: 0; max-width: none; flex: 1 1 100%; }
        .toolbar-actions { flex-direction: column; align-items: stretch; }
        .toolbar-actions .btn-primary, .toolbar-actions .btn-ghost { width: 100%; flex: 1 1 auto; }
        .timeline-card { padding: 1rem; }
        .gantt-scroll-shell { max-height: 60vh; border-radius: 12px; min-height: 320px; }
        .gantt-shell { border-radius: 14px; }
        .view-toggle-group { width: 100%; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .view-toggle-btn { padding: 0.55rem 0.4rem; font-size: 0.74rem; text-align: center; }
        .gantt-toolbar-actions { width: 100%; justify-content: stretch; }
        .gantt-mode-actions { width: 100%; }
        .gantt-mode-actions .btn-ghost,
        .gantt-mode-actions .toolbar-select.compact-select { flex: 1 1 0; min-width: 0; }
        .toolbar-select.compact-select { max-width: none; }
        #projectSelector,
        #phaseFilterSelector,
        #statusFilterSelector {
            font-size: 12px !important;
            padding: 0.35rem 0.6rem !important;
            min-width: 0 !important;
            max-width: 100% !important;
        }
        .table-wrapper { overflow-x: hidden; }
        .standard-data-table,
        .standard-data-table thead,
        .standard-data-table tbody,
        .standard-data-table tr,
        .standard-data-table th,
        .standard-data-table td {
            display: block;
        }
        .standard-data-table thead {
            display: none;
        }
        .standard-data-table tbody tr {
            margin-bottom: 0.8rem;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 0.85rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }
        .standard-data-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.35rem 0;
            border: 0;
        }
        .standard-data-table td::before {
            content: attr(data-label);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--secondary-text);
            flex: 0 0 42%;
        }
        .standard-data-table td > * {
            flex: 1;
            min-width: 0;
        }
        .timeline-table-footer {
            flex-direction: column;
            align-items: flex-start;
        }
        .pagination-bar {
            width: 100%;
            overflow-x: auto;
        }
    }


    /* ================================================================
       FINAL CLEAN MOBILE TIMELINE UI
       One mobile system only. This removes the old table-card behavior
       that caused the green strip / missing title issue.
    ================================================================ */
    .mobile-gantt-view,
    .mobile-timeline-list {
        display: none;
    }

    .desktop-gantt-view {
        display: block;
    }

    @media (max-width: 720px) {
        #pg-timeline {
            padding: 0 0 1.4rem !important;
            overflow-x: hidden !important;
        }

        #pg-timeline .page-header-card,
        #pg-timeline .timeline-card-header-copy,
        #pg-timeline .timeline-side-panel {
            display: none !important;
        }

        #pg-timeline .timeline-layout {
            display: block !important;
        }

        #pg-timeline .top-toolbar {
            padding: 12px !important;
            border-radius: 18px !important;
            margin-bottom: 12px !important;
        }

        #pg-timeline .toolbar-input,
        #pg-timeline .toolbar-select {
            min-height: 44px !important;
            font-size: 16px !important;
            touch-action: manipulation !important;
        }

        #pg-timeline .toolbar-actions {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 10px !important;
        }

        #pg-timeline .toolbar-actions .btn-ghost,
        #pg-timeline .toolbar-actions .btn-primary {
            width: 100% !important;
            min-height: 44px !important;
            border-radius: 14px !important;
            justify-content: center !important;
        }

        #pg-timeline .timeline-card {
            padding: 10px !important;
            border-radius: 20px !important;
            background: #ffffff !important;
            border: 1px solid #e4ece6 !important;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.055) !important;
        }

        #pg-timeline .timeline-card-header {
            margin-bottom: 12px !important;
            gap: 10px !important;
        }

        #pg-timeline .gantt-toolbar-actions {
            width: 100% !important;
            display: grid !important;
            gap: 10px !important;
            justify-content: stretch !important;
            overflow: visible !important;
        }

        #pg-timeline .gantt-mode-actions {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 8px !important;
        }

        #pg-timeline .gantt-mode-actions .btn-ghost,
        #pg-timeline .gantt-mode-actions .toolbar-select.compact-select {
            width: 100% !important;
            min-width: 0 !important;
            height: 40px !important;
            padding: 0 8px !important;
            border-radius: 12px !important;
            font-size: 12px !important;
            text-align: center !important;
            touch-action: manipulation !important;
        }

        #pg-timeline .view-toggle-group {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 5px !important;
            padding: 5px !important;
            border-radius: 999px !important;
            background: #f2f6f3 !important;
        }

        #pg-timeline .view-toggle-btn {
            min-height: 42px !important;
            padding: 0 6px !important;
            border-radius: 999px !important;
            font-size: 12px !important;
            line-height: 1.15 !important;
            touch-action: manipulation !important;
        }

        #pg-timeline .desktop-gantt-view {
            display: none !important;
        }

        #pg-timeline .mobile-gantt-view {
            display: grid !important;
            gap: 12px !important;
            width: 100% !important;
        }

        #pg-timeline .mobile-gantt-note {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 10px 12px !important;
            border: 1px solid #dbeadd !important;
            border-radius: 14px !important;
            background: #f7fcf8 !important;
            color: #365233 !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            line-height: 1.35 !important;
        }

        #pg-timeline .mobile-gantt-card {
            padding: 14px !important;
            border: 1px solid #e2ebe4 !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.045) !important;
        }

        #pg-timeline .mobile-gantt-head {
            display: flex !important;
            align-items: flex-start !important;
            justify-content: space-between !important;
            gap: 12px !important;
            margin-bottom: 12px !important;
            padding-bottom: 12px !important;
            border-bottom: 1px solid #eef4ef !important;
        }

        #pg-timeline .mobile-gantt-title {
            margin: 0 !important;
            color: #10271b !important;
            font-size: 15px !important;
            font-weight: 800 !important;
            line-height: 1.35 !important;
        }

        #pg-timeline .mobile-gantt-code {
            display: inline-flex !important;
            margin-top: 5px !important;
            padding: 4px 8px !important;
            border: 1px solid #dcebe0 !important;
            border-radius: 999px !important;
            background: #f4faf5 !important;
            color: #64748b !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            letter-spacing: 0.04em !important;
        }

        #pg-timeline .mobile-gantt-meta {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 10px !important;
            margin-bottom: 12px !important;
        }

        #pg-timeline .mobile-gantt-field,
        #pg-timeline .timeline-mobile-detail {
            min-width: 0 !important;
            padding: 10px 11px !important;
            border: 1px solid #edf3ee !important;
            border-radius: 14px !important;
            background: #fbfdfb !important;
        }

        #pg-timeline .mobile-gantt-label,
        #pg-timeline .timeline-mobile-label {
            display: block !important;
            margin-bottom: 5px !important;
            color: #66768a !important;
            font-size: 9px !important;
            font-weight: 800 !important;
            letter-spacing: 0.08em !important;
            line-height: 1.15 !important;
            text-transform: uppercase !important;
        }

        #pg-timeline .mobile-gantt-value,
        #pg-timeline .timeline-mobile-value {
            display: block !important;
            min-width: 0 !important;
            color: #0f172a !important;
            font-size: 12.5px !important;
            font-weight: 700 !important;
            line-height: 1.35 !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        #pg-timeline .mobile-gantt-progress-row {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        #pg-timeline .mobile-gantt-track {
            height: 10px !important;
            flex: 1 1 auto !important;
            overflow: hidden !important;
            border-radius: 999px !important;
            background: #edf3ee !important;
            position: relative !important;
            display: block !important;
        }

        #pg-timeline .mobile-gantt-fill {
            display: block !important;
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
            width: var(--bar-width, 0%) !important;
            max-width: 100% !important;
            min-width: 0 !important;
            height: 100% !important;
            border-radius: 999px !important;
            background: #365233 !important;
            transition: width 0.4s ease !important;
        }

        #pg-timeline .mobile-gantt-fill.status-completed { background: #166534 !important; }
        #pg-timeline .mobile-gantt-fill.status-in-progress { background: #1565C0 !important; }
        #pg-timeline .mobile-gantt-fill.status-upcoming { background: #B7791F !important; }
        #pg-timeline .mobile-gantt-fill.status-delayed { background: #C62828 !important; }
        #pg-timeline .mobile-gantt-fill.status-pending { background: #6B7280 !important; }

        #pg-timeline .mobile-gantt-percent {
            min-width: 42px !important;
            color: #10271b !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            text-align: right !important;
        }

        #pg-timeline .table-wrapper {
            overflow: visible !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
        }

        #pg-timeline .table-wrapper > .standard-data-table {
            display: none !important;
        }

        #pg-timeline .mobile-timeline-list {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 12px !important;
            width: 100% !important;
        }

        #pg-timeline .timeline-mobile-card {
            width: 100% !important;
            padding: 14px !important;
            border: 1px solid #e1ebe4 !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.045) !important;
            overflow: hidden !important;
        }

        #pg-timeline .timeline-mobile-head {
            display: grid !important;
            grid-template-columns: 30px minmax(0, 1fr) auto !important;
            gap: 10px !important;
            align-items: start !important;
            padding-bottom: 12px !important;
            margin-bottom: 10px !important;
            border-bottom: 1px solid #edf3ee !important;
        }

        #pg-timeline .timeline-mobile-index {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 28px !important;
            height: 28px !important;
            border-radius: 999px !important;
            border: 1px solid #dcebe0 !important;
            background: #f4faf5 !important;
            color: #365233 !important;
            font-size: 12px !important;
            font-weight: 800 !important;
            line-height: 1 !important;
        }

        #pg-timeline .timeline-mobile-title {
            min-width: 0 !important;
        }

        #pg-timeline .timeline-mobile-title h4 {
            margin: 0 !important;
            color: #10271b !important;
            font-size: 14px !important;
            font-weight: 800 !important;
            line-height: 1.35 !important;
            letter-spacing: -0.01em !important;
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        #pg-timeline .timeline-mobile-code {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 42px !important;
            max-width: 88px !important;
            padding: 5px 9px !important;
            border: 1px solid #dcebe0 !important;
            border-radius: 999px !important;
            background: #f4faf5 !important;
            color: #60708a !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            letter-spacing: 0.04em !important;
            white-space: nowrap !important;
        }

        #pg-timeline .timeline-mobile-detail-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 9px !important;
            width: 100% !important;
        }

        #pg-timeline .timeline-mobile-detail {
            min-height: 62px !important;
        }

        #pg-timeline .timeline-mobile-detail.full {
            grid-column: 1 / -1 !important;
        }

        #pg-timeline .timeline-mobile-detail.progress .timeline-mobile-value {
            color: #10271b !important;
            font-size: 16px !important;
            font-weight: 800 !important;
        }

        #pg-timeline .timeline-mobile-progress-track {
            width: 100% !important;
            height: 8px !important;
            display: block !important;
            background: #edf3ee !important;
            border-radius: 999px !important;
            overflow: hidden !important;
            margin-top: 6px !important;
            position: relative !important;
        }

        #pg-timeline .timeline-mobile-progress-fill {
            display: block !important;
            position: relative !important;
            width: var(--bar-width, 0%) !important;
            max-width: 100% !important;
            min-width: 0 !important;
            height: 100% !important;
            background: #365233 !important;
            border-radius: 999px !important;
            transition: width 0.4s ease !important;
        }

        #pg-timeline .timeline-mobile-detail.status,
        #pg-timeline .timeline-mobile-detail.progress {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
        }

        #pg-timeline .timeline-mobile-actions {
            grid-column: 1 / -1 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
            min-height: 48px !important;
        }

        #pg-timeline .timeline-mobile-actions .btn-icon-table {
            margin-left: auto !important;
            width: 38px !important;
            height: 38px !important;
            border-radius: 12px !important;
            flex: 0 0 auto !important;
        }

        #pg-timeline .status-pill-badge {
            max-width: 100% !important;
            padding: 6px 12px !important;
            border-radius: 999px !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            line-height: 1.2 !important;
            text-align: center !important;
            text-transform: capitalize !important;
        }

        #pg-timeline .timeline-table-footer {
            margin-top: 12px !important;
            padding: 0 !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 10px !important;
        }

        #pg-timeline .pagination-bar {
            width: 100% !important;
            overflow-x: auto !important;
            padding-bottom: 2px !important;
        }
    }

</style>
@endpush

@push('scripts')
    @vite(['resources/js/admin-timeline.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div id="pg-timeline">

    <div class="top-toolbar">
        <div class="toolbar-group search-group">
            <label for="searchMilestones">Search</label>
            <input id="searchMilestones" class="toolbar-input" type="search" placeholder="Search milestone or phase">
        </div>

        <div class="toolbar-group">
            <label for="projectSelector">Project</label>
            <select id="projectSelector" class="toolbar-select">
                @foreach($projectsWithStats as $project)
                    <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="toolbar-group">
            <label for="phaseFilterSelector">Construction Phase</label>
            <select id="phaseFilterSelector" class="toolbar-select">
                <option value="all">All phases</option>
            </select>
        </div>

        <div class="toolbar-group">
            <label for="statusFilterSelector">Status</label>
            <select id="statusFilterSelector" class="toolbar-select">
                <option value="all">All statuses</option>
                <option value="completed">Completed</option>
                <option value="in-progress">In Progress</option>
                <option value="upcoming">Upcoming</option>
                <option value="delayed">Delayed</option>
                <option value="pending">Pending</option>
            </select>
        </div>

        <div class="toolbar-actions">
            <button id="addMilestoneBtn" type="button" class="btn-primary"><i class="bi bi-plus-lg"></i> Add Milestone</button>
        </div>
    </div>

    <div id="timelineContainer"></div>
</div>

<div id="milestoneModalBackdrop" class="milestone-modal-backdrop d-none" role="presentation">
    <div class="milestone-modal-card" role="dialog" aria-modal="true" aria-labelledby="milestoneModalTitle">
        <div class="milestone-modal-header">
            <div>
                <p class="milestone-modal-eyebrow">Project Milestone</p>
                <h3 id="milestoneModalTitle" class="milestone-modal-title">Create Milestone</h3>
                <p id="milestoneModalSubtitle" class="milestone-modal-subtitle">Capture a new milestone for the selected construction phase.</p>
            </div>
            <button type="button" class="milestone-modal-close" data-close-milestone-modal aria-label="Close modal"><i class="bi bi-x-lg"></i></button>
        </div>

        <form id="milestoneModalForm" class="milestone-modal-form" data-mode="create" novalidate>
            <input type="hidden" name="project_id" id="milestoneProjectId">
            <input type="hidden" name="milestone_id" id="milestoneId">
            <input type="hidden" name="original_phase_id" id="milestoneOriginalPhaseId">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="milestone-modal-section">
                <div class="milestone-modal-section-title">General Information</div>
                <p class="milestone-modal-section-copy">Enter the milestone details for this phase.</p>

                <div class="milestone-modal-grid">
                    <div class="milestone-modal-field">
                        <label for="milestonePhaseId">Construction Phase <span class="milestone-modal-required">Required</span></label>
                        <select id="milestonePhaseId" name="phase_id" required></select>
                    </div>

                    <div class="milestone-modal-field">
                        <label for="milestoneName">Milestone Name <span class="milestone-modal-required">Required</span></label>
                        <input id="milestoneName" name="milestone_name" type="text" placeholder="Enter milestone name" required>
                    </div>
                </div>

                <div id="milestonePhaseInfo" class="milestone-modal-field d-none" style="margin-top: 0.75rem;">
                    <label class="text-muted small">Selected Phase Schedule</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="badge bg-light text-dark border">
                            <i class="bi bi-calendar3 me-1"></i>
                            <span id="milestonePhaseStart">--</span>
                        </div>
                        <div class="badge bg-light text-dark border">
                            <i class="bi bi-calendar-check me-1"></i>
                            <span id="milestonePhaseEnd">--</span>
                        </div>
                        <div class="badge bg-light text-dark border">
                            <i class="bi bi-flag me-1"></i>
                            <span id="milestonePhaseStatus">--</span>
                        </div>
                    </div>
                </div>

                <div id="milestoneExistingSelectorWrap" class="milestone-modal-field d-none" style="margin-top: 1rem;">
                    <label for="milestoneExistingSelector">Milestone to Edit</label>
                    <select id="milestoneExistingSelector"></select>
                </div>
            </div>

            <div class="milestone-modal-section">
                <div class="milestone-modal-section-title">Schedule</div>
                <p class="milestone-modal-section-copy">Set the milestone dates.</p>

                <div class="milestone-modal-grid">
                    <div class="milestone-modal-field">
                        <label for="milestoneStartDate">Start Date <span class="milestone-modal-required">Required</span></label>
                        <input id="milestoneStartDate" name="start_date" type="date" required>
                    </div>

                    <div class="milestone-modal-field">
                        <label for="milestoneEndDate">End Date</label>
                        <input id="milestoneEndDate" name="end_date" type="date" required>
                    </div>
                </div>
            </div>

            <input type="hidden" id="milestoneCompleted" name="is_completed" value="0">
            <input type="hidden" id="milestoneDelayed" name="is_delayed" value="0">

            <div class="milestone-modal-actions">
                <button type="button" class="btn-ghost" data-close-milestone-modal>Cancel</button>
                <button id="milestoneSubmitBtn" type="submit" class="btn-primary"> Save Milestone</button>
            </div>
        </form>
    </div>
</div>

<script>
    const projectsData = @json($projectsWithStats);
</script>

<script>
    let selectedProject = null;
    let activeTimelineFilter = 'all';
    let timelineViewMode = 'gantt';
    let activeTimelineScale = 'week';
    const tablePageSize = 10;
    let currentPhasePage = 1;
    let currentTimelinePage = 1;

    function resetTablePagination() {
        currentPhasePage = 1;
        currentTimelinePage = 1;
    }

    function getPaginatedItems(items, page) {
        const start = (page - 1) * tablePageSize;
        return Array.isArray(items) ? items.slice(start, start + tablePageSize) : [];
    }

    function buildTablePagination(totalItems, currentPage, tableKey) {
        const totalPages = Math.max(1, Math.ceil(totalItems / tablePageSize));
        if (totalPages <= 1) {
            return '';
        }

        const previousPage = Math.max(1, currentPage - 1);
        const nextPage = Math.min(totalPages, currentPage + 1);
        const buttons = [
            `<button type="button" class="pagination-button" data-table="${tableKey}" data-page="${previousPage}" ${currentPage === 1 ? 'disabled' : ''} aria-label="Previous page">‹</button>`
        ];

        for (let page = 1; page <= totalPages; page += 1) {
            buttons.push(`
                <button type="button" class="pagination-button ${page === currentPage ? 'active' : ''}" data-table="${tableKey}" data-page="${page}" aria-label="Page ${page}">${page}</button>
            `);
        }

        buttons.push(`<button type="button" class="pagination-button" data-table="${tableKey}" data-page="${nextPage}" ${currentPage === totalPages ? 'disabled' : ''} aria-label="Next page">›</button>`);
        return buttons.join('');
    }

    function attachPaginationControls() {
        document.querySelectorAll('.pagination-button').forEach((button) => {
            button.addEventListener('click', function () {
                const targetTable = this.dataset.table;
                const page = Number(this.dataset.page) || 1;
                if (targetTable === 'phases') {
                    currentPhasePage = page;
                } else if (targetTable === 'timeline') {
                    currentTimelinePage = page;
                }
                if (selectedProject) {
                    renderTimeline(selectedProject);
                }
            });
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function clampPercentage(value) {
        const percentage = Number(value);
        if (!Number.isFinite(percentage)) return 0;
        if (percentage <= 1) {
            return Math.min(100, Math.max(0, percentage * 100));
        }
        return Math.min(100, Math.max(0, percentage));
    }

    function normalizeStatus(status) {
        const norm = String(status || 'pending').trim().toLowerCase().replace(/_/g, '-').replace(/\s+/g, '-');
        if (norm === 'completed') return 'completed';
        if (norm === 'in-progress' || norm === 'ongoing' || norm === 'in_progress') return 'in-progress';
        if (norm === 'delayed' || norm === 'overdue') return 'delayed';
        if (norm === 'pending' || norm === 'not-started' || norm === 'not_started') return 'pending';
        return 'upcoming';
    }

    function formatDateFull(dateValue) {
        if (!dateValue) return 'Not set';
        const date = new Date(dateValue);
        if (Number.isNaN(date.getTime())) return 'Not set';
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function toDateInputValue(value) {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return String(value).slice(0, 10);
        return date.toISOString().slice(0, 10);
    }

    function getProjectPhases(project) {
        return Array.isArray(project?.phases) ? project.phases : [];
    }

    function getFilteredPhases(project) {
        const phases = getProjectPhases(project);
        const searchValue = (document.getElementById('searchMilestones')?.value || '').trim().toLowerCase();
        const phaseFilter = document.getElementById('phaseFilterSelector')?.value || 'all';
        const statusFilter = document.getElementById('statusFilterSelector')?.value || 'all';

        return phases.filter((phase) => {
            const status = normalizeStatus(phase.display_status ?? phase.status ?? 'planning');
            if (activeTimelineFilter !== 'all' && status !== activeTimelineFilter) return false;
            if (statusFilter !== 'all' && status !== statusFilter) return false;
            if (phaseFilter !== 'all') {
                const phaseCode = String(phase.phase_code || phase.code || '').toLowerCase();
                const phaseName = String(phase.phase_name || phase.name || '').toLowerCase();
                if (phaseCode !== phaseFilter.toLowerCase() && phaseName !== phaseFilter.toLowerCase()) return false;
            }
            if (searchValue) {
                const haystack = `${phase.phase_name || phase.name || ''} ${phase.phase_code || phase.code || ''} ${phase.description || ''}`.toLowerCase();
                if (!haystack.includes(searchValue)) return false;
            }
            return true;
        });
    }

    function getMilestoneStatus(milestone) {
        if (milestone?.is_completed) return 'completed';
        if (milestone?.is_delayed) return 'delayed';
        return 'upcoming';
    }

    function getFilteredMilestones(project) {
        const phases = getProjectPhases(project);
        const searchValue = (document.getElementById('searchMilestones')?.value || '').trim().toLowerCase();
        const phaseFilter = document.getElementById('phaseFilterSelector')?.value || 'all';
        const statusFilter = document.getElementById('statusFilterSelector')?.value || 'all';

        const milestones = phases.flatMap((phase) => {
            const phaseName = phase.phase_name || phase.name || 'Unnamed Phase';
            const phaseCode = phase.phase_code || phase.code || '';
            const phaseMilestones = Array.isArray(phase?.milestones) ? phase.milestones : [];

            return phaseMilestones.map((milestone) => ({
                ...milestone,
                phase_name: phaseName,
                phase_code: phaseCode,
                phase_id: phase.phase_id ?? phase.id,
            }));
        });

        return milestones.filter((milestone) => {
            const status = getMilestoneStatus(milestone);
            if (activeTimelineFilter !== 'all' && status !== activeTimelineFilter) return false;
            if (statusFilter !== 'all' && status !== statusFilter) return false;
            if (phaseFilter !== 'all') {
                const phaseCode = String(milestone.phase_code || '').toLowerCase();
                const phaseName = String(milestone.phase_name || '').toLowerCase();
                if (phaseCode !== phaseFilter.toLowerCase() && phaseName !== phaseFilter.toLowerCase()) return false;
            }
            if (searchValue) {
                const haystack = `${milestone.milestone_name || ''} ${milestone.phase_name || ''} ${milestone.phase_code || ''}`.toLowerCase();
                if (!haystack.includes(searchValue)) return false;
            }
            return true;
        });
    }

    function selectProject(projectId) {
        selectedProject = projectsData.find((project) => String(project.id) === String(projectId)) || projectsData[0] || null;
        if (!selectedProject) {
            renderEmptyState();
            return;
        }

        resetTablePagination();
        document.getElementById('projectSelector').value = String(selectedProject.id);
        populatePhaseFilter(selectedProject);
        renderTimeline(selectedProject);
    }

    function populatePhaseFilter(project) {
        const filter = document.getElementById('phaseFilterSelector');
        if (!filter) return;

        const phases = getProjectPhases(project);
        const phaseOptions = Array.from(new Set(phases.map((phase) => phase.phase_code || phase.code || phase.phase_name || phase.name || '').filter(Boolean))).sort();
        const currentValue = filter.value;
        filter.innerHTML = ['<option value="all">All phases</option>', ...phaseOptions.map((value) => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`)].join('');
        if (phaseOptions.includes(currentValue)) {
            filter.value = currentValue;
        }
    }

    function buildGanttTasks(phases) {
        return phases.map((phase, index) => {
            const status = normalizeStatus(phase.display_status ?? phase.status ?? 'planning');
            const start = toDateInputValue(phase.planned_start_date || phase.start || phase.start_date || phase.begin) || '';
            let end = toDateInputValue(phase.planned_end_date || phase.end || phase.end_date || phase.targetEndDate) || '';
            if (!end && start) end = start;
            const milestones = Array.isArray(phase.milestones) ? phase.milestones.map((milestone, milestoneIndex) => ({
                ...milestone,
                milestone_name: milestone.milestone_name || milestone.name || `Milestone ${milestoneIndex + 1}`,
                start_date: milestone.start_date || milestone.planned_date || milestone.start || '',
                end_date: milestone.end_date || milestone.actual_date || milestone.end || '',
            })) : [];
            return {
                id: String(phase.phase_id ?? phase.id ?? `${phase.project_id ?? 'project'}-${index}`),
                text: phase.phase_name || phase.name || 'Milestone',
                start_date: start,
                end_date: end,
                progress: clampPercentage(phase.completion_percentage ?? phase.progress ?? 0) / 100,
                custom_class: status,
                type: (!end || end === start) ? 'milestone' : 'task',
                parent: phase.parent_id ? String(phase.parent_id) : 0,
                open: true,
                color: status === 'completed' ? '#166534' : status === 'in-progress' ? '#1565C0' : status === 'delayed' ? '#C62828' : status === 'pending' ? '#6B7280' : '#B7791F',
                milestones,
                phase_name: phase.phase_name || phase.name || 'Phase',
                phase_code: phase.phase_code || phase.code || ''
            };
        }).filter((task) => task.start_date);
    }

    function initDhtmlxGantt(tasks, project) {
        if (window.initDhtmlxGantt) {
            window.initDhtmlxGantt(tasks, project);
            if (window.setDhtmlxScale) {
                window.setDhtmlxScale(activeTimelineScale);
            }
            return;
        }
        if (window.refreshDhtmlxGantt) {
            window.refreshDhtmlxGantt(tasks);
            if (window.setDhtmlxScale) {
                window.setDhtmlxScale(activeTimelineScale);
            }
            return;
        }
        window.initialGanttTasks = tasks;
    }



    function buildMobileGanttChart(phases) {
        const items = Array.isArray(phases) ? phases : [];

        if (!items.length) {
            return '<div class="timeline-empty-state">No Gantt data available for the selected filters.</div>';
        }

        const cards = items.map((phase) => {
            const status = normalizeStatus(phase.display_status ?? phase.status ?? 'planning');
            const percentage = clampPercentage(phase.completion_percentage ?? phase.progress ?? 0);
            const phaseName = phase.phase_name || phase.name || 'Unnamed phase';
            const phaseCode = phase.phase_code || phase.code || 'Phase';
            const start = formatDateFull(phase.planned_start_date || phase.start || phase.start_date || phase.begin);
            const end = formatDateFull(phase.planned_end_date || phase.end || phase.end_date || phase.targetEndDate);
            const statusLabel = status.replace('-', ' ');

            return `
                <article class="mobile-gantt-card">
                    <div class="mobile-gantt-head">
                        <div>
                            <h4 class="mobile-gantt-title">${escapeHtml(phaseName)}</h4>
                            <span class="mobile-gantt-code">${escapeHtml(phaseCode)}</span>
                        </div>
                        <span class="status-pill-badge ${status}">${escapeHtml(statusLabel)}</span>
                    </div>
                    <div class="mobile-gantt-meta">
                        <div class="mobile-gantt-field">
                            <span class="mobile-gantt-label">Start</span>
                            <span class="mobile-gantt-value">${escapeHtml(start)}</span>
                        </div>
                        <div class="mobile-gantt-field">
                            <span class="mobile-gantt-label">End</span>
                            <span class="mobile-gantt-value">${escapeHtml(end)}</span>
                        </div>
                    </div>
                    <div class="mobile-gantt-progress-row">
                        <div class="mobile-gantt-track"><span class="mobile-gantt-fill status-${status}" style="--bar-width:${Math.max(0, Math.min(100, percentage))}%; width:${Math.max(0, Math.min(100, percentage))}%;"></span></div>
                        <span class="mobile-gantt-percent">${Math.round(percentage)}%</span>
                    </div>
                </article>
            `;
        }).join('');

        return `
            <div class="mobile-gantt-note"><i class="bi bi-bar-chart-line-fill"></i> Mobile Gantt summary. Use the Phases or Timeline tab for detailed rows.</div>
            ${cards}
        `;
    }


    function buildMobilePhaseCards(phases, startIndex) {
        const items = Array.isArray(phases) ? phases : [];
        if (!items.length) {
            return '<div class="timeline-empty-state">No phases match the current filters.</div>';
        }

        return items.map((phase, index) => {
            const status = normalizeStatus(phase.display_status ?? phase.status ?? 'planning');
            const percentage = clampPercentage(phase.completion_percentage ?? phase.progress ?? 0);
            const phaseName = phase.phase_name || phase.name || 'Unnamed phase';
            const phaseCode = phase.phase_code || phase.code || 'Phase';
            const start = formatDateFull(phase.planned_start_date || phase.start);
            const end = formatDateFull(phase.planned_end_date || phase.end);
            const number = startIndex + index;

            return `
                <article class="timeline-mobile-card">
                    <div class="timeline-mobile-head">
                        <span class="timeline-mobile-index">${number}</span>
                        <div class="timeline-mobile-title">
                            <h4>${escapeHtml(phaseName)}</h4>
                        </div>
                        <span class="timeline-mobile-code">${escapeHtml(phaseCode)}</span>
                    </div>
                    <div class="timeline-mobile-detail-grid">
                        <div class="timeline-mobile-detail">
                            <span class="timeline-mobile-label">Start</span>
                            <span class="timeline-mobile-value">${escapeHtml(start)}</span>
                        </div>
                        <div class="timeline-mobile-detail">
                            <span class="timeline-mobile-label">End</span>
                            <span class="timeline-mobile-value">${escapeHtml(end)}</span>
                        </div>
                        <div class="timeline-mobile-detail status">
                            <span class="timeline-mobile-label">Status</span>
                            <span class="status-pill-badge ${status}">${escapeHtml(status.replace('-', ' '))}</span>
                        </div>
                        <div class="timeline-mobile-detail progress">
                            <span class="timeline-mobile-label">Progress</span>
                            <div class="timeline-mobile-progress-track">
                                <span class="timeline-mobile-progress-fill" style="--bar-width:${Math.max(0, Math.min(100, percentage))}%; width:${Math.max(0, Math.min(100, percentage))}%;"></span>
                            </div>
                            <span class="timeline-mobile-value">${Math.round(percentage)}%</span>
                        </div>
                    </div>
                </article>
            `;
        }).join('');
    }

    function buildMobileMilestoneCards(milestones, startIndex) {
        const items = Array.isArray(milestones) ? milestones : [];
        if (!items.length) {
            return '<div class="timeline-empty-state">No milestones match the current filters.</div>';
        }

        return items.map((milestone, index) => {
            const status = getMilestoneStatus(milestone);
            const number = startIndex + index;
            const milestoneName = milestone.milestone_name || 'Unnamed milestone';
            const phaseName = milestone.phase_name || 'Unnamed phase';
            const start = formatDateFull(milestone.planned_start_date || milestone.start_date || milestone.start);
            const end = formatDateFull(milestone.planned_end_date || milestone.end_date || milestone.end);

            return `
                <article class="timeline-mobile-card">
                    <div class="timeline-mobile-head">
                        <span class="timeline-mobile-index">${number}</span>
                        <div class="timeline-mobile-title">
                            <h4>${escapeHtml(milestoneName)}</h4>
                        </div>
                        <span class="status-pill-badge ${status}">${escapeHtml(status.replace('-', ' '))}</span>
                    </div>
                    <div class="timeline-mobile-detail-grid">
                        <div class="timeline-mobile-detail full">
                            <span class="timeline-mobile-label">Phase</span>
                            <span class="timeline-mobile-value">${escapeHtml(phaseName)}</span>
                        </div>
                        <div class="timeline-mobile-detail">
                            <span class="timeline-mobile-label">Start Planned Date</span>
                            <span class="timeline-mobile-value">${escapeHtml(start)}</span>
                        </div>
                        <div class="timeline-mobile-detail">
                            <span class="timeline-mobile-label">End Planned Date</span>
                            <span class="timeline-mobile-value">${escapeHtml(end)}</span>
                        </div>
                        <div class="timeline-mobile-detail timeline-mobile-actions">
                            <span>
                                <span class="timeline-mobile-label">Action</span>
                                <span class="timeline-mobile-value">Edit milestone</span>
                            </span>
                            <button type="button" class="btn-icon-table" data-open-edit="${milestone.phase_id ?? ''}" data-milestone-id="${milestone.milestone_id ?? milestone.id ?? ''}" aria-label="Edit milestone"><i class="bi bi-pencil"></i></button>
                        </div>
                    </div>
                </article>
            `;
        }).join('');
    }

    function renderTimeline(project) {
        const filteredPhases = getFilteredPhases(project);
        const filteredMilestones = getFilteredMilestones(project);
        const completedCount = filteredPhases.filter((phase) => normalizeStatus(phase.display_status ?? phase.status ?? 'planning') === 'completed').length;
        const inProgressCount = filteredPhases.filter((phase) => normalizeStatus(phase.display_status ?? phase.status ?? 'planning') === 'in-progress').length;
        const upcomingCount = filteredPhases.filter((phase) => normalizeStatus(phase.display_status ?? phase.status ?? 'planning') === 'upcoming').length;
        const delayedCount = filteredPhases.filter((phase) => normalizeStatus(phase.display_status ?? phase.status ?? 'planning') === 'delayed').length;
        const pendingCount = filteredPhases.filter((phase) => normalizeStatus(phase.display_status ?? phase.status ?? 'planning') === 'pending').length;
        const projectProgress = clampPercentage(project.progress ?? 0);

        const statusSummary = [
            { key: 'completed', label: 'Completed', count: completedCount, color: '#E8F5E9', textColor: '#2E7D32' },
            { key: 'in-progress', label: 'In Progress', count: inProgressCount, color: '#E3F2FD', textColor: '#1565C0' },
            { key: 'upcoming', label: 'Upcoming', count: upcomingCount, color: '#FFF8E1', textColor: '#B7791F' },
            { key: 'delayed', label: 'Delayed', count: delayedCount, color: '#FDECEC', textColor: '#C62828' },
            { key: 'pending', label: 'Pending', count: pendingCount, color: '#F3F4F6', textColor: '#6B7280' }
        ];

        const paginatedPhases = getPaginatedItems(filteredPhases, currentPhasePage);
        const paginatedMilestones = getPaginatedItems(filteredMilestones, currentTimelinePage);
        const phasesTotal = filteredPhases.length;
        const timelineTotal = filteredMilestones.length;
        const phasesFrom = phasesTotal === 0 ? 0 : ((currentPhasePage - 1) * tablePageSize) + 1;
        const phasesTo = Math.min(phasesTotal, currentPhasePage * tablePageSize);
        const timelineFrom = timelineTotal === 0 ? 0 : ((currentTimelinePage - 1) * tablePageSize) + 1;
        const timelineTo = Math.min(timelineTotal, currentTimelinePage * tablePageSize);


        const htmlOutput = `
            <div class="timeline-layout">
                <div class="timeline-main-panel">
                    <div class="timeline-card">
                        <div class="timeline-card-header">
                            <div class="timeline-card-header-copy">
                                <h3 class="panel-title">${escapeHtml(project.name || 'Project Schedule')}</h3>
                                <p class="panel-subtitle">Live milestone planning with year, quarter, month, week, and day scale views.</p>
                            </div>
                            <div class="gantt-toolbar-actions">
                                ${timelineViewMode === 'gantt' ? `
                                    <div class="gantt-mode-actions gantt-controls-enter">
                                        <button type="button" id="timelineZoomInBtn" class="btn-ghost"><i class="bi bi-plus"></i></button>
                                        <button type="button" id="timelineZoomOutBtn" class="btn-ghost"><i class="bi bi-dash"></i></button>
                                        <button type="button" id="timelineTodayBtn" class="btn-ghost"><i class="bi bi-calendar2-week"></i> Today</button>
                                        <select id="timelineScaleSelector" class="toolbar-select compact-select">
                                            <option value="day" ${activeTimelineScale === 'day' ? 'selected' : ''}>Day</option>
                                            <option value="week" ${activeTimelineScale === 'week' ? 'selected' : ''}>Week</option>
                                            <option value="month" ${activeTimelineScale === 'month' ? 'selected' : ''}>Month</option>
                                            <option value="quarter" ${activeTimelineScale === 'quarter' ? 'selected' : ''}>Quarter</option>
                                            <option value="year" ${activeTimelineScale === 'year' ? 'selected' : ''}>Year</option>
                                        </select>
                                    </div>
                                ` : ''}
                                <div class="view-toggle-group">
                                    <button type="button" class="view-toggle-btn ${timelineViewMode === 'gantt' ? 'active' : ''}" data-view="gantt"><i class="bi bi-bar-chart-line-fill"></i> Gantt</button>
                                    <button type="button" class="view-toggle-btn ${timelineViewMode === 'phases' ? 'active' : ''}" data-view="phases"><i class="bi bi-diagram-3"></i> Phases</button>
                                    <button type="button" class="view-toggle-btn ${timelineViewMode === 'timeline' ? 'active' : ''}" data-view="timeline"><i class="bi bi-signpost-split"></i> Milestones</button>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-view-panel ${timelineViewMode === 'gantt' ? '' : 'd-none'}">
                            <div class="mobile-gantt-view">
                                ${buildMobileGanttChart(filteredPhases)}
                            </div>
                            <div class="desktop-gantt-view gantt-shell">
                                <div class="gantt-scroll-shell">
                                    <div id="dhtmlxGantt"></div>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-view-panel ${timelineViewMode === 'phases' ? '' : 'd-none'}">
                            <div class="table-wrapper">
                                <div class="mobile-timeline-list">
                                    ${buildMobilePhaseCards(paginatedPhases, phasesFrom)}
                                </div>
                                <table class="standard-data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Phase</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${paginatedPhases.length ? paginatedPhases.map((phase, index) => {
                                            const status = normalizeStatus(phase.display_status ?? phase.status ?? 'planning');
                                            const percentage = clampPercentage(phase.completion_percentage ?? phase.progress ?? 0);
                                            const phaseName = phase.phase_name || phase.name || 'Unnamed phase';
                                            const phaseCode = phase.phase_code || phase.code || 'Phase';
                                            return `
                                                <tr class="timeline-phase-card">
                                                    <td data-label="#">${phasesFrom + index}</td>
                                                    <td data-label="Phase" class="timeline-title-cell">
                                                        <div class="timeline-title-row">
                                                            <span class="timeline-number-chip">${phasesFrom + index}</span>
                                                            <div class="timeline-title-copy">
                                                                <strong>${escapeHtml(phaseName)}</strong>
                                                                <span class="timeline-code-chip">${escapeHtml(phaseCode)}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td data-label="Start">${formatDateFull(phase.planned_start_date || phase.start)}</td>
                                                    <td data-label="End">${formatDateFull(phase.planned_end_date || phase.end)}</td>
                                                    <td data-label="Status"><span class="status-pill-badge ${status}">${status.replace('-', ' ')}</span></td>
                                                    <td data-label="Progress">${Math.round(percentage)}%</td>
                                                </tr>
                                            `;
                                        }).join('') : `<tr><td colspan="7"><div class="timeline-empty-state">No phases match the current filters.</div></td></tr>`}
                                    </tbody>
                                </table>
                            <div class="timeline-table-footer">
                                <div class="pagination-summary">Showing ${phasesFrom} to ${phasesTo} of ${phasesTotal} phase${phasesTotal === 1 ? '' : 's'}</div>
                                <div class="pagination-bar">${buildTablePagination(phasesTotal, currentPhasePage, 'phases')}</div>
                            </div>
                            </div>
                        </div>

                        <div class="timeline-view-panel ${timelineViewMode === 'timeline' ? '' : 'd-none'}">
                            <div class="table-wrapper">
                                <div class="mobile-timeline-list">
                                    ${buildMobileMilestoneCards(paginatedMilestones, timelineFrom)}
                                </div>
                                <table class="standard-data-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Milestone</th>
                                            <th>Phase</th>
                                            <th>Start Planned Date</th>
                                            <th>End Planned Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${paginatedMilestones.length ? paginatedMilestones.map((milestone, index) => {
                                            const status = getMilestoneStatus(milestone);
                                            return `
                                                <tr class="timeline-milestone-card">
                                                    <td data-label="#">${timelineFrom + index}</td>
                                                    <td data-label="Milestone" class="timeline-title-cell">
                                                        <div class="timeline-title-row">
                                                            <span class="timeline-number-chip">${timelineFrom + index}</span>
                                                            <div class="timeline-title-copy">
                                                                <strong>${escapeHtml(milestone.milestone_name || 'Unnamed milestone')}</strong>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td data-label="Phase">${escapeHtml(milestone.phase_name || 'Unnamed phase')}</td>
                                                    <td data-label="Start Planned Date">${formatDateFull(milestone.planned_start_date || milestone.start_date || milestone.start)}</td>
                                                    <td data-label="End Planned Date">${formatDateFull(milestone.planned_end_date || milestone.end_date || milestone.end)}</td>
                                                    <td data-label="Status"><span class="status-pill-badge ${status}">${status.replace('-', ' ')}</span></td>
                                                    <td data-label="Actions">
                                                        <div class="action-icons-flex">
                                                            <button type="button" class="btn-icon-table" data-open-edit="${milestone.phase_id ?? ''}" data-milestone-id="${milestone.milestone_id ?? milestone.id ?? ''}"><i class="bi bi-pencil"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('') : `<tr><td colspan="7"><div class="timeline-empty-state">No milestones match the current filters.</div></td></tr>`}
                                    </tbody>
                                </table>
                            <div class="timeline-table-footer">
                                <div class="pagination-summary">Showing ${timelineFrom} to ${timelineTo} of ${timelineTotal} milestone${timelineTotal === 1 ? '' : 's'}</div>
                                <div class="pagination-bar">${buildTablePagination(timelineTotal, currentTimelinePage, 'timeline')}</div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="timeline-side-panel">
                    <div class="insight-card">
                        <div class="insight-card-title">Overall Project Progress</div>
                        <div class="progress-ring" style="background: conic-gradient(var(--primary-green) ${projectProgress}%, #e2e8f0 ${projectProgress}% 100%);"><span>${Math.round(projectProgress)}%</span></div>
                        <div class="metric-list">
                            <div class="summary-stat-grid">
                                <div class="summary-stat-card">
                                    <div class="summary-stat-icon"><i class="bi bi-flag-fill"></i></div>
                                    <div class="summary-stat-body">
                                        <span class="summary-stat-label">Milestones</span>
                                        <strong>${filteredMilestones.length}</strong>
                                    </div>
                                </div>
                                <div class="summary-stat-card">
                                    <div class="summary-stat-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                                    <div class="summary-stat-body">
                                        <span class="summary-stat-label">Current phase</span>
                                        <strong>${escapeHtml(filteredPhases[0]?.phase_name || filteredPhases[0]?.name || 'Planning')}</strong>
                                    </div>
                                </div>
                                <div class="summary-stat-card">
                                    <div class="summary-stat-icon"><i class="bi bi-activity"></i></div>
                                    <div class="summary-stat-body">
                                        <span class="summary-stat-label">Project status</span>
                                        <strong>${escapeHtml(project.status || 'Active')}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="status-guide-card">
                        <div class="status-guide-title">Milestone Bar Guide</div>
                        <div class="status-guide-list">
                            <div class="status-guide-item"><span class="status-guide-swatch" style="background:#166534;"></span><span class="status-guide-label">Completed</span></div>
                            <div class="status-guide-item"><span class="status-guide-swatch" style="background:#1565C0;"></span><span class="status-guide-label">In Progress</span></div>
                            <div class="status-guide-item"><span class="status-guide-swatch" style="background:#B7791F;"></span><span class="status-guide-label">Upcoming</span></div>
                        </div>
                    </div>
                </aside>
        `;

        document.getElementById('timelineContainer').innerHTML = htmlOutput;
        attachTimelineControls();
        attachPaginationControls();

        document.querySelectorAll('[data-open-view]').forEach((button) => {
            button.addEventListener('click', () => window.openPhaseModal?.(button.getAttribute('data-open-view'), false));
        });
        document.querySelectorAll('[data-open-edit]').forEach((button) => {
            button.addEventListener('click', () => window.openMilestoneEditModal?.(button.getAttribute('data-open-edit'), button.getAttribute('data-milestone-id')));
        });

        const tasks = buildGanttTasks(filteredPhases);
        initDhtmlxGantt(tasks, project);
    }

    function attachTimelineControls() {
        document.querySelectorAll('.view-toggle-btn').forEach((button) => {
            button.addEventListener('click', function () {
                timelineViewMode = this.dataset.view || 'gantt';
                document.querySelectorAll('.view-toggle-btn').forEach((btn) => btn.classList.toggle('active', btn === this));
                if (selectedProject) {
                    renderTimeline(selectedProject);
                }
            });
        });

        document.getElementById('timelineTodayBtn')?.addEventListener('click', () => {
            if (window.gantt && typeof window.gantt.showDate === 'function') {
                window.gantt.showDate(new Date());
            }
        });

        document.getElementById('timelineZoomInBtn')?.addEventListener('click', () => window.setDhtmlxZoom?.('in'));
        document.getElementById('timelineZoomOutBtn')?.addEventListener('click', () => window.setDhtmlxZoom?.('out'));
        
        const scaleSelector = document.getElementById('timelineScaleSelector');
        if (scaleSelector && !scaleSelector.dataset.bound) {
            scaleSelector.dataset.bound = 'true';
            scaleSelector.addEventListener('change', function () {
                const scale = this.value || 'week';
                activeTimelineScale = scale;
                if (window.setDhtmlxScale) {
                    window.setDhtmlxScale(scale);
                }
                if (selectedProject) {
                    renderTimeline(selectedProject);
                }
            });
        }
        
        document.getElementById('exportTimelineBtn')?.addEventListener('click', () => {
            const rows = timelineViewMode === 'timeline'
                ? getFilteredMilestones(selectedProject).map((milestone, index) => {
                    const status = getMilestoneStatus(milestone);
                    return [index + 1, milestone.milestone_name || 'Unnamed milestone', milestone.phase_name || 'Unnamed phase', status, milestone.planned_start_date || milestone.start_date || milestone.start || '', milestone.planned_end_date || milestone.end_date || milestone.end || ''];
                })
                : getFilteredPhases(selectedProject).map((phase, index) => {
                    const status = normalizeStatus(phase.display_status ?? phase.status ?? 'planning');
                    return [index + 1, phase.phase_name || phase.name || 'Unnamed phase', phase.phase_code || 'Phase', status, phase.planned_start_date || phase.start || '', phase.planned_end_date || phase.end || ''];
                });
            const headers = timelineViewMode === 'timeline'
                ? ['#', 'Milestone', 'Phase', 'Status', 'Start Planned Date', 'End Planned Date']
                : ['#', 'Phase', 'Code', 'Status', 'Start Date', 'End Date'];
            const csv = [headers, ...rows]
                .map((row) => row.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(','))
                .join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${selectedProject?.name || 'timeline'}.csv`;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            link.remove();
            setTimeout(() => URL.revokeObjectURL(url), 150);
        });
    }

    function renderEmptyState() {
        document.getElementById('timelineContainer').innerHTML = `
            <div class="timeline-empty-state">
                <h3 style="margin:0 0 0.4rem; color: var(--primary-text);">No project data available</h3>
                <p style="margin:0;">Select a project to view its construction schedule.</p>
            </div>
        `;
    }

    function getProjectPhasesForModal() {
        return Array.isArray(selectedProject?.phases) ? selectedProject.phases : [];
    }

    function populateMilestonePhaseOptions(selectedPhaseId = '') {
        const select = document.getElementById('milestonePhaseId');
        if (!select) return;

        const phases = getProjectPhasesForModal();
        if (!phases.length) {
            select.innerHTML = '<option value="">No phases available</option>';
            return;
        }

        select.innerHTML = phases.map((phase) => {
            const phaseId = phase.phase_id ?? phase.id;
            const label = phase.phase_name || phase.name || 'Unnamed Phase';
            const phaseOrder = phase.phase_order ?? phase.order ?? '';
            const displayLabel = phaseOrder ? `${phaseOrder} - ${label}` : label;
            const selectedAttr = String(selectedPhaseId || '') === String(phaseId || '') ? 'selected' : '';
            return `<option value="${escapeHtml(phaseId)}" ${selectedAttr}>${escapeHtml(displayLabel)}</option>`;
        }).join('');

        if (!select.value && phases.length > 0) {
            select.value = String(phases[0].phase_id ?? phases[0].id ?? '');
        }
    }

    function findPhaseInModal(phaseId) {
        const phases = getProjectPhasesForModal();
        return phases.find((item) => String(item.phase_id ?? item.id) === String(phaseId || '')) || phases[0] || null;
    }

    function getPhaseMilestones(phase) {
        return Array.isArray(phase?.milestones) ? phase.milestones : [];
    }

    function updateMilestonePhaseInfo(phase) {
        const wrap = document.getElementById('milestonePhaseInfo');
        const startEl = document.getElementById('milestonePhaseStart');
        const endEl = document.getElementById('milestonePhaseEnd');
        const statusEl = document.getElementById('milestonePhaseStatus');

        if (!wrap || !startEl || !endEl || !statusEl) return;

        if (!phase) {
            wrap.classList.add('d-none');
            return;
        }

        const plannedStart = phase.planned_start_date ? String(phase.planned_start_date).slice(0, 10) : 'Not set';
        const plannedEnd = phase.planned_end_date ? String(phase.planned_end_date).slice(0, 10) : 'Not set';
        const status = phase.status ? String(phase.status).replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Unknown';

        startEl.textContent = plannedStart;
        endEl.textContent = plannedEnd;
        statusEl.textContent = status;
        wrap.classList.remove('d-none');
    }

    function loadMilestoneIntoForm(phase, milestone) {
        document.getElementById('milestoneOriginalPhaseId').value = String(phase?.phase_id ?? phase?.id ?? '');
        document.getElementById('milestoneId').value = milestone?.milestone_id ?? milestone?.id ?? '';
        document.getElementById('milestoneName').value = milestone?.milestone_name || '';
        document.getElementById('milestoneStartDate').value = milestone?.start_date ? String(milestone.start_date).slice(0, 10) : '';
        document.getElementById('milestoneEndDate').value = milestone?.end_date ? String(milestone.end_date).slice(0, 10) : '';
        document.getElementById('milestoneCompleted').value = milestone?.is_completed ? '1' : '0';
        document.getElementById('milestoneDelayed').value = milestone?.is_delayed ? '1' : '0';
        milestoneFormSnapshot = getMilestoneFormSnapshot();
    }

    // Populates the "Milestone to Edit" dropdown for the given phase. Only shown in edit
    // mode, and only when the phase has more than one milestone (otherwise there is
    // nothing to disambiguate). Selecting a different option loads that milestone's data
    // into the form so any milestone under the phase can be edited, not just the first one.
    function populateMilestoneExistingSelector(phase, selectedMilestoneId = '') {
        const wrap = document.getElementById('milestoneExistingSelectorWrap');
        const select = document.getElementById('milestoneExistingSelector');
        if (!wrap || !select) return null;

        const milestones = getPhaseMilestones(phase);

        if (milestones.length <= 1) {
            wrap.classList.add('d-none');
            select.innerHTML = '';
            return milestones[0] || null;
        }

        select.innerHTML = milestones.map((item) => {
            const id = item.milestone_id ?? item.id;
            const label = item.milestone_name || 'Unnamed milestone';
            const dateLabel = item.start_date ? ` (${String(item.start_date).slice(0, 10)})` : '';
            const selectedAttr = String(selectedMilestoneId || '') === String(id || '') ? 'selected' : '';
            return `<option value="${escapeHtml(id)}" ${selectedAttr}>${escapeHtml(label + dateLabel)}</option>`;
        }).join('');

        if (!select.value) {
            select.value = String(milestones[0].milestone_id ?? milestones[0].id ?? '');
        }

        wrap.classList.remove('d-none');

        return milestones.find((item) => String(item.milestone_id ?? item.id) === String(select.value)) || milestones[0] || null;
    }

    function setMilestoneModalMode(mode, phaseId = '', milestoneId = '') {
        const backdrop = document.getElementById('milestoneModalBackdrop');
        const form = document.getElementById('milestoneModalForm');
        const title = document.getElementById('milestoneModalTitle');
        const subtitle = document.getElementById('milestoneModalSubtitle');
        const submitButton = document.getElementById('milestoneSubmitBtn');
        const note = document.getElementById('milestoneModalNote');
        const existingWrap = document.getElementById('milestoneExistingSelectorWrap');

        if (!backdrop || !form || !title || !subtitle || !submitButton) return;

        form.dataset.mode = mode;
        form.reset();
        milestoneFormSnapshot = null;
        document.getElementById('milestoneProjectId').value = selectedProject?.id || '';
        document.getElementById('milestoneId').value = '';
        existingWrap?.classList.add('d-none');
        populateMilestonePhaseOptions(phaseId || '');
        updateMilestonePhaseInfo(findPhaseInModal(document.getElementById('milestonePhaseId')?.value || ''));
        document.body.style.overflow = 'hidden';

        if (mode === 'edit') {
            title.textContent = 'Edit Milestone';
            subtitle.textContent = 'Update the milestone details below.';
            submitButton.innerHTML = '</i> Update Milestone';

            const phase = findPhaseInModal(phaseId);
            const milestones = getPhaseMilestones(phase);
            const milestone = milestones.find((item) => String(item.milestone_id ?? item.id) === String(milestoneId || '')) || milestones[0] || null;

            // Shows the "Milestone to Edit" dropdown whenever the phase has more than one
            // milestone, so any of them can be selected and edited (not just the first).
            populateMilestoneExistingSelector(phase, milestone?.milestone_id ?? milestone?.id ?? '');

            if (phase && milestone) {
                document.getElementById('milestonePhaseId').value = String(phase.phase_id ?? phase.id ?? '');
                loadMilestoneIntoForm(phase, milestone);
            }

            if (note) {
                note.querySelector('span').textContent = '';
            }
        } else {
            title.textContent = 'Create Timeline Milestone';
            subtitle.textContent = 'Add a new milestone under the selected construction phase.';
            submitButton.innerHTML = ' Save Milestone';

            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('milestoneStartDate').value = tomorrow.toISOString().slice(0, 10);
            document.getElementById('milestoneEndDate').value = '';
            document.getElementById('milestoneCompleted').value = '0';
            document.getElementById('milestoneDelayed').value = '0';
            if (note) {
                note.querySelector('span').textContent = '';
            }
        }

        milestoneFormSnapshot = getMilestoneFormSnapshot();
        backdrop.classList.remove('d-none');
    }

    function closeMilestoneModal() {
        const backdrop = document.getElementById('milestoneModalBackdrop');
        const phaseInfo = document.getElementById('milestonePhaseInfo');
        backdrop?.classList.add('d-none');
        phaseInfo?.classList.add('d-none');
        document.body.style.overflow = '';
    }

let milestoneFormSnapshot = null;

    function getMilestoneFormSnapshot() {
        return {
            phaseId: document.getElementById('milestonePhaseId')?.value?.trim() || '',
            name: document.getElementById('milestoneName')?.value?.trim() || '',
            startDate: document.getElementById('milestoneStartDate')?.value?.trim() || '',
            endDate: document.getElementById('milestoneEndDate')?.value?.trim() || '',
            completed: document.getElementById('milestoneCompleted')?.value || '0',
            delayed: document.getElementById('milestoneDelayed')?.value || '0'
        };
    }

    function validateMilestoneForm() {
        const phaseId = document.getElementById('milestonePhaseId')?.value?.trim() || '';
        const name = document.getElementById('milestoneName')?.value?.trim() || '';
        const plannedDate = document.getElementById('milestoneStartDate')?.value?.trim() || '';
        const actualDate = document.getElementById('milestoneEndDate')?.value?.trim() || '';
        const form = document.getElementById('milestoneModalForm');
        const mode = form?.dataset.mode || 'create';

        if (!phaseId) {
            return 'Please select the construction phase that owns this milestone.';
        }

        if (!name) {
            return 'A milestone name is required.';
        }

        if (!plannedDate) {
            return 'A planned date is required.';
        }

        if (actualDate && plannedDate && actualDate < plannedDate) {
            return 'End date cannot be earlier than the start date.';
        }

        const phase = (getProjectPhasesForModal() || []).find((item) => String(item.phase_id ?? item.id) === String(phaseId)) || null;
        if (phase && phase.planned_start_date_raw && phase.planned_end_date_raw) {
            const phaseStart = phase.planned_start_date_raw;
            const phaseEnd = phase.planned_end_date_raw;

            if (plannedDate < phaseStart || plannedDate > phaseEnd) {
                return 'Milestone start date must be within the selected phase schedule.';
            }

            if (actualDate && (actualDate < phaseStart || actualDate > phaseEnd)) {
                return 'Milestone end date must be within the selected phase schedule.';
            }
        }

        if (mode === 'edit' && milestoneFormSnapshot) {
            const currentValues = getMilestoneFormSnapshot();
            const isUnchanged = JSON.stringify(currentValues) === JSON.stringify(milestoneFormSnapshot);

            if (isUnchanged) {
                return 'No changes were made. Update the milestone details before saving.';
            }
        }

        return '';
    }

    function getAppBasePath() {
        const path = window.location.pathname || '/';
        const segments = path.split('/').filter(Boolean);
        const routeIndex = segments.findIndex((segment) => ['admin', 'client', 'supervisor'].includes(segment));

        if (routeIndex > 0) {
            return '/' + segments.slice(0, routeIndex).join('/') + '/';
        }

        return '/';
    }

    function getAdminUrl(path) {
        const basePath = getAppBasePath();
        return `${basePath.replace(/\/$/, '')}${path.startsWith('/') ? path : `/${path}`}`;
    }

    async function refreshTimelineFromServer() {
        if (!selectedProject?.id) return false;

        try {
            const response = await fetch(getAdminUrl(`/admin/timeline/data/${selectedProject.id}`), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success && data.project) {
                const projectIndex = projectsData.findIndex((project) => String(project.id) === String(selectedProject.id));
                if (projectIndex >= 0) {
                    projectsData[projectIndex] = data.project;
                } else {
                    projectsData.push(data.project);
                }

                selectedProject = data.project;
                populatePhaseFilter(selectedProject);
                renderTimeline(selectedProject);
                return true;
            }
        } catch (error) {
            console.error('Failed to refresh timeline data', error);
        }

        if (selectedProject) {
            renderTimeline(selectedProject);
        }
        return false;
    }

    async function submitMilestoneModal(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const mode = form.dataset.mode || 'create';
        const submitButton = document.getElementById('milestoneSubmitBtn');
        const validationMessage = validateMilestoneForm();

        if (validationMessage) {
            if (window.Swal) {
                Swal.fire({
                    title: 'Validation required',
                    text: validationMessage,
                    icon: 'warning',
                    confirmButtonColor: '#166534'
                });
            } else {
                window.alert(validationMessage);
            }
            return;
        }

        const formData = new FormData(form);
        formData.set('is_completed', document.getElementById('milestoneCompleted').value === '1' ? '1' : '0');
        formData.set('is_delayed', document.getElementById('milestoneDelayed').value === '1' ? '1' : '0');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        let url = getAdminUrl('/admin/milestones');
        if (mode === 'edit') {
            const projectId = formData.get('project_id');
            const originalPhaseId = formData.get('original_phase_id') || formData.get('phase_id');
            const milestoneId = formData.get('milestone_id');
            url = getAdminUrl(`/admin/projects/${projectId}/phases/${originalPhaseId}/milestones/${milestoneId}`);
            formData.set('_method', 'PUT');
        }

        submitButton.disabled = true;
        submitButton.innerHTML = mode === 'edit' ? 'Updating...' : 'Creating...';
        let swalLoading = null;
        if (window.Swal) {
            swalLoading = Swal.fire({
                title: mode === 'edit' ? 'Updating milestone...' : 'Creating milestone...',
                text: 'Please wait while we process your request.',
                icon: 'info',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success) {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Success',
                        text: mode === 'edit' ? 'Milestone Updated Successfully' : 'Milestone Created Successfully',
                        icon: 'success',
                        confirmButtonColor: '#166534'
                    });
                } else {
                    window.alert(mode === 'edit' ? 'Milestone Updated Successfully' : 'Milestone Created Successfully');
                }
                closeMilestoneModal();
                await refreshTimelineFromServer();
            } else {
                const errorMessage = data?.message || (data?.errors ? Object.values(data.errors).flat()[0] : 'Please verify the information and try again.');
                console.warn('Milestone save failed', response.status, data);
                if (window.Swal) {
                    Swal.fire({
                        title: 'Unable to save milestone',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#166534'
                    });
                } else {
                    window.alert(errorMessage);
                }
            }
        } catch (error) {
            if (window.Swal) {
                Swal.fire({
                    title: 'Unable to save milestone',
                    text: 'The request could not be completed. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#166534'
                });
            } else {
                window.alert('The request could not be completed. Please try again.');
            }
        } finally {
            if (swalLoading) {
                swalLoading.close();
            }
            submitButton.disabled = false;
            submitButton.innerHTML = mode === 'edit' ? '<i class="bi bi-pencil-square"></i> Update Milestone' : '<i class="bi bi-save2-fill"></i> Save Milestone';
        }
    }

    function attachMilestoneModalEvents() {
        document.getElementById('milestoneModalForm')?.addEventListener('submit', submitMilestoneModal);

        document.getElementById('milestonePhaseId')?.addEventListener('change', function (event) {
            const phase = findPhaseInModal(event.target.value);
            updateMilestonePhaseInfo(phase);

            const form = document.getElementById('milestoneModalForm');
            if (form?.dataset.mode !== 'edit') return;

            const milestones = getPhaseMilestones(phase);
            const milestone = milestones[0] || null;

            populateMilestoneExistingSelector(phase, milestone?.milestone_id ?? milestone?.id ?? '');

            if (phase && milestone) {
                loadMilestoneIntoForm(phase, milestone);
            } else {
                document.getElementById('milestoneOriginalPhaseId').value = String(phase?.phase_id ?? phase?.id ?? '');
                document.getElementById('milestoneId').value = '';
            }
        });

        // Lets the admin pick a different milestone under the same phase without closing
        // and reopening the modal.
        document.getElementById('milestoneExistingSelector')?.addEventListener('change', function (event) {
            const phaseId = document.getElementById('milestonePhaseId')?.value || '';
            const phase = findPhaseInModal(phaseId);
            const milestone = getPhaseMilestones(phase).find((item) => String(item.milestone_id ?? item.id) === String(event.target.value)) || null;

            if (phase && milestone) {
                loadMilestoneIntoForm(phase, milestone);
            }
        });

        document.querySelectorAll('[data-close-milestone-modal]').forEach((button) => {
            button.addEventListener('click', closeMilestoneModal);
        });
        document.getElementById('milestoneModalBackdrop')?.addEventListener('click', function (event) {
            if (event.target === this) {
                closeMilestoneModal();
            }
        });
    }

    window.refreshProjectTimeline = async function () {
        await refreshTimelineFromServer();
    };

    window.openPhaseModal = window.openPhaseModal || function () {
        return true;
    };

    window.openMilestoneCreateModal = function () {
        setMilestoneModalMode('create');
    };

    window.openMilestoneEditModal = function (phaseId, milestoneId) {
        setMilestoneModalMode('edit', phaseId, milestoneId || '');
    };

    document.addEventListener('DOMContentLoaded', function () {
        const selector = document.getElementById('projectSelector');
        const phaseFilter = document.getElementById('phaseFilterSelector');
        const statusFilter = document.getElementById('statusFilterSelector');
        const searchInput = document.getElementById('searchMilestones');
        const addMilestoneButton = document.getElementById('addMilestoneBtn');

        selector?.addEventListener('change', function (event) {
            selectProject(event.target.value);
        });

        phaseFilter?.addEventListener('change', function () {
            resetTablePagination();
            if (selectedProject) renderTimeline(selectedProject);
        });

        statusFilter?.addEventListener('change', function () {
            resetTablePagination();
            if (selectedProject) renderTimeline(selectedProject);
        });

        searchInput?.addEventListener('input', function () {
            resetTablePagination();
            if (selectedProject) renderTimeline(selectedProject);
        });

        addMilestoneButton?.addEventListener('click', function () {
            window.openMilestoneCreateModal?.();
        });

        attachMilestoneModalEvents();

        if (selector && selector.options.length > 0) {
            selectProject(selector.value || selector.options[0].value);
        } else if (projectsData && projectsData.length > 0) {
            selectProject(projectsData[0].id);
        } else {
            renderEmptyState();
        }
    });
</script>
@endsection