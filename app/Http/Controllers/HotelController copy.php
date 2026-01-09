<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\HotelPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    /**
     * Role-based accommodation index
     * Super Admin / Local Admin -> Hotel configuration
     * Federation Admin -> Hotel listing with reservation capability
     */
    public function index(Request $request)
    {
        $user = auth('championat')->user();
        
        if (in_array($user->role, ['super-admin', 'admin-local'])) {
            // Admin view: Hotel configuration with filters
            $query = Hotel::with(['rooms:hotel_id,id,type,price,capacity,total_rooms,available_rooms', 'photos:id,hotel_id,photo_path,order']);
            
            // Apply filters
            if ($request->filled('search')) {
                $query->search($request->search);
            }
            
            if ($request->filled('city')) {
                $query->inCity($request->city);
            }
            
            if ($request->filled('standing')) {
                $query->byStanding($request->standing);
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if ($sortBy === 'city') {
                $query->orderBy('city', $sortOrder);
            } elseif ($sortBy === 'standing') {
                $query->orderBy('standing', $sortOrder === 'asc' ? 'desc' : 'asc'); // Higher stars first
            } else {
                $query->orderBy('name', $sortOrder);
            }
            
            $hotels = $query->paginate(12)->withQueryString();
            
            // Get unique cities for filter
            $cities = Hotel::distinct()->pluck('city')->sort()->values();
            
            // Statistics
            $totalRooms = \App\Models\Room::sum('total_rooms');
            $availableRooms = \App\Models\Room::sum('available_rooms');
            $occupiedRooms = $totalRooms - $availableRooms;
            
            $stats = [
                'total_hotels' => Hotel::count(),
                'total_rooms' => $totalRooms,
                'available_rooms' => $availableRooms,
                'occupied_rooms' => $occupiedRooms,
                'occupancy_rate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0,
                'total_reservations' => \App\Models\RoomReservation::count(),
                'active_reservations' => \App\Models\RoomReservation::where('is_cancelled', false)->count(),
                'pending_payments' => \App\Models\Payment::where('status', 'en_attente')->count(),
                'total_revenue_expected' => \App\Models\RoomReservation::where('is_cancelled', false)
                    ->with('room')
                    ->get()
                    ->sum(function($reservation) {
                        return $reservation->total_cost;
                    }),
            ];
            
            return view('accommodation.admin.index', compact('hotels', 'cities', 'stats', 'request'));
        } elseif ($user->role === 'admin-federation') {
            // Federation admin view: Hotel listing for reservations
            $delegationId = $user->delegation_id;
            
            $query = Hotel::withAvailableRooms()
                ->with([
                    'rooms' => function($q) {
                        $q->available()->select('id', 'hotel_id', 'type', 'price', 'capacity', 'available_rooms');
                    },
                    'photos:id,hotel_id,photo_path,order'
                ]);
            
            // Apply filters
            if ($request->filled('search')) {
                $query->search($request->search);
            }
            
            if ($request->filled('city')) {
                $query->inCity($request->city);
            }
            
            if ($request->filled('standing')) {
                $query->byStanding($request->standing);
            }
            
            if ($request->filled('room_type')) {
                $query->whereHas('rooms', function($q) use ($request) {
                    $q->where('type', $request->room_type)->available();
                });
            }
            
            if ($request->filled('max_price')) {
                $query->whereHas('rooms', function($q) use ($request) {
                    $q->where('price', '<=', $request->max_price)->available();
                });
            }
            
            // Sorting
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
            
            $hotels = $query->paginate(12)->withQueryString();
            
            // Get user's reservations with optimized loading
            $reservations = \App\Models\RoomReservation::where('delegation_id', $delegationId)
                ->with([
                    'room:id,hotel_id,type,price',
                    'room.hotel:id,name,city',
                    'payments:id,room_reservation_id,payment_type,status,receipt_path'
                ])
                ->orderBy('created_at', 'desc')
                ->limit(5) // Show only recent 5 in the summary
                ->get();
            
            // Get unique cities and room types for filters
            $cities = Hotel::withAvailableRooms()->distinct()->pluck('city')->sort()->values();
            $roomTypes = \App\Models\Room::available()->distinct()->pluck('type')->sort()->values();
            $maxPrice = \App\Models\Room::available()->max('price');
            
            return view('accommodation.federation.index', compact('hotels', 'reservations', 'cities', 'roomTypes', 'maxPrice', 'request'));
        }

        abort(403, 'Access denied');
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
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'image|mimes:jpeg,jpg,png|max:5120', // 5MB per photo
        ]);

        $hotel = Hotel::create([
            'name' => $validated['name'],
            'city' => $validated['city'],
            'standing' => $validated['standing'],
            'description' => $validated['description'] ?? null,
        ]);

        // Upload photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store("hotels/{$hotel->id}/photos", 'public');
                HotelPhoto::create([
                    'hotel_id' => $hotel->id,
                    'photo_path' => $path,
                    'order' => $index,
                ]);
            }
        }

        Log::info('Hotel created', [
            'hotel_id' => $hotel->id,
            'name' => $hotel->name,
            'city' => $hotel->city,
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
            'photos' => 'nullable|array|max:10',
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

        // Delete selected photos
        if ($request->has('delete_photos')) {
            foreach ($request->delete_photos as $photoId) {
                $photo = HotelPhoto::find($photoId);
                if ($photo) {
                    Storage::disk('public')->delete($photo->photo_path);
                    $photo->delete();
                }
            }
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            $currentPhotoCount = $hotel->photos()->count();
            $maxPhotos = 10;
            $remainingSlots = $maxPhotos - $currentPhotoCount;

            if ($remainingSlots > 0) {
                $uploadedPhotos = $request->file('photos');
                $photosToUpload = array_slice($uploadedPhotos, 0, $remainingSlots);
                
                foreach ($photosToUpload as $index => $photo) {
                    $path = $photo->store("hotels/{$hotel->id}/photos", 'public');
                    HotelPhoto::create([
                        'hotel_id' => $hotel->id,
                        'photo_path' => $path,
                        'order' => $currentPhotoCount + $index,
                    ]);
                }
            }
        }

        return back()->with('success', 'Hôtel mis à jour avec succès.');
    }

    /**
     * Update photo order (Super Admin / Local Admin only)
     */
    public function updatePhotoOrder(Request $request, Hotel $hotel)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'photo_orders' => 'required|array',
            'photo_orders.*' => 'required|integer|exists:hotel_photos,id',
        ]);

        foreach ($validated['photo_orders'] as $order => $photoId) {
            HotelPhoto::where('id', $photoId)
                ->where('hotel_id', $hotel->id)
                ->update(['order' => $order]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordre des photos mis à jour avec succès.'
        ]);
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

        // Check if hotel has active reservations
        $hasReservations = \App\Models\RoomReservation::whereHas('room', function($q) use ($hotel) {
            $q->where('hotel_id', $hotel->id);
        })->exists();

        if ($hasReservations) {
            return back()->withErrors(['error' => 'Impossible de supprimer cet hôtel car il contient des réservations actives.']);
        }

        // Delete all photos in batch
        $photos = $hotel->photos()->pluck('photo_path')->toArray();
        Storage::disk('public')->delete($photos);
        $hotel->photos()->delete();

        Log::info('Hotel deleted', [
            'hotel_id' => $hotel->id,
            'name' => $hotel->name,
        ]);

        $hotel->delete();

        return back()->with('success', 'Hôtel supprimé avec succès.');
    }
}
