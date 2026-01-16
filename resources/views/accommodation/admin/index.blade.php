@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Accommodation – Configuration des Hôtels</h1>
    
    @include('accommodation._responsive-styles')

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

    <div class="d-flex justify-content-center align-items-center mb-3">
            
        {{-- Statistics Cards --}}
        <div class="row">
            <div class="col-md-2">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hôtels</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_hotels'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Chambres Total</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_rooms'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Disponibles</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['available_rooms'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Occupées</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['occupied_rooms'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Taux Occupation</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['occupancy_rate'] ?? 0 }}%</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Réservations</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_reservations'] ?? 0 }}</div>
                        <div class="text-xs text-muted">Total: {{ $stats['total_reservations'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow mb-4">        
        <button class="btn btn-primary" data-toggle="modal" data-target="#createHotelModal">
            <i class="fas fa-hotel"></i> Ajouter un hôtel
        </button>
    </div>

    <div class="row">
        @forelse($hotels as $hotel)
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                        <div>
                            <strong>{{ $hotel->name }}</strong> – {{ str_repeat('★', $hotel->standing) }}
                        </div>
                        <div>
                            <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#editHotelModal{{ $hotel->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('hotels.destroy', $hotel) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet hôtel ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body">
                        <p>
                            <strong>Ville :</strong> {{ $hotel->city }} <br>
                            <strong>Standing :</strong> {{ $hotel->standing }} ★ <br>
                            <strong>Description :</strong> {{ $hotel->description ?? 'N/A' }}
                        </p>

                        {{-- Photos Gallery --}}
                        @if($hotel->photos->count() > 0)
                            <div class="mb-3">
                                <strong>Photos :</strong>
                                <div class="row mt-2">
                                    @foreach($hotel->photos as $photo)
                                        @if($photo->photo_url)
                                            <div class="col-4 mb-2">
                                                <img src="{{ $photo->photo_url }}" 
                                                     alt="Hotel photo" 
                                                     class="img-thumbnail" 
                                                     style="width: 100%; height: 100px; object-fit: cover;"
                                                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTIiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5Ob3QgZm91bmQ8L3RleHQ+PC9zdmc+';">
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Rooms Table --}}
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Prix / nuit</th>
                                    <th>Capacité</th>
                                    <th>Disponibles</th>
                                    <th>Taux occupation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hotel->rooms as $room)
                                    <tr>
                                        <td>{{ ucfirst($room->type) }}</td>
                                        <td>{{ number_format($room->price) }} FCFA</td>
                                        <td>{{ $room->capacity }} pers.</td>
                                        <td>
                                            <span class="badge {{ $room->available_rooms > 0 ? 'badge-success' : 'badge-danger' }}">
                                                {{ $room->available_rooms }} / {{ $room->total_rooms }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar 
                                                    @if($room->occupancy_percentage >= 80) bg-danger
                                                    @elseif($room->occupancy_percentage >= 50) bg-warning
                                                    @else bg-success
                                                    @endif" 
                                                    role="progressbar" 
                                                    style="width: {{ $room->occupancy_percentage }}%"
                                                    aria-valuenow="{{ $room->occupancy_percentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    {{ $room->occupancy_percentage }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning"
                                                    data-toggle="modal"
                                                    data-target="#editRoomModal{{ $room->id }}"
                                                    title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('rooms.destroy', $room) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette chambre ? Cette action est irréversible si des réservations actives existent.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Supprimer"
                                                        onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer cette chambre ? Cette action est irréversible si des réservations actives existent.');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            Aucune chambre enregistrée
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        <div class="mt-2">
                            <button class="btn btn-sm btn-success"
                                    data-toggle="modal"
                                    data-target="#addRoomModal{{ $hotel->id }}">
                                <i class="fas fa-plus"></i> Ajouter une chambre
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit Hotel Modal --}}
            <div class="modal fade" id="editHotelModal{{ $hotel->id }}">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('hotels.update', $hotel) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Modifier l'hôtel</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Nom de l'hôtel</label>
                                    <input type="text" name="name" class="form-control" value="{{ $hotel->name }}" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Ville</label>
                                        <input type="text" name="city" class="form-control" value="{{ $hotel->city }}" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Standing</label>
                                        <select name="standing" class="form-control" required>
                                            @for($i = 2; $i <= 5; $i++)
                                                <option value="{{ $i }}" {{ $hotel->standing == $i ? 'selected' : '' }}>
                                                    {{ $i }} ★
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control">{{ $hotel->description }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>Photos existantes ({{ $hotel->photos->count() }}/10)</label>
                                    @if($hotel->photos->count() > 0)
                                        <div class="row" id="photo-sortable-{{ $hotel->id }}">
                                            @foreach($hotel->photos->sortBy('order') as $photo)
                                                <div class="col-3 mb-2 photo-item" data-photo-id="{{ $photo->id }}" style="cursor: move;">
                                                    <label class="d-block">
                                                        <input type="checkbox" name="delete_photos[]" value="{{ $photo->id }}" class="photo-delete-checkbox">
                                                        @if($photo->photo_url)
                                                            <img src="{{ $photo->photo_url }}" 
                                                                 class="img-thumbnail" 
                                                                 style="width: 100%; height: 80px; object-fit: cover; user-select: none;"
                                                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2RkZCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSIgZmlsbD0iIzk5OSI+Tm90IGZvdW5kPC90ZXh0Pjwvc3ZnPg==';">
                                                        @else
                                                            <div style="width: 100%; height: 80px; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">No image</div>
                                                        @endif
                                                        <small class="d-block text-center mt-1" style="font-size: 10px;">
                                                            <i class="fas fa-grip-vertical"></i> Glisser pour réorganiser
                                                        </small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle"></i> Glissez les photos pour réorganiser leur ordre d'affichage. Cochez pour supprimer.
                                        </small>
                                    @endif
                                </div>
                                @if($hotel->photos->count() < 10)
                                    <div class="form-group">
                                        <label>Ajouter des photos (max {{ 10 - $hotel->photos->count() }})</label>
                                        <input type="file" name="photos[]" class="form-control-file" multiple accept="image/*">
                                        <small class="text-muted">Maximum 10 photos au total ({{ $hotel->photos->count() }} actuellement)</small>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Add Room Modal --}}
            <div class="modal fade" id="addRoomModal{{ $hotel->id }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('rooms.store') }}">
                            @csrf
                            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Ajouter une chambre – {{ $hotel->name }}</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type" class="form-control" required>
                                        <option value="single">Simple</option>
                                        <option value="double">Double</option>
                                        <option value="suite">Suite</option>
                                    </select>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Prix / nuit (FCFA)</label>
                                        <input type="number" name="price" class="form-control" required min="0" step="1000">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Capacité (personnes)</label>
                                        <input type="number" name="capacity" class="form-control" required min="1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de chambres</label>
                                    <input type="number" name="total_rooms" class="form-control" required min="1">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-success">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit Room Modals --}}
            @foreach($hotel->rooms as $room)
                <div class="modal fade" id="editRoomModal{{ $room->id }}">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('rooms.update', $room) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title">Modifier la chambre – {{ $hotel->name }}</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <small>
                                            <strong>Note :</strong> Le nombre de chambres disponibles sera automatiquement ajusté en fonction des réservations actives.
                                            <br>
                                            Chambres occupées actuellement : <strong>{{ $room->total_rooms - $room->available_rooms }}</strong>
                                        </small>
                                    </div>
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="type" class="form-control" required>
                                            <option value="single" {{ $room->type === 'single' ? 'selected' : '' }}>Simple</option>
                                            <option value="double" {{ $room->type === 'double' ? 'selected' : '' }}>Double</option>
                                            <option value="suite" {{ $room->type === 'suite' ? 'selected' : '' }}>Suite</option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Prix / nuit (FCFA)</label>
                                            <input type="number" name="price" class="form-control" required min="0" step="1000" value="{{ $room->price }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Capacité (personnes)</label>
                                            <input type="number" name="capacity" class="form-control" required min="1" value="{{ $room->capacity }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Nombre total de chambres</label>
                                        <input type="number" name="total_rooms" class="form-control" required min="{{ $room->total_rooms - $room->available_rooms }}" value="{{ $room->total_rooms }}">
                                        <small class="text-muted">Minimum: {{ $room->total_rooms - $room->available_rooms }} (chambres occupées)</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-warning">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    @if($request->hasAny(['search', 'city', 'standing']))
                        Aucun hôtel trouvé avec ces critères.
                    @else
                        Aucun hôtel enregistré pour le moment.
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(method_exists($hotels, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $hotels->links() }}
        </div>
    @endif
