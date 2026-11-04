<?php

namespace App\Models;

use Config\Database;
use PDO;

class Attendance
{
    /**
     * Get attendance records for a specific employee and month.
     * Returns associative array: ['YYYY-MM-DD' => ['status' => ..., 'notes' => ...]]
     */
    public static function getByEmployeeMonth(int $empId, string $year, string $month): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT date, status, notes
             FROM attendance
             WHERE employee_id = :emp_id
               AND YEAR(date)  = :year
               AND MONTH(date) = :month
             ORDER BY date ASC"
        );
        $stmt->execute([
            'emp_id' => $empId,
            'year'   => $year,
            'month'  => $month,
        ]);
        $rows   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['date']] = [
                'status' => $row['status'],
                'notes'  => $row['notes'] ?? '',
            ];
        }
        return $result;
    }

    /**
     * Insert or update a single attendance record (upsert via ON DUPLICATE KEY).
     */
    public static function saveRecord(
        int    $empId,
        string $date,
        string $status,
        string $notes,
        int    $markedBy
    ): void {
        $allowedStatuses = ['present', 'absent', 'half_day', 'leave', 'work_from_home', 'holiday'];
        if (!in_array($status, $allowedStatuses, true)) {
            return; // silently skip invalid statuses
        }

        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO attendance (employee_id, date, status, notes, marked_by, created_at, updated_at)
             VALUES (:emp_id, :date, :status, :notes, :marked_by, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                 status     = VALUES(status),
                 notes      = VALUES(notes),
                 marked_by  = VALUES(marked_by),
                 updated_at = NOW()"
        );
        $stmt->execute([
            'emp_id'    => $empId,
            'date'      => $date,
            'status'    => $status,
            'notes'     => $notes,
            'marked_by' => $markedBy,
        ]);
    }

    /**
     * Get summary counts for a given employee + month.
     * Returns ['present' => N, 'absent' => N, 'half_day' => N, 'leave' => N, 'work_from_home' => N, 'holiday' => N]
     */
    public static function getMonthSummary(int $empId, string $year, string $month): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT status, COUNT(*) AS cnt
             FROM attendance
             WHERE employee_id = :emp_id
               AND YEAR(date)  = :year
               AND MONTH(date) = :month
             GROUP BY status"
        );
        $stmt->execute([
            'emp_id' => $empId,
            'year'   => $year,
            'month'  => $month,
        ]);
        $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary = [
            'present'        => 0,
            'absent'         => 0,
            'half_day'       => 0,
            'leave'          => 0,
            'work_from_home' => 0,
            'holiday'        => 0,
        ];
        foreach ($rows as $row) {
            if (isset($summary[$row['status']])) {
                $summary[$row['status']] = (int)$row['cnt'];
            }
        }
        return $summary;
    }

    /**
     * Get all employees with their attendance summary for a given month.
     * Used for admin overview table.
     */
    public static function getAllEmployeesForMonth(string $year, string $month): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT
                e.id,
                CONCAT(e.first_name, ' ', e.last_name) AS full_name,
                e.employee_code,
                d.name AS department_name,
                COALESCE(SUM(a.status = 'present'),        0) AS present_days,
                COALESCE(SUM(a.status = 'absent'),         0) AS absent_days,
                COALESCE(SUM(a.status = 'half_day'),       0) AS half_days,
                COALESCE(SUM(a.status = 'leave'),          0) AS leave_days,
                COALESCE(SUM(a.status = 'work_from_home'), 0) AS wfh_days,
                COALESCE(SUM(a.status = 'holiday'),        0) AS holiday_days
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN attendance  a ON a.employee_id = e.id
                 AND YEAR(a.date)  = :year
                 AND MONTH(a.date) = :month
             WHERE e.employment_status = 'active'
             GROUP BY e.id, e.first_name, e.last_name, e.employee_code, d.name
             ORDER BY e.first_name ASC"
        );
        $stmt->execute(['year' => $year, 'month' => $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get attendance records for all active employees for a single date.
     * Returns array of employees with their current marked attendance status and notes.
     */
    public static function getByDate(string $date): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT e.id, 
                    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
                    e.first_name,
                    e.last_name,
                    e.employee_code,
                    d.name AS department_name,
                    des.title AS designation_title,
                    a.status,
                    a.notes
             FROM employees e
             LEFT JOIN departments d ON d.id = e.department_id
             LEFT JOIN designations des ON des.id = e.designation_id
             LEFT JOIN attendance a ON a.employee_id = e.id AND a.date = :date
             WHERE e.employment_status = 'active'
             ORDER BY e.first_name ASC"
        );
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save / update daily attendance in batch for all employees.
     */
    public static function saveDailyBatch(string $date, array $attendanceData, array $notesData, int $markedBy): int
    {
        $count = 0;
        foreach ($attendanceData as $empId => $status) {
            $empId = (int)$empId;
            if ($empId <= 0 || empty($status)) {
                continue;
            }
            $note = trim($notesData[$empId] ?? '');
            self::saveRecord($empId, $date, $status, $note, $markedBy);
            $count++;
        }
        return $count;
    }
}

