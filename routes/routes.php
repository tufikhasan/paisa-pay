<?php

use Illuminate\Support\Facades\Route;
use TufikHasan\PaisaPay\Http\Controllers\PaymentController;

Route::prefix('api/paisa')->group(function () {
    // Process payment
    Route::post('/payment', [PaymentController::class, 'processPayment'])
        ->name('paisa.payment.process');

    // Refund transaction
    Route::post('/refund/{transactionId}', [PaymentController::class, 'refundTransaction'])
        ->name('paisa.payment.refund');

    // Get transaction details
    Route::get('/transaction/{transactionId}', [PaymentController::class, 'getTransaction'])
        ->name('paisa.transaction.get');

    // Verify transaction
    Route::get('/verify/{transactionId}', [PaymentController::class, 'verifyTransaction'])
        ->name('paisa.transaction.verify');
});
