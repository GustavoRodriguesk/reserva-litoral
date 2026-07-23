<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Auth\IamUserProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Observers\ReservationObserver;
use App\Observers\RoomObserver;
use App\Observers\GuestObserver;

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

        // Registrar Observers Eloquent
        Reservation::observe(ReservationObserver::class);
        Room::observe(RoomObserver::class);
        Guest::observe(GuestObserver::class);
    }
}
