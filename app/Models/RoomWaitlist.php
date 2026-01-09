<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RoomWaitlist extends Model
{
    protected $table = 'room_waitlist';

    protected $fillable = [
        'delegation_id',
        'room_id',
        'rooms_requested',
        'check_in_date',
        'check_out_date',
        'notified',
        'notified_at',
    ];

    protected $casts = [
        'notified' => 'boolean',
        'notified_at' => 'datetime',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function markAsNotified(): void
    {
        $this->update([
            'notified' => true,
            'notified_at' => Carbon::now(),
        ]);
    }
}
