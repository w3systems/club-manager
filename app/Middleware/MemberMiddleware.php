<?php
// app/Middleware/MemberMiddleware.php

namespace App\Middleware;

use App\Core\Auth;

/**
 * Member Middleware
 * Ensures user is authenticated as member
 */
class MemberMiddleware
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
        if (!$this->auth->check() || !$this->auth->isMember()) {
            $this->handleUnauthorized();
        }
    }
    
    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
        } else {
            http_response_code(403);
            echo "Access denied. Member access required.";
        }
        
        exit;
    }
}
