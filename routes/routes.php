<?php

use Illuminate\Support\Facades\Route;
use TufikHasan\PaisaPay\Http\Controllers\PaymentController;

Route::prefix(rtrim(config('paisapay.route_prefix', 'api/paisa-pay'), '/'))->controller(PaymentController::class)->name('paisa-pay.')->group(function () {
    Route::post('/payment', 'payment')->name('payment.process');
    Route::get('/transaction/{transactionId}', 'getTransaction')->name('transaction.get');
    Route::get('/verify/{transactionId}', 'verifyTransaction')->name('transaction.verify');
    Route::get('/failed', 'failed')->name('failed');
});
