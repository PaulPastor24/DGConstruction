<div class="table-container-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 dg-custom-table" style="font-size: 13px; min-width: 860px;">
            <thead class="table-light text-muted fw-bold" style="font-size: 11px; text-transform: uppercase;">
                <tr>
                    <th class="project-col">Project</th>
                    <th class="supervisor-col">Supervisor</th>
                    <th class="status-col">Status</th>
                    <th class="progress-col">Progress</th>
                    <th class="duration-col">Duration</th>
                    <th class="actions-col text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    @php
                        $projectIsArchived = strtolower((string) ($project->getRawOriginal('status') ?? $project->status ?? '')) === 'archived'
                            || (bool) ($project->is_archived ?? false);

                        if ($projectIsArchived) {
                            continue;
                        }

                        $projectPhaseCount = $project->phase_count;
                        $projectMilestoneCount = $project->milestone_count;
                        $projectApprovedReportsCount = $project->reports()->where('approval_status', 'approved')->count();
                        $projectMaterialsCount = $project->projectMaterials()->count();
                        $projectAttendanceCount = $project->attendanceLogs()->count();
                        $storedStatus = strtolower((string) ($project->getRawOriginal('status') ?? $project->status ?? 'planning'));
                        $normalizedStatus = match ($storedStatus) {
                            'in_progress', 'inprogress', 'ongoing', 'active' => 'ongoing',
                            'completed', 'complete', 'finished' => 'completed',
                            'on_hold' => 'on_hold',
                            'pending', 'planning', 'not_started', 'paused', 'delayed' => 'planning',
                            'archived' => 'archived',
                            default => 'planning',
                        };
                        $projectDaysLeft = $normalizedStatus === 'completed' ? 0 : ($project->target_end_date ? max(0, (int) now()->diffInDays($project->target_end_date, false)) : 0);
                        $projectStatusLabel = match ($normalizedStatus) {
                            'ongoing' => 'In Progress',
                            'on_hold' => 'On Hold',
                            'completed' => 'Completed',
                            'archived' => 'Archived',
                            default => 'Planning',
                        };
                        $projectStatusClass = match ($normalizedStatus) {
                            'ongoing' => 'in-progress',
                            'on_hold' => 'on-hold',
                            'completed' => 'completed',
                            'archived' => 'completed',
                            default => 'planning',
                        };
                        $projectProgressPct = number_format($project->progress_percentage ?? ($normalizedStatus === 'completed' ? 100 : ($normalizedStatus === 'planning' ? 25 : 65)), 0);
                        $projectProgressSubtitle = match ($normalizedStatus) {
                            'completed' => 'Completed',
                            'planning' => 'Site Preparation',
                            'archived' => 'Archived',
                            default => 'Foundation Phase',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-start gap-3">
                                <div class="metric-icon-wrapper" style="background-color: {{ $normalizedStatus === 'planning' ? '#198754' : ($normalizedStatus === 'ongoing' ? '#0d6efd' : ($normalizedStatus === 'archived' ? '#6c757d' : '#198754')) }}; color: white; width: 36px; height: 36px;">
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
                            <span class="status-pill {{ $projectStatusClass }}">
                                {{ $projectStatusLabel }}
                            </span>
                        </td>
                        <td>
                            <div class="custom-progress-container">
                                @php
                                @endphp
                                <div class="progress-percent-lbl">{{ $projectProgressPct }}%</div>
                                <div class="dg-bar-track">
                                    <div class="dg-bar-fill {{ $normalizedStatus === 'planning' ? 'hold-fill' : '' }}" style="width: {{ $projectProgressPct }}%"></div>
                                </div>
                                <div class="progress-phase-subtitle">
                                    {{ $projectProgressSubtitle }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="duration-primary-txt">
                                    @if($normalizedStatus === 'completed')
                                        Completed
                                    @else
                                        {{ $project->start_date ? $project->start_date->diffInDays($project->target_end_date) : 169 }} days left
                                    @endif
                                </div>
                                <div class="duration-secondary-pct">({{ $projectProgressPct }}%)</div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons-flex">
                                <button type="button" class="btn btn-view-action trigger-details-panel"
                                        data-project-json="{{ json_encode($project) }}"
                                        data-supervisor-name="{{ $project->active_supervisor->name ?? 'Juan Dela Cruz' }}"
                                        data-supervisor-id="{{ $project->active_supervisor->user_id ?? '' }}"
                                        data-client-name="{{ $project->client->user->name ?? 'Mr. & Mrs. Reyes' }}"
                                        data-status="{{ $storedStatus }}"
                                        data-start-date="{{ $project->start_date ? $project->start_date->toDateString() : '' }}"
                                        data-target-end-date="{{ $project->target_end_date ? $project->target_end_date->toDateString() : '' }}"
                                        data-days-left="{{ $projectDaysLeft }}"
                                        data-phases="{{ $projectPhaseCount }}"
                                        data-milestones="{{ $projectMilestoneCount }}"
                                        data-reports="{{ $projectApprovedReportsCount }}"
                                        data-materials="{{ $projectMaterialsCount }}"
                                        data-attendance="{{ $projectAttendanceCount }}"
                                        data-pct="{{ $projectProgressPct }}">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                @if($normalizedStatus === 'archived')
                                    <form action="{{ route('admin.projects.restore', $project) }}" method="POST" class="d-inline project-action-form" data-project-confirm="restore" data-confirm-title="Restore Project?" data-confirm-text="This project will be moved back to the Active Project list." data-confirm-button="Restore" data-cancel-button="Cancel">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success" style="border-color:#c8e6c9; color:#166534; background:#f6fff7;"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                    </form>
                                @else
                                    <button class="btn-icon-more"><i class="bi bi-three-dots-vertical"></i></button>
                                @endif
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

    <div class="table-pagination-footer-bar">
        <div>Showing {{ $projects->firstItem() ?: 0 }} to {{ $projects->lastItem() ?: 0 }} of {{ $projects->total() }} projects</div>
        <div class="d-flex gap-1">
            <button class="btn btn-sm btn-light border px-2"><i class="bi bi-chevron-left"></i></button>
            <button class="btn btn-sm btn-success px-3">1</button>
            <button class="btn btn-sm btn-light border px-2"><i class="bi bi-chevron-right"></i></button>
        </div>
    </div>
</div>
