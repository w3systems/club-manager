<div class="space-y-6">
    <!-- Page header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Admin Users</h1>
            <p class="text-gray-600">Manage users who can access the admin panel.</p>
        </div>
        <a href="<?= $this->url('admin/users/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Add Admin User
        </a>
    </div>

    <?php //$this->component('alerts'); ?>

    <!-- Users table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($admins)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No admin users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($admin['email']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($admin['roles'] ?? 'No roles assigned') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M j, Y', strtotime($admin['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= $this->url('admin/users/' . $admin['id'] . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit User"><i class="fas fa-edit"></i></a>
                                        <?php if ($auth->id() != $admin['id']): // Prevent self-delete ?>
                                            <form action="<?= $this->url('admin/users/' . $admin['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="inline">
                                                <?= $this->csrf() ?>
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Delete User"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>