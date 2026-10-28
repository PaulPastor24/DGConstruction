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
    $isArchived = $project->workflowStatus() === \App\Models\Project::STATUS_ARCHIVED;
    $pdActiveSupervisor = $project->active_supervisor;
    $pdStart = $project->start_date;
    $pdTargetEnd = $project->target_end_date;
    $pdActualEnd = $project->actual_end_date;
    $pdDuration = $pdStart && $pdTargetEnd ? max(1, $pdStart->diffInDays($pdTargetEnd) + 1) : 0;
    $pdPhases = $project->phases;
    $pdPhaseCount = $pdPhases->count();
    $pdMilestoneCount = $project->milestones()->count();
    $pdCompletedPhases = $pdPhases->where('status', 'completed')->count();
    $pdApprovedReports = $project->reports()->where('approval_status', 'approved')->count();
    $pdMaterialsCount = $project->projectMaterials()->count();
    $pdAttendanceCount = $project->attendanceLogs()->count();
    $pdOverallProgress = min(100, max(0, (float) ($project->progress_percentage ?? 0)));
    $pdPhasePercent = $pdPhaseCount > 0 ? round(($pdCompletedPhases / $pdPhaseCount) * 100) : 0;
    $pdSetupReady = $project->client_id
        && $project->engineer_id
        && $pdActiveSupervisor
        && $pdStart
        && $pdTargetEnd
        && $pdPhaseCount > 0;
    $pdSetupLabel = $pdSetupReady ? 'Ready for Construction' : 'Setup Required';
    $pdCompletionReady = $pdPhaseCount > 0
        && $pdCompletedPhases === $pdPhaseCount
        && $pdApprovedReports > 0
        && $pdMaterialsCount > 0
        && $pdAttendanceCount > 0;
    $pdCompletionLabel = $pdCompletionReady ? 'Ready for Completion' : 'In Progress';
    $pdNormalizedStatus = $project->workflowStatus();
    $pdIsLate = $project->isLate();
    $pdStatusLabel = $pdIsLate ? 'Late' : $project->workflow_status_label;
    $pdStatusBadgeClass = $pdIsLate ? 'danger' : (in_array($pdNormalizedStatus, [
        \App\Models\Project::STATUS_ONGOING,
        \App\Models\Project::STATUS_COMPLETED,
    ], true) ? 'success' : 'warning');
@endphp

