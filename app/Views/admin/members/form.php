<?php
// Determine if we're editing or creating
$isEdit = isset($member) && $member;
$pageTitle = $isEdit ? 'Edit Member' : 'Add New Member';
$pageDescription = $isEdit ? "Update {$member->first_name} {$member->last_name}'s information" : 'Create a new club member account';
$formAction = $isEdit ? "/admin/members/{$member->id}" : '/admin/members';
$submitText = $isEdit ? 'Update Member' : 'Create Member';
$cancelUrl = $isEdit ? "/admin/members/{$member->id}" : '/admin/members';

// Helper function to get field value (flash data or existing data)
function getFieldValue($fieldName, $member = null, $session = null) {
    // First check flash data (from validation errors)
    if ($session && method_exists($session, 'getFlash')) {
        $flashValue = $session->getFlash($fieldName);
        if ($flashValue !== null) {
            return $flashValue;
        }
    }
    
    // Then check member data
    if ($member && is_object($member)) {
        return $member->{$fieldName} ?? '';
    }
    
    return '';
}

// Helper function to check if checkbox should be checked
function isChecked($fieldName, $member = null, $session = null) {
    // First check flash data
    if ($session && method_exists($session, 'getFlash')) {
        $flashValue = $session->getFlash($fieldName);
        if ($flashValue !== null) {
            return $flashValue ? 'checked' : '';
        }
    }
    
    // Then check member data
    if ($member && is_object($member)) {
        $value = $member->{$fieldName} ?? false;
        // Handle both boolean and string values
        return ($value === true || $value === 1 || $value === '1') ? 'checked' : '';
    }
    
    return '';
}

// Helper function to check if option should be selected
function isSelected($fieldName, $value, $member = null, $session = null) {
    // First check flash data
    if ($session && method_exists($session, 'getFlash')) {
        $flashValue = $session->getFlash($fieldName);
        if ($flashValue !== null) {
            return $flashValue === $value ? 'selected' : '';
        }
    }
    
    // Then check member data
    if ($member && is_object($member)) {
        $currentValue = $member->{$fieldName} ?? '';
        return $currentValue === $value ? 'selected' : '';
    }
    
    return '';
}
?>

