<?php

use App\Http\Controllers\tenant\VehicleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'vehicles', 'as' => 'vehicles.'], function () {
    Route::get('/', [VehicleController::class, 'index'])->name('index');
    Route::post('/', [VehicleController::class, 'store'])->name('store');
    Route::get('/search', [VehicleController::class, 'find'])->name('find');
    Route::get('/stats', [VehicleController::class, 'stats'])->name('stats');
    Route::get('/{id}', [VehicleController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [VehicleController::class, 'update'])->name('update');
    Route::delete('/{id}', [VehicleController::class, 'delete'])->name('delete');
});
