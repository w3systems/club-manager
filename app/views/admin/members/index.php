<?php
// app/views/admin/members/index.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\functions as Helpers; ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">All Members</h1>

<?php displayFlashMessages(); ?>

<?php if (empty($members)): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
        <p class="font-bold">No Members Found</p>
        <p>There are currently no registered members.</p>
    </div>
<?php else: ?>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="min-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Mobile
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date of Birth
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= esc($member['first_name']) ?> <?= esc($member['last_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= esc($member['email']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= esc($member['mobile']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= (new DateTime($member['date_of_birth']))->format('M j, Y') ?> (<?= calculateAge($member['date_of_birth']) ?> yrs)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="/admin/members/<?= esc($member['id']) ?>" class="text-indigo-600 hover:text-indigo-900">View Profile</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php end_section(); ?>
