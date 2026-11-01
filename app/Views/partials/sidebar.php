<?php
$activeNav = $activeNav ?? 'dashboard';
$user = current_user();
?>
<aside class="hrm-sidebar" id="hrmSidebar">

    <!-- Navigation Menu -->
    <div class="hrm-sidebar-nav">
        <?php if (has_role('employee')): ?>
            <!-- Employee Self-Service Navigation -->
            <div class="hrm-nav-section-title hrm-nav-toggle-row d-flex align-items-center justify-content-between">
                <span class="hrm-nav-section-label">My Workspace</span>
                <button type="button" class="hrm-sidebar-toggle-btn js-sidebar-toggle" id="sidebarCollapseBtn" aria-label="Toggle navigation menu" title="Collapse sidebar">
                    <i class="bi bi-chevron-left toggle-icon"></i>
                </button>
            </div>
            <a href="<?= url('dashboard?tab=my-workspace') ?>" class="hrm-nav-link <?= in_array($activeNav, ['dashboard', 'my-workspace']) ? 'active' : '' ?>" data-tab-target="my-workspace">
                <i class="bi bi-house-door-fill"></i>
                <span>Self-Service Overview</span>
            </a>
            <a href="<?= url('dashboard?tab=my-profile') ?>" class="hrm-nav-link <?= $activeNav === 'my-profile' ? 'active' : '' ?>" data-tab-target="my-profile">
                <i class="bi bi-person-badge-fill"></i>
                <span>My Job &amp; Profile</span>
            </a>

            <div class="hrm-nav-section-title mt-3">Work Life</div>
            <a href="<?= url('dashboard?tab=my-attendance') ?>" class="hrm-nav-link <?= $activeNav === 'my-attendance' ? 'active' : '' ?>" data-tab-target="my-attendance">
                <i class="bi bi-clock-history"></i>
                <span>My Attendance</span>
            </a>
            <a href="<?= url('dashboard?tab=my-leaves') ?>" class="hrm-nav-link <?= $activeNav === 'my-leaves' ? 'active' : '' ?>" data-tab-target="my-leaves">
                <i class="bi bi-calendar2-check-fill"></i>
                <span>Leaves &amp; Time Off</span>
            </a>
            <a href="<?= url('dashboard?tab=my-payroll') ?>" class="hrm-nav-link <?= $activeNav === 'my-payroll' ? 'active' : '' ?>" data-tab-target="my-payroll">
                <i class="bi bi-receipt"></i>
                <span>My Payslips</span>
            </a>

            <div class="hrm-nav-section-title mt-3">Department</div>
            <a href="<?= url('dashboard?tab=my-team') ?>" class="hrm-nav-link <?= $activeNav === 'my-team' ? 'active' : '' ?>" data-tab-target="my-team">
                <i class="bi bi-people-fill"></i>
                <span>My Team</span>
            </a>
        <?php else: ?>
            <!-- Administrator & HR Officer Navigation -->
            <div class="hrm-nav-section-title hrm-nav-toggle-row d-flex align-items-center justify-content-between">
                <span class="hrm-nav-section-label">Core Workspace</span>
                <button type="button" class="hrm-sidebar-toggle-btn js-sidebar-toggle" id="sidebarCollapseBtn" aria-label="Toggle navigation menu" title="Collapse sidebar">
                    <i class="bi bi-chevron-left toggle-icon"></i>
                </button>
            </div>
            <a href="<?= url('dashboard') ?>" class="hrm-nav-link <?= in_array($activeNav, ['dashboard', 'overview']) ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Executive Overview</span>
            </a>

            <div class="hrm-nav-section-title mt-3">Organization</div>
            <a href="<?= url('employees') ?>" class="hrm-nav-link <?= $activeNav === 'employees' ? 'active' : '' ?>">
                <i class="bi bi-person-lines-fill"></i>
                <span>Staff Directory</span>
            </a>
            <a href="<?= url('departments') ?>" class="hrm-nav-link <?= in_array($activeNav, ['departments', 'designations']) ? 'active' : '' ?>">
                <i class="bi bi-diagram-3-fill"></i>
                <span>Departments &amp; Roles</span>
            </a>

            <div class="hrm-nav-section-title mt-3">Human Capital</div>
            <a href="<?= url('attendance') ?>" class="hrm-nav-link <?= $activeNav === 'attendance' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check-fill"></i>
                <span>Attendance Roster</span>
            </a>
            <?php 
            $pendingLeavesBadge = 0;
            try { $pendingLeavesBadge = \App\Models\LeaveRequest::countPending(); } catch (\Throwable $e) {}
            ?>
            <a href="<?= url('leaves') ?>" class="hrm-nav-link <?= $activeNav === 'leaves' ? 'active' : '' ?>">
                <i class="bi bi-inbox-fill"></i>
                <span>Leave Approvals</span>
                <?php if ($pendingLeavesBadge > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 0.7rem;"><?= $pendingLeavesBadge ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= url('payroll') ?>" class="hrm-nav-link <?= $activeNav === 'payroll' ? 'active' : '' ?>">
                <i class="bi bi-cash-stack"></i>
                <span>Payroll &amp; Finance</span>
            </a>

            <?php if (has_role('admin') || has_permission('audit.view')): ?>
            <div class="hrm-nav-section-title mt-3">System Security</div>
            <a href="<?= url('audit-logs') ?>" class="hrm-nav-link <?= $activeNav === 'audit' ? 'active' : '' ?>">
                <i class="bi bi-shield-check"></i>
                <span>Security Audit Logs</span>
            </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Sidebar Bottom System Badge -->
    <div class="hrm-sidebar-footer">
        <div class="d-flex align-items-center gap-2 text-muted small">
            <span class="badge bg-success rounded-circle p-1" style="width: 8px; height: 8px;"></span>
            <span>PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?> • <?= e(strtoupper(env('APP_ENV', 'DEV'))) ?></span>
        </div>
    </div>
</aside>
<!-- Mobile Backdrop -->
<div class="hrm-sidebar-backdrop" id="hrmSidebarBackdrop"></div>
