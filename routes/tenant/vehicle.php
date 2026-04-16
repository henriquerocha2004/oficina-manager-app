<?php

use App\Http\Controllers\tenant\VehicleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'vehicles', 'as' => 'vehicles.'], function () {
    Route::get('/', [VehicleController::class, 'index'])->name('index');
    Route::post('/', [VehicleController::class, 'store'])->name('store');
    Route::get('/search', [VehicleController::class, 'find'])->name('find');
    Route::get('/stats', [VehicleController::class, 'stats'])->name('stats');
    Route::get('/check-plate', [VehicleController::class, 'checkPlate'])->name('check-plate');
    Route::get('/{id}/history', [VehicleController::class, 'history'])->name('history');
    Route::get('/{id}', [VehicleController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [VehicleController::class, 'update'])->name('update');
    Route::post('/{id}/transfer-ownership', [VehicleController::class, 'transferOwnership'])
        ->name('transfer-ownership');
    Route::delete('/{id}', [VehicleController::class, 'delete'])->name('delete');
});
