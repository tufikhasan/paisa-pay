<?php

use Illuminate\Support\Facades\Route;
use TufikHasan\PaisaPay\Http\Controllers\PaymentController;

Route::prefix('api/paisa-pay')->controller(PaymentController::class)->name('paisa-pay.')->group(function () {
    Route::post('/payment', 'processPayment')->name('payment.process');
    Route::get('/transaction/{transactionId}', 'getTransaction')->name('transaction.get');
    Route::get('/verify/{transactionId}', 'verifyTransaction')->name('transaction.verify');
    Route::get('/failed', 'failed')->name('failed');
});
