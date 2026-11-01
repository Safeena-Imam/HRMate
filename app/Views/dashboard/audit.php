<!-- =============================================================
     AUDIT LOG TABLE & LIVE FILTER
     ============================================================= -->
<div class="hrm-panel">
    <div class="hrm-panel-header flex-wrap gap-3 py-3">
        <div>
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-shield-check" style="color: var(--hrm-accent);"></i>
                Immutable Event Log
            </h6>
        </div>
        <div class="hrm-search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="auditLogSearchInput" class="form-control" placeholder="Search audit trail by user, action, IP...">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table hrm-table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Identity</th>
                    <th>Event Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentAuditLogs)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No audit logs recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentAuditLogs as $log): ?>
                        <tr class="js-audit-row">
                            <td class="font-monospace small text-muted">
                                <?= e(date('M d, Y H:i:s', strtotime($log['created_at']))) ?>
                            </td>
                            <td>
                                <span class="badge bg-dark font-monospace text-uppercase" style="font-size: 0.72rem;">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">
                                    <?= e($log['full_name'] ?? $log['username'] ?? 'System / Anonymous') ?>
                                </div>
                                <?php if (!empty($log['role_name'])): ?>
                                    <span class="text-muted" style="font-size: 0.72rem;"><?= e($log['role_name']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-secondary">
                                <?= e($log['description'] ?? 'Administrative event') ?>
                            </td>
                            <td class="font-monospace small text-muted">
                                <?= e($log['ip_address'] ?? '127.0.0.1') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
