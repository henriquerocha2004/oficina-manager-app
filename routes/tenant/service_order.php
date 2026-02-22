<?php

use App\Http\Controllers\tenant\ServiceOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('service-orders')
    ->middleware(['auth:sanctum', 'verified'])
    ->group(function () {
        Route::post('/', [ServiceOrderController::class, 'store'])
            ->name('service-orders.store');
    });
