<?php

use App\Http\Controllers\tenant\SupplierController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'suppliers', 'as' => 'suppliers.'], function () {
    Route::get('/', [SupplierController::class, 'index'])->name('index');
    Route::post('/', [SupplierController::class, 'store'])->name('store');
    Route::get('/search', [SupplierController::class, 'find'])->name('find');
    Route::get('/{id}', [SupplierController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [SupplierController::class, 'update'])->name('update');
    Route::delete('/{id}', [SupplierController::class, 'delete'])->name('delete');
});
