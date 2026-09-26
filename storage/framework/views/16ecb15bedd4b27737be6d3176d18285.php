<?php $__env->startSection('title', 'Edit User'); ?>
<?php $__env->startSection('page_title', 'Edit User'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Edit User</h4>
            <p class="text-muted">Update the selected user account.</p>
            <form action="<?php echo e(route('admin.users.update', $user->user_id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" value="<?php echo e(old('first_name', $user->first_name)); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo e(old('last_name', $user->last_name)); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="contact_number" value="<?php echo e(old('contact_number', $user->contact_number)); ?>" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="engineer" <?php echo e(old('role', $user->role) === 'engineer' ? 'selected' : ''); ?>>Engineer</option>
                            <option value="supervisor" <?php echo e(old('role', $user->role) === 'supervisor' ? 'selected' : ''); ?>>Supervisor</option>
                            <option value="client" <?php echo e(old('role', $user->role) === 'client' ? 'selected' : ''); ?>>Client</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" <?php echo e(old('is_active', $user->is_active) ? 'selected' : ''); ?>>Active</option>
                            <option value="0" <?php echo e(!old('is_active', $user->is_active) ? 'selected' : ''); ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">New Password (optional)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Paul Pastor\DGConstruction\resources\views\admin\users\edit.blade.php ENDPATH**/ ?>