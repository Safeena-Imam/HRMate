<?php

namespace App\Models;

use Config\Database;
use PDO;

class Payroll
{
    /**
     * Generate or recalculate payroll for all active employees for a given month ('YYYY-MM')
     */
    public static function generateForMonth(string $month, int $createdBy): array
    {
        [$year, $m] = explode('-', $month);
        $daysInMonth = (int)cal_days_in_month(CAL_GREGORIAN, (int)$m, (int)$year);

        // Count standard working days (Mon-Fri) in this month
        $workingDays = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $ts = strtotime(sprintf('%s-%02d', $month, $d));
            $dayName = date('l', $ts);
            if ($dayName !== 'Saturday' && $dayName !== 'Sunday') {
                $workingDays++;
            }
        }
        $workingDays = max(20, $workingDays);

        $pdo = Database::getConnection();
        $employees = Employee::getAll();

        $generatedCount = 0;

        foreach ($employees as $emp) {
            if ($emp['employment_status'] !== 'active') {
                continue;
            }

            $empId      = (int)$emp['id'];
            $baseSalary = (float)($emp['base_salary'] ?? 55000.00);

            // Fetch actual attendance summary for this month
            $attRecords = Attendance::getByEmployeeMonth($empId, $year, $m);

            $presentDays  = 0;
            $leaveDays    = 0;
            $halfDays     = 0;
            $absentDays   = 0;
            $wfhDays      = 0;
            $holidayDays  = 0;

            foreach ($attRecords as $record) {
                $st = $record['status'] ?? '';
                if ($st === 'present') {
                    $presentDays++;
                } elseif ($st === 'leave') {
                    $leaveDays++;
                } elseif ($st === 'half_day') {
                    $halfDays++;
                } elseif ($st === 'absent') {
                    $absentDays++;
                } elseif ($st === 'work_from_home') {
                    $wfhDays++;
                } elseif ($st === 'holiday') {
                    $holidayDays++;
                }
            }

            // Calculation
            $perDayRate = $baseSalary / $workingDays;
            $unpaidDeductions = round(($absentDays * $perDayRate) + ($halfDays * 0.5 * $perDayRate), 2);
            $allowances = round($baseSalary * 0.10, 2); // 10% standard utility & medical allowance
            $bonus = 0.00;
            $taxDeduction = round(max(0, ($baseSalary + $allowances - 50000) * 0.05), 2); // 5% above 50k
            $netSalary = round(max(0, $baseSalary + $allowances + $bonus - $unpaidDeductions - $taxDeduction), 2);

            // Upsert in database
            $stmt = $pdo->prepare(
                "INSERT INTO payrolls (
                    employee_id, month, base_salary, working_days, present_days, paid_leave_days, 
                    absent_days, half_days, unpaid_deductions, allowances, bonus, tax_deduction, 
                    net_salary, payment_status, created_by, created_at, updated_at
                ) VALUES (
                    :employee_id, :month, :base_salary, :working_days, :present_days, :paid_leave_days, 
                    :absent_days, :half_days, :unpaid_deductions, :allowances, :bonus, :tax_deduction, 
                    :net_salary, 'processed', :created_by, NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                    base_salary = VALUES(base_salary),
                    working_days = VALUES(working_days),
                    present_days = VALUES(present_days),
                    paid_leave_days = VALUES(paid_leave_days),
                    absent_days = VALUES(absent_days),
                    half_days = VALUES(half_days),
                    unpaid_deductions = VALUES(unpaid_deductions),
                    allowances = VALUES(allowances),
                    bonus = VALUES(bonus),
                    tax_deduction = VALUES(tax_deduction),
                    net_salary = VALUES(net_salary),
                    updated_at = NOW()"
            );

            $stmt->execute([
                'employee_id'       => $empId,
                'month'             => $month,
                'base_salary'       => $baseSalary,
                'working_days'      => $workingDays,
                'present_days'      => $presentDays,
                'paid_leave_days'   => $leaveDays,
                'absent_days'       => $absentDays,
                'half_days'         => $halfDays,
                'unpaid_deductions' => $unpaidDeductions,
                'allowances'        => $allowances,
                'bonus'             => $bonus,
                'tax_deduction'     => $taxDeduction,
                'net_salary'        => $netSalary,
                'created_by'        => $createdBy,
            ]);

            $generatedCount++;
        }

        return ['count' => $generatedCount, 'month' => $month];
    }

    /**
     * Get all payroll records for a specific month
     */
    public static function getByMonth(string $month): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT p.*,
                    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                    e.employee_code,
                    e.email AS employee_email,
                    e.bank_name,
                    e.account_number,
                    d.name AS department_name,
                    des.title AS designation_title
             FROM payrolls p
             INNER JOIN employees e ON e.id = p.employee_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations des ON des.id = e.designation_id
             WHERE p.month = :month
             ORDER BY e.first_name ASC"
        );
        $stmt->execute(['month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all payslips for an employee
     */
    public static function getByEmployee(int $empId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT p.*,
                    d.name AS department_name,
                    des.title AS designation_title
             FROM payrolls p
             INNER JOIN employees e ON e.id = p.employee_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations des ON des.id = e.designation_id
             WHERE p.employee_id = :emp_id
             ORDER BY p.month DESC"
        );
        $stmt->execute(['emp_id' => $empId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find single payslip by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT p.*,
                    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                    e.employee_code,
                    e.email AS employee_email,
                    e.phone AS employee_phone,
                    e.hire_date,
                    e.bank_name,
                    e.account_number,
                    d.name AS department_name,
                    des.title AS designation_title
             FROM payrolls p
             INNER JOIN employees e ON e.id = p.employee_id
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations des ON des.id = e.designation_id
             WHERE p.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Update payment status ('draft', 'processed', 'paid')
     */
    public static function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['draft', 'processed', 'paid'], true)) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE payrolls 
             SET payment_status = :status,
                 paid_at = " . ($status === 'paid' ? "NOW()" : "NULL") . ",
                 updated_at = NOW()
             WHERE id = :id"
        );
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * Get aggregate financial totals for a month
     */
    public static function getSummary(string $month): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT 
                COUNT(*) AS total_records,
                COALESCE(SUM(base_salary), 0) AS total_base,
                COALESCE(SUM(allowances), 0) AS total_allowances,
                COALESCE(SUM(unpaid_deductions), 0) AS total_deductions,
                COALESCE(SUM(tax_deduction), 0) AS total_tax,
                COALESCE(SUM(net_salary), 0) AS total_net_disbursement,
                COALESCE(SUM(payment_status = 'paid'), 0) AS paid_count,
                COALESCE(SUM(payment_status = 'processed'), 0) AS processed_count
             FROM payrolls
             WHERE month = :month"
        );
        $stmt->execute(['month' => $month]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: [
            'total_records'          => 0,
            'total_base'             => 0,
            'total_allowances'       => 0,
            'total_deductions'       => 0,
            'total_tax'              => 0,
            'total_net_disbursement' => 0,
            'paid_count'             => 0,
            'processed_count'        => 0,
        ];
    }
}

