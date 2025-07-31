<?php

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\MemberMiddleware;
use App\Middleware\PermissionMiddleware;
use Exception;

/**
 * Application Router
 * Handles URL routing and middleware
 */
class Router
{
    private array $routes = [];
    private array $middleware = [];
    private string $currentUri;
    private string $currentMethod;
    
    public function __construct()
    {
        $this->currentUri = $this->getCurrentUri();
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
        
        // Register middleware classes
        $this->middleware = [
            'auth' => AuthMiddleware::class,
            'admin' => AdminMiddleware::class,
            'member' => MemberMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ];
    }
    
    /**
     * Register a GET route
     */
    public function get(string $pattern, string $handler, array $options = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $options);
    }
    
    /**
     * Register a POST route
     */
    public function post(string $pattern, string $handler, array $options = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $options);
    }
    
    /**
     * Register a PUT route
     */
    public function put(string $pattern, string $handler, array $options = []): void
    {
        $this->addRoute('PUT', $pattern, $handler, $options);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete(string $pattern, string $handler, array $options = []): void
    {
        $this->addRoute('DELETE', $pattern, $handler, $options);
    }
    
    /**
     * Add a route to the routing table
     */
    private function addRoute(string $method, string $pattern, string $handler, array $options): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $options['middleware'] ?? [],
            'permission' => $options['permission'] ?? null,
        ];
    }
    
    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route)) {
                $this->handleRoute($route);
                return;
            }
        }
        
        // No route found
        $this->handleNotFound();
    }
    
    /**
     * Check if current request matches a route
     */
    private function matchRoute(array $route): bool
    {
        if ($route['method'] !== $this->currentMethod) {
            return false;
        }
        
        $pattern = '#^' . str_replace('/', '\/', $route['pattern']) . '$#';
        return preg_match($pattern, $this->currentUri);
    }
    
    /**
     * Handle a matched route
     */
    private function handleRoute(array $route): void
    {

        try {
            // Extract parameters from URL
            $pattern = '#^' . str_replace('/', '\/', $route['pattern']) . '$#';
            preg_match($pattern, $this->currentUri, $matches);
            $params = array_slice($matches, 1);
            
            // Run middleware
            if (!empty($route['middleware'])) {
                foreach ($route['middleware'] as $middlewareName) {
                    $this->runMiddleware($middlewareName, $route['permission'] ?? null);
                }
            }
            
            // Call controller method
            $this->callController($route['handler'], $params);
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Run middleware
     */
    private function runMiddleware(string $middlewareName, ?string $permission = null): void
    {
        if (!isset($this->middleware[$middlewareName])) {
            throw new Exception("Middleware '{$middlewareName}' not found");
        }
        
        $middlewareClass = $this->middleware[$middlewareName];
        $middleware = new $middlewareClass();
        
        if ($middlewareName === 'permission' && $permission) {
            $middleware->handle($permission);
        } else {
            $middleware->handle();
        }
    }
    
    /**
     * Call controller method
     */
    private function callController(string $handler, array $params): void
    {
        [$controllerName, $method] = explode('@', $handler);

        // Determine namespace based on controller location
        if (strpos($controllerName, 'Admin\\') === 0) {
            $controllerClass = 'App\\Controllers\\' . $controllerName;
        } elseif (strpos($controllerName, 'Member\\') === 0) {
            $controllerClass = 'App\\Controllers\\' . $controllerName;
        } elseif (strpos($controllerName, 'Auth\\') === 0) {
            $controllerClass = 'App\\Controllers\\' . $controllerName;
        } elseif (strpos($controllerName, 'Api\\') === 0) {
            $controllerClass = 'App\\Controllers\\' . $controllerName;
        } else {
            $controllerClass = 'App\\Controllers\\' . $controllerName;
        }
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller '{$controllerClass}' not found");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new Exception("Method '{$method}' not found in controller '{$controllerClass}'");
        }
        
        call_user_func_array([$controller, $method], $params);
    }
    
    /**
     * Get current URI
     */
    private function getCurrentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }
    
    /**
     * Handle 404 errors
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        
        $controller = new \App\Controllers\ErrorController();
        $controller->notFound();
    }
    
    /**
     * Handle application errors
     */
    private function handleError(Exception $e): void
    {
        error_log("Router Error: " . $e->getMessage());
        
        if ($_ENV['APP_ENV'] === 'production') {
            http_response_code(500);
            $controller = new \App\Controllers\ErrorController();
            $controller->serverError();
        } else {
            throw $e;
        }
    }
}