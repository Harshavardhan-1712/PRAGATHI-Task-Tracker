<?php

declare(strict_types=1);

namespace App\Routes;

use App\Helpers\View;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, string $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $requestMethod, string $requestUri): void
    {
        // Strip query string
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        // Normalize trailing slash (except root)
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route path pattern into regex (e.g., /tasks/{id}/edit -> ^/tasks/(\d+)/edit$)
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match

                // Execute middleware pipeline
                foreach ($route['middleware'] as $mw) {
                    $passed = match ($mw) {
                        'auth' => AuthMiddleware::handle(),
                        'guest' => GuestMiddleware::handle(),
                        'csrf' => CsrfMiddleware::handle(),
                        default => true,
                    };

                    if (!$passed) {
                        return;
                    }
                }

                // Dispatch handler: ControllerName@methodName
                [$controllerName, $action] = explode('@', $route['handler']);
                $fqcn = "\\App\\Controllers\\{$controllerName}";

                if (!class_exists($fqcn)) {
                    http_response_code(500);
                    echo "Controller {$fqcn} not found.";
                    return;
                }

                $controller = new $fqcn();
                if (!method_exists($controller, $action)) {
                    http_response_code(500);
                    echo "Action {$action} not found in {$fqcn}.";
                    return;
                }

                // Call method with parsed parameters
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        // Route not found -> 404
        http_response_code(404);
        View::render('errors.404', [
            'pageTitle' => '404 Page Not Found — PRAGATHI',
            'activeNav' => '',
        ], 'main');
    }
}
