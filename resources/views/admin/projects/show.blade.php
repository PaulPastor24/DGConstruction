{{--
    Shared "Project Details" content block.

    Used in two places:
      1. Fetched via AJAX and injected into #projectDetailsModalBody (index.blade.php)
         -> pass ['project' => $project, 'isModal' => true]
      2. @include'd directly on the full-page project show view (show.blade.php)
         -> pass ['project' => $project, 'isModal' => false]
--}}
@php
    $isModal = $isModal ?? true;
    $isArchived = strtolower((string) ($project->status ?? '')) === 'archived';
    $pdActiveSupervisor = $project->active_supervisor;
    $pdStart = $project->start_date;
    $pdTargetEnd = $project->target_end_date;
    $pdActualEnd = $project->actual_end_date;
    $pdDuration = $pdStart && $pdTargetEnd ? max(1, $pdStart->diffInDays($pdTargetEnd) + 1) : 0;
    $pdCompletedPhases = $project->phases()->where('status', 'completed')->count();
    $pdApprovedReports = $project->reports()->where('approval_status', 'approved')->count();
    $pdMaterialsCount = $project->projectMaterials()->count();
    $pdAttendanceCount = $project->attendanceLogs()->count();
    $pdCompletionReady = $project->completion_readiness_badge_class === 'success';
    $pdSetupReady = $project->setup_status_badge_class === 'success';
    $pdPhasePercent = $project->phase_count > 0 ? round(($pdCompletedPhases / max(1, $project->phase_count)) * 100) : 0;
    $pdStoredStatus = strtolower((string) ($project->getRawOriginal('status') ?? $project->status ?? 'planning'));
    $pdNormalizedStatus = match ($pdStoredStatus) {
        'in_progress', 'inprogress', 'ongoing', 'active' => 'ongoing',
        'completed', 'complete', 'finished' => 'completed',
        'on_hold', 'pending', 'planning', 'not_started', 'paused', 'delayed' => 'planning',
        'archived' => 'archived',
        default => 'planning',
    };
    $pdStatusLabel = match ($pdNormalizedStatus) {
        'ongoing' => 'In Progress',
        'completed' => 'Completed',
        'archived' => 'Archived',
        'planning' => 'Planning',
        default => 'Planning',
    };
    $pdStatusBadgeClass = match ($pdNormalizedStatus) {
        'ongoing' => 'success',
        'completed' => 'success',
        'archived' => 'warning',
        default => 'warning',
    };
@endphp

<div class="ps-header">
    <div class="ps-header-icon"><i class="bi bi-building"></i></div>
    <div class="ps-header-text">
        <h3 id="projectDetailsModalLabel">{{ $project->project_name ?? 'Project Details' }}</h3>
        <p>ID: #{{ $project->project_id ?? 'N/A' }} &middot; {{ $project->project_location ?? 'Location not specified' }}</p>
    </div>
    @if($isModal)
        <button type="button" class="btn-close ps-close" data-bs-dismiss="modal" aria-label="Close"></button>
    @endif
</div>

