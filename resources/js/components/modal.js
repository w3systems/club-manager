// =====================================
// resources/js/components/modal.js

document.addEventListener('alpine:init', () => {
    Alpine.data('modal', (initialOpen = false) => ({
        open: initialOpen,
        
        show() {
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        
        hide() {
            this.open = false;
            document.body.style.overflow = '';
        },
        
        toggle() {
            this.open ? this.hide() : this.show();
        }
    }));
});