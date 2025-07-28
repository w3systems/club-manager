<?php

namespace App\Core;

use Exception;

/**
 * Simple Dependency Injection Container
 */
class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];
    
    private function __construct() {}
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Bind a service to the container
     */
    public function bind(string $key, callable $resolver): void
    {
        $this->bindings[$key] = $resolver;
    }
    
    /**
     * Bind a singleton service to the container
     */
    public function singleton(string $key, callable $resolver): void
    {
        $this->bind($key, $resolver);
        $this->instances[$key] = null;
    }
    
    /**
     * Get a service from the container
     */
    public function get(string $key)
    {
        // Check if it's a singleton and already resolved
        if (array_key_exists($key, $this->instances)) {
            if ($this->instances[$key] === null) {
                $this->instances[$key] = $this->bindings[$key]();
            }
            return $this->instances[$key];
        }
        
        // Check if binding exists
        if (!isset($this->bindings[$key])) {
            throw new Exception("Service '{$key}' not found in container");
        }
        
        // Resolve and return
        return $this->bindings[$key]();
    }
    
    /**
     * Check if a service is bound
     */
    public function has(string $key): bool
    {
        return isset($this->bindings[$key]);
    }
    
    /**
     * Remove a binding
     */
    public function remove(string $key): void
    {
        unset($this->bindings[$key]);
        unset($this->instances[$key]);
    }
    
    /**
     * Clear all bindings
     */
    public function clear(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
}