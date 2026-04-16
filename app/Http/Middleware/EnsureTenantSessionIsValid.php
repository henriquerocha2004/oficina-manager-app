<?php

namespace App\Http\Middleware;

use App\Services\Tenant\Auth\TenantSessionBinding;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSessionIsValid
{
    public function __construct(
        private TenantSessionBinding $tenantSessionBinding
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user('tenant')) {
            return $next($request);
        }

        if (!$this->tenantSessionBinding->hasBinding($request)) {
            $this->tenantSessionBinding->bindToCurrentTenant($request);

            return $next($request);
        }

        if (!$this->tenantSessionBinding->matchesCurrentTenant($request)) {
            Auth::guard('tenant')->logout();
            $this->tenantSessionBinding->clear($request);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw new AuthenticationException(
                message: 'Unauthenticated.',
                guards: ['tenant'],
                redirectTo: route('tenant.login', $this->tenantSessionBinding->loginRouteParameters($request)),
            );
        }

        $this->tenantSessionBinding->syncSession($request);

        return $next($request);
    }
}
