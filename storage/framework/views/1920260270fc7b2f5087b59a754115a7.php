<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-logo">
        <div class="logo-badge">
            <div class="logo-icon">
                <img src="<?php echo e(asset('images/image.png')); ?>" alt="D&G Logo">
            </div>
            <div>
                <div class="logo-text">D&G Dev't Corp.</div>
                <div class="logo-sub">Construction Management System</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Admin navigation">
        <div class="nav-section-label">Overview</div>
        <a class="nav-item <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">
            <i class="bi bi-grid-1x2-fill"></i><span>Management Dashboard</span>
        </a>
        <div class="nav-section-label">Monitoring & Progress</div>
        <a class="nav-item <?php echo e(request()->routeIs('admin.timeline') ? 'active' : ''); ?>" href="<?php echo e(route('admin.timeline')); ?>">
            <i class="bi bi-bar-chart-steps"></i><span>Project Milestones</span>
        </a>
        <a class="nav-item <?php echo e(request()->routeIs('admin.reports.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.reports.index')); ?>">
            <i class="bi bi-file-earmark-text"></i><span>Progress Report</span>
        </a>
        <a class="nav-item <?php echo e(request()->routeIs('admin.phases') ? 'active' : ''); ?>" href="<?php echo e(route('admin.phases')); ?>">
            <i class="bi bi-kanban"></i><span>Phase Management</span>
        </a>
        <div class="nav-section-label">Materials & Attendance</div>
        <a class="nav-item <?php echo e(request()->routeIs('admin.attendance') ? 'active' : ''); ?>" href="<?php echo e(route('admin.attendance')); ?>">
            <i class="bi bi-person-badge"></i><span>Worker Attendance</span>
        </a>
        <a class="nav-item <?php echo e(request()->routeIs('admin.inventory*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.inventory')); ?>">
            <i class="bi bi-box-seam"></i><span>Materials & Inventory</span>
        </a>
        <div class="nav-section-label">Management</div>
        <a class="nav-item <?php echo e(request()->routeIs('admin.projects.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.projects.index')); ?>">
            <i class="bi bi-building"></i><span>Project Management</span>
        </a>
        <a class="nav-item <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users.index')); ?>">
            <i class="bi bi-people"></i><span>User Management</span>
        </a>
        <div class="nav-section-label">System</div>
        <a class="nav-item <?php echo e(request()->routeIs('admin.landing-gallery.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.landing-gallery.index')); ?>">
            <i class="bi bi-images"></i><span>Landing Page Gallery</span>
        </a>
        <a class="nav-item <?php echo e(request()->routeIs('admin.alerts*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.alerts')); ?>">
            <i class="bi bi-bell"></i><span>Notifications</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar overflow-hidden">
                <?php if(auth()->user()->profile_photo): ?>
                    <img src="<?php echo e(asset('storage/' . ltrim(auth()->user()->profile_photo, '/'))); ?>" alt="Profile photo" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <?php echo e(strtoupper(substr(auth()->user()->name ?? 'A', 0, 1))); ?>

                <?php endif; ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo e(auth()->user()->name ?? 'Admin'); ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
    </div>
</aside>
<?php /**PATH C:\xampp\htdocs\dashboard\resources\views\partials\admin\sidebar.blade.php ENDPATH**/ ?>