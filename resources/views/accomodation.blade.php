@extends('adminTheme.default')

@section('content')

<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">
        Hébergement – Gestion des hôtels & chambres
    </h1>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ===================== CREATE HOTEL BUTTON ===================== --}}
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#createHotelModal">
        <i class="fas fa-hotel"></i> Ajouter un hôtel
    </button>

    {{-- ===================== HOTELS LIST ===================== --}}
    <div class="row">

        @forelse($hotels as $hotel)
            <div class="col-lg-6 mb-4">
                <div class="card shadow">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ $hotel->name }}</strong>
                        <button class="btn btn-sm btn-success"
                                data-toggle="modal"
                                data-target="#addRoomModal{{ $hotel->id }}">
                            + Chambre
                        </button>
                    </div>

                    <div class="card-body">
                        <p>
                            <strong>Ville :</strong> {{ $hotel->city }} <br>
                            <strong>Standing :</strong> {{ $hotel->standing }} ★ <br>
                            <strong>Description :</strong> {{ $hotel->description }}
                        </p>

                        {{-- CHAMBRES --}}
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Prix / nuit</th>
                                    <th>Capacité</th>
                                    <th>Disponibles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hotel->rooms as $room)
                                    <tr>
                                        <td>{{ ucfirst($room->type) }}</td>
                                        <td>{{ number_format($room->price) }} FCFA</td>
                                        <td>{{ $room->capacity }} pers.</td>
                                        <td>{{ $room->available_rooms }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Aucune chambre enregistrée
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ================= ADD ROOM MODAL ================= --}}
            <div class="modal fade" id="addRoomModal{{ $hotel->id }}">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">

                        <form method="POST" action="{{ route('rooms.store') }}">
                            @csrf
                            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">

                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    Ajouter une chambre – {{ $hotel->name }}
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Type</label>
                                        <select name="type" class="form-control" required>
                                            <option value="single">Simple</option>
                                            <option value="double">Double</option>
                                            <option value="suite">Suite</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Prix / nuit (FCFA)</label>
                                        <input type="number" name="price" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Capacité</label>
                                        <input type="number" name="capacity" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Nombre de chambres disponibles</label>
                                    <input type="number" name="available_rooms" class="form-control" required>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button class="btn btn-success">Enregistrer</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun hôtel enregistré pour le moment.
                </div>
            </div>
        @endforelse

    </div>
</div>

{{-- ================= CREATE HOTEL MODAL ================= --}}
<div class="modal fade" id="createHotelModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('hotels.store') }}">
                @csrf

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        Ajouter un hôtel
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Nom de l’hôtel</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Ville</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Standing (★)</label>
                            <select name="standing" class="form-control" required>
                                <option value="2">2 ★</option>
                                <option value="3">3 ★</option>
                                <option value="4">4 ★</option>
                                <option value="5">5 ★</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary">Créer l’hôtel</button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection
