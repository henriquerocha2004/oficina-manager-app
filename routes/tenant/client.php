<?php

use App\Http\Controllers\tenant\ClientController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'clients', 'as' => 'clients.'], function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::post('/', [ClientController::class, 'store'])->name('store');
    Route::get('/stats', [ClientController::class, 'stats'])->name('stats');
    Route::get('/search', [ClientController::class, 'find'])->name('find');
    Route::get('/{id}', [ClientController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [ClientController::class, 'update'])->name('update');
    Route::delete('/{id}', [ClientController::class, 'delete'])->name('delete');
});
