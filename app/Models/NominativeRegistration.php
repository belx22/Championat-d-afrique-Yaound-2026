<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Accreditation;


class NominativeRegistration extends Model
{
    protected $fillable = [
        'delegation_id',
        'family_name',
        'given_name',
        'gender',
        'date_of_birth',
        'nationality',
        'passport_number',
        'passport_expiry_date',
        'passport_scan',
        'function',
        'discipline',
        'category',
        'fig_id',
        'photo_4x4',
        'music_file',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }

    public function isGymnast(): bool
    {
        return $this->function === 'gymnast';
    }

    public function isGAF(): bool
    {
        return $this->discipline === 'GAF';
    }

     public function accreditation()
    {
        return $this->hasOne(Accreditation::class);
    }
}
