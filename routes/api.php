<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Apis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Auth\EmailVerificationPromptController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});




// Registration steps
Route::post('/register', [Apis::class, 'createUser']);
Route::get('/verify-email/{token}', [Apis::class, 'verifyEmail']);
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
 
    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
 
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');



Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
                    return view('auth.verify-email');
                })->middleware('auth')->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
                    $request->fulfill();
                 
                    return redirect('/home');

                })->middleware(['auth', 'signed'])->name('verification.verify');
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');


    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

});


// Additional steps after email verification (assuming middleware checks for verified email)
Route::post('/create-pin', [Apis::class, 'createPin'])->middleware('auth:api');
Route::post('/upload-documents', [Apis::class, 'uploadDocuments'])->middleware('auth:api');
Route::post('/update-personal-info', [Apis::class, 'updatePersonalInfo'])->middleware('auth:api');

// Existing login route (if applicable)
Route::post('/login', [Apis::class, 'loginUser'])->name('login');


