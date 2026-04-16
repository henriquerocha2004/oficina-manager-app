<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');

    require __DIR__ . '/client.php';
    require __DIR__ . '/tenant.php';
});
