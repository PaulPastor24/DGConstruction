<div>
    <h1><?php echo e(__('passkeys::passkeys.passkeys')); ?></h1>
    <div class="mt-2">
        <form id="passkeyForm" wire:submit="validatePasskeyProperties" class="flex items-center space-x-2">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700"><?php echo e(__('passkeys::passkeys.name')); ?></label>
                <input autocomplete="off" type="text" wire:model="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-500 text-sm"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="mt-6 inline-flex justify-center py-2 px-4 font-medium">
                <?php echo e(__('passkeys::passkeys.create')); ?>

            </button>
        </form>
    </div>

    <div class="mt-6">
        <ul class="space-y-4">
            <?php $__currentLoopData = $passkeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $passkey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex justify-between items-center p-4 bg-gray-100 rounded-lg shadow-sm">
                    <div class="text-gray-700">
                        <?php echo e($passkey->name); ?>

                    </div>
                    <div class="ml-2">
                        <?php echo e(__('passkeys::passkeys.last_used')); ?>: <?php echo e($passkey->last_used_at?->diffForHumans() ?? __('passkeys::passkeys.not_used_yet')); ?>

                    </div>


                    <div>
                        <button wire:click="deletePasskey(<?php echo e($passkey->id); ?>)" class="inline-flex justify-center py-2 px-4 text-sm font-medium text-white bg-red-600">
                            <?php echo e(__('passkeys::passkeys.delete')); ?>

                        </button>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
</div>

<?php echo $__env->make('passkeys::livewire.partials.createScript', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\Users\Paul Pastor\DGConstruction\vendor\spatie\laravel-passkeys\resources\views\livewire\passkeys.blade.php ENDPATH**/ ?>