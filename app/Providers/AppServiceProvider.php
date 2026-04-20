<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BookingService;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind services to container
        $this->app->singleton(BookingService::class, function ($app) {
            return new BookingService($app->make(NotificationService::class));
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
