<?php

use App\Http\Controllers\tenant\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/account', [UserController::class, 'account'])->name('account.index');
Route::post('/account', [UserController::class, 'updateAccount'])->name('account.update');
Route::patch('/account/preferences', [UserController::class, 'updatePreferences'])->name('account.preferences');

Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/search', [UserController::class, 'find'])->name('find');
    Route::get('/{id}', [UserController::class, 'findOne'])->name('findOne');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'delete'])->name('delete');
});
