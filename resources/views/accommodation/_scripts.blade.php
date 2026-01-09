{{-- Global Toast Notification Scripts --}}
<script>
// Toast notification helper function
function showToast(message, type = 'success', duration = 5000) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('data-autohide', 'true');
    toast.setAttribute('data-delay', duration);
    
    const icons = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${icons[type] || icons.info} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => {
        toast.remove();
    }, duration);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Confirm action helper
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Loading state helper
function setLoadingState(button, loading = true) {
    if (!button) return;
    
    if (loading) {
        button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> En cours...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalHtml || button.innerHTML;
    }
}
</script>
