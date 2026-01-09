<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accreditation extends Model
{
    protected $fillable = [
        'delegation_id',
        'nominative_registration_id',
        'badge_number',
        'qr_code_path',
        'access_zones',
        'status',
    ];

    protected $casts = [
        'access_zones' => 'array'
    ];

    public function member()
    {
        return $this->belongsTo(NominativeRegistration::class, 'nominative_registration_id');
    }

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }
}

