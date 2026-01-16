<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Delegation;

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

    public function badgesPdf(Delegation $delegation)
    {
        $members = $delegation->nominativeRegistrations()
            ->with('accreditation')
            ->orderBy('function')
            ->orderBy('family_name')
            ->get();

        $pdf = Pdf::loadView(
            'admin.accreditations.badges-pdf',
            compact('delegation','members')
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'badges_'.$delegation->country.'.pdf'
        );
    }
}



