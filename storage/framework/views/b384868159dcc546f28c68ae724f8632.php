<div class="table-container-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 dg-custom-table" style="font-size: 13px;">
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
                <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $projectIsArchived = strtolower((string) ($project->getRawOriginal('status') ?? $project->status ?? '')) === 'archived'
                            || (bool) ($project->is_archived ?? false);

                        if ($projectIsArchived) {
                            continue;
                        }

                        $projectPhaseCount = $project->phase_count;
                        $projectMilestoneCount = $project->milestone_count;
                        $projectReportsCount = $project->report_count;
                        $projectMaterialsCount = $project->material_count;
                        $projectAttendanceCount = $project->attendance_count;
                        $storedStatus = strtolower((string) ($project->getRawOriginal('status') ?? $project->status ?? 'planning'));
                        $normalizedStatus = $project->workflowStatus();
                        $projectDaysLeft = $normalizedStatus === 'completed' ? 0 : ($project->target_end_date ? max(0, (int) now()->diffInDays($project->target_end_date, false)) : 0);
                        $projectStatusLabel = $project->workflow_status_label;
                        $projectStatusClass = $project->workflow_status_class;
                        $projectProgressPct = number_format($project->overall_progress_percentage, 0);
                        $projectProgressSubtitle = match ($normalizedStatus) {
                            'completed' => 'Completed',
                            'planning' => 'Site Preparation',
                            'archived' => 'Archived',
                            default => 'Foundation Phase',
                        };
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-start gap-3">
                                <div class="project-thumb-wrapper" style="width: 44px; height: 36px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #eef2f7; display: flex; align-items: center; justify-content: center;">
                                    <?php if($project->image_url): ?>
                                        <img src="<?php echo e($project->image_url); ?>" alt="<?php echo e($project->project_name); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="bi bi-building text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="project-title-bold"><?php echo e($project->project_name); ?></div>
                                    <div class="project-subtext-muted"><?php echo e(Str::limit($project->location, 35)); ?></div>
                                    <div class="project-date-badge">
                                        <i class="bi bi-calendar3"></i>
                                        <?php echo e($project->start_date ? $project->start_date->format('M d, Y') : ''); ?> -
                                        <?php echo e($project->target_end_date ? $project->target_end_date->format('M d, Y') : ''); ?>

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
                                        <?php echo e($project->active_supervisor->name ?? ($project->supervisors->first()->name ?? 'Juan Dela Cruz')); ?>

                                    </div>
                                    <div class="project-subtext-muted" style="font-size: 11px;">Supervisor</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-pill <?php echo e($projectStatusClass); ?>">
                                <?php echo e($projectStatusLabel); ?>

                            </span>
                        </td>
                        <td>
                            <div class="custom-progress-container">
                                <?php
                                ?>
                                <div class="progress-percent-lbl"><?php echo e($projectProgressPct); ?>%</div>
                                <div class="dg-bar-track">
                                    <div class="dg-bar-fill <?php echo e($normalizedStatus === 'planning' ? 'hold-fill' : ''); ?>" style="width: <?php echo e($projectProgressPct); ?>%"></div>
                                </div>
                                <div class="progress-phase-subtitle">
                                    <?php echo e($projectProgressSubtitle); ?>

                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="duration-primary-txt">
                                    <?php if($normalizedStatus === 'completed'): ?>
                                        Completed
                                    <?php else: ?>
                                        <?php echo e($project->start_date ? $project->start_date->diffInDays($project->target_end_date) : 169); ?> days left
                                    <?php endif; ?>
                                </div>
                                <div class="duration-secondary-pct">(<?php echo e($projectProgressPct); ?>%)</div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="action-buttons-flex">
                                <button type="button" class="btn btn-view-action trigger-details-panel"
                                        data-project-json="<?php echo e(json_encode($project)); ?>"
                                        data-project-location="<?php echo e($project->project_location ?? $project->location ?? ''); ?>"
                                        data-supervisor-name="<?php echo e($project->active_supervisor->name ?? 'Juan Dela Cruz'); ?>"
                                        data-supervisor-id="<?php echo e($project->active_supervisor->user_id ?? ''); ?>"
                                        data-client-name="<?php echo e($project->client->user->name ?? 'Mr. & Mrs. Reyes'); ?>"
                                        data-status="<?php echo e($normalizedStatus); ?>"
                                        data-status-label="<?php echo e($projectStatusLabel); ?>"
                                        data-status-class="<?php echo e($projectStatusClass); ?>"
                                        data-status-transitions="<?php echo e(json_encode($project->allowedStatusTransitions())); ?>"
                                        data-start-date="<?php echo e($project->start_date ? $project->start_date->toDateString() : ''); ?>"
                                        data-target-end-date="<?php echo e($project->target_end_date ? $project->target_end_date->toDateString() : ''); ?>"
                                        data-days-left="<?php echo e($projectDaysLeft); ?>"
                                        data-phases="<?php echo e($projectPhaseCount); ?>"
                                        data-milestones="<?php echo e($projectMilestoneCount); ?>"
                                        data-reports="<?php echo e($projectReportsCount); ?>"
                                        data-materials="<?php echo e($projectMaterialsCount); ?>"
                                        data-attendance="<?php echo e($projectAttendanceCount); ?>"
                                        data-pct="<?php echo e($projectProgressPct); ?>">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <?php if($normalizedStatus === 'archived'): ?>
                                    <form action="<?php echo e(route('admin.projects.restore', $project)); ?>" method="POST" class="d-inline project-action-form" data-project-confirm="restore" data-confirm-title="Restore Project?" data-confirm-text="This project will be moved back to the Active Project list." data-confirm-button="Restore" data-cancel-button="Cancel">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" style="border-color:#c8e6c9; color:#166534; background:#f6fff7;"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-icon-more"><i class="bi bi-three-dots-vertical"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i> No active projects found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
        $currentPage = max(1, (int) $projects->currentPage());
        $lastPage = max(1, (int) $projects->lastPage());
        $queryParams = request()->query();
    ?>

    <div class="table-pagination-footer-bar">
        <div class="text-muted small">Showing <?php echo e($projects->firstItem() ?: 0); ?> to <?php echo e($projects->lastItem() ?: 0); ?> of <?php echo e($projects->total()); ?> projects</div>
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <?php if($currentPage > 1): ?>
                <a href="<?php echo e($projects->appends($queryParams)->url($currentPage - 1)); ?>" class="btn btn-sm btn-outline-secondary px-2">
                    <i class="bi bi-chevron-left"></i>
                </a>
            <?php else: ?>
                <span class="btn btn-sm btn-outline-secondary px-2 disabled" aria-disabled="true">
                    <i class="bi bi-chevron-left"></i>
                </span>
            <?php endif; ?>

            <?php for($page = 1; $page <= $lastPage; $page++): ?>
                <?php if($page === $currentPage): ?>
                    <span class="btn btn-sm btn-success px-3"><?php echo e($page); ?></span>
                <?php else: ?>
                    <a href="<?php echo e($projects->appends($queryParams)->url($page)); ?>" class="btn btn-sm btn-outline-secondary px-3"><?php echo e($page); ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if($currentPage < $lastPage): ?>
                <a href="<?php echo e($projects->appends($queryParams)->url($currentPage + 1)); ?>" class="btn btn-sm btn-outline-secondary px-2">
                    <i class="bi bi-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="btn btn-sm btn-outline-secondary px-2 disabled" aria-disabled="true">
                    <i class="bi bi-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div><?php /**PATH C:\Users\Paul Pastor\DGConstruction\resources\views/admin/projects/partials/table.blade.php ENDPATH**/ ?>