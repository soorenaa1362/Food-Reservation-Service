<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ReserveController;
use App\Http\Controllers\User\SelectCenterController;
use App\Http\Controllers\User\FoodReservationController;
use App\Http\Controllers\User\Dashboard\DashboardController;
use App\Http\Controllers\User\CreditCard\CreditCardController;
use App\Http\Controllers\User\Transaction\TransactionController;

Route::middleware('auth')->group(function () {
    // select center
    Route::get('/select-center', [SelectCenterController::class, 'index'])->name('user.select-center.index');
    Route::post('/select-center', [SelectCenterController::class, 'select'])->name('user.select-center.select');

    // dashboard
    Route::get('/user/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');

    // food reservation 
    Route::get('/user/food-reservation', [FoodReservationController::class, 'index'])->name('user.food-reservation.index');
    Route::post('/user/food-reservation/cart', [FoodReservationController::class, 'cart'])->name('user.food-reservation.cart');
    Route::post('/user/food-reservation', [FoodReservationController::class, 'store'])->name('user.food-reservation.store');

    Route::post('/user/food-reservation/check-credit', [FoodReservationController::class, 'checkCredit'])->name('user.food-reservation.check-credit');

    // reserve
    Route::get('/user/reserves/index', [ReserveController::class, 'index'])->name('user.reserves.index');

    // credit card
    Route::get('/user/credit-card/index', [CreditCardController::class, 'index'])->name('user.credit-card.index');
    Route::get('/user/credit-card/increase', [CreditCardController::class, 'increase'])->name('user.credit-card.increase');
    // روت زیر برای زمانی بود که فرایند افزایش اعتبار رو بدون درگاه موفقیت آمیز در نظر میگرفتم
    Route::patch('/user/credit-card/increase-balance', [CreditCardController::class, 'increaseBalance'])->name('user.credit-card.increase-balance');

    // شروع فرآیند پرداخت آنلاین (وقتی کاربر دکمه پرداخت رو می‌زنه)
    Route::post('/user/transactions/start-payment', [TransactionController::class, 'startPayment'])
        ->name('user.transactions.start-payment');
    Route::get('/user/transactions/payment-callback', [TransactionController::class, 'paymentCallback'])
        ->name('user.transactions.payment-callback');
    
});