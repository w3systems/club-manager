<?php
// app/views/admin/messages/index.php
// require_once VIEW_PATH . '/admin/layouts/admin.php';  use App\Helpers\functions as Helpers;
 ?>

<?php start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Messages</h1>

<?php displayFlashMessages(); ?>

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-xl font-medium text-gray-900 mb-4">All Member-Admin Conversations</h2>
        <?php if (!empty($messages)): ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($messages as $message):
                    $senderName = '';
                    $isUnread = false;
                    $messageClass = '';

                    if ($message['type'] === 'member_to_admin') {
                        $senderName = esc($message['sender_member_first_name'] . ' ' . $message['sender_member_last_name']);
                        $isUnread = !$message['is_read_by_recipient'];
                        $messageClass = 'bg-blue-50'; // Highlight member message
                    } elseif ($message['type'] === 'admin_to_member') {
                        $senderName = 'You (' . esc($message['sender_admin_first_name'] . ' ' . $message['sender_admin_last_name']) . ')';
                        $messageClass = 'bg-gray-50'; // Admin's own message
                    }
                ?>
                    <li class="py-4 px-2 hover:bg-gray-100 <?= $messageClass ?> <?= $isUnread ? 'font-semibold' : '' ?>">
                        <div class="flex justify-between items-center mb-1">
                            <p class="text-sm">
                                <span class="<?= $isUnread ? 'text-indigo-700' : 'text-gray-600' ?>"><?= $senderName ?>:</span>
                                <?= esc(substr($message['content'], 0, 150)) ?><?= strlen($message['content']) > 150 ? '...' : '' ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?= (new DateTime($message['created_at']))->format('M j, Y H:i') ?>
                            </p>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <div>
                                <?php if ($isUnread): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">New</span>
                                <?php endif; ?>
                                <span class="text-gray-500 ml-2">
                                    To:
                                    <?php if ($message['recipient_member_id']): ?>
                                        <?= esc($message['recipient_member_first_name'] . ' ' . $message['recipient_member_last_name']) ?> (Member)
                                    <?php elseif ($message['recipient_admin_id']): ?>
                                        Admin
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if (\App\Core\Auth::hasPermission('view_members') && $message['sender_member_id']): ?>
                                <a href="/admin/members/<?= esc($message['sender_member_id']) ?>#messages" class="text-indigo-600 hover:text-indigo-900">View Conversation</a>
                            <?php elseif (\App\Core\Auth::hasPermission('view_members') && $message['recipient_member_id']): ?>
                                <a href="/admin/members/<?= esc($message['recipient_member_id']) ?>#messages" class="text-indigo-600 hover:text-indigo-900">View Conversation</a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500">No messages from members yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php end_section(); ?>
