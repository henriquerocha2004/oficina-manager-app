<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

// Protected tenant routes
Route::middleware(['auth:tenant', 'tenant'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Tenant/Dashboard');
    })->name('tenant.dashboard');

    // Client routes
    require __DIR__ . '/client.php';

    // Vehicle routes
    require __DIR__ . '/vehicle.php';

    // Service routes
    require __DIR__ . '/service.php';
});
