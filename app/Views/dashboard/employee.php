<?php
$user = current_user();
$fullName = ($profile['first_name'] ?? 'Sophia') . ' ' . ($profile['last_name'] ?? 'Chen');
$initials = strtoupper(substr($profile['first_name'] ?? 'S', 0, 1) . substr($profile['last_name'] ?? 'C', 0, 1));
$activeTab = $activeTab ?? 'my-workspace';
?>


<!-- =============================================================
     TAB 1: PERSONAL WORKSPACE & PUNCH OVERVIEW
     ============================================================= -->
<div class="hrm-tab-pane <?= $activeTab === 'my-workspace' ? 'active' : '' ?>" id="tab-my-workspace">
    <?php if (!empty($announcements)): ?>
        <!-- Company Announcement Broadcast Banner -->
        <div class="mb-4">
            <?php foreach ($announcements as $ann): 
                $priorityClass = ($ann['priority'] === 'urgent') ? 'border-danger bg-danger bg-opacity-10' : (($ann['priority'] === 'warning') ? 'border-warning bg-warning bg-opacity-10' : 'border-info bg-info bg-opacity-10');
                $priorityBadge = ($ann['priority'] === 'urgent') ? 'bg-danger text-white' : (($ann['priority'] === 'warning') ? 'bg-warning text-dark' : 'bg-info text-dark');
                $priorityIcon  = ($ann['priority'] === 'urgent') ? 'bi-exclamation-octagon-fill text-danger' : (($ann['priority'] === 'warning') ? 'bi-exclamation-triangle-fill text-warning' : 'bi-info-circle-fill text-info');
            ?>
                <div class="p-3 rounded-3 border mb-2 d-flex align-items-start gap-3 <?= $priorityClass ?> shadow-sm">
                    <i class="bi <?= $priorityIcon ?> fs-4 mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="badge <?= $priorityBadge ?> text-uppercase small" style="font-size: 0.68rem;"><?= strtoupper($ann['priority']) ?></span>
                            <h6 class="fw-bold mb-0 text-dark"><?= e($ann['title']) ?></h6>
                            <small class="text-muted font-monospace" style="font-size: 0.72rem;">&bull; Broadcast by Management (<?= date('M d, Y', strtotime($ann['created_at'])) ?>)</small>
                        </div>
                        <p class="small text-secondary mb-0"><?= e($ann['message']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <!-- 4 Personal Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="hrm-metric-card" style="--card-accent: var(--hrm-emerald);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="hrm-metric-value text-success" style="font-size: 1.4rem;">09:14 AM</div>
                        <div class="hrm-metric-label">Today's Clock-In</div>
                    </div>
                    <div class="hrm-metric-icon emerald">
                        <i class="bi bi-alarm-fill"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                    <span>Shift Progress</span>
                    <span class="fw-semibold text-success">6h 45m elapsed</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="hrm-metric-card" style="--card-accent: #d97706;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="hrm-metric-value text-warning">14 Days</div>
                        <div class="hrm-metric-label">Annual Leave Balance</div>
                    </div>
                    <div class="hrm-metric-icon amber">
                        <i class="bi bi-calendar2-heart-fill"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                    <span>Quota: 20 Days/yr</span>
                    <span class="fw-semibold text-dark">6 days used</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="hrm-metric-card" style="--card-accent: var(--hrm-accent);">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="hrm-metric-value text-teal" style="color: var(--hrm-accent);">96.5%</div>
                        <div class="hrm-metric-label">Monthly Punctuality</div>
                    </div>
                    <div class="hrm-metric-icon teal">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                    <span>20 of 21 working days</span>
                    <span class="fw-semibold text-success">On-Time</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="hrm-metric-card" style="--card-accent: #64748b;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="hrm-metric-value" style="font-size: 1.25rem;">Marcus S.</div>
                        <div class="hrm-metric-label">Direct Supervisor</div>
                    </div>
                    <div class="hrm-metric-icon slate">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
                    <span>Department</span>
                    <span class="fw-semibold text-dark">Chief Technology Officer</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Overview Split -->
    <div class="row g-4 mb-4">
        <!-- Left: Quick Clock-In Widget & Timesheet -->
        <div class="col-12 col-xl-7">
            <div class="hrm-panel mb-4">
                <div class="hrm-panel-header">
                    <h6 class="hrm-panel-title">
                        <i class="bi bi-stopwatch-fill" style="color: var(--hrm-accent);"></i>
                        Daily Web Attendance Punch
                    </h6>
                    <span class="badge bg-success-subtle text-success">In Progress</span>
                </div>
                <div class="hrm-panel-body">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Standard Work Shift</span>
                            <span class="badge bg-light text-dark border font-monospace">09:00 AM – 05:30 PM</span>
                        </div>
                        <div class="row text-center g-2 mt-2">
                            <div class="col-4">
                                <small class="text-muted d-block">Punch-In</small>
                                <span class="fw-bold text-success font-monospace">09:14 AM</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Break Taken</small>
                                <span class="fw-bold text-secondary font-monospace">45 Mins</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Expected Out</small>
                                <span class="fw-bold text-primary font-monospace">05:30 PM</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="alert('Break recorded. Enjoy your lunch break!');">
                            <i class="bi bi-cup-hot me-1"></i> Start 30-min Lunch Break
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill" onclick="document.querySelector('[data-target=\'tab-my-attendance\']').click();">
                            <i class="bi bi-calendar3 me-1"></i> View Full Timesheet
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Personal Shift Activity -->
            <div class="hrm-panel">
                <div class="hrm-panel-header">
                    <h6 class="hrm-panel-title">
                        <i class="bi bi-clock-history" style="color: var(--hrm-emerald);"></i>
                        My Recent Daily Punches
                    </h6>
                    <span class="badge bg-light text-dark border">This Week</span>
                </div>
                <div class="table-responsive">
                    <table class="table hrm-table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold text-dark">Today, <?= date('M d') ?></td>
                                <td class="font-monospace text-success">09:14 AM</td>
                                <td class="font-monospace text-muted">Currently Active</td>
                                <td class="font-monospace fw-semibold text-dark">6h 45m</td>
                                <td><span class="badge-status active">ON TIME</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-dark">Yesterday</td>
                                <td class="font-monospace text-success">08:58 AM</td>
                                <td class="font-monospace text-muted">05:32 PM</td>
                                <td class="font-monospace fw-semibold text-dark">8h 34m</td>
                                <td><span class="badge-status active">ON TIME</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-dark">2 Days Ago</td>
                                <td class="font-monospace text-success">09:05 AM</td>
                                <td class="font-monospace text-muted">05:35 PM</td>
                                <td class="font-monospace fw-semibold text-dark">8h 30m</td>
                                <td><span class="badge-status active">ON TIME</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold text-dark">3 Days Ago</td>
                                <td class="font-monospace text-success">09:12 AM</td>
                                <td class="font-monospace text-muted">05:40 PM</td>
                                <td class="font-monospace fw-semibold text-dark">8h 28m</td>
                                <td><span class="badge-status active">ON TIME</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Leave Quotas & Upcoming Holidays -->
        <div class="col-12 col-xl-5">
            <!-- Leave Quota Breakdown -->
            <div class="hrm-panel mb-4">
                <div class="hrm-panel-header">
                    <h6 class="hrm-panel-title">
                        <i class="bi bi-calendar-check" style="color: var(--hrm-accent);"></i>
                        My Leave Balance
                    </h6>
                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                        Apply &rarr;
                    </button>
                </div>
                <div class="hrm-panel-body">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small text-dark">Annual Paid Leave</span>
                                <span class="small text-muted font-monospace">14 days left (of 20)</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--hrm-border-subtle); border-radius: 9999px;">
                                <div class="progress-bar bg-success" style="width: 70%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small text-dark">Medical / Sick Leave</span>
                                <span class="small text-muted font-monospace">7 days left (of 10)</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--hrm-border-subtle); border-radius: 9999px;">
                                <div class="progress-bar bg-info" style="width: 70%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold small text-dark">Casual Leave</span>
                                <span class="small text-muted font-monospace">3 days left (of 5)</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: var(--hrm-border-subtle); border-radius: 9999px;">
                                <div class="progress-bar bg-warning" style="width: 60%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Announcements & Holidays -->
            <div class="hrm-panel">
                <div class="hrm-panel-header">
                    <h6 class="hrm-panel-title">
                        <i class="bi bi-megaphone-fill" style="color: var(--hrm-emerald);"></i>
                        Upcoming Official Holidays
                    </h6>
                    <span class="badge bg-light text-dark border">Calendar</span>
                </div>
                <div class="hrm-panel-body">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <div>
                                <span class="fw-semibold text-dark d-block">Annual Corporate Retreat</span>
                                <small class="text-muted">Company-wide team building</small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary font-monospace">Nov 14</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <div>
                                <span class="fw-semibold text-dark d-block">National Holiday</span>
                                <small class="text-muted">Public observance</small>
                            </div>
                            <span class="badge bg-light text-secondary border font-monospace">Dec 25</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <div>
                                <span class="fw-semibold text-dark d-block">New Year's Day</span>
                                <small class="text-muted">Holiday observed</small>
                            </div>
                            <span class="badge bg-light text-secondary border font-monospace">Jan 01</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     TAB 2: MY JOB & PROFILE
     ============================================================= -->
