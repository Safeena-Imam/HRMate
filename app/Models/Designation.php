<?php

namespace App\Models;

use Config\Database;
use PDO;

class Designation
{
    public static function countTotal(): int
    {
        $pdo = Database::getConnection();
        return (int)$pdo->query("SELECT COUNT(*) FROM designations WHERE status = 'active'")->fetchColumn();
    }

    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT des.*, d.name AS department_name, COUNT(e.id) AS employee_count
                FROM designations des
                INNER JOIN departments d ON des.department_id = d.id
                LEFT JOIN employees e ON des.id = e.designation_id AND e.employment_status = 'active'
                GROUP BY des.id
                ORDER BY d.name ASC, des.title ASC";
        return $pdo->query($sql)->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT des.*, d.name AS department_name 
                               FROM designations des 
                               INNER JOIN departments d ON des.department_id = d.id 
                               WHERE des.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getByDepartment(int $deptId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM designations WHERE department_id = :dept_id AND status = 'active' ORDER BY title ASC");
        $stmt->execute(['dept_id' => $deptId]);
        return $stmt->fetchAll();
    }
}
