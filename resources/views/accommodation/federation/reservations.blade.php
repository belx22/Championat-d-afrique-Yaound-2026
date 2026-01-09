@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Mes Réservations</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Payment Deadlines Alert --}}
    <div class="alert alert-info">
        <strong>Dates de paiement importantes :</strong><br>
        • <strong>50% du paiement</strong> : 21 février 2026<br>
        • <strong>100% du paiement</strong> : 21 mars 2026<br>
        <small>Les réservations non payées à temps seront automatiquement annulées. Tous les paiements sont non remboursables.</small>
    </div>

    {{-- Filters --}}
    @if(method_exists($reservations, 'total') && $reservations->total() > 0)
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reservations.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">Tous les statuts</option>
                            <option value="active" {{ $request->status == 'active' ? 'selected' : '' }}>Actives</option>
                            <option value="paid" {{ $request->status == 'paid' ? 'selected' : '' }}>Payées</option>
                            <option value="cancelled" {{ $request->status == 'cancelled' ? 'selected' : '' }}>Annulées</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort_by" class="form-control">
                            <option value="created_at" {{ $request->sort_by == 'created_at' ? 'selected' : '' }}>Date</option>
                            <option value="id" {{ $request->sort_by == 'id' ? 'selected' : '' }}>ID</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort_order" class="form-control">
                            <option value="desc" {{ $request->sort_order == 'desc' ? 'selected' : '' }}>Récent</option>
                            <option value="asc" {{ $request->sort_order == 'asc' ? 'selected' : '' }}>Ancien</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                    @if($request->hasAny(['status', 'sort_by']))
                        <div class="col-md-3">
                            <a href="{{ route('reservations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Réinitialiser
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Liste de Mes Réservations</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Hôtel</th>
                            <th>Type de chambre</th>
                            <th>Nombre de chambres</th>
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
                                <td>
                                    {{ $reservation->room->hotel->name }}<br>
                                    <small class="text-muted">{{ $reservation->room->hotel->city }}</small>
                                </td>
                                <td>{{ ucfirst($reservation->room->type) }}</td>
                                <td>{{ $reservation->rooms_reserved }}</td>
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
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    @if($request->hasAny(['status']))
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

    <div class="card shadow mt-4">
        <div class="card-body text-center">
            <a href="{{ route('accommodation.index') }}" class="btn btn-primary">
                <i class="fas fa-hotel"></i> Réserver une chambre
            </a>
        </div>
    </div>

</div>
@endsection
