@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Détails de l'hôtel : {{ $hotel->name }}</h1>

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ $hotel->name }} {{ str_repeat('★', $hotel->standing) }}</h5>
        </div>
        <div class="card-body">
            @if($hotel->photos->count() > 0)
                {{-- Main Image Carousel --}}
                <div id="hotelCarousel" class="carousel slide mb-4" data-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($hotel->photos->sortBy('order') as $index => $photo)
                            @if($photo->photo_url)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <img src="{{ $photo->photo_url }}" 
                                         class="d-block w-100 hotel-main-image" 
                                         style="height: 400px; object-fit: cover; cursor: pointer;"
                                         alt="Hotel photo {{ $index + 1 }}"
                                         data-photo-index="{{ $index }}"
                                         onclick="openLightbox({{ $index }})"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMjAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjOTk5Ij5JbWFnZSBub3QgZm91bmQ8L3RleHQ+PC9zdmc+';">
                                    <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); padding: 5px; border-radius: 5px;">
                                        <small>Cliquez pour agrandir</small>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @if($hotel->photos->count() > 1)
                        <a class="carousel-control-prev" href="#hotelCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </a>
                        <a class="carousel-control-next" href="#hotelCarousel" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </a>
                    @endif
                </div>

                {{-- Thumbnail Gallery --}}
                @if($hotel->photos->count() > 1)
                <div class="row mb-4">
                    @foreach($hotel->photos->sortBy('order') as $index => $photo)
                        @if($photo->photo_url)
                            <div class="col-3 col-md-2 mb-2">
                                <img src="{{ $photo->photo_url }}" 
                                     class="img-thumbnail photo-thumbnail" 
                                     style="width: 100%; height: 80px; object-fit: cover; cursor: pointer; transition: transform 0.2s;"
                                     alt="Thumbnail {{ $index + 1 }}"
                                     data-photo-index="{{ $index }}"
                                     onclick="openLightbox({{ $index }})"
                                     onmouseover="this.style.transform='scale(1.1)'"
                                     onmouseout="this.style.transform='scale(1)'"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2RkZCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjEwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSIgZmlsbD0iIzk5OSI+Tm90IGZvdW5kPC90ZXh0Pjwvc3ZnPg==';">
                            </div>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Lightbox Modal --}}
                <div class="modal fade" id="photoLightbox" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content bg-dark">
                            <div class="modal-header border-0">
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body p-0 text-center">
                                <img id="lightboxImage" src="" class="img-fluid" style="max-height: 80vh; width: auto;">
                                <div class="text-white mt-2 mb-2">
                                    <span id="photoCounter"></span>
                                </div>
                            </div>
                            <div class="modal-footer border-0 justify-content-between">
                                <button type="button" class="btn btn-light" onclick="previousPhoto()">
                                    <i class="fas fa-chevron-left"></i> Précédent
                                </button>
                                <a id="downloadPhoto" href="" download class="btn btn-light">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                                <button type="button" class="btn btn-light" onclick="nextPhoto()">
                                    Suivant <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-4" style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; border-radius: 8px;">
                    <div class="text-center">
                        <i class="fas fa-hotel" style="font-size: 5rem; opacity: 0.7;"></i>
                        <p class="mb-0 mt-3" style="font-size: 1.2rem;">Aucune photo disponible</p>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <h5>Informations générales</h5>
                    <p><strong>Ville :</strong> {{ $hotel->city }}</p>
                    <p><strong>Standing :</strong> {{ str_repeat('★', $hotel->standing) }}</p>
                    <p><strong>Description :</strong></p>
                    <p>{{ $hotel->description ?? 'Aucune description disponible.' }}</p>
                </div>
            </div>

            <hr>

            <h5>Chambres disponibles</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Type</th>
                            <th>Prix par nuit</th>
                            <th>Capacité</th>
                            <th>Chambres disponibles</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotel->rooms as $room)
                            <tr class="{{ $room->available_rooms === 0 ? 'table-secondary' : '' }}">
                                <td>{{ ucfirst($room->type) }}</td>
                                <td>{{ number_format($room->price) }} FCFA</td>
                                <td>{{ $room->capacity }} personnes</td>
                                <td>
                                    @if($room->available_rooms > 0)
                                        <span class="badge badge-success">{{ $room->available_rooms }} / {{ $room->total_rooms }}</span>
                                    @else
                                        <span class="badge badge-danger">0 / {{ $room->total_rooms }}</span>
                                        <small class="text-muted d-block">Complet</small>
                                    @endif
                                </td>
                                <td>
                                    @if($room->available_rooms > 0)
                                        <button class="btn btn-sm btn-primary" 
                                                data-toggle="modal" 
                                                data-target="#reserveModal{{ $room->id }}">
                                            <i class="fas fa-calendar-check"></i> Réserver
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                data-toggle="modal" 
                                                data-target="#waitlistModal{{ $room->id }}">
                                            <i class="fas fa-list"></i> Liste d'attente
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune chambre disponible</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('accommodation.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    {{-- Reservation Modals --}}
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

    {{-- Waitlist Modals --}}
    @foreach($hotel->rooms as $room)
        @if($room->available_rooms === 0)
            <div class="modal fade" id="waitlistModal{{ $room->id }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="">
                            @csrf
                            <div class="modal-header bg-secondary text-white">
                                <h5 class="modal-title">Rejoindre la liste d'attente</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Liste d'attente :</strong> Vous serez automatiquement notifié par email si des chambres de ce type deviennent disponibles.
                                </div>
                                <p><strong>Hôtel :</strong> {{ $hotel->name }}</p>
                                <p><strong>Type :</strong> {{ ucfirst($room->type) }}</p>
                                <p><strong>Prix par nuit :</strong> {{ number_format($room->price) }} FCFA</p>
                                
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                <div class="form-group">
                                    <label>Nombre de chambres souhaitées *</label>
                                    <input type="number" 
                                           name="rooms_requested" 
                                           class="form-control" 
                                           min="1" 
                                           required
                                           value="1">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Date d'arrivée souhaitée (optionnel)</label>
                                        <input type="date" 
                                               name="check_in_date" 
                                               class="form-control"
                                               min="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Date de départ souhaitée (optionnel)</label>
                                        <input type="date" 
                                               name="check_out_date" 
                                               class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-list"></i> Rejoindre la liste d'attente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

</div>

@push('scripts')
<script>
// Photo Lightbox functionality
let currentPhotoIndex = 0;
const photos = [
    @foreach($hotel->photos->sortBy('order') as $photo)
        @if($photo->photo_url)
            '{{ $photo->photo_url }}',
        @endif
    @endforeach
];

function openLightbox(index) {
    currentPhotoIndex = index;
    const lightbox = document.getElementById('photoLightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const photoCounter = document.getElementById('photoCounter');
    const downloadLink = document.getElementById('downloadPhoto');
    
    if (lightboxImage && photos[index]) {
        lightboxImage.src = photos[index];
        photoCounter.textContent = `Photo ${index + 1} sur ${photos.length}`;
        downloadLink.href = photos[index];
        $(lightbox).modal('show');
    }
}

function nextPhoto() {
    currentPhotoIndex = (currentPhotoIndex + 1) % photos.length;
    updateLightboxImage();
}

function previousPhoto() {
    currentPhotoIndex = (currentPhotoIndex - 1 + photos.length) % photos.length;
    updateLightboxImage();
}

function updateLightboxImage() {
    const lightboxImage = document.getElementById('lightboxImage');
    const photoCounter = document.getElementById('photoCounter');
    const downloadLink = document.getElementById('downloadPhoto');
    
    if (lightboxImage && photos[currentPhotoIndex]) {
        lightboxImage.src = photos[currentPhotoIndex];
        photoCounter.textContent = `Photo ${currentPhotoIndex + 1} sur ${photos.length}`;
        downloadLink.href = photos[currentPhotoIndex];
    }
}

// Keyboard navigation in lightbox
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('photoLightbox');
    if ($(lightbox).hasClass('show')) {
        if (e.key === 'ArrowLeft') {
            previousPhoto();
        } else if (e.key === 'ArrowRight') {
            nextPhoto();
        } else if (e.key === 'Escape') {
            $(lightbox).modal('hide');
        }
    }
});

