<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RoomReservation;
use App\Models\Delegation;
use App\Notifications\Accommodation\PaymentValidatedNotification;
use App\Notifications\Accommodation\PaymentRejectedNotification;
use App\Notifications\Accommodation\PaymentReminderNotification;
use App\Notifications\Accommodation\ReservationCancelledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Store payment receipt (Federation Admin)
     * Can upload either 50% or 100% payment
     */
    public function store(Request $request, RoomReservation $reservation)
    {
        $user = auth('championat')->user();
        
        // Verify that the reservation belongs to the user's delegation
        if ($user->role !== 'admin-federation' || $reservation->delegation_id !== $user->delegation_id) {
            abort(403, 'Access denied');
        }

        // Check if reservation is cancelled
        if ($reservation->is_cancelled) {
            return back()->withErrors(['error' => 'Cette réservation a été annulée.']);
        }

        $request->validate([
            'payment_type' => 'required|in:partial_50,final_100',
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB
        ]);

        // Check if payment of this type already exists
        $existingPayment = Payment::where('room_reservation_id', $reservation->id)
            ->where('payment_type', $request->payment_type)
            ->first();

        if ($existingPayment) {
            return back()->withErrors(['error' => 'Un paiement de ce type existe déjà.']);
        }

        // For 100% payment, ensure 50% is already validated
        if ($request->payment_type === Payment::PAYMENT_TYPE_FINAL) {
            $payment50 = $reservation->payment50;
            if (!$payment50 || $payment50->status !== 'valide') {
                return back()->withErrors(['error' => 'Le paiement de 50% doit être validé avant de payer les 100%.']);
            }
        }

        $path = $request->file('receipt')->store("payments/{$reservation->id}", 'public');

        $deadline50 = Carbon::parse(Payment::PAYMENT_DEADLINE_50);
        $deadline100 = Carbon::parse(Payment::PAYMENT_DEADLINE_100);

        Payment::create([
            'room_reservation_id' => $reservation->id,
            'receipt_path' => $path,
            'status' => 'en_attente',
            'payment_type' => $request->payment_type,
            'payment_deadline_50' => $deadline50,
            'payment_deadline_100' => $deadline100,
            'payment_made_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Reçu envoyé avec succès.');
    }

    /**
     * Validate payment (Super Admin / Local Admin only)
     */
    public function validatePayment(Payment $payment)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $oldStatus = $payment->status;
        
        $payment->update([
            'status' => 'valide',
            'payment_made_at' => Carbon::now(),
        ]);

        // Record history (pass old status explicitly since update() changes getOriginal())
        $payment->recordHistory('valide', 'Paiement validé par ' . $user->email, $oldStatus);

        // Update reservation status if fully paid
        $reservation = $payment->reservation;
        if ($reservation->isFullyPaid()) {
            $reservation->update(['status' => 'valide']);
        }

        // Send notification to delegation
        if ($reservation->delegation && $reservation->delegation->user) {
            $reservation->delegation->user->notify(
                new PaymentValidatedNotification($payment)
            );
        }

        Log::info('Payment validated', [
            'payment_id' => $payment->id,
            'reservation_id' => $payment->room_reservation_id,
            'payment_type' => $payment->payment_type,
            'changed_by' => $user->id,
        ]);

        return back()->with('success', 'Paiement validé.');
    }

    /**
     * Reject payment (Super Admin / Local Admin only)
     */
    public function rejectPayment(Request $request, Payment $payment)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $oldStatus = $payment->status;
        
        $payment->update([
            'status' => 'rejete',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Record history (pass old status explicitly since update() changes getOriginal())
        $payment->recordHistory('rejete', 'Paiement rejeté: ' . $validated['rejection_reason'], $oldStatus);

        // Send notification to delegation
        $reservation = $payment->reservation;
        if ($reservation->delegation && $reservation->delegation->user) {
            $reservation->delegation->user->notify(
                new PaymentRejectedNotification($payment)
            );
        }

        Log::info('Payment rejected', [
            'payment_id' => $payment->id,
            'reservation_id' => $payment->room_reservation_id,
            'reason' => $validated['rejection_reason'],
            'changed_by' => $user->id,
        ]);

        return back()->with('success', 'Paiement rejeté avec raison indiquée.');
    }

    /**
     * Check and cancel reservations with overdue payments
     * This should be run as a scheduled task
     * Can be called from console command
     */
    public static function cancelOverdueReservations()
    {
        $now = Carbon::now();
        $cancelledCount = 0;
        
        // Find reservations with overdue 50% payments
        $overdue50 = Payment::where('payment_type', Payment::PAYMENT_TYPE_PARTIAL)
            ->where('status', 'en_attente')
            ->where('payment_deadline_50', '<', $now)
            ->with('reservation')
            ->get();

        foreach ($overdue50 as $payment) {
            $reservation = $payment->reservation;
            if ($reservation && !$reservation->is_cancelled) {
                $reason = 'Délai de paiement de 50% dépassé';
                $reservation->cancel($reason);
                
                // Send notification
                if ($reservation->delegation && $reservation->delegation->user) {
                    $reservation->delegation->user->notify(
                        new ReservationCancelledNotification($reservation, $reason)
                    );
                }
                
                $cancelledCount++;
            }
        }

        // Find reservations with overdue 100% payments
        $overdue100 = Payment::where('payment_type', Payment::PAYMENT_TYPE_FINAL)
            ->where('status', 'en_attente')
            ->where('payment_deadline_100', '<', $now)
            ->with('reservation')
            ->get();

        foreach ($overdue100 as $payment) {
            $reservation = $payment->reservation;
            if ($reservation && !$reservation->is_cancelled) {
                $reason = 'Délai de paiement de 100% dépassé';
                $reservation->cancel($reason);
                
                // Send notification
                if ($reservation->delegation && $reservation->delegation->user) {
                    $reservation->delegation->user->notify(
                        new ReservationCancelledNotification($reservation, $reason)
                    );
                }
                
                $cancelledCount++;
            }
        }

        return $cancelledCount;
    }

    /**
     * Validate multiple payments in bulk (Super Admin / Local Admin only)
     */
    public function validateBulk(Request $request)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'required|exists:payments,id',
        ]);

        $validatedCount = 0;
        
        foreach ($validated['payment_ids'] as $paymentId) {
            $payment = Payment::find($paymentId);
            
            if ($payment && $payment->status === 'en_attente') {
                $oldStatus = $payment->status;
                $payment->update([
                    'status' => 'valide',
                    'payment_made_at' => Carbon::now(),
                ]);

                // Record history (pass old status explicitly since update() changes getOriginal())
                $payment->recordHistory('valide', 'Paiement validé en masse par ' . $user->email, $oldStatus);

                // Update reservation status if fully paid
                $reservation = $payment->reservation;
                if ($reservation && $reservation->isFullyPaid()) {
                    $reservation->update(['status' => 'valide']);
                }

                // Send notification to delegation
                if ($reservation && $reservation->delegation && $reservation->delegation->user) {
                    $reservation->delegation->user->notify(
                        new PaymentValidatedNotification($payment)
                    );
                }

                $validatedCount++;
                
                Log::info('Payment validated (bulk)', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $payment->room_reservation_id,
                    'changed_by' => $user->id,
                ]);
            }
        }

        return back()->with('success', "{$validatedCount} paiement(s) validé(s) avec succès.");
    }

    /**
     * Reject multiple payments in bulk (Super Admin / Local Admin only)
     */
    public function rejectBulk(Request $request)
    {
        $user = auth('championat')->user();
        
        if (!in_array($user->role, ['super-admin', 'admin-local'])) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'payment_ids' => 'required|array|min:1',
            'payment_ids.*' => 'required|exists:payments,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        $rejectedCount = 0;
        
        foreach ($validated['payment_ids'] as $paymentId) {
            $payment = Payment::find($paymentId);
            
            if ($payment && $payment->status === 'en_attente') {
                $oldStatus = $payment->status;
                $payment->update([
                    'status' => 'rejete',
                    'rejection_reason' => $validated['rejection_reason'],
                ]);

                // Record history (pass old status explicitly since update() changes getOriginal())
                $payment->recordHistory('rejete', 'Paiement rejeté en masse: ' . $validated['rejection_reason'], $oldStatus);

                // Send notification to delegation
                $reservation = $payment->reservation;
                if ($reservation && $reservation->delegation && $reservation->delegation->user) {
                    $reservation->delegation->user->notify(
                        new PaymentRejectedNotification($payment)
                    );
                }

                $rejectedCount++;
                
                Log::info('Payment rejected (bulk)', [
                    'payment_id' => $payment->id,
                    'reservation_id' => $payment->room_reservation_id,
                    'reason' => $validated['rejection_reason'],
                    'changed_by' => $user->id,
                ]);
            }
        }

        return back()->with('success', "{$rejectedCount} paiement(s) rejeté(s) avec succès.");
    }
}
