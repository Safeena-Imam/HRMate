<?php
$monthFormatted = date('F Y', strtotime($payslip['month'] . '-01'));
$isPaid = ($payslip['payment_status'] === 'paid');
?>
<div class="container" style="max-width: 850px;">
    <!-- Top Action Bar (hidden on print) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-dark btn-sm fw-semibold">
                <i class="bi bi-printer me-1"></i> Print Payslip / Save PDF
            </button>
        </div>
    </div>

    <!-- Official Payslip Document Card -->
    <div class="card border shadow-sm p-4 bg-white" style="border-radius: 12px;">
        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.03em;">HRMate</h3>
                <span class="text-muted small">Human Capital Management &bull; Corporate Payroll Office</span>
                <p class="text-muted small mb-0">Tower 4, Financial District &bull; compliance@hrmate.internal</p>
            </div>
            <div class="text-end">
                <span class="badge <?= $isPaid ? 'bg-success' : 'bg-warning text-dark' ?> fs-6 px-3 py-2 mb-1">
                    <?= $isPaid ? 'OFFICIALLY PAID' : 'PROCESSED / PENDING' ?>
                </span>
                <div class="text-muted small">Payslip Reference: #PAY-<?= str_pad($payslip['id'], 5, '0', STR_PAD_LEFT) ?></div>
                <div class="text-muted small">Pay Period: <strong><?= e($monthFormatted) ?></strong></div>
            </div>
        </div>

        <!-- Employee & Disbursement Info Grid -->
        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Employee Name</span>
                <strong class="text-dark"><?= e($payslip['employee_name']) ?></strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Employee Code</span>
                <strong class="font-monospace text-dark"><?= e($payslip['employee_code']) ?></strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Department</span>
                <span class="text-dark"><?= e($payslip['department_name'] ?? 'General') ?></span>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Designation</span>
                <span class="text-dark"><?= e($payslip['designation_title'] ?? 'Staff') ?></span>
            </div>

            <div class="col-6 col-md-3 mt-3">
                <span class="text-muted small d-block">Disbursement Bank</span>
                <span class="text-dark"><?= e($payslip['bank_name'] ?? 'Bank Account') ?></span>
            </div>
            <div class="col-6 col-md-3 mt-3">
                <span class="text-muted small d-block">Account Number</span>
                <span class="font-monospace text-dark"><?= e($payslip['account_number'] ?? 'N/A') ?></span>
            </div>
            <div class="col-6 col-md-3 mt-3">
                <span class="text-muted small d-block">Date of Joining</span>
                <span class="text-dark"><?= !empty($payslip['hire_date']) ? date('M d, Y', strtotime($payslip['hire_date'])) : 'N/A' ?></span>
            </div>
            <div class="col-6 col-md-3 mt-3">
                <span class="text-muted small d-block">Payment Date</span>
                <span class="text-dark"><?= !empty($payslip['paid_at']) ? date('M d, Y', strtotime($payslip['paid_at'])) : date('M t, Y') ?></span>
            </div>
        </div>

        <!-- Attendance Summary Row -->
        <div class="mb-4">
            <h6 class="fw-bold text-uppercase text-muted small mb-2" style="letter-spacing: 0.05em;">Attendance Summary for <?= e($monthFormatted) ?></h6>
            <div class="row g-2 text-center">
                <div class="col-3">
                    <div class="border rounded p-2 bg-light">
                        <small class="text-muted d-block">Working Days</small>
                        <strong class="text-dark"><?= (int)$payslip['working_days'] ?> Days</strong>
                    </div>
                </div>
                <div class="col-3">
                    <div class="border rounded p-2 bg-light">
                        <small class="text-muted d-block">Present / WFH</small>
                        <strong class="text-success"><?= (int)$payslip['present_days'] ?> Days</strong>
                    </div>
                </div>
                <div class="col-3">
                    <div class="border rounded p-2 bg-light">
                        <small class="text-muted d-block">Approved Leaves</small>
                        <strong class="text-primary"><?= (int)$payslip['paid_leave_days'] ?> Days</strong>
                    </div>
                </div>
                <div class="col-3">
                    <div class="border rounded p-2 bg-light">
                        <small class="text-muted d-block">Unpaid Absences</small>
                        <strong class="text-danger"><?= (int)$payslip['absent_days'] + ($payslip['half_days'] * 0.5) ?> Days</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings & Deductions Double Column Table -->
        <div class="row g-3 mb-4">
            <!-- Earnings Column -->
            <div class="col-12 col-md-6">
                <div class="border rounded h-100">
                    <div class="bg-light px-3 py-2 border-bottom fw-bold text-success small text-uppercase">
                        Earnings &amp; Allowances
                    </div>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="px-3 py-2 text-muted">Base Salary</td>
                                <td class="px-3 py-2 text-end font-monospace">PKR <?= number_format($payslip['base_salary'], 2) ?></td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 text-muted">Utility &amp; Medical Allowance</td>
                                <td class="px-3 py-2 text-end font-monospace text-success">+PKR <?= number_format($payslip['allowances'], 2) ?></td>
                            </tr>
                            <?php if ($payslip['bonus'] > 0): ?>
                            <tr>
                                <td class="px-3 py-2 text-muted">Performance Bonus</td>
                                <td class="px-3 py-2 text-end font-monospace text-success">+PKR <?= number_format($payslip['bonus'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td class="px-3 py-2 fw-bold text-dark">Total Gross Remuneration</td>
                                <td class="px-3 py-2 text-end font-monospace fw-bold text-dark">
                                    PKR <?= number_format($payslip['base_salary'] + $payslip['allowances'] + $payslip['bonus'], 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Deductions Column -->
            <div class="col-12 col-md-6">
                <div class="border rounded h-100">
                    <div class="bg-light px-3 py-2 border-bottom fw-bold text-danger small text-uppercase">
                        Deductions &amp; Withholdings
                    </div>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="px-3 py-2 text-muted">Unattended Days Deduction</td>
                                <td class="px-3 py-2 text-end font-monospace text-danger">
                                    <?= ($payslip['unpaid_deductions'] > 0) ? ('-PKR ' . number_format($payslip['unpaid_deductions'], 2)) : 'PKR 0.00' ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 text-muted">Withholding Income Tax (5%)</td>
                                <td class="px-3 py-2 text-end font-monospace text-secondary">
                                    <?= ($payslip['tax_deduction'] > 0) ? ('-PKR ' . number_format($payslip['tax_deduction'], 2)) : 'PKR 0.00' ?>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td class="px-3 py-2 fw-bold text-danger">Total Deductions</td>
                                <td class="px-3 py-2 text-end font-monospace fw-bold text-danger">
                                    -PKR <?= number_format($payslip['unpaid_deductions'] + $payslip['tax_deduction'], 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Net Payable Amount Highlight Banner -->
        <div class="p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center" style="background-color: #101828; color: #ffffff;">
            <div>
                <span class="text-uppercase small fw-semibold" style="letter-spacing: 0.05em; color: #9DE500;">Net Take-Home Pay</span>
                <div class="small text-muted" style="color: #94a3b8 !important;">Credited via Electronic Bank Transfer</div>
            </div>
            <div class="text-end">
                <h3 class="fw-bold mb-0 font-monospace" style="color: #ffffff;">PKR <?= number_format($payslip['net_salary'], 2) ?></h3>
            </div>
        </div>

        <!-- Signatures & Disclaimer Footer -->
        <div class="row pt-4 mt-2 border-top text-center">
            <div class="col-4">
                <div style="border-bottom: 1px dashed #aaa; height: 35px; width: 80%; margin: 0 auto;"></div>
                <small class="text-muted d-block mt-1">Prepared by HR Administration</small>
            </div>
            <div class="col-4">
                <div style="border-bottom: 1px dashed #aaa; height: 35px; width: 80%; margin: 0 auto;"></div>
                <small class="text-muted d-block mt-1">Authorized by Finance Director</small>
            </div>
            <div class="col-4">
                <div style="border-bottom: 1px dashed #aaa; height: 35px; width: 80%; margin: 0 auto;"></div>
                <small class="text-muted d-block mt-1">Employee Acknowledgement</small>
            </div>
        </div>

        <div class="text-center text-muted small mt-4 pt-2 border-top fst-italic" style="font-size: 0.72rem;">
            This document is a computer-generated official pay advice from HRMate HRMS. No manual seal required.
        </div>
    </div>
</div>

