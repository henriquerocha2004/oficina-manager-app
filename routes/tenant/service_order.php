<?php

use App\Http\Controllers\tenant\ServiceOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('service-orders')
    ->middleware(['auth:tenant', 'verified'])
    ->group(function () {
        Route::get('/search', [ServiceOrderController::class, 'find'])
            ->name('service-orders.search');
        Route::get('/', [ServiceOrderController::class, 'find'])
            ->name('service-orders.find');
        Route::post('/', [ServiceOrderController::class, 'store'])
            ->name('service-orders.store');
        Route::get('/stats', [ServiceOrderController::class, 'stats'])
            ->name('service-orders.stats');
        Route::get('/{id}', [ServiceOrderController::class, 'findOne'])
            ->name('service-orders.show');
        Route::get('/{id}/detail', [ServiceOrderController::class, 'show'])
            ->name('service-orders.detail');
        Route::delete('/{id}', [ServiceOrderController::class, 'delete'])
            ->name('service-orders.destroy');
        Route::post('/{id}/send-for-approval', [ServiceOrderController::class, 'sendForApproval'])
            ->name('service-orders.send-for-approval');
        Route::post('/{id}/request-new-approval', [ServiceOrderController::class, 'requestNewApproval'])
            ->name('service-orders.request-new-approval');
        Route::post('/{id}/return-to-approval', [ServiceOrderController::class, 'returnToApproval'])
            ->name('service-orders.return-to-approval');
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
        Route::get('/{id}/items', [ServiceOrderController::class, 'listItems'])
            ->name('service-orders.list-items');
        Route::post('/{id}/items', [ServiceOrderController::class, 'addItem'])
            ->name('service-orders.add-item');
        Route::delete('/{id}/items/{itemId}', [ServiceOrderController::class, 'removeItem'])
            ->name('service-orders.remove-item');
        Route::post('/{id}/payments', [ServiceOrderController::class, 'registerPayment'])
            ->name('service-orders.register-payment');
        Route::post('/{id}/refund', [ServiceOrderController::class, 'refund'])
            ->name('service-orders.refund');
        Route::post('/{id}/photos', [ServiceOrderController::class, 'uploadPhoto'])
            ->name('service-orders.upload-photo');
        Route::delete('/{id}/photos/{photoId}', [ServiceOrderController::class, 'deletePhoto'])
            ->name('service-orders.delete-photo');
    });
