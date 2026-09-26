<header class="topbar">
    <div class="topbar-left">
        <button class="menu-button" id="sidebarToggle" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-breadcrumb"><?php echo $__env->yieldContent('page_title', 'Management Dashboard'); ?></div>
    </div>

    <div class="topbar-right ms-auto d-flex align-items-center gap-3">
        <a class="topbar-icon position-relative enhanced-icon notification-bell <?php echo e(($adminUnreadCount ?? 0) > 0 ? 'notification-bell-animate' : ''); ?>" href="<?php echo e(route('admin.alerts')); ?>" aria-label="Notifications" title="Notifications">
            <i class="bi bi-bell"></i>
            <?php if(($adminUnreadCount ?? 0) > 0): ?>
                <span id="notif-badge" class="position-absolute d-inline-flex align-items-center justify-content-center" style="top:3px; right:3px; min-width:1.15rem; height:1.15rem; padding:0 0.2rem; background:#2a4028; border-radius:999px; border:2px solid #fff; font-size:0.68rem; line-height:1; color:#fff;"><?php echo e($adminUnreadCount); ?></span>
            <?php endif; ?>
        </a>

        <div class="profile-dropdown-wrapper position-relative">
            <button class="topbar-icon enhanced-icon profile-toggle" type="button" id="profileDropdownToggle" aria-label="Profile menu" title="Profile">
                <i class="bi bi-person-fill"></i>
            </button>
            
            <div class="profile-dropdown-menu" id="profileDropdownMenu">
                <div class="profile-card">
                    <div class="profile-avatar overflow-hidden">
                        <?php if(auth()->user()->profile_photo): ?>
                            <img src="<?php echo e(asset('storage/' . ltrim(auth()->user()->profile_photo, '/'))); ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <?php echo e(strtoupper(substr(auth()->user()->name ?? 'A', 0, 1))); ?>

                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name"><?php echo e(auth()->user()->name ?? 'Admin'); ?></div>
                        <div class="profile-role">Administrator</div>
                    </div>
                </div>
                <div class="profile-divider"></div>
                <a href="<?php echo e(route('admin.profile')); ?>" class="profile-menu-item">
                    <i class="bi bi-person"></i> Profile
                </a>
                <form id="logout-form-topbar" action="<?php echo e(route('logout')); ?>" method="POST" style="display:none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="profile-menu-item profile-logout" id="logoutButtonTopbar">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </div>
        </div>

        <div class="topbar-date d-none d-md-block"><?php echo e(now()->format('D, M d, Y')); ?></div>
    </div>
</header>
<?php /**PATH C:\Users\Paul Pastor\DGConstruction\resources\views\partials\admin\topbar.blade.php ENDPATH**/ ?>