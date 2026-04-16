<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Laravel\Fortify\Fortify;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Fortify::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RedirectIfAuthenticated::redirectUsing(function (Request $request): string {
            if ($request->user('admin')) {
                return '/admin/dashboard';
            }

            $user = $request->user('tenant');

            if ($user) {
                $role = $user->role instanceof \BackedEnum
                    ? $user->role->value
                    : (string) $user->role;

                return match ($role) {
                    'mechanic' => '/service-orders',
                    default    => '/clients',
                };
            }

            return '/';
        });
    }
}
