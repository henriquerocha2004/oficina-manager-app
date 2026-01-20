<?php

use App\Http\Controllers\tenant\ClientController;
use Illuminate\Support\Facades\Route;

Route::group([
    'domain' => '{subdomain}.' . config('app.base_domain'),
    'middleware' => 'tenant',
], function () {
    Route::get('/', [ClientController::class, 'index']);
});

Route::get('/admin', function () {
    return 'Admin Panel Login';
});
