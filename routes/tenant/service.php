<?php

use App\Http\Controllers\tenant\ServiceController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::post('/', [ServiceController::class, 'store'])->name('store');
    Route::get('/search', [ServiceController::class, 'find'])->name('find');
    Route::get('/stats', [ServiceController::class, 'stats'])->name('stats');
    Route::get('/{id}', [ServiceController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [ServiceController::class, 'update'])->name('update');
    Route::delete('/{id}', [ServiceController::class, 'delete'])->name('delete');
});
