<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPermission
{
    /**
     * Rota padrão de redirecionamento por perfil quando o acesso é negado.
     */
    private array $defaultRoutes = [
        'mechanic'  => 'service-orders.index',
        'reception' => 'clients.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('tenant');

        if (!$user) {
            return $next($request);
        }

        $role = $user->role instanceof \BackedEnum
            ? $user->role->value
            : (string) $user->role;

        $permissions = config('tenant_permissions', []);
        $allowedPatterns = $permissions[$role] ?? [];

        if ($this->isAllowed($request, $allowedPatterns)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->header('X-Inertia')) {
            abort(403, 'Acesso não autorizado.');
        }

        $redirectRoute = $this->defaultRoutes[$role] ?? 'clients.index';

        return redirect()->route($redirectRoute);
    }

    private function isAllowed(Request $request, array $patterns): bool
    {
        if (in_array('*', $patterns, true)) {
            return true;
        }

        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return true;
        }

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }
}
