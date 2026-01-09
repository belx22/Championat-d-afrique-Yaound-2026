@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Détails de la Réservation #{{ $reservation->id }}</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            {{-- Reservation Information --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informations de la réservation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Délégation :</strong> {{ $reservation->delegation->country }}</p>
                            <p><strong>Fédération :</strong> {{ $reservation->delegation->federation_name }}</p>
                            <p><strong>Contact :</strong> {{ $reservation->delegation->contact_person }}</p>
                            <p><strong>Email :</strong> {{ $reservation->delegation->email }}</p>
                            <p><strong>Téléphone :</strong> {{ $reservation->delegation->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Hôtel :</strong> {{ $reservation->room->hotel->name }}</p>
                            <p><strong>Ville :</strong> {{ $reservation->room->hotel->city }}</p>
                            <p><strong>Type de chambre :</strong> {{ ucfirst($reservation->room->type) }}</p>
                            <p><strong>Nombre de chambres :</strong> {{ $reservation->rooms_reserved }}</p>
                            <p><strong>Prix par nuit :</strong> {{ number_format($reservation->room->price) }} FCFA</p>
                            @if($reservation->check_in_date && $reservation->check_out_date)
                                <p><strong>Dates :</strong> 
                                    {{ $reservation->check_in_date->format('d/m/Y') }} → {{ $reservation->check_out_date->format('d/m/Y') }}
                                    ({{ $reservation->number_of_nights ?? $reservation->calculateNights() }} nuit(s))
                                </p>
                            @endif
                            <p><strong>Coût total :</strong> <strong>{{ number_format($reservation->total_cost) }} FCFA</strong></p>
                            <p><strong>Date de réservation :</strong> {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Statut :</strong> 
                                @if($reservation->is_cancelled)
                                    <span class="badge badge-danger">Annulée</span>
                                    @if($reservation->cancellation_reason)
                                        <br><small class="text-muted">Raison: {{ $reservation->cancellation_reason }}</small>
                                    @endif
                                @elseif($reservation->isFullyPaid())
                                    <span class="badge badge-success">Confirmée</span>
                                @else
                                    <span class="badge badge-warning">En attente</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment 50% Section --}}
            <div class="card shadow mb-4">
                <div class="card-header {{ $reservation->hasValidPayment50() ? 'bg-success' : 'bg-warning' }} text-white">
                    <h5 class="mb-0">Paiement de 50% - Échéance: 21 février 2026</h5>
                </div>
                <div class="card-body">
                    @if($reservation->payment50)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Statut :</strong> 
                                    @if($reservation->payment50->status === 'valide')
                                        <span class="badge badge-success">Validé</span>
                                    @elseif($reservation->payment50->status === 'rejete')
                                        <span class="badge badge-danger">Rejeté</span>
                                    @else
                                        <span class="badge badge-warning">En attente de validation</span>
                                        @if($reservation->payment50->isOverdue())
                                            <span class="badge badge-danger">En retard!</span>
                                        @endif
                                    @endif
                                </p>
                                <p><strong>Date d'upload :</strong> {{ $reservation->payment50->created_at->format('d/m/Y H:i') }}</p>
                                @if($reservation->payment50->payment_made_at)
                                    <p><strong>Date de paiement :</strong> {{ $reservation->payment50->payment_made_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($reservation->payment50->receipt_path)
                                    <p><strong>Reçu :</strong><br>
                                        <a href="{{ Storage::disk('public')->url($reservation->payment50->receipt_path) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Télécharger le reçu
                                        </a>
                                    </p>
                                @endif
                                @if($reservation->payment50->rejection_reason)
                                    <div class="alert alert-danger mt-2">
                                        <strong>Raison du rejet :</strong><br>
                                        {{ $reservation->payment50->rejection_reason }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($reservation->payment50->status === 'en_attente' && !$reservation->is_cancelled)
                            <div class="d-flex gap-2">
                                <form method="POST" 
                                      action="{{ route('payments.validate', $reservation->payment50) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Valider le paiement
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#rejectPayment50Modal">
                                    <i class="fas fa-times"></i> Rejeter
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            Aucun paiement de 50% n'a été téléversé pour le moment.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Timeline Section --}}
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Historique et Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        {{-- Reservation Created --}}
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker bg-primary rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                    <i class="fas fa-calendar-check text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Réservation créée</strong>
                                    <div class="text-muted small">{{ $reservation->created_at->format('d/m/Y à H:i') }}</div>
                                    <small>Réservation #{{ $reservation->id }} créée pour {{ $reservation->rooms_reserved }} chambre(s)</small>
                                </div>
                            </div>
                        </div>

                        {{-- Payment 50% Events --}}
                        @if($reservation->payment50)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-marker bg-warning rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                        <i class="fas fa-upload text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>Paiement 50% téléversé</strong>
                                        <div class="text-muted small">{{ $reservation->payment50->created_at->format('d/m/Y à H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            @foreach($reservation->payment50->history->sortByDesc('created_at') as $history)
                                <div class="timeline-item mb-3 ml-5">
                                    <div class="d-flex">
                                        <div class="timeline-marker rounded-circle {{ $history->status_after === 'valide' ? 'bg-success' : ($history->status_after === 'rejete' ? 'bg-danger' : 'bg-secondary') }}" style="width: 20px; height: 20px; margin-right: 15px; margin-top: 5px;"></div>
                                        <div class="flex-grow-1">
                                            <strong>
                                                @if($history->status_after === 'valide')
                                                    <i class="fas fa-check-circle text-success"></i> Paiement 50% validé
                                                @elseif($history->status_after === 'rejete')
                                                    <i class="fas fa-times-circle text-danger"></i> Paiement 50% rejeté
                                                @else
                                                    Statut changé: {{ $history->status_after }}
                                                @endif
                                            </strong>
                                            <div class="text-muted small">
                                                {{ $history->created_at->format('d/m/Y à H:i') }}
                                                @if($history->changedBy)
                                                    par {{ $history->changedBy->email }}
                                                @endif
                                            </div>
                                            @if($history->notes)
                                                <small class="text-muted">{{ $history->notes }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Payment 100% Events --}}
                        @if($reservation->payment100)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-marker bg-warning rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                        <i class="fas fa-upload text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>Paiement 100% téléversé</strong>
                                        <div class="text-muted small">{{ $reservation->payment100->created_at->format('d/m/Y à H:i') }}</div>
                                    </div>
                                </div>
                            </div>

                            @foreach($reservation->payment100->history->sortByDesc('created_at') as $history)
                                <div class="timeline-item mb-3 ml-5">
                                    <div class="d-flex">
                                        <div class="timeline-marker rounded-circle {{ $history->status_after === 'valide' ? 'bg-success' : ($history->status_after === 'rejete' ? 'bg-danger' : 'bg-secondary') }}" style="width: 20px; height: 20px; margin-right: 15px; margin-top: 5px;"></div>
                                        <div class="flex-grow-1">
                                            <strong>
                                                @if($history->status_after === 'valide')
                                                    <i class="fas fa-check-circle text-success"></i> Paiement 100% validé
                                                @elseif($history->status_after === 'rejete')
                                                    <i class="fas fa-times-circle text-danger"></i> Paiement 100% rejeté
                                                @else
                                                    Statut changé: {{ $history->status_after }}
                                                @endif
                                            </strong>
                                            <div class="text-muted small">
                                                {{ $history->created_at->format('d/m/Y à H:i') }}
                                                @if($history->changedBy)
                                                    par {{ $history->changedBy->email }}
                                                @endif
                                            </div>
                                            @if($history->notes)
                                                <small class="text-muted">{{ $history->notes }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Cancellation Event --}}
                        @if($reservation->is_cancelled && $reservation->cancelled_at)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-marker bg-danger rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                        <i class="fas fa-ban text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>Réservation annulée</strong>
                                        <div class="text-muted small">{{ $reservation->cancelled_at->format('d/m/Y à H:i') }}</div>
                                        @if($reservation->cancellation_reason)
                                            <small class="text-danger">{{ $reservation->cancellation_reason }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment 100% Section --}}
            <div class="card shadow mb-4">
                <div class="card-header {{ $reservation->hasValidPayment100() ? 'bg-success' : 'bg-warning' }} text-white">
                    <h5 class="mb-0">Paiement de 100% - Échéance: 21 mars 2026</h5>
                </div>
                <div class="card-body">
                    @if($reservation->payment100)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Statut :</strong> 
                                    @if($reservation->payment100->status === 'valide')
                                        <span class="badge badge-success">Validé</span>
                                    @elseif($reservation->payment100->status === 'rejete')
                                        <span class="badge badge-danger">Rejeté</span>
                                    @else
                                        <span class="badge badge-warning">En attente de validation</span>
                                        @if($reservation->payment100->isOverdue())
                                            <span class="badge badge-danger">En retard!</span>
                                        @endif
                                    @endif
                                </p>
                                <p><strong>Date d'upload :</strong> {{ $reservation->payment100->created_at->format('d/m/Y H:i') }}</p>
                                @if($reservation->payment100->payment_made_at)
                                    <p><strong>Date de paiement :</strong> {{ $reservation->payment100->payment_made_at->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($reservation->payment100->receipt_path)
                                    <p><strong>Reçu :</strong><br>
                                        <a href="{{ Storage::disk('public')->url($reservation->payment100->receipt_path) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Télécharger le reçu
                                        </a>
                                    </p>
                                @endif
                                @if($reservation->payment100->rejection_reason)
                                    <div class="alert alert-danger mt-2">
                                        <strong>Raison du rejet :</strong><br>
                                        {{ $reservation->payment100->rejection_reason }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($reservation->payment100->status === 'en_attente' && !$reservation->is_cancelled)
                            <div class="d-flex gap-2">
                                <form method="POST" 
                                      action="{{ route('payments.validate', $reservation->payment100) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Valider le paiement
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#rejectPayment100Modal">
                                    <i class="fas fa-times"></i> Rejeter
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            Aucun paiement de 100% n'a été téléversé pour le moment.
                            @if(!$reservation->hasValidPayment50())
                                <br><small>Note: Le paiement de 50% doit être validé avant de pouvoir payer les 100%.</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Internal Notes Section (Admin Only) {{ route('reservations.update-notes', $reservation) }}--}}
            <div class="card shadow mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-sticky-note"></i> Notes internes</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="notesForm">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <textarea name="internal_notes" 
                                      class="form-control" 
                                      rows="6" 
                                      placeholder="Ajouter des notes internes (visibles uniquement par les administrateurs)...">{{ old('internal_notes', $reservation->internal_notes) }}</textarea>
                            <small class="text-muted">Ces notes ne sont pas visibles par la délégation.</small>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </form>
                    @if($reservation->internal_notes)
                        <hr>
                        <div class="mt-2">
                            <small class="text-muted">Dernière mise à jour: {{ $reservation->updated_at->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informations importantes</h5>
                </div>
                <div class="card-body">
                    <h6>Dates de paiement</h6>
                    <ul class="list-unstyled">
                        <li><strong>50% :</strong> 21 février 2026</li>
                        <li><strong>100% :</strong> 21 mars 2026</li>
                    </ul>
                    <hr>
                    <p><small class="text-muted">
                        <strong>Note :</strong> Les réservations non payées dans les délais seront automatiquement annulées. 
                        Tous les paiements sont non remboursables.
                    </small></p>
                </div>
            </div>

            @if(!$reservation->is_cancelled)
                <div class="card shadow mt-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Actions administrateur</h5>
                    </div>
                    <div class="card-body">
                        <button type="button" 
                                class="btn btn-danger w-100" 
                                data-toggle="modal" 
                                data-target="#cancelReservationModal">
                            <i class="fas fa-ban"></i> Annuler la réservation
                        </button>
                        <small class="text-muted d-block mt-2">
                            Cette action libèrera les chambres et ne peut pas être annulée.
                        </small>
                    </div>
                </div>
            @endif

            <div class="card shadow mt-3">
                <div class="card-body text-center">
                    <a href="{{ route('reservations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Payment 50% Modal --}}
    @if($reservation->payment50 && $reservation->payment50->status === 'en_attente')
        <div class="modal fade" id="rejectPayment50Modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('payments.reject', $reservation->payment50) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Rejeter le paiement de 50%</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Raison du rejet *</label>
                                <textarea name="rejection_reason" 
                                          class="form-control" 
                                          rows="4" 
                                          required 
                                          placeholder="Veuillez expliquer la raison du rejet..."></textarea>
                                <small class="text-muted">Cette raison sera visible par la délégation.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                            <button type="submit" 
                                    class="btn btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce paiement ? La raison du rejet sera visible par la délégation.');">
                                Confirmer le rejet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel Reservation Modal --}}
    @if(!$reservation->is_cancelled)
        <div class="modal fade" id="cancelReservationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('reservations.cancel', $reservation) }}" id="cancelReservationForm">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Annuler la réservation #{{ $reservation->id }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <strong>Attention !</strong> Cette action est irréversible. Les chambres seront libérées et disponibles pour d'autres réservations.
                            </div>
                            <div class="form-group">
                                <label>Raison de l'annulation (optionnel)</label>
                                <textarea name="cancellation_reason" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Ex: Demande de la délégation, problème de paiement, etc."></textarea>
                            </div>
                            <p><strong>Délégation :</strong> {{ $reservation->delegation->country }} - {{ $reservation->delegation->federation_name }}</p>
                            <p><strong>Hôtel :</strong> {{ $reservation->room->hotel->name }}</p>
                            <p><strong>Chambres :</strong> {{ $reservation->rooms_reserved }}</p>
                            <p><strong>Coût total :</strong> {{ number_format($reservation->total_cost) }} FCFA</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                            <button type="submit" 
                                    class="btn btn-danger"
                                    onclick="return confirm('⚠️ ATTENTION: Cette action est irréversible. Les chambres seront libérées. Êtes-vous absolument sûr ?');">
                                Confirmer l'annulation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Reject Payment 100% Modal --}}
    @if($reservation->payment100 && $reservation->payment100->status === 'en_attente')
        <div class="modal fade" id="rejectPayment100Modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('payments.reject', $reservation->payment100) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Rejeter le paiement de 100%</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Raison du rejet *</label>
                                <textarea name="rejection_reason" 
                                          class="form-control" 
                                          rows="4" 
                                          required 
                                          placeholder="Veuillez expliquer la raison du rejet..."></textarea>
                                <small class="text-muted">Cette raison sera visible par la délégation.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                            <button type="submit" 
                                    class="btn btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce paiement ? La raison du rejet sera visible par la délégation.');">
                                Confirmer le rejet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Notes Form Auto-save Feedback --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notesForm = document.getElementById('notesForm');
    if (notesForm) {
        notesForm.addEventListener('submit', function(e) {
            const btn = notesForm.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
            }
        });
    }
});
</script>
@endpush

@include('accommodation._responsive-styles')

</div>

@push('styles')
<style>
    .d-flex.gap-2 {
        gap: 0.5rem;
    }
</style>
@endpush
@endsection
