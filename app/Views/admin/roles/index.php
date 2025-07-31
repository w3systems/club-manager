<div class="space-y-8">
    <!-- Page header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions Management</h1>
        <p class="text-gray-600">Manage system roles, permissions, and their assignments</p>
    </div>

    <!-- Roles Management Section -->
    <div class="bg-white rounded-lg shadow grid">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">System Roles</h3>
                <button onclick="addNewRole()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>
                    Add Role
                </button>
            </div>
        </div>
        <div class="divide-y divide-gray-200">
            <?php foreach ($roles as $role): ?>
                <div class="px-6 py-4" data-role-id="<?= $role['id'] ?>">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 mr-4">
                            <div class="role-display-<?= $role['id'] ?>">
                                <div class="flex items-center space-x-3">
                                    <h4 class="text-md font-medium text-gray-900">
                                        <?= htmlspecialchars($role['name']) ?>
                                        <?php if ($role['name'] === 'Super Admin'): ?>
                                            <span class="badge bg-purple-100 text-purple-800 ml-2">
                                                <i class="fas fa-crown mr-1"></i>
                                                System
                                            </span>
                                        <?php endif; ?>
                                    </h4>
                                    <div class="text-sm text-gray-500">
                                        <?= $role['permission_count'] ?> permissions • <?= $role['user_count'] ?> users
                                    </div>
                                </div>
                                <?php if (!empty($role['description'])): ?>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?= htmlspecialchars($role['description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="role-edit-<?= $role['id'] ?> hidden">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <input 
                                            type="text" 
                                            class="form-input text-sm" 
                                            value="<?= htmlspecialchars($role['name']) ?>"
                                            data-field="name"
                                            <?= $role['name'] === 'Super Admin' ? 'readonly' : '' ?>
                                            placeholder="Role name"
                                        >
                                    </div>
                                    <div>
                                        <input 
                                            type="text" 
                                            class="form-input text-sm" 
                                            value="<?= htmlspecialchars($role['description'] ?? '') ?>"
                                            data-field="description"
                                            placeholder="Role description"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="role-actions-<?= $role['id'] ?>">
                                <?php if ($role['name'] !== 'Super Admin'): ?>
                                    <button onclick="editRole(<?= $role['id'] ?>)" class="text-blue-600 hover:text-blue-900" title="Edit Role">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteRole(<?= $role['id'] ?>)" class="text-red-600 hover:text-red-900 ml-2" title="Delete Role">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="role-edit-actions-<?= $role['id'] ?> hidden">
                                <button onclick="saveRole(<?= $role['id'] ?>)" class="text-green-600 hover:text-green-900" title="Save">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="cancelEditRole(<?= $role['id'] ?>)" class="text-gray-600 hover:text-gray-900 ml-2" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- New role form (hidden by default) -->
            <div id="new-role-form" class="px-6 py-4 bg-blue-50 hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <input 
                            type="text" 
                            id="new-role-name"
                            class="form-input text-sm" 
                            placeholder="Role name"
                        >
                    </div>
                    <div>
                        <input 
                            type="text" 
                            id="new-role-description"
                            class="form-input text-sm" 
                            placeholder="Role description"
                        >
                    </div>
                </div>
                <div class="mt-3 flex justify-end space-x-2">
                    <button onclick="saveNewRole()" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i>
                        Save Role
                    </button>
                    <button onclick="cancelNewRole()" class="btn btn-secondary btn-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Management Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">System Permissions</h3>
                <button onclick="addNewPermission()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>
                    Add Permission
                </button>
            </div>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (!empty($permissionGroups)): ?>
                <?php foreach ($permissionGroups as $groupName => $groupPermissions): ?>
                    <div class="px-6 py-4">
                        <h4 class="text-md font-medium text-gray-800 capitalize mb-3">
                            <?= ucfirst($groupName) ?> Permissions
                        </h4>
                        <div class="space-y-2">
                            <?php foreach ($groupPermissions as $permission): ?>
                                <div class="flex items-center justify-between py-2" data-permission-id="<?= $permission['id'] ?>">
                                    <div class="flex-1 mr-4">
                                        <div class="permission-display-<?= $permission['id'] ?>">
                                            <div class="flex items-center space-x-3">
                                                <span class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($permission['name']) ?>
                                                </span>
                                                <?php if (in_array($permission['name'], ['manage_all', 'manage_roles'])): ?>
                                                    <span class="badge bg-red-100 text-red-800">
                                                        <i class="fas fa-shield-alt mr-1"></i>
                                                        Critical
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($permission['description'])): ?>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?= htmlspecialchars($permission['description']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="permission-edit-<?= $permission['id'] ?> hidden">
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                                <div>
                                                    <input 
                                                        type="text" 
                                                        class="form-input text-sm" 
                                                        value="<?= htmlspecialchars($permission['name']) ?>"
                                                        data-field="name"
                                                        <?= in_array($permission['name'], ['manage_all', 'manage_roles']) ? 'readonly' : '' ?>
                                                        placeholder="Permission name"
                                                    >
                                                </div>
                                                <div>
                                                    <input 
                                                        type="text" 
                                                        class="form-input text-sm" 
                                                        value="<?= htmlspecialchars($permission['description'] ?? '') ?>"
                                                        data-field="description"
                                                        placeholder="Permission description"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="permission-actions-<?= $permission['id'] ?>">
                                            <button onclick="editPermission(<?= $permission['id'] ?>)" class="text-blue-600 hover:text-blue-900" title="Edit Permission">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (!in_array($permission['name'], ['manage_all', 'manage_roles'])): ?>
                                                <button onclick="deletePermission(<?= $permission['id'] ?>)" class="text-red-600 hover:text-red-900 ml-2" title="Delete Permission">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="permission-edit-actions-<?= $permission['id'] ?> hidden">
                                            <button onclick="savePermission(<?= $permission['id'] ?>)" class="text-green-600 hover:text-green-900" title="Save">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="cancelEditPermission(<?= $permission['id'] ?>)" class="text-gray-600 hover:text-gray-900 ml-2" title="Cancel">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- New permission form (hidden by default) -->
            <div id="new-permission-form" class="px-6 py-4 bg-blue-50 hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <input 
                            type="text" 
                            id="new-permission-name"
                            class="form-input text-sm" 
                            placeholder="Permission name (e.g., manage_events)"
                        >
                    </div>
                    <div>
                        <input 
                            type="text" 
                            id="new-permission-description"
                            class="form-input text-sm" 
                            placeholder="Permission description"
                        >
                    </div>
                </div>
                <div class="mt-3 flex justify-end space-x-2">
                    <button onclick="saveNewPermission()" class="btn btn-primary btn-sm">
                        <i class="fas fa-save mr-1"></i>
                        Save Permission
                    </button>
                    <button onclick="cancelNewPermission()" class="btn btn-secondary btn-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-Permission Assignment Cards -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Role Permissions Assignment</h3>
                <div class="flex items-center space-x-4">
                    <span id="changes-indicator" class="hidden text-amber-600 font-medium">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Unsaved changes
                    </span>
                    <button onclick="saveAllPermissions()" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Save All Changes
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($roles as $role): ?>
                    <div class="border border-gray-200 rounded-lg overflow-hidden" data-role-id="<?= $role['id'] ?>">
                        <!-- Role Header -->
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-md font-semibold text-gray-900">
                                    <?= htmlspecialchars($role['name']) ?>
                                </h4>
                                <?php if ($role['name'] === 'Super Admin'): ?>
                                    <span class="badge bg-purple-100 text-purple-800">
                                        <i class="fas fa-crown mr-1"></i>
                                        System
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?= $role['permission_count'] ?> permissions assigned
                            </div>
                        </div>
                        
                        <!-- Permissions List -->
                        <div class="p-4">
                            <?php if ($role['name'] === 'Super Admin'): ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-crown text-purple-500 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600">
                                        Super Admin automatically<br>has all permissions
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                    <?php if (!empty($permissionGroups)): ?>
                                        <?php foreach ($permissionGroups as $groupName => $groupPermissions): ?>
                                            <div class="mb-4">
                                                <h5 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2 sticky top-0 bg-white">
                                                    <?= ucfirst($groupName) ?>
                                                </h5>
                                                <div class="space-y-1">
                                                    <?php foreach ($groupPermissions as $permission): ?>
                                                        <?php
                                                        $rolePermissions = $rolePermissionMatrix[$role['id']] ?? [];
                                                        $isChecked = in_array($permission['id'], $rolePermissions);
                                                        ?>
                                                        <label class="flex items-start space-x-2 text-sm cursor-pointer hover:bg-gray-50 rounded px-2 py-1">
                                                            <input 
                                                                type="checkbox" 
                                                                class="mt-0.5 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 flex-shrink-0"
                                                                data-role-id="<?= $role['id'] ?>"
                                                                data-permission-id="<?= $permission['id'] ?>"
                                                                <?= $isChecked ? 'checked' : '' ?>
                                                                onchange="markChanged()"
                                                            >
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-sm text-gray-900 leading-tight">
                                                                    <?= htmlspecialchars($permission['name']) ?>
                                                                </div>
                                                                <?php if (!empty($permission['description'])): ?>
                                                                    <div class="text-xs text-gray-500 leading-tight mt-0.5">
                                                                        <?= htmlspecialchars($permission['description']) ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Quick Actions -->
                                <div class="mt-4 pt-3 border-t border-gray-200">
                                    <div class="flex justify-between text-xs">
                                        <button 
                                            type="button" 
                                            onclick="selectAllForRole(<?= $role['id'] ?>)"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            Select All
                                        </button>
                                        <button 
                                            type="button" 
                                            onclick="clearAllForRole(<?= $role['id'] ?>)"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            Clear All
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-sm text-gray-500">
            <div class="flex items-center justify-between">
                <span>Check permissions for each role, then click "Save All Changes"</span>
                <div class="text-xs">
                    <span class="text-gray-400">Tip:</span> Use "Select All" / "Clear All" for quick setup
                </div>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Quick Help</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Roles:</strong> Click edit icon to modify role name/description</li>
                        <li><strong>Permissions:</strong> Use standard naming (manage_*, view_*, edit_*, send_*)</li>
                        <li><strong>Assignment:</strong> Check/uncheck boxes in the matrix, then click "Save All Changes"</li>
                        <li><strong>Super Admin:</strong> Automatically has all permissions and cannot be modified</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let hasUnsavedChanges = false;

// Role Management Functions
function addNewRole() {
    document.getElementById('new-role-form').classList.remove('hidden');
    document.getElementById('new-role-name').focus();
}

function cancelNewRole() {
    document.getElementById('new-role-form').classList.add('hidden');
    document.getElementById('new-role-name').value = '';
    document.getElementById('new-role-description').value = '';
}

function saveNewRole() {
    const name = document.getElementById('new-role-name').value.trim();
    const description = document.getElementById('new-role-description').value.trim();
    
    if (!name) {
        showError('Role name is required');
        return;
    }
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', description);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch('/admin/roles/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showError(data.message || 'Error creating role');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error creating role');
    });
}

