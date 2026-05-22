<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'wallet',
        'user_id',
        'tx_hash',
        'amount',
        'token_address',
        'network',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
