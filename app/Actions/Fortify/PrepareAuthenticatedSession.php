<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;

class PrepareAuthenticatedSession
{
    public function handle($request, $next)
    {
        $request->session()->regenerate();

        return $next($request);
    }
}
