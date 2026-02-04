<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // ...existing code...
    }

    /**
     * Override unauthenticated to redirect based on guard/context.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Se for admin, redireciona para /admin, senão para /
        $login = $request->is('admin/*') || $request->is('admin') ? '/admin' : '/';
        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }
        return redirect()->guest($login);
    }
}
