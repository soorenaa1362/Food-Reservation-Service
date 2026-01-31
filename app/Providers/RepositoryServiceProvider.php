<?php

namespace App\Providers;

use App\Services\OtpService;
use App\Services\AuthService;
use App\Services\Menu\MenuService;
use App\Services\Center\CenterService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Menu\JsonMenuRepository;
use App\Repositories\User\JsonUserRepository;
use App\Services\CreditCard\CreditCardService;
use App\Repositories\Menu\MenuRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Center\EloquentCenterRepository;
use App\Repositories\CreditCard\CreditCardRepository;
use App\Repositories\Center\CenterRepositoryInterface;
use App\Repositories\CreditCard\CreditCardRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Repository
        $this->app->bind(UserRepositoryInterface::class, JsonUserRepository::class);
        $this->app->bind(CenterRepositoryInterface::class, EloquentCenterRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, JsonMenuRepository::class);
        $this->app->bind(CreditCardRepositoryInterface::class, CreditCardRepository::class);

        // Services
        $this->app->bind(OtpService::class, function ($app) {
            return new OtpService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(CenterService::class, function ($app) {
            return new CenterService($app->make(CenterRepositoryInterface::class));
        });

        $this->app->bind(MenuService::class, function ($app) {
            return new MenuService($app->make(MenuRepositoryInterface::class));
        });

        $this->app->bind(CreditCardService::class, function ($app) {
            return new CreditCardService($app->make(CreditCardRepositoryInterface::class));
        });
        
    }

    public function boot()
    {
        \Log::info('RepositoryServiceProvider loaded successfully.');
    }
}