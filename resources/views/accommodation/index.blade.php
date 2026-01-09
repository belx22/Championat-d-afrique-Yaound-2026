@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

<h1 class="h3 mb-4 text-gray-800">Hébergement – Hôtels & Chambres</h1>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addHotelModal">
    + Ajouter un hôtel
</button>

<div class="row">
@foreach($hotels as $hotel)
<div class="col-md-6 mb-4">
    <div class="card shadow">
        <div class="card-header">
            <strong>{{ $hotel->name }}</strong> – {{ $hotel->standing }} ★
        </div>
        <div class="card-body">

            <p>{{ $hotel->city }}</p>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Dispo</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($hotel->rooms as $room)
                    <tr>
                        <td>{{ ucfirst($room->type) }}</td>
                        <td>{{ $room->price }} FCFA</td>
                        <td>{{ $room->available_rooms }}</td>
                        <td>
                            @if($room->available_rooms > 0)
                            <form method="POST" action="{{ route('reservations.store') }}">
                                @csrf
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                <input type="number" name="rooms_reserved" min="1"
                                       max="{{ $room->available_rooms }}" required>
                                <button class="btn btn-sm btn-success">Réserver</button>
                            </form>
                            @else
                                <span class="badge badge-danger">Complet</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <button class="btn btn-sm btn-outline-primary"
                    data-toggle="modal"
                    data-target="#addRoomModal{{ $hotel->id }}">
                + Ajouter chambre
            </button>

        </div>
    </div>
</div>

{{-- MODAL AJOUT CHAMBRE --}}
<div class="modal fade" id="addRoomModal{{ $hotel->id }}">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="{{ route('rooms.store') }}">
@csrf
<input type="hidden" name="hotel_id" value="{{ $hotel->id }}">

<div class="modal-header">
<h5>Ajouter chambre</h5>
</div>

<div class="modal-body">
    <select name="type" class="form-control mb-2">
        <option value="single">Simple</option>
        <option value="double">Double</option>
        <option value="suite">Suite</option>
    </select>
    <input name="price" type="number" class="form-control mb-2" placeholder="Prix">
    <input name="capacity" type="number" class="form-control mb-2" placeholder="Capacité">
    <input name="total_rooms" type="number" class="form-control" placeholder="Nombre">
</div>

<div class="modal-footer">
<button class="btn btn-primary">Enregistrer</button>
</div>
</form>
</div>
</div>
</div>

@endforeach
</div>

{{-- MODAL AJOUT HOTEL --}}
<div class="modal fade" id="addHotelModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST" action="{{ route('hotels.store') }}">
@csrf
<div class="modal-header"><h5>Ajouter hôtel</h5></div>
<div class="modal-body">
    <input name="name" class="form-control mb-2" placeholder="Nom">
    <input name="city" class="form-control mb-2" placeholder="Ville">
    <select name="standing" class="form-control mb-2">
        <option value="3">3 ★</option>
        <option value="4">4 ★</option>
        <option value="5">5 ★</option>
    </select>
    <textarea name="description" class="form-control" placeholder="Description"></textarea>
</div>
<div class="modal-footer">
<button class="btn btn-primary">Créer</button>
</div>
</form>
</div>
</div>
</div>

</div>
@endsection
