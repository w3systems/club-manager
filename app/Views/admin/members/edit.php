<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="/admin/members/<?= $member->id ?>" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Member</h1>
            <p class="text-gray-600">Update <?= htmlspecialchars($member->first_name . ' ' . $member->last_name) ?>'s information</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-lg">
        <form method="POST" action="/admin/members/<?= $member->id ?>" class="p-6">
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
                                        <li><?= htmlspecialchars($error) ?></li>
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
                            value="<?= htmlspecialchars($session->getFlash('first_name') ?? $member->first_name) ?>"
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
                            value="<?= htmlspecialchars($session->getFlash('last_name') ?? $member->last_name) ?>"
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
                            value="<?= htmlspecialchars($session->getFlash('email') ?? $member->email) ?>"
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
                            value="<?= htmlspecialchars($session->getFlash('date_of_birth') ?? $member->date_of_birth) ?>"
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
                            value="<?= htmlspecialchars($session->getFlash('phone') ?? $member->phone ?? '') ?>"
                            maxlength="20"
                            class="form-input"
                            placeholder="(555) 123-4567"
                        >
                    </div>

                    <div class="form-col-span-2">
                        <label for="status" class="form-label form-label-required">Membership Status</label>
                        <select id="status" name="status" required class="form-select">
                            <option value="">Select Status</option>
                            <option value="active" <?= ($session->getFlash('status') ?? $member->status) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($session->getFlash('status') ?? $member->status) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="suspended" <?= ($session->getFlash('status') ?? $member->status) === 'suspended' ? 'selected' : '' ?>>Suspended</option>
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
                        <label for="parent_first_name" class="form-label form-label-required">Parent/Guardian First Name</label>
                        <input 
                            type="text" 
                            id="parent_first_name" 
                            name="parent_first_name" 
                            value="<?= htmlspecialchars($session->getFlash('parent_first_name') ?? $member->parent_first_name ?? '') ?>"
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_last_name" class="form-label form-label-required">Parent/Guardian Last Name</label>
                        <input 
                            type="text" 
                            id="parent_last_name" 
                            name="parent_last_name" 
                            value="<?= htmlspecialchars($session->getFlash('parent_last_name') ?? $member->parent_last_name ?? '') ?>"
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_email" class="form-label form-label-required">Parent/Guardian Email</label>
                        <input 
                            type="email" 
                            id="parent_email" 
                            name="parent_email" 
                            value="<?= htmlspecialchars($session->getFlash('parent_email') ?? $member->parent_email ?? '') ?>"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_phone" class="form-label form-label-required">Parent/Guardian Phone</label>
                        <input 
                            type="tel" 
                            id="parent_phone" 
                            name="parent_phone" 
                            value="<?= htmlspecialchars($session->getFlash('parent_phone') ?? $member->parent_phone ?? '') ?>"
                            maxlength="20"
                            class="form-input"
                            placeholder="(555) 123-4567"
                        >
                    </div>

                    <div class="form-col-span-2">
                        <label for="relationship" class="form-label form-label-required">Relationship to Member</label>
                        <select id="relationship" name="relationship" class="form-select">
                            <option value="">Select Relationship</option>
                            <option value="parent" <?= ($session->getFlash('relationship') ?? $member->relationship ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
                            <option value="guardian" <?= ($session->getFlash('relationship') ?? $member->relationship ?? '') === 'guardian' ? 'selected' : '' ?>>Legal Guardian</option>
                            <option value="grandparent" <?= ($session->getFlash('relationship') ?? $member->relationship ?? '') === 'grandparent' ? 'selected' : '' ?>>Grandparent</option>
                            <option value="other" <?= ($session->getFlash('relationship') ?? $member->relationship ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
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
                            value="<?= htmlspecialchars($session->getFlash('emergency_contact_name') ?? $member->emergency_contact_name ?? '') ?>"
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
                            value="<?= htmlspecialchars($session->getFlash('emergency_contact_phone') ?? $member->emergency_contact_phone ?? '') ?>"
                            maxlength="20"
                            required
                            class="form-input"
                            placeholder="(555) 123-4567"
                        >
                    </div>

                    <div class="form-col-span-2">
                        <label for="emergency_contact_relationship" class="form-label">Relationship to Member</label>
                        <input 
                            type="text" 
                            id="emergency_contact_relationship" 
                            name="emergency_contact_relationship" 
                            value="<?= htmlspecialchars($session->getFlash('emergency_contact_relationship') ?? $member->emergency_contact_relationship ?? '') ?>"
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
                            <?= ($session->getFlash('consent_photography') ?? $member->consent_photography ?? false) ? 'checked' : '' ?>
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
                            <?= ($session->getFlash('consent_first_aid') ?? $member->consent_first_aid ?? false) ? 'checked' : '' ?>
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
                            id="accept_terms" 
                            name="accept_terms" 
                            type="checkbox" 
                            value="1"
                            <?= ($session->getFlash('accept_terms') ?? $member->accept_terms ?? false) ? 'checked' : '' ?>
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
                            id="marketing_consent" 
                            name="marketing_consent" 
                            type="checkbox" 
                            value="1"
                            <?= ($session->getFlash('marketing_consent') ?? $member->marketing_consent ?? false) ? 'checked' : '' ?>
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
                <a href="/admin/members/<?= $member->id ?>" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    Update Member
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

// Auto-format phone numbers
document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length >= 6) {
                value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
            } else if (value.length >= 3) {
                value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
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