<?php

namespace App\Models;

use Config\Database;
use PDO;

class Department
{
    public static function countTotal(): int
    {
        $pdo = Database::getConnection();
        return (int)$pdo->query("SELECT COUNT(*) FROM departments WHERE status = 'active'")->fetchColumn();
    }

    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT d.*, COUNT(e.id) AS employee_count
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id AND e.employment_status = 'active'
                GROUP BY d.id
                ORDER BY d.name ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO departments (code, name, description, status) VALUES (:code, :name, :description, :status)");
        $stmt->execute([
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active'
        ]);
        return (int)$pdo->lastInsertId();
    }
}
