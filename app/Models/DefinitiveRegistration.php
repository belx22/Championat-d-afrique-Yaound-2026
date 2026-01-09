<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DefinitiveRegistration extends Model
{
    protected $fillable = [
        'delegation_id',
        'mag_junior',
        'mag_senior',
        'wag_junior',
        'wag_senior',
        'gymnast_team',
        'gymnast_individuals',
        'coach',
        'judges_total',
        'head_of_delegation',
        'doctor_paramedics',
        'team_manager',
        'status',
        'signed_document',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }

    public function totalMembers(): int
    {
        return
            $this->mag_junior +
            $this->mag_senior +
            $this->wag_junior +
            $this->wag_senior +
            $this->gymnast_team +
            $this->gymnast_individuals +
            $this->coach +
            $this->judges_total +
            $this->head_of_delegation +
            $this->doctor_paramedics +
            $this->team_manager;
    }
}
