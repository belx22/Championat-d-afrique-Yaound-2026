<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HotelPhoto extends Model
{
    protected $fillable = [
        'hotel_id',
        'photo_path',
        'order',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the full URL for the photo
     */
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo_path) {
            return null;
        }

        // Check if file exists, if not return null (will show placeholder in view)
        if (!Storage::disk('public')->exists($this->photo_path)) {
            return null;
        }

        // Use asset() helper which generates URL relative to public directory
        // This works better than Storage::url() which might use APP_URL from .env
        // asset('storage/...') creates URL that works with current request domain
        return asset('storage/' . $this->photo_path);
    }
}
