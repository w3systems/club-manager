/**
 * Club Manager - Simple JavaScript (No Build Process Required)
 * Save as: public/assets/js/app.js
 */

// Get CSRF token
const token = document.querySelector('meta[name="csrf-token"]');
const csrfToken = token ? token.getAttribute('content') : '';

// Global utilities object
window.ClubManager = {
    // Show toast notification
    toast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        
        // Toast classes based on type
        const classes = {
            success: 'bg-green-50 text-green-800 border border-green-200',
            error: 'bg-red-50 text-red-800 border border-red-200', 
            warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
            info: 'bg-blue-50 text-blue-800 border border-blue-200'
        };
        
        // Toast icons
        const icons = {
            success: 'fas fa-check-circle text-green-400',
            error: 'fas fa-exclamation-circle text-red-400',
            warning: 'fas fa-exclamation-triangle text-yellow-400', 
            info: 'fas fa-info-circle text-blue-400'
        };
        
        toast.className = `fixed top-4 right-4 z-50 max-w-sm p-4 rounded-md shadow-lg transition-all duration-300 transform translate-x-0 ${classes[type] || classes.success}`;
        
        toast.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${icons[type] || icons.success}"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button onclick="ClubManager.removeToast('${toastId}')" 
                            class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times h-4 w-4"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.add('opacity-100');
        }, 10);
        
        // Auto-remove success toasts after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                this.removeToast(toastId);
            }, 5000);
        }
        
        return toastId;
    },
    
    // Remove toast by ID
    removeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.add('opacity-0', 'transform', 'translate-x-full');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
    },
    
    // Confirmation dialog
    confirm(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    // Format currency
    formatCurrency(amount, currency = 'GBP') {
        const symbols = {
            GBP: '£',
            USD: '$', 
            EUR: '€'
        };
        const symbol = symbols[currency] || currency + ' ';
        return symbol + parseFloat(amount).toFixed(2);
    },
    
    // Copy text to clipboard
    copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            // Modern approach
            navigator.clipboard.writeText(text).then(() => {
                this.toast('Copied to clipboard!', 'success');
            }).catch(() => {
                this.toast('Failed to copy to clipboard', 'error');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                this.toast('Copied to clipboard!', 'success');
            } catch (err) {
                this.toast('Failed to copy to clipboard', 'error');
            } finally {
                document.body.removeChild(textArea);
            }
        }
    },
    
    // Simple AJAX helper
    ajax: {
        get(url, options = {}) {
            return this.request('GET', url, null, options);
        },
        
        post(url, data, options = {}) {
            return this.request('POST', url, data, options);
        },
        
        request(method, url, data, options = {}) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open(method, url, true);
                
                // Set headers
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }
                
                // Handle POST data
                if (method === 'POST' && data) {
                    if (data instanceof FormData) {
                        // Let browser set content-type for FormData
                    } else if (typeof data === 'object') {
                        xhr.setRequestHeader('Content-Type', 'application/json');
                        data = JSON.stringify(data);
                    } else {
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    }
                }
                
                // Response handlers
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            resolve(response);
                        } catch (e) {
                            resolve(xhr.responseText);
                        }
                    } else {
                        reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
                    }
                };
                
                xhr.onerror = function() {
                    reject(new Error('Network error occurred'));
                };
                
                xhr.send(data);
            });
        }
    },
    
    // Loading state management
    loading: {
        show(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element) {
                element.disabled = true;
                const originalText = element.textContent;
                element.dataset.originalText = originalText;
                element.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            }
        },
        
        hide(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (element && element.dataset.originalText) {
                element.disabled = false;
                element.textContent = element.dataset.originalText;
                delete element.dataset.originalText;
            }
        }
    }
};

// DOM Ready functionality
document.addEventListener('DOMContentLoaded', function() {
    
    // Auto-focus first input with autofocus attribute
    const autofocusElement = document.querySelector('[autofocus]');
    if (autofocusElement) {
        autofocusElement.focus();
    }
    
    // Handle forms with confirmation
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = form.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Handle auto-submit forms (with debounce)
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
    
    // Simple form validation for required fields
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                // Remove previous error styling
                field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
                
                // Check if field is empty
                if (!field.value.trim()) {
                    field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                ClubManager.toast('Please fill in all required fields', 'error');
                
                // Focus first invalid field
                const firstInvalid = form.querySelector('.border-red-300');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
        });
        
        // Clear error styling on input
        form.addEventListener('input', function(e) {
            if (e.target.hasAttribute('required')) {
                e.target.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
            }
        });
    });
    
    // Handle AJAX forms
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('[type="submit"]');
            ClubManager.loading.show(submitBtn);
            
            const formData = new FormData(form);
            
            ClubManager.ajax.post(form.action, formData)
                .then(response => {
                    if (response.success) {
                        ClubManager.toast(response.message || 'Success!', 'success');
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    } else {
                        ClubManager.toast(response.message || 'An error occurred', 'error');
                    }
                })
                .catch(error => {
                    ClubManager.toast('An error occurred. Please try again.', 'error');
                    console.error('Form submission error:', error);
                })
                .finally(() => {
                    ClubManager.loading.hide(submitBtn);
                });
        });
    });
    
    // Handle copy-to-clipboard buttons
    document.querySelectorAll('[data-copy]').forEach(button => {
        button.addEventListener('click', function() {
            const text = this.dataset.copy || this.textContent;
            ClubManager.copyToClipboard(text);
        });
    });
    
    // Handle toggle buttons
    document.querySelectorAll('[data-toggle]').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.dataset.toggle);
            if (target) {
                target.classList.toggle('hidden');
            }
        });
    });
    
    // Simple table row highlighting
    document.querySelectorAll('table.table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.classList.add('bg-gray-50');
        });
        row.addEventListener('mouseleave', function() {
            this.classList.remove('bg-gray-50');
        });
    });
    
    // Auto-hide alerts that have auto-dismiss data attribute
    document.querySelectorAll('[data-auto-dismiss]').forEach(alert => {
        const delay = parseInt(alert.dataset.autoDismiss) || 5000;
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        }, delay);
    });
});

// Global error handler
window.addEventListener('error', function(e) {
    if (window.ClubManager && typeof ClubManager.toast === 'function') {
        ClubManager.toast('An unexpected error occurred', 'error');
    }
    console.error('Global error:', e.error);
});

// Console welcome message (optional)
console.log('%c🏋️ Club Manager Loaded Successfully! ', 'color: #971b1e; font-size: 14px; font-weight: bold;');
console.log('Global utilities available via ClubManager object');