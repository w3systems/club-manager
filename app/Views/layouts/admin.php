<!-- app/Views/layouts/admin.php - MODIFIED HEAD SECTION -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $this->e($title) . ' - ' : '' ?><?= $this->e($_ENV['APP_NAME'] ?? 'Club Manager') ?> Admin</title>
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
    
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: <?= App\Models\Setting::get('site_color_primary', '#971b1e') ?>;
            --secondary-color: <?= App\Models\Setting::get('site_color_secondary', '#cda22d') ?>;
        }
        
        /* Custom button styles */
        .btn {
            @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors;
        }
        .btn-primary {
            @apply text-white focus:ring-red-500;
            background-color: var(--primary-color);
        }
        .btn-primary:hover {
            filter: brightness(0.9);
        }
        
        /* Form styles */
        .form-input {
            @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm;
        }
        .form-input-error {
            @apply border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500;
        }
        
        /* Badge styles */
        .badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }
        .badge-success { @apply bg-green-100 text-green-800; }
        .badge-error { @apply bg-red-100 text-red-800; }
        .badge-warning { @apply bg-yellow-100 text-yellow-800; }
        .badge-info { @apply bg-blue-100 text-blue-800; }
        
        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Sidebar active states */
        .nav-link-active {
            @apply bg-red-100 text-red-900 border-r-2 border-red-500;
        }
        .nav-link-inactive {
            @apply text-gray-600 hover:bg-gray-50 hover:text-gray-900;
        }
    </style>
</head>