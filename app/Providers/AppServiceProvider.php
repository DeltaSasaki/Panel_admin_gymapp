<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom Blade directive for permission checking
        Blade::if('hasPermission', function (string $permissionCode) {
            return auth()->check() && auth()->user()->hasPermission($permissionCode);
        });

        Blade::if('canAccess', function (string $permissionCode) {
            return auth()->check() && auth()->user()->hasPermission($permissionCode);
        });
    }
}
