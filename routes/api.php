<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\HisReservationController;

// گروه‌بندی برای اعمال middleware امنیتی
Route::prefix('his')->middleware('his.auth')->group(function () {
    Route::get('/reservations', [HisReservationController::class, 'index']);
});