</div>

{{-- Create Hotel Modal --}}
<div class="modal fade" id="createHotelModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('hotels.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Ajouter un hôtel</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nom de l'hôtel *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Ville *</label>
                            <input type="text" name="city" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Standing *</label>
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
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Photos (jusqu'à 10 photos)</label>
                        <input type="file" name="photos[]" class="form-control-file" multiple accept="image/*">
                        <small class="text-muted">Formats acceptés: JPG, PNG. Taille max: 5MB par photo.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer l'hôtel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag and drop for photo sorting
    @foreach($hotels ?? [] as $hotel)
        @if($hotel->photos->count() > 0)
            initPhotoSortable({{ $hotel->id }});
        @endif
    @endforeach
});

function initPhotoSortable(hotelId) {
    const container = document.getElementById('photo-sortable-' + hotelId);
    if (!container) return;

    const photoItems = container.querySelectorAll('.photo-item');
    let draggedElement = null;

    photoItems.forEach(item => {
        item.draggable = true;
        
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        });

        item.addEventListener('dragend', function(e) {
            this.style.opacity = '1';
            photoItems.forEach(item => {
                item.classList.remove('drag-over');
            });
        });

        item.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
            return false;
        });

        item.addEventListener('dragenter', function(e) {
            this.classList.add('drag-over');
        });

        item.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });

        item.addEventListener('drop', function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }

            if (draggedElement !== this) {
                const allItems = Array.from(container.querySelectorAll('.photo-item'));
                const draggedIndex = allItems.indexOf(draggedElement);
                const targetIndex = allItems.indexOf(this);

                if (draggedIndex < targetIndex) {
                    container.insertBefore(draggedElement, this.nextSibling);
                } else {
                    container.insertBefore(draggedElement, this);
                }
            }

            this.classList.remove('drag-over');
            
            // Save new order
            savePhotoOrder(hotelId);
            
            return false;
        });
    });
}

