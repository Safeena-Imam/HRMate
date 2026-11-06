<?php
$user = current_user();
$initials = 'U';
if (!empty($user['first_name'])) {
    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'] ?? '', 0, 1));
} elseif (!empty($user['username'])) {
    $initials = strtoupper(substr($user['username'], 0, 2));
}

$topbarAnnouncements = [];
try {
    $topbarAnnouncements = \App\Models\Announcement::getActive(5);
} catch (\Throwable $e) {
    $topbarAnnouncements = [];
}
$unreadCount = count($topbarAnnouncements);
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
                style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--hrm-border); background: var(--hrm-border-subtle); color: var(--hrm-text, #333);">
            <i class="bi bi-arrow-clockwise" style="font-size: 1rem;"></i>
        </button>

        <!-- Company Notifications & Announcements Bell Dropdown -->
        <div class="dropdown">
            <button class="hrm-toggle-btn d-flex align-items-center justify-content-center position-relative border-0" 
                    type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                    title="Company Notices & Announcements"
                    style="width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--hrm-border); background: var(--hrm-border-subtle); color: var(--hrm-text, #333); cursor: pointer;">
                <i class="bi bi-bell<?= $unreadCount > 0 ? '-fill text-warning' : '' ?>" style="font-size: 1.05rem;"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                          style="font-size: 0.65rem; padding: 0.22em 0.45em; border: 2px solid #fff;">
                        <?= $unreadCount ?>
                    </span>
                <?php endif; ?>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-0" aria-labelledby="notificationsDropdown" 
                style="border-radius: 12px; width: 320px; max-width: 90vw; overflow: hidden; z-index: 1060;">
                <li class="px-3 py-2 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-dark small">
                        <i class="bi bi-megaphone-fill me-1 text-primary"></i> Company Notices
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary small" style="font-size: 0.7rem;">
                        <?= $unreadCount ?> Active
                    </span>
                </li>

                <div style="max-height: 290px; overflow-y: auto;">
                    <?php if (empty($topbarAnnouncements)): ?>
                        <div class="text-center py-4 px-3 text-muted">
                            <i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>
                            <div class="small fw-semibold">No New Announcements</div>
                            <div style="font-size: 0.75rem;">You are all caught up!</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topbarAnnouncements as $ann): 
                            $isUrgent = ($ann['priority'] === 'urgent');
                            $isWarn   = ($ann['priority'] === 'warning');
                            $badgeClass = $isUrgent ? 'bg-danger text-white' : ($isWarn ? 'bg-warning text-dark' : 'bg-primary-subtle text-primary');
                            $badgeText  = strtoupper($ann['priority']);
                        ?>
                            <li class="border-bottom">
                                <a href="<?= url('dashboard') ?>" class="dropdown-item px-3 py-2 text-wrap" style="white-space: normal;">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge <?= $badgeClass ?> font-monospace" style="font-size: 0.62rem;"><?= $badgeText ?></span>
                                        <small class="text-muted" style="font-size: 0.68rem;">
                                            <?= date('M d, Y', strtotime($ann['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="fw-semibold text-dark small mb-1" style="line-height: 1.25;">
                                        <?= e($ann['title']) ?>
                                    </div>
                                    <p class="text-muted small mb-0" style="font-size: 0.75rem; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= e($ann['message']) ?>
                                    </p>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>


            </ul>
        </div>

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
