<?php
// Determine form properties based on whether we are editing an existing admin
$isEdit = isset($admin) && !empty($admin->id);
$formAction = $isEdit ? $this->url('admin/users/' . $admin->id) : $this->url('admin/users');
$pageTitle = $isEdit ? 'Edit Admin User' : 'Add New Admin User';
$pageDescription = $isEdit ? 'Update details for ' . htmlspecialchars($admin->getFullName()) : 'Create a new user with admin panel access.';
$submitText = $isEdit ? 'Update User' : 'Create User';
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= $this->url('admin/users') ?>" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $pageTitle ?></h1>
            <p class="text-gray-600"><?= $pageDescription ?></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg">
        <form method="POST" action="<?= $formAction ?>" class="p-6" id="user-form">
            <?= $this->csrf() ?>
            <?php $this->component('alerts'); ?>

            <div class="form-section">
                <h3 class="form-section-header">User Details</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="first_name" class="form-label form-label-required">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($admin->first_name ?? '') ?>" required class="form-input">
                    </div>
                    <div>
                        <label for="last_name" class="form-label form-label-required">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($admin->last_name ?? '') ?>" required class="form-input">
                    </div>
                    <div class="form-col-span-2">
                        <label for="email" class="form-label form-label-required">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin->email ?? '') ?>" required class="form-input" autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="flex justify-between items-center mb-5 border-b border-gray-200 pb-3">
                    <h3 class="form-section-header" style="margin-bottom: 0; border-bottom: 0;">Password</h3>
                    <button type="button" id="generate-password-btn" class="btn btn-secondary btn-sm">
                        <i class="fas fa-key mr-2"></i>Generate
                    </button>
                </div>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="password" class="form-label <?= !$isEdit ? 'form-label-required' : '' ?>">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="form-input pr-10" <?= !$isEdit ? 'required' : '' ?> autocomplete="new-password">
                            <button type="button" id="toggle-password-btn" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggle-password-icon"></i>
                            </button>
                        </div>
                        <?php if ($isEdit): ?><p class="form-help">Leave blank to keep current password.</p><?php endif; ?>
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label <?= !$isEdit ? 'form-label-required' : '' ?>">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" <?= !$isEdit ? 'required' : '' ?> autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="form-section-header">Assign Roles</h3>
                <div class="space-y-2">
                    <?php if (empty($roles)): ?>
                        <p class="text-gray-500">No roles found. Please <a href="<?= $this->url('admin/roles') ?>" class="text-blue-600 hover:underline">create a role</a> first.</p>
                    <?php else: ?>
                        <?php foreach ($roles as $role): ?>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" name="roles[]" value="<?= $role->id ?>" class="form-checkbox"
                                    <?= in_array($role->id, $adminRoles ?? []) ? 'checked' : '' ?>>
                                <span class="text-sm text-gray-800"><?= htmlspecialchars($role->name) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= $this->url('admin/users') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $submitText ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('user-form');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const generateBtn = document.getElementById('generate-password-btn');
    const toggleBtn = document.getElementById('toggle-password-btn');
    const toggleIcon = document.getElementById('toggle-password-icon');

    // Password mismatch validation
    form.addEventListener('submit', function(event) {
        if (passwordInput.value !== '' && passwordInput.value !== passwordConfirmInput.value) {
            event.preventDefault();
            if (window.ClubManager && typeof window.ClubManager.toast === 'function') {
                window.ClubManager.toast('Passwords do not match. Please try again.', 'error');
            } else {
                alert('Passwords do not match. Please try again.');
            }
            passwordInput.classList.add('border-red-500');
            passwordConfirmInput.classList.add('border-red-500');
        }
    });

    // Password Generator
    generateBtn.addEventListener('click', function() {
        const length = 12;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~";
        let password = "";
        for (let i = 0; i < length; ++i) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        passwordInput.value = password;
        passwordConfirmInput.value = password;
        
        // **NEW**: Automatically show the password
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');

        if (window.ClubManager) {
            window.ClubManager.toast('New password generated and filled.', 'success');
        }
    });

    // **NEW**: Show/Hide password toggle
    toggleBtn.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('fa-eye', !isPassword);
        toggleIcon.classList.toggle('fa-eye-slash', isPassword);
    });

    // Remove error highlight on input
    function removeErrorHighlight() {
        passwordInput.classList.remove('border-red-500');
        passwordConfirmInput.classList.remove('border-red-500');
    }
    passwordInput.addEventListener('input', removeErrorHighlight);
    passwordConfirmInput.addEventListener('input', removeErrorHighlight);
});
</script>