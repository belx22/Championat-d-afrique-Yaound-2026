<?php

namespace App\Http\Controllers;

use App\Models\RoomReservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class AccommodationExportController extends Controller
{
    /**
     * Export reservations to Excel
     */
    public function exportReservations(Request $request)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $query = RoomReservation::with([
            'room.hotel',
            'delegation',
            'payments'
        ]);

        // Apply filters if provided (unless include_all is set)
        if (!$request->has('include_all')) {
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
                        $q->where('payment_type', Payment::PAYMENT_TYPE_FINAL)->where('status', 'valide');
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
        }

        $reservations = $query->orderBy('created_at', 'desc')->get();

        $filename = 'reservations_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($reservations) {
            $file = fopen('php://output', 'w');
            
            if ($file === false) {
                return;
            }
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID', 'Délégation', 'Pays', 'Hôtel', 'Ville', 'Type Chambre',
                'Nombre Chambres', 'Prix/Nuit', 'Nuits', 'Coût Total',
                'Date Check-in', 'Date Check-out', 'Paiement 50%', 'Paiement 100%',
                'Statut', 'Annulée', 'Date Réservation'
            ], ';');

            // Data
            foreach ($reservations as $reservation) {
                $payment50Status = $reservation->payment50 
                    ? ucfirst($reservation->payment50->status) 
                    : 'Non payé';
                $payment100Status = $reservation->payment100 
                    ? ucfirst($reservation->payment100->status) 
                    : 'Non payé';

                fputcsv($file, [
                    $reservation->id,
                    $reservation->delegation->federation_name ?? 'N/A',
                    $reservation->delegation->country ?? 'N/A',
                    $reservation->room->hotel->name ?? 'N/A',
                    $reservation->room->hotel->city ?? 'N/A',
                    ucfirst($reservation->room->type),
                    $reservation->rooms_reserved,
                    number_format($reservation->room->price, 0, ',', ' ') . ' FCFA',
                    $reservation->number_of_nights ?? 1,
                    number_format($reservation->total_cost, 0, ',', ' ') . ' FCFA',
                    $reservation->check_in_date ? $reservation->check_in_date->format('d/m/Y') : 'N/A',
                    $reservation->check_out_date ? $reservation->check_out_date->format('d/m/Y') : 'N/A',
                    $payment50Status,
                    $payment100Status,
                    ucfirst($reservation->status),
                    $reservation->is_cancelled ? 'Oui' : 'Non',
                    $reservation->created_at->format('d/m/Y H:i')
                ], ';');
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
