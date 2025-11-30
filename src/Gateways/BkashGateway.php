<?php

namespace Towfik\PaisaPayPay\Gateways;

use Towfik\PaisaPayPay\Contracts\PaymentGatewayInterface;
use Exception;

class BkashGateway implements PaymentGatewayInterface
{
    protected array $config;
    protected ?string $token = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get authentication token from bKash.
     */
    protected function getToken(): string
    {
        if ($this->token) {
            return $this->token;
        }

        // In a real implementation, you would call bKash token API
        // Example: POST to {base_url}/tokenized/checkout/token/grant
        // with app_key, app_secret, username, password

        $this->token = 'bkash_token_' . uniqid();
        return $this->token;
    }

    /**
     * Process a payment charge via bKash.
     */
    public function charge(array $data): array
    {
        try {
            $token = $this->getToken();

            // In a real implementation:
            // 1. Create payment: POST {base_url}/tokenized/checkout/create
            // 2. Execute payment: POST {base_url}/tokenized/checkout/execute

            // Mock implementation for demonstration
            $transactionId = 'bkash_' . uniqid();

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'amount' => $data['amount'],
                'currency' => 'BDT', // bKash only supports BDT
                'gateway' => 'bkash',
                'raw_response' => [
                    'paymentID' => $transactionId,
                    'trxID' => 'TRX' . uniqid(),
                    'transactionStatus' => 'Completed',
                    'amount' => $data['amount'],
                    'currency' => 'BDT',
                ],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'gateway' => 'bkash',
            ];
        }
    }

    /**
     * Refund a bKash payment.
     */
    public function refund(string $transactionId, ?float $amount = null): array
    {
        try {
            $token = $this->getToken();

            // In a real implementation:
            // POST {base_url}/tokenized/checkout/payment/refund
            // with paymentID, amount, trxID, sku, reason

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'refund_id' => 'refund_' . uniqid(),
                'status' => 'refunded',
                'amount' => $amount,
                'gateway' => 'bkash',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'bkash',
            ];
        }
    }

    /**
     * Verify a bKash payment transaction.
     */
    public function verify(string $transactionId): array
    {
        try {
            $token = $this->getToken();

            // In a real implementation:
            // GET {base_url}/tokenized/checkout/payment/status
            // with paymentID

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'gateway' => 'bkash',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'bkash',
            ];
        }
    }

    /**
     * Get the gateway name.
     */
    public function getName(): string
    {
        return 'bkash';
    }
}
