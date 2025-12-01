<?php

namespace TufikHasan\PaisaPay\Services;

use TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface;
use TufikHasan\PaisaPay\Gateways\StripeGateway;
use TufikHasan\PaisaPay\Models\Transaction;
use Exception;

class PaymentService
{
    /**
     * Get payment gateway instance.
     */
    public function getGateway(string $gateway): PaymentGatewayInterface
    {
        $config = config("paisapay.gateways.{$gateway}");

        if (!$config || !($config['enabled'] ?? false)) {
            throw new Exception("Payment gateway '{$gateway}' is not enabled or configured.");
        }

        return match ($gateway) {
            'stripe' => new StripeGateway($config),
            default => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }

    public function processPayment(array $data): array
    {
        $gateway = $this->getGateway($data['payment_gateway']);

        // Prepare payment data
        $paymentData = [
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'metadata' => $data['metadata'],
        ];

        // Charge the payment
        $response = $gateway->charge($paymentData);

        // Create transaction record
        $transaction = Transaction::create([
            'transaction_id' => $response['transaction_id'] ?? 'pending_' . uniqid(),
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'type' => $data['type'] ?? 'one-time',
            'payment_gateway' => $data['payment_gateway'],
            'status' => $response['success'] ? ($response['status'] ?? 'completed') : 'failed',
            'metadata' => array_merge(
                $data['metadata'] ?? [],
                ['gateway_response' => $response]
            ),
        ]);

        // Return transaction with checkout URL if available
        return [
            'transaction' => $transaction,
            'checkout_url' => $response['checkout_url'],
            'gateway_response' => $response,
        ];
    }

    /**
     * Refund a transaction.
     */
    public function refundTransaction(string $transactionId, ?float $amount = null): array
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        if ($transaction->status !== 'completed') {
            throw new Exception('Only completed transactions can be refunded.');
        }

        $gateway = $this->getGateway($transaction->payment_gateway);
        $response = $gateway->refund($transactionId, $amount);

        if ($response['success']) {
            $transaction->update([
                'status' => 'refunded',
                'metadata' => array_merge(
                    $transaction->metadata ?? [],
                    ['refund_response' => $response]
                ),
            ]);
        }

        return $response;
    }

    /**
     * Verify a transaction.
     */
    public function verifyTransaction(string $transactionId): array
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();
        $gateway = $this->getGateway($transaction->payment_gateway);

        return $gateway->verify($transactionId);
    }

    /**
     * Get transaction by ID.
     */
    public function getTransaction(string $transactionId): Transaction
    {
        return Transaction::where('transaction_id', $transactionId)->firstOrFail();
    }
}
