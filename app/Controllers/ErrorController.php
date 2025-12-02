<?php
// app/Controllers/ErrorController.php

namespace App\Controllers;

use App\Core\Controller;

/**
 * Error Controller
 * Handles error pages
 */
class ErrorController extends Controller
{
    /**
     * Handle 404 Not Found
     */
    public function notFound(): void
    {
        http_response_code(404);
        
        $data = [
            'title' => 'Page Not Found',
            'message' => 'The page you are looking for could not be found.',
        ];
        
        // Determine layout based on current path
        $layout = 'guest';
        if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
            $layout = 'admin';
        } elseif ($this->auth->isMember()) {
            $layout = 'member';
        }
        
        $this->render('errors/404', $data, $layout);
    }
    
    /**
     * Handle 500 Server Error
     */
    public function serverError(): void
    {
        http_response_code(500);
        
        $data = [
            'title' => 'Server Error',
            'message' => 'An internal server error occurred. Please try again later.',
        ];
        
        // Determine layout based on current path
        $layout = 'guest';
        if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
            $layout = 'admin';
        } elseif ($this->auth->isMember()) {
            $layout = 'member';
        }
        
        $this->render('errors/500', $data, $layout);
    }
    
    /**
     * Handle 403 Forbidden
     */
    public function forbidden(): void
    {
        http_response_code(403);
        
        $data = [
            'title' => 'Access Denied',
            'message' => 'You do not have permission to access this resource.',
        ];
        
        // Determine layout based on current path
        $layout = 'guest';
        if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
            $layout = 'admin';
        } elseif ($this->auth->isMember()) {
            $layout = 'member';
        }
        
        $this->render('errors/403', $data, $layout);
    }
}
