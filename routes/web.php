<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/email-verified', function () {
    return view('email-verified');
})->name('verification.verified');

require __DIR__.'/auth.php';
