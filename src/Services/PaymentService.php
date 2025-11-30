<?php

namespace TufikHasan\PaisaPay\Services;

use TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface;
use TufikHasan\PaisaPay\Gateways\StripeGateway;
use TufikHasan\PaisaPay\Gateways\PaypalGateway;
use TufikHasan\PaisaPay\Gateways\BkashGateway;
use TufikHasan\PaisaPay\Models\Transaction;
use Exception;

class PaymentService
{
    /**
     * Get payment gateway instance.
     */
    public function getGateway(string $gateway): PaymentGatewayInterface
    {
        $config = config("paisa.gateways.{$gateway}");

        if (!$config || !($config['enabled'] ?? false)) {
            throw new Exception("Payment gateway '{$gateway}' is not enabled or configured.");
        }

        return match ($gateway) {
            'stripe' => new StripeGateway($config),
            'paypal' => new PaypalGateway($config),
            'bkash' => new BkashGateway($config),
            default => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }

    /**
     * Process a payment.
     */
    public function processPayment(array $data): Transaction
    {
        $gateway = $this->getGateway($data['payment_type']);

        // Prepare payment data
        $paymentData = [
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? config('paisa.currency'),
            'metadata' => $data['metadata'] ?? [],
        ];

        // Charge the payment
        $response = $gateway->charge($paymentData);

        // Create transaction record
        $transaction = Transaction::create([
            'transaction_id' => $response['transaction_id'] ?? 'pending_' . uniqid(),
            'amount' => $data['amount'],
            'user_id' => $data['user_id'],
            'type' => $data['type'] ?? 'one-time',
            'payment_gateway' => $data['payment_type'],
            'status' => $response['success'] ? 'completed' : 'failed',
            'metadata' => array_merge(
                $data['metadata'] ?? [],
                ['gateway_response' => $response]
            ),
        ]);

        return $transaction;
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
