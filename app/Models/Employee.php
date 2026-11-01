<?php

namespace App\Models;

use Config\Database;
use PDO;

class Employee
{
    public static function countTotal(): int
    {
        $pdo = Database::getConnection();
        return (int)$pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    }

    public static function countByStatus(string $status): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE employment_status = :status");
        $stmt->execute(['status' => $status]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get aggregate breakdown of employee counts
     */
    public static function getStatusBreakdown(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT employment_status, COUNT(*) as count 
                FROM employees 
                GROUP BY employment_status";
        $results = $pdo->query($sql)->fetchAll();

        $metrics = [
            'total'      => 0,
            'active'     => 0,
            'inactive'   => 0,
            'on_leave'   => 0,
            'terminated' => 0
        ];

        foreach ($results as $row) {
            $status = $row['employment_status'];
            $count = (int)$row['count'];
            $metrics[$status] = $count;
            $metrics['total'] += $count;
        }

        return $metrics;
    }

    /**
     * Get all employees with joined department, designation and manager names
     */
    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, 
                       d.name AS department_name, 
                       des.title AS designation_title,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_name
                FROM employees e
                INNER JOIN departments d ON e.department_id = d.id
                INNER JOIN designations des ON e.designation_id = des.id
                LEFT JOIN employees m ON e.manager_id = m.id
                ORDER BY e.id ASC";

        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Get recent employees with joined department, designation and manager names
     */
    public static function getRecent(int $limit = 6): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, 
                       d.name AS department_name, 
                       des.title AS designation_title,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_name
                FROM employees e
                INNER JOIN departments d ON e.department_id = d.id
                INNER JOIN designations des ON e.designation_id = des.id
                LEFT JOIN employees m ON e.manager_id = m.id
                ORDER BY e.created_at DESC, e.id DESC
                LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, 
                       d.name AS department_name, 
                       des.title AS designation_title,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_name
                FROM employees e
                INNER JOIN departments d ON e.department_id = d.id
                INNER JOIN designations des ON e.designation_id = des.id
                LEFT JOIN employees m ON e.manager_id = m.id
                WHERE e.id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO employees 
                (employee_code, first_name, last_name, email, phone, hire_date, department_id, designation_id, manager_id, employment_status)
                VALUES 
                (:employee_code, :first_name, :last_name, :email, :phone, :hire_date, :department_id, :designation_id, :manager_id, :employment_status)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'employee_code'     => $data['employee_code'],
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'hire_date'         => $data['hire_date'],
            'department_id'     => $data['department_id'],
            'designation_id'    => $data['designation_id'],
            'manager_id'        => $data['manager_id'] ?? null,
            'employment_status' => $data['employment_status'] ?? 'active'
        ]);

        return (int)$pdo->lastInsertId();
    }
}
