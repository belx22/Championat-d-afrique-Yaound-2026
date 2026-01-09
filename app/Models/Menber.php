<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'delegation_id',
        'full_name',
        'role',
        'gender',
        'date_of_birth',
        'arrival_date',
        'departure_date',
        'fig_id',
        'passport_path',
        'validated',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'validated' => 'boolean',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }

    public function accommodation()
    {
        return $this->hasOne(Accommodation::class);
    }

    public function accreditation()
    {
        return $this->hasOne(Accreditation::class);
    }
}
