<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultGuardMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Detecta se é uma requisição admin (via prefix /admin)
        if ($request->is('admin/*') || $request->is('admin')) {
            config(['fortify.guard' => 'admin']);
            config(['fortify.passwords' => 'admin_users']);
            config(['fortify.home' => '/admin/dashboard']);
        } else {
            // Requisições de tenant (via subdomain)
            config(['fortify.guard' => 'tenant']);
            config(['fortify.passwords' => 'users']);
            config(['fortify.home' => '/dashboard']);
        }

        return $next($request);
    }
}
