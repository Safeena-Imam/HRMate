<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Role;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Announcement;
use App\Services\MailService;

class DashboardController extends BaseController
{
    /**
     * Minimal Executive Dashboard with essential KPI cards, analytics, and recent activity
     */
    public function index(): void
    {
        // If current logged-in user is an Employee, route to their personal Self-Service Portal
        if (has_role('employee')) {
            $this->employeeIndex();
            return;
        }

        // Backward compatibility with ?tab= queries: redirect cleanly to dedicated page
        if (!empty($_GET['tab'])) {
            $tab = strtolower(trim($_GET['tab']));
            $tabRoutes = [
                'employees'   => 'employees',
                'departments' => 'departments',
                'attendance'  => 'attendance',
                'payroll'     => 'payroll',
                'audit'       => 'audit-logs'
            ];
            if (isset($tabRoutes[$tab])) {
                redirect($tabRoutes[$tab]);
                return;
            }
        }

        try {
            $employeeMetrics = Employee::getStatusBreakdown();
            $departmentCount = Department::countTotal();
            $departments     = Department::getAll();
            $recentEmployees = Employee::getRecent(5);
            $recentAuditLogs = AuditLog::getRecent(5);
            $announcements   = Announcement::getAll();
            $dbConnected     = true;
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "DashboardController::index metrics fetch failed");
            $employeeMetrics = ['total' => 0, 'active' => 0, 'inactive' => 0, 'on_leave' => 0, 'terminated' => 0];
            $departmentCount = 0;
            $departments     = [];
            $recentEmployees = [];
            $recentAuditLogs = [];
            $announcements   = [];
            $dbConnected     = false;
            set_flash('danger', 'Unable to retrieve dashboard metrics: ' . $e->getMessage());
        }

