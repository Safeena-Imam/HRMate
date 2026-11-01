<?php

namespace App\Middleware;

use App\Services\PermissionService;

class RoleMiddleware
{
    /**
     * Check if user has required permission slug
     */
    public static function requirePermission(string $permission): bool
    {
        AuthMiddleware::handle();

        if (!PermissionService::hasPermission($permission)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';
            exit;
        }

        return true;
    }

    /**
     * Check if user has required role
     */
    public static function requireRole(string|array $roles): bool
    {
        AuthMiddleware::handle();

        if (!PermissionService::hasRole($roles)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';
            exit;
        }

        return true;
    }
}
