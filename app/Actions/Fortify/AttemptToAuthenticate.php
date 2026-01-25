<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Illuminate\Support\Facades\DB;

class AttemptToAuthenticate
{
    protected $limiter;

    public function __construct(LoginRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle($request, $next)
    {
        $guardName = $this->extractGuardFromRequest($request);

        if ($guardName === "tenant") {
            DB::setDefaultConnection(config('database.connections_names.tenant'));
        }

        $guard = auth()->guard($guardName);

        if (
                $guard->attempt(
                    $request->only(Fortify::username(), "password"),
                    $request->boolean("remember")
                )
        ) {
            return $next($request);
        }

        $this->throwFailedAuthenticationException($request);
    }

    protected function throwFailedAuthenticationException($request)
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            Fortify::username() => [trans("auth.failed")],
        ]);
    }

    private function extractGuardFromRequest(Request $request): string
    {
        return $request->is("admin/*") || $request->is("admin")
            ? "admin"
            : "tenant";
    }
}
