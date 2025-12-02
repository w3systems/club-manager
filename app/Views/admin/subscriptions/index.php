<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
            <p class="text-gray-600">Manage the types of memberships you offer.</p>
        </div>
        <a href="<?= $this->url('admin/subscriptions/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Add Plan
        </a>
    </div>

    <?php $this->component('alerts'); ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($subscriptions)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No subscription plans found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($sub->name) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge <?= $sub->status === 'active' ? 'badge-success' : 'badge-gray' ?>">
                                        <?= htmlspecialchars(ucfirst($sub->status)) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">£<?= number_format($sub->price, 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $sub->type))) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php if ($sub->type === 'session_based'): ?>
                                        <?= htmlspecialchars($sub->term_length) ?> Sessions
                                    <?php else: ?>
                                        <?= htmlspecialchars($sub->term_length . ' ' . ucfirst($sub->term_unit)) ?><?= $sub->term_length > 1 ? 's' : '' ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= $this->url('admin/subscriptions/' . $sub->id . '/edit') ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit Plan"><i class="fas fa-edit"></i></a>
                                        <form action="<?= $this->url('admin/subscriptions/' . $sub->id . '/delete') ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?');" class="inline">
                                            <?= $this->csrf() ?>
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Plan"><i class="fas fa-trash"></i></button>
                                        </form>
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