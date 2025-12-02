<?php

namespace App\Config;

use PDO;
use PDOException;
use Exception;

/**
 * Database Configuration and Connection Management
 */
class Database
{
    private static ?PDO $connection = null;
    
    /**
     * Initialize database connection
     */
    public static function initialize(): void
    {
        if (self::$connection === null) {
            self::connect();
        }
    }
    
    /**
     * Get database connection
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }
        
        return self::$connection;
    }
    
    /**
     * Create database connection
     */
    private static function connect(): void
    {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? '';
            $username = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            
            if (empty($database)) {
                throw new Exception('Database name is required');
            }
            
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            self::$connection = new PDO($dsn, $username, $password, $options);
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
    }
    
    /**
     * Close database connection
     */
    public static function close(): void
    {
        self::$connection = null;
    }
    
    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollback();
    }
    
    /**
     * Check if in transaction
     */
    public static function inTransaction(): bool
    {
        return self::getConnection()->inTransaction();
    }
}