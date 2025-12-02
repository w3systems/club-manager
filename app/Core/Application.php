<?php

namespace App\Core;

use App\Core\Container;
use App\Core\Session;
use App\Core\Auth;
use Exception;

/**
 * Main Application Class
 * Handles application initialization and core services
 */
class Application
{
    private Container $container;
    private Session $session;
    private Auth $auth;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->session = new Session();
        $this->auth = new Auth();
        
        $this->registerServices();
    }
    
    /**
     * Register core services in the container
     */
    private function registerServices(): void
    {
        // Register session service
        $this->container->bind('session', function() {
            return $this->session;
        });
        
        // Register auth service
        $this->container->bind('auth', function() {
            return $this->auth;
        });
        
        // Register view service
        $this->container->bind('view', function() {
            return new View();
        });
        
        // Register database service
        $this->container->bind('db', function() {
            return \App\Config\Database::getConnection();
        });
        
        // Register validator service
        $this->container->bind('validator', function() {
            return new Validator();
        });
    }
    
    /**
     * Get service from container
     */
    public function get(string $service)
    {
        return $this->container->get($service);
    }
    
    /**
     * Check if application is in debug mode
     */
    public function isDebug(): bool
    {
        return $_ENV['APP_DEBUG'] === 'true';
    }
    
    /**
     * Get application environment
     */
    public function getEnvironment(): string
    {
        return $_ENV['APP_ENV'] ?? 'production';
    }
    
    /**
     * Get application URL
     */
    public function getUrl(): string
    {
        return rtrim($_ENV['APP_URL'] ?? '', '/');
    }
    
    /**
     * Redirect to URL
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        if (!headers_sent()) {
            header("Location: $url", true, $statusCode);
            exit;
        }
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('_csrf_token', $token);
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        $sessionToken = $this->session->get('_csrf_token');
        return $sessionToken && hash_equals($sessionToken, $token);
    }
}