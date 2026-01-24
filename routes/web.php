<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'domain' => '{subdomain}.' . config('app.base_domain'),
    'middleware' => 'tenant',
], function () {
   require __DIR__ . '/tenant/tenants.php';
});

Route::group([
    'prefix' => 'admin',
    'domain' => config('app.base_domain'),
], function () {
    require __DIR__ . '/admin/admin.php';
});