function editRole(roleId) {
    document.querySelector(`.role-display-${roleId}`).classList.add('hidden');
    document.querySelector(`.role-edit-${roleId}`).classList.remove('hidden');
    document.querySelector(`.role-actions-${roleId}`).classList.add('hidden');
    document.querySelector(`.role-edit-actions-${roleId}`).classList.remove('hidden');
}

function cancelEditRole(roleId) {
    document.querySelector(`.role-display-${roleId}`).classList.remove('hidden');
    document.querySelector(`.role-edit-${roleId}`).classList.add('hidden');
    document.querySelector(`.role-actions-${roleId}`).classList.remove('hidden');
    document.querySelector(`.role-edit-actions-${roleId}`).classList.add('hidden');
}

function saveRole(roleId) {
    const editForm = document.querySelector(`.role-edit-${roleId}`);
    const name = editForm.querySelector('[data-field="name"]').value.trim();
    const description = editForm.querySelector('[data-field="description"]').value.trim();
    
    if (!name) {
        showError('Role name is required');
        return;
    }
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', description);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch(`/admin/roles/${roleId}/update`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showError(data.message || 'Error updating role');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error updating role');
    });
}

function deleteRole(roleId) {
    if (!confirm('Are you sure you want to delete this role?')) return;
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    fetch(`/admin/roles/${roleId}/delete`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showError(data.message || 'Error deleting role');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error deleting role');
    });
}

