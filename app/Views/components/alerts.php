<?php
// =====================================
// app/Views/components/alerts.php
?>
<?php if (isset($flashMessages) && !empty($flashMessages)): ?>
    <div class="space-y-4 mb-6">
        <?php foreach ($flashMessages as $type => $message): ?>
            <?php if ($message): ?>
                <div x-data="{ show: true }" x-show="show" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-90"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-90"
                     class="<?= $this->getAlertClasses($type) ?> p-4 rounded-md"
                     <?php if ($type === 'success'): ?>
                         x-init="setTimeout(() => show = false, 5000)"
                     <?php endif; ?>>
                    
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="<?= $this->getAlertIcon($type) ?> h-5 w-5"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium">
                                <?= $this->e($message) ?>
                            </p>
                        </div>
                        <?php if ($type !== 'success'): ?>
                            <div class="ml-auto pl-3">
                                <button @click="show = false" class="inline-flex text-gray-400 hover:text-gray-600">
                                    <span class="sr-only">Dismiss</span>
                                    <i class="fas fa-times h-4 w-4"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>