<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?= $cancelUrl ?>" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $pageTitle ?></h1>
            <p class="text-gray-600"><?= $pageDescription ?></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg">
        <form method="POST" action="<?= $formAction ?>" class="p-6">
            <input type="hidden" name="_token" value="<?= $csrfToken ?? 'test-token' ?>">
            
            <!-- Display Errors -->
            <?php if (!empty($flashMessages['errors'])): ?>
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach ($flashMessages['errors'] as $error): ?>
                                        <li><?= htmlspecialchars($error0) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Personal Information -->
            <div class="form-section">
                <h3 class="form-section-header">Personal Information</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="first_name" class="form-label form-label-required">First Name</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="<?= htmlspecialchars(getFieldValue('first_name', $member ?? null, $session ?? null)) ?>"
                            required
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="last_name" class="form-label form-label-required">Last Name</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="<?= htmlspecialchars(getFieldValue('last_name', $member ?? null, $session ?? null)) ?>"
                            required
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div class="form-col-span-2">
                        <label for="email" class="form-label form-label-required">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars(getFieldValue('email', $member ?? null, $session ?? null)) ?>"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="date_of_birth" class="form-label form-label-required">Date of Birth</label>
                        <input 
                            type="date" 
                            id="date_of_birth" 
                            name="date_of_birth" 
                            value="<?= htmlspecialchars(getFieldValue('date_of_birth', $member ?? null, $session ?? null)) ?>"
                            required
                            class="form-input"
                            onchange="checkAge()"
                        >
                        <div id="age-display" class="form-help"></div>
                    </div>

                    <div>
                        <label for="phone" class="form-label">Phone Number</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?= htmlspecialchars(getFieldValue('phone', $member ?? null, $session ?? null)) ?>"
                            maxlength="20"
                            class="form-input"
                            placeholder="+44 XXXX XXXXXX"
                        >
                    </div>

                    <div class="form-col-span-2">
                        <label for="status" class="form-label form-label-required">Membership Status</label>
                        <select id="status" name="status" required class="form-select">
                            <option value="">Select Status</option>
                            <option value="active" <?= isSelected('status', 'active', $member ?? null, $session ?? null) ?>>Active</option>
                            <option value="inactive" <?= isSelected('status', 'inactive', $member ?? null, $session ?? null) ?>>Inactive</option>
                            <?php if ($isEdit): ?>
                                <option value="suspended" <?= isSelected('status', 'suspended', $member ?? null, $session ?? null) ?>>Suspended</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Parent/Guardian Information (for under 18s) -->
            <div id="parent-guardian-section" class="form-section" style="display: none;">
                <h3 class="form-section-header">
                    Parent/Guardian Information
                    <span class="form-section-description">Required for members under 18 years old</span>
                </h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="parent_guardian_first_name" class="form-label form-label-required">Parent/Guardian First Name</label>
                        <input 
                            type="text" 
                            id="parent_guardian_first_name" 
                            name="parent_guardian_first_name" 
                            value="<?= htmlspecialchars(getFieldValue('parent_guardian_first_name', $member ?? null, $session ?? null)) ?>"
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_guardian_last_name" class="form-label form-label-required">Parent/Guardian Last Name</label>
                        <input 
                            type="text" 
                            id="parent_guardian_last_name" 
                            name="parent_guardian_last_name" 
                            value="<?= htmlspecialchars(getFieldValue('parent_guardian_last_name', $member ?? null, $session ?? null)) ?>"
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_guardian_email" class="form-label form-label-required">Parent/Guardian Email</label>
                        <input 
                            type="email" 
                            id="parent_guardian_email" 
                            name="parent_guardian_email" 
                            value="<?= htmlspecialchars(getFieldValue('parent_guardian_email', $member ?? null, $session ?? null)) ?>"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_guardian_phone" class="form-label form-label-required">Parent/Guardian Phone</label>
                        <input 
                            type="tel" 
                            id="parent_guardian_phone" 
                            name="parent_guardian_phone" 
                            value="<?= htmlspecialchars(getFieldValue('parent_guardian_phone', $member ?? null, $session ?? null)) ?>"
                            maxlength="20"
                            class="form-input"
                            placeholder="+44 XXXX XXXXXX"
                        >
                    </div>

                    <div class="form-col-span-2">
                        <label for="parent_guardian_relationship" class="form-label form-label-required">Relationship to Member</label>
                        <select id="parent_guardian_relationship" name="parent_guardian_relationship" class="form-select">
                            <option value="">Select Relationship</option>
                            <option value="parent" <?= isSelected('parent_guardian_relationship', 'parent', $member ?? null, $session ?? null) ?>>Parent</option>
                            <option value="guardian" <?= isSelected('parent_guardian_relationship', 'guardian', $member ?? null, $session ?? null) ?>>Legal Guardian</option>
                            <option value="grandparent" <?= isSelected('parent_guardian_relationship', 'grandparent', $member ?? null, $session ?? null) ?>>Grandparent</option>
                            <option value="other" <?= isSelected('parent_guardian_relationship', 'other', $member ?? null, $session ?? null) ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="form-section">
                <h3 class="form-section-header">Emergency Contact</h3>
                <div class="form-grid form-grid-cols-2">
                    <div>
                        <label for="emergency_contact_name" class="form-label form-label-required">Emergency Contact Name</label>
                        <input 
                            type="text" 
                            id="emergency_contact_name" 
                            name="emergency_contact_name" 
                            value="<?= htmlspecialchars(getFieldValue('emergency_contact_name', $member ?? null, $session ?? null)) ?>"
                            maxlength="100"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="emergency_contact_phone" class="form-label form-label-required">Emergency Contact Phone</label>
                        <input 
                            type="tel" 
                            id="emergency_contact_phone" 
                            name="emergency_contact_phone" 
                            value="<?= htmlspecialchars(getFieldValue('emergency_contact_phone', $member ?? null, $session ?? null)) ?>"
                            maxlength="20"
                            required
                            class="form-input"
                            placeholder="+44 XXXX XXXXXX"
                        >
                    </div>

                    <div class="form-col-span-2">
                        <label for="emergency_contact_relationship" class="form-label">Relationship to Member</label>
                        <input 
                            type="text" 
                            id="emergency_contact_relationship" 
                            name="emergency_contact_relationship" 
                            value="<?= htmlspecialchars(getFieldValue('emergency_contact_relationship', $member ?? null, $session ?? null)) ?>"
                            maxlength="50"
                            placeholder="e.g., Mother, Father, Spouse, Friend"
                            class="form-input"
                        >
                    </div>
                </div>
            </div>

            <!-- Consents and Agreements -->
            <div class="form-section">
                <h3 class="form-section-header">Consents and Agreements</h3>
                
                <div class="consent-box optional">
                    <div class="flex items-start">
                        <input 
                            id="consent_photography" 
                            name="consent_photography" 
                            type="checkbox" 
                            value="1"
                            <?= isChecked('consent_photography', $member ?? null, $session ?? null) ?>
                            class="form-checkbox mt-1"
                        >
                        <div class="ml-3">
                            <div class="consent-label">Photography Consent</div>
                            <div class="consent-description">
                                I consent to photographs being taken during club activities and potentially used for promotional purposes.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="consent-box required">
                    <div class="flex items-start">
                        <input 
                            id="consent_first_aid" 
                            name="consent_first_aid" 
                            type="checkbox" 
                            value="1"
                            <?= isChecked('consent_first_aid', $member ?? null, $session ?? null) ?>
                            required
                            class="form-checkbox mt-1"
                        >
                        <div class="ml-3">
                            <div class="consent-label">First Aid Consent <span class="text-red-500">*</span></div>
                            <div class="consent-description">
                                I consent to first aid being administered if required during club activities.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="consent-box required">
                    <div class="flex items-start">
                        <input 
                            id="terms_conditions_acceptance" 
                            name="terms_conditions_acceptance" 
                            type="checkbox" 
                            value="1"
                            <?= isChecked('terms_conditions_acceptance', $member ?? null, $session ?? null) ?>
                            required
                            class="form-checkbox mt-1"
                        >
                        <div class="ml-3">
                            <div class="consent-label">Terms and Conditions <span class="text-red-500">*</span></div>
                            <div class="consent-description">
                                I accept the club's <a href="/terms" target="_blank" class="text-blue-600 hover:text-blue-800 underline">terms and conditions</a> and 
                                <a href="/privacy" target="_blank" class="text-blue-600 hover:text-blue-800 underline">privacy policy</a>.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="consent-box optional">
                    <div class="flex items-start">
                        <input 
                            id="consent_marketing" 
                            name="consent_marketing" 
                            type="checkbox" 
                            value="1"
                            <?= isChecked('consent_marketing', $member ?? null, $session ?? null) ?>
                            class="form-checkbox mt-1"
                        >
                        <div class="ml-3">
                            <div class="consent-label">Marketing Communications</div>
                            <div class="consent-description">
                                I would like to receive newsletters, event updates, and promotional materials from the club.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="<?= $cancelUrl ?>" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    <?= $submitText ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function checkAge() {
    const dobInput = document.getElementById('date_of_birth');
    const parentSection = document.getElementById('parent-guardian-section');
    const ageDisplay = document.getElementById('age-display');
    const parentFields = ['parent_first_name', 'parent_last_name', 'parent_email', 'parent_phone', 'relationship'];
    
    if (!dobInput.value) {
        parentSection.style.display = 'none';
        ageDisplay.innerHTML = '';
        setParentFieldsRequired(false);
        return;
    }
    
    const today = new Date();
    const birthDate = new Date(dobInput.value);
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    const actualAge = (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) 
        ? age - 1 
        : age;
    
    if (actualAge < 18) {
        parentSection.style.display = 'block';
        setParentFieldsRequired(true);
        ageDisplay.innerHTML = `<span class="status-badge status-badge-warning">${actualAge} years old - Parent/Guardian details required</span>`;
    } else {
        parentSection.style.display = 'none';
        setParentFieldsRequired(false);
        ageDisplay.innerHTML = `<span class="status-badge status-badge-info">${actualAge} years old</span>`;
    }
    
    function setParentFieldsRequired(required) {
        parentFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                if (required) {
                    field.setAttribute('required', 'required');
                } else {
                    field.removeAttribute('required');
                }
            }
        });
    }
}

