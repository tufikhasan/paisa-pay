<?php

namespace Towfik\PaisaPay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Towfik\PaisaPay\Models\Transaction processPayment(array $data)
 * @method static array refundTransaction(string $transactionId, ?float $amount = null)
 * @method static array verifyTransaction(string $transactionId)
 * @method static \Towfik\PaisaPay\Models\Transaction getTransaction(string $transactionId)
 * @method static \Towfik\PaisaPay\Contracts\PaymentGatewayInterface getGateway(string $gateway)
 *
 * @see \Towfik\PaisaPay\Services\PaymentService
 */
class PaisaPay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Towfik\PaisaPay\Services\PaymentService::class;
    }
}