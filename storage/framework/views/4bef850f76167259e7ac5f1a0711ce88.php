<div>
    <?php echo $__env->make('passkeys::components.partials.authenticateScript', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form id="passkey-login-form" method="POST" action="<?php echo e(route('passkeys.login')); ?>">
        <?php echo csrf_field(); ?>
    </form>

    <?php if($message = session()->get('authenticatePasskey::message')): ?>
        <div class="bg-red-100 text-red-700 p-4 border border-red-400 rounded">
            <?php echo e($message); ?>

        </div>
    <?php endif; ?>

    <div onclick="authenticateWithPasskey()">
        <?php if($slot->isEmpty()): ?>
            <div class="underline cursor-pointer">
                <?php echo e(__('passkeys::passkeys.authenticate_using_passkey')); ?>

            </div>
        <?php else: ?>
            <?php echo e($slot); ?>

        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Paul Pastor\DGConstruction\vendor\spatie\laravel-passkeys\resources\views\components\authenticate.blade.php ENDPATH**/ ?>