// Auto-format UK phone numbers
document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove all non-digits
            
            // If it starts with 44, keep it as is (already has country code)
            if (value.startsWith('44')) {
                // Format as +44 XXXX XXXXXX
                if (value.length >= 6) {
                    value = `+44 ${value.slice(2, 6)} ${value.slice(6, 12)}`;
                } else if (value.length >= 2) {
                    value = `+44 ${value.slice(2)}`;
                } else {
                    value = '+44';
                }
            } else if (value.startsWith('0')) {
                // UK number starting with 0, convert to +44
                value = value.slice(1); // Remove the leading 0
                if (value.length >= 4) {
                    value = `+44 ${value.slice(0, 4)} ${value.slice(4, 10)}`;
                } else {
                    value = `+44 ${value}`;
                }
            } else if (value.length > 0) {
                // Assume it's a UK number without country code
                if (value.length >= 4) {
                    value = `+44 ${value.slice(0, 4)} ${value.slice(4, 10)}`;
                } else {
                    value = `+44 ${value}`;
                }
            }
            
            e.target.value = value;
        });
    });
    
    // Check age on page load
    checkAge();
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredCheckboxes = this.querySelectorAll('input[type="checkbox"][required]');
    let isValid = true;
    
    requiredCheckboxes.forEach(checkbox => {
        const consentBox = checkbox.closest('.consent-box');
        if (!checkbox.checked) {
            isValid = false;
            if (consentBox) {
                consentBox.style.borderColor = '#f87171';
                consentBox.style.backgroundColor = '#fef2f2';
            }
        } else {
            if (consentBox) {
                consentBox.style.borderColor = '';
                consentBox.style.backgroundColor = '';
            }
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please accept all required terms and consents');
        return false;
    }
});
</script>