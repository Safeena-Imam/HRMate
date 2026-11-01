<?php

namespace App\Middleware;

class GuestMiddleware
{
    public static function handle(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['auth_logged_in']) && !empty($_SESSION['user_id'])) {
            redirect('dashboard');
            return false;
        }

        return true;
    }
}
