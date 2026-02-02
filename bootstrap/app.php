<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetDefaultGuardMiddleware;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
        $middleware->redirectGuestsTo(function (Request $request): string {
            $host = $request->getHost();
            $baseDomain = config('app.base_domain');

            // Se o host for o domínio do Admin
            if ($host === $baseDomain) {
                // Verifique se você nomeou sua rota de admin como 'admin.login'
                return route('admin.login');
            }

            $subdomain = explode('.', $host)[0];
            return route('tenant.login', ['subdomain' => $subdomain]);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Request $request) {
            if ($request->is('admin/*')) {
                return redirect()->route('admin.login');
            }

            return redirect()->route('tenant.login');
        });
    })->create();