// Enhanced client-side validation with real-time feedback
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('is-invalid');
        let errorDiv = field.parentElement.querySelector('.invalid-feedback');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            field.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }
}

function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentElement.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

function validateDates(checkInId, checkOutId, roomId) {
    const checkIn = document.getElementById(checkInId);
    const checkOut = document.getElementById(checkOutId);
    
    if (!checkIn || !checkOut) return false;
    
    if (!checkIn.value) {
        showFieldError(checkInId, 'La date d\'arrivée est obligatoire.');
        return false;
    }
    
    if (!checkOut.value) {
        showFieldError(checkOutId, 'La date de départ est obligatoire.');
        return false;
    }
    
    const start = new Date(checkIn.value);
    const end = new Date(checkOut.value);
    
    if (end <= start) {
        showFieldError(checkOutId, 'La date de départ doit être après la date d\'arrivée.');
        checkOut.value = '';
        return false;
    }
    
    clearFieldError(checkInId);
    clearFieldError(checkOutId);
    return true;
}

function calculateNights(roomId, pricePerNight) {
    const checkIn = document.getElementById('check_in_' + roomId);
    const checkOut = document.getElementById('check_out_' + roomId);
    const roomsReserved = parseInt(document.getElementById('rooms_reserved_' + roomId).value) || 1;
    const costInfo = document.getElementById('cost_info_' + roomId);
    const totalCostSpan = document.getElementById('total_cost_' + roomId);
    const nightsInfoSpan = document.getElementById('nights_info_' + roomId);
    
    if (!costInfo || !totalCostSpan || !nightsInfoSpan) return;
    
    // Validate dates first
    if (!validateDates('check_in_' + roomId, 'check_out_' + roomId, roomId)) {
        costInfo.style.display = 'none';
        return;
    }
    
    if (checkIn.value && checkOut.value) {
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const nights = diffDays > 0 ? diffDays : 1;
        
        const totalCost = pricePerNight * roomsReserved * nights;
        
        totalCostSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalCost);
        nightsInfoSpan.textContent = `${nights} nuit(s) × ${roomsReserved} chambre(s) × ${new Intl.NumberFormat('fr-FR').format(pricePerNight)} FCFA/nuit`;
        costInfo.style.display = 'block';
        costInfo.classList.remove('alert-warning');
        costInfo.classList.add('alert-success');
    } else {
        costInfo.style.display = 'none';
    }
}