<div class="ps-body">
    <div class="ps-overview-card">
        <div class="ps-overview-top">
            <div class="ps-overview-identity">
                <div class="ps-overview-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div class="ps-overview-name">{{ $project->project_name }}</div>
                    <div class="ps-overview-id">Project ID: #{{ $project->project_id }}</div>
                    <div class="ps-overview-meta">
                        <span class="ps-meta-pill"><i class="bi bi-geo-alt"></i> {{ $project->project_location ?? 'Location not specified' }}</span>
                        <span class="ps-meta-pill"><i class="bi bi-person-workspace"></i> {{ $pdActiveSupervisor?->name ?? 'Supervisor pending' }}</span>
                    </div>
                </div>
            </div>
            <div class="ps-overview-status">
                <span class="ps-field-label">Current Status</span>
                <span class="ps-status-pill {{ $pdStatusBadgeClass === 'warning' ? 'warning' : 'success' }}">
                    <i class="bi bi-{{ $pdNormalizedStatus === 'planning' ? 'pause-circle' : 'check-circle' }}"></i>
                    {{ $pdStatusLabel }}
                </span>
            </div>
        </div>

        <div class="ps-hero-summary-grid">
            <div class="ps-summary-chip">
                <span class="ps-summary-label">Progress</span>
                <span class="ps-summary-value">{{ number_format($project->progress_percentage ?? 0, 0) }}%</span>
            </div>
            <div class="ps-summary-chip">
                <span class="ps-summary-label">Phases</span>
                <span class="ps-summary-value">{{ $project->phase_count }}</span>
            </div>
            <div class="ps-summary-chip">
                <span class="ps-summary-label">Milestones</span>
                <span class="ps-summary-value">{{ $project->milestone_count }}</span>
            </div>
            <div class="ps-summary-chip">
                <span class="ps-summary-label">Reports</span>
                <span class="ps-summary-value">{{ $pdApprovedReports }} approved</span>
            </div>
        </div>

        <div class="ps-info-grid">
            <div class="ps-info-card">
                <div class="ps-field-label">Project Summary</div>
                <div class="ps-field-value">{{ $project->description ?: 'No summary provided yet.' }}</div>
            </div>
            <div class="ps-info-card">
                <div class="ps-field-label">Key Dates</div>
                <div class="ps-field-value"><i class="bi bi-calendar3"></i> Start: {{ $pdStart ? $pdStart->format('M d, Y') : 'Not set' }}</div>
                <div class="ps-field-value"><i class="bi bi-calendar3"></i> Target: {{ $pdTargetEnd ? $pdTargetEnd->format('M d, Y') : 'Not set' }}</div>
                <div class="ps-field-value"><i class="bi bi-clock-history"></i> Duration: {{ $pdDuration > 0 ? $pdDuration . ' days' : 'Not available' }}</div>
            </div>
            <div class="ps-info-card">
                <div class="ps-field-label">Delivery Snapshot</div>
                <div class="ps-field-value"><i class="bi bi-box-seam"></i> Materials: {{ $pdMaterialsCount }}</div>
                <div class="ps-field-value"><i class="bi bi-people"></i> Attendance Logs: {{ $pdAttendanceCount }}</div>
                <div class="ps-field-value"><i class="bi bi-graph-up"></i> Phase Completion: {{ $pdPhasePercent }}%</div>
            </div>
            <div class="ps-info-card">
                <div class="ps-field-label">Project Readiness</div>
                <div class="ps-field-value"><i class="bi bi-shield-check"></i> Setup: {{ $project->setup_status_label }}</div>
                <div class="ps-field-value"><i class="bi bi-check2-circle"></i> Completion: {{ $project->completion_readiness_label }}</div>
                <div class="ps-field-value"><i class="bi bi-calendar-event"></i> Created: {{ $project->created_at ? $project->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="ps-actions-grid pd-actions-grid">
        @unless($isArchived)
            <a href="{{ route('admin.phases', ['project_id' => $project->project_id]) }}" class="ps-action-card">
                <p class="ps-action-title"><i class="bi bi-kanban"></i>Manage Phases</p>
                <p class="ps-action-copy">View and update construction phases</p>
            </a>
            <a href="{{ route('admin.timeline') }}?project_id={{ $project->project_id }}" class="ps-action-card">
                <p class="ps-action-title"><i class="bi bi-bar-chart-steps"></i>View Timeline</p>
                <p class="ps-action-copy">Milestones &amp; schedule overview</p>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="ps-action-card">
                <p class="ps-action-title"><i class="bi bi-file-earmark-text"></i>Review Reports</p>
                <p class="ps-action-copy">Accomplishment report queue</p>
            </a>
        @endunless
    </div>

    <div class="ps-section-card">
        <div class="ps-section-head">
            <h6>Project Health</h6>
            <span class="ps-readonly-pill">Live Snapshot</span>
        </div>
        <div class="ps-mini-grid">
            <div class="ps-mini-card">
                <div class="ps-mini-icon {{ $pdSetupReady ? 'success' : 'warning' }}">
                    <i class="bi bi-{{ $pdSetupReady ? 'check-lg' : 'exclamation-lg' }}"></i>
                </div>
                <div class="ps-mini-label">Setup Status</div>
                <span class="ps-status-pill {{ $pdSetupReady ? 'success' : 'warning' }}">{{ $project->setup_status_label }}</span>
            </div>
            <div class="ps-mini-card">
                <div class="ps-mini-icon success"><i class="bi bi-layers"></i></div>
                <div class="ps-mini-label">Construction Phases</div>
                <div class="ps-mini-value">{{ $pdCompletedPhases }} / {{ $project->phase_count }} completed</div>
            </div>
            <div class="ps-mini-card">
                <div class="ps-mini-icon neutral"><i class="bi bi-flag"></i></div>
                <div class="ps-mini-label">Timeline Milestones</div>
                <div class="ps-mini-value">{{ $project->milestone_count }} current count</div>
            </div>
            <div class="ps-mini-card">
                <div class="ps-mini-icon neutral"><i class="bi bi-clipboard-check"></i></div>
                <div class="ps-mini-label">Approved Reports</div>
                <div class="ps-mini-value">{{ $pdApprovedReports }}</div>
            </div>
            <div class="ps-mini-card">
                <div class="ps-mini-icon neutral"><i class="bi bi-box-seam"></i></div>
                <div class="ps-mini-label">Materials</div>
                <div class="ps-mini-value">{{ $pdMaterialsCount }} linked</div>
            </div>
            <div class="ps-mini-card">
                <div class="ps-mini-icon neutral"><i class="bi bi-people"></i></div>
                <div class="ps-mini-label">Attendance</div>
                <div class="ps-mini-value">{{ $pdAttendanceCount }} logs</div>
            </div>
        </div>
    </div>

    <div class="ps-section-card">
        <div class="ps-section-head">
            <h6>Project Team</h6>
        </div>
        <div class="ps-team-grid">
            <div class="ps-team-card">
                <div class="ps-team-icon"><i class="bi bi-building"></i></div>
                <div class="ps-team-role">Client</div>
                <div class="ps-team-name">{{ optional($project->client?->user)->name ?? 'Not assigned' }}</div>
                <div class="ps-team-sub">{{ optional($project->client?->user)->email ?? 'No email' }}</div>
            </div>
            <div class="ps-team-card">
                <div class="ps-team-icon"><i class="bi bi-person"></i></div>
                <div class="ps-team-role">Project Engineer</div>
                <div class="ps-team-name">{{ optional($project->engineer)->name ?? 'Not assigned' }}</div>
                <div class="ps-team-sub">{{ optional($project->engineer)->email ?? 'No email' }}</div>
            </div>
            <div class="ps-team-card">
                <div class="ps-team-icon"><i class="bi bi-person-badge"></i></div>
                <div class="ps-team-role">Site Supervisor</div>
                <div class="ps-team-name">{{ $pdActiveSupervisor?->name ?? 'Not assigned' }}</div>
                <div class="ps-team-sub">{{ $pdActiveSupervisor?->email ?? 'No email' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="ps-footer d-flex justify-content-center gap-2 flex-wrap">
    @if($isModal)
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
    @else
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    @endif
    @unless($isArchived)
        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline-success" style="border-color:#c8e6c9; color:#166534; background:#f6fff7;">
            <i class="bi bi-pencil"></i> Edit Project
        </a>
    @endunless
    @if($isArchived)
        <form action="{{ route('admin.projects.restore', $project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="restore" data-confirm-title="Restore Project?" data-confirm-text="This project will be moved back to the Active Project list." data-confirm-button="Restore" data-cancel-button="Cancel">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-outline-success" style="border-color:#c8e6c9; color:#166534; background:#f6fff7;">
                <i class="bi bi-arrow-counterclockwise"></i> Restore
            </button>
        </form>
    @else
        @if(auth('web')->check() && in_array(strtolower((string) auth('web')->user()?->role), ['engineer', 'admin', 'administrator'], true))
            <form action="{{ route('admin.projects.archive', $project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="archive" data-confirm-title="Archive Project?" data-confirm-text="This project will be removed from the Active Project list. All construction history, reports, phases, milestones, attendance, and materials will remain available." data-confirm-button="Archive" data-cancel-button="Cancel">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline-warning" style="border-color:#f5d9a0; color:#b7791f; background:#fffaf1;">
                    <i class="bi bi-archive"></i> Archive
                </button>
            </form>
            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="delete" data-confirm-title="Delete Project?" data-confirm-text="This project has no construction records. This action is permanent and cannot be undone." data-confirm-button="Delete" data-cancel-button="Cancel">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm px-3 py-2 btn-outline-danger" style="border-color:#fecaca; color:#b91c1c; background:#fff7f7;">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        @endif
    @endif
</div>

@push('styles')
<style>
    .ps-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 28px 32px 10px;
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
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
        font-size: 1.45rem;
    }

    .ps-header-text {
        flex: 1;
        min-width: 0;
    }

    .ps-header-text h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 4px;
    }

    .ps-header-text p {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0;
    }

    .ps-body {
        padding: 20px 32px 28px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .ps-overview-card,
    .ps-section-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    }

    .ps-overview-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        padding-bottom: 14px;
        border-bottom: 1px solid #e2e8f0;
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
        background: #166534;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .ps-overview-name {
        font-size: 1.03rem;
        font-weight: 700;
        color: #0f172a;
    }

    .ps-overview-id {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 2px;
    }

    .ps-overview-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .ps-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .ps-overview-status {
        text-align: right;
        flex: 0 0 auto;
    }

    .ps-hero-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .ps-summary-chip {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        background: #f8fafc;
    }

    .ps-summary-label {
        display: block;
        font-size: 0.67rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        margin-bottom: 4px;
    }

    .ps-summary-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    .ps-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-info-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        background: #fff;
    }

    .ps-info-card .ps-field-value {
        margin-top: 5px;
        line-height: 1.45;
    }

    .ps-actions-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-action-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
        text-decoration: none;
        background: #fff;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .ps-action-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(22, 101, 52, 0.08);
        border-color: #166534;
    }

    .ps-mini-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-mini-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        background: #fff;
    }

    .ps-team-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-team-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        text-align: center;
        background: #fff;
    }

    .ps-footer {
        border-top: 1px solid #e2e8f0;
        padding: 16px 32px 22px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (max-width: 767.98px) {
        .ps-hero-summary-grid,
        .ps-info-grid,
        .ps-actions-grid,
        .ps-mini-grid,
        .ps-team-grid {
            grid-template-columns: 1fr;
        }

        .ps-overview-top {
            flex-direction: column;
        }

        .ps-overview-status {
            text-align: left;
        }
    }
</style>
@endpush