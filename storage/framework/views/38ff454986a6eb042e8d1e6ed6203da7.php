<?php $__env->startSection('title', 'Landing Page Gallery - D&G Construction Monitor'); ?>
<?php $__env->startSection('page_title', 'Landing Page Gallery'); ?>

<?php $__env->startSection('content'); ?>
<div class="landing-gallery-page">
        <?php
        $formErrorMessage = $errors->first();
        $successMessage = session('success');
        $flashErrorMessage = session('error');
    ?>

    <?php if($successMessage): ?>
        <div class="alert alert-success mb-4 d-none" data-swal-success="<?php echo e($successMessage); ?>"><?php echo e($successMessage); ?></div>
    <?php endif; ?>
    <?php if($flashErrorMessage): ?>
        <div class="alert alert-danger mb-4 d-none" data-swal-error="<?php echo e($flashErrorMessage); ?>"><?php echo e($flashErrorMessage); ?></div>
    <?php endif; ?>
    <?php if($formErrorMessage): ?>
        <div class="alert alert-danger mb-4 d-none" data-swal-error="<?php echo e($formErrorMessage); ?>"><?php echo e($formErrorMessage); ?></div>
    <?php endif; ?>

    <div class="ug-hero-card landing-gallery-hero mb-4" aria-label="Landing page gallery header">
        <div class="dashboard-title-area">
            <h2>Manage Landing Page Gallery</h2>
            <p class="text-muted small mb-0">Upload and organize the images shown on the public landing page of your project. Add new images and keep your gallery up to date.</p>
        </div>

        <button type="submit" form="galleryCreateForm" class="ug-add-user-btn">
            <span class="ug-add-icon"><i class="bi bi-plus-lg"></i></span>
            <span>Add Image</span>
        </button>
    </div>

    <section class="landing-gallery-panel landing-gallery-panel--upload">
        <div class="landing-gallery-panel__title-wrap">
            <div class="panel-icon">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="landing-gallery-panel__title-copy">
                <h2>Project</h2>
            </div>

            <label class="external-project-toggle" for="is_external">
                <input class="form-check-input" type="checkbox" value="1" id="is_external" name="is_external" form="galleryCreateForm">
                <span>This is an external / past featured project not in the system</span>
            </label>
        </div>

        <form id="galleryCreateForm" action="<?php echo e(route('admin.landing-gallery.store')); ?>" method="POST" enctype="multipart/form-data" class="landing-gallery-form">
            <?php echo csrf_field(); ?>

            <div class="form-grid">
                <div class="field-group field-group--project">
                    <label for="project_id">Project</label>
                    <select id="project_id" name="project_id" class="form-select" required>
                        <option value="">Select a completed project</option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($project->project_id); ?>"><?php echo e($project->project_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="field-group field-group--image">
                    <label for="image">Carousel Image</label>
                    <div class="upload-field-wrap">
                        <span class="upload-field-icon"><i class="bi bi-upload"></i></span>
                        <input id="image" name="image" type="file" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                        <span class="file-name-display">No file chosen</span>
                    </div>
                </div>
            </div>

            <?php $__errorArgs = ['project_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="field-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="field-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </form>
    </section>

    <form id="galleryReorderForm" action="<?php echo e(route('admin.landing-gallery.reorder')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>
    </form>

    <section class="landing-gallery-panel landing-gallery-panel--list">
        <div class="landing-gallery-panel__header">
            <div class="landing-gallery-panel__title-wrap">
                <div class="panel-icon panel-icon--sm">
                    <i class="bi bi-card-image"></i>
                </div>
                <h3>Current carousel entries</h3>
            </div>
            <button type="submit" form="galleryReorderForm" class="btn btn-sm btn-outline-success">
                <i class="bi bi-check2 me-1"></i>Save order
            </button>
        </div>

        <?php if($galleryImages->count()): ?>
            <div class="gallery-row-list">
                <?php $__currentLoopData = $galleryImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $galleryImage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="gallery-row-item" data-gallery-row>
                        <img src="<?php echo e(asset('storage/' . ltrim($galleryImage->image_path, '/'))); ?>" alt="<?php echo e($galleryImage->display_name); ?>">
                        <div class="gallery-row-copy">
                            <div class="gallery-row-name"><?php echo e($galleryImage->display_name); ?></div>
                            <div class="gallery-row-meta">Position <?php echo e($loop->iteration); ?> · <?php echo e($galleryImage->is_active ? 'Visible' : 'Hidden'); ?></div>
                        </div>
                        <input type="hidden" name="order[]" value="<?php echo e($galleryImage->id); ?>" form="galleryReorderForm">
                        <div class="gallery-row-actions">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Change gallery position">
                                <button type="button" class="btn btn-outline-secondary" data-move-gallery="up" title="Move up"><i class="bi bi-chevron-up"></i></button>
                                <button type="button" class="btn btn-outline-secondary" data-move-gallery="down" title="Move down"><i class="bi bi-chevron-down"></i></button>
                            </div>
                            <form action="<?php echo e(route('admin.landing-gallery.toggle', $galleryImage)); ?>" method="POST" class="d-inline-block">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="btn btn-sm <?php echo e($galleryImage->is_active ? 'btn-outline-secondary' : 'btn-outline-success'); ?>">
                                    <i class="bi bi-eye<?php echo e($galleryImage->is_active ? '-slash' : ''); ?> me-1"></i><?php echo e($galleryImage->is_active ? 'Hide' : 'Show'); ?>

                                </button>
                            </form>
                            <form action="<?php echo e(route('admin.landing-gallery.destroy', $galleryImage)); ?>" method="POST" onsubmit="return confirm('Remove this image from the landing page?');" class="d-inline-block">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="gallery-empty-state">
                <div class="gallery-empty-illustration" aria-hidden="true">
                    <span class="gallery-empty-frame"></span>
                    <span class="gallery-empty-plus">+</span>
                </div>
                <h4>No images yet</h4>
                <p>Add a completed project and upload images to display them in the landing page gallery.</p>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .landing-gallery-page {
        width: 100%;
        max-width: 100%;
        padding: 6px 0 32px;
    }

    .ug-hero-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 1rem;
        padding: 1.05rem 1.15rem;
        border: 1px solid rgba(22, 101, 52, 0.10);
        border-radius: 18px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fdf9 100%);
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.045);
    }

    .dashboard-title-area h2,
    .dashboard-title-area h2 * {
        font-family: 'Syne', 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif !important;
        font-size: 28px !important;
        font-weight: 600 !important;
        color: #111827 !important;
        letter-spacing: -0.02em !important;
        margin-bottom: 6px !important;
    }

    .dashboard-title-area p {
        max-width: 760px;
        margin-top: 12px;
        margin-bottom: 0;
        color: #64748b;
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .ug-add-user-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        min-height: 46px;
        padding: 0.78rem 1.05rem;
        border: 0;
        border-radius: 14px;
        background: #1d7b4c;
        color: #ffffff;
        font-weight: 800;
        line-height: 1.15;
        box-shadow: 0 12px 22px rgba(22, 101, 52, 0.20);
        transition: transform 0.16s ease, box-shadow 0.16s ease;
    }

    .ug-add-user-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(22, 101, 52, 0.24);
    }

    .ug-add-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.14);
        flex-shrink: 0;
    }

    .landing-gallery-panel {
        background: #f6f8f7;
        border: 1px solid rgba(30, 76, 49, 0.08);
        border-radius: 18px;
        box-shadow: none;
        padding: 18px 18px 16px;
        margin-top: 0;
    }

    .landing-gallery-panel + .landing-gallery-panel {
        margin-top: 22px;
    }

    .landing-gallery-panel__title-wrap,
    .panel-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .landing-gallery-panel__title-copy {
        display: flex;
        align-items: center;
        min-width: 0;
    }

    .external-project-toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        padding: 10px 12px;
        border: 1px solid rgba(30, 76, 49, 0.12);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.72);
        color: #234936;
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.35;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .external-project-toggle:hover {
        border-color: rgba(30, 76, 49, 0.22);
        box-shadow: 0 8px 18px rgba(20, 61, 44, 0.06);
    }

    .external-project-toggle .form-check-input {
        width: 18px;
        height: 18px;
        margin-top: 0;
        border-color: rgba(28, 118, 77, 0.45);
        background-color: #fff;
        box-shadow: none;
    }

    .external-project-toggle .form-check-input:checked {
        background-color: #1d7b4c;
        border-color: #1d7b4c;
    }

    .external-project-toggle span {
        color: #2b4d41;
        font-weight: 600;
    }

    .panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: rgba(20, 119, 67, 0.12);
        color: #1b6548;
        font-size: 1rem;
    }

    .panel-icon--sm {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        font-size: 0.95rem;
    }

    .landing-gallery-panel__title-wrap h2,
    .landing-gallery-panel__title-wrap h3,
    .landing-gallery-panel h2,
    .landing-gallery-panel h3 {
        margin: 0;
        color: #1b3d30;
        font-size: 1.15rem;
        line-height: 1.2;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .landing-gallery-panel__title-wrap h3,
    .landing-gallery-panel h3 {
        font-size: 1.05rem;
        letter-spacing: -0.02em;
        font-weight: 800;
    }

    .landing-gallery-form {
        margin-top: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1.35fr);
        gap: 18px;
        align-items: end;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .field-group label {
        color: #1f3b2d;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .form-select,
    .form-control {
        height: 54px;
        border: 1px solid rgba(146, 167, 156, 0.65);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.7);
        color: #123120;
        font-size: 1rem;
        box-shadow: none;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: rgba(47, 117, 78, 0.7);
        box-shadow: 0 0 0 0.2rem rgba(47, 117, 78, 0.12);
    }

    .upload-field-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .upload-field-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(25, 82, 58, 0.8);
        font-size: 1.1rem;
        z-index: 1;
    }

    .upload-field-wrap .form-control {
        width: 100%;
        padding-left: 42px;
        padding-right: 128px;
    }

    .upload-field-wrap .file-name-display {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        max-width: 110px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: rgba(50, 66, 58, 0.78);
        font-size: 0.8rem;
        font-weight: 600;
        pointer-events: none;
    }

    .field-error {
        margin-top: 10px;
        color: #d13d3d;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .landing-gallery-panel--list {
        padding-bottom: 0;
    }

    .landing-gallery-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .gallery-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 430px;
        text-align: center;
        background: rgba(230, 239, 235, 0.62);
        border: 1px solid rgba(42, 79, 58, 0.08);
        border-radius: 16px;
        padding: 30px 20px;
    }

    .gallery-empty-illustration {
        position: relative;
        width: 142px;
        height: 118px;
        margin-bottom: 18px;
    }

    .gallery-empty-frame {
        position: absolute;
        inset: 16px 18px 18px 16px;
        border-radius: 12px;
        border: 3px solid rgba(18, 93, 64, 0.9);
        background: linear-gradient(135deg, rgba(160, 200, 171, 0.48) 0%, rgba(231, 244, 236, 0.85) 100%);
        box-shadow: inset 0 0 0 8px rgba(19, 95, 69, 0.05);
    }

    .gallery-empty-frame::before,
    .gallery-empty-frame::after {
        content: "";
        position: absolute;
        background: rgba(20, 120, 82, 0.28);
    }

    .gallery-empty-frame::before {
        left: 18px;
        right: 18px;
        bottom: 18px;
        height: 18px;
        border-radius: 6px;
    }

    .gallery-empty-frame::after {
        left: 24px;
        top: 22px;
        width: 40px;
        height: 26px;
        border-radius: 8px;
        background: rgba(20, 120, 82, 0.36);
    }

    .gallery-empty-plus {
        position: absolute;
        right: 3px;
        bottom: 0;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(180deg, #1b7d56 0%, #166b49 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        line-height: 1;
        box-shadow: 0 10px 18px rgba(18, 99, 69, 0.2);
    }

    .gallery-empty-state h4 {
        margin: 0;
        color: #153b2b;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .gallery-empty-state p {
        max-width: 430px;
        margin: 10px auto 0;
        color: #587065;
        font-size: 0.96rem;
        line-height: 1.6;
    }

    .gallery-row-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .gallery-row-item {
        display: flex;
        align-items: center;
        gap: 18px;
        background: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(23, 79, 49, 0.09);
        border-radius: 14px;
        padding: 14px 16px;
    }

    .gallery-row-item img {
        width: 118px;
        height: 82px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid rgba(17, 62, 42, 0.08);
        flex-shrink: 0;
    }

    .gallery-row-copy {
        flex: 1 1 auto;
        min-width: 0;
    }

    .gallery-row-name {
        color: #16392d;
        font-size: 1rem;
        font-weight: 700;
    }

    .gallery-row-meta {
        margin-top: 2px;
        color: #64776b;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .gallery-row-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    @media (max-width: 1200px) {
        .ug-hero-card {
            grid-template-columns: 1fr;
            align-items: flex-start;
        }

        .ug-add-user-btn {
            justify-self: flex-start;
        }
    }

    @media (max-width: 900px) {
        .ug-hero-card {
            min-height: auto !important;
            padding: 0.7rem 0.9rem;
            gap: 0.45rem;
        }

        .dashboard-title-area {
            min-width: 0;
            max-width: 100%;
        }

        .dashboard-title-area h2,
        .dashboard-title-area h2 * {
            font-size: 1.45rem !important;
            line-height: 1.25 !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .dashboard-title-area p {
            font-size: 0.74rem;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .gallery-row-item {
            flex-wrap: wrap;
        }

        .gallery-row-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .landing-gallery-page {
            padding-top: 4px;
        }

        .ug-hero-card {
            min-height: auto !important;
            padding: 0.7rem 0.8rem;
        }

        .dashboard-title-area {
            width: 100%;
        }

        .dashboard-title-area h2,
        .dashboard-title-area h2 * {
            font-size: 1.05rem !important;
            line-height: 1.35 !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .dashboard-title-area p {
            font-size: 0.72rem;
        }

        .ug-add-user-btn {
            width: 100%;
        }

        .landing-gallery-panel {
            padding: 14px 12px 12px;
        }

        .gallery-empty-state {
            min-height: 300px;
            padding: 20px 16px;
        }

        .gallery-row-item {
            display: grid;
            grid-template-columns: 96px 1fr;
            align-items: center;
            gap: 12px;
            padding: 12px;
        }

        .gallery-row-item img {
            width: 96px;
            height: 72px;
        }

        .gallery-row-copy {
            min-width: 0;
        }

        .gallery-row-actions {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }
    }

    @media (max-width: 575px) {
        .ug-hero-card {
            grid-template-columns: 1fr !important;
        }

        .dashboard-title-area {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            overflow: visible !important;
        }

        .dashboard-title-area h2,
        .dashboard-title-area h2 * {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            font-size: 0.9rem !important;
            line-height: 1.45 !important;
            letter-spacing: -0.02em !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
    }

    @media (max-width: 480px) {
        .upload-field-wrap {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .upload-field-icon {
            display: none;
        }

        .upload-field-wrap .form-control {
            padding-left: 14px;
            padding-right: 14px;
        }

        .upload-field-wrap .file-name-display {
            position: static;
            transform: none;
            display: block;
            width: 100%;
            max-width: none;
            margin-top: 10px;
            padding-left: 4px;
            text-align: left;
        }

        .gallery-row-item {
            grid-template-columns: 1fr;
        }

        .gallery-row-item img {
            width: 100%;
            height: 180px;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('image');
        const externalToggle = document.getElementById('is_external');
        const projectSelect = document.getElementById('project_id');

        const syncProjectRequirement = () => {
            if (!externalToggle || !projectSelect) {
                return;
            }

            projectSelect.required = !externalToggle.checked;
            projectSelect.disabled = externalToggle.checked;
            if (externalToggle.checked) {
                projectSelect.value = '';
            }
        };

        if (externalToggle) {
            externalToggle.addEventListener('change', syncProjectRequirement);
            syncProjectRequirement();
        }

        if (fileInput) {
            const target = fileInput.closest('.upload-field-wrap');
            if (target) {
                let label = target.querySelector('.file-name-display');
                if (!label) {
                    label = document.createElement('span');
                    label.className = 'file-name-display';
                    target.appendChild(label);
                }
                label.textContent = fileInput.files && fileInput.files.length ? fileInput.files[0].name : 'No file chosen';
            }

            fileInput.addEventListener('change', function () {
                const fileName = this.files && this.files.length ? this.files[0].name : 'No file chosen';
                const target = this.closest('.upload-field-wrap');
                if (!target) {
                    return;
                }

                let label = target.querySelector('.file-name-display');
                if (!label) {
                    label = document.createElement('span');
                    label.className = 'file-name-display';
                    target.appendChild(label);
                }

                label.textContent = fileName;
            });
        }

        const swalError = document.querySelector('[data-swal-error]');
        if (swalError) {
            Swal.fire({
                icon: 'error',
                title: 'Notice',
                text: swalError.dataset.swalError,
                confirmButtonColor: '#1d7b4c',
                confirmButtonText: 'OK'
            });
        }

        const swalSuccess = document.querySelector('[data-swal-success]');
        if (swalSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: swalSuccess.dataset.swalSuccess,
                confirmButtonColor: '#1d7b4c',
                confirmButtonText: 'OK'
            });
        }

        document.querySelectorAll('[data-move-gallery]').forEach(button => {
            button.addEventListener('click', () => {
                const row = button.closest('[data-gallery-row]');
                if (!row) {
                    return;
                }

                const sibling = button.dataset.moveGallery === 'up' ? row.previousElementSibling : row.nextElementSibling;

                if (!sibling || !sibling.matches('[data-gallery-row]')) {
                    return;
                }

                if (button.dataset.moveGallery === 'up') {
                    row.parentElement.insertBefore(row, sibling);
                } else {
                    row.parentElement.insertBefore(sibling, row);
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\resources\views\admin\gallery\index.blade.php ENDPATH**/ ?>