<?php
// app/views/admin/classes/calendar.php
 //require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\functions as Helpers; 
 ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Class Calendar</h1>

<?php displayFlashMessages(); ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Upcoming Classes & Bookings</h2>

    <?php if (empty($classes)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p class="font-bold">No Classes Scheduled</p>
            <p>There are no upcoming class instances to display. Check class definitions or generate recurring instances.</p>
        </div>
    <?php else: ?>
        <div class="min-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Class Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Capacity
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Booked Members
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= esc($class['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= (new DateTime($class['instance_date_time']))->format('D, M jS, Y \a\t H:i') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php
                                $bookedCount = $class['current_bookings'] ?? 0;
                                $capacityText = $class['capacity'] !== null ? esc($bookedCount) . ' / ' . esc($class['capacity']) : esc($bookedCount) . ' / Unlimited';
                                echo $capacityText;
                                ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php if (!empty($class['booked_members_names'])): ?>
                                    <ul class="list-disc list-inside">
                                        <?php
                                        // Split by semicolon, then display each name
                                        $members = explode(';', $class['booked_members_names']);
                                        foreach ($members as $member_name) {
                                            echo '<li>' . esc($member_name) . '</li>';
                                        }
                                        ?>
                                    </ul>
                                <?php else: ?>
                                    No bookings yet.
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <?php if (\App\Core\Auth::hasPermission('manage_bookings')): ?>
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">Manage Bookings</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php end_section(); ?>
