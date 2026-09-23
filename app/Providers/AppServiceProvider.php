<?php

namespace App\Providers;

use App\Models\Bumdes;
use App\Models\Pelanggan;
use App\Models\UnitUsaha;
use App\Policies\BumdesPolicy;
use App\Policies\PelangganPolicy;
use App\Policies\UnitUsahaPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Bumdes::class, BumdesPolicy::class);
        Gate::policy(UnitUsaha::class, UnitUsahaPolicy::class);
        Gate::policy(Pelanggan::class, PelangganPolicy::class);

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                $request->ip() . '|' . strtolower((string) $request->input('username')),
            );
        });
    }
}
