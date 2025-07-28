<?php
// =====================================
// app/Views/layouts/auth.php - UPDATED FOR CDN
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->e($title) . ' - ' : '' ?><?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?></title>
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#fef2f2', 100: '#fee2e2', 200: '#fecaca', 300: '#fca5a5', 400: '#f87171',
                            500: '#ef4444', 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b', 900: '#7f1d1d'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: <?= App\Models\Setting::get('site_color_primary', '#971b1e') ?>;
            --secondary-color: <?= App\Models\Setting::get('site_color_secondary', '#cda22d') ?>;
        }
        
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        .focus\:ring-primary:focus { 
            --tw-ring-color: var(--primary-color); 
            --tw-ring-opacity: 0.5;
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <?php $this->component('alerts') ?>
        </div>
        
        <?= $content ?>
    </div>
</body>
</html>