<?php
// app/Middleware/PermissionMiddleware.php

namespace App\Middleware;

use App\Core\Auth;

/**
 * Permission Middleware
 * Ensures admin user has required permission
 */
class PermissionMiddleware
{
    private Auth $auth;
    
    public function __construct()
    {
        $this->auth = new Auth();
    }
    
    /**
     * Handle the middleware
     */
    public function handle(string $permission = null): void
    {
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->handleUnauthorized();
        }
        
        if ($permission && !$this->auth->hasPermission($permission)) {
            $this->handleInsufficientPermissions();
        }
    }
    
    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized(): void
    {
        http_response_code(403);
        echo "Access denied. Admin privileges required.";
        exit;
    }
    
    /**
     * Handle insufficient permissions
     */
    private function handleInsufficientPermissions(): void
    {
        http_response_code(403);
        echo "Access denied. Insufficient permissions.";
        exit;
    }
}
