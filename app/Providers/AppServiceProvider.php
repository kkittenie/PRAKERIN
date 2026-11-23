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
        if (is_dir(resource_path('views/admin')) && !file_exists(resource_path('views/pages'))) {
            @symlink('admin', resource_path('views/pages'));
        }

        URL::forceRootUrl(config('app.url'));

        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if ($request->is('mobile/*')) {
                return Auth::guard('member')->check()
                    ? route('member.beranda')
                    : route('member.welcome');
            }

            return route('dashboard');
        });
    }
}