<div class="ps-header">
    <div class="ps-header-icon"><i class="bi bi-building"></i></div>
    <div class="ps-header-text">
        <h3 id="projectDetailsModalLabel">{{ $project->project_name ?? 'Project Details' }}</h3>
        <p>ID: #{{ $project->project_id ?? 'N/A' }} &middot; {{ $project->location ?? 'Location not specified' }}</p>
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
                        <span class="ps-meta-pill"><i class="bi bi-geo-alt"></i> {{ $project->location ?? 'Location not specified' }}</span>
                        <span class="ps-meta-pill"><i class="bi bi-person-workspace"></i> {{ $pdActiveSupervisor?->name ?? 'Supervisor pending' }}</span>
                    </div>
                </div>
            </div>
            <div class="ps-overview-status">
                <span class="ps-field-label">Current Status</span>
                <span class="ps-status-pill {{ $pdStatusBadgeClass }}">
                    <i class="bi bi-{{ $pdIsLate ? 'exclamation-circle' : ($pdNormalizedStatus === 'planning' || $pdNormalizedStatus === \App\Models\Project::STATUS_ON_HOLD ? 'pause-circle' : 'check-circle') }}"></i>
                    {{ $pdStatusLabel }}
                </span>
            </div>
        </div>

        <div class="ps-hero-summary-grid">
            <div class="ps-summary-chip">
                <span class="ps-summary-label"><i class="bi bi-graph-up-arrow"></i> Progress</span>
                <span class="ps-summary-value">{{ number_format($pdOverallProgress, 0) }}<small>%</small></span>
                <div class="ps-progress-track" role="progressbar" aria-label="Project progress" aria-valuenow="{{ $pdOverallProgress }}" aria-valuemin="0" aria-valuemax="100">
                    <span style="width: {{ $pdOverallProgress }}%"></span>
                </div>
            </div>
            <div class="ps-summary-chip">
                <span class="ps-summary-label"><i class="bi bi-layers"></i> Phases</span>
                <span class="ps-summary-value">{{ $pdPhaseCount }}</span>
                <span class="ps-summary-detail">{{ $pdCompletedPhases }} completed</span>
            </div>
            <div class="ps-summary-chip">
                <span class="ps-summary-label"><i class="bi bi-flag"></i> Milestones</span>
                <span class="ps-summary-value">{{ $pdMilestoneCount }}</span>
                <span class="ps-summary-detail">Project schedule</span>
            </div>
            <div class="ps-summary-chip">
                <span class="ps-summary-label"><i class="bi bi-file-earmark-check"></i> Reports</span>
                <span class="ps-summary-value">{{ $pdApprovedReports }}</span>
                <span class="ps-summary-detail">Approved</span>
            </div>
        </div>

        <div class="ps-info-grid">
            <div class="ps-info-card ps-summary-info-card">
                <div class="ps-info-card-heading">
                    <span class="ps-info-icon"><i class="bi bi-card-text"></i></span>
                    <span class="ps-field-label">Project Summary</span>
                </div>
                <p class="ps-summary-copy {{ $project->description ? '' : 'is-empty' }}">{{ $project->description ?: 'No summary provided yet.' }}</p>
            </div>
            <div class="ps-info-card">
                <div class="ps-info-card-heading">
                    <span class="ps-info-icon"><i class="bi bi-calendar3"></i></span>
                    <span class="ps-field-label">Key Dates</span>
                </div>
                <div class="ps-detail-list">
                    <div class="ps-detail-row"><span>Start date</span><strong>{{ $pdStart ? $pdStart->format('M d, Y') : 'Not set' }}</strong></div>
                    <div class="ps-detail-row"><span>Target date</span><strong>{{ $pdTargetEnd ? $pdTargetEnd->format('M d, Y') : 'Not set' }}</strong></div>
                    <div class="ps-detail-row"><span>Duration</span><strong>{{ $pdDuration > 0 ? $pdDuration . ' days' : 'Not available' }}</strong></div>
                </div>
            </div>
            <div class="ps-info-card">
                <div class="ps-info-card-heading">
                    <span class="ps-info-icon"><i class="bi bi-truck"></i></span>
                    <span class="ps-field-label">Delivery Snapshot</span>
                </div>
                <div class="ps-detail-list">
                    <div class="ps-detail-row"><span><i class="bi bi-box-seam"></i> Materials</span><strong>{{ $pdMaterialsCount }}</strong></div>
                    <div class="ps-detail-row"><span><i class="bi bi-people"></i> Attendance logs</span><strong>{{ $pdAttendanceCount }}</strong></div>
                    <div class="ps-detail-row"><span><i class="bi bi-check2-circle"></i> Phase completion</span><strong>{{ $pdPhasePercent }}%</strong></div>
                </div>
            </div>
            <div class="ps-info-card">
                <div class="ps-info-card-heading">
                    <span class="ps-info-icon"><i class="bi bi-shield-check"></i></span>
                    <span class="ps-field-label">Project Readiness</span>
                </div>
                <div class="ps-detail-list">
                    <div class="ps-detail-row"><span>Setup</span><span class="ps-readiness-pill {{ $pdSetupReady ? 'is-ready' : '' }}">{{ $pdSetupLabel }}</span></div>
                    <div class="ps-detail-row"><span>Completion</span><span class="ps-readiness-pill {{ $pdCompletionReady ? 'is-ready' : '' }}">{{ $pdCompletionLabel }}</span></div>
                    <div class="ps-detail-row"><span>Created</span><strong>{{ $project->created_at ? $project->created_at->format('M d, Y · h:i A') : 'N/A' }}</strong></div>
                </div>
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
                <span class="ps-status-pill {{ $pdSetupReady ? 'success' : 'warning' }}">{{ $pdSetupLabel }}</span>
            </div>
            <div class="ps-mini-card">
                <div class="ps-mini-icon success"><i class="bi bi-layers"></i></div>
                <div class="ps-mini-label">Construction Phases</div>
                <div class="ps-mini-value">{{ $pdCompletedPhases }} / {{ $pdPhaseCount }} completed</div>
            </div>
            <div class="ps-mini-card">
                <div class="ps-mini-icon neutral"><i class="bi bi-flag"></i></div>
                <div class="ps-mini-label">Timeline Milestones</div>
                <div class="ps-mini-value">{{ $pdMilestoneCount }} total milestones</div>
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

    @include('admin.projects.partials.workers-materials')

    <div class="ps-section-card">
        <div class="ps-section-head">
            <h6>Status History</h6>
            <span class="ps-readonly-pill">Audit Trail</span>
        </div>
        @forelse($project->statusHistory as $statusChange)
            <div class="d-flex align-items-start gap-2 py-2 border-bottom">
                <i class="bi bi-arrow-left-right text-success mt-1"></i>
                <div class="small">
                    <div class="fw-semibold">{{ $statusChange->from_status ? \App\Models\Project::statusLabel($statusChange->from_status) : 'Created' }} &rarr; {{ \App\Models\Project::statusLabel($statusChange->to_status) }}</div>
                    <div class="text-muted">{{ $statusChange->user?->name ?? 'System' }} &middot; {{ $statusChange->created_at?->format('M d, Y h:i A') }}</div>
                    @if($statusChange->reason)
                        <div class="text-muted">Reason: {{ $statusChange->reason }}</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="small text-muted">No status changes recorded yet.</div>
        @endforelse
    </div>
</div>