function savePhotoOrder(hotelId) {
    const container = document.getElementById('photo-sortable-' + hotelId);
    if (!container) return;

    const photoItems = container.querySelectorAll('.photo-item');
    const photoOrders = Array.from(photoItems).map((item, index) => {
        return item.getAttribute('data-photo-id');
    });

    // Store the order in a hidden input field to be sent with the form
    let orderInput = document.getElementById('photo_orders_' + hotelId);
    if (!orderInput) {
        orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'photo_orders';
        orderInput.id = 'photo_orders_' + hotelId;
        const form = container.closest('form');
        if (form) {
            form.appendChild(orderInput);
        }
    }
    orderInput.value = JSON.stringify(photoOrders);
}

// Add CSS for drag and drop
const style = document.createElement('style');
style.textContent = `
    .photo-item {
        transition: all 0.3s ease;
    }
    .photo-item.drag-over {
        border: 2px dashed #007bff;
        background-color: #e7f3ff;
        transform: scale(1.05);
    }
    .photo-item:hover {
        transform: scale(1.02);
    }
    .photo-item img {
        pointer-events: none;
    }
    .photo-delete-checkbox {
        position: absolute;
        top: 5px;
        left: 5px;
        z-index: 10;
        width: 20px;
        height: 20px;
    }
    .photo-item {
        position: relative;
    }
`;
document.head.appendChild(style);
</script>
@endpush

@endsection
