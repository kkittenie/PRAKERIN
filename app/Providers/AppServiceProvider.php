<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $listen = [
        App\Events\StatusProduksiUpdate::class => [
            App\Listeners\UpdateStatusProduksi::class,
        ],
        App\Events\PesananPickupUpdated::class => [
            App\Listeners\KirimNotifikasiMemberPesanan::class,
        ],
    ];
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
        URL::forceRootUrl(config('app.url'));

        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if (Auth::guard('member')->check()) {
                return route('member.beranda');
            }

            return route('dashboard');
        });
    }
}