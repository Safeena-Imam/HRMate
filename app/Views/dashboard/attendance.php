<?php
$csrf = csrf_token();
$currentMode = $currentMode ?? 'daily';
$selectedDateStr = $selectedDate ?? date('Y-m-d');
$dateFormatted = date('l, F d, Y', strtotime($selectedDateStr));

$selectedMonthStr = $selectedMonth ?? date('Y-m');
[$selYear, $selMonth] = explode('-', $selectedMonthStr);
$daysInMonth = (int)cal_days_in_month(CAL_GREGORIAN, (int)$selMonth, (int)$selYear);
$monthName = date('F Y', strtotime("{$selectedMonthStr}-01"));

// Daily summary
$dailyPresent  = $dailySummary['present'] ?? 0;
$dailyAbsent   = $dailySummary['absent'] ?? 0;
$dailyHalf     = $dailySummary['half_day'] ?? 0;
$dailyLeave    = $dailySummary['leave'] ?? 0;
$dailyWfh      = $dailySummary['work_from_home'] ?? 0;
$dailyHoliday  = $dailySummary['holiday'] ?? 0;
$dailyUnmarked = $dailySummary['unmarked'] ?? 0;

// Monthly employee summary
$presentCount = $monthSummary['present'] ?? 0;
$absentCount  = $monthSummary['absent'] ?? 0;
$halfDayCount = $monthSummary['half_day'] ?? 0;
$leaveCount   = $monthSummary['leave'] ?? 0;
$wfhCount     = $monthSummary['work_from_home'] ?? 0;
$holidayCount = $monthSummary['holiday'] ?? 0;
?>

<!-- =============================================================
     TOP NAVIGATION & ATTENDANCE MODE SWITCHER
     ============================================================= -->
<div class="hrm-panel mb-4">
    <div class="hrm-panel-body py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-calendar-check-fill me-1" style="color: var(--hrm-accent, #9DE500);"></i>
                    Attendance Roster &amp; Shift Management
                </h5>
                <p class="text-muted small mb-0">Admin-Managed Attendance. Switch between daily roll call and monthly timesheets.</p>
            </div>
            
            <div class="btn-group" role="group">
                <a href="<?= url('attendance') ?>?mode=daily&date=<?= e($selectedDateStr) ?>" class="btn <?= ($currentMode === 'daily') ? 'btn-dark' : 'btn-outline-dark' ?> fw-semibold btn-sm px-3">
                    <i class="bi bi-calendar-day me-1" style="<?= ($currentMode === 'daily') ? 'color: #9DE500;' : '' ?>"></i>
                    Daily Attendance Sheet
                </a>
                <a href="<?= url('attendance') ?>?mode=monthly&month=<?= e($selectedMonthStr) ?>" class="btn <?= ($currentMode === 'monthly') ? 'btn-dark' : 'btn-outline-dark' ?> fw-semibold btn-sm px-3">
                    <i class="bi bi-calendar-month me-1" style="<?= ($currentMode === 'monthly') ? 'color: #9DE500;' : '' ?>"></i>
                    Monthly Individual Roster
                </a>
            </div>
        </div>
    </div>
</div>

