<?php

namespace App\Models;

use Config\Database;
use PDO;

class Role
{
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM roles WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Retrieve all permission slugs assigned to a specific role ID
     */
    public static function getPermissionsForRole(int $roleId): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT p.slug FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
