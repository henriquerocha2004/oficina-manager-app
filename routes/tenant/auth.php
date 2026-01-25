<?php

use App\Http\Controllers\tenant\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [AuthController::class, 'login']);
Route::get("/forgot-password", [AuthController::class, 'forgotPassword']);
