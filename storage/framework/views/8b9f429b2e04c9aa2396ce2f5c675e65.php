<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'eyebrow' => null,
    'title',
    'description' => null,
    'date' => now()->format('D, M d, Y'),
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'eyebrow' => null,
    'title',
    'description' => null,
    'date' => now()->format('D, M d, Y'),
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="dashboard-page-header">
    <div class="dashboard-page-heading">
        <?php if($eyebrow): ?>
            <span class="dashboard-page-eyebrow"><?php echo e($eyebrow); ?></span>
        <?php endif; ?>
        <h2 class="dashboard-page-title"><?php echo e($title); ?></h2>
        <?php if($description): ?>
            <p class="dashboard-page-description"><?php echo e($description); ?></p>
        <?php endif; ?>
    </div>

    <div class="dashboard-page-tools" aria-label="Page utilities">
        <?php echo $extra ?? ''; ?>

        <div style="position: relative;">
            <button type="button" class="dashboard-notification-button notification-toggle-btn <?php echo e(($clientUnreadCount ?? 0) > 0 ? 'notification-bell-animate' : ''); ?>" style="position: relative;" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                <?php if(($clientUnreadCount ?? 0) > 0): ?>
                    <span class="notification-badge" aria-label="<?php echo e($clientUnreadCount ?? 0); ?> unread notifications"><?php echo e($clientUnreadCount ?? 0); ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Paul Pastor\DGConstruction\resources\views\client\partials\page-header.blade.php ENDPATH**/ ?>