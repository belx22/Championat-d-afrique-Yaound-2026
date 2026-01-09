@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Toutes les Réservations</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Statistics --}}
    @if(isset($stats))
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Réservations</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        <div class="text-xs text-muted">Actives: {{ $stats['active'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Paiements en attente</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_payment'] }}</div>
                        <div class="text-xs text-muted">50%: {{ $stats['pending_payment_50'] ?? 0 }} | 100%: {{ $stats['pending_payment_100'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Revenus totaux</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_revenue'] ?? 0) }} FCFA</div>
                        <div class="text-xs text-muted">Confirmés: {{ number_format($stats['validated_revenue'] ?? 0) }} FCFA</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Annulées</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['cancelled'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        @if(isset($stats['urgent_payments']) && $stats['urgent_payments'] > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention !</strong> Il y a <strong>{{ $stats['urgent_payments'] }}</strong> paiement(s) avec une échéance dans moins de 7 jours.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    @endif

    {{-- Actions Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800">Gestion des Réservations</h1>
        <div>
            <a href="{{ route('accommodation.dashboard') }}" class="btn btn-info mr-2">
                <i class="fas fa-chart-line"></i> Tableau de Bord
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-download"></i> Exporter
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('accommodation.export.reservations', array_merge($request->all(), ['format' => 'csv'])) }}">
                        <i class="fas fa-file-csv"></i> Exporter en CSV
                    </a>
                    <a class="dropdown-item" href="{{ route('accommodation.export.reservations', array_merge($request->all(), ['format' => 'csv', 'include_all' => '1'])) }}">
                        <i class="fas fa-file-csv"></i> Exporter tout (sans filtres)
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reservations.index') }}" class="row g-3" id="filterForm">
                <div class="col-md-3">
                    <select name="delegation_id" class="form-control">
                        <option value="">Toutes les délégations</option>
                        @foreach($delegations ?? [] as $delegation)
                            <option value="{{ $delegation->id }}" {{ $request->delegation_id == $delegation->id ? 'selected' : '' }}>
                                {{ $delegation->country }} - {{ $delegation->federation_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="hotel_id" class="form-control">
                        <option value="">Tous les hôtels</option>
                        @foreach($hotels ?? [] as $hotel)
                            <option value="{{ $hotel->id }}" {{ $request->hotel_id == $hotel->id ? 'selected' : '' }}>
                                {{ $hotel->name }} - {{ $hotel->city }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ $request->status == 'active' ? 'selected' : '' }}>Actives</option>
                        <option value="paid" {{ $request->status == 'paid' ? 'selected' : '' }}>Payées</option>
                        <option value="cancelled" {{ $request->status == 'cancelled' ? 'selected' : '' }}>Annulées</option>
                        <option value="urgent" {{ $request->status == 'urgent' ? 'selected' : '' }}>Paiements urgents</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort_by" class="form-control">
                        <option value="created_at" {{ $request->sort_by == 'created_at' ? 'selected' : '' }}>Date</option>
                        <option value="id" {{ $request->sort_by == 'id' ? 'selected' : '' }}>ID</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="sort_order" class="form-control">
                        <option value="desc" {{ $request->sort_order == 'desc' ? 'selected' : '' }}>↓</option>
                        <option value="asc" {{ $request->sort_order == 'asc' ? 'selected' : '' }}>↑</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @if($request->hasAny(['delegation_id', 'hotel_id', 'status']))
                    <div class="col-md-12 mt-2">
                        <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Bulk Actions --}}
    @if(isset($reservations) && $reservations->count() > 0)
        <div class="card shadow mb-4" id="bulkActionsCard" style="display: none;">
            <div class="card-body">
                <form id="bulkPaymentForm" method="POST">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <strong><span id="selectedCount">0</span> paiement(s) sélectionné(s)</strong>
                        </div>
                        <div class="col-md-4">
                            <select name="bulk_action" id="bulkActionSelect" class="form-control" required>
                                <option value="">-- Choisir une action --</option>
                                <option value="validate">Valider les paiements</option>
                                <option value="reject">Rejeter les paiements</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="rejectionReasonDiv" style="display: none;">
                            <input type="text" name="rejection_reason" class="form-control" 
                                   placeholder="Raison du rejet (requis)" maxlength="500">
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-primary" id="bulkActionBtn" disabled>
                                <i class="fas fa-check"></i> Appliquer
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearBulkSelection()">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="payment_ids" id="selectedPaymentIds">
                </form>
            </div>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Liste des Réservations</h5>
            <div>
                <button type="button" class="btn btn-sm btn-light" onclick="toggleBulkSelection()">
                    <i class="fas fa-check-square"></i> Sélection multiple
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="30" id="selectAllTh" style="display: none;">
                                <input type="checkbox" id="selectAllPayments" onchange="toggleAllPayments(this)">
                            </th>
                            <th>ID</th>
                            <th>Délégation</th>
                            <th>Hôtel</th>
                            <th>Type de chambre</th>
                            <th>Nombre de chambres</th>
                            <th>Dates</th>
                            <th>Coût total</th>
                            <th>Paiement 50%</th>
                            <th>Paiement 100%</th>
                            <th>Statut</th>
                            <th>Date de réservation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $reservation)
                            <tr class="{{ $reservation->is_cancelled ? 'table-danger' : '' }}">
                                <td class="bulk-select-cell" style="display: none;">
                                    @if($reservation->payment50 && $reservation->payment50->status === 'en_attente')
                                        <input type="checkbox" class="payment-checkbox" 
                                               value="{{ $reservation->payment50->id }}" 
                                               data-payment-type="50"
                                               onchange="updateBulkSelection()">
                                    @endif
                                    @if($reservation->payment100 && $reservation->payment100->status === 'en_attente')
                                        <input type="checkbox" class="payment-checkbox" 
                                               value="{{ $reservation->payment100->id }}" 
                                               data-payment-type="100"
                                               onchange="updateBulkSelection()">
                                    @endif
                                </td>
                                <td>#{{ $reservation->id }}</td>
                                <td>
                                    <strong>{{ $reservation->delegation->country }}</strong><br>
                                    <small class="text-muted">{{ $reservation->delegation->federation_name }}</small>
                                </td>
                                <td>
                                    {{ $reservation->room->hotel->name }}<br>
                                    <small class="text-muted">{{ $reservation->room->hotel->city }}</small>
                                </td>
                                <td>{{ ucfirst($reservation->room->type) }}</td>
                                <td>{{ $reservation->rooms_reserved }}</td>
                                <td>
                                    @if($reservation->check_in_date && $reservation->check_out_date)
                                        {{ $reservation->check_in_date->format('d/m/Y') }} → {{ $reservation->check_out_date->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $reservation->number_of_nights ?? $reservation->calculateNights() }} nuit(s)</small>
                                    @else
                                        <span class="text-muted">Non défini</span>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($reservation->total_cost) }} FCFA</strong></td>
                                <td>
                                    @if($reservation->payment50)
                                        @if($reservation->payment50->status === 'valide')
                                            <span class="badge badge-success">Validé</span>
                                        @elseif($reservation->payment50->status === 'rejete')
                                            <span class="badge badge-danger">Rejeté</span>
                                        @else
                                            <span class="badge badge-warning">En attente</span>
                                            @if($reservation->payment50->isOverdue())
                                                <br><small class="text-danger">En retard!</small>
                                            @endif
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">Non payé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reservation->payment100)
                                        @if($reservation->payment100->status === 'valide')
                                            <span class="badge badge-success">Validé</span>
                                        @elseif($reservation->payment100->status === 'rejete')
                                            <span class="badge badge-danger">Rejeté</span>
                                        @else
                                            <span class="badge badge-warning">En attente</span>
                                            @if($reservation->payment100->isOverdue())
                                                <br><small class="text-danger">En retard!</small>
                                            @endif
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">Non payé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($reservation->is_cancelled)
                                        <span class="badge badge-danger">Annulée</span>
                                            @if($reservation->cancellation_reason)
                                                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($reservation->cancellation_reason, 30) }}</small>
                                            @endif
                                    @elseif($reservation->isFullyPaid())
                                        <span class="badge badge-success">Confirmée</span>
                                    @else
                                        <span class="badge badge-warning">En attente</span>
                                    @endif
                                </td>
                                <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('reservations.show', $reservation) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center">
                                    @if($request->hasAny(['delegation_id', 'hotel_id', 'status']))
                                        Aucune réservation trouvée avec ces critères.
                                    @else
                                        Aucune réservation trouvée.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if(method_exists($reservations, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $reservations->links() }}
        </div>
    @endif

</div>
@endsection
