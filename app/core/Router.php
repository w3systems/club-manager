<?php
// app/core/Router.php
namespace App\Core;

class Router
{
    protected $routes = [];
    protected $notFoundCallback;

    public function get($uri, $action, $middleware = [])
    {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post($uri, $action, $middleware = [])
    {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    protected function addRoute($method, $uri, $action, $middleware)
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    public function setNotFound($callback)
    {
        $this->notFoundCallback = $callback;
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match("#^" . $this->formatUri($route['uri']) . "$#", $uri, $matches)) {
                array_shift($matches); // Remove full match

                // Apply middleware
                if (!empty($route['middleware'])) {
                    foreach ($route['middleware'] as $key => $value) {
                        if ($key === 'auth') {
                            if (!Auth::check()) {
                                header('Location: /login'); // Not authenticated at all
                                exit();
                            }
                            if ($value === 'admin' && !Auth::isAdmin()) {
                                // Logged in, but not admin. Redirect to member dashboard.
                                $_SESSION['error_message'] = "Access denied. You are not authorized to view this page.";
                                header('Location: /');
                                exit();
                            } elseif ($value === 'member' && Auth::isAdmin()) {
                                // Logged in as admin, trying to access member area. Redirect to admin dashboard.
                                $_SESSION['error_message'] = "Access denied. Admin users cannot access member-only areas directly.";
                                header('Location: /admin');
                                exit();
                            }
                            // No specific role check (e.g., 'auth' => 'admin_permission:manage_users') here,
                            // as role-based permission checks will happen within the controller methods using Auth::checkPermission().
                        }
                    }
                }

                $this->callAction($route['action'], $matches);
                return;
            }
        }

        // If no route matches
        if ($this->notFoundCallback) {
            $this->callAction($this->notFoundCallback);
        } else {
            http_response_code(404);
            echo "404 Not Found"; // Or require a default 404 view
        }
    }

    protected function callAction($action, $params = [])
    {
        if (is_string($action)) {
            list($controller, $method) = explode('@', $action);
            $controller = "App\\Controllers\\" . $controller;
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                if (method_exists($controllerInstance, $method)) {
                    call_user_func_array([$controllerInstance, $method], $params);
                } else {
                    throw new \Exception("Method {$method} does not exist in controller {$controller}");
                }
            } else {
                throw new \Exception("Controller {$controller} not found.");
            }
        } elseif (is_callable($action)) {
            call_user_func_array($action, $params);
        }
    }

/*protected function callAction($action, $params = [])
{
    if (is_string($action)) {
        list($controller, $method) = explode('@', $action);
        $fullControllerClass = "App\\Controllers\\" . $controller;

        //echo "Attempting to load controller: " . $fullControllerClass . "<br>";
        //echo "Controller file path (should be): " . APP_PATH . "/controllers/" . $controller . ".php<br>";

        if (class_exists($fullControllerClass)) { // <-- Line 96, where the error occurs
            echo "Class " . $fullControllerClass . " EXISTS.<br>"; // THIS IS THE KEY LINE
            $controllerInstance = new $fullControllerClass();
            if (method_exists($controllerInstance, $method)) {
                echo "Method " . $method . " EXISTS in " . $fullControllerClass . ". Calling...<br>";
                call_user_func_array([$controllerInstance, $method], $params);
            } else {
                throw new \Exception("Method {$method} does not exist in controller {$fullControllerClass}");
            }
        } else {
            echo "CLASS " . $fullControllerClass . " DOES NOT EXIST.<br>"; // THIS IS THE KEY LINE
            // Check if file exists, but class within it might be wrong
            $expectedFilePath = APP_PATH . "/controllers/" . $controller . ".php";
            if (file_exists($expectedFilePath)) {
                echo "Controller file EXISTS at: " . $expectedFilePath . " but class is not found. Namespace or class name mismatch?<br>";
            } else {
                echo "Controller file DOES NOT EXIST at: " . $expectedFilePath . "<br>";
            }
            throw new \Exception("Controller {$fullControllerClass} not found."); // Original error line
        }
    } elseif (is_callable($action)) {
        call_user_func_array($action, $params);
    }
}*/


    protected function formatUri($uri)
    {
        // Convert dynamic segments like {id} to regex (\d+)
        return preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(\d+)', str_replace('/', '\/', $uri));
    }
}
