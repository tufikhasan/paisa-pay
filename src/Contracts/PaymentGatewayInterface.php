<?php

namespace Towfik\PaisaPayPay\Contracts;

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
     * Refund a payment.
     *
     * @param string $transactionId The transaction ID to refund
     * @param float|null $amount Optional partial refund amount
     * @return array Response with refund status and details
     */
    public function refund(string $transactionId, ?float $amount = null): array;

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
