<?php $__env->startSection('title', 'Create User'); ?>
<?php $__env->startSection('page_title', 'Create User'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Create User</h4>
            <p class="text-muted">Add a new engineer, supervisor, or client account.</p>
            <form action="<?php echo e(route('admin.users.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="contact_number" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="engineer">Engineer</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="client">Client</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Create User</button>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\dashboard\resources\views\admin\users\create.blade.php ENDPATH**/ ?>