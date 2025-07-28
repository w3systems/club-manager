<?php
// app/views/admin/layouts/admin.php

use App\Core\Auth;
use App\Helpers\functions as Helpers;

// Basic templating function start/end section to allow views to define content blocks
$__sections = [];
$__current_section = null;

/*function start_section($name) {
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
}*/

$loggedInAdmin = Auth::admin();
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
    <title>Admin Portal | Subscription App</title>
    <script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="/css/app.css"> 
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
            transition: width 0.3s ease-in-out, transform 0.3s ease-in-out;
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
        /* Mobile specific adjustments */
        @media (max-width: 1023px) { /* Tailwind's 'lg' breakpoint - mobile/tablet */
            .sidebar {
                position: fixed;
                transform: translateX(-100%);
            }
            .sidebar.expanded {
                transform: translateX(0);
            }
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40; /* Below sidebar, above content */
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen">
        <aside id="sidebar" class="sidebar bg-gray-800 text-white shadow-lg fixed lg:relative h-full flex-shrink-0 z-50">
            <div class="px-4 py-5 flex items-center justify-center h-24 border-b border-gray-700">
				<img src="/img/logo.png" style="max-width:200px" />
            </div>
            <div class="px-4 py-5 flex items-center justify-center h-16 border-b border-gray-700">
                <a href="/admin" class="text-xl font-bold text-white sidebar-item-text">Admin Panel</a>
                <a href="/admin" class="text-xl font-bold text-white sidebar-icon hidden sidebar-collapsed-only">AP</a>
            </div>
            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                <a href="/admin" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l7 7m-7-7v10a1 1 0 01-1 1H5a1 1 0 01-1-1v-10h16z"/></svg>
                    <span class="sidebar-item-text">Dashboard</span>
                </a>
                <?php if (Auth::hasPermission('view_members')): ?>
                <a href="/admin/members" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg  style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-4v-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2H3a2 2 0 00-2 2v2a2 2 0 002 2h18a2 2 0 002-2v-2a2 2 0 00-2-2zM4 12a2 2 0 100-4h16a2 2 0 100 4H4zM12 11V3a1 1 0 00-1-1H7a1 1 0 00-1 1v8h6z"/></svg>
                    <span class="sidebar-item-text">Members</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::hasPermission('view_subscriptions')): ?>
                <a href="/admin/subscriptions" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 4v4m-4 8h8a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="sidebar-item-text">Subscriptions</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::hasPermission('view_classes')): ?>
                <a href="/admin/classes" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span class="sidebar-item-text">Classes</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::hasPermission('view_payments')): ?>
                <a href="/admin/payments" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    <span class="sidebar-item-text">Payments</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::hasPermission('view_member_messages')): ?>
                <a href="/admin/messages" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="sidebar-item-text">Messages</span>
                </a>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_users')): ?>
                <a href="/admin/users" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-4v-2a2 2 0 00-2-2H9a2 2 0 00-2 2v2H3a2 2 0 00-2 2v2a2 2 0 002 2h18a2 2 0 002-2v-2a2 2 0 00-2-2zM4 12a2 2 0 100-4h16a2 2 0 100 4H4zM12 11V3a1 1 0 00-1-1H7a1 1 0 00-1 1v8h6z"/></svg>
                    <span class="sidebar-item-text">Admin Users</span>
                </a>
                <?php endif; ?>


				<?php if (\App\Core\Auth::hasPermission('manage_roles')): ?>
					<a href="/admin/roles" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
						<!-- Shield Icon for Roles/Permissions -->
						<svg class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
						</svg>
						<span class="sidebar-item-text">Roles & Permissions</span>
					</a>
				<?php endif; ?>


                <?php if (Auth::hasPermission('manage_settings')): ?>
                <a href="/admin/settings" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.827 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.827 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.827-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.827-3.31 2.37-2.37.576.354 1.29.573 2.05.626v.001z"/></svg>
                    <span class="sidebar-item-text">Settings</span>
                </a>
                <?php endif; ?>
            </nav>
            <div class="px-3 py-4 border-t border-gray-700">
                <a href="/logout" class="flex items-center space-x-3 px-3 py-2 rounded-md hover:bg-gray-700 text-gray-300 hover:text-white group">
                    <svg style="width:25px;height:auto;" class="h-6 w-6 sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="sidebar-item-text">Logout</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="w-full bg-white shadow-sm lg:static lg:overflow-y-visible h-16 flex items-center justify-between px-4 sm:px-6 z-40">
                <button id="sidebarToggle" class="text-gray-500 hover:text-gray-900 lg:hidden focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <svg style="width:25px;height:auto;" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex-1 flex justify-between items-center">
                    <h1 class="text-xl font-semibold text-gray-900">
                        <?php if (isset($loggedInAdmin)): ?>
                            Admin, <?= esc($loggedInAdmin['first_name']) ?>!
                        <?php else: ?>
                            Admin Panel
                        <?php endif; ?>
                    </h1>
                    <div class="flex items-center ml-auto">
                        <div class="relative ml-3">
                            <div>
								<button type="button" class="max-w-xs bg-white rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
    <span class="sr-only">Open user menu</span>
    <svg class="h-8 w-8 rounded-full text-gray-500 bg-gray-200" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
</button>
								
                            </div>
                            <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" id="user-dropdown">
                                <a href="/admin/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>
                                <a href="/admin/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-menu-item-1">Settings</a>
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
        let sidebarOverlay = null; // To manage mobile overlay

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('expanded');
            if (window.innerWidth < 1024) { // Only for mobile/tablet
                if (sidebar.classList.contains('expanded')) {
                    createOverlay();
                } else {
                    removeOverlay();
                }
            }
        }

        function createOverlay() {
            if (!sidebarOverlay) {
                sidebarOverlay = document.createElement('div');
                sidebarOverlay.classList.add('sidebar-overlay');
                document.body.appendChild(sidebarOverlay);
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
        }

        function removeOverlay() {
            if (sidebarOverlay) {
                sidebarOverlay.removeEventListener('click', toggleSidebar);
                sidebarOverlay.parentNode.removeChild(sidebarOverlay);
                sidebarOverlay = null;
            }
        }

        // Initial state for desktop based on screen width
        if (window.innerWidth >= 1024) { // Tailwind's 'lg' breakpoint
            sidebar.classList.add('expanded');
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('fixed');
            sidebar.classList.add('relative');
        } else {
            sidebar.classList.add('collapsed');
            sidebar.classList.remove('expanded');
            sidebar.classList.add('fixed');
            sidebar.classList.remove('relative');
        }

        sidebarToggle.addEventListener('click', toggleSidebar);

        userMenuButton.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent document click from closing it immediately
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', (event) => {
            if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Handle resize: ensure correct sidebar behavior
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.add('expanded');
                sidebar.classList.remove('collapsed');
                sidebar.classList.remove('fixed');
                sidebar.classList.add('relative');
                removeOverlay(); // Remove overlay if transitioning to desktop
            } else {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('expanded');
                sidebar.classList.add('fixed');
                sidebar.classList.remove('relative');
                // Don't create overlay here, only on toggle click on mobile
            }
        });
    </script>
</body>
</html>
