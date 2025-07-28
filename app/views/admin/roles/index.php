<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Roles & Permissions Management</h1>

<?php displayFlashMessages(); ?>

<!-- Create Forms Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
    <!-- Form to Create New Role (Updated) -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">Create New Role</h2>
        <form action="/admin/roles/create" method="POST" class="space-y-4">
            <div>
                <label for="role_name" class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" name="role_name" id="role_name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="role_description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                <input type="text" name="role_description" id="role_description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <button type="submit" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Create Role
            </button>
        </form>
    </div>

    <!-- Form to Create New Permission -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">Create New Permission</h2>
        <form action="/admin/permissions/create" method="POST" class="space-y-4">
            <div>
                <label for="permission_name" class="block text-sm font-medium text-gray-700">Permission Name</label>
                <input type="text" name="permission_name" id="permission_name" required placeholder="e.g., manage_users" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="permission_description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                <input type="text" name="permission_description" id="permission_description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
            </div>
            <button type="submit" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Create Permission
            </button>
        </form>
    </div>
</div>


<!-- Manage Existing Roles and Their Permissions (Updated) -->
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-medium text-gray-900 mb-4">Manage Role Permissions</h2>
    <?php foreach ($roles as $role): ?>
        <form action="/admin/roles/<?= esc($role['id']) ?>/permissions" method="POST">
            <div class="border-t border-gray-200 py-6">
                <h3 class="text-lg font-semibold text-gray-800"><?= esc(ucfirst($role['name'])) ?></h3>
                <?php if (!empty($role['description'])): ?>
                    <p class="text-sm text-gray-500 mt-1"><?= esc($role['description']) ?></p>
                <?php endif; ?>
                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php
                        $rolePermissionIds = !empty($role['permission_ids']) ? explode(',', $role['permission_ids']) : [];
                    ?>
                    <?php foreach ($permissions as $permission): ?>
                        <div class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input id="perm_<?= esc($role['id']) ?>_<?= esc($permission['id']) ?>"
                                       name="permissions[]"
                                       type="checkbox"
                                       value="<?= esc($permission['id']) ?>"
                                       class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                       <?= in_array($permission['id'], $rolePermissionIds) ? 'checked' : '' ?>>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="perm_<?= esc($role['id']) ?>_<?= esc($permission['id']) ?>" class="font-medium text-gray-700" title="<?= esc($permission['description']) ?>">
                                    <?= esc($permission['name']) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-6">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-secondary hover:bg-secondary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary">
                        Update <?= esc(ucfirst($role['name'])) ?> Permissions
                    </button>
                </div>
            </div>
        </form>
    <?php endforeach; ?>
</div>

<?php end_section(); ?>