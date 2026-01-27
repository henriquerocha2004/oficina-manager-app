<?php

use App\Http\Controllers\tenant\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

// Guest routes (unauthenticated)
Route::middleware(['tenant', 'guard.resolver', 'guest:tenant'])->group(function () {
    // Login routes
    Route::get('/', [AuthController::class, 'login'])->name('tenant.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Password reset routes
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('tenant.password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('tenant.password.email');

    Route::get('/reset-password/{token}', function ($token) {
        return inertia('Tenant/Auth/ResetPassword', [
            'token' => $token,
            'email' => request('email'),
        ]);
    })->name('tenant.password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('tenant.password.update');
});

// Authenticated routes
Route::middleware(['auth:tenant', 'tenant'])->group(function () {
    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');

    // Email verification routes
    if (Features::enabled(Features::emailVerification())) {
        Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->name('tenant.verification.notice');

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('tenant.verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('tenant.verification.send');
    }
});
