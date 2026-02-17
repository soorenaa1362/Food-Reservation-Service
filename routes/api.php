<?php

use App\Http\Controllers\API\HisCreditLedgerSyncController;
use App\Http\Controllers\API\HisReservationController;
use App\Http\Controllers\API\HisSyncController;
use Illuminate\Support\Facades\Route;

Route::prefix('his')->middleware('his.auth')->group(function () {
    // Sync initial data
    Route::post('/sync/all', [HisSyncController::class, 'syncAll']);
    Route::post('/sync/centers', [HisSyncController::class, 'syncCenters']);
    Route::post('/sync/users',   [HisSyncController::class, 'syncUsers']);
    Route::post('/sync/menus',   [HisSyncController::class, 'syncMenus']);

    // Sync credit ledger
    // Route::post('/credit-ledgers/sync', [HisCreditLedgerSyncController::class, 'sync']);

    Route::get('/reservations', [HisReservationController::class, 'index']);
});

Route::prefix('his')->group(function () {
    Route::post('/credit-ledgers/sync', [HisCreditLedgerSyncController::class, 'sync']);
});
