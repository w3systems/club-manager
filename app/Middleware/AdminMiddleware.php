<?php
// app/Middleware/AdminMiddleware.php

namespace App\Middleware;

use App\Core\Auth;

/**
 * Admin Middleware
 * Ensures user is authenticated as admin
 */
class AdminMiddleware
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
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->handleUnauthorized();
        }
    }
    
    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized(): void
    {
        if (!$this->auth->check()) {
            header('Location: /admin/login');
        } else {
            http_response_code(403);
            echo "Access denied. Admin privileges required.";
        }
        
        exit;
    }
}
