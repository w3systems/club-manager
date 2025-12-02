<?php if (isset($flashMessages) && !empty($flashMessages)): ?>
    <div class="space-y-4 mb-6">
        <?php foreach ($flashMessages as $type => $message): ?>
            <?php if (!empty($message)): ?>
                <?php
                $alertClasses = match($type) {
                    'success' => 'bg-green-50 text-green-800 border-green-200',
                    'errors'  => 'bg-red-50 text-red-800 border-red-200',
                    'error'   => 'bg-red-50 text-red-800 border-red-200',
                    'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                    default   => 'bg-blue-50 text-blue-800 border-blue-200'
                };
                $iconClasses = match($type) {
                    'success' => 'fas fa-check-circle text-green-400',
                    'errors'  => 'fas fa-exclamation-circle text-red-400',
                    'error'   => 'fas fa-exclamation-circle text-red-400',
                    'warning' => 'fas fa-exclamation-triangle text-yellow-400',
                    default   => 'fas fa-info-circle text-blue-400'
                };
                ?>
                <div x-data="{ show: true }" x-show="show" x-transition class="p-4 rounded-md border <?= $alertClasses ?>">
                    <div class="flex">
                        <div class="flex-shrink-0"><i class="<?= $iconClasses ?> h-5 w-5"></i></div>
                        <div class="ml-3 flex-1">
                            <?php if ($type === 'errors' && is_array($message)): ?>
                                <h3 class="text-sm font-medium">Please correct the following errors:</h3>
                                <ul class="mt-2 list-disc list-inside text-sm">
                                    <?php foreach ($message as $fieldErrors): ?>
                                        <?php if (is_array($fieldErrors)): ?>
                                            <?php foreach ($fieldErrors as $error): ?>
                                                <li><?= htmlspecialchars($error) ?></li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php elseif (is_string($message)): // Only display if it's a string ?>
                                <p class="text-sm font-medium"><?= htmlspecialchars($message) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="ml-auto pl-3">
                            <button @click="show = false" class="inline-flex text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times h-4 w-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>