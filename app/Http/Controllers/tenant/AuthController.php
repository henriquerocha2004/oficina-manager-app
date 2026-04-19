<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\User\ChangePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\tenant\ForceChangePasswordRequest;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(): InertiaResponse
    {
        return inertia("Tenant/Auth/Login");
    }

    public function forgotPassword(): InertiaResponse
    {
        return inertia("Tenant/Auth/ForgotPassword");
    }

    public function showForceChangePassword(): InertiaResponse
    {
        return inertia('Tenant/Auth/ForceChangePassword');
    }

    public function forceChangePassword(
        ForceChangePasswordRequest $request,
        ChangePasswordAction $changePasswordAction,
    ): Response {
        $user = $request->user('tenant');
        $changePasswordAction($user, $request->validated('password'));

        $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
        $home = match ($role) {
            'mechanic' => '/service-orders',
            default    => '/clients',
        };

        return Inertia::location($home);
    }
}
