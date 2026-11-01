<?php

namespace App\Controllers;

use App\Services\CSRF;

abstract class BaseController
{
    /**
     * Render a view file wrapped within an optional layout template
     */
    protected function view(string $viewName, array $data = [], ?string $layout = 'main'): void
    {
        // Extract data variables into local scope for the view
        extract($data);

        $viewsBase = dirname(__DIR__) . '/Views/';
        $viewFile = $viewsBase . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        // Start output buffering to capture inner page content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If a layout is specified, inject $content into the layout
        if ($layout !== null) {
            $layoutFile = $viewsBase . 'layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        // Output raw content if layout not found or null
        echo $content;
    }

    /**
     * Verify incoming CSRF token on POST requests
     */
    protected function validateCSRF(): bool
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!CSRF::verify($token)) {
            set_flash('danger', 'Security validation failed (Invalid or expired CSRF token). Please try submitting the form again.');
            return false;
        }
        return true;
    }

    /**
     * Send JSON response
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
