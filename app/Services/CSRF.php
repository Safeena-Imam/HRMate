<?php

namespace App\Services;

class CSRF
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Get active token or generate a new cryptographically secure token.
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Generate HTML hidden input tag for forms.
     */
    public static function getField(): string
    {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    /**
     * Validate incoming token using timing-safe comparison.
     */
    public static function verify(?string $providedToken): bool
    {
        if (empty($providedToken)) {
            return false;
        }

        $sessionToken = self::getToken();
        return hash_equals($sessionToken, $providedToken);
    }

    /**
     * Regenerate token after sensitive actions (e.g., login, password change)
     */
    public static function regenerate(): string
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        return $_SESSION[self::SESSION_KEY];
    }
}
