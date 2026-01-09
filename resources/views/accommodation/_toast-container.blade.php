{{-- Toast Notification Container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    @if(session('success'))
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="true" data-delay="5000">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Succès</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="true" data-delay="7000">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Erreur</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="true" data-delay="8000">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong class="me-auto">Erreurs de validation</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

<script>
// Auto-hide toasts after delay
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast[data-autohide="true"]');
    toasts.forEach(function(toast) {
        const delay = toast.getAttribute('data-delay') || 5000;
        setTimeout(function() {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        }, parseInt(delay));
    });
});
</script>
