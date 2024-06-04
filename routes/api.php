<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Apis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});




// Registration steps
Route::post('/register', [Apis::class, 'createUser']);
Route::get('/verify-email/{token}', [Apis::class, 'verifyEmail']);


Route::get('/login', [Apis::class, 'loginUser'])->name('login');
Route::post('/login', [Apis::class, 'loginUser']);


Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['auth', 'signed', 'throttle:6,1'])
                ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['auth', 'throttle:6,1'])
                ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware('auth')
                ->name('logout');



// Route::get('/login', [Apis::class, 'loginUser'])->name('login');
// Route::post('/login', [Apis::class, 'loginUser']);


// Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
//                 ->middleware('guest')
//                 ->name('password.email');

// Route::post('/reset-password', [NewPasswordController::class, 'store'])
//                 ->middleware('guest')
//                 ->name('password.store');

// Route::middleware('auth:api')->group(function () {
//     Route::post('/create-pin', [Apis::class, 'createPin']);
//     Route::post('/upload-documents', [Apis::class, 'uploadDocuments']);
//     Route::post('/update-personal-info', [Apis::class, 'updatePersonalInfo']);
// });


