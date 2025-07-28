<?php
// app/Controllers/Auth/LoginController.php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\Admin;
use App\Models\Member;

/**
 * Login Controller
 * Handles authentication for both admin and member users
 */
class LoginController extends Controller
{
    /**
     * Show member login form
     */
    public function showMemberLogin(): void
    {
        // Redirect if already authenticated
        if ($this->auth->check()) {
            $this->auth->redirectToDashboard();
        }
        
        $this->render('auth/member-login', [], 'auth');
    }
    
    /**
     * Show admin login form
     */
    public function showAdminLogin(): void
    {
        // Redirect if already authenticated as admin
        if ($this->auth->isAdmin()) {
            $this->redirect('/admin');
        }
        
        $this->render('auth/admin-login', [], 'auth');
    }
    
    /**
     * Handle member login
     */
    public function memberLogin(): void
    {
        $this->requireCsrfToken();
        
        $credentials = $this->validate($this->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Rate limiting check (optional - implement if needed)
        $this->checkRateLimit($credentials['email']);
        
        if ($this->auth->attemptMember($credentials['email'], $credentials['password'])) {
            $this->session->success('Welcome back!');
            
            // Redirect to intended page or dashboard
            $redirectUrl = $this->session->get('intended_url', '/');
            $this->session->remove('intended_url');
            
            $this->redirect($redirectUrl);
        } else {
            $this->recordFailedAttempt($credentials['email']);
            $this->errorAndBack('Invalid email or password.');
        }
    }
    
    /**
     * Handle admin login
     */
    public function adminLogin(): void
    {
        $this->requireCsrfToken();
        
        $credentials = $this->validate($this->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
		// Rate limiting check
        $this->checkRateLimit($credentials['email']);
        
        if ($this->auth->attemptAdmin($credentials['email'], $credentials['password'])) {
            $this->session->success('Welcome to the admin panel!');
            
            // Redirect to intended page or admin dashboard
            $redirectUrl = $this->session->get('intended_url', '/admin');
            $this->session->remove('intended_url');
            
            $this->redirect($redirectUrl);
        } else {
            $this->recordFailedAttempt($credentials['email']);
            $this->errorAndBack('Invalid email or password.');
        }
    }


	/*public function adminLogin(): void
	{
		$this->requireCsrfToken();
		
		$credentials = $this->validate($this->all(), [
			'email' => 'required|email',
			'password' => 'required',
		]);
		
		if ($this->auth->attemptAdmin($credentials['email'], $credentials['password'])) {
			
			// DEBUG: Check what's in session immediately after login
			error_log("LOGIN SUCCESS - Session contents: " . print_r($_SESSION, true));
			
			// Set a simple success message
			$this->session->success('Welcome to the admin panel!');
			
			// Try redirect
			$this->redirect('/admin');
			
		} else {
			$this->errorAndBack('Invalid email or password.');
		}
	}*/



    
    /**
     * Check rate limiting for login attempts
     */
    private function checkRateLimit(string $email): void
    {
        $cacheKey = 'login_attempts_' . md5($email);
        $attempts = $this->session->get($cacheKey, 0);
        
        if ($attempts >= 5) {
            $lockoutTime = $this->session->get($cacheKey . '_lockout', 0);
            
            if (time() < $lockoutTime) {
                $remainingTime = ceil(($lockoutTime - time()) / 60);
                $this->errorAndBack("Too many failed attempts. Please try again in {$remainingTime} minutes.");
            } else {
                // Reset attempts after lockout period
                $this->session->remove($cacheKey);
                $this->session->remove($cacheKey . '_lockout');
            }
        }
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt(string $email): void
    {
        $cacheKey = 'login_attempts_' . md5($email);
        $attempts = $this->session->get($cacheKey, 0) + 1;
        
        $this->session->set($cacheKey, $attempts);
        
        if ($attempts >= 5) {
            // Set lockout for 15 minutes
            $this->session->set($cacheKey . '_lockout', time() + (15 * 60));
        }
    }
}
