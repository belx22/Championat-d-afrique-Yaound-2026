<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payment extends Model
{
    const PAYMENT_TYPE_PARTIAL = 'partial_50';
    const PAYMENT_TYPE_FINAL = 'final_100';

    const PAYMENT_DEADLINE_50 = '2026-02-21';
    const PAYMENT_DEADLINE_100 = '2026-03-21';

    protected $fillable = [
        'room_reservation_id',
        'receipt_path',
        'status',
        'payment_type',
        'payment_deadline_50',
        'payment_deadline_100',
        'payment_made_at',
        'rejection_reason',
    ];

    protected $casts = [
        'payment_deadline_50' => 'date',
        'payment_deadline_100' => 'date',
        'payment_made_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(RoomReservation::class, 'room_reservation_id');
    }

    public function history()
    {
        return $this->hasMany(PaymentHistory::class);
    }

    /**
     * Record payment status change in history
     */
    public function recordHistory(string $statusAfter, ?string $notes = null, ?string $statusBefore = null): void
    {
        // Get old status before update (if not provided)
        $oldStatus = $statusBefore ?? $this->getOriginal('status') ?? $this->status;
        
        PaymentHistory::create([
            'payment_id' => $this->id,
            'status_before' => $oldStatus,
            'status_after' => $statusAfter,
            'notes' => $notes,
            'changed_by' => auth('championat')->id(),
        ]);
    }

    public function isOverdue(): bool
    {
        $deadline = $this->payment_type === self::PAYMENT_TYPE_PARTIAL 
            ? $this->payment_deadline_50 
            : $this->payment_deadline_100;

        if (!$deadline) {
            return false;
        }

        return Carbon::now()->isAfter($deadline) && $this->status !== 'valide';
    }

    public function getDeadlineAttribute(): ?Carbon
    {
        return $this->payment_type === self::PAYMENT_TYPE_PARTIAL 
            ? $this->payment_deadline_50 
            : $this->payment_deadline_100;
    }

    public function isPartialPayment(): bool
    {
        return $this->payment_type === self::PAYMENT_TYPE_PARTIAL;
    }

    public function isFinalPayment(): bool
    {
        return $this->payment_type === self::PAYMENT_TYPE_FINAL;
    }
}
