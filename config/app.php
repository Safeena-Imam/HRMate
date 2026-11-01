<?php

/**
 * Apex HRMS - Application & Session Configuration
 */

// Ensure session is started with hardened security parameters
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = (int)env('SESSION_LIFETIME', 7200);
    $secure = filter_var(env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOLEAN);
    $httponly = filter_var(env('SESSION_HTTPONLY', true), FILTER_VALIDATE_BOOLEAN);
    $samesite = (string)env('SESSION_SAMESITE', 'Lax');

    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);

    // Use strict session options
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_start();
}

// Timezone setup
$timezone = env('APP_TIMEZONE', 'UTC');
date_default_timezone_set($timezone);

return [
    'name'     => env('APP_NAME', 'HRMate'),
    'env'      => env('APP_ENV', 'development'),
    'debug'    => filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN),
    'url'      => env('APP_URL', 'http://localhost/HRM%20SYSTEM/public'),
    'timezone' => $timezone,
];
