@extends('layouts.supervisor')

@section('title', 'Project Timeline - Supervisor View')
@section('page_title', 'Project Timeline')

@push('styles')
<style>
    .timeline-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .timeline-header-card {
        background: var(--supervisor-surface);
        border: 1px solid var(--supervisor-border);
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(9, 96, 86, 0.05);
        padding: 1.25rem;
    }

    .timeline-header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.5rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .timeline-container {
        min-height: 200px;
    }

    .timeline-empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        padding: 2rem;
        text-align: center;
        color: var(--supervisor-muted);
    }

    .timeline-empty-state > div {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .eyebrow {
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--supervisor-muted);
        margin-bottom: 0.4rem;
    }

    .page-title {
        font-family: 'Syne', sans-serif;
        font-size: 2rem;
        font-weight: 700;
        color: var(--supervisor-text);
        line-height: 1.1;
    }

    .page-subtitle {
        font-size: 0.95rem;
        color: var(--supervisor-muted);
        line-height: 1.5;
    }

    .mb-2 { margin-bottom: 0.5rem; }
    .mb-0 { margin-bottom: 0; }

    .timeline-grid {
        display: grid;
        grid-template-columns: 1.35fr 0.95fr;
        gap: 1.25rem;
    }

    .timeline-column {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .timeline-card {
        background: var(--supervisor-surface);
        border: 1px solid var(--supervisor-border);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 6px 16px rgba(9, 96, 86, 0.04);
    }

    .timeline-card-header {
        margin-bottom: 1.25rem;
    }

    .timeline-card-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--supervisor-text);
        margin: 0 0 0.35rem 0;
    }

    .timeline-card-subtitle {
        font-size: 0.85rem;
        color: var(--supervisor-muted);
        margin: 0;
        line-height: 1.4;
    }

    .timeline-summary-card {
        background: linear-gradient(135deg, #fafdfb 0%, var(--supervisor-surface) 100%);
    }

    .timeline-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1rem;
        background: #eef4ee;
        border: 1px solid #d7e3c6;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--supervisor-primary);
        margin-bottom: 1rem;
    }

    .timeline-status-badge i {
        font-size: 1rem;
    }

    .timeline-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .timeline-summary-item {
        padding: 0.85rem;
        background: #fcfdfc;
        border: 1px solid #e8eef2;
        border-radius: 12px;
    }

    .timeline-item-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-bottom: 0.4rem;
    }

    .timeline-item-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--supervisor-text);
    }

    .timeline-phases-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .timeline-phase-card {
        padding: 1rem;
        background: #fafdfb;
        border: 1px solid #e8eef2;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .timeline-phase-card:hover {
        background: #f5faf7;
        border-color: var(--supervisor-accent);
        box-shadow: 0 4px 12px rgba(9, 96, 86, 0.06);
    }

    .timeline-phase-card.is-active {
        background: #eef7de;
        border-color: var(--supervisor-secondary);
    }

    .timeline-phase-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 0.75rem;
    }

    .timeline-phase-name {
        font-family: 'Syne', sans-serif;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--supervisor-text);
    }

    .timeline-phase-badge {
        display: inline-flex;
        padding: 0.35rem 0.7rem;
        background: #e8eef2;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--supervisor-muted);
        white-space: nowrap;
    }

    .timeline-phase-badge.is-active {
        background: var(--supervisor-secondary);
        color: #fff;
    }

    .timeline-phase-dates {
        font-size: 0.8rem;
        color: var(--supervisor-muted);
        margin-bottom: 0.75rem;
    }

    .timeline-progress-section {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .timeline-progress-bar {
        height: 6px;
        background: #e8eef2;
        border-radius: 999px;
        overflow: hidden;
    }

    .timeline-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--supervisor-primary), var(--supervisor-secondary));
        transition: width 0.3s ease;
    }

    .timeline-progress-text {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--supervisor-muted);
    }

    .timeline-progress-display {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .timeline-circular-wrapper {
        display: flex;
        justify-content: center;
        margin-bottom: 0.5rem;
    }

    .timeline-circular-visual {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .timeline-circular-visual::before {
        content: '';
        position: absolute;
        width: 120px;
        height: 120px;
        background: var(--supervisor-surface);
        border-radius: 50%;
    }

    .timeline-circular-center {
        position: relative;
        z-index: 1;
        text-align: center;
    }

    .timeline-circular-value {
        font-family: 'Syne', sans-serif;
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--supervisor-primary);
        line-height: 1;
    }

    .timeline-circular-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-top: 0.3rem;
    }

    .timeline-progress-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .timeline-progress-hero > div:first-child {
        flex-shrink: 0;
    }

    .timeline-progress-hero-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-bottom: 0.25rem;
    }

    .timeline-progress-hero-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--supervisor-primary);
    }

    .timeline-progress-hero-bar {
        flex: 1;
        height: 8px;
        background: #e8eef2;
        border-radius: 999px;
        overflow: hidden;
    }

    .timeline-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.85rem;
    }

    .timeline-stat-card {
        padding: 0.85rem;
        background: #fafdfb;
        border: 1px solid #e8eef2;
        border-radius: 10px;
        text-align: center;
    }

    .timeline-stat-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--supervisor-muted);
        margin-bottom: 0.4rem;
    }

    .timeline-stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--supervisor-primary);
    }

    .timeline-milestones-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .timeline-milestone-item {
        display: flex;
        gap: 0.85rem;
        padding: 0.9rem;
        background: #fafdfb;
        border-left: 3px solid var(--supervisor-border);
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .timeline-milestone-item:hover {
        background: #f5faf7;
        border-left-color: var(--supervisor-accent);
    }

    .timeline-milestone-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #eef4ee;
        border-radius: 8px;
        flex-shrink: 0;
        color: var(--supervisor-primary);
        font-size: 1.1rem;
    }

    .timeline-milestone-content {
        flex: 1;
        min-width: 0;
    }

    .timeline-milestone-title {
        font-family: 'Syne', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--supervisor-text);
        margin-bottom: 0.2rem;
    }

    .timeline-milestone-meta {
        font-size: 0.8rem;
        color: var(--supervisor-muted);
        line-height: 1.4;
    }

    /* Project Cards Grid */
    .timeline-projects-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .project-card {
        background: var(--supervisor-surface);
        border: 1px solid var(--supervisor-border);
        border-radius: 16px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }

    .project-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(9, 96, 86, 0.02) 0%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .project-card:hover {
        border-color: var(--supervisor-accent);
        box-shadow: 0 12px 32px rgba(9, 96, 86, 0.12), 0 4px 12px rgba(9, 96, 86, 0.06);
        transform: translateY(-4px);
    }

    .project-card:hover::before {
        opacity: 1;
    }

    .project-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }

    .project-card-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--supervisor-text);
        line-height: 1.3;
        margin: 0;
        flex: 1;
    }

    .project-card-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.75rem;
        background: #eef4ee;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--supervisor-primary);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .project-card-status-badge.completed {
        background: #e8f5e9;
        color: #1b5e20;
    }

    .project-card-status-badge.delayed {
        background: #fff3e0;
        color: #e65100;
    }

    .project-card-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--supervisor-muted);
    }

    .project-card-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .project-card-label {
        font-weight: 600;
        color: var(--supervisor-text);
        flex: 1;
    }

    .project-card-value {
        color: var(--supervisor-muted);
    }

    .project-card-client {
        font-size: 0.85rem;
        color: var(--supervisor-muted);
        padding: 0.5rem 0;
        border-top: 1px solid var(--supervisor-border);
        border-bottom: 1px solid var(--supervisor-border);
    }

    .project-card-progress-section {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        padding: 0.75rem 0;
    }

    .project-card-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
    }

    .project-card-progress-label {
        font-weight: 600;
        color: var(--supervisor-text);
    }

    .project-card-progress-percent {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        color: var(--supervisor-primary);
    }

    .project-card-progress-bar {
        height: 8px;
        background: #e8eef2;
        border-radius: 999px;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        transition: height 0.2s ease;
    }

    .project-card-progress-bar:hover {
        height: 10px;
    }

    .project-card-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--supervisor-primary), var(--supervisor-secondary));
        border-radius: 999px;
        transition: width 0.3s ease;
        position: relative;
    }

    .project-card-progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.3) 0%, transparent 50%, rgba(0, 0, 0, 0.1) 100%);
        border-radius: 999px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .project-card-progress-bar:hover .project-card-progress-fill::after {
        opacity: 1;
    }

    .project-card-dates {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: var(--supervisor-muted);
        padding: 0.5rem 0;
    }

    .project-card-footer {
        display: flex;
        justify-content: center;
        padding-top: 0.25rem;
        margin-top: auto;
    }

    .project-card-btn {
        width: auto;
        padding: 0.65rem 1rem;
        background: var(--supervisor-primary);
        color: #fff;
        border: none;
        border-radius: 999px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        box-shadow: 0 6px 14px rgba(9, 96, 86, 0.12);
    }

    .project-card-btn:hover {
        background: var(--supervisor-secondary);
        box-shadow: 0 8px 18px rgba(9, 96, 86, 0.18);
        transform: translateY(-1px);
    }

    .project-card-target {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.8rem;
        color: var(--supervisor-muted);
        padding-top: 0.15rem;
    }

    .project-card-target-label {
        font-weight: 600;
    }

    .project-card-target-date {
        font-weight: 700;
        color: var(--supervisor-text);
    }

    .timeline-actions-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 1.25rem;
    }

    .timeline-back-button {
        padding: 0.72rem 1.1rem;
        background: transparent;
        border: 1px solid var(--supervisor-border);
        border-radius: 999px;
        color: var(--supervisor-primary);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .timeline-back-button:hover {
        background: #f4f8f4;
        box-shadow: 0 6px 14px rgba(9, 96, 86, 0.08);
        transform: translateY(-1px);
    }

    .project-card-btn:active {
        transform: scale(0.98);
    }

    /* Tooltip Styles */
    .tooltip {
        position: absolute;
        background: var(--supervisor-text);
        color: #fff;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
        pointer-events: none;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px) translateX(-50%);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        line-height: 1.4;
        max-width: 280px;
        white-space: normal;
    }

    .tooltip.visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(-12px) translateX(-50%);
    }

    .tooltip::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid var(--supervisor-text);
    }

    .tooltip-item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .tooltip-item:last-child {
        margin-bottom: 0;
    }

    .tooltip-label {
        opacity: 0.85;
        min-width: fit-content;
    }

    .tooltip-value {
        font-weight: 700;
        text-align: right;
    }

    /* No Projects Empty State */
    .timeline-no-projects {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        padding: 2rem;
        text-align: center;
    }

    .timeline-no-projects > div {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .timeline-no-projects-icon {
        font-size: 3.5rem;
        opacity: 0.3;
        margin-bottom: 0.5rem;
    }

    .timeline-no-projects-title {
        font-family: 'Syne', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--supervisor-text);
    }

    .timeline-no-projects-desc {
        font-size: 0.95rem;
        color: var(--supervisor-muted);
        max-width: 400px;
        line-height: 1.6;
    }

    .timeline-no-projects-btn {
        padding: 0.75rem 1.5rem;
        background: var(--supervisor-primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .timeline-no-projects-btn:hover {
        background: var(--supervisor-secondary);
        box-shadow: 0 4px 12px rgba(9, 96, 86, 0.2);
    }

    .interactive-progress {
        position: relative;
        cursor: help;
    }

    .interactive-progress:hover {
        opacity: 0.9;
    }

    @media (max-width: 1200px) {
        .timeline-grid {
            grid-template-columns: 1fr;
        }

        .timeline-projects-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .timeline-header-top {
            flex-direction: column;
            align-items: stretch;
        }

        .timeline-selector-wrapper {
            min-width: auto;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .timeline-summary-grid {
            grid-template-columns: 1fr 1fr;
        }

        .timeline-stat-grid {
            grid-template-columns: 1fr;
        }

        .timeline-circular-visual {
            width: 140px;
            height: 140px;
        }

        .timeline-circular-visual::before {
            width: 90px;
            height: 90px;
        }

        .timeline-circular-value {
            font-size: 1.6rem;
        }

        .timeline-progress-hero {
            flex-direction: column;
            align-items: stretch;
        }

        .timeline-projects-grid {
            grid-template-columns: 1fr;
        }

        .project-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .project-card-status-badge {
            align-self: flex-start;
        }

        .tooltip {
            max-width: 240px;
            font-size: 0.8rem;
            padding: 0.7rem 0.85rem;
        }
    }
</style>
@endpush

@section('content')
<div class="timeline-wrapper">
    <section class="timeline-header-card">
        <div class="timeline-header-top">
            <div>
                <div class="eyebrow">Field Operations</div>
                <h1 class="page-title mb-2">Project Timeline</h1>
                <p class="page-subtitle mb-0">Monitor construction progress, active phases, and upcoming milestones for your assigned work.</p>
            </div>
        </div>

        <div id="timelineContainer" class="timeline-container">
            <div class="timeline-empty-state">
                <div>
                    <i class="bi bi-calendar-check" style="font-size: 2.5rem; display: block; margin-bottom: 0.85rem; opacity: 0.45;"></i>
                    <div style="font-size: 1rem; font-weight: 600;">Select a project to view timeline and milestones.</div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    const projectsData = @json($projectsWithStats);

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttribute(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function formatDate(dateString) {
        if (!dateString) return 'Pending';
        try {
            const date = new Date(dateString);
            if (Number.isNaN(date.getTime())) return dateString;
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch (e) {
            return dateString;
        }
    }

    let currentTooltip = null;

    function getProjectStatus(project) {
        const phases = Array.isArray(project.phases) ? project.phases : [];
        const hasDelayed = phases.some(phase => phase.status === 'delayed' || phase.display_status === 'delayed' || phase.display_status === 'late');

        if (project.completedPhases === phases.length && phases.length > 0) {
            return { label: 'Completed', class: 'completed' };
        }
        if (hasDelayed) {
            return { label: 'Delayed', class: 'delayed' };
        }
        if (project.inProgressPhases > 0) {
            return { label: 'Ongoing', class: 'ongoing' };
        }
        return { label: 'Planned', class: 'planned' };
    }

    function showTooltip(event, data) {
        removeTooltip();

        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip visible';
        tooltip.innerHTML = data;

        const x = event.clientX || 0;
        const y = event.clientY || 0;
        const left = Math.min(Math.max(16, x), window.innerWidth - 220);
        const top = Math.max(12, y - 8);

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
        document.body.appendChild(tooltip);
        currentTooltip = tooltip;
    }

    function attachTooltipHandlers(container) {
        if (!container) return;

        container.querySelectorAll('[data-tooltip-content]').forEach((element) => {
            const content = element.getAttribute('data-tooltip-content');
            if (!content) return;

            const handleTooltip = (event) => showTooltip(event, content);
            element.addEventListener('mouseenter', handleTooltip);
            element.addEventListener('mousemove', handleTooltip);
            element.addEventListener('mouseleave', removeTooltip);
        });
    }

    function removeTooltip() {
        if (currentTooltip) {
            currentTooltip.remove();
            currentTooltip = null;
        }
    }

    function renderProjectCards() {
        const container = document.getElementById('timelineContainer');
        
        if (!projectsData || projectsData.length === 0) {
            container.innerHTML = `
                <div class="timeline-empty-state">
                    <div>
                        <i class="bi bi-folder-x" style="font-size: 2.5rem; display: block; margin-bottom: 0.85rem; opacity: 0.45;"></i>
                        <div style="font-size: 1rem; font-weight: 600;">No assigned projects yet.</div>
                        <div style="font-size: 0.9rem; color: var(--supervisor-muted); margin-top: 0.5rem;">Please wait for the administrator to assign a construction project.</div>
                    </div>
                </div>
            `;
            return;
        }

        const cardsHtml = projectsData.map(project => {
            const status = getProjectStatus(project);
            const phases = Array.isArray(project.phases) ? project.phases : [];
            const currentPhase = phases.find(p => p.status === 'in_progress') || phases[0];
            const progressPercent = Math.round(Number(project.progress || 0));
            const tooltipContent = `<div class='tooltip-item'><span class='tooltip-label'>Overall Progress</span><span class='tooltip-value'>${progressPercent}%</span></div><div class='tooltip-item'><span class='tooltip-label'>Completed</span><span class='tooltip-value'>${project.completedPhases}/${phases.length} phases</span></div><div class='tooltip-item'><span class='tooltip-label'>Current Phase</span><span class='tooltip-value'>${escapeHtml(currentPhase ? currentPhase.phase_name : 'N/A')}</span></div><div class='tooltip-item'><span class='tooltip-label'>Target Finish</span><span class='tooltip-value'>${escapeHtml(formatDate(project.targetEndDate))}</span></div>`;

            return `
                <div class="project-card" onclick="selectProject('${project.id}')">
                    <div class="project-card-header">
                        <h3 class="project-card-title">${escapeHtml(project.name || 'Project')}</h3>
                        <span class="project-card-status-badge ${status.class}">
                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                            ${escapeHtml(status.label)}
                        </span>
                    </div>
                    
                    <div class="project-card-client">
                        <strong>Client:</strong> ${escapeHtml(project.client_name || 'N/A')} 
                        ${project.location ? ` • <strong>Location:</strong> ${escapeHtml(project.location)}` : ''}
                    </div>

                    <div class="project-card-info">
                        <div class="project-card-info-row">
                            <span class="project-card-label">Current Phase:</span>
                            <span class="project-card-value">${escapeHtml(currentPhase ? currentPhase.phase_name : 'N/A')}</span>
                        </div>
                    </div>

                            <div class="project-card-progress-section">
                        <div class="project-card-progress-header">
                            <span class="project-card-progress-label">Overall Progress</span>
                            <span class="project-card-progress-percent">${progressPercent}%</span>
                        </div>
                        <div class="project-card-progress-bar interactive-progress" data-tooltip-content="${escapeAttribute(tooltipContent)}">
                            <div class="project-card-progress-fill" style="width: ${progressPercent}%;"></div>
                        </div>
                    </div>

                    <div class="project-card-target">
                        <span class="project-card-target-label">Target Completion</span>
                        <span class="project-card-target-date">${escapeHtml(formatDate(project.targetEndDate))}</span>
                    </div>

                    <div class="project-card-footer">
                        <button class="project-card-btn" onclick="event.stopPropagation(); selectProject('${project.id}');">
                            View Project
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = `<div class="timeline-projects-grid">${cardsHtml}</div>`;
        attachTooltipHandlers(container);
    }

    function selectProject(projectId) {
        const container = document.getElementById('timelineContainer');
        
        if (!projectId) {
            renderProjectCards();
            return;
        }

        const project = projectsData.find(p => String(p.id) === String(projectId));
        if (!project) return;

        renderTimeline(project, container);
    }

    function renderTimeline(project, container) {
        const phases = Array.isArray(project.phases) ? project.phases : [];
        
        const currentPhase = phases.find(p => p.status === 'in_progress') || 
                           phases.find(p => p.display_status === 'in-progress') || 
                           phases[0] || null;
        const nextPhase = phases.find(p => p.status !== 'completed' && p.status !== 'in_progress') || null;
        
        const progressPercent = Math.max(0, Math.min(100, Number(project.progress || 0)));
        const statusLabel = project.inProgressPhases > 0 ? 'In Progress' : 
                           (project.completedPhases === phases.length && phases.length > 0 ? 'Completed' : 'Planned');
        
        const progressDegrees = Math.round((progressPercent / 100) * 360);
        const progressStyle = `conic-gradient(var(--supervisor-primary) ${progressDegrees}deg, #edf4ee 0deg)`;
        const progressTooltip = `<div class='tooltip-item'><span class='tooltip-label'>Overall Progress</span><span class='tooltip-value'>${progressPercent}%</span></div><div class='tooltip-item'><span class='tooltip-label'>Completed Phases</span><span class='tooltip-value'>${project.completedPhases}</span></div><div class='tooltip-item'><span class='tooltip-label'>Current Phase</span><span class='tooltip-value'>${escapeHtml(currentPhase ? currentPhase.phase_name : 'N/A')}</span></div><div class='tooltip-item'><span class='tooltip-label'>Estimated Completion</span><span class='tooltip-value'>${escapeHtml(formatDate(project.targetEndDate))}</span></div>`;

        // Phase cards HTML
        const phaseCardsHtml = phases.map((phase, index) => {
            const isCurrent = phase.status === 'in_progress';
            const startDate = formatDate(phase.planned_start_date);
            const endDate = formatDate(phase.planned_end_date);
            const completion = Math.round(Number(phase.completion_percentage || 0));
            const badgeText = isCurrent ? 'Active' : phase.status === 'completed' ? 'Done' : 'Planned';
            const tooltipContent = `<div class='tooltip-item'><span class='tooltip-label'>${escapeHtml(phase.phase_name)}</span><span class='tooltip-value'>${completion}% Complete</span></div><div class='tooltip-item'><span class='tooltip-label'>Status</span><span class='tooltip-value'>${badgeText}</span></div><div class='tooltip-item'><span class='tooltip-label'>Start</span><span class='tooltip-value'>${startDate}</span></div><div class='tooltip-item'><span class='tooltip-label'>Expected End</span><span class='tooltip-value'>${endDate}</span></div>`;

            return `
                <div class="timeline-phase-card ${isCurrent ? 'is-active' : ''}">
                    <div class="timeline-phase-header">
                        <div class="timeline-phase-name">${escapeHtml(phase.phase_name || `Phase ${index + 1}`)}</div>
                        <span class="timeline-phase-badge ${isCurrent ? 'is-active' : ''}">${escapeHtml(badgeText)}</span>
                    </div>
                    <div class="timeline-phase-dates">${escapeHtml(startDate)} – ${escapeHtml(endDate)}</div>
                    <div class="timeline-progress-section">
                        <div class="timeline-progress-bar interactive-progress" data-tooltip-content="${escapeAttribute(tooltipContent)}">
                            <div class="timeline-progress-fill" style="width: ${completion}%;"></div>
                        </div>
                        <div class="timeline-progress-text">${completion}% complete</div>
                    </div>
                </div>
            `;
        }).join('');

        // Milestones HTML
        const milestones = [];
        if (currentPhase) {
            milestones.push({
                icon: 'bi-flag',
                title: escapeHtml(currentPhase.phase_name || 'Current Phase'),
                meta: 'Active phase and current delivery focus'
            });
        }
        if (nextPhase) {
            milestones.push({
                icon: 'bi-clock-history',
                title: escapeHtml(nextPhase.phase_name || 'Upcoming Phase'),
                meta: 'Next planned phase on the schedule'
            });
        }
        if (project.completedPhases > 0) {
            milestones.push({
                icon: 'bi-check2-circle',
                title: `${project.completedPhases} phase${project.completedPhases !== 1 ? 's' : ''} completed`,
                meta: 'Successfully finished phases'
            });
        }

        const milestonesHtml = milestones.slice(0, 3).map(m => `
            <div class="timeline-milestone-item">
                <span class="timeline-milestone-icon"><i class="bi ${m.icon}"></i></span>
                <div class="timeline-milestone-content">
                    <div class="timeline-milestone-title">${m.title}</div>
                    <div class="timeline-milestone-meta">${m.meta}</div>
                </div>
            </div>
        `).join('');

        container.innerHTML = `
            <div class="timeline-grid">
                <div class="timeline-column">
                    <section class="timeline-card timeline-summary-card">
                        <div class="timeline-card-header">
                            <h2 class="timeline-card-title">${escapeHtml(project.name || 'Selected Project')}</h2>
                            <p class="timeline-card-subtitle">Current delivery outlook for the selected project</p>
                        </div>
                        <div class="timeline-status-badge">
                            <i class="bi bi-calendar3"></i>${escapeHtml(statusLabel)}
                        </div>
                        <div class="timeline-summary-grid">
                            <div class="timeline-summary-item">
                                <span class="timeline-item-label">Target Completion</span>
                                <div class="timeline-item-value">${escapeHtml(formatDate(project.targetEndDate))}</div>
                            </div>
                            <div class="timeline-summary-item">
                                <span class="timeline-item-label">Current Phase</span>
                                <div class="timeline-item-value">${escapeHtml(currentPhase ? currentPhase.phase_name : 'Pending')}</div>
                            </div>
                            <div class="timeline-summary-item">
                                <span class="timeline-item-label">Completed</span>
                                <div class="timeline-item-value">${project.completedPhases}</div>
                            </div>
                            <div class="timeline-summary-item">
                                <span class="timeline-item-label">Upcoming</span>
                                <div class="timeline-item-value">${project.upcomingPhases}</div>
                            </div>
                        </div>
                    </section>

                    <section class="timeline-card">
                        <div class="timeline-card-header">
                            <h2 class="timeline-card-title">Construction Phases</h2>
                            <p class="timeline-card-subtitle">Sequence and progress for each phase in the project plan</p>
                        </div>
                        <div class="timeline-phases-list">${phaseCardsHtml}</div>
                    </section>
                </div>

                <div class="timeline-column">
                    <section class="timeline-card">
                        <div class="timeline-card-header">
                            <h2 class="timeline-card-title">Project Progress</h2>
                            <p class="timeline-card-subtitle">Current overall health of the schedule</p>
                        </div>
                        <div class="timeline-progress-display">
                            <div class="timeline-circular-wrapper">
                                <div class="timeline-circular-visual interactive-progress" style="background: ${progressStyle};" data-tooltip-content="${escapeAttribute(progressTooltip)}">
                                    <div class="timeline-circular-center">
                                        <div class="timeline-circular-value">${Math.round(progressPercent)}%</div>
                                        <div class="timeline-circular-label">Complete</div>
                                    </div>
                                </div>
                            </div>
                            <div class="timeline-progress-hero">
                                <div>
                                    <div class="timeline-progress-hero-label">Overall Progress</div>
                                    <div class="timeline-progress-hero-value">${Math.round(progressPercent)}%</div>
                                </div>
                                <div class="timeline-progress-hero-bar interactive-progress" data-tooltip-content="${escapeAttribute(progressTooltip)}">
                                    <div class="timeline-progress-fill" style="width: ${progressPercent}%;"></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="timeline-card">
                        <div class="timeline-card-header">
                            <h2 class="timeline-card-title">Timeline Status</h2>
                            <p class="timeline-card-subtitle">Key counts across the current plan</p>
                        </div>
                        <div class="timeline-stat-grid">
                            <div class="timeline-stat-card">
                                <span class="timeline-stat-label">Completed</span>
                                <div class="timeline-stat-value">${project.completedPhases}</div>
                            </div>
                            <div class="timeline-stat-card">
                                <span class="timeline-stat-label">In Progress</span>
                                <div class="timeline-stat-value">${project.inProgressPhases}</div>
                            </div>
                            <div class="timeline-stat-card">
                                <span class="timeline-stat-label">Upcoming</span>
                                <div class="timeline-stat-value">${project.upcomingPhases}</div>
                            </div>
                        </div>
                    </section>

                    <section class="timeline-card">
                        <div class="timeline-card-header">
                            <h2 class="timeline-card-title">Upcoming Milestones</h2>
                            <p class="timeline-card-subtitle">Nearest phase indicators and follow-up items</p>
                        </div>
                        <div class="timeline-milestones-list">${milestonesHtml}</div>
                    </section>
                </div>
            </div>
            <div class="timeline-actions-footer">
                <button class="timeline-back-button" onclick="selectProject('')">
                    <i class="bi bi-chevron-left"></i> Back to Projects
                </button>
            </div>
        `;
        attachTooltipHandlers(container);
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderProjectCards();
    });

    document.addEventListener('click', removeTooltip);
</script>
@endsection
