<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

// repositories
use App\Repositories\LocalUserRepository;
use App\Repositories\UserRepositoryInterface;

// payment gateway
use App\Services\Payment\GatewayService;
use App\Services\Payment\Gateways\SamanGateway;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Repository Bindings
        |--------------------------------------------------------------------------
        */

        // برای دمو فعلی از LocalUserRepository استفاده می‌کنیم
        $this->app->bind(
            UserRepositoryInterface::class,
            LocalUserRepository::class
        );

        /*
        |--------------------------------------------------------------------------
        | Payment Gateway Binding
        |--------------------------------------------------------------------------
        |
        | هر جا GatewayService درخواست شد،
        | پیاده‌سازی سامان تزریق می‌شود
        |
        */

        $this->app->bind(
            GatewayService::class,
            SamanGateway::class
        );
    }

    public function boot(): void
    {
        View::composer(
            ['layouts.sections.sidebar_content'],
            function ($view) {
                $user = auth()->user();
                $activeRole = session('active_role');

                $view->with('authUser', $user);
                $view->with('userRole', $activeRole);
            }
        );
    }
}
