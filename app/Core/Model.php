<?php
// UPDATED: app/Core/Model.php - Fix the createFromRow method

namespace App\Core;

use App\Config\Database;
use PDO;
use PDOStatement;
use Exception;

/**
 * Base Model Class
 * Provides common database operations for all models
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $hidden = [];
    protected static array $casts = [];
    
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;
    
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->syncOriginal();
    }
    
    /**
     * Fill model with attributes (respects fillable)
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable) || empty(static::$fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Fill model with ALL attributes (used for database results)
     */
    public function fillFromDatabase(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        
        return $this;
    }
    
    /**
     * Set attribute value
     */
    public function setAttribute(string $key, $value): void
    {
        // Cast value if casting is defined
        if (isset(static::$casts[$key])) {
            $value = $this->castAttribute($key, $value);
        }
        
        $this->attributes[$key] = $value;
    }
    
    /**
     * Get attribute value
     */
    public function getAttribute(string $key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }
        
        $value = $this->attributes[$key];
        
        // Cast value if casting is defined
        if (isset(static::$casts[$key])) {
            return $this->castAttribute($key, $value);
        }
        
        return $value;
    }
    
    /**
     * Magic getter
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Magic isset
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }
    
    /**
     * Cast attribute value
     */
    protected function castAttribute(string $key, $value)
    {
        $cast = static::$casts[$key];
        
        switch ($cast) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'float':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'json':
                return json_decode($value, true);
            case 'datetime':
                return $value ? new \DateTime($value) : null;
            default:
                return $value;
        }
    }
    
    /**
     * Get database connection
     */
    protected static function getConnection(): PDO
    {
        return Database::getConnection();
    }
    
    /**
     * Find record by ID
     */
    public static function find(int $id): ?static
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ? LIMIT 1";
        $stmt = static::getConnection()->prepare($sql);
        $stmt->execute([$id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return null;
        }
        
        return static::createFromRow($row);
    }
    
    /**
     * Create model instance from database row - FIXED VERSION
     */
    protected static function createFromRow(array $row): static
    {
        $model = new static(); // Create empty instance
        $model->fillFromDatabase($row); // Fill with ALL database fields
        $model->exists = true;
        $model->syncOriginal();
        return $model;
    }
    
    // ... rest of the methods remain the same ...
    
    /**
     * Find record by ID or throw exception
     */
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        
        if (!$model) {
            throw new Exception(static::class . " with ID {$id} not found");
        }
        
        return $model;
    }
    
    /**
     * Get insertable attributes
     */
    protected function getInsertableAttributes(): array
    {
        $attributes = $this->attributes;
        
        // Remove primary key if it's auto-increment
        if (!$this->getAttribute(static::$primaryKey)) {
            unset($attributes[static::$primaryKey]);
        }
        
        // Add timestamps if not present
        if (!isset($attributes['created_at'])) {
            $attributes['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($attributes['updated_at'])) {
            $attributes['updated_at'] = date('Y-m-d H:i:s');
        }
        
        return $attributes;
    }
    
    /**
     * Save model to database
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
    /**
     * Insert new record
     */
    protected function insert(): bool
    {
        $attributes = $this->getInsertableAttributes();
        
        if (empty($attributes)) {
            return false;
        }
        
        $columns = array_keys($attributes);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = static::getConnection()->prepare($sql);
        $result = $stmt->execute(array_values($attributes));
        
        if ($result) {
            $this->setAttribute(static::$primaryKey, static::getConnection()->lastInsertId());
            $this->exists = true;
            $this->syncOriginal();
        }
        
        return $result;
    }
    
    /**
     * Convert model to array
     */
    public function toArray(): array
    {
        $array = $this->attributes;
        
        // Remove hidden attributes
        foreach (static::$hidden as $hidden) {
            unset($array[$hidden]);
        }
        
        return $array;
    }
    
    /**
     * Get dirty (changed) attributes
     */
    protected function getDirtyAttributes(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        
        // Always update timestamp
        $dirty['updated_at'] = date('Y-m-d H:i:s');
        
        return $dirty;
    }
    
    /**
     * Update existing record
     */
    protected function update(): bool
    {
        $attributes = $this->getDirtyAttributes();
        
        if (empty($attributes)) {
            return true;
        }
        
        $setParts = array_map(fn($key) => "$key = ?", array_keys($attributes));
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $setParts) . " WHERE " . static::$primaryKey . " = ?";
        
        $values = array_values($attributes);
        $values[] = $this->getAttribute(static::$primaryKey);
        
        $stmt = static::getConnection()->prepare($sql);
        $result = $stmt->execute($values);
        
        if ($result) {
            $this->syncOriginal();
        }
        
        return $result;
    }
    
    /**
     * Sync original attributes
     */
    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }
}