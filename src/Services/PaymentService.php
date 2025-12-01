<?php

namespace TufikHasan\PaisaPay\Services;

use TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface;
use TufikHasan\PaisaPay\Gateways\StripeGateway;
use TufikHasan\PaisaPay\Models\Transaction;
use TufikHasan\PaisaPay\Enums\PaymentGateway;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Get payment gateway instance.
     */
    public function getGateway(string $gateway): PaymentGatewayInterface
    {
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
            default => throw new Exception("Unsupported payment gateway: {$gateway}"),
        };
    }

    public function processPayment(array $data): array
    {
        $gateway = $this->getGateway($data['payment_gateway']);

        // Validate currency is supported by the gateway
        $this->validateCurrency($data['payment_gateway'], $data['currency']);

        // Charge the payment
        $response = $gateway->charge($data);

        $transaction = null;

        // Create transaction record
        DB::transaction(function () use (&$transaction, $response, $data) {
            $transaction = Transaction::create([
                'transaction_id' => $response['transaction_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'type' => $data['type'] ?? 'one-time',
                'payment_gateway' => $data['payment_gateway'],
                'status' => $response['status'],
                'metadata' => $response,
            ]);
        });

        return [
            'transaction' => $transaction,
            'checkout_url' => $response['checkout_url'] ?? null,
            'gateway_response' => $response,
        ];
    }

    /**
     * Validate if currency is supported by the gateway.
     */
    protected function validateCurrency(string $gateway, string $currency): void
    {
        $supportedCurrencies = config("paisapay.gateways.{$gateway}.supported_currencies", []);

        if (empty($supportedCurrencies)) {
            return; // No currency restriction if not configured
        }

        $currency = strtoupper($currency);

        if (!in_array($currency, $supportedCurrencies, true)) {
            $supported = implode(', ', $supportedCurrencies);
            throw new Exception(
                "Currency '{$currency}' is not supported by {$gateway} gateway. " .
                "Supported currencies: {$supported}"
            );
        }
    }

    /**
     * Verify a transaction.
     */
    public function verifyTransaction(string $transactionId): array
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();
        $gateway = $this->getGateway($transaction->payment_gateway);

        $response = $gateway->verify($transactionId);

        // Update transaction if verification was successful
        if ($response['success'] && $response['status'] === 'completed') {
            $transaction->update([
                'status' => 'completed',
                'metadata' => $response,
            ]);
        }

        return $response;
    }

    /**
     * Get transaction by ID.
     */
    public function getTransaction(string $transactionId): Transaction
    {
        return Transaction::where('transaction_id', $transactionId)->firstOrFail();
    }
}