<div class="hrm-tab-pane <?= $activeTab === 'my-profile' ? 'active' : '' ?>" id="tab-my-profile">
    <div class="row g-4">
        <!-- Left: Official Profile Card -->
        <div class="col-12 col-md-4">
            <div class="hrm-panel text-center p-4">
                <div class="hrm-avatar-circle mx-auto mb-3" style="width: 72px; height: 72px; font-size: 1.6rem;">
                    <?= e($initials) ?>
                </div>
                <h4 class="fw-bold text-dark mb-1"><?= e($fullName) ?></h4>
                <p class="text-secondary small mb-2"><?= e($profile['designation_title'] ?? 'Senior Software Engineer') ?></p>
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-light text-secondary font-monospace border"><?= e($profile['employee_code'] ?? 'EMP-1003') ?></span>
                    <span class="badge-status <?= e($profile['employment_status'] ?? 'active') ?>">
                        <?= strtoupper(e($profile['employment_status'] ?? 'active')) ?>
                    </span>
                </div>
                <hr class="my-3">
                <div class="text-start small">
                    <div class="mb-2">
                        <span class="text-muted d-block">Official Email</span>
                        <span class="fw-semibold text-dark"><?= e($profile['email'] ?? 'sophia.chen@company.org') ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted d-block">Phone Number</span>
                        <span class="fw-semibold text-dark"><?= e($profile['phone'] ?? '+1 (555) 019-8392') ?></span>
                    </div>
                    <div>
                        <span class="text-muted d-block">Hire Date</span>
                        <span class="fw-semibold text-dark"><?= e(date('F j, Y', strtotime($profile['hire_date'] ?? '2022-01-10'))) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Department & Employment Details -->
        <div class="col-12 col-md-8">
            <div class="hrm-panel">
                <div class="hrm-panel-header">
                    <h6 class="hrm-panel-title">
                        <i class="bi bi-briefcase-fill" style="color: var(--hrm-accent);"></i>
                        Employment &amp; Position Details
                    </h6>
                    <span class="badge bg-light text-dark border">Verified Record</span>
                </div>
                <div class="hrm-panel-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Assigned Department</span>
                                <h6 class="fw-bold text-dark mb-0"><?= e($profile['department_name'] ?? 'Engineering & IT') ?></h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Official Position Title</span>
                                <h6 class="fw-bold text-dark mb-0"><?= e($profile['designation_title'] ?? 'Senior Software Engineer') ?></h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Reporting Line (Supervisor)</span>
                                <h6 class="fw-bold text-dark mb-0"><?= e($profile['manager_name'] ?? 'Marcus Sterling') ?> (CTO)</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Employment Type</span>
                                <h6 class="fw-bold text-dark mb-0">Full-Time Permanent</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small d-block mb-1">Work Location &amp; Mode</span>
                                <h6 class="fw-bold text-dark mb-1">Main Technology Headquarters &bull; Hybrid Mode</h6>
                                <p class="text-secondary small mb-0">Authorized for Monday/Friday remote work and Tuesday through Thursday on-site engineering collaboration.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     TAB 3: MY ATTENDANCE LOG
     ============================================================= -->
