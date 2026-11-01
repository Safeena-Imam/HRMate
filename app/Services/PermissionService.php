<?php

namespace App\Services;

class PermissionService
{
    /**
     * Check if currently logged in user has a specific permission slug
     */
    public static function hasPermission(string $permissionSlug): bool
    {
        if (empty($_SESSION['auth_logged_in']) || empty($_SESSION['user'])) {
            return false;
        }

        // Administrator has blanket permission for all modules
        if (($_SESSION['role_slug'] ?? '') === 'admin') {
            return true;
        }

        $permissions = $_SESSION['permissions'] ?? [];
        return in_array($permissionSlug, $permissions, true);
    }

    /**
     * Check if currently logged in user has a specific role or one of multiple roles
     */
    public static function hasRole(string|array $roles): bool
    {
        if (empty($_SESSION['auth_logged_in']) || empty($_SESSION['role_slug'])) {
            return false;
        }

        $currentRole = $_SESSION['role_slug'];

        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }

        return $currentRole === $roles;
    }
}
