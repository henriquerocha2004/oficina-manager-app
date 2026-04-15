<?php

use App\Http\Controllers\admin\TenantController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'tenants', 'as' => 'admin.tenants.'], function () {
    Route::get('/', [TenantController::class, 'index'])->name('index');
    Route::post('/', [TenantController::class, 'store'])->name('store');
    Route::get('/search', [TenantController::class, 'find'])->name('find');
    Route::get('/{id}', [TenantController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [TenantController::class, 'update'])->name('update');
    Route::delete('/{id}', [TenantController::class, 'delete'])->name('delete');
});
