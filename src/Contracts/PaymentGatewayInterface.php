<?php

namespace TufikHasan\PaisaPay\Contracts;

interface PaymentGatewayInterface
{

    /**
     * Process a payment charge.
     *
     * @param array $data Payment data including amount, currency, metadata
     * @return array Response with transaction_id, status, and other details
     */
    public function charge(array $data): array;

    /**
     * Verify a payment transaction.
     *
     * @param string $transactionId The transaction ID to verify
     * @return array Response with transaction status and details
     */
    public function verify(string $transactionId): array;

    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function getName(): string;
}
