<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\HisApiAuth;  // ← این خط رو اضافه کن

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // مستثنی کردن CSRF برای مسیرهای خاص (همون چیزی که داشتی)
        $middleware->validateCsrfTokens(except: [
            'user/transactions/payment-callback',
            // 'user/transactions/*',  ← اگر همه زیرمسیرها رو می‌خوای مستثنی کنی
        ]);

        // ثبت alias برای middleware سفارشی شما
        $middleware->alias([
            'his.auth' => HisApiAuth::class,
        ]);

        // اگر بعداً خواستی این middleware رو به صورت global (روی همه درخواست‌ها) اجرا کنی:
        // $middleware->append(HisApiAuth::class);
        // یا prepend برای اول صف
        // $middleware->prepend(HisApiAuth::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();