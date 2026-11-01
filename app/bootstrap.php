<?php

/**
 * Apex HRMS - Core Application Bootstrap
 * PSR-4 Autoloader, Environment Initialization, Centralized Error Handling
 */

define('APP_START_TIME', microtime(true));
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// 1. Register PSR-4 Autoloader
spl_autoload_register(function (string $class) {
    $prefixes = [
        'App\\'    => APP_PATH . '/',
        'Config\\' => CONFIG_PATH . '/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 2. Load Environment Variables & Global Helpers
\App\Services\Env::load(ROOT_PATH . '/.env');
require_once APP_PATH . '/helpers.php';

// 3. Initialize Centralized Logger
\App\Services\Logger::init(STORAGE_PATH . '/logs/app.log');

// 4. Load Application Configuration & Session Security
require_once CONFIG_PATH . '/app.php';

// 5. Load CSRF and Permission Services
require_once APP_PATH . '/Services/CSRF.php';
require_once APP_PATH . '/Services/PermissionService.php';

// 6. Centralized Error & Exception Handling
$isDebug = filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN);

error_reporting(E_ALL);
ini_set('display_errors', $isDebug ? '1' : '0');

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    \App\Services\Logger::error("PHP Notice/Warning: {$errstr}", [
        'file' => $errfile,
        'line' => $errline,
        'code' => $errno
    ]);
    return false;
});

set_exception_handler(function (\Throwable $e) use ($isDebug) {
    \App\Services\Logger::exception($e, "Uncaught Exception");

    if (php_sapi_name() === 'cli') {
        echo "\n[ERROR] " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
        exit(1);
    }

    http_response_code(500);
    $viewFile = APP_PATH . '/Views/errors/500.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "<h1>500 Internal Server Error</h1><p>An unexpected error occurred. Please check the logs.</p>";
    }
    exit;
});
