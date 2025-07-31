<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="/admin/members" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Member</h1>
            <p class="text-gray-600">Create a new club member account</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="/admin/members" class="space-y-6 p-6">
            <input type="hidden" name="_token" value="<?= $csrfToken ?? 'test-token' ?>">
            
            <!-- Display Errors -->
            <?php if (!empty($flashMessages['errors'])): ?>
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
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
                <h3>Personal Information</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="<?= htmlspecialchars($session->getFlash('first_name') ?? '') ?>"
                            required
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="<?= htmlspecialchars($session->getFlash('last_name') ?? '') ?>"
                            required
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div class="/*sm:col-span-2*/">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($session->getFlash('email') ?? '') ?>"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number
                        </label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            value="<?= htmlspecialchars($session->getFlash('phone') ?? '') ?>"
                            maxlength="20"
                            class="form-input"
                        >
                    </div>



                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">
                            Date of Birth <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="date_of_birth" 
                            name="date_of_birth" 
                            value="<?= htmlspecialchars($session->getFlash('date_of_birth') ?? '') ?>"
                            required
                            class="form-input"
                            onchange="checkAge()"
                        >
                        <div id="age-display" class="text-sm text-gray-500 mt-2"></div>
                    </div>



                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required class="form-input">
                            <option value="">Select Status</option>
                            <option value="active" <?= ($session->getFlash('status') === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($session->getFlash('status') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Parent/Guardian Information (for under 18s) -->
            <div id="parent-guardian-section" class="hidden form-section">
                <h3>
                    Parent/Guardian Information 
                    <span class="text-sm font-normal text-amber-600">(Required for members under 18)</span>
                </h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="parent_first_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Parent/Guardian First Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="parent_first_name" 
                            name="parent_first_name" 
                            value="<?= htmlspecialchars($session->getFlash('parent_first_name') ?? '') ?>"
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_last_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Parent/Guardian Last Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="parent_last_name" 
                            name="parent_last_name" 
                            value="<?= htmlspecialchars($session->getFlash('parent_last_name') ?? '') ?>"
                            maxlength="50"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_email" class="block text-sm font-medium text-gray-700 mb-1">
                            Parent/Guardian Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="parent_email" 
                            name="parent_email" 
                            value="<?= htmlspecialchars($session->getFlash('parent_email') ?? '') ?>"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="parent_phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Parent/Guardian Phone <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="parent_phone" 
                            name="parent_phone" 
                            value="<?= htmlspecialchars($session->getFlash('parent_phone') ?? '') ?>"
                            maxlength="20"
                            class="form-input"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label for="relationship" class="block text-sm font-medium text-gray-700 mb-1">
                            Relationship to Member <span class="text-red-500">*</span>
                        </label>
                        <select id="relationship" name="relationship" class="form-input">
                            <option value="">Select Relationship</option>
                            <option value="parent" <?= ($session->getFlash('relationship') === 'parent') ? 'selected' : '' ?>>Parent</option>
                            <option value="guardian" <?= ($session->getFlash('relationship') === 'guardian') ? 'selected' : '' ?>>Legal Guardian</option>
                            <option value="grandparent" <?= ($session->getFlash('relationship') === 'grandparent') ? 'selected' : '' ?>>Grandparent</option>
                            <option value="other" <?= ($session->getFlash('relationship') === 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="form-section">
                <h3>Emergency Contact</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Emergency Contact Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="emergency_contact_name" 
                            name="emergency_contact_name" 
                            value="<?= htmlspecialchars($session->getFlash('emergency_contact_name') ?? '') ?>"
                            maxlength="100"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Emergency Contact Phone <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="emergency_contact_phone" 
                            name="emergency_contact_phone" 
                            value="<?= htmlspecialchars($session->getFlash('emergency_contact_phone') ?? '') ?>"
                            maxlength="20"
                            required
                            class="form-input"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label for="emergency_contact_relationship" class="block text-sm font-medium text-gray-700 mb-1">
                            Relationship to Member
                        </label>
                        <input 
                            type="text" 
                            id="emergency_contact_relationship" 
                            name="emergency_contact_relationship" 
                            value="<?= htmlspecialchars($session->getFlash('emergency_contact_relationship') ?? '') ?>"
                            maxlength="50"
                            placeholder="e.g., Mother, Father, Spouse, Friend"
                            class="form-input"
                        >
                    </div>
                </div>
            </div>

            <!-- Consents and Agreements -->
            <div class="form-section">
                <h3>Consents and Agreements</h3>
                <div class="space-y-4">
                    <div class="consent-item">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input 
                                    id="consent_photography" 
                                    name="consent_photography" 
                                    type="checkbox" 
                                    value="1"
                                    <?= $session->getFlash('consent_photography') ? 'checked' : '' ?>
                                >
                            </div>
                            <div class="ml-4 text-sm">
                                <label for="consent_photography" class="font-semibold text-gray-800">
                                    Photography Consent
                                    <span class="text-xs text-gray-500 font-normal">(Optional)</span>
                                </label>
                                <p class="text-gray-600 mt-1 leading-relaxed">
                                    I consent to photographs being taken during club activities and potentially used for promotional purposes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="consent-item required">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input 
                                    id="consent_first_aid" 
                                    name="consent_first_aid" 
                                    type="checkbox" 
                                    value="1"
                                    <?= $session->getFlash('consent_first_aid') ? 'checked' : '' ?>
                                    required
                                >
                            </div>
                            <div class="ml-4 text-sm">
                                <label for="consent_first_aid" class="font-semibold text-gray-800">
                                    First Aid Consent 
                                    <span class="text-red-500 font-bold">*</span>
                                </label>
                                <p class="text-gray-600 mt-1 leading-relaxed">
                                    I consent to first aid being administered if required during club activities.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="consent-item required">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input 
                                    id="accept_terms" 
                                    name="accept_terms" 
                                    type="checkbox" 
                                    value="1"
                                    <?= $session->getFlash('accept_terms') ? 'checked' : '' ?>
                                    required
                                >
                            </div>
                            <div class="ml-4 text-sm">
                                <label for="accept_terms" class="font-semibold text-gray-800">
                                    Terms and Conditions 
                                    <span class="text-red-500 font-bold">*</span>
                                </label>
                                <p class="text-gray-600 mt-1 leading-relaxed">
                                    I accept the club's <a href="/terms" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">terms and conditions</a> and 
                                    <a href="/privacy" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">privacy policy</a>.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="consent-item">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input 
                                    id="marketing_consent" 
                                    name="marketing_consent" 
                                    type="checkbox" 
                                    value="1"
                                    <?= $session->getFlash('marketing_consent') ? 'checked' : '' ?>
                                >
                            </div>
                            <div class="ml-4 text-sm">
                                <label for="marketing_consent" class="font-semibold text-gray-800">
                                    Marketing Communications
                                    <span class="text-xs text-gray-500 font-normal">(Optional)</span>
                                </label>
                                <p class="text-gray-600 mt-1 leading-relaxed">
                                    I would like to receive newsletters, event updates, and promotional materials from the club.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-4 pt-8 border-t-2 border-gray-100">
                <a href="/admin/members" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    Create Member
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Simple enhanced input styling */
/*.form-input {
    padding: 8px 12px !important;
    border: 2px solid #d1d5db !important;
    border-radius: 3px !important;
    transition: all 0.2s ease !important;
    font-size: 14px !important;
}

.form-input:hover {
    border-color: #9ca3af !important;
}

.form-input:focus {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    outline: none !important;
}

.section-box {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.consent-box {
    background-color: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
}

.consent-box.required {
    border-left: 4px solid #ef4444;
}*/
</style>

<script>
// Calculate age and show/hide parent section
function checkAge() {
    const dobInput = document.getElementById('date_of_birth');
    const parentSection = document.getElementById('parent-guardian-section');
    const ageDisplay = document.getElementById('age-display');
    const parentFields = parentSection.querySelectorAll('input, select');
    
    if (!dobInput.value) {
        parentSection.classList.add('hidden');
        ageDisplay.textContent = '';
        setParentFieldsRequired(false);
        return;
    }
    
    const today = new Date();
    const birthDate = new Date(dobInput.value);
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    // Adjust age if birthday hasn't occurred this year
    const actualAge = (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) 
        ? age - 1 
        : age;
    
    ageDisplay.innerHTML = `<span class="age-badge">${actualAge} years old</span>`;
    
    if (actualAge < 18) {
        parentSection.classList.remove('hidden');
        setParentFieldsRequired(true);
        ageDisplay.innerHTML = `<span class="age-badge minor">${actualAge} years old - Parent/Guardian details required</span>`;
    } else {
        parentSection.classList.add('hidden');
        setParentFieldsRequired(false);
    }
}

// Set parent fields as required or not
function setParentFieldsRequired(required) {
    const parentFields = [
        'parent_first_name',
        'parent_last_name', 
        'parent_email',
        'parent_phone',
        'relationship'
    ];
    
    parentFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            if (required) {
                field.setAttribute('required', 'required');
            } else {
                field.removeAttribute('required');
                field.value = ''; // Clear values when not required
            }
        }
    });
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
    
    // Check age on page load if date is already filled
    checkAge();
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredCheckboxes = this.querySelectorAll('input[type="checkbox"][required]');
    let isValid = true;
    
    requiredCheckboxes.forEach(checkbox => {
        const consentItem = checkbox.closest('.consent-item');
        if (!checkbox.checked) {
            isValid = false;
            if (consentItem) {
                consentItem.classList.add('border-red-300', 'bg-red-50');
            }
        } else {
            if (consentItem) {
                consentItem.classList.remove('border-red-300', 'bg-red-50');
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