<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Edit Admin User: <?= esc($admin['first_name'] . ' ' . $admin['last_name']) ?></h1>

<div class="bg-white shadow rounded-lg p-6">
    <form action="/admin/users/update/<?= esc($admin['id']) ?>" method="POST" class="space-y-6">
        
        <!-- User Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" name="first_name" id="first_name" required value="<?= esc($admin['first_name']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" name="last_name" id="last_name" required value="<?= esc($admin['last_name']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" required value="<?= esc($admin['email']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
            </div>
            <div>
                <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile (Optional)</label>
                <input type="text" name="mobile" id="mobile" value="<?= esc($admin['mobile'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
            </div>
        </div>

        <!-- Role Assignment -->
        <div>
            <label for="role_ids" class="block text-sm font-medium text-gray-700">Roles</label>
            <select name="role_ids[]" id="role_ids" multiple class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" style="height: 150px;">
                <?php foreach ($roles as $role): ?>
                    <option value="<?= esc($role['id']) ?>" <?= in_array($role['id'], $assignedRoleIds) ? 'selected' : '' ?>>
                        <?= esc(ucfirst($role['name'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="mt-1 text-sm text-gray-500">Hold Ctrl (or Cmd on Mac) to select multiple roles.</p>
        </div>

        <!-- Password Update -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
            <p class="mt-1 text-sm text-gray-500">Leave these fields blank to keep the current password.</p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pt-6">
            <a href="/admin/users" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark">
                Update User
            </button>
        </div>
    </form>
</div>

<?php end_section(); ?>