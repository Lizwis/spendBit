<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LedgerService;

class LedgerController extends Controller
{
    public function balance($userId)
    {
        return response()->json([
            'balance' => app(LedgerService::class)->getBalance($userId)
        ]);
    }
}
