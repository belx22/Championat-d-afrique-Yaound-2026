<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\Payment;
use App\Models\Delegation;
use App\Notifications\Accommodation\ReservationCreatedNotification;
use App\Notifications\Accommodation\ReservationCancelledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RoomReservationController extends Controller
{
    /**
     * Show reservations index
     * - Super Admin / Local Admin: See ALL reservations from all delegations
     * - Federation Admin: See only their own delegation's reservations
     */
    public function index(Request $request)
    {
        $user = auth('championat')->user();
        
        if (in_array($user->role, ['super-admin', 'admin-local'])) {
            // Admin view: All reservations with filters
            $query = RoomReservation::with([
                'room:id,hotel_id,type,price',
                'room.hotel:id,name,city',
                'delegation:id,country,federation_name,contact_person,email,phone',
                'payments:id,room_reservation_id,payment_type,status,receipt_path,created_at'
            ]);
            
            // Filters
            if ($request->filled('delegation_id')) {
                $query->where('delegation_id', $request->delegation_id);
            }
            
            if ($request->filled('status')) {
                if ($request->status === 'cancelled') {
                    $query->where('is_cancelled', true);
                } elseif ($request->status === 'active') {
                    $query->where('is_cancelled', false);
                } elseif ($request->status === 'paid') {
                    $query->whereHas('payments', function($q) {
                        $q->where('payment_type', 'final_100')->where('status', 'valide');
                    });
                } elseif ($request->status === 'urgent') {
                    $urgentDeadline = Carbon::now()->addDays(7);
                    $now = Carbon::now();
                    $query->where('is_cancelled', false)
                        ->whereHas('payments', function($q) use ($urgentDeadline, $now) {
                            $q->where('status', 'en_attente')
                              ->where(function($q2) use ($urgentDeadline, $now) {
                                  $q2->where(function($q3) use ($urgentDeadline, $now) {
                                      $q3->where('payment_type', Payment::PAYMENT_TYPE_PARTIAL)
                                         ->where('payment_deadline_50', '<=', $urgentDeadline)
                                         ->where('payment_deadline_50', '>', $now);
                                  })->orWhere(function($q3) use ($urgentDeadline, $now) {
                                      $q3->where('payment_type', Payment::PAYMENT_TYPE_FINAL)
                                         ->where('payment_deadline_100', '<=', $urgentDeadline)
                                         ->where('payment_deadline_100', '>', $now);
                                  });
                              });
                        });
                }
            }
            
            if ($request->filled('hotel_id')) {
                $query->whereHas('room', function($q) use ($request) {
                    $q->where('hotel_id', $request->hotel_id);
                });
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $allReservations = $query->get();
            $reservations = $allReservations->groupBy('delegation_id');
            
            // Statistics with eager loading for better performance
            $now = \Carbon\Carbon::now();
            $urgentDeadline = $now->copy()->addDays(7);
            
            $stats = [
                'total' => RoomReservation::count(),
                'active' => RoomReservation::where('is_cancelled', false)->count(),
                'cancelled' => RoomReservation::where('is_cancelled', true)->count(),
                'pending_payment' => RoomReservation::where('is_cancelled', false)
                    ->whereDoesntHave('payments', function($q) {
                        $q->where('payment_type', 'final_100')->where('status', 'valide');
                    })->count(),
                'pending_payment_50' => Payment::where('status', 'en_attente')
                    ->where('payment_type', Payment::PAYMENT_TYPE_PARTIAL)
                    ->count(),
                'pending_payment_100' => Payment::where('status', 'en_attente')
                    ->where('payment_type', Payment::PAYMENT_TYPE_FINAL)
                    ->count(),
                'urgent_payments' => Payment::where('status', 'en_attente')
                    ->where(function($q) use ($urgentDeadline, $now) {
                        $q->where(function($q2) use ($urgentDeadline, $now) {
                            $q2->where('payment_type', Payment::PAYMENT_TYPE_PARTIAL)
                               ->where('payment_deadline_50', '<=', $urgentDeadline)
                               ->where('payment_deadline_50', '>', $now);
                        })->orWhere(function($q2) use ($urgentDeadline, $now) {
                            $q2->where('payment_type', Payment::PAYMENT_TYPE_FINAL)
                               ->where('payment_deadline_100', '<=', $urgentDeadline)
                               ->where('payment_deadline_100', '>', $now);
                        });
                    })
                    ->count(),
                'total_revenue' => RoomReservation::where('is_cancelled', false)
                    ->with('room')
                    ->get()
                    ->sum(function($reservation) {
                        return $reservation->total_cost;
                    }),
                'validated_revenue' => RoomReservation::where('is_cancelled', false)
                    ->whereHas('payments', function($q) {
                        $q->where('payment_type', 'final_100')->where('status', 'valide');
                    })
                    ->with('room')
                    ->get()
                    ->sum(function($reservation) {
                        return $reservation->total_cost;
                    }),
            ];
            
            // Get delegations and hotels for filters
            $delegations = \App\Models\Delegation::select('id', 'country', 'federation_name')->orderBy('country')->get();
            $hotels = \App\Models\Hotel::select('id', 'name', 'city')->orderBy('name')->get();
            
            return view('accommodation.admin.reservations', compact('reservations', 'stats', 'delegations', 'hotels', 'request'));
        } elseif ($user->role === 'admin-federation') {
            // Federation admin view: Only their own reservations
            
                $delegation = \App\Models\Delegation::where('user_id',$user->id)->first();
                
                // MODE TEST

                if(!$delegation){
                    abort(403,'Délegation non  trouvé');

                }

                $delegationId = $delegation->id;
            
            $query = RoomReservation::where('delegation_id', $delegationId)
                ->with([
                    'room:id,hotel_id,type,price',
                    'room.hotel:id,name,city',
                    'delegation:id,country,federation_name,contact_person,email,phone',
                    'payments:id,room_reservation_id,payment_type,status,receipt_path,created_at'
                ]);
            
            // Filters
            if ($request->filled('status')) {
                if ($request->status === 'cancelled') {
                    $query->where('is_cancelled', true);
                } elseif ($request->status === 'active') {
                    $query->where('is_cancelled', false);
                } elseif ($request->status === 'paid') {
                    $query->whereHas('payments', function($q) {
                        $q->where('payment_type', 'final_100')->where('status', 'valide');
                    });
                }
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $allReservations = $query->get();
            $reservations = $allReservations->groupBy('delegation_id');

            return view('accommodation.federation.reservations', compact('reservations', 'request'));
        }

        abort(403, 'Access denied ');
    }

    /**
     * Store reservation (Federation Admin)
     * First come, first serve basis
     */
    public function store(Request $request)
    {
        $user = auth('championat')->user();
        
        if ($user->role !== 'admin-federation') {
            abort(403, 'Access denied');
        }


        $delegation = \App\Models\Delegation::where('user_id',$user->id)->first();
        
        // MODE TEST

        if(!$delegation){
            abort(403,'Délegation non  trouvé');

        }

        $delegationId = $delegation->id;

        // Check if user has a delegation_id
        if (!$delegationId) {
            return back()->withErrors([
                'error' => 'Votre compte n\'est pas associé à une délégation. Veuillez contacter l\'administrateur.'
            ])->withInput();
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'rooms_reserved' => 'required|integer|min:1',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ], [
            'check_in_date.required' => 'La date d\'arrivée est obligatoire.',
            'check_in_date.date' => 'La date d\'arrivée doit être une date valide.',
            'check_in_date.after_or_equal' => 'La date d\'arrivée doit être aujourd\'hui ou une date future.',
            'check_out_date.required' => 'La date de départ est obligatoire.',
            'check_out_date.date' => 'La date de départ doit être une date valide.',
            'check_out_date.after' => 'La date de départ doit être après la date d\'arrivée.',
        ]);

        // Calculate number of nights
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $numberOfNights = max(1, $checkIn->diffInDays($checkOut));
        $validated['number_of_nights'] = $numberOfNights;

        $room = Room::findOrFail($validated['room_id']);

        // Check availability - first come, first serve
        if ($room->available_rooms < $validated['rooms_reserved']) {
            $message = $room->available_rooms > 0 
                ? sprintf('Nombre de chambres insuffisant. Il ne reste que %d chambre(s) disponible(s) pour ce type.', $room->available_rooms)
                : 'Désolé, toutes les chambres de ce type sont actuellement réservées. Veuillez choisir un autre type de chambre.';
            
            return back()->withErrors([
                'rooms_reserved' => $message
            ])->withInput();
        }

        // Use database transaction to ensure atomicity
        try {
            $reservation = DB::transaction(function () use ($room, $validated, $delegationId) {
                // Lock the room availability (atomic operation)
                $lockedRoom = Room::lockForUpdate()->findOrFail($room->id);

                // Double-check availability after locking (race condition protection)
                if ($lockedRoom->available_rooms < $validated['rooms_reserved']) {
                    $message = $lockedRoom->available_rooms > 0
                        ? sprintf('Désolé, quelqu\'un vient de réserver des chambres. Il ne reste maintenant que %d chambre(s) disponible(s). Veuillez rafraîchir la page et réessayer.', $lockedRoom->available_rooms)
                        : 'Désolé, toutes les chambres de ce type viennent d\'être réservées par un autre utilisateur. Veuillez rafraîchir la page et choisir un autre type de chambre.';
                    
                    throw new \Exception($message);
                }

                // Decrement available rooms
                $lockedRoom->decrement('available_rooms', $validated['rooms_reserved']);

                // Create reservation
                $reservation = RoomReservation::create([
                    'delegation_id' => $delegationId,
                    'room_id' => $lockedRoom->id,
                    'rooms_reserved' => $validated['rooms_reserved'],
                    'status' => 'en_attente',
                    'check_in_date' => $validated['check_in_date'],
                    'check_out_date' => $validated['check_out_date'],
                    'number_of_nights' => $validated['number_of_nights'],
                ]);

                // Log successful reservation
                Log::info('Reservation created', [
                    'reservation_id' => $reservation->id,
                    'delegation_id' => $delegationId,
                    'room_id' => $lockedRoom->id,
                    'rooms_reserved' => $validated['rooms_reserved'],
                ]);

                return $reservation;
            });

            // Send notification to federation admin
            $user = auth('championat')->user();
           // $user->notify(new ReservationCreatedNotification($reservation));

            return back()->with('success', 'Réservation effectuée avec succès. Veuillez effectuer le paiement de 50% avant le 21 février 2026.');
        } catch (\Exception $e) {
            Log::error('Reservation creation failed', [
                'error' => $e->getMessage(),
                'delegation_id' => $delegationId,
                'room_id' => $room->id,
                'rooms_reserved' => $validated['rooms_reserved'],
            ]);

            // If reservation failed due to unavailability, offer waitlist option
            if (strpos($e->getMessage(), 'chambre') !== false || strpos($e->getMessage(), 'disponible') !== false) {
                // Option to join waitlist could be shown here
                Log::info('Reservation failed - rooms unavailable', [
                    'delegation_id' => $delegationId,
                    'room_id' => $room->id,
                    'requested' => $validated['rooms_reserved'],
                ]);
            }

            return back()->withErrors([
                'rooms_reserved' => $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Add to waitlist (Federation Admin)
     */
    public function addToWaitlist(Request $request)
    {
        $user = auth('championat')->user();
        
        if ($user->role !== 'admin-federation') {
            abort(403, 'Access denied');
        }

        $user = auth('championat')->user(); 

        $delegation = \App\Models\Delegation::where('user_id',$user->id)->first();
        
        // MODE TEST

        if(!$delegation){
            abort(403,'Délegation non  trouvé');

        }

        $delegationId = $delegation->id;

        if (!$delegationId) {
            return back()->withErrors([
                'error' => 'Votre compte n\'est pas associé à une délégation. Veuillez contacter l\'administrateur.'
            ])->withInput();
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'rooms_requested' => 'required|integer|min:1',
            'check_in_date' => 'nullable|date|after_or_equal:today',
            'check_out_date' => 'nullable|date|after:check_in_date',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Check if already on waitlist
        $existing = \App\Models\RoomWaitlist::where('delegation_id', $delegationId)
            ->where('room_id', $validated['room_id'])
            ->first();

        if ($existing) {
            return back()->withErrors([
                'error' => 'Vous êtes déjà sur la liste d\'attente pour ce type de chambre.'
            ])->withInput();
        }

        \App\Models\RoomWaitlist::create([
            'delegation_id' => $delegationId,
            'room_id' => $validated['room_id'],
            'rooms_requested' => $validated['rooms_requested'],
            'check_in_date' => $validated['check_in_date'] ?? null,
            'check_out_date' => $validated['check_out_date'] ?? null,
        ]);

        Log::info('Added to waitlist', [
            'delegation_id' => $delegationId,
            'room_id' => $validated['room_id'],
        ]);

        return back()->with('success', 'Vous avez été ajouté à la liste d\'attente. Vous serez notifié si des chambres deviennent disponibles.');
    }

    /**
     * Show reservation details
     * - Super Admin / Local Admin: Can view any reservation
     * - Federation Admin: Can only view their own delegation's reservations
     */
    public function show(RoomReservation $reservation)
    {
        $user = auth('championat')->user();
        
        // Check access based on role
        if (in_array($user->role, ['super-admin', 'admin-local'])) {
            // Admins can view any reservation
            $reservation->load([
                'room.hotel',
                'delegation',
                'payments.history.changedBy:id,email',
                'payments' => function($q) {
                    $q->with(['history' => function($q) {
                        $q->with('changedBy:id,email')->orderBy('created_at', 'desc');
                    }]);
                }
            ]);
            return view('accommodation.admin.reservation_show', compact('reservation'));
        } elseif ($user->role === 'admin-federation') {
            // Federation admin can only view their own reservations
            if ($reservation->delegation_id !== $user->delegation_id) {
                abort(403, 'Vous ne pouvez pas accéder au réservation des autres délégations.');
                              
            }
            $reservation->load([
                'room.hotel',
                'payments' => function($q) {
                    $q->with(['history' => function($q) {
                        $q->orderBy('created_at', 'desc');
                    }]);
                }
            ]);
            return view('accommodation.federation.reservation_show', compact('reservation'));
        }

        abort(403, 'Access denied');
    }

    /**
     * Cancel reservation manually (Super Admin / Local Admin only)
     */
    public function cancel(Request $request, RoomReservation $reservation)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        if ($reservation->is_cancelled) {
            return back()->withErrors(['error' => 'Cette réservation est déjà annulée.']);
        }

        $reason = $validated['cancellation_reason'] ?? 'Annulation manuelle par administrateur';
        $reservation->cancel($reason);

        // Send notification to delegation
        if ($reservation->delegation && $reservation->delegation->user) {
            $reservation->delegation->user->notify(
                new ReservationCancelledNotification($reservation, $reason)
            );
        }

        Log::info('Reservation cancelled manually', [
            'reservation_id' => $reservation->id,
            'delegation_id' => $reservation->delegation_id,
            'reason' => $reason,
        ]);

        return back()->with('success', 'Réservation annulée avec succès.');
    }

    /**
     * Update internal notes (Super Admin / Local Admin only)
     */
    public function updateNotes(Request $request, RoomReservation $reservation)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'internal_notes' => 'nullable|string|max:2000',
        ]);

        $reservation->update([
            'internal_notes' => $validated['internal_notes'] ?? null,
        ]);

        Log::info('Reservation notes updated', [
            'reservation_id' => $reservation->id,
            'updated_by' => $user->id,
        ]);

        return back()->with('success', 'Notes internes mises à jour avec succès.');
    }
}
