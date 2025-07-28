<?php
// app/views/admin/payments/index.php
 require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\Helpers\functions as Helpers; ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">All Payments</h1>

<?php displayFlashMessages(); ?>

<?php if (empty($payments)): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
        <p class="font-bold">No Payments Recorded</p>
        <p>There are no payment records in the system.</p>
    </div>
<?php else: ?>
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="min-w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Member
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Method
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= (new DateTime($payment['payment_date']))->format('M j, Y H:i') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="/admin/members/<?= esc($payment['member_id']) ?>" class="text-indigo-600 hover:text-indigo-900">
                                    <?= esc($payment['first_name']) ?> <?= esc($payment['last_name']) ?>
                                </a>
                                <span class="block text-xs text-gray-400"><?= esc($payment['member_email']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                &pound;<?= esc(number_format($payment['amount'], 2)) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= esc($payment['description']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php
                                        switch ($payment['status']) {
                                            case 'succeeded': echo 'bg-green-100 text-green-800'; break;
                                            case 'failed': echo 'bg-red-100 text-red-800'; break;
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'refunded': echo 'bg-gray-100 text-gray-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800'; break;
                                        }
                                    ?>">
                                    <?= esc(ucfirst($payment['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= esc(ucfirst(str_replace('_', ' ', $payment['payment_gateway']))) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <?php if (\App\Core\Auth::hasPermission('manage_payments')): ?>
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">View/Refund</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php end_section(); ?>
