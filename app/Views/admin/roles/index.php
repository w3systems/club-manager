<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions Management</h1>
        <p class="text-gray-600">Manage system roles, permissions, and their assignments.</p>
    </div>

    <?php //$this->component('alerts'); ?>

    <div x-data="{ open: true }" class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 cursor-pointer" @click="open = !open">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">System Roles</h3>
                <i class="fas" :class="{ 'fa-chevron-up': !open, 'fa-chevron-down': open }"></i>
            </div>
        </div>
        <div x-show="open" x-transition class="divide-y divide-gray-200">
            <?php foreach ($roles as $role): ?>
                <div class="px-6 py-4">
                    <div id="role-display-<?= $role['id'] ?>" class="flex items-center justify-between">
                        <div>
                            <h4 class="text-md font-medium text-gray-900"><?= htmlspecialchars($role['name']) ?></h4>
                            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($role['description'] ?? 'No description') ?></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <?php if ($role['name'] !== 'Super Admin'): ?>
                                <button onclick="editRole(<?= $role['id'] ?>)" class="text-blue-600 hover:text-blue-900 p-2" title="Edit"><i class="fas fa-edit"></i></button>
                                <form action="<?= $this->url('admin/roles/' . $role['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?');" class="inline">
                                    <?= $this->csrf() ?>
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-2" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="role-edit-<?= $role['id'] ?>" class="hidden">
                        <form action="<?= $this->url('admin/roles/' . $role['id'] . '/update') ?>" method="POST" class="flex items-center gap-2">
                            <?= $this->csrf() ?>
                            <input type="text" name="name" class="form-input text-sm flex-grow" value="<?= htmlspecialchars($role['name']) ?>" required>
                            <input type="text" name="description" class="form-input text-sm flex-grow" value="<?= htmlspecialchars($role['description'] ?? '') ?>" placeholder="Description">
                            <button type="submit" class="text-green-600 hover:text-green-900 p-2" title="Save"><i class="fas fa-check"></i></button>
                            <button type="button" onclick="cancelEditRole(<?= $role['id'] ?>)" class="text-gray-600 hover:text-gray-900 p-2" title="Cancel"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="px-6 py-4 bg-gray-50">
                <form action="<?= $this->url('admin/roles/store') ?>" method="POST" class="flex items-center gap-2">
                    <?= $this->csrf() ?>
                    <input type="text" name="name" class="form-input text-sm flex-grow" placeholder="New Role Name" required>
                    <input type="text" name="description" class="form-input text-sm flex-grow" placeholder="Description (optional)">
                    <button type="submit" class="btn btn-primary btn-sm">Add Role</button>
                </form>
            </div>
        </div>
    </div>
    
    <div x-data="{ open: true }" class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 cursor-pointer" @click="open = !open">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">System Permissions</h3>
                <i class="fas" :class="{ 'fa-chevron-up': !open, 'fa-chevron-down': open }"></i>
            </div>
        </div>
        <div x-show="open" x-transition class="divide-y divide-gray-200">
             <?php foreach ($permissions as $permission): ?>
                <div class="px-6 py-4">
                    <div id="permission-display-<?= $permission['id'] ?>" class="flex items-center justify-between">
                        <div>
                            <h4 class="text-md font-medium text-gray-900"><?= htmlspecialchars($permission['name']) ?></h4>
                            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($permission['description'] ?? 'No description') ?></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="editPermission(<?= $permission['id'] ?>)" class="text-blue-600 hover:text-blue-900 p-2" title="Edit"><i class="fas fa-edit"></i></button>
                            <form action="<?= $this->url('admin/permissions/' . $permission['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                <?= $this->csrf() ?>
                                <button type="submit" class="text-red-600 hover:text-red-900 p-2" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <div id="permission-edit-<?= $permission['id'] ?>" class="hidden">
                        <form action="<?= $this->url('admin/permissions/' . $permission['id'] . '/update') ?>" method="POST" class="flex items-center gap-2">
                            <?= $this->csrf() ?>
                            <input type="text" name="name" class="form-input text-sm flex-grow" value="<?= htmlspecialchars($permission['name']) ?>" required>
                            <input type="text" name="description" class="form-input text-sm flex-grow" value="<?= htmlspecialchars($permission['description'] ?? '') ?>" placeholder="Description">
                            <button type="submit" class="text-green-600 hover:text-green-900 p-2" title="Save"><i class="fas fa-check"></i></button>
                            <button type="button" onclick="cancelEditPermission(<?= $permission['id'] ?>)" class="text-gray-600 hover:text-gray-900 p-2" title="Cancel"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="px-6 py-4 bg-gray-50">
                <form action="<?= $this->url('admin/permissions/store') ?>" method="POST" class="flex items-center gap-2">
                    <?= $this->csrf() ?>
                    <input type="text" name="name" class="form-input text-sm flex-grow" placeholder="new_permission_name" required>
                    <input type="text" name="description" class="form-input text-sm flex-grow" placeholder="Description (e.g., Allows user to...)">
                    <button type="submit" class="btn btn-primary btn-sm">Add Permission</button>
                </form>
            </div>
        </div>
    </div>

    <div x-data="{ open: true }" class="bg-white rounded-lg shadow">
         <div class="px-6 py-4 border-b border-gray-200 cursor-pointer" @click="open = !open">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Permissions Assignment</h3>
                 <i class="fas" :class="{ 'fa-chevron-up': !open, 'fa-chevron-down': open }"></i>
            </div>
        </div>
        <form x-show="open" x-transition action="<?= $this->url('admin/roles/update-permissions') ?>" method="POST">
             <?= $this->csrf() ?>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($roles as $role): ?>
                    <div class="border border-gray-200 rounded-lg">
                        <div class="px-4 py-3 bg-gray-50 border-b">
                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($role['name']) ?></h4>
                        </div>
                        <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                            <?php if ($role['name'] === 'Super Admin'): ?>
                                <p class="text-sm text-gray-500 text-center py-4">Has all permissions by default.</p>
                            <?php else: ?>
                                <?php foreach ($permissionGroups as $groupName => $groupPermissions): ?>
                                    <div>
                                        <h5 class="text-xs font-semibold text-gray-700 uppercase mb-2"><?= htmlspecialchars($groupName) ?></h5>
                                        <?php foreach ($groupPermissions as $permission): ?>
                                            <?php $isChecked = isset($rolePermissionMatrix[$role['id']]) && in_array($permission['id'], $rolePermissionMatrix[$role['id']]); ?>
                                            <label class="flex items-center space-x-2 text-sm">
                                                <input type="checkbox" name="assignments[<?= $role['id'] ?>][]" value="<?= $permission['id'] ?>" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" <?= $isChecked ? 'checked' : '' ?>>
                                                <span><?= htmlspecialchars($permission['name']) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t text-right">
                <button type="submit" class="btn btn-primary">Save Permission Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editRole(roleId) {
    document.getElementById('role-display-' + roleId).classList.add('hidden');
    document.getElementById('role-edit-' + roleId).classList.remove('hidden');
}

function cancelEditRole(roleId) {
    document.getElementById('role-display-' + roleId).classList.remove('hidden');
    document.getElementById('role-edit-' + roleId).classList.add('hidden');
}

function editPermission(permissionId) {
    document.getElementById('permission-display-' + permissionId).classList.add('hidden');
    document.getElementById('permission-edit-' + permissionId).classList.remove('hidden');
}

function cancelEditPermission(permissionId) {
    document.getElementById('permission-display-' + permissionId).classList.remove('hidden');
    document.getElementById('permission-edit-' + permissionId).classList.add('hidden');
}
</script>