<?php
$csrf = csrf_token();
$pendingList = array_filter($leaveRequests, fn($r) => $r['status'] === 'pending');
$approvedList = array_filter($leaveRequests, fn($r) => $r['status'] === 'approved');
$rejectedList = array_filter($leaveRequests, fn($r) => $r['status'] === 'rejected');
?>

<!-- =============================================================
     LEAVE MANAGEMENT KPI CARDS
     ============================================================= -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="hrm-kpi-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.78rem;">Pending Review</span>
                <i class="bi bi-hourglass-split text-warning fs-5"></i>
            </div>
            <h3 class="fw-bold text-warning mb-1 hrm-metric-value"><?= count($pendingList) ?> Requests</h3>
            <div class="pt-2 mt-2 border-top">
                <small class="text-muted">Awaiting administrator / manager decision</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="hrm-kpi-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.78rem;">Approved Leaves</span>
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
            </div>
            <h3 class="fw-bold text-success mb-1 hrm-metric-value"><?= count($approvedList) ?> Approved</h3>
            <div class="pt-2 mt-2 border-top">
                <small class="text-muted">Auto-synchronized with attendance calendar</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="hrm-kpi-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.78rem;">Rejected</span>
                <i class="bi bi-x-circle text-danger fs-5"></i>
            </div>
            <h3 class="fw-bold text-danger mb-1 hrm-metric-value"><?= count($rejectedList) ?> Rejected</h3>
            <div class="pt-2 mt-2 border-top">
                <small class="text-muted">Declined requests with HR remarks</small>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     LEAVE REQUESTS LISTING TABLE
     ============================================================= -->
<div class="hrm-panel">
    <div class="hrm-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-inbox-fill" style="color: var(--hrm-accent, #9DE500);"></i>
                Employee Leave Applications Queue
            </h6>
            <small class="text-muted">Review, approve, or reject absence requests submitted by staff members.</small>
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <a href="<?= url('leaves') ?>" class="btn <?= empty($currentFilter) ? 'btn-dark' : 'btn-outline-dark' ?>">All (<?= count($leaveRequests) ?>)</a>
            <a href="<?= url('leaves?status=pending') ?>" class="btn <?= ($currentFilter === 'pending') ? 'btn-warning' : 'btn-outline-warning' ?>">Pending (<?= count($pendingList) ?>)</a>
            <a href="<?= url('leaves?status=approved') ?>" class="btn <?= ($currentFilter === 'approved') ? 'btn-success' : 'btn-outline-success' ?>">Approved (<?= count($approvedList) ?>)</a>
            <a href="<?= url('leaves?status=rejected') ?>" class="btn <?= ($currentFilter === 'rejected') ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected (<?= count($rejectedList) ?>)</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table hrm-table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Department</th>
                    <th>Leave Type</th>
                    <th>Duration &amp; Period</th>
                    <th>Reason / Notes</th>
                    <th>Status</th>
                    <th class="text-end">Decision Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaveRequests)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No leave requests found matching this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leaveRequests as $req): 
                        $initials = strtoupper(substr($req['employee_name'], 0, 2));
                        $isPending = ($req['status'] === 'pending');
                    ?>
                        <tr class="<?= $isPending ? 'table-warning bg-opacity-25' : '' ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="hrm-avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        <?= e($initials) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark small"><?= e($req['employee_name']) ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($req['employee_code']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border small"><?= e($req['department_name'] ?? 'General') ?></span></td>
                            <td>
                                <?php if ($req['leave_type'] === 'annual'): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Annual Vacation</span>
                                <?php elseif ($req['leave_type'] === 'sick'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Medical / Sick</span>
                                <?php elseif ($req['leave_type'] === 'casual'): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning">Casual Leave</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border">Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">
                                    <?= date('M d, Y', strtotime($req['start_date'])) ?> &rarr; <?= date('M d, Y', strtotime($req['end_date'])) ?>
                                </div>
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.7rem;">
                                    <?= (int)$req['days'] ?> Business Day(s)
                                </span>
                            </td>
                            <td class="small" style="max-width: 250px;">
                                <div class="text-dark"><?= e($req['reason']) ?></div>
                                <?php if (!empty($req['admin_remark'])): ?>
                                    <small class="text-muted d-block mt-1 fst-italic">
                                        <i class="bi bi-chat-left-text me-1"></i><strong>Admin:</strong> <?= e($req['admin_remark']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($req['status'] === 'approved'): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>APPROVED</span>
                                <?php elseif ($req['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i>REJECTED</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-25 text-dark fw-bold"><i class="bi bi-hourglass-split me-1"></i>PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($isPending): ?>
                                    <div class="d-inline-flex gap-1">
                                        <!-- Approve Form -->
                                        <form method="POST" action="<?= url('leaves/action') ?>" onsubmit="return confirm('Are you sure you want to APPROVE this leave request? It will automatically be marked in the employee\'s attendance calendar.');">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="leave_id" value="<?= (int)$req['id'] ?>">
                                            <input type="hidden" name="action" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve Request">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>

                                        <!-- Reject Form -->
                                        <form method="POST" action="<?= url('leaves/action') ?>" onsubmit="return confirm('Are you sure you want to REJECT this leave request?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="leave_id" value="<?= (int)$req['id'] ?>">
                                            <input type="hidden" name="action" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject Request">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Decided <?= !empty($req['actioned_at']) ? date('M d', strtotime($req['actioned_at'])) : '' ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

