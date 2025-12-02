<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Your Profile</h1>
        <p class="text-gray-600">Update your personal details and password.</p>
    </div>

    <div class="bg-white rounded-lg shadow-lg">
        <form method="POST" action="<?= $this->url('admin/profile') ?>" class="p-6" id="profile-form">
            <?= $this->csrf() ?>
            <?php $this->component('alerts'); ?>

            <div class="form-section">
                <h3 class="form-section-header">Personal Details</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="first_name" class="form-label form-label-required">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($admin->first_name ?? '') ?>" required class="form-input">
                    </div>
                    <div>
                        <label for="last_name" class="form-label form-label-required">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($admin->last_name ?? '') ?>" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <div class="form-display"><?= htmlspecialchars($admin->email ?? '') ?></div>
                        <p class="form-help">Your email address cannot be changed.</p>
                    </div>
                    <div>
                        <label class="form-label">Your Role(s)</label>
                        <div class="form-display font-medium"><?= htmlspecialchars($roleNames) ?></div>
                        <p class="form-help">Roles are managed by a Super Admin.</p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="flex justify-between items-center mb-5 border-b border-gray-200 pb-3">
                    <h3 class="form-section-header" style="margin-bottom: 0; border-bottom: 0;">Change Password</h3>
                    <button type="button" id="generate-password-btn" class="btn btn-secondary btn-sm">
                        <i class="fas fa-key mr-2"></i>Generate
                    </button>
                </div>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="password" class="form-label">New Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" class="form-input pr-10" autocomplete="new-password">
                            <button type="button" id="toggle-password-btn" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggle-password-icon"></i>
                            </button>
                        </div>
                        <p class="form-help">Leave blank to keep your current password.</p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profile-form');
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
        
        // Automatically show the generated password
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');

        if (window.ClubManager) {
            window.ClubManager.toast('New password generated and filled.', 'success');
        }
    });

    // Show/Hide password toggle
    toggleBtn.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleIcon.classList.toggle('fa-eye', !isPassword);
        toggleIcon.classList.toggle('fa-eye-slash', isPassword);
    });
});
</script>