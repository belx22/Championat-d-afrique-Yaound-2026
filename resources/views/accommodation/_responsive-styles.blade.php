{{-- Responsive Design Improvements --}}
<style>
    /* Mobile optimizations */
    @media (max-width: 768px) {
        /* Cards stacking on mobile */
        .card {
            margin-bottom: 1rem;
        }
        
        /* Table responsive */
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .table th,
        .table td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Modal full width on mobile */
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
        
        /* Form inputs full width on mobile */
        .form-row .col-md-6,
        .form-row .col-md-3 {
            margin-bottom: 0.5rem;
        }
        
        /* Hotel cards in federation view */
        .hotel-card {
            margin-bottom: 1.5rem;
        }
        
        /* Statistics cards */
        .border-left-primary,
        .border-left-success,
        .border-left-info,
        .border-left-warning {
            border-left: none !important;
            border-top: 0.25rem solid !important;
        }
        
        /* Photo gallery */
        .photo-thumbnail {
            height: 60px !important;
        }
        
        /* Dashboard charts */
        canvas {
            max-height: 250px !important;
        }
    }
    
    @media (max-width: 576px) {
        /* Even smaller screens */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .h3 {
            font-size: 1.5rem;
        }
        
        /* Stack filters vertically */
        .row.g-3 > [class*="col-"] {
            margin-bottom: 0.75rem;
        }
    }
    
    /* Touch-friendly buttons on mobile */
    @media (hover: none) and (pointer: coarse) {
        .btn {
            min-height: 44px;
            min-width: 44px;
        }
    }
    
    /* Print styles */
    @media print {
        .btn, .modal, .sidebar, .navbar {
            display: none !important;
        }
    }
</style>
