<?php

namespace App\Services;

class Router
{
    private static array $routes = [];

    public static function get(string $uri, array|callable $action, array $middleware = []): void
    {
        self::addRoute('GET', $uri, $action, $middleware);
    }

    public static function post(string $uri, array|callable $action, array $middleware = []): void
    {
        self::addRoute('POST', $uri, $action, $middleware);
    }

    private static function addRoute(string $method, string $uri, array|callable $action, array $middleware): void
    {
        $uri = '/' . trim($uri, '/');
        self::$routes[] = [
            'method'     => strtoupper($method),
            'uri'        => $uri,
            'action'     => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Resolve the current normalized request path
     */
    public static function getCurrentUri(): string
    {
        // 1. Direct route parameter override fallback
        if (!empty($_GET['route'])) {
            return '/' . trim($_GET['route'], '/');
        }

        // 2. PATH_INFO fallback
        if (!empty($_SERVER['PATH_INFO'])) {
            return '/' . trim($_SERVER['PATH_INFO'], '/');
        }

        // 3. Apache/Nginx REQUEST_URI resolution
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUri = parse_url($requestUri, PHP_URL_PATH);
        $parsedUri = rawurldecode($parsedUri ?: '/');

        // Remove script directory path if hosted in subfolder (e.g. /HRM SYSTEM/public)
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $baseDir = str_replace('\\', '/', dirname($scriptName));

        if ($baseDir !== '/' && $baseDir !== '.' && str_starts_with($parsedUri, $baseDir)) {
            $parsedUri = substr($parsedUri, strlen($baseDir));
        }

        // Remove trailing index.php if present
        if (str_starts_with($parsedUri, '/index.php')) {
            $parsedUri = substr($parsedUri, 10);
        }

        $normalized = '/' . trim($parsedUri, '/');
        return $normalized === '//' ? '/' : $normalized;
    }

    /**
     * Dispatch the current request to the matched handler
     */
    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = self::getCurrentUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if ($route['uri'] === $requestUri) {
                // Execute Middlewares
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $res = call_user_func($mw);
                        if ($res === false) return;
                    } elseif (is_string($mw) && class_exists($mw) && method_exists($mw, 'handle')) {
                        $res = $mw::handle();
                        if ($res === false) return;
                    }
                }

                // Execute Route Action
                $action = $route['action'];
                if (is_callable($action)) {
                    call_user_func($action);
                    return;
                }

                if (is_array($action) && count($action) === 2) {
                    [$controllerClass, $method] = $action;
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $method)) {
                            $controller->$method();
                            return;
                        }
                    }
                }
            }
        }

        // No route matched: Render 404 view
        http_response_code(404);
        require dirname(__DIR__) . '/Views/errors/404.php';
        exit;
    }
}
