<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomReservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AccommodationDashboardController extends Controller
{
    /**
     * Show accommodation dashboard (Super Admin / Local Admin only)
     */
    public function index()
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        // Cache statistics for 5 minutes (optimized with better eager loading)
        $cacheKey = 'accommodation_stats_' . $user->id; // Per-user cache to avoid conflicts
        $stats = Cache::remember($cacheKey, 300, function () {
            $totalHotels = Hotel::count();
            $totalRooms = Room::sum('total_rooms');
            $availableRooms = Room::sum('available_rooms');
            $occupiedRooms = $totalRooms - $availableRooms;
            
            $totalReservations = RoomReservation::count();
            $activeReservations = RoomReservation::where('is_cancelled', false)->count();
            $cancelledReservations = RoomReservation::where('is_cancelled', true)->count();
            
            $pendingPayment50 = Payment::where('status', 'en_attente')
                ->where('payment_type', Payment::PAYMENT_TYPE_PARTIAL)
                ->count();
            $pendingPayment100 = Payment::where('status', 'en_attente')
                ->where('payment_type', Payment::PAYMENT_TYPE_FINAL)
                ->count();
            
            // Calculate revenue (optimized with select specific columns)
            $totalRevenue = RoomReservation::where('is_cancelled', false)
                ->with(['room:id,hotel_id,price'])
                ->get()
                ->sum(function($reservation) {
                    $nights = $reservation->number_of_nights ?? 1;
                    return $reservation->room->price * $reservation->rooms_reserved * $nights;
                });
            
            $validatedRevenue = RoomReservation::where('is_cancelled', false)
                ->whereHas('payments', function($q) {
                    $q->where('payment_type', Payment::PAYMENT_TYPE_FINAL)
                      ->where('status', 'valide');
                })
                ->with(['room:id,hotel_id,price'])
                ->get()
                ->sum(function($reservation) {
                    $nights = $reservation->number_of_nights ?? 1;
                    return $reservation->room->price * $reservation->rooms_reserved * $nights;
                });
            
            // Occupancy by hotel
            $hotelOccupancy = Hotel::with('rooms')->get()->map(function($hotel) {
                $totalRooms = $hotel->rooms->sum('total_rooms');
                $availableRooms = $hotel->rooms->sum('available_rooms');
                $occupied = $totalRooms - $availableRooms;
                
                return [
                    'name' => $hotel->name,
                    'total' => $totalRooms,
                    'occupied' => $occupied,
                    'available' => $availableRooms,
                    'occupancy_rate' => $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0,
                ];
            })->sortByDesc('occupancy_rate')->take(10);
            
            // Recent reservations
            $recentReservations = RoomReservation::with(['room.hotel', 'delegation'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Reservations by status
            $reservationsByStatus = [
                'en_attente' => RoomReservation::where('is_cancelled', false)
                    ->where('status', 'en_attente')
                    ->count(),
                'valide' => RoomReservation::where('is_cancelled', false)
                    ->where('status', 'valide')
                    ->count(),
                'rejete' => RoomReservation::where('is_cancelled', false)
                    ->where('status', 'rejete')
                    ->count(),
                'cancelled' => RoomReservation::where('is_cancelled', true)->count(),
            ];
            
            // Reservations evolution (last 30 days)
            $reservationsEvolution = [];
            $revenueEvolution = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->startOfDay();
                $endDate = $date->copy()->endOfDay();
                
                $reservationsEvolution[] = RoomReservation::whereBetween('created_at', [$date, $endDate])->count();
                
                $revenueEvolution[] = RoomReservation::whereBetween('created_at', [$date, $endDate])
                    ->where('is_cancelled', false)
                    ->with(['room:id,hotel_id,price'])
                    ->get()
                    ->sum(function($reservation) {
                        $nights = $reservation->number_of_nights ?? 1;
                        return $reservation->room->price * $reservation->rooms_reserved * $nights;
                    });
            }
            
            // Revenue by month (last 6 months) - Optimized
            $monthlyRevenue = [];
            $monthlyLabels = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthlyLabels[] = $month->format('M Y');
                
                $monthlyRevenue[] = RoomReservation::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('is_cancelled', false)
                    ->with(['room:id,hotel_id,price'])
                    ->get()
                    ->sum(function($reservation) {
                        $nights = $reservation->number_of_nights ?? 1;
                        return $reservation->room->price * $reservation->rooms_reserved * $nights;
                    });
            }
            
            // Top hotels by revenue - Optimized with select
            $hotelsRevenue = Hotel::select('id', 'name')
                ->with(['rooms' => function($q) {
                    $q->select('id', 'hotel_id', 'price')
                      ->with(['reservations' => function($q) {
                          $q->select('id', 'room_id', 'rooms_reserved', 'number_of_nights', 'is_cancelled')
                            ->where('is_cancelled', false);
                      }]);
                }])
                ->get()
                ->map(function($hotel) {
                    $revenue = $hotel->rooms->sum(function($room) {
                        return $room->reservations->sum(function($reservation) use ($room) {
                            $nights = $reservation->number_of_nights ?? 1;
                            return $room->price * $reservation->rooms_reserved * $nights;
                        });
                    });
                    return [
                        'name' => $hotel->name,
                        'revenue' => $revenue,
                    ];
                })->sortByDesc('revenue')->take(10);
            
            return [
                'total_hotels' => $totalHotels,
                'total_rooms' => $totalRooms,
                'available_rooms' => $availableRooms,
                'occupied_rooms' => $occupiedRooms,
                'occupancy_rate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0,
                'total_reservations' => $totalReservations,
                'active_reservations' => $activeReservations,
                'cancelled_reservations' => $cancelledReservations,
                'pending_payment_50' => $pendingPayment50,
                'pending_payment_100' => $pendingPayment100,
                'total_revenue' => $totalRevenue,
                'validated_revenue' => $validatedRevenue,
                'hotel_occupancy' => $hotelOccupancy,
                'recent_reservations' => $recentReservations,
                'reservations_by_status' => $reservationsByStatus,
                'reservations_evolution' => $reservationsEvolution,
                'revenue_evolution' => $revenueEvolution,
                'monthly_revenue' => $monthlyRevenue,
                'monthly_labels' => $monthlyLabels,
                'hotels_revenue' => $hotelsRevenue,
            ];
        });

        return view('accommodation.admin.dashboard', compact('stats'));
    }
}
