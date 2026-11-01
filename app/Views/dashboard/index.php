<?php
$user = current_user();
$totalEmployees    = $employeeMetrics['total'] ?? 0;
$activeEmployees   = $employeeMetrics['active'] ?? 0;
$inactiveEmployees = $employeeMetrics['inactive'] ?? 0;
$onLeaveEmployees  = $employeeMetrics['on_leave'] ?? 0;
?>

<!-- =============================================================
     4 ESSENTIAL HR KPI CARDS
     ============================================================= -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-metric-card" style="--card-accent: var(--hrm-accent);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="hrm-metric-value"><?= number_format($totalEmployees) ?></div>
                    <div class="hrm-metric-label">Total Workforce</div>
                </div>
                <div class="hrm-metric-icon teal">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                <span>Enrolled Staff</span>
                <span class="fw-semibold text-dark"><?= $totalEmployees ?> Members</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-metric-card" style="--card-accent: var(--hrm-primary);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="hrm-metric-value text-dark"><?= number_format($activeEmployees) ?></div>
                    <div class="hrm-metric-label">Active Staff</div>
                </div>
                <div class="hrm-metric-icon emerald">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                <span>Operational</span>
                <span class="fw-semibold text-dark">
                    <?= $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100) : 0 ?>% Active Rate
                </span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-metric-card" style="--card-accent: var(--hrm-peach);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="hrm-metric-value" style="color: var(--hrm-peach-text);"><?= number_format($onLeaveEmployees) ?></div>
                    <div class="hrm-metric-label">On Leave</div>
                </div>
                <div class="hrm-metric-icon amber">
                    <i class="bi bi-calendar2-range-fill"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                <span>Approved Absence</span>
                <span class="fw-semibold" style="color: var(--hrm-peach-text);">Vacation &amp; Sick</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-metric-card" style="--card-accent: #64748b;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="hrm-metric-value text-secondary"><?= number_format($inactiveEmployees) ?></div>
                    <div class="hrm-metric-label">Inactive Records</div>
                </div>
                <div class="hrm-metric-icon slate">
                    <i class="bi bi-person-x-fill"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                <span>Departed / Suspended</span>
                <span class="fw-semibold text-dark">Archived</span>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     COMPANY NOTICE BOARD & ANNOUNCEMENT BROADCAST (ADMIN)
     ============================================================= -->
<div class="hrm-panel mb-4">
    <div class="hrm-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-megaphone-fill" style="color: var(--hrm-accent, #9DE500);"></i>
                Company Notice Board &amp; Announcements
            </h6>
            <small class="text-muted">Broadcast notices, holiday schedules, and urgent alerts across all employee portals.</small>
        </div>
        <button type="button" class="btn btn-dark btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newAnnouncementModal" style="background-color: #101828; color: #fff;">
            <i class="bi bi-plus-lg me-1" style="color: #9DE500;"></i> Post Announcement
        </button>
    </div>
    <div class="hrm-panel-body py-3">
        <?php if (empty($announcements)): ?>
            <p class="text-muted small mb-0 text-center py-2">No active announcements currently posted. Click "Post Announcement" to create one.</p>
        <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($announcements as $ann): 
                    $priorityClass = ($ann['priority'] === 'urgent') ? 'border-danger bg-danger bg-opacity-10' : (($ann['priority'] === 'warning') ? 'border-warning bg-warning bg-opacity-10' : 'border-info bg-info bg-opacity-10');
                    $priorityBadge = ($ann['priority'] === 'urgent') ? 'bg-danger text-white' : (($ann['priority'] === 'warning') ? 'bg-warning text-dark' : 'bg-info text-dark');
                ?>
                    <div class="p-3 rounded-3 border d-flex justify-content-between align-items-start <?= $priorityClass ?>">
                        <div class="me-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge <?= $priorityBadge ?> text-uppercase small" style="font-size: 0.68rem;"><?= strtoupper($ann['priority']) ?></span>
                                <h6 class="fw-bold mb-0 text-dark"><?= e($ann['title']) ?></h6>
                                <small class="text-muted font-monospace" style="font-size: 0.72rem;">&bull; Posted <?= date('M d, Y', strtotime($ann['created_at'])) ?></small>
                            </div>
                            <p class="small text-secondary mb-0"><?= e($ann['message']) ?></p>
                        </div>
                        <form method="POST" action="<?= url('announcements/delete') ?>" onsubmit="return confirm('Delete this announcement from the notice board?');">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="announcement_id" value="<?= (int)$ann['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Delete Announcement">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- =============================================================
     ANALYTICS: ATTENDANCE TREND & DEPARTMENT DISTRIBUTION
     ============================================================= -->
