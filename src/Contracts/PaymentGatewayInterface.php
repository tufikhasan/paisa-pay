<?php

namespace TufikHasan\PaisaPay\Contracts;

interface PaymentGatewayInterface
{

    /**
     * Process a payment pay.
     *
     * @param array $data Payment data including amount, currency, metadata
     * @return array Response with transaction_id, status, and other details
     */
    public function pay(array $data): array;

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
