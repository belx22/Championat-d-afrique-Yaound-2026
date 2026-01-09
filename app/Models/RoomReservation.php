<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RoomReservation extends Model
{
    protected $fillable = [
        'delegation_id',
        'room_id',
        'rooms_reserved',
        'status',
        'is_cancelled',
        'cancelled_at',
        'cancellation_reason',
        'check_in_date',
        'check_out_date',
        'number_of_nights',
        'internal_notes',
    ];

    protected $casts = [
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payment50()
    {
        return $this->hasOne(Payment::class)->where('payment_type', Payment::PAYMENT_TYPE_PARTIAL);
    }

    public function payment100()
    {
        return $this->hasOne(Payment::class)->where('payment_type', Payment::PAYMENT_TYPE_FINAL);
    }

    public function getTotalCostAttribute(): float
    {
        $nights = $this->number_of_nights ?? 1;
        return $this->room->price * $this->rooms_reserved * $nights;
    }

    /**
     * Calculate number of nights from check-in and check-out dates
     */
    public function calculateNights(): ?int
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return null;
        }

        return max(1, $this->check_in_date->diffInDays($this->check_out_date));
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'is_cancelled' => true,
            'cancelled_at' => Carbon::now(),
            'cancellation_reason' => $reason ?? 'Payment deadline exceeded',
            'status' => 'rejete',
        ]);

        // Release the rooms back to availability
        $this->room->increment('available_rooms', $this->rooms_reserved);

        // Notification is sent from the controller to avoid circular dependencies
    }

    public function hasValidPayment50(): bool
    {
        return $this->payment50 && $this->payment50->status === 'valide';
    }

    public function hasValidPayment100(): bool
    {
        return $this->payment100 && $this->payment100->status === 'valide';
    }

    public function isFullyPaid(): bool
    {
        return $this->hasValidPayment50() && $this->hasValidPayment100();
    }
}
