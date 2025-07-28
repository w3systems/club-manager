<?php

namespace App\Core;

use App\Models\Admin;
use App\Models\Member;
use App\Core\Session;

/**
 * Authentication System
 * Clean version without debug code
 */
class Auth
{
    private Session $session;
    private ?object $user = null;
    private ?string $userType = null;
    
    public function __construct()
    {
        $this->session = new Session();
        $this->loadUser();
    }
    
    /**
     * Attempt to login admin user
     */
    public function attemptAdmin(string $email, string $password): bool
    {
        $admin = Admin::findByEmail($email);
        
        if (!$admin || !password_verify($password, $admin->password_hash)) {
            return false;
        }
        
        $this->loginAdmin($admin);
        return true;
    }
    
    /**
     * Login admin user - SIMPLE VERSION
     */
    public function loginAdmin(Admin $admin): void
    {
        // Get admin ID directly from database to bypass model issues
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$admin->email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$result || !$result['id']) {
            error_log("Could not get admin ID from database");
            return;
        }
        
        $adminId = (int) $result['id'];
        
        // Regenerate session for security
        $this->session->regenerate();
        
        // Set session data
        $this->session->set('admin_id', $adminId);
        $this->session->set('user_type', 'admin');
        
        // Set in memory
        $this->user = $admin;
        $this->userType = 'admin';
        
        // Update last login
        $admin->updateLastLogin();
        
        //error_log("Admin login successful - ID: " . $adminId);
    }
    
    /**
     * Attempt to login member user
     */
    public function attemptMember(string $email, string $password): bool
    {
        $member = Member::findByEmail($email);
        
        if (!$member || !password_verify($password, $member->password_hash)) {
            return false;
        }
        
        $this->loginMember($member);
        return true;
    }
    
    /**
     * Login member user
     */
    public function loginMember(Member $member): void
    {
        // Get member ID directly from database
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->execute([$member->email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$result || !$result['id']) {
            return;
        }
        
        $memberId = (int) $result['id'];
        
        $this->session->regenerate();
        $this->session->set('member_id', $memberId);
        $this->session->set('user_type', 'member');
        
        $this->user = $member;
        $this->userType = 'member';
    }
    
    /**
     * Logout current user
     */
    public function logout(): void
    {
        $this->session->clear();
        $this->session->destroy();
        
        $this->user = null;
        $this->userType = null;
    }
    
    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return $this->user !== null;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->userType === 'admin';
    }
    
    /**
     * Check if user is member
     */
    public function isMember(): bool
    {
        return $this->userType === 'member';
    }
    
    /**
     * Get current user
     */
    public function user(): ?object
    {
        return $this->user;
    }
    
    /**
     * Get current user ID
     */
    public function id(): ?int
    {
        return $this->user?->id;
    }
    
    /**
     * Get current user type
     */
    public function userType(): ?string
    {
        return $this->userType;
    }
    
    /**
     * Check if admin has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isAdmin()) {
            return false;
        }
        
        return $this->user->hasPermission($permission);
    }
    
    /**
     * Load user from session - SIMPLE VERSION
     */
    private function loadUser(): void
    {
        $userType = $this->session->get('user_type');
        
        if ($userType === 'admin') {
            $adminId = $this->session->get('admin_id');
            
            if ($adminId && is_numeric($adminId)) {
                $this->user = Admin::find((int) $adminId);
                if ($this->user) {
                    $this->userType = 'admin';
                } else {
                    $this->clearSession();
                }
            } else {
                $this->clearSession();
            }
        } elseif ($userType === 'member') {
            $memberId = $this->session->get('member_id');
            
            if ($memberId && is_numeric($memberId)) {
                $this->user = Member::find((int) $memberId);
                if ($this->user) {
                    $this->userType = 'member';
                } else {
                    $this->clearSession();
                }
            } else {
                $this->clearSession();
            }
        }
    }
    
    /**
     * Clear invalid session
     */
    private function clearSession(): void
    {
        $this->session->clear();
        $this->user = null;
        $this->userType = null;
    }
    
    /**
     * Redirect to appropriate login page
     */
    public function redirectToLogin(): void
    {
        $currentPath = $_SERVER['REQUEST_URI'];
        
        if (strpos($currentPath, '/admin') === 0) {
            header('Location: /admin/login');
        } else {
            header('Location: /login');
        }
        exit;
    }
    
    /**
     * Redirect to appropriate dashboard
     */
    public function redirectToDashboard(): void
    {
        if ($this->isAdmin()) {
            header('Location: /admin');
        } else {
            header('Location: /');
        }
        exit;
    }
}