<?php
// app/views/member/messages.php
 require_once VIEW_PATH . '/member/layouts/member.php';  use App\Helpers\functions as Helpers; ?>

<?php Helpers\start_section('content'); ?>

<h1 class="text-2xl font-semibold text-gray-900 mb-6">Your Messages</h1>

<?php Helpers\displayFlashMessages();  Helpers\displayErrors(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg lg:col-span-2">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-xl font-medium text-gray-900 mb-4">Conversation History</h2>
            <?php if (!empty($messages)): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($messages as $message): ?>
                        <li class="py-4 px-2 hover:bg-gray-50">
                            <div class="flex justify-between items-center">
                                <p class="text-sm font-medium <?= $message['is_read_by_recipient'] ? 'text-gray-500' : 'text-gray-900' ?>">
                                    <?php if ($message['type'] === 'member_to_admin'): ?>
                                        <span class="text-indigo-600">You:</span>
                                    <?php elseif ($message['type'] === 'admin_to_member'): ?>
                                        <span class="text-green-600">Admin (<?= Helpers\esc($message['sender_admin_first_name'] . ' ' . $message['sender_admin_last_name']) ?>):</span>
                                    <?php endif; ?>
                                    <?= Helpers\esc(substr($message['content'], 0, 100)) ?><?= strlen($message['content']) > 100 ? '...' : '' ?>
                                </p>
                                <p class="text-xs text-gray-400">
                                    <?= (new DateTime($message['created_at']))->format('M j, Y H:i') ?>
                                </p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?= Helpers\esc($message['content']) ?></p>
                            <?php if ($message['type'] === 'admin_to_member' && !$message['is_read_by_recipient']): ?>
                                <form action="/messages/mark-read" method="POST" class="mt-2">
                                    <input type="hidden" name="message_id" value="<?= Helpers\esc($message['id']) ?>">
                                    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Mark as Read</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No messages found. Start a new conversation below.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-xl font-medium text-gray-900 mb-4">Send a New Message to Admin</h2>
            <form action="/messages" method="POST" class="space-y-4">
                <div>
                    <label for="message_content" class="sr-only">Your Message</label>
                    <textarea id="message_content" name="message_content" rows="6" required
                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-3"
                              placeholder="Type your message here..."><?= Helpers\old('message_content') ?></textarea>
                    <?php Helpers\displayErrors('message_content'); ?>
                </div>
                <div>
                    <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php Helpers\end_section(); ?>
