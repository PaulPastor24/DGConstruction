@forelse($projects as $summary)
    @php
        $projectItem = $summary['project'];
        $percent = max(0, min(100, (float) ($summary['completion'] ?? 0)));
        $phaseName = optional($summary['current_phase'])->phase_name ?? $projectItem->current_phase ?? 'Planning & Design';
        $projectImage = 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&w=900&q=80';
        
        $statusLabel = match ($projectItem->status) {
            'completed' => 'Completed',
            'ongoing' => 'On Track',
            'planning' => 'Planning',
            'on_hold' => 'Delayed',
            'delayed' => 'Delayed',
            default => ucfirst((string) ($projectItem->status ?? 'Planning')),
        };
        
        $statusClass = match ($projectItem->status) {
            'completed' => 'status-completed',
            'ongoing' => 'status-on-track',
            'planning' => 'status-planning',
            'on_hold' => 'status-delayed',
            'delayed' => 'status-delayed',
            default => 'status-planning',
        };
        
        $scheduleHealth = $projectItem->status === 'completed' ? 'Completed' : ($projectItem->status === 'on_hold' ? 'At Risk' : 'On Track');
        
        // DYNAMIC ATTRIBUTE RESOLUTION FIX: Reads live location text without breaking or using dummy values
        $projectLocation = trim((string) ($projectItem->project_location ?? $projectItem->location ?? $projectItem->location_address ?? ''));
        
        $startDate = $projectItem->start_date ? $projectItem->start_date->format('M d, Y') : 'TBD';
        $targetEndDate = $projectItem->target_end_date ? $projectItem->target_end_date->format('M d, Y') : 'TBD';
        $daysRemaining = $projectItem->target_end_date ? max(0, $projectItem->target_end_date->diffInDays(now())) : 0;
        $projectManager = optional($projectItem->engineer)->name ?? 'Unassigned';
        $siteSupervisor = optional($projectItem->activeSupervisor)->name ?? 'Not assigned';
        
        $latestUpdate = match (true) {
            $projectItem->status === 'completed' => 'Structural works have been completed and the project is now moving into final closeout and client handover.',
            $projectItem->status === 'on_hold' => 'Project activities are temporarily paused while the team addresses site constraints and realigns the next milestone plan.',
            default => "{$phaseName} is progressing according to schedule. Structural works remain on track and the next milestone is expected to be delivered as planned.",
        };
    @endphp

    <article class="project-dashboard-tile" data-project-card data-bs-toggle="modal" data-bs-target="#projectDetailModal-{{ $projectItem->project_id }}" tabindex="0" role="button" aria-label="Open details for {{ $projectItem->project_name }}">
        <div class="project-dashboard-cover">
            <img src="{{ $projectImage }}" alt="{{ $projectItem->project_name }} thumbnail" class="project-dashboard-image">
            <div class="project-dashboard-cover-overlay"></div>
            <span class="project-dashboard-status {{ $statusClass }}">
                <span class="status-indicator-dot"></span>{{ $statusLabel }}
            </span>
        </div>

        <div class="project-dashboard-content">
            <div class="project-dashboard-header-row">
                <h3 class="project-dashboard-title brand-dark-green-header">{{ $projectItem->project_name }}</h3>
                <div class="project-dashboard-progress-percentage-group">
                    <span class="progress-context-lbl">Overall Progress</span>
                    <strong class="project-dashboard-progress-percentage">{{ round($percent) }}%</strong>
                </div>
            </div>

            <div class="project-dashboard-progress-container">
                <div class="project-dashboard-progress-track">
                    <span class="project-dashboard-progress-bar-fill" style="width: {{ round($percent) }}%;"></span>
                </div>
            </div>

            <div class="project-dashboard-meta-row">
                <div class="project-dashboard-meta-item">
                    <span class="project-dashboard-meta-label">Current Phase</span>
                    <div class="project-dashboard-meta-value-wrapper">
                        <div class="meta-icon-container phase-icon-bg">
                            <i class="bi bi-building"></i>
                        </div>
                        <span class="project-dashboard-meta-value font-weight-bold-css brand-dark-green-header">{{ $phaseName }}</span>
                    </div>
                </div>

                <div class="project-dashboard-meta-item">
                    <span class="project-dashboard-meta-label">Target Completion</span>
                    <div class="project-dashboard-meta-value-wrapper">
                        <div class="meta-icon-container date-icon-bg">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <span class="project-dashboard-meta-value">{{ $targetEndDate }}</span>
                    </div>
                </div>

                <div class="project-dashboard-meta-item">
                    <span class="project-dashboard-meta-label">Location</span>
                    <div class="project-dashboard-meta-value-wrapper">
                        <div class="meta-icon-container location-icon-bg">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <span class="project-dashboard-meta-value text-truncated-css">
                            @if(!empty($projectLocation))
                                {{ $projectLocation }}
                            @else
                                <span class="text-muted italic-lbl">Location Pending</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="project-dashboard-action-wrapper">
                    <button type="button" class="project-dashboard-button-link" data-bs-toggle="modal" data-bs-target="#projectDetailModal-{{ $projectItem->project_id }}">
                        View Project <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </article>

    <div class="modal fade project-command-modal" id="projectDetailModal-{{ $projectItem->project_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content project-command-shell">
                
                <button type="button" class="project-command-modal-close-trigger" data-bs-dismiss="modal" aria-label="Close modal">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div class="project-command-body">
                    
                    <header class="project-command-header">
                        <div class="project-command-header-badge-row">
                            <span class="project-command-status-badge {{ $statusClass }}">
                                <span class="status-indicator-dot"></span>{{ $statusLabel }}
                            </span>
                            <span class="project-command-phase-text-badge">
                                <i class="bi bi-layers me-1 text-success-dg"></i>{{ $phaseName }}
                            </span>
                        </div>
                        <h2 class="project-command-modal-title brand-dark-green-header">{{ $projectItem->project_name }}</h2>
                    </header>

                    <section class="project-command-summary-panel-matrix">
                        
                        <div class="command-panel-card card-highlight-border">
                            <span class="command-panel-lbl">Overall Progress</span>
                            <div class="command-panel-main-val-group">
                                <strong class="command-panel-large-display-val text-success-dg">{{ round($percent) }}%</strong>
                            </div>
                            <div class="command-panel-bar-track">
                                <span class="command-panel-bar-fill-css" style="width: {{ round($percent) }}%;"></span>
                            </div>
                        </div>

                        <div class="command-panel-card">
                            <span class="command-panel-lbl">Current Phase</span>
                            <div class="command-panel-horizontal-split">
                                <div class="command-panel-circle-icon icon-phase-green">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div class="command-panel-split-copy">
                                    <strong class="command-panel-medium-display-val brand-dark-green-header">{{ $phaseName }}</strong>
                                    <span class="command-panel-subtext-lbl">Active Stage</span>
                                </div>
                            </div>
                        </div>

                        <div class="command-panel-card card-highlight-border">
                            <span class="command-panel-lbl">Schedule Health</span>
                            <div class="command-panel-main-val-group">
                                <span class="project-command-status-badge status-on-track project-command-schedule-badge">{{ $scheduleHealth }}</span>
                            </div>
                            <span class="command-panel-subtext-lbl mt-1">Timeline Baseline Status</span>
                        </div>

                        <div class="command-panel-card">
                            <span class="command-panel-lbl">Estimated Completion</span>
                            <div class="command-panel-horizontal-split">
                                <div class="command-panel-circle-icon icon-date-green">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div class="command-panel-split-copy">
                                    <strong class="command-panel-medium-display-val brand-dark-green-header">{{ $targetEndDate }}</strong>
                                    <span class="command-panel-subtext-lbl">{{ $daysRemaining }} Days Remaining</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="project-command-dual-grid-split">
                        
                        <section class="project-command-column-section">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h4 class="project-command-section-label brand-dark-green-header mb-0">
                                    <i class="bi bi-camera me-2 text-success-dg"></i>Construction Snapshot
                                </h4>
                                <span class="badge image-counter-badge font-monospace-lbl">1 OF 1 PHOTO</span>
                            </div>
                            <div class="project-command-media-card">
                                <img src="{{ $projectImage }}" alt="{{ $projectItem->project_name }} construction operational snapshot">
                            </div>
                        </section>

                        <section class="project-command-column-section">
                            <h4 class="project-command-section-label brand-dark-green-header mb-3">
                                <i class="bi bi-grid-1x2 me-2 text-success-dg"></i>Project Information
                            </h4>
                            <div class="project-info-untruncated-list">
                                
                                <div class="info-list-row-item">
                                    <div class="info-row-label-block">
                                        <i class="bi bi-geo-alt info-row-icon"></i>
                                        <span class="info-row-text-label">Location</span>
                                    </div>
                                    <div class="info-row-value-block">
                                        @if(!empty($projectLocation))
                                            {{ $projectLocation }}
                                        @else
                                            <span class="text-muted italic-lbl">Location Pending</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="info-list-row-item">
                                    <div class="info-row-label-block">
                                        <i class="bi bi-building info-row-icon"></i>
                                        <span class="info-row-text-label">Current Phase</span>
                                    </div>
                                    <div class="info-row-value-block font-semibold brand-dark-green-header">{{ $phaseName }}</div>
                                </div>

                                <div class="info-list-row-item">
                                    <div class="info-row-label-block">
                                        <i class="bi bi-person-badge info-row-icon"></i>
                                        <span class="info-row-text-label">Project Manager</span>
                                    </div>
                                    <div class="info-row-value-block">{{ $projectManager }}</div>
                                </div>

                                <div class="info-list-row-item">
                                    <div class="info-row-label-block">
                                        <i class="bi bi-cone-striped info-row-icon"></i>
                                        <span class="info-row-text-label">Site Supervisor</span>
                                    </div>
                                    <div class="info-row-value-block">{{ $siteSupervisor }}</div>
                                </div>

                                <div class="info-list-row-item">
                                    <div class="info-row-label-block">
                                        <i class="bi bi-card-text info-row-icon"></i>
                                        <span class="info-row-text-label">Description</span>
                                    </div>
                                    <div class="info-row-value-block">{{ $projectItem->description ?: 'No description available.' }}</div>
                                </div>

                            </div>
                        </section>
                    </div>

                    <section class="project-command-section project-command-fullwidth-update-wrap">
                        <div class="project-command-update-card dynamic-green-accent-card">
                            <div class="project-command-update-icon-wrap">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <div class="project-command-update-copy">
                                <span class="project-command-update-header-title">Latest Update</span>
                                <p class="project-command-update-text-paragraph">{{ $latestUpdate }}</p>
                                <span class="project-command-update-meta">Updated 2 days ago</span>
                            </div>
                        </div>
                    </section>

                </div>

                <footer class="project-command-footer">
                    <button type="button" class="project-command-button-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('client.timeline', ['project_id' => $projectItem->project_id]) }}" class="project-command-button-primary">
                        View Timeline
                    </a>
                </footer>

            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5 empty-gallery-state-container">
        <div class="mb-3 empty-state-icon-canvas"><i class="bi bi-folder2-open"></i></div>
        <h5 class="fw-bold mb-2 context-empty-title-css brand-dark-green-header">No Projects Found</h5>
        <p class="text-muted mb-0">No active records matched your current query or filter selections.</p>
    </div>
