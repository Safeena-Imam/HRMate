<?php

/**
 * Apex HRMS - Global Application Helper Functions
 * (Loaded into root global namespace)
 */

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return \App\Services\Env::get($key, $default);
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        static $baseUrl = null;

        if ($baseUrl === null) {
            $configured = env('APP_URL', '');
            if (!empty($configured) && $configured !== 'http://localhost/HRM%20SYSTEM/public') {
                $baseUrl = rtrim($configured, '/');
            } else {
                // Auto-detect based on current request
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                
                // Get directory of index.php in public
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $scriptDir = str_replace('\\', '/', dirname($scriptName));
                
                $scriptDir = rtrim($scriptDir, '/');
                $baseUrl = "{$protocol}://{$host}{$scriptDir}";
            }
        }

        $path = ltrim($path, '/');
        return $path === '' ? $baseUrl : "{$baseUrl}/{$path}";
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return base_url($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        return base_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        $target = str_starts_with($path, 'http') ? $path : url($path);
        header("Location: {$target}");
        exit;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $type): ?string
    {
        if (isset($_SESSION['_flash'][$type])) {
            $msg = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $msg;
        }
        return null;
    }
}

if (!function_exists('has_flash')) {
    function has_flash(string $type): bool
    {
        return isset($_SESSION['_flash'][$type]);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $val = $_SESSION['_old_input'][$key] ?? $default;
        return is_string($val) ? htmlspecialchars($val, ENT_QUOTES, 'UTF-8') : $val;
    }
}

if (!function_exists('is_authenticated')) {
    function is_authenticated(): bool
    {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['auth_logged_in']);
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Services\CSRF::getToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return \App\Services\CSRF::getField();
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $permissionSlug): bool
    {
        return \App\Services\PermissionService::hasPermission($permissionSlug);
    }
}

if (!function_exists('has_role')) {
    function has_role(string|array $roles): bool
    {
        return \App\Services\PermissionService::hasRole($roles);
    }
}
