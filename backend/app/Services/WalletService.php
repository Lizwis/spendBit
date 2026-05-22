<?php

namespace App\Services;

use App\Models\User;

class WalletService
{
    public function credit(User $user, float $amount)
    {
        $user->balance = ($user->balance ?? 0) + $amount;
        $user->save();

        return $user->balance;
    }
}
