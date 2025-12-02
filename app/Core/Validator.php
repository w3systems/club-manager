<?php

namespace App\Core;

/**
 * Input Validation Class
 * Handles form validation with custom rules
 */
class Validator
{
    private array $data = [];
    private array $rules = [];
    private array $errors = [];
    private array $messages = [];
    
    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $this->validateField($field, $fieldRules);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get first error for a field
     */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
    
    /**
     * Check if field has errors
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
    
    /**
     * Validate a single field
     */
    private function validateField(string $field, string $rules): void
    {
        $value = $this->data[$field] ?? null;
        $rules = explode('|', $rules);
        
        foreach ($rules as $rule) {
            $this->applyRule($field, $value, $rule);
        }
    }
    
    /**
     * Apply validation rule
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        [$ruleName, $parameter] = $this->parseRule($rule);
        switch ($ruleName) {
            case 'required':
                if ($this->isEmpty($value)) {
                    $this->addError($field, 'required');
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'email');
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < $parameter) {
                    $this->addError($field, 'min', ['min' => $parameter]);
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > $parameter) {
                    $this->addError($field, 'max', ['max' => $parameter]);
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'numeric');
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'integer');
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, 'alpha');
                }
                break;
                
            case 'alpha_num':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, 'alpha_num');
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!empty($value) && $value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, 'confirmed');
                }
                break;
                
            /*case 'unique':
                if (!empty($value) && !$this->isUnique($parameter, $field, $value)) {
                    $this->addError($field, 'unique');
                }
                break;*/
 

			/*case 'unique':
				if (!empty($value)) {
					// Parse table and optional exclude ID
					$parts = explode(',', $parameter);
					$table = $parts[0];
					$excludeId = isset($parts[1]) ? $parts[1] : null;
					
					if (!$this->isUnique($table, $field, $value, $excludeId)) {
						$this->addError($field, 'unique');
					}
				}
				break;*/

            /*case 'unique':
                if (!empty($value)) {
                    // Parse table and optional exclude ID from the rule parameter
                    // e.g., 'unique:members,15' -> $parameter = 'members,15'
                    $parts = explode(',', $parameter);
                    $table = $parts[0];
                    $excludeId = $parts[1] ?? null;
                    
                    if (!$this->isUnique($table, $field, $value, $excludeId)) {
                        $this->addError($field, 'unique');
                    }
                }
                break;*/

            case 'unique':
                if (!empty($value)) {
                    // Correctly parse parameters like 'table,excludeId'
                    $parts = explode(',', $parameter);
                    $table = $parts[0];
                    $excludeId = $parts[1] ?? null; // The ID to ignore during the check
                    
                    if (!$this->isUnique($table, $field, $value, $excludeId)) {
                        $this->addError($field, 'unique');
                    }
                }
                break;
 
            case 'exists':
                if (!empty($value) && !$this->exists($parameter, $field, $value)) {
                    $this->addError($field, 'exists');
                }
                break;
                
            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->addError($field, 'date');
                }
                break;
                
            case 'date_format':
                if (!empty($value) && !$this->validateDateFormat($value, $parameter)) {
                    $this->addError($field, 'date_format', ['format' => $parameter]);
                }
                break;
                
            case 'in':
                $options = explode(',', $parameter);
                if (!empty($value) && !in_array($value, $options)) {
                    $this->addError($field, 'in', ['values' => implode(', ', $options)]);
                }
                break;
                
            case 'phone':
                if (!empty($value) && !$this->validatePhone($value)) {
                    $this->addError($field, 'phone');
                }
                break;
        }
    }
    
    /**
     * Parse rule and parameter
     */
    private function parseRule(string $rule): array
    {
        if (strpos($rule, ':') !== false) {
            return explode(':', $rule, 2);
        }
        
        return [$rule, null];
    }
    
    /**
     * Check if value is empty
     */
    private function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }
        
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        
        if (is_array($value) && count($value) === 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Add validation error
     */
    private function addError(string $field, string $rule, array $parameters = []): void
    {
        $message = $this->getErrorMessage($field, $rule, $parameters);
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get error message
     */
    private function getErrorMessage(string $field, string $rule, array $parameters): string
    {
        $key = "{$field}.{$rule}";
        
        if (isset($this->messages[$key])) {
            return $this->messages[$key];
        }
        
        $fieldName = ucfirst(str_replace('_', ' ', $field));
        
		$minValue = $parameters['min'] ?? 'the required number of';
		$maxValue = $parameters['max'] ?? 'the required number of';
		$dateFormat = $parameters['format'] ?? 'dd/mm/yyyy';
		$inValue = $parameters['values'] ?? ' the specified values';
		
        $messages = [
            'required' => "{$fieldName} is required.",
            'email' => "{$fieldName} must be a valid email address.",
            //'min' => "{$fieldName} must be at least {$parameters['min']} characters.",
			'min' => "{$fieldName} must be at least {$minValue} characters.",
            //'max' => "{$fieldName} may not be greater than {$parameters['max']} characters.",
            'max' => "{$fieldName} may not be greater than {$maxValue} characters.",
            'numeric' => "{$fieldName} must be a number.",
            'integer' => "{$fieldName} must be an integer.",
            'alpha' => "{$fieldName} may only contain letters.",
            'alpha_num' => "{$fieldName} may only contain letters and numbers.",
            'confirmed' => "{$fieldName} confirmation does not match.",
            'unique' => "{$fieldName} has already been taken.",
            'exists' => "Selected {$fieldName} is invalid.",
            'date' => "{$fieldName} is not a valid date.",
            'date_format' => "{$fieldName} does not match the format {$dateFormat}.",
            'in' => "{$fieldName} must be one of: {$inValue}.",
            'phone' => "{$fieldName} must be a valid phone number.",
        ];
        
        return $messages[$rule] ?? "{$fieldName} is invalid.";
    }
    
    /**
     * Check if value is unique in database
     */
    private function isUniqueolder(string $table, string $field, $value, $excludeId = null): bool
    {
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$field} = ?");
		$params = [$value];
		
		
		if ($excludeId !== null) {
			$query .= " AND id != ?";
			$params[] = $excludeId;
		}
		echo (print_r($params));
		$stmt = $this->db->prepare($query);
		$stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }

	private function isUniqueold(string $table, string $field, $value, $excludeId = null): bool
	{
		$db = \App\Config\Database::getConnection();
		$query = "SELECT COUNT(*) FROM {$table} WHERE {$field} = ?";
		$params = [$value];
		
		if ($excludeId !== null) {
			$query .= " AND id != ?";
			$params[] = $excludeId;
		}
		
		$stmt = $db->prepare($query);
		$stmt->execute($params);
		return $stmt->fetchColumn() == 0;
	}

    private function isUnique(string $table, string $field, $value, $excludeId = null): bool
    {
        $db = \App\Config\Database::getConnection();
        $query = "SELECT COUNT(*) FROM `{$table}` WHERE `{$field}` = ?";
        $params = [$value];
        
        if ($excludeId !== null) {
            // Assumes the primary key is 'id'
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    
    /**
     * Check if value exists in database
     */
    private function exists(string $table, string $field, $value): bool
    {
        $db = \App\Config\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$field} = ?");
        $stmt->execute([$value]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Validate date format
     */
    private function validateDateFormat(string $date, string $format): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate phone number
     */
    private function validatePhone(string $phone): bool
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid UK mobile (starts with 07 and 11 digits total)
        // or landline (10-11 digits)
        return preg_match('/^(07\d{9}|\d{10,11})$/', $phone);
    }
}