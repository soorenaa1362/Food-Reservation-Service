<?php

use App\Http\Controllers\Auth\LoginController;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'sendOTP'])->name('sendOTP');

Route::get('/verify', [LoginController::class, 'showVerifyForm'])->name('verify');
Route::post('/verify', [LoginController::class, 'verifyOTP'])->name('verifyOTP');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');