<div class="hrm-tab-pane <?= $activeTab === 'my-attendance' ? 'active' : '' ?>" id="tab-my-attendance">
    <?php
    $empMonth = $selectedMonth ?? date('Y-m');
    [$empYear, $empM] = explode('-', $empMonth);
    $empDaysInMonth = (int)cal_days_in_month(CAL_GREGORIAN, (int)$empM, (int)$empYear);
    $empMonthName = date('F Y', strtotime("{$empMonth}-01"));
    $empPresent = $monthSummary['present'] ?? 0;
    $empAbsent  = $monthSummary['absent'] ?? 0;
    $empHalf    = $monthSummary['half_day'] ?? 0;
    $empLeave   = $monthSummary['leave'] ?? 0;
    $empWfh     = $monthSummary['work_from_home'] ?? 0;
    $empHoliday = $monthSummary['holiday'] ?? 0;
    ?>

    <!-- Monthly Filter Header -->
    <div class="hrm-panel mb-4">
        <div class="hrm-panel-body py-3">
            <form method="GET" action="<?= url('dashboard') ?>" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="my-attendance">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Select Attendance Period</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-month text-muted"></i></span>
                        <input type="month" name="month" class="form-control border-start-0 ps-0" value="<?= e($empMonth) ?>" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-12 col-md-3 mt-md-4">
                    <button type="submit" class="btn btn-dark btn-sm fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filter Month
                    </button>
                </div>
                <div class="col-12 col-md-5 text-md-end mt-md-4 d-flex justify-content-md-end gap-2 align-items-center">
                    <a href="<?= url('attendance/export') ?>?month=<?= e($empMonth) ?>" class="btn btn-outline-success btn-sm fw-semibold">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export (CSV)
                    </a>
                    <span class="badge bg-light text-dark border p-2">
                        <i class="bi bi-lock-fill text-muted me-1"></i> HR Maintained
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Summary Metric Badges -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Present</span>
                <h4 class="fw-bold text-success my-1"><?= $empPresent ?></h4>
                <small class="text-muted">Days Marked</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Absent</span>
                <h4 class="fw-bold text-danger my-1"><?= $empAbsent ?></h4>
                <small class="text-muted">Unattended</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Half Day</span>
                <h4 class="fw-bold text-warning my-1"><?= $empHalf ?></h4>
                <small class="text-muted">Half Shift</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Leave</span>
                <h4 class="fw-bold text-primary my-1"><?= $empLeave ?></h4>
                <small class="text-muted">Approved</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">WFH</span>
                <h4 class="fw-bold text-info my-1"><?= $empWfh ?></h4>
                <small class="text-muted">Remote</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Holidays</span>
                <h4 class="fw-bold text-secondary my-1"><?= $empHoliday ?></h4>
                <small class="text-muted">Off Duty</small>
            </div>
        </div>
    </div>

    <!-- Monthly Attendance Roster (Read-Only) -->
    <div class="hrm-panel">
        <div class="hrm-panel-header d-flex justify-content-between align-items-center">
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-clock-history" style="color: var(--hrm-accent, #9DE500);"></i>
                My Monthly Attendance Log &bull; <?= e($empMonthName) ?>
            </h6>
            <span class="badge bg-light text-dark border"><?= $empDaysInMonth ?> Days Total</span>
        </div>
        <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
            <table class="table hrm-table table-hover align-middle mb-0">
                <thead class="sticky-top bg-light" style="z-index: 1;">
                    <tr>
                        <th style="width: 140px;">Date</th>
                        <th style="width: 120px;">Day</th>
                        <th style="width: 180px;">Official Status</th>
                        <th>Admin Remark / Reason</th>
                        <th style="width: 130px;" class="text-end">Verification</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($d = 1; $d <= $empDaysInMonth; $d++): 
                        $dayDateStr = sprintf('%04d-%02d-%02d', $empYear, $empM, $d);
                        $timestamp  = strtotime($dayDateStr);
                        $dayName    = date('l', $timestamp);
                        $shortDate  = date('M d, Y', $timestamp);
                        $isWeekend  = ($dayName === 'Saturday' || $dayName === 'Sunday');
                        $record     = $attendanceRecords[$dayDateStr] ?? null;
                        $curStatus  = $record['status'] ?? ($isWeekend ? 'holiday' : null);
                        $curNotes   = $record['notes'] ?? '';
                    ?>
                        <tr class="<?= $isWeekend ? 'bg-light bg-opacity-50' : '' ?>">
                            <td class="fw-semibold text-dark"><?= $shortDate ?></td>
                            <td>
                                <span class="badge <?= $isWeekend ? 'bg-secondary bg-opacity-25 text-secondary' : 'bg-light text-dark border' ?> small">
                                    <?= $dayName ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($curStatus === 'present'): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Present</span>
                                <?php elseif ($curStatus === 'absent'): ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i>Absent</span>
                                <?php elseif ($curStatus === 'half_day'): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-dark fw-bold"><i class="bi bi-clock-fill me-1"></i>Half Day</span>
                                <?php elseif ($curStatus === 'leave'): ?>
                                    <span class="badge bg-primary bg-opacity-25 text-primary fw-bold"><i class="bi bi-calendar-check-fill me-1"></i>Approved Leave</span>
                                <?php elseif ($curStatus === 'work_from_home'): ?>
                                    <span class="badge bg-info bg-opacity-25 text-info fw-bold"><i class="bi bi-laptop me-1"></i>Work From Home</span>
                                <?php elseif ($curStatus === 'holiday'): ?>
                                    <span class="badge bg-secondary bg-opacity-25 text-secondary"><i class="bi bi-calendar-event me-1"></i>Holiday / Off</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border fst-italic">Pending / Not Marked</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-secondary">
                                <?= !empty($curNotes) ? e($curNotes) : ($isWeekend ? '<span class="text-muted">Weekend Off</span>' : '<span class="text-muted fst-italic">—</span>') ?>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-light text-secondary border small">
                                    <i class="bi bi-shield-check text-success me-1"></i>HR Verified
                                </span>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =============================================================
     TAB 4: MY LEAVES & TIME OFF
     ============================================================= -->
<div class="hrm-tab-pane <?= $activeTab === 'my-leaves' ? 'active' : '' ?>" id="tab-my-leaves">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h5 class="fw-bold text-dark mb-1">Time Off &amp; Absence Requests</h5>
            <p class="text-muted small mb-0">Check remaining leave allowances and submit time-off requests to management.</p>
        </div>
        <button type="button" class="btn btn-dark btn-sm px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#applyLeaveModal" style="background-color: #101828; color: #fff;">
            <i class="bi bi-plus-lg me-1" style="color: #9DE500;"></i> Apply for Time Off
        </button>
    </div>

    <!-- Leave Quota KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="hrm-kpi-box">
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Annual Paid Vacation</span>
                <h3 class="fw-bold text-primary my-1"><?= (int)($leaveBalance['annual']['remaining'] ?? 14) ?> Days Left</h3>
                <small class="text-muted"><?= (int)($leaveBalance['annual']['used'] ?? 0) ?> of <?= (int)($leaveBalance['annual']['total'] ?? 14) ?> days utilized this year</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="hrm-kpi-box">
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Medical / Sick Leave</span>
                <h3 class="fw-bold text-danger my-1"><?= (int)($leaveBalance['sick']['remaining'] ?? 7) ?> Days Left</h3>
                <small class="text-muted"><?= (int)($leaveBalance['sick']['used'] ?? 0) ?> of <?= (int)($leaveBalance['sick']['total'] ?? 7) ?> days utilized this year</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="hrm-kpi-box">
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Casual Time Off</span>
                <h3 class="fw-bold text-warning my-1"><?= (int)($leaveBalance['casual']['remaining'] ?? 5) ?> Days Left</h3>
                <small class="text-muted"><?= (int)($leaveBalance['casual']['used'] ?? 0) ?> of <?= (int)($leaveBalance['casual']['total'] ?? 5) ?> days utilized this year</small>
            </div>
        </div>
    </div>

    <div class="hrm-panel">
        <div class="hrm-panel-header d-flex justify-content-between align-items-center">
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-calendar2-check-fill" style="color: var(--hrm-accent, #9DE500);"></i>
                My Submitted Leave Requests
            </h6>
            <span class="badge bg-light text-dark border"><?= count($leaveRequests ?? []) ?> Applications</span>
        </div>
        <div class="table-responsive">
            <table class="table hrm-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>Dates Requested</th>
                        <th>Duration</th>
                        <th>Reason / Handover Notes</th>
                        <th>Status</th>
                        <th>Management Remark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaveRequests)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No leave requests submitted yet. Click "Apply for Time Off" above to submit one.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaveRequests as $req): ?>
                            <tr>
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
                                <td class="small fw-semibold text-dark">
                                    <?= date('M d, Y', strtotime($req['start_date'])) ?> &rarr; <?= date('M d, Y', strtotime($req['end_date'])) ?>
                                </td>
                                <td class="font-monospace small"><?= (int)$req['days'] ?> Business Day(s)</td>
                                <td class="small text-muted" style="max-width: 260px;"><?= e($req['reason']) ?></td>
                                <td>
                                    <?php if ($req['status'] === 'approved'): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>APPROVED</span>
                                    <?php elseif ($req['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i>REJECTED</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-25 text-dark fw-bold"><i class="bi bi-hourglass-split me-1"></i>PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-secondary fst-italic">
                                    <?= !empty($req['admin_remark']) ? e($req['admin_remark']) : '<span class="text-muted">—</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =============================================================
     TAB 5: MY PAYROLL & PAYSLIPS
     ============================================================= -->
<div class="hrm-tab-pane <?= $activeTab === 'my-payroll' ? 'active' : '' ?>" id="tab-my-payroll">
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="hrm-kpi-box">
                <div>
                    <span class="text-muted small d-block mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.78rem;">Base Salary Structure</span>
                    <h4 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;">PKR <?= number_format($profile['base_salary'] ?? 55000) ?> / mo</h4>
                </div>
                <div class="pt-2 mt-2 border-top">
                    <small class="text-success fw-semibold"><i class="bi bi-shield-check me-1"></i>Official Salary Grade</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="hrm-kpi-box">
                <div>
                    <span class="text-muted small d-block mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.78rem;">Disbursement Bank</span>
                    <h4 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;"><?= e($profile['bank_name'] ?? 'Direct Transfer') ?></h4>
                </div>
                <div class="pt-2 mt-2 border-top">
                    <small class="text-muted font-monospace"><?= e($profile['account_number'] ?? 'N/A') ?></small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="hrm-kpi-box">
                <div>
                    <span class="text-muted small d-block mb-1 text-uppercase fw-semibold" style="letter-spacing: 0.04em; font-size: 0.78rem;">Tax &amp; Insurance Status</span>
                    <h4 class="fw-bold text-dark mb-0" style="letter-spacing: -0.02em;">Verified &amp; Active</h4>
                </div>
                <div class="pt-2 mt-2 border-top">
                    <small class="text-muted">Standard 5% income withholding applied</small>
                </div>
            </div>
        </div>
    </div>

    <div class="hrm-panel">
        <div class="hrm-panel-header d-flex justify-content-between align-items-center">
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-receipt" style="color: var(--hrm-emerald, #10b981);"></i>
                My Generated Pay Slips Archive
            </h6>
            <span class="badge bg-light text-dark border"><?= count($myPayrolls ?? []) ?> Payslips Available</span>
        </div>
        <div class="table-responsive">
            <table class="table hrm-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Pay Period</th>
                        <th class="text-end">Base Salary</th>
                        <th class="text-end text-success">Allowances</th>
                        <th class="text-end text-danger">Deductions</th>
                        <th class="text-end fw-bold">Net Salary</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">View &amp; Print</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myPayrolls)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No payrolls published yet. When administration processes payroll for the month, your official payslip will appear here.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($myPayrolls as $p): 
                            $mTitle = date('F Y', strtotime($p['month'] . '-01'));
                            $isPaid = ($p['payment_status'] === 'paid');
                        ?>
                            <tr>
                                <td class="fw-semibold text-dark">
                                    <?= $mTitle ?> Full Pay Cycle
                                </td>
                                <td class="text-end font-monospace small">PKR <?= number_format($p['base_salary']) ?></td>
                                <td class="text-end font-monospace small text-success">+<?= number_format($p['allowances']) ?></td>
                                <td class="text-end font-monospace small text-danger">
                                    <?= ($p['unpaid_deductions'] + $p['tax_deduction'] > 0) ? ('-' . number_format($p['unpaid_deductions'] + $p['tax_deduction'])) : '0' ?>
                                </td>
                                <td class="text-end font-monospace fw-bold text-dark">
                                    PKR <?= number_format($p['net_salary']) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isPaid): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>DISBURSED</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning bg-opacity-25 text-dark fw-bold"><i class="bi bi-clock-history me-1"></i>PROCESSED</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('payroll/payslip') ?>?id=<?= (int)$p['id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm fw-semibold">
                                        <i class="bi bi-printer me-1"></i> View Payslip
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- =============================================================
     TAB 6: MY ENGINEERING TEAM
     ============================================================= -->
<div class="hrm-tab-pane <?= $activeTab === 'my-team' ? 'active' : '' ?>" id="tab-my-team">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-1">Department Colleagues</h5>
            <p class="text-muted small mb-0">Team members working alongside you in <?= e($profile['department_name'] ?? 'your division') ?>.</p>
        </div>
        <span class="badge bg-light text-dark border px-3 py-2"><?= count($teamMembers) ?> Team Members</span>
    </div>

    <div class="row g-3">
        <?php foreach ($teamMembers as $member): 
            $mInitials = strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1));
            $isMe = ($member['id'] == ($profile['id'] ?? 3));
            $isManager = empty($member['manager_id']);
        ?>
            <div class="col-12 col-md-4">
                <div class="hrm-dept-card position-relative <?= $isMe ? 'border-primary' : '' ?>" style="<?= $isMe ? 'background-color: #f0fdfa;' : '' ?>">
                    <?php if ($isMe): ?>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-primary text-white">YOU</span>
                    <?php elseif ($isManager): ?>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>MANAGER</span>
                    <?php endif; ?>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="hrm-avatar-circle" style="width: 44px; height: 44px; font-size: 1rem;">
                            <?= e($mInitials) ?>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></h6>
                            <small class="text-secondary"><?= e($member['designation_title']) ?></small>
                        </div>
                    </div>

                    <div class="pt-2 border-top small text-muted">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Code:</span>
                            <span class="font-monospace fw-semibold text-dark"><?= e($member['employee_code']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Email:</span>
                            <span class="text-dark"><?= e($member['email']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Status:</span>
                            <span class="badge-status active">ACTIVE</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- =============================================================
     MODAL: APPLY FOR LEAVE
     ============================================================= -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark" id="applyLeaveModalLabel">
                    <i class="bi bi-calendar-plus text-teal me-1" style="color: var(--hrm-accent, #9DE500);"></i>
                    Submit Time Off Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('leaves/apply') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Leave Category <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select" required>
                            <option value="annual" selected>Annual Paid Vacation (<?= (int)($leaveBalance['annual']['remaining'] ?? 14) ?> days remaining)</option>
                            <option value="sick">Sick / Medical Leave (<?= (int)($leaveBalance['sick']['remaining'] ?? 7) ?> days remaining)</option>
                            <option value="casual">Casual Time Off (<?= (int)($leaveBalance['casual']['remaining'] ?? 5) ?> days remaining)</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Reason / Handover Notes <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Describe your leave reason and any ongoing code tasks handed over to team members..."></textarea>
                    </div>
                    <div class="p-2 bg-light rounded-2 border text-muted small">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Request will be routed directly to HR Administration for approval. Once approved, dates are automatically locked in your attendance record.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm px-4 fw-semibold" style="background-color: #101828; color: #fff;">
                        <i class="bi bi-send me-1" style="color: #9DE500;"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
