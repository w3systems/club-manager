<?php
// app/Controllers/Auth/LogoutController.php

namespace App\Controllers\Auth;

use App\Core\Controller;

/**
 * Logout Controller
 * Handles user logout
 */
class LogoutController extends Controller
{
    /**
     * Handle member logout
     */
    public function logout(): void
    {
        $this->auth->logout();
        $this->session->success('You have been logged out successfully.');
        $this->redirect('/login');
    }
    
    /**
     * Handle admin logout
     */
    public function adminLogout(): void
    {
        $this->auth->logout();
        $this->session->success('You have been logged out successfully.');
        $this->redirect('/admin/login');
    }
}
