<?php

namespace TufikHasan\PaisaPay\Services;

use Exception;
use TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface;
use TufikHasan\PaisaPay\Enums\PaymentGateway;
use TufikHasan\PaisaPay\Events\TransactionCreated;
use TufikHasan\PaisaPay\Events\TransactionVerified;
use TufikHasan\PaisaPay\Gateways\StripeGateway;
use TufikHasan\PaisaPay\Models\Transaction;

class PaymentService {
    /**
     * Get payment gateway instance.
     */
    public function getGateway(string $gateway): PaymentGatewayInterface {
        // Validate gateway is supported
        if (!PaymentGateway::isValid($gateway)) {
            throw new Exception("Unsupported payment gateway: {$gateway}. Supported gateways: " . PaymentGateway::valuesString());
        }

        $config = config("paisapay.gateways.{$gateway}");

        if (!$config || !($config['enabled'] ?? false)) {
            throw new Exception("Payment gateway '{$gateway}' is not enabled or configured.");
        }

        return match ($gateway) {
            PaymentGateway::STRIPE->value => new StripeGateway($config),
            default                       => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }

    public function payment(array $data): array {
        $payment_gateway = $data['payment_gateway'] ?? config('paisapay.default_gateway');
        $gateway = $this->getGateway($payment_gateway);

        // Pay the payment (currency validation happens in gateway)
        $response = $gateway->pay($data);

        // Create transaction record
        $transaction = Transaction::create([
            'transaction_id'  => $response['transaction_id'],
            'amount'          => $data['amount'],
            'currency'        => $data['currency'] ?? config('paisapay.default_currency'),
            'type'            => $data['type'] ?? 'one-time',
            'payment_gateway' => $payment_gateway,
            'status'          => $response['status'],
            'metadata'        => array_merge(
                $data['metadata'] ?? [],
                ['gateway_response' => $response]
            ),
        ]);
        event(new TransactionCreated($transaction));

        return [
            'transaction'      => $transaction,
            'checkout_url'     => $response['checkout_url'] ?? null,
            'gateway_response' => $response,
        ];
    }

    /**
     * Verify a transaction.
     */
    public function verifyTransaction(string $transactionId): array {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();
        $gateway = $this->getGateway($transaction->payment_gateway);

        $response = $gateway->verify($transactionId);

        // Update transaction if verification was successful
        if ($response['success'] && $response['status'] === 'completed') {
            $metadata = $transaction->metadata ?? [];
            $metadata['gateway_response'] = $response;

            $transaction->update([
                'status'   => 'completed',
                'metadata' => $metadata,
            ]);

            event(new TransactionVerified($transaction, $response));

        }

        if (!$response['success']) {
            event(new TransactionFailed($transaction));
        }

        return $response;
    }

    /**
     * Get transaction by ID.
     */
    public function getTransaction(string $transactionId): Transaction {
        return Transaction::where('transaction_id', $transactionId)->firstOrFail();
    }
}