// Permission Management Functions
function addNewPermission() {
    document.getElementById('new-permission-form').classList.remove('hidden');
    document.getElementById('new-permission-name').focus();
}

function cancelNewPermission() {
    document.getElementById('new-permission-form').classList.add('hidden');
    document.getElementById('new-permission-name').value = '';
    document.getElementById('new-permission-description').value = '';
}

function saveNewPermission() {
    const name = document.getElementById('new-permission-name').value.trim();
    const description = document.getElementById('new-permission-description').value.trim();
    
    if (!name) {
        alert('Permission name is required');
        return;
    }
    
    fetch('/admin/permissions/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: `name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error creating permission');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating permission');
    });
}

function editPermission(permissionId) {
    document.querySelector(`.permission-display-${permissionId}`).classList.add('hidden');
    document.querySelector(`.permission-edit-${permissionId}`).classList.remove('hidden');
    document.querySelector(`.permission-actions-${permissionId}`).classList.add('hidden');
    document.querySelector(`.permission-edit-actions-${permissionId}`).classList.remove('hidden');
}

function cancelEditPermission(permissionId) {
    document.querySelector(`.permission-display-${permissionId}`).classList.remove('hidden');
    document.querySelector(`.permission-edit-${permissionId}`).classList.add('hidden');
    document.querySelector(`.permission-actions-${permissionId}`).classList.remove('hidden');
    document.querySelector(`.permission-edit-actions-${permissionId}`).classList.add('hidden');
}

