<?php

/**
 * Root Redirection Handler
 * If user accesses http://localhost/HRM%20SYSTEM/ directly, forward cleanly to public/
 */

$uri = $_SERVER['REQUEST_URI'] ?? '/';
if (!str_contains($uri, '/public/')) {
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $target = ($scriptDir === '' ? '' : $scriptDir) . '/public/';
    header("Location: {$target}");
    exit;
}

require_once __DIR__ . '/public/index.php';