@endforelse

<style>
    /* --- ARBITRARY THEME ANCHORS (MATCHING D&G DESIGN SYSTEM VARIABLES) --- */
    :root {
        --dg-primary-green: #10b981;
        --dg-dark-green: #0f172a; /* Deep Slate Blue branding header accent color */
        --dg-muted-gray: #64748b;
        --dg-border-color: #e2e8f0;
        --dg-light-bg: #f8fafc;
        --dg-radius-lg: 16px;
        --dg-radius-xl: 24px;
        --dg-shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.03);
        --dg-shadow-lg: 0 30px 80px -15px rgba(15, 23, 42, 0.12);
        --dg-font-stack: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* BRAND TYPOGRAPHY CONTEXT DECORATOR */
    .brand-dark-green-header {
        color: var(--dg-dark-green) !important;
        font-family: var(--dg-font-stack);
    }
    .italic-lbl {
        font-style: italic;
    }

    /* --- PORTAL MAIN FEED INTERFACE ENFORCEMENTS --- */
    .project-dashboard-tile {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        overflow: hidden;
        box-shadow: var(--dg-shadow-sm);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        position: relative;
        height: 100%;
        width: 100%;
    }
    .project-dashboard-tile:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
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
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.02em;
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
        color: var(--dg-muted-gray);
        font-weight: 700;
    }
    .project-dashboard-progress-percentage {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dg-primary-green);
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
        background-color: var(--dg-primary-green);
        border-radius: 999px;
    }

    .project-dashboard-meta-row {
        display: grid;
        grid-template-columns: 1fr;
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
        color: var(--dg-muted-gray);
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
    .phase-icon-bg { background-color: #e6f7ed; color: var(--dg-primary-green); }
    .date-icon-bg { background-color: #e6f7ed; color: #10b981; }
    .location-icon-bg { background-color: #f1f5f9; color: #64748b; }

    .project-dashboard-meta-value {
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .font-weight-bold-css { font-weight: 700 !important; }
    .project-dashboard-action-wrapper {
        display: flex;
        justify-content: flex-end;
        padding-top: 6px;
    }
    
    /* BRANDED GREEN INTEGRATED ACTION BUTTON STYLING */
    .project-dashboard-button-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 44px;
        padding: 0 1.35rem;
        background: #10b981;
        border: 1px solid #10b981;
        color: #ffffff;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.14);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .project-dashboard-button-link:hover {
        background: #059669;
        border-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(16, 185, 129, 0.22);
    }
    .project-dashboard-button-link:active,
    .project-dashboard-button-link:focus {
        transform: translateY(0);
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.28);
    }

    /* --- ENTERPRISE MODAL INFRASTRUCTURE STYLING --- */
    .project-command-modal .modal-dialog {
        max-width: 1160px;
        width: 92%;
    }
    .project-command-shell {
        background: #ffffff;
        border: none;
        border-radius: var(--dg-radius-xl);
        box-shadow: var(--dg-shadow-lg);
        position: relative;
    }
    
    .modal.fade .project-command-shell {
        transform: scale(0.98) translateY(15px);
        opacity: 0;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease-out;
    }
    .modal.show .project-command-shell {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    .project-command-modal-close-trigger {
        position: absolute;
        top: 24px;
        right: 24px;
        z-index: 120;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: var(--dg-light-bg);
        border: 1px solid var(--dg-border-color);
        color: var(--dg-muted-gray);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .project-command-modal-close-trigger:hover {
        background-color: #e2e8f0;
        color: var(--dg-dark-green);
        transform: rotate(90deg);
    }

    .project-command-body {
        padding: 40px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .project-command-header {
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .project-command-header-badge-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .project-command-status-badge {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .project-command-phase-text-badge {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
        background-color: var(--dg-light-bg);
        border: 1px solid var(--dg-border-color);
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        display: inline-flex;
        align-items: center;
    }
    .text-success-dg { color: var(--dg-primary-green) !important; }
    
    .project-command-modal-title {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    /* Upper Summary KPI Row Panel Matrix */
    .project-command-summary-panel-matrix {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 32px;
        width: 100%;
    }
    .command-panel-card {
        background-color: #ffffff;
        border: 1px solid var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 115px;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .command-panel-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
    }
    .card-highlight-border {
        background-color: var(--dg-light-bg);
        border-color: #cbd5e1;
    }
    .command-panel-lbl {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dg-muted-gray);
        margin-bottom: 8px;
    }
    .command-panel-large-display-val {
        font-size: 2.2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1;
    }
    .command-panel-bar-track {
        width: 100%;
        height: 6px;
        background-color: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        margin-top: auto;
    }
    .command-panel-bar-fill-css {
        display: block;
        height: 100%;
        background-color: var(--dg-primary-green);
        border-radius: 999px;
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
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .icon-phase-green { background-color: #e6f7ed; color: var(--dg-primary-green); }
    .icon-health-green { background-color: #e6f7ed; color: var(--dg-primary-green); }
    .icon-date-green { background-color: #e6f7ed; color: var(--dg-primary-green); }

    .command-panel-split-copy {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .command-panel-medium-display-val {
        font-size: 0.95rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .command-panel-subtext-lbl {
        font-size: 0.75rem;
        color: var(--dg-muted-gray);
        font-weight: 500;
    }

    /* Asymmetric Splits Workspace Section */
    .project-command-dual-grid-split {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 32px;
        align-items: start;
        border-top: 1px solid var(--dg-border-color);
        padding-top: 28px;
    }
    .project-command-section-label {
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }
    .image-counter-badge {
        background-color: #e2e8f0;
        color: #475569;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
    }

    .project-command-media-card {
        width: 100%;
        border-radius: var(--dg-radius-lg);
        overflow: hidden;
        aspect-ratio: 16 / 9;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
        border: 1px solid var(--dg-border-color);
        background: var(--dg-light-bg);
    }
    .project-command-media-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* UNTRUNCATED PARAMETERS MULTI-ROW CONTROLLER LIST */
    .project-info-untruncated-list {
        display: flex;
        flex-direction: column;
        background-color: #ffffff;
        border: 1px solid var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        overflow: hidden;
    }
    .info-list-row-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid var(--dg-border-color);
        gap: 24px;
    }
    .info-list-row-item:last-child {
        border-bottom: none;
    }
    .info-list-row-item:nth-child(even) {
        background-color: var(--dg-light-bg);
    }
    .info-row-label-block {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
    .info-row-icon {
        color: var(--dg-primary-green);
        font-size: 1rem;
        width: 16px;
        text-align: center;
    }
    .info-row-text-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--dg-muted-gray);
    }
    .info-row-value-block {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        text-align: right;
        word-break: break-word;
    }
    .font-semibold { font-weight: 700 !important; }

    /* Bottom Update Log Banner Card Component */
    .project-command-fullwidth-update-wrap {
        border-top: 1px solid var(--dg-border-color);
        margin-top: 28px;
        padding-top: 28px;
        width: 100%;
    }
    .project-command-update-card.dynamic-green-accent-card {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 20px;
        background-color: #e6f7ed;
        border: 1px solid #a7f3d0;
        border-radius: var(--dg-radius-lg);
    }
    .dynamic-green-accent-card .project-command-update-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background-color: var(--dg-primary-green);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .dynamic-green-accent-card .project-command-update-header-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #065f46;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
        display: block;
    }
    .dynamic-green-accent-card .project-command-update-text-paragraph {
        margin: 0;
        font-size: 0.92rem;
        line-height: 1.5;
        color: #044e37;
        font-weight: 600;
    }
    .dynamic-green-accent-card .project-command-update-meta {
        display: inline-block;
        margin-top: 8px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #065f46;
        opacity: 0.8;
    }

    /* Footer Operations Controls Layout Components */
    .project-command-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        padding: 20px 40px 32px 40px;
        border-top: 1px solid var(--dg-border-color);
        background: #ffffff;
    }
    .project-command-button-secondary {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        padding: 0.6rem 1.4rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        min-height: 42px;
        transition: all 0.2s ease;
    }
    .project-command-button-secondary:hover {
        background: var(--dg-light-bg);
        color: var(--dg-dark-green);
    }
    
    /* MODAL VIEW TIMELINE TRIGGER OVERRIDE */
    .project-command-button-primary {
        background: #10b981;
        border: 1px solid #10b981;
        color: #ffffff;
        padding: 0.68rem 1.55rem;
        border-radius: 12px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.16);
        transition: all 0.2s ease;
    }
    .project-command-button-primary:hover {
        background: #059669;
        border-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(16, 185, 129, 0.24);
    }
    .project-command-button-primary:active,
    .project-command-button-primary:focus {
        transform: translateY(0);
        outline: none;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.28);
    }
    .project-command-schedule-badge {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0;
        min-height: auto;
        font-size: 1.05rem;
        color: #10b981;
    }

    /* --- RESPONSIVE STRUCTURAL OVERRIDES --- */
    @media (max-width: 1199px) {
        .project-command-summary-panel-matrix {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .project-command-dual-grid-split {
            grid-template-columns: 1fr;
            gap: 28px;
        }
    }

    @media (max-width: 991px) {
        .project-command-body { padding: 32px 24px; }
        .project-command-footer { padding: 16px 24px 24px 24px; }
    }

    @media (max-width: 767px) {
        .project-command-modal .modal-dialog {
            margin: 0;
            max-width: 100%;
            width: 100%;
        }
        .project-command-shell {
            border-radius: 0;
            min-height: 100vh;
        }
        .project-command-body {
            padding: 24px 16px;
        }
        .project-command-modal-title {
            font-size: 1.65rem;
        }
        .project-command-summary-panel-matrix {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .info-list-row-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            padding: 12px 16px;
        }
        .info-row-value-block {
            text-align: left;
            padding-left: 28px;
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

    .empty-gallery-state-container {
        background-color: #ffffff;
        border: 1px dashed var(--dg-border-color);
        border-radius: var(--dg-radius-lg);
        padding: 4rem 2rem !important;
    }
    .empty-state-icon-canvas { font-size: 3rem; color: var(--dg-muted-gray); }
</style>