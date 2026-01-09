<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelPhoto;
use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HotelController extends Controller
{
    private const MAX_PHOTOS = 10;
    private const PAGINATION_PER_PAGE = 12;
    private const CACHE_CITIES_TTL = 3600;
    private const CACHE_AVAILABLE_TTL = 1800;

    /**
     * Role-based accommodation index
     * Super Admin / Local Admin -> Hotel configuration
     * Federation Admin -> Hotel listing with reservation capability
     */
    public function index(Request $request)
    {
        $user = auth('championat')->user();
        
        if (in_array($user->role, ['super-admin', 'admin-local'])) {
            return $this->adminIndex($request);
        } elseif ($user->role === 'admin-federation') {
            return $this->federationIndex($request, $user);
        }

        abort(403, 'Access denied');
    }

    /**
     * Admin view: Hotel configuration with filters
     */
    private function adminIndex(Request $request)
    {
        $query = Hotel::with([
            'rooms' => function($q) {
                $q->select('hotel_id', 'id', 'type', 'price', 'capacity', 'total_rooms', 'available_rooms');
            },
            'photos' => function($q) {
                $q->select('id', 'hotel_id', 'photo_path', 'order')->orderBy('order');
            }
        ]);
        
        $query = $this->applyCommonFilters($query, $request);
        $query = $this->applyAdminFilters($query, $request);
        $query = $this->applySorting($query, $request);
        
        $hotels = $query->paginate(self::PAGINATION_PER_PAGE)->withQueryString();
        
        $cities = $this->getCachedCities();
        $stats = $this->getAdminStats();
        
        return view('accommodation.admin.index', compact('hotels', 'cities', 'stats', 'request'));
    }

    /**
     * Federation admin view: Hotel listing for reservations
     */
    private function federationIndex(Request $request, $user)
    {
        $query = Hotel::withAvailableRooms()
            ->with([
                'rooms' => function($q) {
                    $q->available()->select('id', 'hotel_id', 'type', 'price', 'capacity', 'available_rooms');
                },
                'photos' => function($q) {
                    $q->select('id', 'hotel_id', 'photo_path', 'order')->orderBy('order');
                }
            ]);
        
        $query = $this->applyCommonFilters($query, $request);
        $query = $this->applyFederationFilters($query, $request);
        $query = $this->applyFederationSorting($query, $request);
        
        $hotels = $query->paginate(self::PAGINATION_PER_PAGE)->withQueryString();
        
        $reservations = $this->getUserReservations($user->delegation_id);
        $cities = $this->getCachedAvailableCities();
        $roomTypes = $this->getCachedRoomTypes();
        $maxPrice = $this->getCachedMaxPrice();
        
        return view('accommodation.federation.index', compact('hotels', 'reservations', 'cities', 'roomTypes', 'maxPrice', 'request'));
    }

    /**
     * Apply common filters (used by both admin and federation views)
     */
    private function applyCommonFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        if ($request->filled('city')) {
            $query->inCity($request->city);
        }
        
        if ($request->filled('standing')) {
            $query->byStanding($request->standing);
        }
        
        return $query;
    }

    /**
     * Apply admin-specific filters
     */
    private function applyAdminFilters($query, Request $request)
    {
        if ($request->filled('min_price')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }
        
        if ($request->filled('max_price')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }
        
        if ($request->filled('has_available_rooms')) {
            $query->withAvailableRooms();
        }
        
        return $query;
    }

    /**
     * Apply federation-specific filters
     */
    private function applyFederationFilters($query, Request $request)
    {
        if ($request->filled('room_type')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('type', $request->room_type)->available();
            });
        }
        
        if ($request->filled('min_price')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('price', '>=', $request->min_price)->available();
            });
        }
        
        if ($request->filled('max_price')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('price', '<=', $request->max_price)->available();
            });
        }
        
        if ($request->filled('min_available_rooms')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('available_rooms', '>=', $request->min_available_rooms);
            });
        }
        
        return $query;
    }

    /**
     * Apply sorting for admin view
     */
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'city') {
            $query->orderBy('city', $sortOrder);
        } elseif ($sortBy === 'standing') {
            $query->orderBy('standing', $sortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', $sortOrder);
        }
        
        return $query;
    }

    /**
     * Apply sorting for federation view
     */
    private function applyFederationSorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if ($sortBy === 'price') {
            $query->orderByRaw('(SELECT MIN(price) FROM rooms WHERE rooms.hotel_id = hotels.id AND rooms.available_rooms > 0) ' . $sortOrder);
        } elseif ($sortBy === 'city') {
            $query->orderBy('city', $sortOrder);
        } elseif ($sortBy === 'standing') {
            $query->orderBy('standing', $sortOrder === 'asc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', $sortOrder);
        }
        
        return $query;
    }

    /**
     * Get cached cities for admin view
     */
    private function getCachedCities()
    {
        return Cache::remember('hotels_cities', self::CACHE_CITIES_TTL, function () {
            return Hotel::distinct()->pluck('city')->sort()->values();
        });
    }

    /**
     * Get cached cities for federation view
     */
    private function getCachedAvailableCities()
    {
        return Cache::remember('hotels_cities_available', self::CACHE_AVAILABLE_TTL, function () {
            return Hotel::withAvailableRooms()->distinct()->pluck('city')->sort()->values();
        });
    }

    /**
     * Get cached room types
     */
    private function getCachedRoomTypes()
    {
        return Cache::remember('available_room_types', self::CACHE_AVAILABLE_TTL, function () {
            return Room::available()->distinct()->pluck('type')->sort()->values();
        });
    }

    /**
     * Get cached max price
     */
    private function getCachedMaxPrice()
    {
        return Cache::remember('max_room_price', self::CACHE_AVAILABLE_TTL, function () {
            return Room::available()->max('price') ?? 0;
        });
    }

    /**
     * Get user reservations
     */
    private function getUserReservations($delegationId)
    {
        return RoomReservation::where('delegation_id', $delegationId)
            ->with([
                'room:id,hotel_id,type,price',
                'room.hotel:id,name,city',
                'payments:id,room_reservation_id,payment_type,status,receipt_path'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get admin statistics
     */
    private function getAdminStats()
    {
        $totalRooms = Room::sum('total_rooms');
        $availableRooms = Room::sum('available_rooms');
        $occupiedRooms = $totalRooms - $availableRooms;
        
        return [
            'total_hotels' => Hotel::count(),
            'total_rooms' => $totalRooms,
            'available_rooms' => $availableRooms,
            'occupied_rooms' => $occupiedRooms,
            'occupancy_rate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0,
            'total_reservations' => RoomReservation::count(),
            'active_reservations' => RoomReservation::where('is_cancelled', false)->count(),
            'pending_payments' => Payment::where('status', 'en_attente')->count(),
            'total_revenue_expected' => RoomReservation::where('is_cancelled', false)
                ->with('room')
                ->get()
                ->sum(function($reservation) {
                    return $reservation->total_cost;
                }),
        ];
    }

    /**
     * Show hotel details (for federation admin)
     */
    public function show(Hotel $hotel)
    {
        $user = auth('championat')->user();
        
        if ($user->role !== 'admin-federation') {
            abort(403, 'Access denied');
        }

        $hotel->load(['rooms', 'photos']);
        
        return view('accommodation.federation.show', compact('hotel'));
    }

    /**
     * Store hotel (Super Admin / Local Admin only)
     */
    public function store(Request $request)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'standing' => 'required|integer|min:2|max:5',
            'description' => 'nullable|string',
            'photos' => 'nullable|array|max:' . self::MAX_PHOTOS,
            'photos.*' => 'image|mimes:jpeg,jpg,png|max:5120',
        ]);

        $hotel = Hotel::create([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'standing' => $validated['standing'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->uploadPhotos($hotel, $request->file('photos', []));

        Log::info('Hotel created', [
            'hotel_id' => $hotel->id,
            'name' => $hotel->name,
            'city' => $hotel->city,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Hôtel créé avec succès.');
    }

    /**
     * Update hotel (Super Admin / Local Admin only)
     */
    public function update(Request $request, Hotel $hotel)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'standing' => 'required|integer|min:2|max:5',
            'description' => 'nullable|string',
            'photos' => 'nullable|array|max:' . self::MAX_PHOTOS,
            'photos.*' => 'image|mimes:jpeg,jpg,png|max:5120',
            'delete_photos' => 'nullable|array',
            'delete_photos.*' => 'exists:hotel_photos,id',
        ]);

        $hotel->update([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'standing' => $validated['standing'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->deletePhotos($hotel, $request->delete_photos ?? []);
        $this->updatePhotoOrder($hotel, $request->photo_orders);
        $this->uploadPhotos($hotel, $request->file('photos', []), true);

        Log::info('Hotel updated', [
            'hotel_id' => $hotel->id,
            'updated_by' => $user->id,
        ]);

        return back()->with('success', 'Hôtel mis à jour avec succès.');
    }

    /**
     * Upload photos for a hotel
     */
    private function uploadPhotos(Hotel $hotel, array $photos, bool $isUpdate = false)
    {
        if (empty($photos)) {
            return;
        }

        $currentPhotoCount = $hotel->photos()->count();
        $remainingSlots = self::MAX_PHOTOS - $currentPhotoCount;

        if ($remainingSlots <= 0) {
            return;
        }

        $photosToUpload = array_slice($photos, 0, $remainingSlots);
        
        foreach ($photosToUpload as $index => $photo) {
            $path = $photo->store("hotels/{$hotel->id}/photos", 'public');
            HotelPhoto::create([
                'hotel_id' => $hotel->id,
                'photo_path' => $path,
                'order' => $isUpdate ? $currentPhotoCount + $index : $index,
            ]);
        }
    }

    /**
     * Delete selected photos
     */
    private function deletePhotos(Hotel $hotel, array $photoIds)
    {
        if (empty($photoIds)) {
            return;
        }

        foreach ($photoIds as $photoId) {
            $photo = HotelPhoto::find($photoId);
            if ($photo && $photo->hotel_id === $hotel->id) {
                Storage::disk('public')->delete($photo->photo_path);
                $photo->delete();
            }
        }
    }

    /**
     * Update photo order
     */
    private function updatePhotoOrder(Hotel $hotel, ?string $photoOrdersJson)
    {
        if (!$photoOrdersJson) {
            return;
        }

        $photoOrders = json_decode($photoOrdersJson, true);
        if (!is_array($photoOrders)) {
            return;
        }

        foreach ($photoOrders as $order => $photoId) {
            HotelPhoto::where('id', $photoId)
                ->where('hotel_id', $hotel->id)
                ->update(['order' => $order]);
        }
    }

    /**
     * Delete hotel (Super Admin / Local Admin only)
     */
    public function destroy(Hotel $hotel)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        if ($this->hasActiveReservations($hotel)) {
            return back()->withErrors(['error' => 'Impossible de supprimer cet hôtel car il contient des réservations actives.']);
        }

        $this->deleteHotelPhotos($hotel);
        $hotel->delete();

        Log::info('Hotel deleted', [
            'hotel_id' => $hotel->id,
            'name' => $hotel->name,
            'deleted_by' => $user->id,
        ]);

        return back()->with('success', 'Hôtel supprimé avec succès.');
    }

    /**
     * Check if hotel has active reservations
     */
    private function hasActiveReservations(Hotel $hotel): bool
    {
        return RoomReservation::whereHas('room', function($q) use ($hotel) {
            $q->where('hotel_id', $hotel->id);
        })->exists();
    }

    /**
     * Delete all photos for a hotel
     */
    private function deleteHotelPhotos(Hotel $hotel): void
    {
        $photos = $hotel->photos()->pluck('photo_path')->toArray();
        Storage::disk('public')->delete($photos);
        $hotel->photos()->delete();
    }
}