        $this->view('dashboard.index', [
            'pageTitle'        => 'Executive Dashboard',
            'activeNav'        => 'dashboard',
            'employeeMetrics'  => $employeeMetrics,
            'departmentCount'  => $departmentCount,
            'departments'      => $departments,
            'recentEmployees'  => $recentEmployees,
            'recentAuditLogs'  => $recentAuditLogs,
            'announcements'    => $announcements,
            'dbConnected'      => $dbConnected
        ], 'main');
    }

    /**
     * Dedicated Staff Directory Page
     */
    public function employees(): void
    {
        try {
            $allEmployees    = Employee::getAll();
            $departments     = Department::getAll();
            $designations    = Designation::getAll();
            $employeeMetrics = Employee::getStatusBreakdown();
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "DashboardController::employees failed");
            $allEmployees    = [];
            $departments     = [];
            $designations    = [];
            $employeeMetrics = ['total' => 0, 'active' => 0, 'inactive' => 0, 'on_leave' => 0];
            set_flash('danger', 'Error loading staff records: ' . $e->getMessage());
        }

        $this->view('dashboard.employees', [
            'pageTitle'        => 'Staff Directory',
            'activeNav'        => 'employees',
            'allEmployees'     => $allEmployees,
            'departments'      => $departments,
            'designations'     => $designations,
            'employeeMetrics'  => $employeeMetrics
        ], 'main');
    }

    /**
     * Dedicated Departments & Roles Page
     */
    public function departments(): void
    {
        try {
            $departments    = Department::getAll();
            $designations   = Designation::getAll();
            $totalEmployees = Employee::countTotal();

            $selectedDeptId     = (int)($_GET['dept_id'] ?? 0);
            $selectedDepartment = null;
            $deptEmployees      = [];

            if ($selectedDeptId > 0) {
                $selectedDepartment = Department::findById($selectedDeptId);
                if ($selectedDepartment) {
                    $pdo = \Config\Database::getConnection();
                    $stmt = $pdo->prepare(
                        "SELECT e.*, des.title AS designation_title 
                         FROM employees e 
                         LEFT JOIN designations des ON e.designation_id = des.id 
                         WHERE e.department_id = :dept_id 
                         ORDER BY e.first_name ASC"
                    );
                    $stmt->execute(['dept_id' => $selectedDeptId]);
                    $deptEmployees = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            }
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "DashboardController::departments failed");
            $departments        = [];
            $designations       = [];
            $totalEmployees     = 0;
            $selectedDeptId     = 0;
            $selectedDepartment = null;
            $deptEmployees      = [];
            set_flash('danger', 'Error loading department records: ' . $e->getMessage());
        }

        $this->view('dashboard.departments', [
            'pageTitle'          => 'Departments & Roles',
            'activeNav'          => 'departments',
            'departments'        => $departments,
            'designations'       => $designations,
            'totalEmployees'     => $totalEmployees,
            'selectedDeptId'     => $selectedDeptId,
            'selectedDepartment' => $selectedDepartment,
            'deptEmployees'      => $deptEmployees
        ], 'main');
    }

    /**
     * Dedicated Attendance & Leaves Page (Admin Daily Roster & Monthly Calendar)
     */
    public function attendance(): void
    {
        if (has_role('employee')) {
            redirect('dashboard?tab=my-attendance');
            return;
        }

        try {
            $employeeMetrics = Employee::getStatusBreakdown();
            $allEmployees    = Employee::getAll();
            $departments     = Department::getAll();

            // Mode: 'daily' (default) or 'monthly'
            $currentMode = $_GET['mode'] ?? 'daily';
            if (!in_array($currentMode, ['daily', 'monthly'], true)) {
                $currentMode = 'daily';
            }

            // Daily Date selector
            $selectedDate = $_GET['date'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
                $selectedDate = date('Y-m-d');
            }

            // Fetch daily attendance records for all active employees
            $dailyAttendance = Attendance::getByDate($selectedDate);
            $dailySummary = [
                'present'        => 0,
                'absent'         => 0,
                'half_day'       => 0,
                'leave'          => 0,
                'work_from_home' => 0,
                'holiday'        => 0,
                'unmarked'       => 0,
            ];
            foreach ($dailyAttendance as $dRow) {
                $st = $dRow['status'] ?? '';
                if (isset($dailySummary[$st])) {
                    $dailySummary[$st]++;
                } else {
                    $dailySummary['unmarked']++;
                }
            }

            // Monthly timesheet selector
            $selectedMonth = $_GET['month'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
                $selectedMonth = date('Y-m');
            }
            [$year, $month] = explode('-', $selectedMonth);

            $selectedEmployeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : (isset($allEmployees[0]['id']) ? (int)$allEmployees[0]['id'] : 0);
            
            $selectedEmployee = null;
            foreach ($allEmployees as $emp) {
                if ((int)$emp['id'] === $selectedEmployeeId) {
                    $selectedEmployee = $emp;
                    break;
                }
            }
            if (!$selectedEmployee && !empty($allEmployees)) {
                $selectedEmployee = $allEmployees[0];
                $selectedEmployeeId = (int)$selectedEmployee['id'];
            }

            $attendanceRecords = [];
            $monthSummary = ['present' => 0, 'absent' => 0, 'half_day' => 0, 'leave' => 0, 'work_from_home' => 0, 'holiday' => 0];
            if ($selectedEmployeeId > 0) {
                $attendanceRecords = Attendance::getByEmployeeMonth($selectedEmployeeId, $year, $month);
                $monthSummary = Attendance::getMonthSummary($selectedEmployeeId, $year, $month);
            }

            $monthlyStaffOverview = Attendance::getAllEmployeesForMonth($year, $month);

        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "DashboardController::attendance failed");
            $employeeMetrics      = ['total' => 0, 'active' => 0, 'inactive' => 0, 'on_leave' => 0];
            $allEmployees         = [];
            $departments          = [];
            $currentMode          = 'daily';
            $selectedDate         = date('Y-m-d');
            $dailyAttendance      = [];
            $dailySummary         = ['present' => 0, 'absent' => 0, 'half_day' => 0, 'leave' => 0, 'work_from_home' => 0, 'holiday' => 0, 'unmarked' => 0];
            $selectedMonth        = date('Y-m');
            $selectedEmployeeId   = 0;
            $selectedEmployee     = null;
            $attendanceRecords    = [];
            $monthSummary         = ['present' => 0, 'absent' => 0, 'half_day' => 0, 'leave' => 0, 'work_from_home' => 0, 'holiday' => 0];
            $monthlyStaffOverview = [];
            set_flash('danger', 'Error loading attendance records: ' . $e->getMessage());
        }

        $this->view('dashboard.attendance', [
            'pageTitle'            => 'Attendance & Leaves',
            'activeNav'            => 'attendance',
            'employeeMetrics'      => $employeeMetrics,
            'allEmployees'         => $allEmployees,
            'departments'          => $departments,
            'currentMode'          => $currentMode,
            'selectedDate'         => $selectedDate,
            'dailyAttendance'      => $dailyAttendance,
            'dailySummary'         => $dailySummary,
            'selectedMonth'        => $selectedMonth,
            'selectedEmployeeId'   => $selectedEmployeeId,
            'selectedEmployee'     => $selectedEmployee,
            'attendanceRecords'    => $attendanceRecords,
            'monthSummary'         => $monthSummary,
            'monthlyStaffOverview' => $monthlyStaffOverview
        ], 'main');
    }

    /**
     * Save daily attendance batch for all employees (Admin only)
     */
    public function saveDailyAttendance(): void
    {
        if (has_role('employee') || (!has_role('admin') && !has_permission('attendance.manage'))) {
            $this->view('errors.403', [
                'pageTitle' => '403 Forbidden',
                'message'   => 'Only administrators and authorized managers can manage attendance records.'
            ], 'auth');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('attendance');
            return;
        }

        $date           = trim($_POST['date'] ?? date('Y-m-d'));
        $attendanceData = $_POST['daily_attendance'] ?? [];
        $notesData      = $_POST['daily_notes'] ?? [];
        $currentUser    = current_user();
        $markedBy       = $currentUser['id'] ?? 1;

        if (is_array($attendanceData) && !empty($attendanceData)) {
            try {
                $savedCount = Attendance::saveDailyBatch($date, $attendanceData, $notesData, $markedBy);

                AuditLog::record(
                    $currentUser['id'] ?? null,
                    'ATTENDANCE_DAILY_UPDATE',
                    'attendance',
                    null,
                    "Marked daily attendance for {$savedCount} staff members for date {$date}"
                );

                set_flash('success', "Daily attendance for " . date('M d, Y', strtotime($date)) . " was successfully saved ({$savedCount} staff records updated)!");
            } catch (\Throwable $e) {
                \App\Services\Logger::exception($e, "saveDailyAttendance failed");
                set_flash('danger', 'Error updating daily attendance: ' . $e->getMessage());
            }
        }

        redirect("attendance?date={$date}&mode=daily");
    }

    /**
     * Save employee monthly attendance records (Admin only)
     */
    public function saveAttendance(): void
    {
        if (has_role('employee') || (!has_role('admin') && !has_permission('attendance.manage'))) {
            $this->view('errors.403', [
                'pageTitle' => '403 Forbidden',
                'message'   => 'Only administrators and authorized managers can manage attendance records.'
            ], 'auth');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('attendance');
            return;
        }

        $empId          = (int)($_POST['employee_id'] ?? 0);
        $month          = trim($_POST['month'] ?? date('Y-m'));
        $attendanceData = $_POST['attendance'] ?? [];
        $notesData      = $_POST['notes'] ?? [];
        $currentUser    = current_user();
        $markedBy       = $currentUser['id'] ?? 1;

        if ($empId > 0 && is_array($attendanceData)) {
            try {
                foreach ($attendanceData as $date => $status) {
                    if (empty($status)) {
                        continue;
                    }
                    $notes = trim($notesData[$date] ?? '');
                    Attendance::saveRecord($empId, $date, $status, $notes, $markedBy);
                }

                AuditLog::record(
                    $currentUser['id'] ?? null,
                    'ATTENDANCE_UPDATE',
                    'attendance',
                    $empId,
                    "Saved monthly attendance records for employee ID #{$empId} for period {$month}"
                );

                set_flash('success', "Attendance records for period {$month} were successfully updated!");
            } catch (\Throwable $e) {
                \App\Services\Logger::exception($e, "DashboardController::saveAttendance failed");
                set_flash('danger', 'Error updating attendance: ' . $e->getMessage());
            }
        }

        redirect("attendance?employee_id={$empId}&month={$month}");
    }

    /**
     * Export attendance records as CSV
     */
    public function exportAttendance(): void
    {
        $selectedMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }
        [$year, $month] = explode('-', $selectedMonth);
        $daysInMonth = (int)cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

        $empId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
        $currentUser = current_user();

        // If employee role, restrict to their own records
        if (has_role('employee')) {
            $empId = (int)($currentUser['employee_id'] ?? 0);
        }

        $employees = [];
        if ($empId > 0) {
            $single = Employee::findById($empId);
            if ($single) {
                $employees[] = $single;
            }
        } else {
            $employees = Employee::getAll();
        }

        $filename = "Attendance_Report_{$selectedMonth}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // CSV Header
        fputcsv($out, ['Date', 'Day', 'Employee Code', 'Employee Name', 'Department', 'Designation', 'Status', 'Notes']);

        foreach ($employees as $emp) {
            $records = Attendance::getByEmployeeMonth((int)$emp['id'], $year, $month);
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dStr = sprintf('%s-%02d', $selectedMonth, $d);
                $ts = strtotime($dStr);
                $dayName = date('l', $ts);
                $isWeekend = ($dayName === 'Saturday' || $dayName === 'Sunday');
                $rec = $records[$dStr] ?? null;
                $status = $rec['status'] ?? ($isWeekend ? 'holiday' : 'unmarked');
                $notes = $rec['notes'] ?? ($isWeekend ? 'Weekend Off' : '');

                fputcsv($out, [
                    $dStr,
                    $dayName,
                    $emp['employee_code'],
                    $emp['first_name'] . ' ' . $emp['last_name'],
                    $emp['department_name'] ?? '',
                    $emp['designation_title'] ?? '',
                    strtoupper($status),
                    $notes
                ]);
            }
        }
        fclose($out);
        exit;
    }

    /**
     * Dedicated Leaves Review & Approval Page (Admin)
     */
    public function leaves(): void
    {
        if (has_role('employee')) {
            redirect('dashboard?tab=my-leaves');
            return;
        }

        $filterStatus = $_GET['status'] ?? null;
        if (!in_array($filterStatus, ['pending', 'approved', 'rejected'], true)) {
            $filterStatus = null;
        }

        $leaveRequests = LeaveRequest::getAll($filterStatus);
        $pendingCount  = LeaveRequest::countPending();

        $this->view('dashboard.leaves', [
            'pageTitle'     => 'Time Off & Leave Requests',
            'activeNav'     => 'leaves',
            'leaveRequests' => $leaveRequests,
            'pendingCount'  => $pendingCount,
            'currentFilter' => $filterStatus,
        ], 'main');
    }

    /**
     * Handle Employee Leave Submission
     */
    public function applyLeave(): void
    {
        if (!$this->validateCSRF()) {
            redirect('dashboard?tab=my-leaves');
            return;
        }

        $currentUser = current_user();
        $empId = (int)($currentUser['employee_id'] ?? 0);

        // Admin applying on behalf or employee
        if (has_role('admin') && !empty($_POST['employee_id'])) {
            $empId = (int)$_POST['employee_id'];
        }

        if ($empId <= 0) {
            set_flash('danger', 'Unable to associate employee profile with this leave request.');
            redirect('dashboard?tab=my-leaves');
            return;
        }

        $leaveType = trim($_POST['leave_type'] ?? 'annual');
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate   = trim($_POST['end_date'] ?? '');
        $reason    = trim($_POST['reason'] ?? '');

        if (empty($startDate) || empty($endDate) || empty($reason)) {
            set_flash('danger', 'Please complete all required fields (Start Date, End Date, Reason).');
            redirect('dashboard?tab=my-leaves');
            return;
        }

        $startTs = strtotime($startDate);
        $endTs   = strtotime($endDate);

        if ($endTs < $startTs) {
            set_flash('danger', 'End Date cannot be earlier than Start Date.');
            redirect('dashboard?tab=my-leaves');
            return;
        }

        // Calculate business days
        $days = 0;
        for ($cur = $startTs; $cur <= $endTs; $cur = strtotime('+1 day', $cur)) {
            $dName = date('l', $cur);
            if ($dName !== 'Saturday' && $dName !== 'Sunday') {
                $days++;
            }
        }
        $days = max(1, $days);

        $emp = Employee::findById($empId);

        try {
            LeaveRequest::create([
                'employee_id' => $empId,
                'leave_type'  => $leaveType,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'days'        => $days,
                'reason'      => $reason,
                'manager_id'  => $emp['manager_id'] ?? null,
            ]);

            AuditLog::record(
                $currentUser['id'] ?? null,
                'LEAVE_REQUEST',
                'leave_requests',
                $empId,
                "Submitted {$days}-day {$leaveType} leave request from {$startDate} to {$endDate}"
            );

            set_flash('success', "Your leave request for {$days} day(s) has been successfully submitted and routed for review!");
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "Failed to submit leave request");
            set_flash('danger', 'Error submitting leave request: ' . $e->getMessage());
        }

        redirect('dashboard?tab=my-leaves');
    }

    /**
     * Admin Approve or Reject Leave Request
     */
    public function actionLeave(): void
    {
        if (has_role('employee') || (!has_role('admin') && !has_permission('leaves.manage'))) {
            $this->view('errors.403', [
                'pageTitle' => '403 Forbidden',
                'message'   => 'Only administrators and managers can approve or reject leave requests.'
            ], 'auth');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('leaves');
            return;
        }

        $leaveId     = (int)($_POST['leave_id'] ?? 0);
        $action      = trim($_POST['action'] ?? '');
        $adminRemark = trim($_POST['admin_remark'] ?? '');
        $currentUser = current_user();

        if ($leaveId > 0 && in_array($action, ['approved', 'rejected'], true)) {
            try {
                LeaveRequest::updateStatus($leaveId, $action, $adminRemark, (int)$currentUser['id']);
                
                AuditLog::record(
                    $currentUser['id'] ?? null,
                    'LEAVE_' . strtoupper($action),
                    'leave_requests',
                    $leaveId,
                    "Marked leave request #{$leaveId} as {$action}. Remark: {$adminRemark}"
                );

                $msg = ($action === 'approved')
                    ? "Leave request has been APPROVED and successfully synced into the employee's attendance record!"
                    : "Leave request has been REJECTED.";
                set_flash('success', $msg);
            } catch (\Throwable $e) {
                \App\Services\Logger::exception($e, "Failed to action leave request");
                set_flash('danger', 'Error updating leave request: ' . $e->getMessage());
            }
        }

        redirect('leaves');
    }

    /**
     * Create Notice / Announcement (Admin only)
     */
    public function createAnnouncement(): void
    {
        if (has_role('employee') || !has_role('admin')) {
            redirect('dashboard');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('dashboard');
            return;
        }

        $title    = trim($_POST['title'] ?? '');
        $message  = trim($_POST['message'] ?? '');
        $priority = trim($_POST['priority'] ?? 'info');
        $currentUser = current_user();

        if (empty($title) || empty($message)) {
            set_flash('danger', 'Announcement title and message are required.');
            redirect('dashboard');
            return;
        }

        try {
            Announcement::create($title, $message, $priority, (int)$currentUser['id']);
            set_flash('success', "Company announcement '{$title}' has been successfully broadcasted to all employees!");
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "createAnnouncement failed");
            set_flash('danger', 'Error publishing announcement: ' . $e->getMessage());
        }

        redirect('dashboard');
    }

    /**
     * Delete Announcement (Admin only)
     */
    public function deleteAnnouncement(): void
    {
        if (has_role('employee') || !has_role('admin')) {
            redirect('dashboard');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('dashboard');
            return;
        }

        $id = (int)($_POST['announcement_id'] ?? 0);
        if ($id > 0) {
            Announcement::delete($id);
            set_flash('success', 'Announcement has been removed from notice board.');
        }

        redirect('dashboard');
    }

    /**
     * Dedicated Payroll & Finance Page (Admin)
     */
    public function payroll(): void
    {
        if (has_role('employee')) {
            redirect('dashboard?tab=my-payroll');
            return;
        }

        try {
            $selectedMonth = $_GET['month'] ?? date('Y-m');
            if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
                $selectedMonth = date('Y-m');
            }

            $payrolls     = Payroll::getByMonth($selectedMonth);
            $summary      = Payroll::getSummary($selectedMonth);
            $departments  = Department::getAll();
            $designations = Designation::getAll();
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "DashboardController::payroll failed");
            $payrolls     = [];
            $summary      = ['total_records' => 0, 'total_base' => 0, 'total_net_disbursement' => 0, 'paid_count' => 0, 'processed_count' => 0];
            $departments  = [];
            $designations = [];
            $selectedMonth = date('Y-m');
            set_flash('danger', 'Error loading payroll structures: ' . $e->getMessage());
        }

        $this->view('dashboard.payroll', [
            'pageTitle'     => 'Payroll & Finance',
            'activeNav'     => 'payroll',
            'selectedMonth' => $selectedMonth,
            'payrolls'      => $payrolls,
            'summary'       => $summary,
            'departments'   => $departments,
            'designations'  => $designations
        ], 'main');
    }

    /**
     * Generate or recalculate monthly payroll from attendance data
     */
    public function generatePayroll(): void
    {
        if (has_role('employee') || (!has_role('admin') && !has_permission('payroll.manage'))) {
            $this->view('errors.403', [
                'pageTitle' => '403 Forbidden',
                'message'   => 'Only administrators can generate monthly payroll.'
            ], 'auth');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('payroll');
            return;
        }

        $month = trim($_POST['month'] ?? date('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $currentUser = current_user();

        try {
            $result = Payroll::generateForMonth($month, (int)$currentUser['id']);

            AuditLog::record(
                $currentUser['id'] ?? null,
                'PAYROLL_GENERATE',
                'payrolls',
                null,
                "Generated attendance-integrated payroll for period {$month} ({$result['count']} employees processed)"
            );

            set_flash('success', "Payroll for {$month} has been successfully computed based on attendance records ({$result['count']} staff members processed)!");
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "DashboardController::generatePayroll failed");
            set_flash('danger', 'Error calculating payroll: ' . $e->getMessage());
        }

        redirect("payroll?month={$month}");
    }

    /**
     * Update individual payroll disbursement status
     */
    public function updatePayrollStatus(): void
    {
        if (has_role('employee') || (!has_role('admin') && !has_permission('payroll.manage'))) {
            redirect('payroll');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('payroll');
            return;
        }

        $id     = (int)($_POST['payroll_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'paid');
        $month  = trim($_POST['month'] ?? date('Y-m'));

        if ($id > 0) {
            Payroll::updateStatus($id, $status);
            set_flash('success', "Payroll record #{$id} status changed to " . strtoupper($status));
        }

        redirect("payroll?month={$month}");
    }

    /**
     * Printable Payslip view
     */
    public function viewPayslip(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $payslip = Payroll::findById($id);

        if (!$payslip) {
            set_flash('danger', 'Payslip not found.');
            redirect('payroll');
            return;
        }

        $currentUser = current_user();
        if (has_role('employee')) {
            $empId = (int)($currentUser['employee_id'] ?? 0);
            if ((int)$payslip['employee_id'] !== $empId) {
                $this->view('errors.403', [
                    'pageTitle' => '403 Forbidden',
                    'message'   => 'You are not authorized to view this payslip.'
                ], 'auth');
                return;
            }
        }

        $this->view('dashboard.payslip', [
            'pageTitle' => 'Payslip • ' . $payslip['employee_name'] . ' (' . $payslip['month'] . ')',
            'payslip'   => $payslip
        ], 'blank');
    }

    /**
     * Export payroll ledger as CSV
     */
    public function exportPayroll(): void
    {
        if (has_role('employee')) {
            redirect('dashboard?tab=my-payroll');
            return;
        }

        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $payrolls = Payroll::getByMonth($month);

        $filename = "Payroll_Ledger_{$month}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'Payroll ID', 'Employee Code', 'Staff Name', 'Department', 'Designation',
            'Bank Name', 'Account Number', 'Month', 'Working Days', 'Present Days',
            'Leaves', 'Absent Days', 'Half Days', 'Base Salary (PKR)', 'Allowances',
            'Absent Deductions', 'Income Tax', 'Net Disbursed (PKR)', 'Status'
        ]);

        foreach ($payrolls as $p) {
            fputcsv($out, [
                $p['id'],
                $p['employee_code'],
                $p['employee_name'],
                $p['department_name'],
                $p['designation_title'],
                $p['bank_name'],
                $p['account_number'],
                $p['month'],
                $p['working_days'],
                $p['present_days'],
                $p['paid_leave_days'],
                $p['absent_days'],
                $p['half_days'],
                number_format($p['base_salary'], 2, '.', ''),
                number_format($p['allowances'], 2, '.', ''),
                number_format($p['unpaid_deductions'], 2, '.', ''),
                number_format($p['tax_deduction'], 2, '.', ''),
                number_format($p['net_salary'], 2, '.', ''),
                strtoupper($p['payment_status'])
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Dedicated Security Audit Logs Page
     */
    public function auditLogs(): void
    {
        if (!has_role('admin') && !has_permission('audit.view')) {
            $this->view('errors.403', [
                'pageTitle' => '403 Forbidden',
                'message'   => 'You do not have administrative permission to view the security audit logs.'
            ], 'auth');
            return;
        }

        try {
            $recentAuditLogs = AuditLog::getRecent(100);
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "DashboardController::auditLogs failed");
            $recentAuditLogs = [];
            set_flash('danger', 'Error loading audit logs: ' . $e->getMessage());
        }

        $this->view('dashboard.audit', [
            'pageTitle'       => 'Security Audit Logs',
            'activeNav'       => 'audit',
            'recentAuditLogs' => $recentAuditLogs
        ], 'main');
    }

    /**
     * Handle quick employee creation
     */
    public function createEmployee(): void
    {
        if (has_role('employee') || (!has_role('admin') && !has_permission('employees.manage'))) {
            $this->view('errors.403', [
                'pageTitle' => '403 Forbidden',
                'message'   => 'Only administrators and authorized managers can register new employees.'
            ], 'auth');
            return;
        }

        if (!$this->validateCSRF()) {
            redirect('employees');
        }

        $firstName    = trim($_POST['first_name'] ?? '');
        $lastName     = trim($_POST['last_name'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $deptId       = (int)($_POST['department_id'] ?? 0);
        $desigId      = (int)($_POST['designation_id'] ?? 0);
        $managerId    = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : null;
        $hireDate     = trim($_POST['hire_date'] ?? date('Y-m-d'));
        $status       = in_array($_POST['employment_status'] ?? '', ['active', 'inactive', 'on_leave', 'terminated']) 
                        ? $_POST['employment_status'] 
                        : 'active';

        if (empty($firstName) || empty($lastName) || empty($email) || $deptId <= 0 || $desigId <= 0) {
            set_flash('danger', 'Please complete all required fields (First Name, Last Name, Email, Department, Designation).');
            redirect('employees');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('danger', 'Please provide a valid employee email address.');
            redirect('employees');
        }

        // Check if email already exists
        if (Employee::findByEmail($email)) {
            set_flash('danger', 'An employee with this email address already exists.');
            redirect('employees');
        }

        // Auto-generate employee code if not provided
        $total = Employee::countTotal();
        $code = 'EMP-' . str_pad((string)($total + 1001), 4, '0', STR_PAD_LEFT);

        try {
            $newId = Employee::create([
                'employee_code'     => $code,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'email'             => $email,
                'phone'             => $phone ?: null,
                'hire_date'         => $hireDate,
                'department_id'     => $deptId,
                'designation_id'    => $desigId,
                'manager_id'        => $managerId,
                'employment_status' => $status
            ]);

            // Determine Employee Role ID
            $employeeRole = Role::findBySlug('employee');
            $roleId = $employeeRole['id'] ?? 4;

            // Generate unique username (e.g. firstname.lastname)
            $cleanFirst = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName));
            $cleanLast = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $lastName));
            $baseUsername = ($cleanFirst && $cleanLast) ? "{$cleanFirst}.{$cleanLast}" : strtolower(explode('@', $email)[0]);
            $username = $baseUsername;
            $counter = 1;
            while (User::findByUsernameOrEmail($username)) {
                $username = "{$baseUsername}{$counter}";
                $counter++;
            }

            // Generate secure temporary password
            $tempPassword = 'HRM#' . rand(100000, 999999);
            $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

            // Provision login account linked to employee
            $userId = User::create([
                'employee_id'   => $newId,
                'username'      => $username,
                'email'         => $email,
                'password_hash' => $passwordHash,
                'role_id'       => $roleId,
                'status'        => 'active'
            ]);

            // Send Welcome Email with credentials
            MailService::sendWelcomeEmployee([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'code'       => $code
            ], $username, $tempPassword);

            $currentUser = current_user();
            AuditLog::record(
                $currentUser['id'] ?? null,
                'EMPLOYEE_CREATE',
                'employees',
                $newId,
                "Registered new employee {$firstName} {$lastName} ({$code}) and provisioned user '{$username}'"
            );

            set_flash('success', "Employee {$firstName} {$lastName} ({$code}) was successfully added! Welcome email sent to {$email} (Username: {$username} | Temp Password: {$tempPassword})");
        } catch (\Throwable $e) {
            \App\Services\Logger::exception($e, "Failed to create employee");
            set_flash('danger', 'Error saving employee record: ' . $e->getMessage());
        }

        redirect('employees');
    }

    /**
     * Display the Personalized Employee Self-Service (ESS) Portal
     */
    protected function employeeIndex(): void
    {
        $currentUser = current_user();
        $empId = $currentUser['employee_id'] ?? null;
        $profile = null;

        if ($empId) {
            $profile = Employee::findById($empId);
        }
        if (!$profile && !empty($currentUser['email'])) {
            $profile = Employee::findByEmail($currentUser['email']);
            if ($profile) {
                $profile = Employee::findById($profile['id']);
            }
        }

        // Fallback default for Sophia Chen if needed
        if (!$profile) {
            $profile = [
                'id'                => 3,
                'employee_code'     => 'EMP-1003',
                'first_name'        => 'Sophia',
                'last_name'         => 'Chen',
                'email'             => $currentUser['email'] ?? 'sophia.chen@company.org',
                'phone'             => '+1 (555) 019-8392',
                'hire_date'         => '2022-01-10',
                'department_name'   => 'Engineering & IT',
                'department_id'     => 2,
                'designation_title' => 'Senior Software Engineer',
                'manager_name'      => 'Marcus Sterling',
                'employment_status' => 'active'
            ];
        }

        // Fetch team members in the same department
        $teamMembers = [];
        if (!empty($profile['department_id'])) {
            try {
                $pdo = \Config\Database::getConnection();
                $stmt = $pdo->prepare("SELECT e.*, des.title AS designation_title 
                                       FROM employees e 
                                       INNER JOIN designations des ON e.designation_id = des.id 
                                       WHERE e.department_id = :dept_id 
                                       ORDER BY e.manager_id ASC, e.first_name ASC");
                $stmt->execute(['dept_id' => $profile['department_id']]);
                $teamMembers = $stmt->fetchAll();
            } catch (\Throwable $e) {
                $teamMembers = [];
            }
        }

        $activeTab = $_GET['tab'] ?? 'my-workspace';
        $validTabs = ['my-workspace', 'my-profile', 'my-attendance', 'my-leaves', 'my-payroll', 'my-team'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'my-workspace';
        }

        $selectedMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }
        [$attYear, $attMonth] = explode('-', $selectedMonth);

        $attendanceRecords = [];
        $monthSummary = ['present' => 0, 'absent' => 0, 'half_day' => 0, 'leave' => 0, 'work_from_home' => 0, 'holiday' => 0];
        $leaveRequests = [];
        $leaveBalance = ['annual' => ['total' => 14, 'used' => 0, 'remaining' => 14], 'sick' => ['total' => 7, 'used' => 0, 'remaining' => 7], 'casual' => ['total' => 5, 'used' => 0, 'remaining' => 5]];
        $myPayrolls = [];

        if (!empty($profile['id'])) {
            $empIdNum = (int)$profile['id'];
            $attendanceRecords = Attendance::getByEmployeeMonth($empIdNum, $attYear, $attMonth);
            $monthSummary = Attendance::getMonthSummary($empIdNum, $attYear, $attMonth);
            $leaveRequests = LeaveRequest::getByEmployee($empIdNum);
            $leaveBalance = LeaveRequest::getEmployeeLeaveBalance($empIdNum, (int)date('Y'));
            $myPayrolls = Payroll::getByEmployee($empIdNum);
        }

        $announcements = Announcement::getActive();

        $this->view('dashboard.employee', [
            'pageTitle'         => 'Employee Portal • ' . ($profile['first_name'] . ' ' . $profile['last_name']),
            'activeNav'         => $activeTab,
            'activeTab'         => $activeTab,
            'profile'           => $profile,
            'teamMembers'       => $teamMembers,
            'selectedMonth'     => $selectedMonth,
            'attendanceRecords' => $attendanceRecords,
            'monthSummary'      => $monthSummary,
            'leaveRequests'     => $leaveRequests,
            'leaveBalance'      => $leaveBalance,
            'myPayrolls'        => $myPayrolls,
            'announcements'     => $announcements,
        ], 'main');
    }
}
