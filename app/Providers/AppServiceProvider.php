<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Auth\IamUserProvider;
use Illuminate\Support\Facades\Auth;

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
        Auth::provider('iam_provider', function ($app, array $config) {
            return new IamUserProvider($app['hash'], $config['model']);
        });
    }
}
