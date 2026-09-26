<div class="project-selector-wrap">
    <button type="button" class="project-selector-button" id="projectSelectorButton" aria-expanded="false" aria-controls="projectSelectorMenu" <?php if($allProjects->count() <= 1): ?> disabled <?php endif; ?>>
        <span class="project-selector-icon"><i class="bi bi-building"></i></span>
        <span class="project-selector-label"><?php echo e($primaryProjectName); ?></span>
        <?php if($allProjects->count() > 1): ?>
            <i class="bi bi-chevron-down project-selector-caret"></i>
        <?php endif; ?>
    </button>
    <div class="project-selector-menu" id="projectSelectorMenu" hidden>
        <?php $__currentLoopData = $allProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a class="project-selector-item <?php echo e($project->project_id == ($primaryProject?->project_id ?? null) ? 'active' : ''); ?>" href="<?php echo e(route('client.dashboard', ['project_id' => $project->project_id])); ?>">
                <span class="project-selector-item-icon"><i class="bi bi-building"></i></span>
                <span><?php echo e($project->project_name); ?></span>
                <?php if($project->project_id == ($primaryProject?->project_id ?? null)): ?>
                    <i class="bi bi-check2 project-selector-check"></i>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\Users\Paul Pastor\DGConstruction\resources\views\client\partials\project-selector.blade.php ENDPATH**/ ?>