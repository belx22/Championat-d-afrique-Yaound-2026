<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class DelegationInfo extends Model
{
    protected $fillable = [
        'delegation_id',
        'arrival_date',
        'departure_date',
        'flag_image',
        'national_anthem',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }
}

