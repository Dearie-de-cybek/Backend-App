<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Apis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\Auth\VerifyEmailController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/register', [Apis::class, 'createUser']);
Route::post('/login', [Apis::class, 'loginUser'])->name('login');;







Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['auth', 'signed', 'throttle:6,1'])
                ->name('verification.verify');
