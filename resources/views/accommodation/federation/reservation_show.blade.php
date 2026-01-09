@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Détails de la réservation</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informations de la réservation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Hôtel :</strong> {{ $reservation->room->hotel->name }}</p>
                            <p><strong>Ville :</strong> {{ $reservation->room->hotel->city }}</p>
                            <p><strong>Type de chambre :</strong> {{ ucfirst($reservation->room->type) }}</p>
                            <p><strong>Nombre de chambres :</strong> {{ $reservation->rooms_reserved }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Prix par nuit :</strong> {{ number_format($reservation->room->price) }} FCFA</p>
                            <p><strong>Coût total :</strong> <strong>{{ number_format($reservation->total_cost) }} FCFA</strong></p>
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
                        <p><strong>Statut :</strong> 
                            @if($reservation->payment50->status === 'valide')
                                <span class="badge badge-success">Validé</span>
                            @elseif($reservation->payment50->status === 'rejete')
                                <span class="badge badge-danger">Rejeté</span>
                            @else
                                <span class="badge badge-warning">En attente de validation</span>
                            @endif
                        </p>
                        @if($reservation->payment50->receipt_path)
                            <p><strong>Reçu :</strong> 
                                <a href="{{ asset('storage/' . $reservation->payment50->receipt_path) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download"></i> Voir le reçu
                                </a>
                            </p>
                        @endif
                        @if($reservation->payment50->status === 'rejete')
                            <div class="alert alert-warning">
                                Votre paiement a été rejeté. Veuillez téléverser un nouveau reçu.
                            </div>
                            <form method="POST" 
                                  action="{{ route('payments.store', $reservation) }}" 
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="payment_type" value="partial_50">
                                <div class="form-group">
                                    <label>Téléverser un nouveau reçu de paiement (50%)</label>
                                    <input type="file" name="receipt" class="form-control-file" required accept="image/*,application/pdf">
                                    <small class="text-muted">Formats: JPG, PNG, PDF (max 10MB)</small>
                                </div>
                                <button type="submit" class="btn btn-primary">Envoyer</button>
                            </form>
                        @endif
                    @else
                        @if(!$reservation->is_cancelled)
                            <form method="POST" 
                                  action="{{ route('payments.store', $reservation) }}" 
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="payment_type" value="partial_50">
                                <div class="form-group">
                                    <label>Téléverser le reçu de paiement (50%) *</label>
                                    <input type="file" name="receipt" class="form-control-file" required accept="image/*,application/pdf">
                                    <small class="text-muted">Montant: {{ number_format($reservation->total_cost * 0.5) }} FCFA</small>
                                    <br><small class="text-muted">Formats: JPG, PNG, PDF (max 10MB)</small>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Attention :</strong> Le paiement doit être effectué avant le 21 février 2026, 
                                    sinon la réservation sera annulée.
                                </div>
                                <button type="submit" class="btn btn-primary">Envoyer le reçu</button>
                            </form>
                        @else
                            <div class="alert alert-danger">
                                Cette réservation a été annulée. Les paiements ne sont plus acceptés.
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Payment 100% Section --}}
            @if($reservation->hasValidPayment50() && !$reservation->is_cancelled)
                <div class="card shadow mb-4">
                    <div class="card-header {{ $reservation->hasValidPayment100() ? 'bg-success' : 'bg-warning' }} text-white">
                        <h5 class="mb-0">Paiement de 100% - Échéance: 21 mars 2026</h5>
                    </div>
                    <div class="card-body">
                        @if($reservation->payment100)
                            <p><strong>Statut :</strong> 
                                @if($reservation->payment100->status === 'valide')
                                    <span class="badge badge-success">Validé</span>
                                @elseif($reservation->payment100->status === 'rejete')
                                    <span class="badge badge-danger">Rejeté</span>
                                @else
                                    <span class="badge badge-warning">En attente de validation</span>
                                @endif
                            </p>
                            @if($reservation->payment100->receipt_path)
                                <p><strong>Reçu :</strong> 
                                    <a href="{{ asset('storage/' . $reservation->payment100->receipt_path) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i> Voir le reçu
                                    </a>
                                </p>
                            @endif
                            @if($reservation->payment100->status === 'rejete')
                                <div class="alert alert-warning">
                                    Votre paiement a été rejeté. Veuillez téléverser un nouveau reçu.
                                </div>
                                <form method="POST" 
                                      action="{{ route('payments.store', $reservation) }}" 
                                      enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="payment_type" value="final_100">
                                    <div class="form-group">
                                        <label>Téléverser un nouveau reçu de paiement (100%)</label>
                                        <input type="file" name="receipt" class="form-control-file" required accept="image/*,application/pdf">
                                        <small class="text-muted">Formats: JPG, PNG, PDF (max 10MB)</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Envoyer</button>
                                </form>
                            @endif
                        @else
                            <form method="POST" 
                                  action="{{ route('payments.store', $reservation) }}" 
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="payment_type" value="final_100">
                                <div class="form-group">
                                    <label>Téléverser le reçu de paiement (100%) *</label>
                                    <input type="file" name="receipt" class="form-control-file" required accept="image/*,application/pdf">
                                    <small class="text-muted">Montant: {{ number_format($reservation->total_cost) }} FCFA</small>
                                    <br><small class="text-muted">Formats: JPG, PNG, PDF (max 10MB)</small>
                                </div>
                                <div class="alert alert-warning">
                                    <strong>Attention :</strong> Le paiement doit être effectué avant le 21 mars 2026, 
                                    sinon la réservation sera annulée.
                                </div>
                                <button type="submit" class="btn btn-primary">Envoyer le reçu</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
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

            <div class="card shadow mt-3">
                <div class="card-body text-center">
                    <a href="{{ route('reservations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
