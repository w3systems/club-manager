<?php

namespace App\Core;

use Exception;

/**
 * View Rendering System
 * Handles template rendering with layouts
 */
class View
{
    private string $viewPath;
    private array $data = [];
    
    public function __construct()
    {
        $this->viewPath = VIEW_PATH;
    }
    
    /**
     * Render a view with optional layout
     */
    public function render(string $view, array $data = [], ?string $layout = null): void
    {
        $this->data = $data;
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = $this->getViewFile($view);
        
        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }
        
        // Extract data variables
        extract($data, EXTR_SKIP);
        
        // Include the view
        include $viewFile;
        
        // Get the view content
        $content = ob_get_clean();
        
        // If layout is specified, render with layout
        if ($layout) {
            $this->renderWithLayout($layout, $content, $data);
        } else {
            echo $content;
        }
    }
    
    /**
     * Render view with layout
     */
    private function renderWithLayout(string $layout, string $content, array $data): void
    {
        $layoutFile = $this->getLayoutFile($layout);
        
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found: {$layoutFile}");
        }
        
        // Make content available to layout
        $data['content'] = $content;
        
        // Extract data variables
        extract($data, EXTR_SKIP);
        
        // Include the layout
        include $layoutFile;
    }
    
    /**
     * Include a partial view
     */
    public function partial(string $partial, array $data = []): void
    {
        $partialFile = $this->getViewFile($partial);
        
        if (!file_exists($partialFile)) {
            throw new Exception("Partial file not found: {$partialFile}");
        }
        
        // Merge with existing data
        $data = array_merge($this->data, $data);
        
        // Extract data variables
        extract($data, EXTR_SKIP);
        
        // Include the partial
        include $partialFile;
    }
    
    /**
     * Include a component
     */
    public function component(string $component, array $data = []): void
    {
        $componentFile = $this->viewPath . '/components/' . str_replace('.', '/', $component) . '.php';
        
        if (!file_exists($componentFile)) {
            throw new Exception("Component file not found: {$componentFile}");
        }
        
        // Merge with existing data
        $data = array_merge($this->data, $data);
        
        // Extract data variables
        extract($data, EXTR_SKIP);
        
        // Include the component
        include $componentFile;
    }
    
    /**
     * Escape HTML output
     */
    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate URL
     */
    public function url(string $path = ''): string
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        $path = '/' . ltrim($path, '/');
        return $baseUrl . $path;
    }
    
    /**
     * Generate asset URL
     */
    public function asset(string $path): string
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }
    
    /**
     * Generate CSRF input field
     */
    public function csrf(): string
    {
        $token = $_SESSION['_csrf_token'] ?? '';
        return '<input type="hidden" name="_token" value="' . $this->e($token) . '">';
    }
    
    /**
     * Get old input value (for form repopulation) - CORRECTED
     */
    public function old(string $key, $default = '')
    {
        $oldInput = $_SESSION['_flash']['old_input'] ?? [];
        // Return the old input if it exists, otherwise return the default value.
        return $oldInput[$key] ?? $default;
    }

    
    /**
     * Check if field has validation error
     */
    public function hasError(string $field): bool
    {
        $errors = $_SESSION['_flash']['errors'] ?? [];
        return isset($errors[$field]);
    }
    
    /**
     * Get first validation error for field
     */
    public function error(string $field): string
    {
        $errors = $_SESSION['_flash']['errors'] ?? [];
        return $errors[$field][0] ?? '';
    }
    
    /**
     * Format date
     */
    public function formatDate(string $date, string $format = 'Y-m-d H:i'): string
    {
        return date($format, strtotime($date));
    }
    
    /**
     * Format currency
     */
    public function formatCurrency(float $amount, string $currency = 'GBP'): string
    {
        return match($currency) {
            'GBP' => '£' . number_format($amount, 2),
            'USD' => '$' . number_format($amount, 2),
            'EUR' => '€' . number_format($amount, 2),
            default => $currency . ' ' . number_format($amount, 2)
        };
    }
    
    /**
     * Truncate text
     */
    public function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Pluralize word
     */
    public function pluralize(int $count, string $singular, string $plural = null): string
    {
        if ($count === 1) {
            return $singular;
        }
        
        return $plural ?: $singular . 's';
    }
    
    /**
     * Generate pagination links
     */
    public function pagination(array $pagination): void
    {
        if ($pagination['total_pages'] <= 1) {
            return;
        }
        
        $current = $pagination['current_page'];
        $total = $pagination['total_pages'];
        $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
        $query = $_GET;
        
        echo '<nav class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">';
        echo '<div class="flex flex-1 justify-between sm:hidden">';
        
        // Mobile previous
        if ($pagination['has_prev']) {
            $query['page'] = $pagination['prev_page'];
            $url = $baseUrl . '?' . http_build_query($query);
            echo '<a href="' . $url . '" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>';
        }
        
        // Mobile next
        if ($pagination['has_next']) {
            $query['page'] = $pagination['next_page'];
            $url = $baseUrl . '?' . http_build_query($query);
            echo '<a href="' . $url . '" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>';
        }
        
        echo '</div>';
        
        // Desktop pagination
        echo '<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">';
        echo '<div><p class="text-sm text-gray-700">Showing page <span class="font-medium">' . $current . '</span> of <span class="font-medium">' . $total . '</span></p></div>';
        echo '<div><nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">';
        
        // Generate page links
        $start = max(1, $current - 2);
        $end = min($total, $current + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $query['page'] = $i;
            $url = $baseUrl . '?' . http_build_query($query);
            $classes = $i === $current 
                ? 'relative z-10 inline-flex items-center bg-indigo-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                : 'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0';
            
            echo '<a href="' . $url . '" class="' . $classes . '">' . $i . '</a>';
        }
        
        echo '</nav></div></div></nav>';
    }
    
    /**
     * Get view file path
     */
    private function getViewFile(string $view): string
    {
        return $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
    }
    
    /**
     * Get layout file path
     */
    private function getLayoutFile(string $layout): string
    {
        return $this->viewPath . '/layouts/' . str_replace('.', '/', $layout) . '.php';
    }



	// Helper methods for alerts (these would be added to the View class)
	function getAlertClasses($type) {
		return match($type) {
			'success' => 'bg-green-50 text-green-800 border border-green-200',
			'error' => 'bg-red-50 text-red-800 border border-red-200',
			'warning' => 'bg-yellow-50 text-yellow-800 border border-yellow-200',
			'info' => 'bg-blue-50 text-blue-800 border border-blue-200',
			default => 'bg-gray-50 text-gray-800 border border-gray-200'
		};
	}

	function getAlertIcon($type) {
		return match($type) {
			'success' => 'fas fa-check-circle text-green-400',
			'error' => 'fas fa-exclamation-circle text-red-400',
			'warning' => 'fas fa-exclamation-triangle text-yellow-400',
			'info' => 'fas fa-info-circle text-blue-400',
			default => 'fas fa-info-circle text-gray-400'
		};
	}

}