<?php

namespace App\Middleware;

use App\Services\AuthService;

class AuthMiddleware
{
    public static function handle(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['auth_logged_in']) || empty($_SESSION['user_id'])) {
            // Store current request URI for post-login redirection
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/dashboard';
            set_flash('danger', 'Please log in to access the system dashboard.');
            redirect('login');
            return false;
        }

        // Check session inactivity timeout
        $timeout = (int)env('SESSION_LIFETIME', 7200);
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            AuthService::logout();
            set_flash('warning', 'Your session has expired due to inactivity. Please log in again.');
            redirect('login');
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }
}
