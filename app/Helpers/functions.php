<?php
/**
 * Global Helper Functions
 * Utility functions available throughout the application
 */

if (!function_exists('app')) {
    /**
     * Get application instance
     */
    function app(): App\Core\Application
    {
        return App\Core\Container::getInstance()->get('app');
    }
}

if (!function_exists('auth')) {
    /**
     * Get authentication instance
     */
    function auth(): App\Core\Auth
    {
        return App\Core\Container::getInstance()->get('auth');
    }
}

if (!function_exists('session')) {
    /**
     * Get session instance
     */
    function session(): App\Core\Session
    {
        return App\Core\Container::getInstance()->get('session');
    }
}

if (!function_exists('view')) {
    /**
     * Get view instance
     */
    function view(): App\Core\View
    {
        return App\Core\Container::getInstance()->get('view');
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url(string $path = ''): string
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        $path = '/' . ltrim($path, '/');
        return $baseUrl . $path;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     */
    function redirect(string $url, int $statusCode = 302): void
    {
        if (!headers_sent()) {
            header("Location: $url", true, $statusCode);
            exit;
        }
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to previous page
     */
    function back(): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referrer);
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP status code
     */
    function abort(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);
        
        switch ($statusCode) {
            case 403:
                include VIEW_PATH . '/errors/403.php';
                break;
            case 404:
                include VIEW_PATH . '/errors/404.php';
                break;
            case 500:
                include VIEW_PATH . '/errors/500.php';
                break;
            default:
                echo $message ?: "HTTP Error $statusCode";
        }
        
        exit;
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML output
     */
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, string $default = ''): string
    {
        $oldInput = $_SESSION['_flash']['old_input'] ?? [];
        return e($oldInput[$key] ?? $default);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate CSRF token
     */
    function csrf_token(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF input field
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     */
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (for debugging)
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable (for debugging)
     */
    function dump($var): void
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

if (!function_exists('logger')) {
    /**
     * Log message
     */
    function logger(string $message, string $level = 'info'): void
    {
        $logFile = STORAGE_PATH . '/logs/app.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency
     */
    function format_currency(float $amount, string $currency = 'GBP'): string
    {
        return match($currency) {
            'GBP' => '£' . number_format($amount, 2),
            'USD' => '$' . number_format($amount, 2),
            'EUR' => '€' . number_format($amount, 2),
            default => $currency . ' ' . number_format($amount, 2)
        };
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date
     */
    function format_date(string $date, string $format = 'Y-m-d H:i'): string
    {
        return date($format, strtotime($date));
    }
}

if (!function_exists('time_ago')) {
    /**
     * Get time ago string
     */
    function time_ago(string $datetime): string
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) {
            return 'just now';
        } elseif ($time < 3600) {
            $minutes = floor($time / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($time < 86400) {
            $hours = floor($time / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($time < 2592000) {
            $days = floor($time / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($time < 31536000) {
            $months = floor($time / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        } else {
            $years = floor($time / 31536000);
            return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
        }
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate URL-friendly slug
     */
    function str_slug(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        
        return strtolower($text);
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate random string
     */
    function str_random(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}

if (!function_exists('array_get')) {
    /**
     * Get array value using dot notation
     */
    function array_get(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            
            $array = $array[$segment];
        }
        
        return $array;
    }
}

if (!function_exists('validate_email')) {
    /**
     * Validate email address
     */
    function validate_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validate_phone')) {
    /**
     * Validate UK phone number
     */
    function validate_phone(string $phone): bool
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return preg_match('/^(07\d{9}|\d{10,11})$/', $phone);
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize input data
     */
    function sanitize_input($data)
    {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('is_production')) {
    /**
     * Check if application is in production
     */
    function is_production(): bool
    {
        return env('APP_ENV') === 'production';
    }
}

if (!function_exists('is_debug')) {
    /**
     * Check if application is in debug mode
     */
    function is_debug(): bool
    {
        return env('APP_DEBUG') === 'true';
    }
}