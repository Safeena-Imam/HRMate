<?php

namespace App\Models;

use Config\Database;
use PDO;

class User
{
    /**
     * Find user by username or email with joined role and employee information
     */
    public static function findByUsernameOrEmail(string $login): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT u.*, 
                       r.name AS role_name, 
                       r.slug AS role_slug,
                       e.first_name, 
                       e.last_name, 
                       e.employee_code,
                       e.department_id,
                       d.name AS department_name,
                       des.title AS designation_title
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                LEFT JOIN employees e ON u.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                WHERE u.username = :login_user OR u.email = :login_email
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'login_user' => $login,
            'login_email' => $login
        ]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT u.*, 
                       r.name AS role_name, 
                       r.slug AS role_slug,
                       e.first_name, 
                       e.last_name, 
                       e.employee_code,
                       e.department_id,
                       d.name AS department_name,
                       des.title AS designation_title
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                LEFT JOIN employees e ON u.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations des ON e.designation_id = des.id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Create a new user record
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $sql = "INSERT INTO users (employee_id, username, email, password_hash, role_id, status)
                VALUES (:employee_id, :username, :email, :password_hash, :role_id, :status)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'employee_id'   => $data['employee_id'] ?? null,
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'role_id'       => $data['role_id'],
            'status'        => $data['status'] ?? 'active'
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Update last login timestamp and IP address
     */
    public static function updateLastLogin(int $userId, ?string $ip): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id");
        $stmt->execute([
            'ip' => $ip,
            'id' => $userId
        ]);
    }

    /**
     * Update user password
     */
    public static function updatePassword(int $userId, string $newPasswordHash): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            'hash' => $newPasswordHash,
            'id'   => $userId
        ]);
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Check if at least one Administrator account exists
     */
    public static function hasAnyAdmin(): bool
    {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE r.slug = 'admin' AND u.status = 'active'";
        $stmt = $pdo->query($sql);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Count total users
     */
    public static function countTotal(): int
    {
        $pdo = Database::getConnection();
        return (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
}
