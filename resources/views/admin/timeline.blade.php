```blade
@extends('layouts.admin')

@section('title', 'Project Timeline - D&G Construction Monitor')
@section('page_title', 'Project Timeline')

@push('styles')
<style>
    #pg-timeline {
        --green: #22b866;
        --green-dark: #16834c;
        --green-soft: #eef9f3;

        --blue: #3b82f6;
        --blue-dark: #2563eb;
        --blue-soft: #eff6ff;

        --amber: #f59e0b;
        --amber-dark: #b76d00;
        --amber-soft: #fff8e8;

        --dark: #17211b;
        --muted: #68736d;
        --border: #e5e9e6;
        --background: #f4f6f4;
        --white: #ffffff;

        --shadow: 0 10px 30px rgba(19, 50, 31, 0.06);

        color: var(--dark);

        font-family:
            Inter,
            ui-sans-serif,
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Helvetica,
            Arial,
            sans-serif;

        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;

        padding-bottom: 2rem;
    }

    #pg-timeline *,
    #pg-timeline *::before,
    #pg-timeline *::after {
        box-sizing: border-box;
        font-family: inherit;
    }

    /* ================================
       TOOLBAR
    ================================ */

    .timeline-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.3rem;
    }

    .project-select-wrapper {
        position: relative;
        width: min(100%, 340px);
    }

    .project-select-wrapper::after {
        content: "\F282";
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        font-family: "bootstrap-icons";
        color: var(--muted);
        pointer-events: none;
    }

    .project-select-wrapper select {
        appearance: none;
        width: 100%;
        height: 46px;
        padding: 0 2.8rem 0 1rem;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: var(--white);
        color: var(--dark);
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.4;
        outline: none;
        box-shadow: 0 3px 12px rgba(19, 50, 31, 0.03);
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .project-select-wrapper select:focus {
        border-color: rgba(34, 184, 102, 0.55);
        box-shadow: 0 0 0 4px rgba(34, 184, 102, 0.1);
    }

    .timeline-filters {
        display: none;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .timeline-filters.show {
        display: flex;
    }

    .filter-button {
        min-height: 38px;
        padding: 0.55rem 0.95rem;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.85);
        color: var(--muted);
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1.3;
        cursor: pointer;
        transition:
            color 0.2s ease,
            border-color 0.2s ease,
            background 0.2s ease,
            transform 0.2s ease;
    }

    .filter-button:hover {
        color: var(--dark);
        border-color: #ccd5cf;
        transform: translateY(-1px);
    }

    .filter-button.active {
        color: var(--green-dark);
        border-color: rgba(34, 184, 102, 0.35);
        background: var(--green-soft);
        box-shadow: 0 4px 12px rgba(34, 184, 102, 0.08);
    }

    .filter-button[data-filter="in-progress"].active {
        color: var(--blue-dark);
        border-color: rgba(59, 130, 246, 0.3);
        background: var(--blue-soft);
    }

    .filter-button[data-filter="upcoming"].active {
        color: var(--amber-dark);
        border-color: rgba(245, 158, 11, 0.35);
        background: var(--amber-soft);
    }

    /* ================================
       MAIN LAYOUT
    ================================ */

    .timeline-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 390px;
        align-items: start;
        gap: 1.3rem;
    }

    .timeline-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow);
    }

    .phases-card {
        min-width: 0;
        padding: 1.5rem;
    }

    .card-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1.1rem;
        border-bottom: 1px solid var(--border);
    }

    .card-title {
        margin: 0;
        color: var(--dark);
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: -0.01em;
    }

    .card-subtitle {
        margin: 0.3rem 0 0;
        color: var(--muted);
        font-size: 0.68rem;
        font-weight: 400;
        line-height: 1.5;
    }

    .overall-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        flex-shrink: 0;
        padding: 0.48rem 0.7rem;
        border-radius: 999px;
        background: var(--green-soft);
        color: var(--green-dark);
        font-size: 0.64rem;
        font-weight: 600;
        line-height: 1;
    }

    .overall-pill::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--green);
    }

    /* ================================
       STATUS SUMMARY
    ================================ */

    .status-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        padding: 1.2rem 0;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    .summary-icon {
        display: grid;
        place-items: center;
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--green-soft);
        color: var(--green);
        font-size: 0.95rem;
    }

    .summary-item.in-progress .summary-icon {
        background: var(--blue-soft);
        color: var(--blue);
    }

    .summary-item.upcoming .summary-icon {
        background: var(--amber-soft);
        color: var(--amber);
    }

    .summary-label {
        color: var(--muted);
        font-size: 0.58rem;
        font-weight: 600;
        letter-spacing: 0.07em;
        line-height: 1.4;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .summary-value-wrapper {
        display: flex;
        align-items: baseline;
        gap: 0.3rem;
        margin-top: 0.15rem;
    }

    .summary-value {
        color: var(--dark);
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1;
    }

    .summary-unit {
        color: var(--muted);
        font-size: 0.62rem;
        font-weight: 400;
    }

    /* ================================
       PHASE ITEMS
    ================================ */

    .phase-list {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .phase-item {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.9rem;
        min-width: 0;
        padding: 0.9rem 0.95rem;
        border: 1px solid transparent;
        border-radius: 13px;
        background: var(--amber-soft);
        transition:
            transform 0.2s ease,
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .phase-item:hover {
        transform: translateY(-1px);
        border-color: rgba(245, 158, 11, 0.25);
        box-shadow: 0 8px 18px rgba(19, 50, 31, 0.05);
    }

    .phase-item.completed {
        background: #f1faf5;
    }

    .phase-item.completed:hover {
        border-color: rgba(34, 184, 102, 0.25);
    }

    .phase-item.in-progress {
        background: #f2f6ff;
    }

    .phase-item.in-progress:hover {
        border-color: rgba(59, 130, 246, 0.25);
    }

    .phase-index {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        border: 1px solid rgba(245, 158, 11, 0.35);
        border-radius: 50%;
        background: var(--white);
        color: var(--amber);
        font-size: 0.7rem;
        font-weight: 700;
        line-height: 1;
    }

    .phase-item.completed .phase-index {
        border-color: var(--green);
        background: var(--green);
        color: var(--white);
    }

    .phase-item.in-progress .phase-index {
        border-color: rgba(59, 130, 246, 0.35);
        color: var(--blue);
    }

    .phase-main {
        min-width: 0;
    }

    .phase-name {
        overflow: hidden;
        color: var(--dark);
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: -0.005em;
        text-overflow: ellipsis;
    }

    .phase-dates {
        margin-top: 0.2rem;
        color: var(--muted);
        font-size: 0.62rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .phase-progress-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.7rem;
        margin-top: 0.6rem;
    }

    .phase-progress-track {
        height: 5px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(105, 115, 109, 0.17);
    }

    .phase-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: var(--green);
        transition: width 0.45s ease;
    }

    .phase-progress-text {
        color: var(--muted);
        font-size: 0.6rem;
        font-weight: 500;
        line-height: 1;
        white-space: nowrap;
    }

    .phase-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        min-height: 27px;
        padding: 0.38rem 0.55rem;
        border: 1px solid rgba(245, 158, 11, 0.28);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.7);
        color: var(--amber-dark);
        font-size: 0.53rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        line-height: 1;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .phase-item.completed .phase-status {
        border-color: rgba(34, 184, 102, 0.28);
        color: var(--green-dark);
    }

    .phase-item.in-progress .phase-status {
        border-color: rgba(59, 130, 246, 0.28);
        color: var(--blue-dark);
    }

    /* ================================
       RIGHT COLUMN
    ================================ */

    .timeline-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.3rem;
        min-width: 0;
    }

    .progress-card,
    .milestones-card {
        padding: 1.3rem;
    }

    .panel-title {
        margin: 0;
        color: var(--dark);
        font-size: 0.88rem;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: -0.01em;
    }

    .progress-content {
        display: grid;
        grid-template-columns: 145px minmax(0, 1fr);
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .circular-progress {
        position: relative;
        width: 138px;
        height: 138px;
        margin: auto;
    }

    .circular-progress svg {
        display: block;
        width: 100%;
        height: 100%;
    }

    .circular-progress-text {
        position: absolute;
        inset: 0;
        display: grid;
        place-content: center;
        text-align: center;
    }

    .circular-progress-value {
        color: var(--dark);
        font-size: 1.6rem;
        font-weight: 700;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .circular-progress-label {
        margin-top: 0.35rem;
        color: var(--muted);
        font-size: 0.55rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        line-height: 1;
        text-transform: uppercase;
    }

    .progress-legend {
        display: flex;
        flex-direction: column;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-height: 40px;
        border-bottom: 1px solid var(--border);
        color: #455048;
        font-size: 0.68rem;
        font-weight: 500;
        line-height: 1.4;
    }

    .legend-item:last-child {
        border-bottom: 0;
    }

    .legend-dot {
        flex: 0 0 8px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .legend-item.completed .legend-dot {
        background: var(--green);
    }

    .legend-item.in-progress .legend-dot {
        background: var(--blue);
    }

    .legend-item.upcoming .legend-dot {
        background: var(--amber);
    }

    /* ================================
       MILESTONES
    ================================ */

    .milestone-list {
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
        margin-top: 1rem;
    }

    .milestone-item {
        display: grid;
        grid-template-columns: 30px minmax(0, 1fr);
        gap: 0.7rem;
        padding: 0.85rem;
        border-left: 3px solid var(--green);
        border-radius: 10px;
        background: var(--green-soft);
    }

    .milestone-item.in-progress {
        border-left-color: var(--blue);
        background: var(--blue-soft);
    }

    .milestone-item.upcoming {
        border-left-color: var(--amber);
        background: var(--amber-soft);
    }

    .milestone-icon {
        display: grid;
        place-items: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.75);
        color: var(--green);
        font-size: 0.78rem;
    }

    .milestone-item.in-progress .milestone-icon {
        color: var(--blue);
    }

    .milestone-item.upcoming .milestone-icon {
        color: var(--amber);
    }

    .milestone-badge {
        display: inline-flex;
        align-items: center;
        min-height: 22px;
        padding: 0.27rem 0.46rem;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.75);
        color: var(--green-dark);
        font-size: 0.5rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        line-height: 1;
        text-transform: uppercase;
    }

    .milestone-item.in-progress .milestone-badge {
        color: var(--blue-dark);
    }

    .milestone-item.upcoming .milestone-badge {
        color: var(--amber-dark);
    }

    .milestone-title {
        margin-top: 0.42rem;
        color: var(--dark);
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .milestone-description {
        margin-top: 0.15rem;
        color: var(--muted);
        font-size: 0.59rem;
        font-weight: 400;
        line-height: 1.5;
    }

    .target-completion {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 0.95rem;
        padding-top: 0.9rem;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 0.62rem;
        font-weight: 500;
        line-height: 1.4;
    }

    .target-completion strong {
        color: var(--dark);
        font-size: 0.66rem;
        font-weight: 600;
        text-align: right;
    }

    /* ================================
       EMPTY STATE
    ================================ */

    .timeline-empty-state {
        display: grid;
        place-items: center;
        min-height: 390px;
        padding: 2rem;
        border: 1px dashed #d8dfda;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.62);
        color: var(--muted);
        text-align: center;
    }

    .empty-content {
        max-width: 340px;
    }

    .empty-icon {
        display: grid;
        place-items: center;
        width: 54px;
        height: 54px;
        margin: 0 auto 0.9rem;
        border-radius: 50%;
        background: var(--green-soft);
        color: var(--green);
        font-size: 1.25rem;
    }

    .empty-title {
        margin: 0;
        color: var(--dark);
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.4;
    }

    .empty-description {
        margin: 0.45rem 0 0;
        font-size: 0.68rem;
        font-weight: 400;
        line-height: 1.6;
    }

    .filtered-empty-state {
        min-height: 180px;
        border-radius: 12px;
        box-shadow: none;
    }

    /* ================================
       RESPONSIVE
    ================================ */

    @media (max-width: 1180px) {
        .timeline-layout {
            grid-template-columns: minmax(0, 1fr) 340px;
        }

        .progress-content {
            grid-template-columns: 125px minmax(0, 1fr);
        }

        .circular-progress {
            width: 120px;
            height: 120px;
        }
    }

    @media (max-width: 992px) {
        .timeline-layout {
            grid-template-columns: 1fr;
        }

        .timeline-sidebar {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px) {
        .timeline-toolbar {
            align-items: stretch;
        }

        .project-select-wrapper {
            width: 100%;
        }

        .timeline-filters {
            width: 100%;
        }

        .filter-button {
            flex: 1 1 calc(50% - 0.4rem);
        }

        .phases-card,
        .progress-card,
        .milestones-card {
            padding: 1rem;
            border-radius: 15px;
        }

        .status-summary {
            gap: 0.6rem;
        }

        .summary-item {
            align-items: flex-start;
            gap: 0.5rem;
        }

        .summary-icon {
            flex-basis: 30px;
            width: 30px;
            height: 30px;
            font-size: 0.8rem;
        }

        .summary-label {
            font-size: 0.5rem;
        }

        .phase-item {
            grid-template-columns: 32px minmax(0, 1fr);
            gap: 0.7rem;
        }

        .phase-status {
            grid-column: 2;
            justify-self: start;
        }

        .timeline-sidebar {
            display: flex;
        }
    }

    @media (max-width: 480px) {
        .card-heading {
            flex-direction: column;
        }

        .status-summary {
            grid-template-columns: 1fr;
        }

        .progress-content {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page active" id="pg-timeline">

    <div class="timeline-toolbar">

        <div class="project-select-wrapper">
            <select id="projectSelector" onchange="selectProject(this.value)">
                <option value="">Select a project</option>

                @foreach($projectsWithStats as $project)
                    <option value="{{ $project['id'] }}">
                        {{ $project['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="timeline-filters" id="timelineFilters">

            <button
                type="button"
                class="filter-button active"
                data-filter="all"
                onclick="setTimelineFilter('all')">
                All
            </button>

            <button
                type="button"
                class="filter-button"
                data-filter="in-progress"
                onclick="setTimelineFilter('in-progress')">
                In Progress
            </button>

            <button
                type="button"
                class="filter-button"
                data-filter="completed"
                onclick="setTimelineFilter('completed')">
                Completed
            </button>

            <button
                type="button"
                class="filter-button"
                data-filter="upcoming"
                onclick="setTimelineFilter('upcoming')">
                Upcoming
            </button>

        </div>
    </div>

    <div id="timelineContainer">

        <div class="timeline-empty-state">
            <div class="empty-content">

                <div class="empty-icon">
                    <i class="bi bi-calendar3"></i>
                </div>

                <h3 class="empty-title">
                    Choose a project
                </h3>

                <p class="empty-description">
                    Select a project above to view its construction phases,
                    progress and milestone status.
                </p>

            </div>
        </div>

    </div>
</div>

<script>
    const projectsData = @json($projectsWithStats);

    let selectedProject = null;
    let activeTimelineFilter = 'all';

    /**
     * Escape dynamic text before inserting it into HTML.
     */
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Keep percentage between 0 and 100.
     */
    function clampPercentage(value) {
        const percentage = Number(value);

        if (!Number.isFinite(percentage)) {
            return 0;
        }

        return Math.min(100, Math.max(0, percentage));
    }

    /**
     * Convert phase statuses into consistent values.
     */
    function normalizeStatus(status) {
        const normalizedStatus = String(status || 'planning')
            .trim()
            .toLowerCase()
            .replace(/_/g, '-')
            .replace(/\s+/g, '-');

        if (normalizedStatus === 'completed') {
            return 'completed';
        }

        if (
            normalizedStatus === 'in-progress' ||
            normalizedStatus === 'ongoing'
        ) {
            return 'in-progress';
        }

        return 'upcoming';
    }

    /**
     * Format dates safely.
     */
    function formatDate(dateValue, options) {
        if (!dateValue) {
            return 'Not set';
        }

        const date = new Date(dateValue);

        if (Number.isNaN(date.getTime())) {
            return 'Not set';
        }

        return date.toLocaleDateString('en-US', options);
    }

    /**
     * Select and display a project.
     */
    function selectProject(projectId) {
        if (!projectId) {
            selectedProject = null;
            activeTimelineFilter = 'all';

            updateFilterButtons();

            document
                .getElementById('timelineFilters')
                .classList.remove('show');

            renderEmptyState();

            return;
        }

        selectedProject = projectsData.find(
            project => String(project.id) === String(projectId)
        );

        if (!selectedProject) {
            return;
        }

        activeTimelineFilter = 'all';

        updateFilterButtons();

        document
            .getElementById('timelineFilters')
            .classList.add('show');

        renderTimeline(selectedProject);
    }

    /**
     * Change the active phase filter.
     */
    function setTimelineFilter(filter) {
        activeTimelineFilter = filter;

        updateFilterButtons();

        if (selectedProject) {
            renderTimeline(selectedProject);
        }
    }

    /**
     * Update filter button appearance.
     */
    function updateFilterButtons() {
        document
            .querySelectorAll('.filter-button')
            .forEach(button => {
                button.classList.toggle(
                    'active',
                    button.dataset.filter === activeTimelineFilter
                );
            });
    }

    /**
     * Display the default empty state.
     */
    function renderEmptyState() {
        document.getElementById('timelineContainer').innerHTML = `
            <div class="timeline-empty-state">
                <div class="empty-content">

                    <div class="empty-icon">
                        <i class="bi bi-calendar3"></i>
                    </div>

                    <h3 class="empty-title">
                        Choose a project
                    </h3>

                    <p class="empty-description">
                        Select a project above to view its construction phases,
                        progress and milestone status.
                    </p>

                </div>
            </div>
        `;
    }

    /**
     * Calculate project status counts.
     */
    function getProjectStatistics(project, phases) {
        const calculatedStatistics = phases.reduce(
            (statistics, phase) => {
                const status = normalizeStatus(phase.display_status);

                if (status === 'completed') {
                    statistics.completed++;
                }

                if (status === 'in-progress') {
                    statistics.inProgress++;
                }

                if (status === 'upcoming') {
                    statistics.upcoming++;
                }

                return statistics;
            },
            {
                completed: 0,
                inProgress: 0,
                upcoming: 0
            }
        );

        return {
            completed: Number(
                project.completedPhases ??
                calculatedStatistics.completed
            ),

            inProgress: Number(
                project.inProgressPhases ??
                calculatedStatistics.inProgress
            ),

            upcoming: Number(
                project.upcomingPhases ??
                calculatedStatistics.upcoming
            )
        };
    }

    /**
     * Filter project phases.
     */
    function getFilteredPhases(phases) {
        if (activeTimelineFilter === 'all') {
            return phases;
        }

        return phases.filter(
            phase =>
                normalizeStatus(phase.display_status) ===
                activeTimelineFilter
        );
    }

    /**
     * Render the selected project timeline.
     */
    function renderTimeline(project) {
        const phases = Array.isArray(project.phases)
            ? project.phases
            : [];

        const filteredPhases = getFilteredPhases(phases);

        const statistics = getProjectStatistics(
            project,
            phases
        );

        const projectProgress = clampPercentage(
            project.progress
        );

        const projectName = escapeHtml(
            project.name
        );

        const targetMonth = formatDate(
            project.targetEndDate,
            {
                month: 'short',
                year: 'numeric'
            }
        );

        const targetFullDate = formatDate(
            project.targetEndDate,
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }
        );

        const phaseItems = filteredPhases.length
            ? filteredPhases
                .map(phase => {
                    const originalIndex = phases.indexOf(phase);

                    return renderPhaseItem(
                        phase,
                        originalIndex + 1
                    );
                })
                .join('')
            : renderFilteredEmptyState();

        document.getElementById('timelineContainer').innerHTML = `
            <div class="timeline-layout">

                <section class="timeline-card phases-card">

                    <div class="card-heading">

                        <div>
                            <h2 class="card-title">
                                Construction Phases – ${projectName}
                            </h2>

                            <p class="card-subtitle">
                                Target: ${escapeHtml(targetMonth)}
                            </p>
                        </div>

                        <span class="overall-pill">
                            ${Math.round(projectProgress)}% overall
                        </span>

                    </div>

                    <div class="status-summary">

                        ${renderSummaryItem(
                            'completed',
                            'bi-check-lg',
                            'Completed',
                            statistics.completed,
                            statistics.completed === 1
                                ? 'phase'
                                : 'phases'
                        )}

                        ${renderSummaryItem(
                            'in-progress',
                            'bi-clock',
                            'In Progress',
                            statistics.inProgress,
                            statistics.inProgress === 1
                                ? 'phase'
                                : 'phases'
                        )}

                        ${renderSummaryItem(
                            'upcoming',
                            'bi-calendar3',
                            'Upcoming',
                            statistics.upcoming,
                            statistics.upcoming === 1
                                ? 'phase'
                                : 'phases'
                        )}

                    </div>

                    <div class="phase-list">
                        ${phaseItems}
                    </div>

                </section>

                <aside class="timeline-sidebar">

                    <section class="timeline-card progress-card">

                        <h3 class="panel-title">
                            Overall Progress
                        </h3>

                        <div class="progress-content">

                            <div class="circular-progress">
                                ${generateCircularProgress(projectProgress)}
                            </div>

                            <div class="progress-legend">

                                <div class="legend-item completed">
                                    <span class="legend-dot"></span>

                                    <span>
                                        ${statistics.completed} Done
                                    </span>
                                </div>

                                <div class="legend-item in-progress">
                                    <span class="legend-dot"></span>

                                    <span>
                                        ${statistics.inProgress} In Progress
                                    </span>
                                </div>

                                <div class="legend-item upcoming">
                                    <span class="legend-dot"></span>

                                    <span>
                                        ${statistics.upcoming} Upcoming
                                    </span>
                                </div>

                            </div>

                        </div>

                    </section>

                    <section class="timeline-card milestones-card">

                        <h3 class="panel-title">
                            Milestone Flags
                        </h3>

                        <div class="milestone-list">
                            ${renderMilestones(
                                phases,
                                statistics
                            )}
                        </div>

                        <div class="target-completion">

                            <span>
                                Target completion
                            </span>

                            <strong>
                                ${escapeHtml(targetFullDate)}
                            </strong>

                        </div>

                    </section>

                </aside>

            </div>
        `;
    }

    /**
     * Render one status summary item.
     */
    function renderSummaryItem(
        className,
        iconClass,
        label,
        value,
        unit
    ) {
        return `
            <div class="summary-item ${className}">

                <span class="summary-icon">
                    <i class="bi ${iconClass}"></i>
                </span>

                <div>

                    <div class="summary-label">
                        ${label}
                    </div>

                    <div class="summary-value-wrapper">

                        <span class="summary-value">
                            ${value}
                        </span>

                        <span class="summary-unit">
                            ${unit}
                        </span>

                    </div>

                </div>

            </div>
        `;
    }

    /**
     * Render one construction phase.
     */
    function renderPhaseItem(phase, phaseNumber) {
        const status = normalizeStatus(
            phase.display_status
        );

        const percentage = clampPercentage(
            phase.completion_percentage
        );

        const startDate = formatDate(
            phase.planned_start_date,
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }
        );

        const endDate = formatDate(
            phase.planned_end_date,
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }
        );

        const statusInformation = {
            completed: {
                label: 'Completed',
                icon: 'bi-check-circle'
            },

            'in-progress': {
                label: 'In Progress',
                icon: 'bi-arrow-repeat'
            },

            upcoming: {
                label: 'Upcoming',
                icon: 'bi-clock'
            }
        }[status];

        const indexContent =
            status === 'completed'
                ? '<i class="bi bi-check-lg"></i>'
                : phaseNumber;

        return `
            <article class="phase-item ${status}">

                <div class="phase-index">
                    ${indexContent}
                </div>

                <div class="phase-main">

                    <div class="phase-name">
                        ${escapeHtml(phase.phase_name)}
                    </div>

                    <div class="phase-dates">
                        ${escapeHtml(startDate)}
                        –
                        ${escapeHtml(endDate)}
                    </div>

                    <div class="phase-progress-row">

                        <div
                            class="phase-progress-track"
                            aria-label="${percentage}% complete">

                            <div
                                class="phase-progress-fill"
                                style="width: ${percentage}%;">
                            </div>

                        </div>

                        <span class="phase-progress-text">
                            ${Math.round(percentage)}% Complete
                        </span>

                    </div>

                </div>

                <span class="phase-status">

                    <i class="bi ${statusInformation.icon}"></i>

                    ${statusInformation.label}

                </span>

            </article>
        `;
    }

    /**
     * Render milestone cards.
     */
    function renderMilestones(phases, statistics) {
        const milestones = [];

        if (statistics.completed > 0) {
            milestones.push(`
                <div class="milestone-item">

                    <span class="milestone-icon">
                        <i class="bi bi-check-lg"></i>
                    </span>

                    <div>

                        <span class="milestone-badge">
                            Completed
                        </span>

                        <div class="milestone-title">
                            ${statistics.completed}
                            Phase${statistics.completed === 1 ? '' : 's'}
                            Done
                        </div>

                        <div class="milestone-description">
                            Completed successfully
                        </div>

                    </div>

                </div>
            `);
        }

        const currentPhase = phases.find(
            phase =>
                normalizeStatus(phase.display_status) ===
                'in-progress'
        );

        if (currentPhase) {
            const currentPercentage = clampPercentage(
                currentPhase.completion_percentage
            );

            milestones.push(`
                <div class="milestone-item in-progress">

                    <span class="milestone-icon">
                        <i class="bi bi-flag"></i>
                    </span>

                    <div>

                        <span class="milestone-badge">
                            In Progress
                        </span>

                        <div class="milestone-title">
                            ${escapeHtml(currentPhase.phase_name)}
                        </div>

                        <div class="milestone-description">
                            ${Math.round(currentPercentage)}% complete
                        </div>

                    </div>

                </div>
            `);
        }

        const upcomingPhase = phases.find(
            phase =>
                normalizeStatus(phase.display_status) ===
                'upcoming'
        );

        if (upcomingPhase) {
            const upcomingDate = formatDate(
                upcomingPhase.planned_start_date,
                {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                }
            );

            milestones.push(`
                <div class="milestone-item upcoming">

                    <span class="milestone-icon">
                        <i class="bi bi-clock"></i>
                    </span>

                    <div>

                        <span class="milestone-badge">
                            Upcoming
                        </span>

                        <div class="milestone-title">
                            ${escapeHtml(upcomingPhase.phase_name)}
                        </div>

                        <div class="milestone-description">
                            Starting ${escapeHtml(upcomingDate)}
                        </div>

                    </div>

                </div>
            `);
        }

        if (milestones.length === 0) {
            milestones.push(`
                <div class="milestone-item upcoming">

                    <span class="milestone-icon">
                        <i class="bi bi-flag"></i>
                    </span>

                    <div>

                        <span class="milestone-badge">
                            Timeline
                        </span>

                        <div class="milestone-title">
                            No milestone data yet
                        </div>

                        <div class="milestone-description">
                            Add phases to display milestone updates.
                        </div>

                    </div>

                </div>
            `);
        }

        return milestones.join('');
    }

    /**
     * Render empty filter result.
     */
    function renderFilteredEmptyState() {
        return `
            <div class="timeline-empty-state filtered-empty-state">

                <div class="empty-content">

                    <div class="empty-icon">
                        <i class="bi bi-filter"></i>
                    </div>

                    <h3 class="empty-title">
                        No matching phases
                    </h3>

                    <p class="empty-description">
                        There are no phases under the selected status.
                    </p>

                </div>

            </div>
        `;
    }

    /**
     * Generate the circular progress chart.
     */
    function generateCircularProgress(percentage) {
        const radius = 46;
        const circumference = 2 * Math.PI * radius;

        const strokeOffset =
            circumference -
            (percentage / 100) * circumference;

        return `
            <svg
                viewBox="0 0 120 120"
                aria-label="${Math.round(percentage)}% overall progress">

                <circle
                    cx="60"
                    cy="60"
                    r="${radius}"
                    fill="none"
                    stroke="#e8ece9"
                    stroke-width="9">
                </circle>

                <circle
                    cx="60"
                    cy="60"
                    r="${radius}"
                    fill="none"
                    stroke="#22b866"
                    stroke-width="9"
                    stroke-linecap="round"
                    stroke-dasharray="${circumference}"
                    stroke-dashoffset="${strokeOffset}"
                    transform="rotate(-90 60 60)"
                    style="transition: stroke-dashoffset 0.55s ease;">
                </circle>

            </svg>

            <div class="circular-progress-text">

                <div class="circular-progress-value">
                    ${Math.round(percentage)}%
                </div>

                <div class="circular-progress-label">
                    Progress
                </div>

            </div>
        `;
    }

    /**
     * Automatically display the first project.
     * Remove this section to require manual selection.
     */
    document.addEventListener('DOMContentLoaded', function () {
        const projectSelector =
            document.getElementById('projectSelector');

        if (
            projectSelector &&
            projectSelector.options.length > 1
        ) {
            projectSelector.selectedIndex = 1;
            selectProject(projectSelector.value);
        }
    });
</script>
@endsection
```
