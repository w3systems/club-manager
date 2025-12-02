<?php

namespace App\Core;

use App\Core\Container;
use App\Core\Session;
use App\Core\Auth;
use App\Core\View;
use App\Core\Validator;

/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
abstract class Controller
{
    protected Container $container;
    protected Session $session;
    protected Auth $auth;
    protected View $view;
    protected Validator $validator;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->session = $this->container->get('session');
        $this->auth = $this->container->get('auth');
        $this->view = $this->container->get('view');
        $this->validator = $this->container->get('validator');
    }
    
    /**
     * Render a view
     */
    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        // Add common data available to all views
        $data['auth'] = $this->auth;
        $data['session'] = $this->session;
        $data['currentUrl'] = $_SERVER['REQUEST_URI'];
        $data['csrfToken'] = $this->generateCsrfToken();
        
        // Add flash messages
        $data['flashMessages'] = $this->session->getAllFlash();

        // **NEW**: Make logo path globally available to all admin views
        if ($layout === 'admin') {
            $data['appLogoPath'] = $_ENV['APP_LOGO_PATH'] ?? null;
        }
        
        $this->view->render($view, $data, $layout);
    }


    
    /**
     * Render JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        if (!headers_sent()) {
            header("Location: $url", true, $statusCode);
            exit;
        }
    }
    
    /**
     * Redirect back to previous page
     */
    protected function back(): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referrer);
    }
    
    /**
     * Validate request data
     */
    protected function validate(array $data, array $rules, array $messages = []): array
    {
        if (!$this->validator->validate($data, $rules, $messages)) {
            $this->session->flash('errors', $this->validator->errors());
            $this->session->flashInput($data);
            $this->back();
        }
        
        return $data;
    }
    
    /**
     * Get request input
     */
    protected function input(string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $input;
        }
        
        return $input[$key] ?? $default;
    }
    
    /**
     * Check if request has input
     */
    protected function has(string $key): bool
    {
        $input = array_merge($_GET, $_POST);
        return isset($input[$key]);
    }
    
    /**
     * Get all request input
     */
    protected function all(): array
    {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * Get only specified input fields
     */
    protected function only(array $keys): array
    {
        $input = $this->all();
        return array_intersect_key($input, array_flip($keys));
    }
    
    /**
     * Get input except specified fields
     */
    protected function except(array $keys): array
    {
        $input = $this->all();
        return array_diff_key($input, array_flip($keys));
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request is GET
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Generate and return CSRF token
     */
    protected function generateCsrfToken(): string
    {
        if (!$this->session->has('_csrf_token')) {
            $token = bin2hex(random_bytes(32));
            $this->session->set('_csrf_token', $token);
        }
        
        return $this->session->get('_csrf_token');
    }
    
    /**
     * Verify CSRF token
     */
    protected function verifyCsrfToken(): bool
    {
        $token = $this->input('_token');
        $sessionToken = $this->session->get('_csrf_token');
        
        return $token && $sessionToken && hash_equals($sessionToken, $token);
    }
    
    /**
     * Require CSRF token verification
     */
    protected function requireCsrfToken(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->session->error('Invalid security token. Please try again.');
            $this->back();
        }
    }
    
    /**
     * Set success message and redirect
     */
    protected function successAndRedirect(string $message, string $url): void
    {
        $this->session->success($message);
        $this->redirect($url);
    }
    
    /**
     * Set error message and redirect
     */
    protected function errorAndRedirect(string $message, string $url): void
    {
        $this->session->error($message);
        $this->redirect($url);
    }
    
    /**
     * Set success message and redirect back
     */
    protected function successAndBack(string $message): void
    {
        $this->session->success($message);
        $this->back();
    }
    
    /**
     * Set error message and redirect back
     */
    protected function errorAndBack(string $message): void
    {
        $this->session->error($message);
        $this->back();
    }
    
    /**
     * Abort with HTTP status code
     */
    protected function abort(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);
        
        switch ($statusCode) {
            case 403:
                $this->render('errors/403', ['message' => $message]);
                break;
            case 404:
                $this->render('errors/404', ['message' => $message]);
                break;
            case 500:
                $this->render('errors/500', ['message' => $message]);
                break;
            default:
                echo $message ?: "HTTP Error $statusCode";
        }
        
        exit;
    }
    
    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->auth->check()) {
            $this->auth->redirectToLogin();
        }
    }
    
    /**
     * Require admin authentication
     */
    protected function requireAdmin(): void
    {
        if (!$this->auth->isAdmin()) {
            $this->abort(403, 'Access denied. Admin privileges required.');
        }
    }
    
    /**
     * Require member authentication
     */
    protected function requireMember(): void
    {
        if (!$this->auth->isMember()) {
            $this->abort(403, 'Access denied. Member access required.');
        }
    }
    
    /**
     * Require specific permission
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->auth->hasPermission($permission)) {
            $this->abort(403, 'Access denied. Insufficient permissions.');
        }
    }
    
    /**
     * Upload file
     */
    protected function uploadFile(array $file, string $directory = 'uploads'): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $uploadDir = STORAGE_PATH . '/' . trim($directory, '/') . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
        }
        
        return null;
    }
    
    /**
     * Paginate results
     */
    protected function paginate(array $items, int $perPage = 15): array
    {
        $page = max(1, (int)($this->input('page', 1)));
        $total = count($items);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        return [
            'items' => array_slice($items, $offset, $perPage),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages,
                'prev_page' => $page > 1 ? $page - 1 : null,
                'next_page' => $page < $totalPages ? $page + 1 : null,
            ]
        ];
    }
}