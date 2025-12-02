<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Application Settings</h1>
        <p class="text-gray-600">Manage global configuration for your club.</p>
    </div>

    <?php $this->component('alerts'); ?>

    <form action="<?= $this->url('admin/settings') ?>" method="POST" enctype="multipart/form-data">
        <?= $this->csrf() ?>

        <div x-data="{ open: true }" class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 cursor-pointer" @click="open = !open">
                <div class="flex justify-between items-center"><h3 class="text-lg font-medium text-gray-900">Site Settings</h3><i class="fas" :class="{ 'fa-chevron-up': !open, 'fa-chevron-down': open }"></i></div>
            </div>
            <div x-show="open" x-transition class="p-6 space-y-4">
                <div>
                    <label for="setting-app_name" class="form-label">Club Name</label>
                    <input type="text" id="setting-app_name" name="app_name" class="form-input" value="<?= htmlspecialchars($settings['app_name'] ?? '') ?>" placeholder="Your Club Name">
                </div>
                <div>
                    <label for="setting-site_color_primary" class="form-label">Primary Color</label>
                    <input type="color" id="setting-site_color_primary" name="site_color_primary" class="h-10 w-20 p-1 border rounded-md" value="<?= htmlspecialchars($settings['site_color_primary'] ?? '#971b1e') ?>">
                </div>
                 <div>
                    <label for="setting-site_color_secondary" class="form-label">Secondary Color</label>
                    <input type="color" id="setting-site_color_secondary" name="site_color_secondary" class="h-10 w-20 p-1 border rounded-md" value="<?= htmlspecialchars($settings['site_color_secondary'] ?? '#cda22d') ?>">
                </div>
            </div>
        </div>

        <div x-data="{ open: true }" class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 cursor-pointer" @click="open = !open">
                <div class="flex justify-between items-center"><h3 class="text-lg font-medium text-gray-900">Site Identity</h3><i class="fas" :class="{ 'fa-chevron-up': !open, 'fa-chevron-down': open }"></i></div>
            </div>
            <div x-show="open" x-transition class="p-6 space-y-6">
                <div>
                    <label for="app_logo" class="form-label">Site Logo</label>
                    <?php if (!empty($envSettings['APP_LOGO_PATH']['value'])): ?>
                        <img src="<?= htmlspecialchars($envSettings['APP_LOGO_PATH']['value']) ?>" alt="Current Logo" class="h-12 bg-gray-200 p-1 rounded mb-2">
                    <?php endif; ?>
                    <input type="file" id="app_logo" name="app_logo" class="form-input">
                    <p class="form-help">Upload a new logo (e.g., PNG, SVG). Leave blank to keep the current one.</p>
                </div>
                <div>
                    <label for="app_favicon" class="form-label">Site Favicon</label>
                     <?php if (!empty($envSettings['APP_FAVICON_PATH']['value'])): ?>
                        <img src="<?= htmlspecialchars($envSettings['APP_FAVICON_PATH']['value']) ?>" alt="Current Favicon" class="h-8 w-8 bg-gray-200 p-1 rounded mb-2">
                    <?php endif; ?>
                    <input type="file" id="app_favicon" name="app_favicon" class="form-input">
                    <p class="form-help">Upload a new favicon (e.g., ICO, PNG). Leave blank to keep the current one.</p>
                </div>
            </div>
        </div>
        
        <div x-data="{ open: true }" class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 cursor-pointer" @click="open = !open">
                <div class="flex justify-between items-center"><h3 class="text-lg font-medium text-gray-900">API Keys & Environment</h3><i class="fas" :class="{ 'fa-chevron-up': !open, 'fa-chevron-down': open }"></i></div>
            </div>
            <div x-show="open" x-transition class="p-6 space-y-4">
                <p class="text-sm text-gray-600 bg-yellow-50 p-3 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    <strong>Warning:</strong> Changing these values can break your application. Only enter a new value to update a key. Leave fields blank to keep existing values.
                </p>
                <?php foreach ($envSettings as $key => $setting): ?>
                    <?php if ($key !== 'APP_LOGO_PATH' && $key !== 'APP_FAVICON_PATH'): ?>
                    <div>
                        <label for="env-<?= htmlspecialchars($key) ?>" class="form-label"><?= htmlspecialchars($key) ?></label>
                        <input type="password" id="env-<?= htmlspecialchars($key) ?>" name="env_<?= htmlspecialchars($key) ?>" class="form-input" placeholder="<?= htmlspecialchars($setting['placeholder']) ?>">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-2"></i>
                Save All Settings
            </button>
        </div>
    </form>
</div>