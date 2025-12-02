<?php if (!isset($layout) || $layout === 'guest'): ?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - <?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?></title>
    <link href="<?= $this->asset('css/app.css') ?>" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?= App\Models\Setting::get('site_color_primary', '#971b1e') ?>;
        }
    </style>
</head>
<body class="h-full bg-gray-50">
<?php endif; ?>

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <div class="mx-auto h-12 w-12 text-red-600">
                <i class="fas fa-exclamation-circle text-6xl"></i>
            </div>
            <h1 class="mt-6 text-3xl font-extrabold text-gray-900">
                Server Error
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                <?= isset($message) ? $this->e($message) : 'Something went wrong on our end. Please try again later.' ?>
            </p>
        </div>
        
        <div class="mt-8 text-center space-y-4">
            <button onclick="location.reload()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color: var(--primary-color); focus:ring-color: var(--primary-color)">
                <i class="fas fa-redo mr-2"></i>
                Try Again
            </button>
            
            <div class="text-sm">
                <a href="<?= $this->url('/') ?>" class="font-medium hover:underline" style="color: var(--primary-color)">
                    <i class="fas fa-home mr-1"></i>
                    Return Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!isset($layout) || $layout === 'guest'): ?>
</body>
</html>
<?php endif; ?>