function calculateTotal(roomId, pricePerNight) {
    const roomsReserved = parseInt(document.getElementById('rooms_reserved_' + roomId).value) || 1;
    const checkIn = document.getElementById('check_in_' + roomId);
    const checkOut = document.getElementById('check_out_' + roomId);
    const costInfo = document.getElementById('cost_info_' + roomId);
    const totalCostSpan = document.getElementById('total_cost_' + roomId);
    const nightsInfoSpan = document.getElementById('nights_info_' + roomId);
    
    if (!costInfo || !totalCostSpan || !nightsInfoSpan) return;
    
    let nights = 1;
    if (checkIn && checkOut && checkIn.value && checkOut.value) {
        if (validateDates('check_in_' + roomId, 'check_out_' + roomId, roomId)) {
            const start = new Date(checkIn.value);
            const end = new Date(checkOut.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            nights = diffDays > 0 ? diffDays : 1;
        }
    }
    
    const totalCost = pricePerNight * roomsReserved * nights;
    
    totalCostSpan.textContent = new Intl.NumberFormat('fr-FR').format(totalCost);
    if (nights > 1) {
        nightsInfoSpan.textContent = `${nights} nuit(s) × ${roomsReserved} chambre(s) × ${new Intl.NumberFormat('fr-FR').format(pricePerNight)} FCFA/nuit`;
    } else {
        nightsInfoSpan.textContent = `1 nuit × ${roomsReserved} chambre(s) × ${new Intl.NumberFormat('fr-FR').format(pricePerNight)} FCFA/nuit`;
    }
    costInfo.style.display = 'block';
}

// Enhanced validation on form submit
document.addEventListener('DOMContentLoaded', function() {
    @foreach($hotel->rooms as $room)
        @if($room->available_rooms > 0)
            const checkIn{{ $room->id }} = document.getElementById('check_in_{{ $room->id }}');
            const checkOut{{ $room->id }} = document.getElementById('check_out_{{ $room->id }}');
            const roomsReserved{{ $room->id }} = document.getElementById('rooms_reserved_{{ $room->id }}');
            const form{{ $room->id }} = checkIn{{ $room->id }}?.closest('form');
            
            if (checkIn{{ $room->id }}) {
                checkIn{{ $room->id }}.addEventListener('change', function() {
                    if (checkOut{{ $room->id }} && checkOut{{ $room->id }}.value && checkOut{{ $room->id }}.value <= checkIn{{ $room->id }}.value) {
                        checkOut{{ $room->id }}.value = '';
                        showFieldError('check_out_{{ $room->id }}', 'La date de départ doit être après la date d\'arrivée.');
                    }
                    if (checkOut{{ $room->id }}) {
                        checkOut{{ $room->id }}.min = checkIn{{ $room->id }}.value;
                    }
                    calculateNights({{ $room->id }}, {{ $room->price }});
                });
            }
            
            if (checkOut{{ $room->id }}) {
                checkOut{{ $room->id }}.addEventListener('change', function() {
                    validateDates('check_in_{{ $room->id }}', 'check_out_{{ $room->id }}', {{ $room->id }});
                    calculateNights({{ $room->id }}, {{ $room->price }});
                });
            }
            
            if (roomsReserved{{ $room->id }}) {
                roomsReserved{{ $room->id }}.addEventListener('input', function() {
                    calculateNights({{ $room->id }}, {{ $room->price }});
                });
            }
            
            // Form submission validation
            if (form{{ $room->id }}) {
                form{{ $room->id }}.addEventListener('submit', function(e) {
                    if (!validateDates('check_in_{{ $room->id }}', 'check_out_{{ $room->id }}', {{ $room->id }})) {
                        e.preventDefault();
                        return false;
                    }
                    
                    if (!roomsReserved{{ $room->id }}.value || parseInt(roomsReserved{{ $room->id }}.value) < 1) {
                        e.preventDefault();
                        showFieldError('rooms_reserved_{{ $room->id }}', 'Veuillez sélectionner au moins une chambre.');
                        return false;
                    }
                    
                    if (parseInt(roomsReserved{{ $room->id }}.value) > {{ $room->available_rooms }}) {
                        e.preventDefault();
                        showFieldError('rooms_reserved_{{ $room->id }}', `Il ne reste que {{ $room->available_rooms }} chambre(s) disponible(s).`);
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = form{{ $room->id }}.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> En cours...';
                    }
                });
            }
        @endif
    @endforeach
});
</script>
@endpush
@endsection
