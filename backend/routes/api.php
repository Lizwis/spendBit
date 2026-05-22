<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CryptoDepositController;
use App\Http\Controllers\AuthController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/crypto/deposit', [CryptoDepositController::class, 'store']);
    Route::get('/crypto/deposits', [CryptoDepositController::class, 'history']);
    Route::get('/wallet/balance', [CryptoDepositController::class, 'balance']);


    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
});
