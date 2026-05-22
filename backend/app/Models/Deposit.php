<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'wallet',
        'tx_hash',
        'amount',
        'token_address',
        'network',
        'status',
    ];
}
