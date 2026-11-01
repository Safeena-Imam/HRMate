<?php

namespace App\Models;

use Config\Database;
use PDO;

class Announcement
{
    /**
     * Get active announcements for employee and admin dashboards
     */
    public static function getActive(int $limit = 5): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT a.*, u.username AS posted_by_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.posted_by
             WHERE a.is_active = 1
             ORDER BY 
                 (a.priority = 'urgent') DESC,
                 (a.priority = 'warning') DESC,
                 a.created_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all announcements for admin management
     */
    public static function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT a.*, u.username AS posted_by_name
             FROM announcements a
             LEFT JOIN users u ON u.id = a.posted_by
             ORDER BY a.created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new announcement
     */
    public static function create(string $title, string $message, string $priority, int $postedBy): int
    {
        $allowed = ['info', 'warning', 'urgent'];
        if (!in_array($priority, $allowed, true)) {
            $priority = 'info';
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO announcements (title, message, priority, is_active, posted_by, created_at, updated_at)
             VALUES (:title, :message, :priority, 1, :posted_by, NOW(), NOW())"
        );
        $stmt->execute([
            'title'     => $title,
            'message'   => $message,
            'priority'  => $priority,
            'posted_by' => $postedBy,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Delete an announcement
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

