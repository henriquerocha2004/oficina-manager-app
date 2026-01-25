<?php

namespace App\Http\Controllers\tenant;

use App\Http\Controllers\Controller;
use Inertia\Response as InertiaResponse;

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
}
