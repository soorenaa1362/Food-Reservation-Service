<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Repositories\LocalUserRepository;
use App\Repositories\UserRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // برای دمو فعلی از LocalUserRepository استفاده می‌کنیم
        $this->app->bind(UserRepositoryInterface::class, LocalUserRepository::class);
        
        // وقتی API آماده شد، فقط این خط را تغییر بده:
        // $this->app->bind(UserRepositoryInterface::class, ApiUserRepository::class);
    }

    
    public function boot(): void
    {
        View::composer([
            'layouts.sections.sidebar_content'
        ], function ($view) {
            $user = auth()->user();
            $activeRole = session('active_role'); // نقشی که کاربر هنگام ورود انتخاب کرده

            $view->with('authUser', $user);
            $view->with('userRole', $activeRole);
        });
    }
}
