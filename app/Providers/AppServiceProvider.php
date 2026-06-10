<?php

namespace App\Providers;

use App\Http\Responses\Auth\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as FilamentLogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FilamentLogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate for Filament access: allow superadmin, admin and staff to enter Filament area
        Gate::define('viewFilament', fn($user) => $user && $user->hasAnyRole(['superadmin', 'admin', 'staff']));
    }
}
