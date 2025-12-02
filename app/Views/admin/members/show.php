<div class="space-y-6">
    <!-- Page header -->
    <div class="flex items-center gap-4">
        <a href="/admin/members" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900">
                <?= htmlspecialchars($member->first_name . ' ' . $member->last_name) ?>
            </h1>
            <p class="text-gray-600">Member Details</p>
        </div>
        <div class="flex gap-3">
            <a href="/admin/members/<?= $member->id ?>/edit" class="btn btn-secondary">
                <i class="fas fa-edit mr-2"></i>
                Edit Member
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Member Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Details -->
            <div class="form-section">
                <h3 class="form-section-header">Personal Information</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label class="form-label">First Name</label>
                        <div class="form-display"><?= htmlspecialchars($member->first_name) ?></div>
                    </div>
                    <div>
                        <label class="form-label">Last Name</label>
                        <div class="form-display"><?= htmlspecialchars($member->last_name) ?></div>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <div class="form-display">
                            <a href="mailto:<?= htmlspecialchars($member->email) ?>" class="text-blue-600 hover:text-blue-800">
                                <?= htmlspecialchars($member->email) ?>
                            </a>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <div class="form-display">
                            <?php if ($member->phone ?? null): ?>
                                <div class="flex items-center gap-2">
                                    <a href="tel:<?= htmlspecialchars($member->phone) ?>" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-phone mr-1"></i><?= htmlspecialchars($member->phone) ?>
                                    </a>
                                    <?php 
                                    // Create WhatsApp link (remove +, spaces, and other formatting)
                                    $whatsappNumber = preg_replace('/[^\d]/', '', $member->phone);
                                    if (strlen($whatsappNumber) >= 10):
                                    ?>
                                        <a href="https://wa.me/<?= $whatsappNumber ?>" target="_blank" class="text-green-600 hover:text-green-800" title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Date of Birth</label>
                        <div class="form-display">
                            <?= ($member->date_of_birth ?? null) ? date('F j, Y', strtotime($member->date_of_birth)) : 'Not provided' ?>
                            <?php if ($member->date_of_birth ?? null): ?>
                                <?php 
                                $age = floor((time() - strtotime($member->date_of_birth)) / (365 * 24 * 60 * 60));
                                ?>
                                <span class="text-sm text-gray-500">(<?= $age ?> years old)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <div class="form-display">
                            <span class="status-badge status-badge-<?= $member->status === 'active' ? 'success' : ($member->status === 'suspended' ? 'error' : 'info') ?>">
                                <?= ucfirst($member->status) ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Member Since</label>
                        <div class="form-display"><?= date('d/m/Y', strtotime($member->created_at)) ?></div>
                    </div>
                </div>
            </div>

            <!-- Parent/Guardian Information -->
            <?php if (($member->parent_guardian_first_name ?? null) || ($member->parent_guardian_last_name ?? null) || ($member->parent_guardian_email ?? null) || ($member->parent_guardian_phone ?? null)): ?>
            <div class="form-section">
                <h3 class="form-section-header">Parent/Guardian Information</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label class="form-label">Parent/Guardian Name</label>
                        <div class="form-display">
                            <?= htmlspecialchars(trim(($member->parent_guardian_first_name ?? '') . ' ' . ($member->parent_guardian_last_name ?? ''))) ?: 'Not provided' ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Relationship</label>
                        <div class="form-display"><?= htmlspecialchars(ucwords($member->parent_guardian_relationship) ?? 'Not provided') ?></div>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <div class="form-display">
                            <?php if ($member->parent_guardian_email ?? null): ?>
                                <a href="mailto:<?= htmlspecialchars($member->parent_guardian_email) ?>" class="text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($member->parent_guardian_email) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <div class="form-display">
                            <?php if ($member->parent_guardian_phone ?? null): ?>
                                <div class="flex items-center gap-2">
                                    <a href="tel:<?= htmlspecialchars($member->parent_guardian_phone) ?>" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-phone mr-1"></i><?= htmlspecialchars($member->parent_guardian_phone) ?>
                                    </a>
                                    <?php 
                                    $whatsappNumber = preg_replace('/[^\d]/', '', $member->parent_guardian_phone);
                                    if (strlen($whatsappNumber) >= 10):
                                    ?>
                                        <a href="https://wa.me/<?= $whatsappNumber ?>" target="_blank" class="text-green-600 hover:text-green-800" title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Emergency Contact -->
            <div class="form-section">
                <h3 class="form-section-header">Emergency Contact</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label class="form-label">Name</label>
                        <div class="form-display"><?= htmlspecialchars($member->emergency_contact_name ?? 'Not provided') ?></div>
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <div class="form-display">
                            <?php if ($member->emergency_contact_phone ?? null): ?>
                                <div class="flex items-center gap-2">
                                    <a href="tel:<?= htmlspecialchars($member->emergency_contact_phone) ?>" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-phone mr-1"></i><?= htmlspecialchars($member->emergency_contact_phone) ?>
                                    </a>
                                    <?php 
                                    $whatsappNumber = preg_replace('/[^\d]/', '', $member->emergency_contact_phone);
                                    if (strlen($whatsappNumber) >= 10):
                                    ?>
                                        <a href="https://wa.me/<?= $whatsappNumber ?>" target="_blank" class="text-green-600 hover:text-green-800" title="WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-col-span-2">
                        <label class="form-label">Relationship</label>
                        <div class="form-display"><?= htmlspecialchars($member->emergency_contact_relationship ?? 'Not provided') ?></div>
                    </div>
                </div>
            </div>

            <!-- Consents and Agreements -->
            <div class="form-section">
                <h3 class="form-section-header">Consents and Agreements</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <div>
                            <span class="font-medium text-gray-900">Photography Consent</span>
                            <span class="text-xs text-gray-500 ml-2">(Optional)</span>
                            <p class="text-sm text-gray-600">Consent to photographs being taken during club activities</p>
                        </div>
                        <div>
                            <?php if ($member->consent_photography ?? false): ?>
                                <span class="status-badge status-badge-success">
                                    <i class="fas fa-check mr-1"></i>Granted
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-badge-info">
                                    <i class="fas fa-times mr-1"></i>Not granted
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <div>
                            <span class="font-medium text-gray-900">First Aid Consent</span>
                            <span class="text-xs text-gray-500 ml-2">(Optional)</span>
                            <p class="text-sm text-gray-600">Consent to first aid being administered if required</p>
                        </div>
                        <div>
                            <?php if ($member->consent_first_aid ?? false): ?>
                                <span class="status-badge status-badge-success">
                                    <i class="fas fa-check mr-1"></i>Granted
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-badge-info">
                                    <i class="fas fa-times mr-1"></i>Not granted
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-200">
                        <div>
                            <span class="font-medium text-gray-900">Terms and Conditions</span>
                            <span class="text-xs text-red-500 ml-2 font-semibold">(Required)</span>
                            <p class="text-sm text-gray-600">Acceptance of club terms and conditions</p>
                        </div>
                        <div>
                            <?php if ($member->terms_conditions_acceptance ?? false): ?>
                                <span class="status-badge status-badge-success">
                                    <i class="fas fa-check mr-1"></i>Accepted
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-badge-error">
                                    <i class="fas fa-times mr-1"></i>Not accepted
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div>
                            <span class="font-medium text-gray-900">Marketing Communications</span>
                            <span class="text-xs text-gray-500 ml-2">(Optional)</span>
                            <p class="text-sm text-gray-600">Consent to receive newsletters and promotional materials</p>
                        </div>
                        <div>
                            <?php if ($member->consent_marketing ?? false): ?>
                                <span class="status-badge status-badge-success">
                                    <i class="fas fa-check mr-1"></i>Granted
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-badge-info">
                                    <i class="fas fa-times mr-1"></i>Not granted
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscriptions -->
            <div class="form-section">
                <h3 class="form-section-header">
                    Subscriptions
                    <span class="text-sm font-normal text-gray-600">(<?= count($subscriptions) ?> total)</span>
                </h3>
                <?php if (empty($subscriptions)): ?>
                    <p class="text-gray-500 text-center py-8">No subscriptions found</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($subscriptions as $subscription): ?>
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($subscription['subscription_name']) ?></h4>
                                        <p class="text-sm text-gray-600">
                                            <?= ucfirst($subscription['type']) ?> subscription
                                            <?php if ($subscription['term_length'] && $subscription['term_unit']): ?>
                                                - <?= $subscription['term_length'] ?> <?= $subscription['term_unit'] ?><?= $subscription['term_length'] > 1 ? 's' : '' ?>
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Started: <?= date('M j, Y', strtotime($subscription['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-gray-900">£<?= number_format($subscription['price'], 2) ?></div>
                                        <span class="status-badge status-badge-<?= $subscription['status'] === 'active' ? 'success' : 'info' ?>">
                                            <?= ucfirst($subscription['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="form-section">
                <h3 class="form-section-header">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Active Subscriptions</span>
                        <span class="font-semibold"><?= count(array_filter($subscriptions, fn($s) => $s['status'] === 'active')) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Payments</span>
                        <span class="font-semibold"><?= count($payments) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Class Bookings</span>
                        <span class="font-semibold"><?= count($bookings) ?></span>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="form-section">
                <h3 class="form-section-header">Recent Payments</h3>
                <?php if (empty($payments)): ?>
                    <p class="text-gray-500 text-center py-4">No payments found</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($payments, 0, 5) as $payment): ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <div>
                                    <div class="font-medium">£<?= number_format($payment['amount'], 2) ?></div>
                                    <div class="text-xs text-gray-500"><?= date('M j, Y', strtotime($payment['payment_date'])) ?></div>
                                </div>
                                <span class="status-badge status-badge-<?= $payment['status'] === 'succeeded' ? 'success' : ($payment['status'] === 'failed' ? 'error' : 'warning') ?>">
                                    <?= ucfirst($payment['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Bookings -->
            <div class="form-section">
                <h3 class="form-section-header">Recent Class Bookings</h3>
                <?php if (empty($bookings)): ?>
                    <p class="text-gray-500 text-center py-4">No bookings found</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                            <div class="py-2 border-b border-gray-100">
                                <div class="font-medium text-sm"><?= htmlspecialchars($booking['class_name']) ?></div>
                                <div class="text-xs text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($booking['instance_date_time'])) ?>
                                </div>
                                <span class="status-badge status-badge-<?= $booking['status'] === 'booked' ? 'success' : 'info' ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.form-display {
    padding: 8px 0;
    font-size: 14px;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
}
</style>