<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $table = 'payment_history';

    protected $fillable = [
        'payment_id',
        'status_before',
        'status_after',
        'notes',
        'changed_by',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(UserChampionat::class, 'changed_by');
    }
}
