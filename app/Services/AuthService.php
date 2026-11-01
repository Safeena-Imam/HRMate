<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;

class AuthService
{
    /**
     * Attempt user authentication with credentials
     *
     * @return array{success: bool, message: string, user: ?array}
     */
    public static function attempt(string $login, string $password): array
    {
        $login = trim($login);

        if ($login === '' || $password === '') {
            return [
                'success' => false,
                'message' => 'Please provide both username/email and password.',
                'user'    => null
            ];
        }

        try {
            $user = User::findByUsernameOrEmail($login);

            if (!$user) {
                // Timing attack mitigation: still perform hash verification calculation
                password_verify($password, '$2y$10$dummyHashToPreventTimingAttacks1234567890123456789012345');
                AuditLog::record(null, 'LOGIN_FAILED', 'User', null, "Failed login attempt for nonexistent identity: {$login}");
                
                return [
                    'success' => false,
                    'message' => 'Invalid credentials provided.',
                    'user'    => null
                ];
            }

            // Verify password using native bcrypt/Argon2
            if (!password_verify($password, $user['password_hash'])) {
                AuditLog::record($user['id'], 'LOGIN_FAILED', 'User', $user['id'], "Incorrect password attempt for user: {$user['username']}");
                
                return [
                    'success' => false,
                    'message' => 'Invalid credentials provided.',
                    'user'    => null
                ];
            }

            // Check account status
            if ($user['status'] !== 'active') {
                AuditLog::record($user['id'], 'LOGIN_BLOCKED', 'User', $user['id'], "Login blocked due to inactive status ({$user['status']})");
                
                return [
                    'success' => false,
                    'message' => "Your account is currently {$user['status']}. Please contact your system administrator.",
                    'user'    => null
                ];
            }

            // Regenerate session ID to prevent Session Fixation attacks
            if (!headers_sent()) {
                session_regenerate_id(true);
            }

            // Fetch RBAC permissions for the user's role
            $permissions = Role::getPermissionsForRole((int)$user['role_id']);

            // Save clean user session payload (exclude password_hash)
            $sessionUser = [
                'id'                => (int)$user['id'],
                'username'          => $user['username'],
                'email'             => $user['email'],
                'role_id'           => (int)$user['role_id'],
                'role_name'         => $user['role_name'],
                'role_slug'         => $user['role_slug'],
                'employee_id'       => $user['employee_id'] ? (int)$user['employee_id'] : null,
                'employee_code'     => $user['employee_code'] ?? null,
                'first_name'        => $user['first_name'] ?? $user['username'],
                'last_name'         => $user['last_name'] ?? '',
                'full_name'         => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username'],
                'department_name'   => $user['department_name'] ?? 'General',
                'designation_title' => $user['designation_title'] ?? $user['role_name'],
            ];

            $_SESSION['auth_logged_in'] = true;
            $_SESSION['user_id']        = (int)$user['id'];
            $_SESSION['user']           = $sessionUser;
            $_SESSION['role_slug']      = $user['role_slug'];
            $_SESSION['permissions']    = $permissions;
            $_SESSION['last_activity']  = time();

            // Regenerate CSRF token on auth state change
            CSRF::regenerate();

            // Record login timestamp and IP in database
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            User::updateLastLogin((int)$user['id'], $ip);

            // Audit log
            AuditLog::record((int)$user['id'], 'LOGIN_SUCCESS', 'User', (int)$user['id'], "User {$user['username']} logged in successfully");

            Logger::info("User logged in successfully", ['user_id' => $user['id'], 'username' => $user['username'], 'ip' => $ip]);

            return [
                'success' => true,
                'message' => 'Login successful.',
                'user'    => $sessionUser
            ];
        } catch (\Throwable $e) {
            Logger::exception($e, "AuthService::attempt failed");
            return [
                'success' => false,
                'message' => 'A system error occurred during authentication. Please verify database connectivity.',
                'user'    => null
            ];
        }
    }

    /**
     * Terminate user session securely
     */
    public static function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['user']['username'] ?? 'User';

        if ($userId) {
            AuditLog::record((int)$userId, 'LOGOUT', 'User', (int)$userId, "User {$username} logged out");
            Logger::info("User logged out", ['user_id' => $userId, 'username' => $username]);
        }

        // Wipe session contents
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        @session_destroy();

        // Start fresh session for next request / flash message
        if (!headers_sent()) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            session_regenerate_id(true);
        }
        CSRF::regenerate();
    }
}
