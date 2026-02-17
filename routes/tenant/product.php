<?php

use App\Http\Controllers\tenant\ProductController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::get('/stats', [ProductController::class, 'stats'])->name('stats');
    Route::get('/search', [ProductController::class, 'find'])->name('find');
    Route::get('/{id}', [ProductController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [ProductController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProductController::class, 'delete'])->name('delete');

    // Product-Supplier relationship routes
    Route::post('/{id}/suppliers', [ProductController::class, 'attachSupplier'])->name('attachSupplier');
    Route::put('/{productId}/suppliers/{supplierId}', [ProductController::class, 'updateSupplier'])->name('updateSupplier');
    Route::delete('/{productId}/suppliers/{supplierId}', [ProductController::class, 'detachSupplier'])->name('detachSupplier');
});
