<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Inertia\Response as InertiaResponse;

class AuthController extends Controller
{
    public function login(): InertiaResponse
    {
        return inertia("Admin/Auth/Login");
    }

    public function forgotPassword(): InertiaResponse
    {
        return inertia("Admin/Auth/ForgotPassword");
    }
}
