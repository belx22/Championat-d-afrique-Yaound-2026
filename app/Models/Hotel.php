<?php 


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'city',
        'standing',
        'description',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function photos()
    {
        return $this->hasMany(HotelPhoto::class)->orderBy('order');
    }

    /**
     * Scope: Hotels with available rooms
     */
    public function scopeWithAvailableRooms(Builder $query): Builder
    {
        return $query->whereHas('rooms', function($q) {
            $q->where('available_rooms', '>', 0);
        });
    }

    /**
     * Scope: Filter by city
     */
    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Scope: Filter by star rating
     */
    public function scopeByStanding(Builder $query, int $standing): Builder
    {
        return $query->where('standing', $standing);
    }

    /**
     * Scope: Search hotels
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('city', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Get total available rooms across all room types
     */
    public function getTotalAvailableRoomsAttribute(): int
    {
        return $this->rooms->sum('available_rooms');
    }

    /**
     * Get minimum price per night
     */
    public function getMinPriceAttribute(): ?float
    {
        return $this->rooms->where('available_rooms', '>', 0)->min('price');
    }
}
