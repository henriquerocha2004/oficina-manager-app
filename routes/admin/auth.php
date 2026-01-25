<?php

use App\Http\Controllers\admin\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'login']);
Route::get("/forgot-password", [AuthController::class, 'forgotPassword']);
