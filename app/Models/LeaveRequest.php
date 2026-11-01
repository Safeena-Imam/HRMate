<?php

namespace App\Models;

use Config\Database;
use PDO;

class LeaveRequest
{
    /**
     * Submit a new leave request
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO leave_requests (
                employee_id, leave_type, start_date, end_date, days, reason, status, manager_id, created_at, updated_at
            ) VALUES (
                :employee_id, :leave_type, :start_date, :end_date, :days, :reason, 'pending', :manager_id, NOW(), NOW()
            )"
        );

        $stmt->execute([
            'employee_id' => (int)$data['employee_id'],
            'leave_type'  => $data['leave_type'] ?? 'annual',
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'days'        => (int)($data['days'] ?? 1),
            'reason'      => trim($data['reason'] ?? ''),
            'manager_id'  => !empty($data['manager_id']) ? (int)$data['manager_id'] : null,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Get all leave requests for a specific employee
     */
    public static function getByEmployee(int $empId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT lr.*, 
                    CONCAT(m.first_name, ' ', m.last_name) AS manager_name,
                    u.username AS actioned_by_username
             FROM leave_requests lr
             LEFT JOIN employees m ON m.id = lr.manager_id
             LEFT JOIN users u ON u.id = lr.actioned_by
             WHERE lr.employee_id = :emp_id
             ORDER BY lr.created_at DESC"
        );
        $stmt->execute(['emp_id' => $empId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all leave requests for admin review
     */
    public static function getAll(?string $status = null): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT lr.*, 
                       CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                       e.employee_code,
                       d.name AS department_name,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_name
                FROM leave_requests lr
                INNER JOIN employees e ON e.id = lr.employee_id
                LEFT JOIN departments d ON d.id = e.department_id
                LEFT JOIN employees m ON m.id = lr.manager_id";

        if ($status && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $sql .= " WHERE lr.status = :status";
        }
        $sql .= " ORDER BY (lr.status = 'pending') DESC, lr.created_at DESC";

        $stmt = $pdo->prepare($sql);
        if ($status && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $stmt->execute(['status' => $status]);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find single request by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT lr.*, 
                    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
                    e.employee_code,
                    e.email AS employee_email
             FROM leave_requests lr
             INNER JOIN employees e ON e.id = lr.employee_id
             WHERE lr.id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Update leave request status (Approve or Reject).
     * If approved, syncs automatically to attendance table!
     */
    public static function updateStatus(int $id, string $status, ?string $adminRemark, int $actionedBy): bool
    {
        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            return false;
        }

        $leave = self::findById($id);
        if (!$leave) {
            return false;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE leave_requests 
             SET status = :status,
                 admin_remark = :remark,
                 actioned_by = :actioned_by,
                 actioned_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            'status'      => $status,
            'remark'      => $adminRemark,
            'actioned_by' => $actionedBy,
            'id'          => $id,
        ]);

        // If approved, automatically sync into attendance calendar!
        if ($status === 'approved') {
            $startDate = strtotime($leave['start_date']);
            $endDate   = strtotime($leave['end_date']);

            for ($curr = $startDate; $curr <= $endDate; $curr = strtotime('+1 day', $curr)) {
                $dayName = date('l', $curr);
                // Skip weekends
                if ($dayName === 'Saturday' || $dayName === 'Sunday') {
                    continue;
                }
                $dateStr = date('Y-m-d', $curr);
                $noteText = 'Approved ' . ucfirst($leave['leave_type']) . ' Leave: ' . substr($leave['reason'], 0, 50);
                Attendance::saveRecord((int)$leave['employee_id'], $dateStr, 'leave', $noteText, $actionedBy);
            }
        }

        return true;
    }

    /**
     * Calculate leave balances for an employee for a given year
     */
    public static function getEmployeeLeaveBalance(int $empId, int $year): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT leave_type, SUM(days) AS total_days
             FROM leave_requests
             WHERE employee_id = :emp_id
               AND status = 'approved'
               AND YEAR(start_date) = :year
             GROUP BY leave_type"
        );
        $stmt->execute(['emp_id' => $empId, 'year' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $quotas = [
            'annual' => ['total' => 14, 'used' => 0],
            'sick'   => ['total' => 7,  'used' => 0],
            'casual' => ['total' => 5,  'used' => 0],
        ];

        foreach ($rows as $r) {
            $t = $r['leave_type'];
            if (isset($quotas[$t])) {
                $quotas[$t]['used'] = (int)$r['total_days'];
            }
        }

        foreach ($quotas as $key => $q) {
            $quotas[$key]['remaining'] = max(0, $q['total'] - $q['used']);
        }

        return $quotas;
    }

    /**
     * Count pending requests for notification badge
     */
    public static function countPending(): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM leave_requests WHERE status = 'pending'");
        return (int)$stmt->fetchColumn();
    }
}