<div class="row g-3 mb-4">
    <!-- Left: Weekly Attendance Chart -->
    <div class="col-12 col-xl-7">
        <div class="hrm-panel h-100">
            <div class="hrm-panel-header">
                <h6 class="hrm-panel-title">
                    <i class="bi bi-bar-chart-fill" style="color: var(--hrm-accent);"></i>
                    Weekly Attendance Overview
                </h6>
                <a href="<?= url('attendance') ?>" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">
                    Detailed Logs &rarr;
                </a>
            </div>
            <div class="hrm-panel-body">
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="attendanceAnalyticsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Department Distribution -->
    <div class="col-12 col-xl-5">
        <div class="hrm-panel h-100">
            <div class="hrm-panel-header">
                <h6 class="hrm-panel-title">
                    <i class="bi bi-buildings-fill" style="color: var(--hrm-accent);"></i>
                    Department Headcount
                </h6>
                <a href="<?= url('departments') ?>" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">
                    Manage Roles &rarr;
                </a>
            </div>
            <div class="hrm-panel-body">
                <?php if (empty($departments)): ?>
                    <p class="text-muted small text-center my-4">No department records available.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($departments as $dept): 
                            $deptCount = (int)($dept['employee_count'] ?? 0);
                            $pct = $totalEmployees > 0 ? round(($deptCount / $totalEmployees) * 100) : 0;
                        ?>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark small">
                                        <?= e($dept['name']) ?>
                                    </span>
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.75rem;">
                                        <?= $deptCount ?> <?= $deptCount === 1 ? 'member' : 'members' ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 9999px;">
                                    <div class="progress-bar" style="width: <?= $pct ?>%; background-color: var(--hrm-accent);" title="<?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     BOTTOM ROW: RECENT ONBOARDING & AUDIT TRAIL
     ============================================================= -->
<div class="row g-3">
    <!-- Recent Staff Onboarding -->
    <div class="col-12 col-xl-7">
        <div class="hrm-panel h-100">
            <div class="hrm-panel-header">
                <h6 class="hrm-panel-title">
                    <i class="bi bi-clock-history" style="color: var(--hrm-emerald);"></i>
                    Recent Staff Onboarding
                </h6>
                <a href="<?= url('employees') ?>" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">
                    View Full Directory &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="table hrm-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentEmployees)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No employees found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentEmployees as $emp): 
                                $initials = strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1));
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="hrm-avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                <?= e($initials) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark small"><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                                                <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($emp['employee_code']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border small"><?= e($emp['department_name']) ?></span></td>
                                    <td class="small text-secondary"><?= e($emp['designation_title']) ?></td>
                                    <td>
                                        <span class="badge-status <?= e($emp['employment_status']) ?>">
                                            <?= e(str_replace('_', ' ', $emp['employment_status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Security Audit Logs Stream -->
    <div class="col-12 col-xl-5">
        <div class="hrm-panel h-100">
            <div class="hrm-panel-header">
                <h6 class="hrm-panel-title">
                    <i class="bi bi-shield-check" style="color: var(--hrm-accent);"></i>
                    Latest Security Activity
                </h6>
                <a href="<?= url('audit-logs') ?>" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold">
                    Full Audit Log &rarr;
                </a>
            </div>
            <div class="hrm-panel-body p-0">
                <?php if (empty($recentAuditLogs)): ?>
                    <p class="text-muted small text-center my-4">No recent activity recorded.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($recentAuditLogs, 0, 5) as $log): ?>
                            <div class="list-group-item px-3 py-2 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge bg-dark font-monospace" style="font-size: 0.68rem;"><?= e($log['action']) ?></span>
                                    <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e(date('H:i:s', strtotime($log['created_at']))) ?></small>
                                </div>
                                <div class="small text-dark fw-semibold text-truncate"><?= e($log['description'] ?? 'System event') ?></div>
                                <div class="small text-muted" style="font-size: 0.72rem;">
                                    By <?= e($log['full_name'] ?? $log['username'] ?? 'System') ?> &bull; <span class="font-monospace"><?= e($log['ip_address'] ?? '127.0.0.1') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     MODAL: BROADCAST NEW ANNOUNCEMENT (ADMIN)
     ============================================================= -->
<div class="modal fade" id="newAnnouncementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-megaphone-fill text-teal me-1" style="color: var(--hrm-accent, #9DE500);"></i>
                    Broadcast New Announcement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('announcements/create') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Announcement Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Eid Holidays Schedule or Townhall Meeting" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Priority Level <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            <option value="info">📌 Information / General Notice</option>
                            <option value="warning">⚠️ Important / Action Required</option>
                            <option value="urgent" selected>🚨 Urgent / Official Holiday</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Announcement Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Provide full details for the notice to be displayed on employee portals..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm px-4 fw-semibold" style="background-color: #101828; color: #fff;">
                        <i class="bi bi-send me-1" style="color: #9DE500;"></i> Publish Notice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
