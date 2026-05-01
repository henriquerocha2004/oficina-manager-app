<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__.'/auth.php';

// Protected tenant routes
Route::middleware(['auth:tenant', 'tenant.permission', 'password.change'])->group(function () {
    Route::get('/faq', fn () => Inertia::render('Tenant/Faq/Index'))->name('faq.index');

    // Client routes
    require __DIR__.'/client.php';

    // Vehicle routes
    require __DIR__.'/vehicle.php';

    // Service routes
    require __DIR__.'/service.php';

    // Supplier routes
    require __DIR__.'/supplier.php';

    // Product routes
    require __DIR__.'/product.php';

    // User routes
    require __DIR__.'/user.php';

    // Stock routes
    require __DIR__.'/stock.php';

    // Service Order routes
    require __DIR__.'/service_order.php';
    require __DIR__.'/service_order_pages.php';

    // Settings routes
    require __DIR__.'/setting.php';
});