<?php if ($currentMode === 'daily'): ?>
    <!-- =============================================================
         MODE 1: DAILY ATTENDANCE SHEET (SUPER FAST REGULAR ENTRY)
         ============================================================= -->
    <div class="hrm-panel mb-4">
        <div class="hrm-panel-body py-3">
            <form method="GET" action="<?= url('attendance') ?>" class="row g-3 align-items-end">
                <input type="hidden" name="mode" value="daily">
                
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Select Attendance Date</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event text-muted"></i></span>
                        <input type="date" name="date" class="form-control border-start-0 ps-0" value="<?= e($selectedDateStr) ?>" onchange="this.form.submit()">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <button type="submit" class="btn btn-dark w-100 fw-semibold">
                        <i class="bi bi-calendar-check me-1"></i> Load Date
                    </button>
                </div>

                <div class="col-6 col-md-4 text-end">
                    <a href="<?= url('attendance') ?>?date=<?= date('Y-m-d') ?>&mode=daily" class="btn btn-outline-secondary w-100 fw-semibold">
                        <i class="bi bi-clock-history me-1"></i> Today: <?= date('M d, Y') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Daily Status KPI Summary Row -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Present</span>
                <h3 class="fw-bold text-success my-1"><?= $dailyPresent ?></h3>
                <small class="text-muted">On Duty</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Absent</span>
                <h3 class="fw-bold text-danger my-1"><?= $dailyAbsent ?></h3>
                <small class="text-muted">Unattended</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Half Day</span>
                <h3 class="fw-bold text-warning my-1"><?= $dailyHalf ?></h3>
                <small class="text-muted">Half Shift</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Approved Leave</span>
                <h3 class="fw-bold text-primary my-1"><?= $dailyLeave ?></h3>
                <small class="text-muted">Time Off</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Remote / WFH</span>
                <h3 class="fw-bold text-info my-1"><?= $dailyWfh ?></h3>
                <small class="text-muted">Online</small>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="hrm-kpi-box text-center py-2 h-100">
                <span class="text-muted small text-uppercase" style="font-size: 0.7rem;">Unmarked</span>
                <h3 class="fw-bold text-secondary my-1"><?= $dailyUnmarked ?></h3>
                <small class="text-muted">Pending Entry</small>
            </div>
        </div>
    </div>

    <!-- Daily Staff Attendance Form (All Staff In 1 Sheet) -->
    <div class="hrm-panel mb-5">
        <div class="hrm-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="hrm-panel-title mb-0">
                    <i class="bi bi-person-check-fill" style="color: var(--hrm-accent, #9DE500);"></i>
                    Daily Roll Call &bull; <?= e($dateFormatted) ?>
                </h6>
                <small class="text-muted">Set statuses for all employees in one place, then click "Save Daily Attendance Sheet".</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-success fw-semibold" onclick="setAllDailyStatus('present')">
                    <i class="bi bi-check-all me-1"></i> Mark All Present
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAllDailyStatus('holiday')">
                    <i class="bi bi-calendar-event me-1"></i> Mark Holiday
                </button>
            </div>
        </div>

        <form method="POST" action="<?= url('attendance/save-daily') ?>" id="dailyAttendanceForm">
            <?= csrf_field() ?>
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="date" value="<?= e($selectedDateStr) ?>">

            <div class="table-responsive">
                <table class="table hrm-table table-hover align-middle mb-0" id="dailyAttendanceTable">
                    <thead>
                        <tr>
                            <th style="width: 250px;">Employee</th>
                            <th>Department &amp; Role</th>
                            <th style="width: 230px;">Attendance Status</th>
                            <th>Admin Notes / Remark</th>
                            <th style="width: 120px;" class="text-center">Live Badge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dailyAttendance)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No active employees found to mark attendance.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dailyAttendance as $dEmp): 
                                $empId = (int)$dEmp['id'];
                                $initials = strtoupper(substr($dEmp['first_name'], 0, 1) . substr($dEmp['last_name'], 0, 1));
                                $status = $dEmp['status'] ?? '';
                                $notes  = $dEmp['notes'] ?? '';
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="hrm-avatar-circle" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                <?= e($initials) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark small"><?= e($dEmp['full_name']) ?></div>
                                                <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($dEmp['employee_code']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?= e($dEmp['department_name'] ?? 'General') ?></div>
                                        <small class="text-muted"><?= e($dEmp['designation_title'] ?? 'Staff') ?></small>
                                    </td>
                                    <td>
                                        <select name="daily_attendance[<?= $empId ?>]" class="form-select form-select-sm daily-status-select" onchange="updateDailyBadge(this)">
                                            <option value="" <?= empty($status) ? 'selected' : '' ?>>-- Unmarked --</option>
                                            <option value="present" <?= $status === 'present' ? 'selected' : '' ?>>✅ Present</option>
                                            <option value="absent" <?= $status === 'absent' ? 'selected' : '' ?>>❌ Absent</option>
                                            <option value="half_day" <?= $status === 'half_day' ? 'selected' : '' ?>>🕐 Half Day</option>
                                            <option value="leave" <?= $status === 'leave' ? 'selected' : '' ?>>🏖️ Approved Leave</option>
                                            <option value="work_from_home" <?= $status === 'work_from_home' ? 'selected' : '' ?>>🏠 Work From Home</option>
                                            <option value="holiday" <?= $status === 'holiday' ? 'selected' : '' ?>>🎉 Holiday / Off</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="daily_notes[<?= $empId ?>]" class="form-control form-control-sm" placeholder="Optional remark (e.g. On-time, late arrival)" value="<?= e($notes) ?>">
                                    </td>
                                    <td class="text-center daily-badge-cell">
                                        <?php if ($status === 'present'): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success">Present</span>
                                        <?php elseif ($status === 'absent'): ?>
                                            <span class="badge bg-danger bg-opacity-25 text-danger">Absent</span>
                                        <?php elseif ($status === 'half_day'): ?>
                                            <span class="badge bg-warning bg-opacity-25 text-dark">Half Day</span>
                                        <?php elseif ($status === 'leave'): ?>
                                            <span class="badge bg-primary bg-opacity-25 text-primary">Leave</span>
                                        <?php elseif ($status === 'work_from_home'): ?>
                                            <span class="badge bg-info bg-opacity-25 text-info">WFH</span>
                                        <?php elseif ($status === 'holiday'): ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">Holiday</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Unmarked</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-light border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Click save to register attendance for all employees on <?= e($dateFormatted) ?>.
                </span>
                <button type="submit" class="btn btn-dark px-4 fw-semibold" style="background-color: #101828; color: #fff;">
                    <i class="bi bi-check2-circle me-1" style="color: #9DE500;"></i> Save Daily Attendance Sheet
                </button>
            </div>
        </form>
    </div>

<?php else: ?>
    <!-- =============================================================
         MODE 2: MONTHLY INDIVIDUAL TIMESHEET ROSTER
         ============================================================= -->
    <div class="hrm-panel mb-4">
        <div class="hrm-panel-body py-3">
            <form method="GET" action="<?= url('attendance') ?>" class="row g-3 align-items-end">
                <input type="hidden" name="mode" value="monthly">
                
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Select Employee</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <select name="employee_id" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
                            <?php foreach ($allEmployees as $emp): ?>
                                <option value="<?= (int)$emp['id'] ?>" <?= ((int)$emp['id'] === (int)$selectedEmployeeId) ? 'selected' : '' ?>>
                                    <?= e($emp['first_name'] . ' ' . $emp['last_name']) ?> (<?= e($emp['employee_code']) ?>) - <?= e($emp['department_name'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold small text-muted text-uppercase mb-1">Attendance Month</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-month text-muted"></i></span>
                        <input type="month" name="month" class="form-control border-start-0 ps-0" value="<?= e($selectedMonthStr) ?>" onchange="this.form.submit()">
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-dark w-100 fw-semibold">
                        <i class="bi bi-filter me-1"></i> View Sheet
                    </button>
                </div>

                <div class="col-12 col-md-3 text-md-end d-flex gap-2">
                    <a href="<?= url('attendance/export') ?>?month=<?= e($selectedMonthStr) ?>&employee_id=<?= (int)$selectedEmployeeId ?>" class="btn btn-outline-success w-100 fw-semibold" title="Export this employee's monthly attendance to CSV">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export (CSV)
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selectedEmployee): ?>
        <!-- Selected Employee Summary Card & KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
                <div class="hrm-kpi-box h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="hrm-avatar-circle" style="width: 48px; height: 48px; font-size: 1.1rem; background: #101828; color: #9DE500;">
                                <?= strtoupper(substr($selectedEmployee['first_name'], 0, 1) . substr($selectedEmployee['last_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark"><?= e($selectedEmployee['first_name'] . ' ' . $selectedEmployee['last_name']) ?></h5>
                                <span class="text-muted small"><?= e($selectedEmployee['employee_code']) ?> &bull; <?= e($selectedEmployee['designation_title'] ?? 'Staff') ?></span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge bg-light text-dark border"><i class="bi bi-building me-1"></i><?= e($selectedEmployee['department_name'] ?? 'General') ?></span>
                            <span class="badge-status <?= e($selectedEmployee['employment_status']) ?>"><?= strtoupper(e($selectedEmployee['employment_status'])) ?></span>
                        </div>
                    </div>
                    <div class="pt-2 border-top text-muted small">
                        Managing Attendance for <strong><?= e($monthName) ?></strong>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="row g-2 h-100">
                    <div class="col-6 col-md-4">
                        <div class="hrm-kpi-box h-100 text-center py-3">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Present</span>
                            <h3 class="fw-bold text-success my-1"><?= $presentCount ?></h3>
                            <small class="text-muted">Days Marked</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hrm-kpi-box h-100 text-center py-3">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Absent</span>
                            <h3 class="fw-bold text-danger my-1"><?= $absentCount ?></h3>
                            <small class="text-muted">Days Unattended</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hrm-kpi-box h-100 text-center py-3">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Half Day</span>
                            <h3 class="fw-bold text-warning my-1"><?= $halfDayCount ?></h3>
                            <small class="text-muted">4 Hours Shift</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hrm-kpi-box h-100 text-center py-3">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Approved Leave</span>
                            <h3 class="fw-bold text-primary my-1"><?= $leaveCount ?></h3>
                            <small class="text-muted">Time Off</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hrm-kpi-box h-100 text-center py-3">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Work From Home</span>
                            <h3 class="fw-bold text-info my-1"><?= $wfhCount ?></h3>
                            <small class="text-muted">Remote Duty</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hrm-kpi-box h-100 text-center py-3">
                            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.72rem;">Holidays</span>
                            <h3 class="fw-bold text-secondary my-1"><?= $holidayCount ?></h3>
                            <small class="text-muted">Official Off</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Timesheet Table (30-day view for 1 employee) -->
        <div class="hrm-panel mb-5">
            <div class="hrm-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="hrm-panel-title mb-0">
                        <i class="bi bi-calendar3-range" style="color: var(--hrm-accent, #9DE500);"></i>
                        Monthly Timesheet: <?= e($selectedEmployee['first_name'] . ' ' . $selectedEmployee['last_name']) ?> &bull; <?= e($monthName) ?>
                    </h6>
                    <small class="text-muted">Admin-Only Attendance Roster. Select status for each day and click Save.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="markAllWeekdaysPresent()">
                        <i class="bi bi-check-all me-1"></i> Mark Weekdays as Present
                    </button>
                </div>
            </div>

            <form method="POST" action="<?= url('attendance/save') ?>" id="attendanceForm">
                <?= csrf_field() ?>
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="employee_id" value="<?= (int)$selectedEmployeeId ?>">
                <input type="hidden" name="month" value="<?= e($selectedMonthStr) ?>">

                <div class="table-responsive" style="max-height: 580px; overflow-y: auto;">
                    <table class="table hrm-table table-hover align-middle mb-0" id="attendanceGridTable">
                        <thead class="sticky-top bg-light" style="z-index: 2;">
                            <tr>
                                <th style="width: 130px;">Date</th>
                                <th style="width: 110px;">Day</th>
                                <th style="width: 220px;">Attendance Status</th>
                                <th>Admin Notes / Reason</th>
                                <th style="width: 120px;" class="text-center">Current State</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                                $dayDateStr = sprintf('%04d-%02d-%02d', $selYear, $selMonth, $d);
                                $timestamp  = strtotime($dayDateStr);
                                $dayName    = date('l', $timestamp);
                                $shortDate  = date('M d, Y', $timestamp);
                                $isWeekend  = ($dayName === 'Saturday' || $dayName === 'Sunday');
                                $record     = $attendanceRecords[$dayDateStr] ?? null;
                                $curStatus  = $record['status'] ?? ($isWeekend ? 'holiday' : '');
                                $curNotes   = $record['notes'] ?? '';
                            ?>
                                <tr class="<?= $isWeekend ? 'bg-light bg-opacity-50' : '' ?>" data-day="<?= $dayName ?>">
                                    <td class="fw-semibold text-dark">
                                        <?= $shortDate ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $isWeekend ? 'bg-secondary bg-opacity-25 text-secondary' : 'bg-light text-dark border' ?> small">
                                            <?= $dayName ?>
                                        </span>
                                    </td>
                                    <td>
                                        <select name="attendance[<?= $dayDateStr ?>]" class="form-select form-select-sm status-selector" data-weekend="<?= $isWeekend ? '1' : '0' ?>" onchange="updateRowStateBadge(this)">
                                            <option value="" <?= empty($curStatus) ? 'selected' : '' ?>>-- Select Status --</option>
                                            <option value="present" <?= $curStatus === 'present' ? 'selected' : '' ?>>✅ Present</option>
                                            <option value="absent" <?= $curStatus === 'absent' ? 'selected' : '' ?>>❌ Absent</option>
                                            <option value="half_day" <?= $curStatus === 'half_day' ? 'selected' : '' ?>>🕐 Half Day</option>
                                            <option value="leave" <?= $curStatus === 'leave' ? 'selected' : '' ?>>🏖️ Approved Leave</option>
                                            <option value="work_from_home" <?= $curStatus === 'work_from_home' ? 'selected' : '' ?>>🏠 Work From Home</option>
                                            <option value="holiday" <?= $curStatus === 'holiday' ? 'selected' : '' ?>>🎉 Holiday / Off</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="notes[<?= $dayDateStr ?>]" class="form-control form-control-sm" placeholder="Optional remark (e.g. Approved leave, late arrival)" value="<?= e($curNotes) ?>">
                                    </td>
                                    <td class="text-center row-state-badge">
                                        <?php if ($curStatus === 'present'): ?>
                                            <span class="badge bg-success bg-opacity-25 text-success">Present</span>
                                        <?php elseif ($curStatus === 'absent'): ?>
                                            <span class="badge bg-danger bg-opacity-25 text-danger">Absent</span>
                                        <?php elseif ($curStatus === 'half_day'): ?>
                                            <span class="badge bg-warning bg-opacity-25 text-dark">Half Day</span>
                                        <?php elseif ($curStatus === 'leave'): ?>
                                            <span class="badge bg-primary bg-opacity-25 text-primary">Leave</span>
                                        <?php elseif ($curStatus === 'work_from_home'): ?>
                                            <span class="badge bg-info bg-opacity-25 text-info">WFH</span>
                                        <?php elseif ($curStatus === 'holiday'): ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">Holiday</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Unmarked</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="bi bi-shield-lock-fill me-1 text-success"></i> Changes will be saved for <?= e($selectedEmployee['first_name'] . ' ' . $selectedEmployee['last_name']) ?>.
                    </span>
                    <button type="submit" class="btn btn-dark px-4 fw-semibold" style="background-color: #101828; color: #fff;">
                        <i class="bi bi-check2-circle me-1" style="color: #9DE500;"></i> Save Timesheet
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- =============================================================
     ALL STAFF MONTHLY ATTENDANCE OVERVIEW TABLE
     ============================================================= -->
<div class="hrm-panel" id="staff-overview-section">
    <div class="hrm-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="hrm-panel-title mb-0">
                <i class="bi bi-people-fill" style="color: var(--hrm-accent, #9DE500);"></i>
                Monthly Staff Attendance Summary &bull; <?= e($monthName) ?>
            </h6>
            <small class="text-muted">High-level attendance breakdown for all enrolled employees</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('attendance/export') ?>?month=<?= e($selectedMonthStr) ?>" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export All Staff (CSV)
            </a>
            <span class="badge bg-light text-dark border"><?= count($monthlyStaffOverview) ?> Active Employees</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table hrm-table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="text-center text-success">Present</th>
                    <th class="text-center text-danger">Absent</th>
                    <th class="text-center text-warning">Half Day</th>
                    <th class="text-center text-primary">Leaves</th>
                    <th class="text-center text-info">WFH</th>
                    <th class="text-center text-secondary">Holidays</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($monthlyStaffOverview)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No employee attendance records found for this month.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($monthlyStaffOverview as $row): 
                        $initials = strtoupper(substr($row['full_name'], 0, 2));
                        $isCurrent = ((int)$row['id'] === (int)$selectedEmployeeId);
                    ?>
                        <tr class="<?= $isCurrent ? 'table-active' : '' ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="hrm-avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                        <?= e($initials) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark small"><?= e($row['full_name']) ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($row['employee_code']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border small"><?= e($row['department_name'] ?? 'General') ?></span></td>
                            <td class="text-center fw-bold text-success"><?= (int)$row['present_days'] ?></td>
                            <td class="text-center fw-bold text-danger"><?= (int)$row['absent_days'] ?></td>
                            <td class="text-center fw-bold text-warning"><?= (int)$row['half_days'] ?></td>
                            <td class="text-center fw-bold text-primary"><?= (int)$row['leave_days'] ?></td>
                            <td class="text-center fw-bold text-info"><?= (int)$row['wfh_days'] ?></td>
                            <td class="text-center text-muted"><?= (int)$row['holiday_days'] ?></td>
                            <td class="text-end">
                                <a href="<?= url('attendance') ?>?mode=monthly&employee_id=<?= (int)$row['id'] ?>&month=<?= e($selectedMonthStr) ?>" class="btn btn-sm <?= $isCurrent ? 'btn-dark' : 'btn-outline-dark' ?>">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Monthly Sheet
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function setAllDailyStatus(statusVal) {
    const selects = document.querySelectorAll('#dailyAttendanceTable .daily-status-select');
    let count = 0;
    selects.forEach(sel => {
        sel.value = statusVal;
        updateDailyBadge(sel);
        count++;
    });
}

function updateDailyBadge(selectElem) {
    const row = selectElem.closest('tr');
    const badgeCell = row.querySelector('.daily-badge-cell');
    const val = selectElem.value;
    
    let badgeHtml = '<span class="badge bg-light text-muted border">Unmarked</span>';
    if (val === 'present') {
        badgeHtml = '<span class="badge bg-success bg-opacity-25 text-success">Present</span>';
    } else if (val === 'absent') {
        badgeHtml = '<span class="badge bg-danger bg-opacity-25 text-danger">Absent</span>';
    } else if (val === 'half_day') {
        badgeHtml = '<span class="badge bg-warning bg-opacity-25 text-dark">Half Day</span>';
    } else if (val === 'leave') {
        badgeHtml = '<span class="badge bg-primary bg-opacity-25 text-primary">Leave</span>';
    } else if (val === 'work_from_home') {
        badgeHtml = '<span class="badge bg-info bg-opacity-25 text-info">WFH</span>';
    } else if (val === 'holiday') {
        badgeHtml = '<span class="badge bg-secondary bg-opacity-25 text-secondary">Holiday</span>';
    }
    badgeCell.innerHTML = badgeHtml;
}

function markAllWeekdaysPresent() {
    const selects = document.querySelectorAll('#attendanceGridTable .status-selector');
    let updated = 0;
    selects.forEach(sel => {
        const isWeekend = sel.getAttribute('data-weekend') === '1';
        if (!isWeekend && (!sel.value || sel.value === '')) {
            sel.value = 'present';
            updateRowStateBadge(sel);
            updated++;
        }
    });
    alert(updated + ' unmarked weekday(s) have been set to "Present". Click "Save Timesheet" below to commit changes.');
}

function updateRowStateBadge(selectElem) {
    const row = selectElem.closest('tr');
    const badgeCell = row.querySelector('.row-state-badge');
    const val = selectElem.value;
    
    let badgeHtml = '<span class="badge bg-light text-muted border">Unmarked</span>';
    if (val === 'present') {
        badgeHtml = '<span class="badge bg-success bg-opacity-25 text-success">Present</span>';
    } else if (val === 'absent') {
        badgeHtml = '<span class="badge bg-danger bg-opacity-25 text-danger">Absent</span>';
    } else if (val === 'half_day') {
        badgeHtml = '<span class="badge bg-warning bg-opacity-25 text-dark">Half Day</span>';
    } else if (val === 'leave') {
        badgeHtml = '<span class="badge bg-primary bg-opacity-25 text-primary">Leave</span>';
    } else if (val === 'work_from_home') {
        badgeHtml = '<span class="badge bg-info bg-opacity-25 text-info">WFH</span>';
    } else if (val === 'holiday') {
        badgeHtml = '<span class="badge bg-secondary bg-opacity-25 text-secondary">Holiday</span>';
    }
    badgeCell.innerHTML = badgeHtml;
}
</script>
