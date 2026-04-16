<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use App\Services\Tenant\Auth\TenantSessionBinding;

class PrepareAuthenticatedSession
{
    public function __construct(
        private TenantSessionBinding $tenantSessionBinding
    ) {
    }

    public function handle($request, $next)
    {
        $request->session()->regenerate();
        if (!($request instanceof Request)) {
            return $next($request);
        }

        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        $this->tenantSessionBinding->bindToCurrentTenant(
            request: $request,
            remember: $request->boolean('remember'),
        );

        return $next($request);
    }
}
