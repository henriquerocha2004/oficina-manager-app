<?php

use App\Http\Controllers\tenant\StockController;
use App\Http\Controllers\tenant\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::post('/stock/move/{product_id}', [StockController::class, 'move'])->name('stock.move');

Route::group(['prefix' => 'stock/movements', 'as' => 'stock.movements.'], function () {
    Route::get('/', [StockMovementController::class, 'index'])->name('index');
    Route::get('/search', [StockMovementController::class, 'find'])->name('find');
    Route::get('/stats', [StockMovementController::class, 'stats'])->name('stats');
});
