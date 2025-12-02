// resources/js/app.js

import Alpine from 'alpinejs';
import axios from 'axios';
import './components/alerts';
import './components/modal';
import './components/forms';
import '../css/app.css';

// Configure axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add CSRF token to all requests
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

// Make axios available globally
window.axios = axios;

// Start Alpine
window.Alpine = Alpine;
Alpine.start();

// Global utilities
window.ClubManager = {
    // Show toast notification
    toast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transition-all duration-300 ${this.getToastClasses(type)}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="${this.getToastIcon(type)} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">&times;</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove success toasts
        if (type === 'success') {
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }
    },
    
    getToastClasses(type) {
        const classes = {
            success: 'bg-green-50 text-green-800 border border-green-200',
            error: 'bg-red-50 text-red-800 border border-red-200',
            warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
            info: 'bg-blue-50 text-blue-800 border border-blue-200'
        };
        return classes[type] || classes.info;
    },
    
    getToastIcon(type) {
        const icons = {
            success: 'fas fa-check-circle text-green-400',
            error: 'fas fa-exclamation-circle text-red-400',
            warning: 'fas fa-exclamation-triangle text-yellow-400',
            info: 'fas fa-info-circle text-blue-400'
        };
        return icons[type] || icons.info;
    },
    
    // Confirm dialog
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
        return `${symbols[currency] || currency} ${parseFloat(amount).toFixed(2)}`;
    },
    
    // Copy to clipboard
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.toast('Copied to clipboard!', 'success');
        }).catch(() => {
            this.toast('Failed to copy to clipboard', 'error');
        });
    }
};