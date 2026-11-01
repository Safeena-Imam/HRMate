<?php
$csrf = csrf_token();
$selectedMonthStr = $selectedMonth ?? date('Y-m');
$monthName = date('F Y', strtotime("{$selectedMonthStr}-01"));

$totalNet    = (float)($summary['total_net_disbursement'] ?? 0);
$totalBase   = (float)($summary['total_base'] ?? 0);
$totalDeduct = (float)($summary['total_deductions'] ?? 0);
$totalTax    = (float)($summary['total_tax'] ?? 0);
$paidCount   = (int)($summary['paid_count'] ?? 0);
$procCount   = (int)($summary['processed_count'] ?? 0);
$totalStaff  = count($payrolls ?? []);
?>

<!-- =============================================================
     PAYROLL FILTER & BATCH ACTION BAR
     ============================================================= -->
<div class="hrm-panel mb-4">
    <div class="hrm-panel-body py-3">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-12 col-md-5">
                <form method="GET" action="<?= url('payroll') ?>" class="row g-2 align-items-center">
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-month text-muted"></i></span>
                            <input type="month" name="month" class="form-control border-start-0 ps-0" value="<?= e($selectedMonthStr) ?>" onchange="this.form.submit()">
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-dark w-100 fw-semibold">
                            <i class="bi bi-filter me-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-12 col-md-7 text-md-end d-flex flex-wrap gap-2 justify-content-md-end">
                <!-- 1-Click Generate / Recalculate Payroll Form -->
                <form method="POST" action="<?= url('payroll/generate') ?>" onsubmit="return confirm('Recalculate monthly payroll for <?= e($monthName) ?> based on current employee attendance logs?');">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <input type="hidden" name="month" value="<?= e($selectedMonthStr) ?>">
                    <button type="submit" class="btn fw-semibold" style="background-color: #101828; color: #fff;">
                        <i class="bi bi-calculator me-1" style="color: #9DE500;"></i> Calculate from Attendance
                    </button>
                </form>

                <!-- Export CSV Button (Option 4) -->
                <a href="<?= url('payroll/export') ?>?month=<?= e($selectedMonthStr) ?>" class="btn btn-outline-success fw-semibold">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Ledger (CSV)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     PAYROLL KPI SUMMARY METRICS
     ============================================================= -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-kpi-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.75rem;">Total Net Payroll</span>
                <i class="bi bi-cash-stack text-success fs-5"></i>
            </div>
            <h3 class="fw-bold text-success mb-1 hrm-metric-value">PKR <?= number_format($totalNet) ?></h3>
            <div class="pt-2 mt-2 border-top">
                <small class="text-muted"><?= $totalStaff ?> active staff processed</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-kpi-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.75rem;">Base Salary Volume</span>
                <i class="bi bi-building text-primary fs-5"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1 hrm-metric-value">PKR <?= number_format($totalBase) ?></h3>
            <div class="pt-2 mt-2 border-top">
                <small class="text-muted">Gross base remuneration</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-kpi-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.75rem;">Absent Deductions</span>
                <i class="bi bi-arrow-down-circle text-danger fs-5"></i>
            </div>
            <h3 class="fw-bold text-danger mb-1 hrm-metric-value">- PKR <?= number_format($totalDeduct) ?></h3>
            <div class="pt-2 mt-2 border-top">
                <small class="text-muted">Unattended days deducted</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="hrm-kpi-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.75rem;">Disbursement Status</span>
                <i class="bi bi-check2-circle text-info fs-5"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1 hrm-metric-value"><?= $paidCount ?> / <?= $totalStaff ?> Paid</h3>
            <div class="pt-2 mt-2 border-top">
                <small class="text-muted"><?= $procCount ?> awaiting bank transfer</small>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     MONTHLY PAYROLL LEDGER TABLE
     ============================================================= -->
<div class="hrm-panel">
    <div class="hrm-panel-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-receipt" style="color: var(--hrm-accent, #9DE500);"></i>
                Monthly Compensation &amp; Payroll Matrix &bull; <?= e($monthName) ?>
            </h6>
            <small class="text-muted">Integrated automatically with staff attendance calculations</small>
        </div>
        <span class="badge bg-light text-dark border"><?= count($payrolls) ?> Records</span>
    </div>

    <div class="table-responsive">
        <table class="table hrm-table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Staff Member</th>
                    <th>Bank &amp; Account</th>
                    <th class="text-center">Attendance Breakdown</th>
                    <th class="text-end">Base Salary</th>
                    <th class="text-end text-success">+ Allowance</th>
                    <th class="text-end text-danger">- Deduction</th>
                    <th class="text-end text-secondary">- Tax</th>
                    <th class="text-end fw-bold">Net Salary</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payrolls)): ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">
                            <i class="bi bi-calculator fs-2 text-muted d-block mb-2"></i>
                            No payroll records generated for <strong><?= e($monthName) ?></strong> yet.<br>
                            Click <strong>"Calculate from Attendance"</strong> above to compute monthly salaries with 1 click!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payrolls as $p): 
                        $initials = strtoupper(substr($p['employee_name'], 0, 2));
                        $isPaid = ($p['payment_status'] === 'paid');
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="hrm-avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        <?= e($initials) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark small"><?= e($p['employee_name']) ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($p['employee_code']) ?> &bull; <?= e($p['designation_title'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="small">
                                <div class="text-dark fw-semibold"><?= e($p['bank_name'] ?? 'Bank Transfer') ?></div>
                                <span class="font-monospace text-muted" style="font-size: 0.72rem;"><?= e($p['account_number'] ?? 'N/A') ?></span>
                            </td>
                            <td class="text-center small">
                                <span class="badge bg-success bg-opacity-10 text-success me-1" title="Present Days"><?= (int)$p['present_days'] ?> P</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary me-1" title="Approved Leaves"><?= (int)$p['paid_leave_days'] ?> L</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger me-1" title="Absent Days"><?= (int)$p['absent_days'] ?> A</span>
                                <span class="badge bg-warning bg-opacity-10 text-dark" title="Half Days"><?= (int)$p['half_days'] ?> HD</span>
                            </td>
                            <td class="text-end font-monospace small">PKR <?= number_format($p['base_salary']) ?></td>
                            <td class="text-end font-monospace small text-success">+<?= number_format($p['allowances']) ?></td>
                            <td class="text-end font-monospace small text-danger">
                                <?= ($p['unpaid_deductions'] > 0) ? ('-' . number_format($p['unpaid_deductions'])) : '0' ?>
                            </td>
                            <td class="text-end font-monospace small text-secondary">
                                <?= ($p['tax_deduction'] > 0) ? ('-' . number_format($p['tax_deduction'])) : '0' ?>
                            </td>
                            <td class="text-end font-monospace fw-bold text-dark">
                                PKR <?= number_format($p['net_salary']) ?>
                            </td>
                            <td class="text-center">
                                <?php if ($isPaid): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>PAID</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-25 text-dark fw-bold"><i class="bi bi-clock-history me-1"></i>PROCESSED</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="<?= url('payroll/payslip') ?>?id=<?= (int)$p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="View & Print Official Payslip">
                                        <i class="bi bi-printer me-1"></i> Payslip
                                    </a>

                                    <?php if (!$isPaid): ?>
                                        <form method="POST" action="<?= url('payroll/update-status') ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                            <input type="hidden" name="payroll_id" value="<?= (int)$p['id'] ?>">
                                            <input type="hidden" name="status" value="paid">
                                            <input type="hidden" name="month" value="<?= e($selectedMonthStr) ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Mark as Disbursed / Paid">
                                                <i class="bi bi-check2"></i> Mark Paid
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
