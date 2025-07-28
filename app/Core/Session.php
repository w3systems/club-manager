<?php

namespace App\Core;

/**
 * Session Management Class
 * Handles session operations and flash messages
 */
class Session
{
    public function __construct()
    {
        // Session is already started in index.php, so just ensure it's active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Set session value
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $_SESSION = [];
    }
    
    /**
     * Destroy session
     */
    public function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }
    
    /**
     * Regenerate session ID
     */
    public function regenerate(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }
    
    // ... rest of the flash message methods remain the same ...
    
    /**
     * Set flash message
     */
    public function flash(string $key, $value): void
    {
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get flash message and remove it
     */
    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    /**
     * Check if flash message exists
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }
    
    /**
     * Get all flash messages and clear them
     */
    public function getAllFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        $_SESSION['_flash'] = [];
        return $flash;
    }
    
    /**
     * Set success message
     */
    public function success(string $message): void
    {
        $this->flash('success', $message);
    }
    
    /**
     * Set error message
     */
    public function error(string $message): void
    {
        $this->flash('error', $message);
    }
    
    /**
     * Set warning message
     */
    public function warning(string $message): void
    {
        $this->flash('warning', $message);
    }
    
    /**
     * Set info message
     */
    public function info(string $message): void
    {
        $this->flash('info', $message);
    }
    
    /**
     * Store old input for form repopulation
     */
    public function flashInput(array $input): void
    {
        $this->flash('old_input', $input);
    }
    
    /**
     * Get old input value
     */
    public function old(string $key, $default = '')
    {
        $oldInput = $this->get('_flash')['old_input'] ?? [];
        return $oldInput[$key] ?? $default;
    }
}
