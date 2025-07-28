// =====================================
// resources/js/components/alerts.js

document.addEventListener('alpine:init', () => {
    Alpine.data('alerts', () => ({
        alerts: [],
        
        addAlert(message, type = 'info') {
            const id = Date.now();
            this.alerts.push({ id, message, type });
            
            // Auto-remove success alerts
            if (type === 'success') {
                setTimeout(() => {
                    this.removeAlert(id);
                }, 5000);
            }
        },
        
        removeAlert(id) {
            this.alerts = this.alerts.filter(alert => alert.id !== id);
        },
        
        getAlertClasses(type) {
            const classes = {
                success: 'bg-green-50 text-green-800 border border-green-200',
                error: 'bg-red-50 text-red-800 border border-red-200',
                warning: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
                info: 'bg-blue-50 text-blue-800 border border-blue-200'
            };
            return classes[type] || classes.info;
        },
        
        getAlertIcon(type) {
            const icons = {
                success: 'fas fa-check-circle text-green-400',
                error: 'fas fa-exclamation-circle text-red-400',
                warning: 'fas fa-exclamation-triangle text-yellow-400',
                info: 'fas fa-info-circle text-blue-400'
            };
            return icons[type] || icons.info;
        }
    }));
});