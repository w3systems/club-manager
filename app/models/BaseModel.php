<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Exceptions\DuplicateEntryException; // <-- This line is the critical fix

abstract class BaseModel
{
    /**
     * The database table associated with the model.
     */
    protected static $tableName;

    /**
     * Creates a new record in the database.
     */
    public static function create($data)
    {
        $db = Database::getInstance();

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO " . static::$tableName . " ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(array_values($data));
            return $db->lastInsertId();

        } catch (\PDOException $e) {
            // Check for the specific duplicate entry error code
            if ($e->getCode() === '23000') {
                // Because of the 'use' statement above, this now throws the correct exception
                throw new DuplicateEntryException("A record with this value already exists.");
            }
            // For any other database error, re-throw the original exception
            throw $e;
        }
    }

    // ... other shared model methods ...
}