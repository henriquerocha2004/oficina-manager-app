<?php

use App\Http\Controllers\tenant\StockController;
use Illuminate\Support\Facades\Route;

Route::post('/stock/move/{product_id}', [StockController::class, 'move'])->name('stock.move');
