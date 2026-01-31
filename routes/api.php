<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\HisReservationController;

Route::get('/his/reservations', [HisReservationController::class, 'index']);


