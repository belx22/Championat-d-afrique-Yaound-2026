<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class RoomController extends Controller
{
    public function store(Request $request)
    {
        $user = auth('championat')->user();
        
        // Only Super Admin and Local Admin can add rooms
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'type' => 'required|in:single,double,suite',
            'price' => 'required|integer|min:0',
            'capacity' => 'required|integer|min:1',
            'total_rooms' => 'required|integer|min:1',
        ]);

        $room = Room::create([
            'hotel_id' => $validated['hotel_id'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'capacity' => $validated['capacity'],
            'total_rooms' => $validated['total_rooms'],
            'available_rooms' => $validated['total_rooms'],
        ]);

        Log::info('Room created', [
            'room_id' => $room->id,
            'hotel_id' => $validated['hotel_id'],
            'type' => $validated['type'],
        ]);

        return back()->with('success', 'Chambre ajoutée avec succès.');
    }

    /**
     * Update room
     */
    public function update(Request $request, Room $room)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'type' => 'required|in:single,double,suite',
            'price' => 'required|integer|min:0',
            'capacity' => 'required|integer|min:1',
            'total_rooms' => 'required|integer|min:1',
        ]);

        // Check if new total_rooms is less than (total_rooms - available_rooms)
        $occupiedRooms = $room->total_rooms - $room->available_rooms;
        if ($validated['total_rooms'] < $occupiedRooms) {
            return back()->withErrors([
                'total_rooms' => "Impossible de réduire le nombre total de chambres à {$validated['total_rooms']}. Il y a actuellement {$occupiedRooms} chambre(s) occupée(s)."
            ])->withInput();
        }

        // Update available_rooms accordingly
        $newAvailableRooms = $validated['total_rooms'] - $occupiedRooms;

        $room->update([
            'type' => $validated['type'],
            'price' => $validated['price'],
            'capacity' => $validated['capacity'],
            'total_rooms' => $validated['total_rooms'],
            'available_rooms' => $newAvailableRooms,
        ]);

        Log::info('Room updated', [
            'room_id' => $room->id,
            'changes' => $validated,
        ]);

        return back()->with('success', 'Chambre mise à jour avec succès.');
    }

    /**
     * Delete room
     */
    public function destroy(Room $room)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        // Check if room has active reservations
        $hasActiveReservations = RoomReservation::where('room_id', $room->id)
            ->where('is_cancelled', false)
            ->exists();

        if ($hasActiveReservations) {
            return back()->withErrors([
                'error' => 'Impossible de supprimer cette chambre car elle contient des réservations actives.'
            ]);
        }

        Log::info('Room deleted', [
            'room_id' => $room->id,
            'hotel_id' => $room->hotel_id,
        ]);

        $room->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Chambre supprimée avec succès.']);
        }

        return back()->with('success', 'Chambre supprimée avec succès.');
    }
}
