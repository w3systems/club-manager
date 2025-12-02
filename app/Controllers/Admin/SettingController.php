<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Display the settings page form.
     */
    public function index(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_settings');
        
        try {
            $settings = Setting::getAll();
            $envSettings = $this->getEnvSettings(); // Get .env values
            
            $this->render('admin/settings/index', compact('settings', 'envSettings'), 'admin');
            
        } catch (\Exception $e) {
            logger('Error loading settings page: ' . $e->getMessage(), 'error');
            $this->session->error('Could not load settings page. Please try again.');
            $this->redirect('/admin');
        }
    }

    /**
     * Update both database settings and .env file.
     */
    public function update(): void
    {
        $this->requireAdmin();
        $this->requirePermission('manage_settings');
        $this->requireCsrfToken();
        
        $postedData = $this->all();
        $files = $_FILES;

        try {
            // Separate DB settings from .env settings
            $dbSettings = [];
            $envSettings = [];

            foreach ($postedData as $key => $value) {
                if (strpos($key, 'env_') === 0) {
                    $envSettings[substr($key, 4)] = $value;
                } elseif ($key !== '_token') {
                    $dbSettings[$key] = $value;
                }
            }

            // Update database settings
            foreach ($dbSettings as $key => $value) {
                Setting::set($key, $value);
            }

            // Handle file uploads and update .env settings
            $this->handleFileUploads($files, $envSettings);
            $this->updateEnvFile($envSettings);
            
            $this->session->success('Settings updated successfully.');
            
        } catch (\Exception $e) {
            logger('Error updating settings: ' . $e->getMessage(), 'error');
            $this->session->error('An error occurred while saving settings.');
        }
        
        $this->redirect('/admin/settings');
    }

    /**
     * Reads and filters relevant settings from the .env file.
     */
    private function getEnvSettings(): array
    {
        $envPath = APP_ROOT . '/.env';
        if (!file_exists($envPath)) {
            return [];
        }

        $envContent = file_get_contents($envPath);
        $lines = explode("\n", $envContent);
        $settings = [];
        $prefixes = ['APP_', 'STRIPE_', 'MICROSOFT_'];

        foreach ($lines as $line) {
            if (empty($line) || strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                foreach ($prefixes as $prefix) {
                    if (strpos($key, $prefix) === 0) {
                        $settings[$key] = [
                            'value' => $value,
                            'placeholder' => $this->obscureValue($value)
                        ];
                    }
                }
            }
        }
        return $settings;
    }

    /**
     * Updates the .env file with new values.
     */
    private function updateEnvFile(array $newSettings): void
    {
        $envPath = APP_ROOT . '/.env';
        if (!is_writable($envPath)) {
            throw new \Exception('.env file is not writable.');
        }

        $envContent = file_get_contents($envPath);
        
        foreach ($newSettings as $key => $value) {
            // Only update if a new value was provided
            if (!empty($value)) {
                $key = strtoupper($key);
                // Escape special characters for regex
                $escapedKey = preg_quote($key, '/');
                $escapedValue = preg_quote($value, '/');

                if (preg_match("/^{$escapedKey}=/m", $envContent)) {
                    // Key exists, replace it
                    $envContent = preg_replace("/^{$escapedKey}=.*/m", "{$key}={$value}", $envContent);
                } else {
                    // Key doesn't exist, append it
                    $envContent .= "\n{$key}={$value}";
                }
            }
        }
        
        file_put_contents($envPath, $envContent);
    }
    
    /**
     * Obscures a value for display, showing only the last 4 characters.
     */
    private function obscureValue(string $value): string
    {
        if (empty($value)) {
            return 'Not Set';
        }
        $length = strlen($value);
        if ($length > 8) {
            return '****************' . substr($value, -4);
        }
        return '********';
    }

    /**
     * Handles logo and favicon uploads.
     */
    private function handleFileUploads(array $files, array &$envSettings): void
    {
        $uploadDir = PUBLIC_PATH . '/assets/images/site/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploads = [
            'app_logo' => 'APP_LOGO_PATH',
            'app_favicon' => 'APP_FAVICON_PATH'
        ];

        foreach ($uploads as $fileKey => $envKey) {
            if (isset($files[$fileKey]) && $files[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $file = $files[$fileKey];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = str_replace('_', '-', $fileKey) . '.' . $extension;
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Set the public path to be saved in the .env file
                    $envSettings[$envKey] = '/assets/images/site/' . $filename;
                }
            }
        }
    }
}