<div class="ps-footer">
    @unless($isModal)
        <div class="ps-footer-navigation" role="group" aria-label="Project navigation">
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
        </div>
    @endunless
    <div class="ps-footer-actions" role="group" aria-label="Project actions">
    @if($isArchived)
        <form action="{{ route('admin.projects.restore', $project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="restore" data-confirm-title="Restore Project?" data-confirm-text="This project will be moved back to the Active Project list." data-confirm-button="Restore" data-cancel-button="Cancel">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm btn-outline-success">
                <i class="bi bi-arrow-counterclockwise"></i> Restore
            </button>
        </form>
    @else
        @if(auth('web')->check() && in_array(strtolower((string) auth('web')->user()?->role), ['engineer', 'admin', 'administrator'], true))
            <form action="{{ route('admin.projects.archive', $project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="archive" data-confirm-title="Archive Project?" data-confirm-text="This project will be removed from the Active Project list. All construction history, reports, phases, milestones, attendance, and materials will remain available." data-confirm-button="Archive" data-cancel-button="Cancel">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-archive"></i> Archive Project
                </button>
            </form>
            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="delete" data-confirm-title="Delete Project?" data-confirm-text="This project has no construction records. This action is permanent and cannot be undone." data-confirm-button="Delete" data-cancel-button="Cancel">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i> Delete Project
                </button>
            </form>
        @endif
    @endif
    </div>
</div>

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

    .ps-status-pill.danger {
        background: #fee2e2;
        color: #b91c1c;
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
        background: linear-gradient(145deg, #fff 0%, #f8fbf8 100%);
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

    .ps-summary-label i {
        margin-right: 4px;
        color: #66836d;
    }

    .ps-summary-value {
        display: block;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    .ps-summary-value small {
        margin-left: 1px;
        color: #64748b;
        font-size: 0.75em;
    }

    .ps-summary-detail {
        display: block;
        margin-top: 2px;
        color: #718174;
        font-size: 0.72rem;
    }

    .ps-progress-track {
        height: 5px;
        margin-top: 8px;
        overflow: hidden;
        border-radius: 999px;
        background: #e8eee9;
    }

    .ps-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: #66836d;
    }

    .ps-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .ps-info-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px 16px;
        background: linear-gradient(155deg, #fff 0%, #fbfdfb 100%);
    }

    .ps-info-card-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 12px;
    }

    .ps-info-icon {
        display: inline-flex;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #edf4ee;
        color: #52705a;
    }

    .ps-info-card-heading .ps-field-label {
        color: #405347;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.045em;
        text-transform: uppercase;
    }

    .ps-summary-copy {
        margin: 0;
        color: #334155;
        font-size: 0.88rem;
        line-height: 1.65;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .ps-summary-copy.is-empty {
        color: #87938a;
        font-style: italic;
    }

    .ps-detail-list {
        display: flex;
        flex-direction: column;
    }

    .ps-detail-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        border-top: 1px solid #edf1ed;
        color: #718174;
        font-size: 0.78rem;
        line-height: 1.4;
    }

    .ps-detail-row:first-child {
        padding-top: 0;
        border-top: 0;
    }

    .ps-detail-row:last-child {
        padding-bottom: 0;
    }

    .ps-detail-row > span:first-child {
        min-width: 0;
    }

    .ps-detail-row > span:first-child i {
        width: 17px;
        color: #7a8e7e;
    }

    .ps-detail-row strong {
        color: #33483a;
        font-weight: 700;
        text-align: right;
    }

    .ps-readiness-pill {
        max-width: 65%;
        padding: 3px 8px;
        border: 1px solid #e3e8e3;
        border-radius: 999px;
        background: #f5f7f5;
        color: #657168;
        font-size: 0.68rem;
        font-weight: 700;
        text-align: center;
    }

    .ps-readiness-pill.is-ready {
        border-color: #d7e5d9;
        background: #edf4ee;
        color: #48634e;
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
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
    }

    .ps-footer-navigation,
    .ps-footer-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ps-footer-actions form {
        display: flex;
        margin: 0;
    }

    .ps-footer-actions {
        justify-content: flex-end;
    }

    .ps-footer .btn {
        min-height: 36px;
        padding: 0.45rem 0.8rem;
        font-weight: 600;
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

        .ps-footer {
            align-items: stretch;
            flex-direction: column;
            padding: 14px 18px 18px;
        }

        .ps-footer-navigation,
        .ps-footer-actions {
            width: 100%;
        }

        .ps-footer-navigation {
            justify-content: flex-start;
        }

        .ps-footer-actions {
            justify-content: flex-end;
        }

        .ps-footer-navigation .btn,
        .ps-footer-actions form,
        .ps-footer-actions form .btn {
            flex: 1 1 0;
        }

        .ps-footer-navigation .btn,
        .ps-footer-actions form .btn {
            justify-content: center;
        }

        .ps-overview-status {
            text-align: left;
        }
    }
</style>