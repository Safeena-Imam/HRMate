<?php
$user = current_user();
$initials = 'U';
if (!empty($user['first_name'])) {
    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'] ?? '', 0, 1));
} elseif (!empty($user['username'])) {
    $initials = strtoupper(substr($user['username'], 0, 2));
}
?>
<header class="hrm-topbar">
    <div class="hrm-topbar-left d-flex align-items-center">
    <!-- System Brand: Official HRMate Logo (Themed to Parakeet Lime & Charcoal) -->
    <a href="<?= url('dashboard') ?>" class="d-inline-flex align-items-center text-decoration-none py-1">
        <img src="<?= asset('images/logo-parakeet.png') ?>" alt="HRMate Logo" class="hrm-brand-img">
    </a>
    </div>

    <div class="d-flex align-items-center gap-2 gap-md-3">

        <!-- Refresh Button -->
        <button type="button" class="hrm-toggle-btn d-none d-md-flex align-items-center justify-content-center"
                onclick="window.location.reload();" title="Refresh dashboard data"
                style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--hrm-border); background: var(--hrm-border-subtle);">
            <i class="bi bi-arrow-clockwise" style="font-size: 1rem;"></i>
        </button>

        <!-- Org Pill -->
        <div class="d-none d-xl-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill border">
            <span style="background-color: var(--hrm-emerald); width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0;"></span>
            <small class="fw-semibold text-secondary" style="font-size: 0.75rem; white-space: nowrap;">
                <?= e($user['department_name'] ?? 'Human Resources') ?>
            </small>
        </div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <button class="hrm-user-pill dropdown-toggle border-0" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="hrm-user-avatar">
                    <?= e($initials) ?>
                </div>
                <div class="text-start d-none d-sm-block me-1">
                    <div class="fw-bold" style="font-size: 0.88rem; line-height: 1.2;">
                        <?= e($user['full_name'] ?? $user['username'] ?? 'User') ?>
                    </div>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <span class="badge bg-secondary" style="font-size: 0.65rem;"><?= e($user['role_name'] ?? 'Employee') ?></span>
                    </small>
                </div>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" aria-labelledby="userMenuDropdown" style="border-radius: 10px; min-width: 220px;">
                <li class="px-3 py-2 border-bottom mb-2 bg-light">
                    <div class="fw-bold text-dark"><?= e($user['full_name'] ?? $user['username']) ?></div>
                    <small class="text-muted text-break"><?= e($user['email'] ?? '') ?></small><br>
                    <span class="badge bg-success-subtle text-success mt-1">Status: Active</span>
                </li>

                <li>
                    <span class="dropdown-item-text text-muted small">
                        <i class="bi bi-briefcase me-2"></i><?= e($user['designation_title'] ?? 'Staff') ?>
                    </span>
                </li>
                <li>
                    <span class="dropdown-item-text text-muted small">
                        <i class="bi bi-card-text me-2"></i>ID: <?= e($user['employee_code'] ?? 'EMP-SYSTEM') ?>
                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="<?= url('logout') ?>" method="POST" class="m-0 p-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out Securely</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
