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
    @if($reservations->isNotEmpty())
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
                        @foreach($reservations as $delegationId => $delegationReservations)
                            @php
                                // Aggregate data for federation (their own delegation)
                                $totalRooms = $delegationReservations->sum('rooms_reserved');
                                $totalCost = $delegationReservations->sum('total_cost');
                                
                                // Hotels and types
                                $hotels = $delegationReservations->map(function($r) {
                                    return $r->room->hotel->name . ' (' . $r->room->hotel->city . ')';
                                })->unique()->implode('<br>');
                                
                                $roomTypes = $delegationReservations->map(function($r) {
                                    return ucfirst($r->room->type);
                                })->unique()->implode(', ');
                                
                                // Payment statuses
                                $allPayment50Valid = $delegationReservations->every(function($r) {
                                    return $r->hasValidPayment50();
                                });
                                $allPayment100Valid = $delegationReservations->every(function($r) {
                                    return $r->hasValidPayment100();
                                });
                                
                                // Global status
                                $allFullyPaid = $delegationReservations->every(function($r) {
                                    return $r->isFullyPaid();
                                });
                                $anyCancelled = $delegationReservations->contains('is_cancelled', true);
                                
                                // Earliest reservation date
                                $earliestDate = $delegationReservations->min('created_at')->format('d/m/Y H:i');
                            @endphp
                            <tr>
                                <td>{{ $hotels }}</td>
                                <td>{{ $roomTypes }}</td>
                                <td>{{ $totalRooms }}</td>
                                <td><strong>{{ number_format($totalCost) }} FCFA</strong></td>
                                <td>
                                    @if($allPayment50Valid)
                                        <span class="badge badge-success">Validé</span>
                                    @elseif($delegationReservations->some(function($r) { return $r->payment50 && $r->payment50->status === 'en_attente'; }))
                                        <span class="badge badge-warning">En attente</span>
                                    @else
                                        <span class="badge badge-secondary">Non payé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($allPayment100Valid)
                                        <span class="badge badge-success">Validé</span>
                                    @elseif($delegationReservations->some(function($r) { return $r->payment100 && $r->payment100->status === 'en_attente'; }))
                                        <span class="badge badge-warning">En attente</span>
                                    @else
                                        <span class="badge badge-secondary">Non payé</span>
                                    @endif
                                </td>
                                <td>
                                    @if($anyCancelled)
                                        <span class="badge badge-danger">Annulée</span>
                                    @elseif($allFullyPaid)
                                        <span class="badge badge-success">Confirmée</span>
                                    @else
                                        <span class="badge badge-warning">En attente</span>
                                    @endif
                                </td>
                                <td>{{ $earliestDate }}</td>
                                <td>
                                    <a href="{{ route('reservations.show', $delegationReservations->first()) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        @if($reservations->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center">
                                    @if($request->hasAny(['status']))
                                        Aucune réservation trouvée avec ces critères.
                                    @else
                                        Aucune réservation trouvée.
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mt-4">
        <div class="card-body text-center">
            <a href="{{ route('accommodation.index') }}" class="btn btn-primary">
                <i class="fas fa-hotel"></i> Réserver une chambre
            </a>
        </div>
    </div>

</div>
@endsection
