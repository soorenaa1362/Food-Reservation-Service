<?php

use Illuminate\Support\Facades\Route;

// صفحه‌ی خوش‌آمدگویی
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// روت‌های مربوط به احراز هویت
require __DIR__ . '/auth.php';

// روت‌های مربوط به پنل کاربر و رزرو غذا
require __DIR__ . '/user.php';