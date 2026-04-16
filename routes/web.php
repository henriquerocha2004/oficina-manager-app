<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin',
    'domain' => config('app.base_domain'),
    'middleware' => ['guard.resolver'],
], function () {
    require __DIR__ . '/admin/admin.php';
});

Route::group([
    'domain' => '{subdomain}.' . config('app.base_domain'),
    'middleware' => ['tenant', 'check.tenant.status', 'guard.resolver'],
], function () {
    require __DIR__ . '/tenant/tenants.php';
});