function savePermission(permissionId) {
    const editForm = document.querySelector(`.permission-edit-${permissionId}`);
    const name = editForm.querySelector('[data-field="name"]').value.trim();
    const description = editForm.querySelector('[data-field="description"]').value.trim();
    
    if (!name) {
        alert('Permission name is required');
        return;
    }
    
    fetch(`/admin/permissions/${permissionId}/update`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: `name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating permission');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating permission');
    });
}

function deletePermission(permissionId) {
    if (!confirm('Are you sure you want to delete this permission?')) return;
    
    fetch(`/admin/permissions/${permissionId}/delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting permission');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting permission');
    });
}

// Permission Matrix Functions
function markChanged() {
    hasUnsavedChanges = true;
    document.getElementById('changes-indicator').classList.remove('hidden');
}

function selectAllForRole(roleId) {
    const checkboxes = document.querySelectorAll(`input[type="checkbox"][data-role-id="${roleId}"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    markChanged();
}

function clearAllForRole(roleId) {
    const checkboxes = document.querySelectorAll(`input[type="checkbox"][data-role-id="${roleId}"]`);
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    markChanged();
}

function saveAllPermissions() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][data-role-id][data-permission-id]');
    const assignments = {};
    
    checkboxes.forEach(checkbox => {
        const roleId = checkbox.dataset.roleId;
        const permissionId = checkbox.dataset.permissionId;
        
        if (!assignments[roleId]) {
            assignments[roleId] = [];
        }
        
        if (checkbox.checked) {
            assignments[roleId].push(permissionId);
        }
    });
    
    const saveBtn = document.querySelector('button[onclick="saveAllPermissions()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    saveBtn.disabled = true;
    
    fetch('/admin/roles/update-permissions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ assignments })
    })
    .then(response => {
        console.log('Bulk update response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Bulk update response data:', data);
        if (data.success) {
            hasUnsavedChanges = false;
            document.getElementById('changes-indicator').classList.add('hidden');
            
            // Show success feedback
            saveBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Saved!';
            saveBtn.classList.remove('btn-primary');
            saveBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            
            // Restore original state after 2 seconds
            setTimeout(() => {
                saveBtn.innerHTML = originalText;
                saveBtn.classList.add('btn-primary');
                saveBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            }, 2000);
        } else {
            showError(data.message || 'Error updating permissions');
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Bulk update error details:', error);
        showError(`Error updating permissions: ${error.message}`);
        saveBtn.innerHTML = originalText;
    })
    .finally(() => {
        saveBtn.disabled = false;
    });
}

// Error and success message functions
function showError(message) {
    showMessage(message, 'error');
}

function showSuccess(message) {
    showMessage(message, 'success');
}

function showMessage(message, type) {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.alert-banner');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create alert banner
    const alertBanner = document.createElement('div');
    alertBanner.className = `alert-banner fixed top-4 right-4 z-50 max-w-sm p-4 rounded-md shadow-lg ${
        type === 'error' 
            ? 'bg-red-50 border border-red-200 text-red-800' 
            : 'bg-green-50 border border-green-200 text-green-800'
    }`;
    
    alertBanner.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} ${
                    type === 'error' ? 'text-red-400' : 'text-green-400'
                }"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                        class="inline-flex ${type === 'error' ? 'text-red-400 hover:text-red-600' : 'text-green-400 hover:text-green-600'}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(alertBanner);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertBanner.parentNode) {
            alertBanner.remove();
        }
    }, 5000);
}

// Warn about unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>