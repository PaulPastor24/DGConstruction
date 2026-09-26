<?php $__env->startSection('title', 'Landing Page Gallery - D&G Construction Monitor'); ?>
<?php $__env->startSection('page_title', 'Landing Page Gallery'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-1">Add a completed project</h2>
            <p class="text-muted small mb-3">Choose a project from the system or add a past featured project not in the system.</p>
            <form action="<?php echo e(route('admin.landing-gallery.store')); ?>" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                <?php echo csrf_field(); ?>
                <div class="col-12 col-md-5">
                    <label for="project_id" class="form-label">Project</label>
                    <select id="project_id" name="project_id" class="form-select">
                        <option value="">Select a completed project</option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($project->project_id); ?>"><?php echo e($project->project_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_external" name="is_external">
                        <label class="form-check-label small" for="is_external">
                            This is an external / past featured project not in the system
                        </label>
                    </div>
                </div>
                <div class="col-12 col-md-5">
                    <label for="image" class="form-label">Carousel image</label>
                    <input id="image" name="image" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus-lg me-1"></i>Add image</button>
                </div>
            </form>
            <?php $__errorArgs = ['project_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-2"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-2"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div id="externalProjectFields" class="row g-3 mt-2" style="display: none;">
                <div class="col-12 col-md-6">
                    <label for="external_project_name" class="form-label">External project name</label>
                    <input id="external_project_name" name="external_project_name" type="text" class="form-control" maxlength="255">
                </div>
                <div class="col-12 col-md-6">
                    <label for="external_project_location" class="form-label">Location</label>
                    <input id="external_project_location" name="external_project_location" type="text" class="form-control" maxlength="255">
                </div>
                <div class="col-12">
                    <label for="external_project_description" class="form-label">Description</label>
                    <textarea id="external_project_description" name="external_project_description" class="form-control" rows="3" maxlength="2000"></textarea>
                </div>
                <div class="col-12">
                    <label for="external_project_url" class="form-label">Project URL (optional)</label>
                    <input id="external_project_url" name="external_project_url" type="url" class="form-control" maxlength="2000" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>

    <form id="galleryReorderForm" action="<?php echo e(route('admin.landing-gallery.reorder')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
    </form>
    <div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Current carousel entries</span>
                <button type="submit" form="galleryReorderForm" class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-down-up me-1"></i>Save order</button>
            </div>
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $galleryImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $galleryImage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="list-group-item d-flex flex-wrap gap-3 align-items-center" data-gallery-row>
                        <img src="<?php echo e(asset('storage/' . ltrim($galleryImage->image_path, '/'))); ?>" alt="<?php echo e($galleryImage->display_name); ?>" style="width:96px;height:64px;object-fit:cover;border-radius:8px;">
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?php echo e($galleryImage->display_name); ?></div>
                            <div class="small text-muted"><?php echo e($galleryImage->display_location ?: 'No location'); ?> · Position <?php echo e($loop->iteration); ?> · <?php echo e($galleryImage->is_active ? 'Visible' : 'Hidden'); ?> · <?php echo e($galleryImage->is_external ? 'External' : 'System'); ?></div>
                        </div>
                        <input type="hidden" name="order[]" value="<?php echo e($galleryImage->id); ?>" form="galleryReorderForm">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Change gallery position">
                            <button type="button" class="btn btn-outline-secondary" data-move-gallery="up" title="Move up"><i class="bi bi-chevron-up"></i></button>
                            <button type="button" class="btn btn-outline-secondary" data-move-gallery="down" title="Move down"><i class="bi bi-chevron-down"></i></button>
                        </div>
                        <form action="<?php echo e(route('admin.landing-gallery.toggle', $galleryImage)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-sm <?php echo e($galleryImage->is_active ? 'btn-outline-secondary' : 'btn-outline-success'); ?>">
                                <i class="bi bi-eye<?php echo e($galleryImage->is_active ? '-slash' : ''); ?> me-1"></i><?php echo e($galleryImage->is_active ? 'Hide' : 'Show'); ?>

                            </button>
                        </form>
                        <form action="<?php echo e(route('admin.landing-gallery.destroy', $galleryImage)); ?>" method="POST" onsubmit="return confirm('Remove this image from the landing page?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item text-muted">No landing page images have been added yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('[data-move-gallery]').forEach(button => {
    button.addEventListener('click', () => {
        const row = button.closest('[data-gallery-row]');
        const sibling = button.dataset.moveGallery === 'up' ? row.previousElementSibling : row.nextElementSibling;

        if (!row || !sibling || !sibling.matches('[data-gallery-row]')) return;
        if (button.dataset.moveGallery === 'up') {
            row.parentElement.insertBefore(row, sibling);
        } else {
            row.parentElement.insertBefore(sibling, row);
        }
    });
});

document.getElementById('is_external').addEventListener('change', function () {
    const fields = document.getElementById('externalProjectFields');
    const projectSelect = document.getElementById('project_id');

    if (this.checked) {
        fields.style.display = 'flex';
        projectSelect.value = '';
    } else {
        fields.style.display = 'none';
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Paul Pastor\DGConstruction\resources\views/admin/gallery/index.blade.php ENDPATH**/ ?>