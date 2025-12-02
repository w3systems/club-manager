<?php
// UPDATED: app/Core/Model.php - With a robust, instance-based query builder and date handling fix.

namespace App\Core;

use App\Config\Database;
use PDO;
use Exception;

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

    // --- QUERY BUILDER PROPERTIES (INSTANCE-BASED) ---
    protected string $query = '';
    protected array $bindings = [];
    
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->syncOriginal();
    }
    
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable) || empty(static::$fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }
    
    public function fillFromDatabase(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }
    
    public function setAttribute(string $key, $value): void
    {
        // This method now only sets the raw attribute. Casting happens on get.
        $this->attributes[$key] = $value;
    }
    
    public function getAttribute(string $key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }
        
        $value = $this->attributes[$key];

        // Only attempt to cast the attribute if a cast is defined for it.
        if (isset(static::$casts[$key])) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }
    
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }
    
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }
    
    protected function castAttribute(string $key, $value)
    {
        if ($value === null) return null;
        
        $cast = static::$casts[$key];
        switch ($cast) {
            case 'int': case 'integer':
                return (int) $value;
            case 'bool': case 'boolean':
                return (bool) $value;
            case 'float':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'array': case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'datetime':
                // Return existing DateTime object or create a new one from string
                return $value instanceof \DateTime ? $value : new \DateTime($value);
            default:
                return $value;
        }
    }
    
    protected static function getConnection(): PDO
    {
        return Database::getConnection();
    }
    
    public static function find(int $id): ?static
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ? LIMIT 1";
        $stmt = static::getConnection()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? static::createFromRow($row) : null;
    }
    
    protected static function createFromRow(array $row): static
    {
        $model = new static();
        $model->fillFromDatabase($row);
        $model->exists = true;
        $model->syncOriginal();
        return $model;
    }
    
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        if (!$model) {
            throw new Exception(static::class . " with ID {$id} not found");
        }
        return $model;
    }

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function where(string $column, string $operator, $value): self
    {
        $model = new static();
        $model->query = "SELECT * FROM " . static::$table . " WHERE `{$column}` {$operator} ?";
        $model->bindings = [$value];
        return $model;
    }

    public function andWhere(string $column, string $operator, $value): self
    {
        $this->query .= " AND `{$column}` {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->query .= " ORDER BY `{$column}` {$direction}";
        return $this;
    }

    public function fetchAll(): array
    {
        $stmt = static::getConnection()->prepare($this->query);
        $stmt->execute($this->bindings);
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = static::createFromRow($row);
        }
        return $results;
    }
    
    protected function getInsertableAttributes(): array
    {
        $attributes = $this->attributes;
        if (!$this->getAttribute(static::$primaryKey)) {
            unset($attributes[static::$primaryKey]);
        }
        if (!isset($attributes['created_at'])) {
            $attributes['created_at'] = new \DateTime();
        }
        if (!isset($attributes['updated_at'])) {
            $attributes['updated_at'] = new \DateTime();
        }
        return $this->prepareAttributesForDb($attributes);
    }

    protected function prepareAttributesForDb(array $attributes): array
    {
        foreach ($attributes as $key => &$value) {
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
        }
        return $attributes;
    }
    
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
    protected function insert(): bool
    {
        $attributes = $this->getInsertableAttributes();
        if (empty($attributes)) return false;
        $columns = '`' . implode('`, `', array_keys($attributes)) . '`';
        $placeholders = implode(', ', array_fill(0, count($attributes), '?'));
        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        $stmt = static::getConnection()->prepare($sql);
        $result = $stmt->execute(array_values($attributes));
        if ($result) {
            $this->setAttribute(static::$primaryKey, static::getConnection()->lastInsertId());
            $this->exists = true;
            $this->syncOriginal();
        }
        return $result;
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?";
        $stmt = static::getConnection()->prepare($sql);
        return $stmt->execute([$this->getAttribute(static::$primaryKey)]);
    }
    
    protected function getDirtyAttributes(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        if (!empty($dirty)) {
            $dirty['updated_at'] = new \DateTime();
        }
        return $this->prepareAttributesForDb($dirty);
    }
    
    protected function update(): bool
    {
        $attributes = $this->getDirtyAttributes();
        if (empty($attributes)) return true;
        $setParts = array_map(fn($key) => "`$key` = ?", array_keys($attributes));
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
    
    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    public static function all(string $orderBy = null): array
    {
        $sql = "SELECT * FROM " . static::$table;
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        $stmt = static::getConnection()->prepare($sql);
        $stmt->execute();
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = static::createFromRow($row);
        }
        return $results;
    }
}