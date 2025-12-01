<?php

use Illuminate\Support\Facades\Route;
use TufikHasan\PaisaPay\Http\Controllers\PaymentController;

Route::prefix('api/paisa-pay')->name('paisa-pay.')->group(function () {
    // Process payment
    Route::post('/payment', [PaymentController::class, 'processPayment'])->name('payment.process');
    // Get transaction details
    Route::get('/transaction/{transactionId}', [PaymentController::class, 'getTransaction'])->name('transaction.get');
    // Verify transaction
    Route::get('/verify/{transactionId}', [PaymentController::class, 'verifyTransaction'])->name('transaction.verify');

    Route::get('/failed', function () {
        return "Failed";
    })->name('failed');
});
