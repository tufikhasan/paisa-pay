<?php

namespace TufikHasan\PaisaPay\Gateways;

use TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface;
use Exception;

abstract class AbstractGateway implements PaymentGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->init($config);
    }

    /**
     * Validate if currency is supported by this gateway.
     */
    protected function validateCurrency(string $currency): void
    {
        $supportedCurrencies = $this->config['supported_currencies'] ?? [];

        if (empty($supportedCurrencies)) {
            return; // No currency restriction if not configured
        }

        $currency = strtoupper($currency);

        if (!in_array($currency, $supportedCurrencies, true)) {
            $supported = implode(', ', $supportedCurrencies);
            throw new Exception(
                "Currency '{$currency}' is not supported by {$this->getName()} gateway. " .
                "Supported currencies: {$supported}"
            );
        }
    }

    /**
     * Get the gateway name.
     */
    abstract public function getName(): string;

    /**
     * Initialize the gateway.
     */
    abstract public function init(array $config): void;

    /**
     * Pay a payment.
     */
    abstract public function pay(array $data): array;

    /**
     * Verify a transaction.
     */
    abstract public function verify(string $transactionId): array;
}
