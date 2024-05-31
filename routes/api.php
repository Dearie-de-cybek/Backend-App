<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Apis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\Auth\VerifyEmailController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});




// Registration steps
Route::post('/register', [Apis::class, 'createUser']);
Route::get('/verify-email/{token}', [Apis::class, 'verifyEmail'])->middleware('auth:api');

// Additional steps after email verification (assuming middleware checks for verified email)
Route::post('/create-pin', [Apis::class, 'createPin'])->middleware('auth:api');
Route::post('/upload-documents', [Apis::class, 'uploadDocuments'])->middleware('auth:api');
Route::post('/update-personal-info', [Apis::class, 'updatePersonalInfo'])->middleware('auth:api');

// Existing login route (if applicable)
Route::post('/login', [Apis::class, 'loginUser'])->name('login');


