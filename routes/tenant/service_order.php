<?php

use App\Http\Controllers\tenant\ServiceOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('service-orders')
    ->middleware(['auth:sanctum', 'verified'])
    ->group(function () {
        Route::get('/', [ServiceOrderController::class, 'find'])
            ->name('service-orders.find');
        Route::post('/', [ServiceOrderController::class, 'store'])
            ->name('service-orders.store');
        Route::get('/stats', [ServiceOrderController::class, 'stats'])
            ->name('service-orders.stats');
        Route::get('/{id}', [ServiceOrderController::class, 'findOne'])
            ->name('service-orders.show');
        Route::delete('/{id}', [ServiceOrderController::class, 'delete'])
            ->name('service-orders.destroy');
        Route::post('/{id}/send-for-approval', [ServiceOrderController::class, 'sendForApproval'])
            ->name('service-orders.send-for-approval');
        Route::post('/{id}/approve', [ServiceOrderController::class, 'approve'])
            ->name('service-orders.approve');
        Route::post('/{id}/start-work', [ServiceOrderController::class, 'startWork'])
            ->name('service-orders.start-work');
        Route::post('/{id}/finish-work', [ServiceOrderController::class, 'finishWork'])
            ->name('service-orders.finish-work');
        Route::post('/{id}/cancel', [ServiceOrderController::class, 'cancel'])
            ->name('service-orders.cancel');
        Route::put('/{id}/diagnosis', [ServiceOrderController::class, 'updateDiagnosis'])
            ->name('service-orders.update-diagnosis');
        Route::put('/{id}/discount', [ServiceOrderController::class, 'updateDiscount'])
            ->name('service-orders.update-discount');
        Route::post('/{id}/items', [ServiceOrderController::class, 'addItem'])
            ->name('service-orders.add-item');
        Route::delete('/{id}/items/{itemId}', [ServiceOrderController::class, 'removeItem'])
            ->name('service-orders.remove-item');
        Route::post('/{id}/payments', [ServiceOrderController::class, 'registerPayment'])
            ->name('service-orders.register-payment');
        Route::post('/{id}/payments/{paymentId}/refund', [ServiceOrderController::class, 'refundPayment'])
            ->name('service-orders.refund-payment');
    });
