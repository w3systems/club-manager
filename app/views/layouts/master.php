<?php
// app/views/layouts/master.php

use App\Models\Setting;
$siteColors = [
    'primary' => Setting::get('site_color_primary') ?? '#0d9488', // teal-600
    'secondary' => Setting::get('site_color_secondary') ?? '#3b82f6' // blue-500
];

// Basic templating function start/end section to allow views to define content blocks
$__sections = [];
$__current_section = null;

/*function start_section($name) {
    global $__sections, $__current_section;
    $__current_section = $name;
    ob_start();
}*/

/*function end_section() {
    global $__sections, $__current_section;
    $__sections[$__current_section] = ob_get_clean();
    $__current_section = null;
}*/

/*function yield_section($name) {
    global $__sections;
    echo $__sections[$name] ?? '';
}*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Define custom properties for dynamic colors */
        :root {
            --color-primary: <?= $siteColors['primary'] ?>;
            --color-secondary: <?= $siteColors['secondary'] ?>;
        }
        /* Apply dynamic colors using custom properties */
        .bg-primary { background-color: var(--color-primary); }
        .text-primary { color: var(--color-primary); }
        .hover\:bg-primary-dark:hover { background-color: color-mix(in srgb, var(--color-primary) 90%, black); }
        .focus\:ring-primary { --tw-ring-color: var(--color-primary); }
        .border-primary { border-color: var(--color-primary); }

        .bg-secondary { background-color: var(--color-secondary); }
        .text-secondary { color: var(--color-secondary); }
        .hover\:bg-secondary-dark:hover { background-color: color-mix(in srgb, var(--color-secondary) 90%, black); }
        .focus\:ring-secondary { --tw-ring-color: var(--color-secondary); }
        .border-secondary { border-color: var(--color-secondary); }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <main>
        <?php yield_section('content'); ?>
    </main>
</body>
</html>
