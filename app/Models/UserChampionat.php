<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserChampionat extends Authenticatable
{
    use Notifiable;

    protected $table = 'user_championat';

    protected $fillable = [
        'email',
        'password',
        'role',
        'status',
        'delegation_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /* ======================
       Relations
    ====================== */

    public function delegation()
    {
        return $this->belongsTo(Delegation::class);
    }

    /* ======================
       Helpers métier
    ====================== */

    public function isActive(): bool
    {
        return $this->status === 'actif';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super-admin';
    }

    public function isAdminLocal(): bool
    {
        return $this->role === 'admin-local';
    }

    public function isAdminFederation(): bool
    {
        return $this->role === 'admin-federation';
    }

       public function delegationByEmail(){
        return $this->hosOne(\App\Models\Delegation::class,'user_id');
    }
}
