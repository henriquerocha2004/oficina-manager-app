<?php

use App\Http\Controllers\tenant\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::post('/logo', [SettingController::class, 'uploadLogo'])->name('upload-logo');
    Route::delete('/logo', [SettingController::class, 'removeLogo'])->name('remove-logo');
});
