@extends('layouts.supervisor')

@section('title', 'Project Timeline - Supervisor View')
@section('page_title', 'Project Timeline')

@push('styles')
<style>
    :root {
        --supervisor-primary: #2E7D32;
        --supervisor-secondary: #7CB342;
        --supervisor-accent: #C0CA33;
        --supervisor-highlight: #FDD835;
        --supervisor-bg: #FFF8E1;
        --supervisor-surface: #fff;
        --supervisor-border: #e7edd7;
        --supervisor-muted: #6b7280;
        --supervisor-text: #1f2937;
    }

    #pg-timeline {
        padding-bottom: 1.5rem;
    }

    .timeline-header {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-bottom: 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .timeline-project-selector {
        width: 100%;
        max-width: 340px;
        min-width: 240px;
        flex: 0 0 auto;
    }

    .timeline-project-selector select {
        width: 100%;
        padding: 0.8rem 0.9rem;
        border: 1px solid var(--supervisor-border);
        border-radius: 10px;
        font-size: 14px;
        font-family: 'Syne', sans-serif;
        font-weight: 600;
        color: var(--supervisor-text);
        background: var(--supervisor-surface);
        box-shadow: inset 0 1px 2px rgba(0,0,0,.03);
    }

    .timeline-container {
        background: var(--supervisor-surface);
        border-radius: 18px;
        border: 1px solid var(--supervisor-border);
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(46, 125, 50, 0.08);
    }

    .timeline-main {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 0;
        min-height: 500px;
    }

    .timeline-phases {
        padding: 2rem;
        border-right: 1px solid var(--supervisor-border);
        overflow-y: auto;
        background: linear-gradient(180deg, #fffef8 0%, var(--supervisor-bg) 100%);
    }

    .timeline-phases h3 {
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 0.5rem;
        color: var(--supervisor-text);
    }

    .timeline-header-info {
        font-size: 12px;
        color: var(--supervisor-muted);
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--supervisor-border);
    }

    .status-columns {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .status-column {
        flex: 1;
        min-width: 120px;
        background: #fff;
        border: 1px solid var(--supervisor-border);
        border-radius: 14px;
        padding: 0.9rem;
    }

    .status-column-header {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--supervisor-muted);
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-column-count {
        font-size: 20px;
        font-weight: 800;
        color: var(--supervisor-text);
        font-family: 'Syne', sans-serif;
    }

    .status-column.completed .status-column-count { color: var(--supervisor-primary); }
    .status-column.in-progress .status-column-count { color: var(--supervisor-secondary); }
    .status-column.upcoming .status-column-count { color: var(--supervisor-highlight); }

    .status-dot { width: 8px; height: 8px; border-radius: 50%; }

    .phase-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        position: relative;
        padding: 1rem;
        border-radius: 14px;
        background: #fff;
        transition: all 0.2s ease;
        border: 1px solid var(--supervisor-border);
    }

    .phase-item:hover {
        background: #fffef5;
        border-color: var(--supervisor-accent);
        box-shadow: 0 6px 16px rgba(46, 125, 50, 0.06);
    }

    .phase-item.completed { background: #f7fbf3; }
    .phase-item.in-progress { background: #fbfcef; }
    .phase-item.planning { background: #fffaf0; }

    .phase-indicator { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; min-width: 32px; }

    .phase-dot {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e7edd7;
        border: 2px solid #d7e3c6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--supervisor-text);
        transition: all 0.2s;
    }

    .phase-item.completed .phase-dot {
        background: var(--supervisor-primary);
        border-color: #1b5e20;
        color: #fff;
    }

    .phase-item.in-progress .phase-dot {
        background: var(--supervisor-secondary);
        border-color: #5f8d2c;
        color: #fff;
        box-shadow: 0 0 0 3px rgba(124, 179, 66, 0.12);
    }

    .phase-item.planning .phase-dot {
        background: var(--supervisor-highlight);
        border-color: #e1be17;
        color: #5a4700;
    }

    .phase-line {
        width: 2px;
        height: 60px;
        background: var(--supervisor-border);
    }

    .phase-item.completed .phase-line { background: var(--supervisor-primary); }
    .phase-item.in-progress .phase-line { background: var(--supervisor-secondary); }

    .phase-content { flex: 1; padding-top: 0.25rem; }
    .phase-title { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 14px; color: var(--supervisor-text); margin-bottom: 0.25rem; }
    .phase-dates { font-size: 12px; color: var(--supervisor-muted); margin-bottom: 0.5rem; }
    .phase-progress-bar { background: #eef4dd; height: 8px; border-radius: 999px; overflow: hidden; margin-bottom: 0.5rem; }
    .phase-progress-fill { height: 100%; background: linear-gradient(90deg, var(--supervisor-secondary), var(--supervisor-highlight)); transition: width 0.3s ease; }
    .phase-progress-text { font-size: 11px; font-weight: 600; color: var(--supervisor-muted); }

    .timeline-sidebar { padding: 2rem; display: flex; flex-direction: column; gap: 2rem; background: #fff; }
    .phase-progress-section { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; }
    .circular-progress { position: relative; width: 160px; height: 160px; margin-bottom: 1rem; }
    .circular-progress svg { width: 100%; height: 100%; }
    .circular-progress-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
    .circular-progress-value { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 32px; color: var(--supervisor-text); line-height: 1; }
    .circular-progress-label { font-size: 11px; font-weight: 600; color: var(--supervisor-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem; }
    .progress-legend { display: flex; flex-direction: column; gap: 0.75rem; width: 100%; }
    .legend-item { display: flex; align-items: center; gap: 0.75rem; font-size: 12px; font-weight: 600; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; }
    .legend-item.done .legend-dot { background: var(--supervisor-primary); }
    .legend-item.progress .legend-dot { background: var(--supervisor-secondary); }
    .legend-item.upcoming .legend-dot { background: var(--supervisor-highlight); }

    .milestones-section { border-top: 1px solid var(--supervisor-border); padding-top: 1.5rem; }
    .milestones-section h4 { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--supervisor-muted); margin-bottom: 1rem; }
    .milestone-item { display: flex; gap: 0.75rem; margin-bottom: 1rem; padding: 0.85rem; border-radius: 12px; background: #fff; border-left: 3px solid var(--supervisor-border); }
    .milestone-item.warning { background: #fffbe3; border-left-color: var(--supervisor-highlight); }
    .milestone-item.info { background: #f7fbf3; border-left-color: var(--supervisor-secondary); }
    .milestone-flag { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0; font-size: 16px; }
    .milestone-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.65rem; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
    .milestone-badge.warning { background: #fff7ca; color: #7a5a00; }
    .milestone-badge.info { background: #eef7de; color: #2e7d32; }
    .milestone-badge.milestone { background: #eef7de; color: #2e7d32; }
    .milestone-badge.completed { background: #e9f6e7; color: #1b5e20; }
    .milestone-badge.alert { background: #fff1f1; color: #9b1c1c; }
    .milestone-text { flex: 1; font-size: 12px; }
    .milestone-title { font-weight: 600; color: var(--supervisor-text); margin-bottom: 0.25rem; }
    .milestone-date { font-size: 11px; color: var(--supervisor-muted); }
    .no-project-selected { display: flex; align-items: center; justify-content: center; min-height: 400px; color: var(--supervisor-muted); font-size: 14px; text-align: center; }

    @media (max-width: 1024px) {
        .timeline-main { grid-template-columns: 1fr; }
        .timeline-phases { border-right: none; border-bottom: 1px solid var(--supervisor-border); }
        .timeline-sidebar { flex-direction: row; gap: 1rem; }
    }

    @media (max-width: 768px) {
        .timeline-header { flex-direction: column; align-items: stretch; }
        .timeline-project-selector { min-width: unset; }
        .timeline-phases { padding: 1.5rem; }
        .timeline-sidebar { padding: 1.5rem; flex-direction: column; gap: 1.5rem; }
        .circular-progress { width: 120px; height: 120px; }
        .circular-progress-value { font-size: 24px; }
    }
</style>
@endpush

@section('content')
<div class="page active" id="pg-timeline">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1">Project Timeline</h4>
                    <p class="text-muted mb-0 small">Track project phases, milestones, and progress for your assigned work.</p>
                </div>
                <div class="timeline-project-selector">
                    <select id="projectSelector" onchange="selectProject(this.value)" class="form-select">
                        <option value="">-- Select Project --</option>
                @foreach($projectsWithStats as $project)
                    <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="timeline-container" id="timelineContainer">
        <div class="no-project-selected">
            <div>
                <i class="bi bi-calendar-check" style="font-size: 48px; display: block; margin-bottom: 1rem; opacity: 0.3;"></i>
                Select a project to view timeline and milestones
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const projectsData = @json($projectsWithStats);

    function selectProject(projectId) {
        if (!projectId) {
            document.getElementById('timelineContainer').innerHTML = `
                <div class="no-project-selected">
                    <div>
                        <i class="bi bi-calendar-check" style="font-size: 48px; display: block; margin-bottom: 1rem; opacity: 0.3;"></i>
                        Select a project to view timeline and milestones
                    </div>
                </div>
            `;
            return;
        }

        const project = projectsData.find(p => p.id == projectId);
        if (!project) return;

        renderTimeline(project);
    }

    function renderTimeline(project) {
        const phases = project.phases || [];
        let phasesHTML = '<h3>Construction Phases – ' + project.name + '</h3>';
        
        // Add timeline info
        const targetDate = new Date(project.targetEndDate).toLocaleDateString('en-US', {
            month: 'short',
            year: 'numeric'
        });
        phasesHTML += `<div class="timeline-header-info">Target: ${targetDate}</div>`;
        
        // Add status columns
        phasesHTML += `
            <div class="status-columns">
                <div class="status-column completed">
                    <div class="status-column-header">
                        <span class="status-dot" style="background: var(--primary-green);"></span>
                        COMPLETED
                    </div>
                    <div class="status-column-count">${project.completedPhases}</div>
                    <div style="font-size: 11px; color: #6b7280; margin-top: 0.25rem;">phases</div>
                </div>
                <div class="status-column in-progress">
                    <div class="status-column-header">
                        <span class="status-dot" style="background: var(--blue);"></span>
                        IN PROGRESS
                    </div>
                    <div class="status-column-count">${project.inProgressPhases}</div>
                    <div style="font-size: 11px; color: #6b7280; margin-top: 0.25rem;">phase</div>
                </div>
                <div class="status-column upcoming">
                    <div class="status-column-header">
                        <span class="status-dot" style="background: var(--amber);"></span>
                        UPCOMING
                    </div>
                    <div class="status-column-count">${project.upcomingPhases}</div>
                    <div style="font-size: 11px; color: #6b7280; margin-top: 0.25rem;">phases</div>
                </div>
            </div>
        `;

        phases.forEach((phase, index) => {
            const isLastPhase = index === phases.length - 1;
            const statusClass = phase.display_status || 'planning';
            
            const startDate = new Date(phase.planned_start_date).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            const endDate = new Date(phase.planned_end_date).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            phasesHTML += `
                <div class="phase-item ${statusClass}">
                    <div class="phase-indicator">
                        <div class="phase-dot">${index + 1}</div>
                        ${!isLastPhase ? '<div class="phase-line"></div>' : ''}
                    </div>
                    <div class="phase-content">
                        <div class="phase-title">${phase.phase_name}</div>
                        <div class="phase-dates">${startDate} – ${endDate}</div>
                        <div class="phase-progress-bar">
                            <div class="phase-progress-fill" style="width: ${phase.completion_percentage}%"></div>
                        </div>
                        <div class="phase-progress-text">${phase.completion_percentage}% Complete</div>
                    </div>
                </div>
            `;
        });

        const progressPercentage = project.progress || 0;
        const circularProgress = generateCircularProgress(progressPercentage);

        // Generate milestones based on phase status
        let milestonesHTML = `
            <h4>🚩 Milestone Flags</h4>
        `;

        // Add dynamic milestones based on phase status
        if (project.completedPhases > 0) {
            milestonesHTML += `
                <div class="milestone-item info">
                    <div class="milestone-flag">✓</div>
                    <div class="milestone-text">
                        <div class="milestone-badge completed">
                            <i class="bi bi-check-circle"></i>
                            COMPLETED
                        </div>
                        <div class="milestone-title">${project.completedPhases} Phase${project.completedPhases > 1 ? 's' : ''} Done</div>
                        <div class="milestone-date">Completed successfully</div>
                    </div>
                </div>
            `;
        }

        if (project.inProgressPhases > 0) {
            const currentPhase = phases.find(p => p.display_status === 'in-progress');
            if (currentPhase) {
                milestonesHTML += `
                    <div class="milestone-item warning">
                        <div class="milestone-flag" style="color: #3b82f6;">⧖</div>
                        <div class="milestone-text">
                            <div class="milestone-badge info">
                                <i class="bi bi-hourglass-split"></i>
                                IN PROGRESS
                            </div>
                            <div class="milestone-title">${currentPhase.phase_name}</div>
                            <div class="milestone-date">${currentPhase.completion_percentage}% complete</div>
                        </div>
                    </div>
                `;
            }
        }

        // Add upcoming phase milestone
        const upcomingPhase = phases.find(p => p.display_status === 'planning' && project.inProgressPhases > 0);
        if (upcomingPhase) {
            milestonesHTML += `
                <div class="milestone-item">
                    <div class="milestone-flag" style="color: #f59e0b;">◆</div>
                    <div class="milestone-text">
                        <div class="milestone-badge warning">
                            <i class="bi bi-calendar"></i>
                            UPCOMING
                        </div>
                        <div class="milestone-title">${upcomingPhase.phase_name}</div>
                        <div class="milestone-date">Scheduled to start ${new Date(upcomingPhase.planned_start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</div>
                    </div>
                </div>
            `;
        }

        // Add project completion milestone
        milestonesHTML += `
            <div class="milestone-item">
                <div class="milestone-flag" style="color: #22c55e;">🎯</div>
                <div class="milestone-text">
                    <div class="milestone-badge milestone">
                        <i class="bi bi-flag"></i>
                        TARGET
                    </div>
                    <div class="milestone-title">Project Completion</div>
                    <div class="milestone-date">${new Date(project.targetEndDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                </div>
            </div>
        `;

        const timelineHTML = `
            <div class="timeline-main">
                <div class="timeline-phases">${phasesHTML}</div>
                <div class="timeline-sidebar">
                    <div class="phase-progress-section">
                        <div class="circular-progress">
                            ${circularProgress}
                        </div>
                        <div style="width: 100%;">
                            <div class="progress-legend">
                                <div class="legend-item done"><span class="legend-dot"></span><span>${project.completedPhases} Done</span></div>
                                <div class="legend-item progress"><span class="legend-dot"></span><span>${project.inProgressPhases} In Progress</span></div>
                                <div class="legend-item upcoming"><span class="legend-dot"></span><span>${project.upcomingPhases} Upcoming</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="milestones-section">${milestonesHTML}</div>
                </div>
            </div>
        `;

        document.getElementById('timelineContainer').innerHTML = timelineHTML;
    }

    function generateCircularProgress(percentage) {
        const radius = 45;
        const circumference = 2 * Math.PI * radius;
        const strokeDashoffset = circumference - (percentage / 100) * circumference;

        return `
            <svg viewBox="0 0 120 120" style="transform: rotate(-90deg);">
                <circle cx="60" cy="60" r="${radius}" fill="none" stroke="#e5e7eb" stroke-width="8" />
                <circle cx="60" cy="60" r="${radius}" fill="none" stroke="#22c55e" stroke-width="8" 
                        stroke-dasharray="${circumference}" stroke-dashoffset="${strokeDashoffset}"
                        style="transition: stroke-dashoffset 0.5s ease;" />
            </svg>
            <div class="circular-progress-text">
                <div class="circular-progress-value">${Math.round(percentage)}%</div>
                <div class="circular-progress-label">Progress</div>
            </div>
        `;
    }
</script>
@endsection
