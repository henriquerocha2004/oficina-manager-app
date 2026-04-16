<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetDefaultGuardMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Detecta se é uma requisição admin (via prefix /admin)
        if ($request->is('admin/*') || $request->is('admin')) {
            Auth::shouldUse('admin');
            config(['fortify.guard' => 'admin']);
            config(['fortify.passwords' => 'admin_users']);
            config(['fortify.home' => '/admin/dashboard']);
        } else {
            // Requisições de tenant (via subdomain)
            Auth::shouldUse('tenant');
            config(['fortify.guard' => 'tenant']);
            config(['fortify.passwords' => 'users']);
            config(['fortify.home' => '/clients']);
        }

        return $next($request);
    }
}
