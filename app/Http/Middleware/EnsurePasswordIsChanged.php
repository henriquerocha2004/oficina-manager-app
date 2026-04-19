<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('tenant');

        if (is_null($user) || !$user->must_change_password) {
            return $next($request);
        }

        $subdomain = explode('.', $request->getHost())[0];

        return redirect()->route('tenant.password.force-change', ['subdomain' => $subdomain]);
    }
}
