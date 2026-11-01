<?php

/**
 * Apex HRMS - Public Web Entry Point (Front Controller)
 */

// 1. Initialize bootstrap
require_once dirname(__DIR__) . '/app/bootstrap.php';

// 2. Load web route definitions
require_once dirname(__DIR__) . '/routes/web.php';

// 3. Dispatch the incoming HTTP request
\App\Services\Router::dispatch();
