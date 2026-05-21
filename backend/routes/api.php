<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Webhook\CryptoWebhookController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\CryptoDepositController;

Route::post('/webhooks/crypto', [CryptoWebhookController::class, 'handle']);

Route::get('/balance/{userId}', [LedgerController::class, 'balance']);

Route::post('/crypto/deposit', [CryptoDepositController::class, 'store']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
