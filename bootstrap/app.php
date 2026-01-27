<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetDefaultGuardMiddleware;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
           'tenant' => IdentifyTenant::class,
           'guard.resolver' => SetDefaultGuardMiddleware::class,
           'guest.admin' => RedirectIfAdminAuthenticated::class,
        ]);
        $middleware->web(
            append: [
                HandleInertiaRequests::class,
            ],
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
