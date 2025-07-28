// =====================================
// resources/js/components/forms.js

document.addEventListener('alpine:init', () => {
    Alpine.data('form', () => ({
        loading: false,
        errors: {},
        
        async submit(formElement, options = {}) {
            this.loading = true;
            this.errors = {};
            
            const formData = new FormData(formElement);
            
            try {
                const response = await axios.post(formElement.action, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                if (options.onSuccess) {
                    options.onSuccess(response.data);
                } else if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else if (response.data.message) {
                    ClubManager.toast(response.data.message, 'success');
                }
                
            } catch (error) {
                if (error.response && error.response.data) {
                    if (error.response.data.errors) {
                        this.errors = error.response.data.errors;
                    }
                    
                    if (error.response.data.message) {
                        ClubManager.toast(error.response.data.message, 'error');
                    }
                } else {
                    ClubManager.toast('An error occurred. Please try again.', 'error');
                }
                
                if (options.onError) {
                    options.onError(error);
                }
            } finally {
                this.loading = false;
            }
        },
        
        hasError(field) {
            return this.errors[field] && this.errors[field].length > 0;
        },
        
        getError(field) {
            return this.hasError(field) ? this.errors[field][0] : '';
        },
        
        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        }
    }));
});

// Global form utilities
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit forms with data-auto-submit attribute
    document.querySelectorAll('form[data-auto-submit]').forEach(form => {
        const delay = parseInt(form.dataset.autoSubmitDelay) || 500;
        let timeout;
        
        form.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                form.submit();
            }, delay);
        });
    });
    
    // Confirm forms with data-confirm attribute
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = form.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-focus first input with autofocus
    const autofocusElement = document.querySelector('[autofocus]');
    if (autofocusElement) {
        autofocusElement.focus();
    }
});