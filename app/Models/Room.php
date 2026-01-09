<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Room extends Model
{
    protected $fillable = [
        'hotel_id',
        'type',
        'price',
        'capacity',
        'total_rooms',
        'available_rooms',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function reservations()
    {
        return $this->hasMany(RoomReservation::class);
    }

    /**
     * Scope: Available rooms only
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('available_rooms', '>', 0);
    }

    /**
     * Scope: By room type
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Check if room is available
     */
    public function isAvailable(): bool
    {
        return $this->available_rooms > 0;
    }

    /**
     * Get occupancy percentage
     */
    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->total_rooms == 0) {
            return 0;
        }
        return round((($this->total_rooms - $this->available_rooms) / $this->total_rooms) * 100, 2);
    }

    /**
     * Validate that available_rooms doesn't exceed total_rooms
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($room) {
            if ($room->available_rooms > $room->total_rooms) {
                throw new \InvalidArgumentException('Le nombre de chambres disponibles ne peut pas dépasser le nombre total de chambres.');
            }
        });
    }
}
