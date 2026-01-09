<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Delegation extends Model
{
    protected $fillable = [
        'country',
        'federation_name',
        'contact_person',
        'email',
        'phone',
        'user_id',
    ];

   public function user()
{
    return $this->belongsTo(UserChampionat::class, 'user_id');
}

    public function provisionalRegistration()
    {
        return $this->hasOne(ProvisionalRegistration::class);
    }

    public function definitiveRegistration()
    {
        return $this->hasOne(DefinitiveRegistration::class);
    }

    public function nominativeRegistrations() {
        return $this->hasMany(NominativeRegistration::class);
    }
    public function delegationInfo() {
        return $this->hasOne(DelegationInfo::class);
    }

    

public function hasAnyFile(): bool
{
    /*
    |--------------------------------------------------------------------------
    | Delegation Info
    |--------------------------------------------------------------------------
    */
    if ($this->delegationInfo) {
        if (
            ($this->delegationInfo->flag_image &&
             Storage::disk('public')->exists($this->delegationInfo->flag_image)) ||
            ($this->delegationInfo->national_anthem &&
             Storage::disk('public')->exists($this->delegationInfo->national_anthem))
        ) {
            return true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Provisional Registration
    |--------------------------------------------------------------------------
    */
    if (
        $this->provisionalRegistration &&
        $this->provisionalRegistration->signed_document &&
        Storage::disk('public')->exists($this->provisionalRegistration->signed_document)
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Definitive Registration
    |--------------------------------------------------------------------------
    */
    if (
        $this->definitiveRegistration &&
        $this->definitiveRegistration->signed_document &&
        Storage::disk('public')->exists($this->definitiveRegistration->signed_document)
    ) {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Nominative Members
    |--------------------------------------------------------------------------
    */
    foreach ($this->nominativeRegistrations as $member) {

        if (
            ($member->passport_scan &&
             Storage::disk('public')->exists($member->passport_scan)) ||
            ($member->photo_4x4 &&
             Storage::disk('public')->exists($member->photo_4x4)) ||
            ($member->music_file &&
             Storage::disk('public')->exists($member->music_file))
        ) {
            return true;
        }
    }

    return false;
}


}
