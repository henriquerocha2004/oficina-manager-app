<?php

use App\Http\Controllers\tenant\ProductController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::get('/search', [ProductController::class, 'find'])->name('find');
    Route::get('/{id}', [ProductController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [ProductController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProductController::class, 'delete'])->name('delete');
});
