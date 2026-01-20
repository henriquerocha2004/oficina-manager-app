<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();

        return response()->json(['app' => 'ok', 'database' => 'ok']);
    } catch (Exception $e) {
        Log::error('Health check failed: ' . $e->getMessage());
    }
});
