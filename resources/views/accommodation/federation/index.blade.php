@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Hébergement – Liste des Hôtels Disponibles</h1>

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

    {{-- Payment Deadlines Alert --}}
    <div class="alert alert-info">
        <strong>Dates de paiement importantes :</strong><br>
        • <strong>50% du paiement</strong> : 21 février 2026<br>
        • <strong>100% du paiement</strong> : 21 mars 2026<br>
        <small>Les réservations non payées à temps seront automatiquement annulées. Tous les paiements sont non remboursables.</small>
    </div>

    {{-- Filters --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('accommodation.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher un hôtel..." 
                           value="{{ $request->search ?? '' }}">
                </div>
                <div class="col-md-2">
                    <select name="city" class="form-control">
                        <option value="">Toutes les villes</option>
                        @foreach($cities ?? [] as $city)
                            <option value="{{ $city }}" {{ $request->city == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="standing" class="form-control">
                        <option value="">Tous les standings</option>
                        @for($i = 2; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ $request->standing == $i ? 'selected' : '' }}>
                                {{ $i }} ★
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="room_type" class="form-control">
                        <option value="">Tous les types</option>
                        @foreach($roomTypes ?? [] as $type)
                            <option value="{{ $type }}" {{ $request->room_type == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="min_price" class="form-control" placeholder="Prix min (FCFA)"
                           value="{{ $request->min_price ?? '' }}" min="0">
                </div>
                <div class="col-md-2">
                    <input type="number" name="max_price" class="form-control" placeholder="Prix max (FCFA)"
                           value="{{ $request->max_price ?? '' }}" min="0">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @if($request->hasAny(['search', 'city', 'standing', 'room_type', 'min_price', 'max_price', 'min_available_rooms']))
                    <div class="col-md-12 mt-2">
                        <a href="{{ route('accommodation.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times"></i> Réinitialiser les filtres
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- My Reservations Section --}}
    @if($reservations->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Mes Réservations</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Hôtel</th>
                                <th>Type de chambre</th>
                                <th>Nombre de chambres</th>
                                <th>Coût total</th>
                                <th>Paiement 50%</th>
                                <th>Paiement 100%</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $reservation)
                                <tr class="{{ $reservation->is_cancelled ? 'table-danger' : '' }}">
                                    <td>{{ $reservation->room->hotel->name }}</td>
                                    <td>{{ ucfirst($reservation->room->type) }}</td>
                                    <td>{{ $reservation->rooms_reserved }}</td>
                                    <td>{{ number_format($reservation->total_cost) }} FCFA</td>
                                    <td>
                                        @if($reservation->payment50)
                                            @if($reservation->payment50->status === 'valide')
                                                <span class="badge badge-success">Validé</span>
                                            @elseif($reservation->payment50->status === 'rejete')
                                                <span class="badge badge-danger">Rejeté</span>
                                            @else
                                                <span class="badge badge-warning">En attente</span>
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
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">Non payé</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reservation->is_cancelled)
                                            <span class="badge badge-danger">Annulée</span>
                                        @elseif($reservation->isFullyPaid())
                                            <span class="badge badge-success">Confirmée</span>
                                        @else
                                            <span class="badge badge-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('reservations.show', $reservation) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Available Hotels --}}
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-hotel"></i> Hôtels Disponibles</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($hotels as $hotel)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            @if($hotel->photos->count() > 0)
                                <div id="carousel{{ $hotel->id }}" class="carousel slide" data-ride="carousel">
                                    <div class="carousel-inner">
                                        @foreach($hotel->photos as $index => $photo)
                                            @if($photo->photo_url)
                                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                                    <img src="{{ $photo->photo_url }}" 
                                                         class="d-block w-100" 
                                                         style="height: 200px; object-fit: cover;"
                                                         alt="Hotel photo"
                                                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5JbWFnZSBub3QgZm91bmQ8L3RleHQ+PC9zdmc+';">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @if($hotel->photos->count() > 1)
                                        <a class="carousel-control-prev" href="#carousel{{ $hotel->id }}" role="button" data-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        </a>
                                        <a class="carousel-control-next" href="#carousel{{ $hotel->id }}" role="button" data-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white;">
                                    <div class="text-center">
                                        <i class="fas fa-hotel" style="font-size: 3rem; opacity: 0.7;"></i>
                                        <p class="mb-0 mt-2">Pas de photo</p>
                                    </div>
                                </div>
                            @endif
                            <div class="card-body">
                                <h5 class="card-title">
                                    {{ $hotel->name }} 
                                    <span class="text-warning">{{ str_repeat('★', $hotel->standing) }}</span>
                                </h5>
                                    <p class="card-text">
                                    <i class="fas fa-map-marker-alt"></i> {{ $hotel->city }}<br>
                                    {{ \Illuminate\Support\Str::limit($hotel->description ?? '', 100) }}
                                </p>
                                
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Prix/nuit</th>
                                            <th>Disponible</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hotel->rooms as $room)
                                            @if($room->available_rooms > 0)
                                                <tr>
                                                    <td>{{ ucfirst($room->type) }}</td>
                                                    <td>{{ number_format($room->price) }} FCFA</td>
                                                    <td>{{ $room->available_rooms }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-toggle="modal" 
                                                                data-target="#reserveModal{{ $room->id }}">
                                                            Réserver
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                                
                                <a href="{{ route('accommodation.federation.hotel.show', $hotel) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-info-circle"></i> Voir les détails
                                </a>
                            </div>
                        </div>
                    </div>

  @foreach($hotel->rooms as $room)
        @if($room->available_rooms > 0)
            <div class="modal fade" id="reserveModal{{ $room->id }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('reservations.store') }}">
                            @csrf
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Réserver des chambres</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Hôtel :</strong> {{ $hotel->name }}</p>
                                <p><strong>Type :</strong> {{ ucfirst($room->type) }}</p>
                                <p><strong>Prix par nuit :</strong> {{ number_format($room->price) }} FCFA</p>
                                <p><strong>Chambres disponibles :</strong> {{ $room->available_rooms }}</p>
                                
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                <div class="form-group">
                                    <label>Nombre de chambres à réserver *</label>
                                    <input type="number" 
                                           name="rooms_reserved" 
                                           class="form-control" 
                                           min="1" 
                                           max="{{ $room->available_rooms }}" 
                                           required
                                           id="rooms_reserved_{{ $room->id }}"
                                           onchange="calculateTotal({{ $room->id }}, {{ $room->price }})">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Date d'arrivée *</label>
                                        <input type="date" 
                                               name="check_in_date" 
                                               class="form-control"
                                               min="{{ date('Y-m-d') }}"
                                               id="check_in_{{ $room->id }}"
                                               required
                                               onchange="calculateNights({{ $room->id }}, {{ $room->price }})">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Date de départ *</label>
                                        <input type="date" 
                                               name="check_out_date" 
                                               class="form-control"
                                               id="check_out_{{ $room->id }}"
                                               required
                                               onchange="calculateNights({{ $room->id }}, {{ $room->price }})">
                                    </div>
                                </div>
                                
                                <div class="alert alert-info" id="cost_info_{{ $room->id }}" style="display: none;">
                                    <strong><i class="fas fa-calculator"></i> Coût total estimé :</strong> 
                                    <span class="h5 text-primary" id="total_cost_{{ $room->id }}">0</span> FCFA
                                    <br>
                                    <small id="nights_info_{{ $room->id }}"></small>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <small>
                                        <strong>Rappel :</strong> Réservation sur base du premier arrivé, premier servi. 
                                        Veuillez effectuer le paiement de 50% avant le 21 février 2026.
                                        <br>
                                        Les dates d'arrivée et de départ sont obligatoires pour calculer le coût total.
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach


                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            @if($request->hasAny(['search', 'city', 'standing', 'room_type', 'max_price']))
                                Aucun hôtel trouvé avec ces critères. Veuillez modifier vos filtres.
                            @else
                                Aucun hôtel disponible pour le moment.
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if(method_exists($hotels, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $hotels->links() }}
        </div>
    @endif

</div>
@endsection
