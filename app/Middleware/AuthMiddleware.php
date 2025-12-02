<?php
// app/Middleware/AuthMiddleware.php

namespace App\Middleware;

use App\Core\Auth;

/**
 * Authentication Middleware
 * Ensures user is authenticated
 */
class AuthMiddleware
{
    private Auth $auth;
    
    public function __construct()
    {
        $this->auth = new Auth();
    }
    
    /**
     * Handle the middleware
     */
    public function handle(): void
    {
        if (!$this->auth->check()) {
            $this->redirectToLogin();
        }
    }
    
    /**
     * Redirect to appropriate login page
     */
    private function redirectToLogin(): void
    {
        $currentPath = $_SERVER['REQUEST_URI'];
        
        if (strpos($currentPath, '/admin') === 0) {
            header('Location: /admin/login');
        } else {
            header('Location: /login');
        }
        
        exit;
    }
}
