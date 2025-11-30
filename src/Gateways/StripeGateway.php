<?php

namespace TufikHasan\PaisaPay\Gateways;

use TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface;
use Exception;

class StripeGateway implements PaymentGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Process a payment charge via Stripe.
     */
    public function charge(array $data): array
    {
        try {
            // In a real implementation, you would use Stripe SDK here
            // Example: \Stripe\Stripe::setApiKey($this->config['secret_key']);
            // $charge = \Stripe\Charge::create([...]);

            // Mock implementation for demonstration
            $transactionId = 'stripe_' . uniqid();

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'USD',
                'gateway' => 'stripe',
                'raw_response' => [
                    'id' => $transactionId,
                    'object' => 'charge',
                    'amount' => $data['amount'] * 100, // Stripe uses cents
                    'currency' => strtolower($data['currency'] ?? 'USD'),
                ],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'gateway' => 'stripe',
            ];
        }
    }

    /**
     * Refund a Stripe payment.
     */
    public function refund(string $transactionId, ?float $amount = null): array
    {
        try {
            // In a real implementation:
            // $refund = \Stripe\Refund::create([
            //     'charge' => $transactionId,
            //     'amount' => $amount ? $amount * 100 : null,
            // ]);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'refund_id' => 'refund_' . uniqid(),
                'status' => 'refunded',
                'amount' => $amount,
                'gateway' => 'stripe',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'stripe',
            ];
        }
    }

    /**
     * Verify a Stripe payment transaction.
     */
    public function verify(string $transactionId): array
    {
        try {
            // In a real implementation:
            // $charge = \Stripe\Charge::retrieve($transactionId);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'gateway' => 'stripe',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => 'stripe',
            ];
        }
    }

    /**
     * Get the gateway name.
     */
    public function getName(): string
    {
        return 'stripe';
    }
}
