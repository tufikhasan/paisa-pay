<?php

namespace TufikHasan\PaisaPay\Facades;

use Illuminate\Support\Facades\Facade;
use TufikHasan\PaisaPay\Services\PaymentService;

/**
 * @method static \TufikHasan\PaisaPay\Models\Transaction payment(array $data)
 * @method static array verifyTransaction(string $transactionId)
 * @method static \TufikHasan\PaisaPay\Models\Transaction getTransaction(string $transactionId)
 * @method static \TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface getGateway(string $gateway)
 *
 * @see \TufikHasan\PaisaPay\Services\PaymentService
 */
class PaisaPay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PaymentService::class;
    }
}