<?php $__env->startSection('title', 'Project Archives - D&G Construction Monitor'); ?>
<?php $__env->startSection('page_title', 'Project Archives'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Archived Projects</h2>
            <p class="text-muted mb-0">Review archived project snapshots and manage historical records.</p>
        </div>
        <a href="<?php echo e(route('admin.projects.index')); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Projects
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form id="archiveFilterForm" method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" id="archiveSearch" name="search" value="<?php echo e(request('search')); ?>" class="form-control" placeholder="Project name, location, or client">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Client</label>
                    <select id="archiveClientFilter" name="client_id" class="form-select">
                        <option value="">All Clients</option>
                        <?php $__currentLoopData = $filterClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($client->client_id); ?>" <?php echo e(request('client_id') == $client->client_id ? 'selected' : ''); ?>><?php echo e($client->company_name ?? 'Client'); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Engineer</label>
                    <select id="archiveEngineerFilter" name="engineer_id" class="form-select">
                        <option value="">All Engineers</option>
                        <?php $__currentLoopData = $filterEngineers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $engineer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($engineer->user_id); ?>" <?php echo e(request('engineer_id') == $engineer->user_id ? 'selected' : ''); ?>><?php echo e($engineer->full_name ?? $engineer->name ?? 'Engineer'); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <a href="<?php echo e(route('admin.project-archives.index')); ?>" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive rounded-3 border overflow-hidden">
                <table class="table align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Project</th>
                            <th>Location</th>
                            <th>Client</th>
                            <th>Engineer</th>
                            <th>Timeline</th>
                            <th>Archived At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $archives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $archive): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $archiveClientLabel = trim((string) ($archive->client?->company_name ?? ''));
                                if ($archiveClientLabel === '' || strtolower($archiveClientLabel) === 'd&g construction corp') {
                                    $archiveClientLabel = null;
                                }

                                $archiveClientContact = trim((string) ($archive->client?->user?->name ?? ''));
                                if ($archiveClientContact === '' || strtolower($archiveClientContact) === 'd&g construction corp') {
                                    $archiveClientContact = null;
                                }

                                $archiveEngineerLabel = trim((string) ($archive->engineer?->full_name ?: $archive->engineer?->name ?? ''));
                                if ($archiveEngineerLabel === '' || strtolower($archiveEngineerLabel) === 'lead engineer') {
                                    $archiveEngineerLabel = null;
                                }

                                $archiveEngineerEmail = trim((string) ($archive->engineer?->email ?? ''));
                                if ($archiveEngineerEmail === '' || strtolower($archiveEngineerEmail) === 'lead engineer') {
                                    $archiveEngineerEmail = null;
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo e($archive->project_name); ?></div>
                                    <div class="small text-muted">#<?php echo e($archive->project_id); ?></div>
                                </td>
                                <td><?php echo e($archive->project_location ?: optional($archive->project)->location ?: optional($archive->project)->project_location ?: '—'); ?></td>
                                <td>
                                    <div class="fw-semibold small text-dark"><?php echo e($archiveClientLabel); ?></div>
                                    <div class="small text-muted"><?php echo e($archiveClientContact); ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold small text-dark"><?php echo e($archiveEngineerLabel); ?></div>
                                    <div class="small text-muted"><?php echo e($archiveEngineerEmail); ?></div>
                                </td>
                                <td>
                                    <div class="small text-muted">Start: <?php echo e($archive->start_date ? $archive->start_date->format('M d, Y') : '—'); ?></div>
                                    <div class="small text-muted">Target: <?php echo e($archive->target_end_date ? $archive->target_end_date->format('M d, Y') : '—'); ?></div>
                                    <div class="small text-muted">Actual: <?php echo e($archive->actual_end_date ? $archive->actual_end_date->format('M d, Y') : '—'); ?></div>
                                </td>
                                <td><?php echo e($archive->archived_at ? $archive->archived_at->format('M d, Y H:i') : '—'); ?></td>
                                <td>
                                    <?php if($archive->project): ?>
                                        <form action="<?php echo e(route('admin.projects.restore', $archive->project)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn btn-sm" style="border-color:#c8e6c9; color:#166534; background:#f6fff7;">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No archived projects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($archives->hasPages()): ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing <?php echo e($archives->firstItem() ?: 0); ?> to <?php echo e($archives->lastItem() ?: 0); ?> of <?php echo e($archives->total()); ?> archived projects
                    </div>
                    <nav aria-label="Archived projects pagination">
                        <ul class="pagination pagination-sm mb-0" style="display:flex; gap:4px;">
                            <li class="page-item <?php echo e($archives->onFirstPage() ? 'disabled' : ''); ?>">
                                <a class="page-link" href="<?php echo e($archives->previousPageUrl() ?? '#'); ?>" style="color:#198754;" aria-label="Previous page">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for($page = 1; $page <= $archives->lastPage(); $page++): ?>
                                <li class="page-item <?php echo e($page == $archives->currentPage() ? 'active' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($archives->url($page)); ?>" style="<?php echo e($page == $archives->currentPage() ? 'background-color:#198754; border-color:#198754; color:#fff;' : 'color:#198754;'); ?>"><?php echo e($page); ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo e($archives->hasMorePages() ? '' : 'disabled'); ?>">
                                <a class="page-link" href="<?php echo e($archives->nextPageUrl() ?? '#'); ?>" style="color:#198754;" aria-label="Next page">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('archiveFilterForm');
    const search = document.getElementById('archiveSearch');
    const clientFilter = document.getElementById('archiveClientFilter');
    const engineerFilter = document.getElementById('archiveEngineerFilter');

    if (form && search) {
        search.addEventListener('input', function () {
            form.submit();
        });
    }

    [clientFilter, engineerFilter].forEach(function (element) {
        if (element) {
            element.addEventListener('change', function () {
                form && form.submit();
            });
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Paul Pastor\DGConstruction\resources\views\admin\project_archives\index.blade.php ENDPATH**/ ?>