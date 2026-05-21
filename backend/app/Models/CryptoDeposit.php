<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoDeposit extends Model
{
    protected $fillable = [
        'user_id',
        'tx_hash',
        'from_address',
        'to_address',
        'amount',
        'currency',
        'chain',
        'confirmations',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
