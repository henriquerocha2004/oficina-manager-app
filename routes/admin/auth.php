<?php

use App\Http\Controllers\admin\AuthController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

// Guest routes (unauthenticated)
Route::middleware(['guest.admin', 'guard.resolver'])->group(function () {
    // Login routes
    Route::get('/', [AuthController::class, 'login'])->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Password reset routes
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('admin.password.email');

    Route::get('/reset-password/{token}', function ($token) {
        return inertia('Admin/Auth/ResetPassword', ['token' => $token]);
    })->name('admin.password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('admin.password.update');
});

// Authenticated routes
Route::middleware(['auth:admin'])->group(function () {
    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

    // Email verification routes
    if (Features::enabled(Features::emailVerification())) {
        Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
            ->name('admin.verification.notice');

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('admin.verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('admin.verification.send');
    }
});
