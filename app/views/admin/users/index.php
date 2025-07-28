<?php
// app/views/admin/users/index.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\Helpers\functions as Helpers; ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Admin Users</h1>

<?php displayFlashMessages();  displayErrors(); ?>

<?php if (\App\Core\Auth::hasPermission('manage_users')): ?>
    <div class="mb-8 bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">Add New Admin User</h2>
        <form action="/admin/users/create" method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="first_name" id="first_name" required class="form-input" value="<?= old('first_name') ?>">
                    <?php displayErrors('first_name'); ?>
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required class="form-input" value="<?= old('last_name') ?>">
                    <?php displayErrors('last_name'); ?>
                </div>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" required class="form-input" value="<?= old('email') ?>">
                <?php displayErrors('email'); ?>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required class="form-input">
                    <?php displayErrors('password'); ?>
                </div>
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" required class="form-input">
                    <?php displayErrors('password_confirm'); ?>
                </div>
            </div>
            <div>
                <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile (Optional)</label>
                <input type="tel" name="mobile" id="mobile" class="form-input" value="<?= old('mobile') ?>">
                <?php displayErrors('mobile'); ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Assign Roles</label>
                <p class="mt-1 text-sm text-gray-500">Select one or more roles for this admin user.</p>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                    <?php foreach ($roles as $role): ?>
                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="role_<?= esc($role['id']) ?>" name="role_ids[]" type="checkbox" value="<?= esc($role['id']) ?>"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                       <?= in_array($role['id'], old('role_ids', [])) ? 'checked' : '' ?>>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="role_<?= esc($role['id']) ?>" class="font-medium text-gray-700"><?= esc($role['name']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php displayErrors('role_ids'); ?>
            </div>

            <div class="pt-5">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Admin User
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<style>
    .form-input {
        @apply mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm;
    }
</style>

<div class="mt-8">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Existing Admin Users</h2>
    <?php if (empty($admins)): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p class="font-bold">No Admin Users Found</p>
            <p>Use the form above to add your first admin user.</p>
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
                                Roles
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= esc($admin['first_name']) ?> <?= esc($admin['last_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc($admin['email']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= esc($admin['roles'] ?? 'None') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if (\App\Core\Auth::hasPermission('manage_users')): ?>
                                        <a href="/admin/users/edit/<?= esc($admin['id']) ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
										<?php 
											$currentUser = \App\Core\Auth::user();
											// Check that we have a user AND that their ID is not the same as the admin in the loop
											if ($currentUser && $currentUser['id'] !== $admin['id']):
										?>
                                        <button type="button" class="ml-4 text-red-600 hover:text-red-900" onclick="confirmDeleteAdmin(<?= esc($admin['id']) ?>)">Delete</button>
										<?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function confirmDeleteAdmin(id) {
        if (confirm('Are you sure you want to delete this admin user? This action cannot be undone.')) {
            // Implement actual delete form submission or AJAX call
            alert('Delete functionality for admin ID: ' + id + ' to be implemented.');
            // Example:
            // const form = document.createElement('form');
            // form.method = 'POST';
            // form.action = '/admin/users/delete/' + id;
            // document.body.appendChild(form);
            // form.submit();
        }
    }
</script>

<?php end_section(); ?>
