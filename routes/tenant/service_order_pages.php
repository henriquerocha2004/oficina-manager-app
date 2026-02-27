<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group(['prefix' => 'service-orders', 'as' => 'service-orders.'], function () {
    Route::get('/', function () {
        return Inertia::render('Tenant/ServiceOrders/Index');
    })->name('index');

    Route::get('/create', function () {
        return Inertia::render('Tenant/ServiceOrders/Create');
    })->name('create');

    Route::get('/{id}', function (string $id) {
        return Inertia::render('Tenant/ServiceOrders/Show', ['id' => $id]);
    })->name('show');
});
