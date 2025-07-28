<?php
// app/views/member/layouts/member.php

use App\Core\Auth;
use App\Helpers\functions as Helpers;

// Basic templating function start/end section to allow views to define content blocks
$__sections = [];
$__current_section = null;

function start_section($name) {
    global $__sections, $__current_section;
    $__current_section = $name;
    ob_start();
}

function end_section() {
    global $__sections, $__current_section;
    $__sections[$__current_section] = ob_get_clean();
    $__current_section = null;
}

function yield_section($name) {
    global $__sections;
    echo $__sections[$name] ?? '';
}

$loggedInMember = Auth::member();
$siteColors = [
    'primary' => \App\Models\Setting::get('site_color_primary') ?? '#0d9488', // teal-600
    'secondary' => \App\Models\Setting::get('site_color_secondary') ?? '#3b82f6' // blue-500
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Portal | Subscription App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-primary: <?= $siteColors['primary'] ?>;
            --color-secondary: <?= $siteColors['secondary'] ?>;
        }
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

        /* Custom styles for collapsible sidebar */
        .sidebar {
            transition: width 0.3s ease-in-out;
        }
        .sidebar.collapsed {
            width: 4rem; /* w-16 */
        }
        .sidebar.expanded {
            width: 16rem; /* w-64 */
        }
        .sidebar-item-text {
            display: block;
            transition: opacity 0.1s ease-in-out;
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar.collapsed .sidebar-item-text {
            opacity: 0;
            width: 0;
            padding: 0;
            margin: 0;
        }
        .sidebar-icon {
            min-width: 1.5rem; /* For consistent icon size */
        }
        @media (max-width: 1023px) { /* Adjust for mobile/tablet */
            .sidebar.expanded {
                transform: translateX(0);
            }
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen">
        <aside id="sidebar" class="sidebar bg-gray-800 text-white shadow-lg fixed lg:relative h-full flex-shrink-0 z-50">
            <div class="px-4 py-5 flex items-center justify-center h-16 border-b border-gray-700">
                <a href="/" class="text-xl font-bold text-white sidebar-item-text">App Name</a>
                <a href="/" class="text-xl font-bold text-white sidebar-icon hidden sidebar-collapsed-only">A</a>
            </div>
            <nav class="flex-1 px-2 py-4 space-y-2">
                <a href="/" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-7-7v10a1 1 0 01-1 1H5a1 1 0 01-1-1v-10h16z"/></svg>
                    <span class="sidebar-item-text">Dashboard</span>
                </a>
                <a href="/subscriptions" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 4v4m-4 8h8a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="sidebar-item-text">Subscriptions</span>
                </a>
                <a href="/classes" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span class="sidebar-item-text">Classes</span>
                </a>
                <a href="/payments" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10m-10 4h10M3 6h18a1 1 0 011 1v10a1 1 0 01-1 1H3a1 1 0 01-1-1V7a1 1 0 011-1z"/></svg>
                    <span class="sidebar-item-text">Payments</span>
                </a>
                <a href="/payment-methods" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10m-10 4h10M3 6h18a1 1 0 011 1v10a1 1 0 01-1 1H3a1 1 0 01-1-1V7a1 1 0 011-1z"/></svg>
                    <span class="sidebar-item-text">Payment Methods</span>
                </a>
                <a href="/notifications" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.001 2.001 0 0118 14.59V13a3 3 0 00-3-3H9a3 3 0 00-3 3v1.59c0 .537.213 1.052.595 1.435L5 17h5m-2 0v1a3 3 0 003 3h2a3 3 0 003-3v-1m-6 0H9"/></svg>
                    <span class="sidebar-item-text">Notifications</span>
                </a>
                <a href="/messages" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="sidebar-item-text">Messages</span>
                </a>
                <a href="/profile" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="sidebar-item-text">Profile</span>
                </a>
            </nav>
            <div class="px-3 py-4 border-t border-gray-700">
                <a href="/logout" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="sidebar-item-text">Logout</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="w-full bg-white shadow-sm lg:static lg:overflow-y-visible h-16 flex items-center justify-between px-4 sm:px-6 z-40">
                <button id="sidebarToggle" class="text-gray-500 hover:text-gray-900 lg:hidden focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1 flex justify-between items-center">
                    <h1 class="text-xl font-semibold text-gray-900">
                        <?php if (isset($loggedInMember)): ?>
                            Hello, <?= Helpers\esc($loggedInMember['first_name']) ?>!
                        <?php else: ?>
                            Welcome!
                        <?php endif; ?>
                    </h1>
                    <div class="flex items-center ml-auto">
                        <div class="relative ml-3">
                            <div>
                                <button type="button" class="max-w-xs bg-white rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full" src="https://via.placeholder.com/150" alt="">
                                </button>
                            </div>
                            <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" id="user-dropdown">
                                <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>
                                <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-1">Settings</a>
                                <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-2">Sign out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <?php yield_section('content'); ?>
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const userMenuButton = document.getElementById('user-menu-button');
        const userDropdown = document.getElementById('user-dropdown');

        // Initial state for desktop based on screen width
        if (window.innerWidth >= 1024) { // Tailwind's 'lg' breakpoint
            sidebar.classList.add('expanded');
            sidebar.classList.remove('collapsed');
        } else {
            sidebar.classList.add('collapsed');
            sidebar.classList.remove('expanded');
        }

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('expanded');
        });

        userMenuButton.addEventListener('click', () => {
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', (event) => {
            if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Close sidebar on mobile when clicking outside (or navigating)
        // This is a simplified approach; a more robust one might use an overlay
        window.addEventListener('resize', () => {
             if (window.innerWidth >= 1024) {
                sidebar.classList.add('expanded');
                sidebar.classList.remove('collapsed');
                sidebar.classList.remove('fixed'); // Ensure it's not fixed on larger screens
                sidebar.classList.add('relative');
            } else {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('expanded');
                sidebar.classList.add('fixed');
                sidebar.classList.remove('relative');
            }
        });
    </script>
</body>
</html>
