<?php

namespace App\Models;

use Config\Database;
use PDO;

class AuditLog
{
    /**
     * Record an audit event into the database
     */
    public static function record(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, ?string $description = null): void
    {
        try {
            $pdo = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI', 0, 255);

            $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent)
                    VALUES (:user_id, :action, :entity_type, :entity_id, :description, :ip, :ua)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id'     => $userId,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'description' => $description,
                'ip'          => $ip,
                'ua'          => $ua
            ]);
        } catch (\Throwable $e) {
            // Silently log failure to app.log so audit failure never halts main transactions
            \App\Services\Logger::error("Audit log record failure: " . $e->getMessage());
        }
    }

    /**
     * Retrieve recent audit events with username and role
     */
    public static function getRecent(int $limit = 8): array
    {
        try {
            $pdo = Database::getConnection();
            $sql = "SELECT al.*, 
                           u.username, 
                           r.name AS role_name,
                           CONCAT(e.first_name, ' ', e.last_name) AS full_name
                    FROM audit_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    LEFT JOIN roles r ON u.role_id = r.id
                    LEFT JOIN employees e ON u.employee_id = e.id
                    ORDER BY al.created_at DESC, al.id DESC
                    LIMIT :limit";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
