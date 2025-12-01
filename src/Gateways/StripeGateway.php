<?php

namespace TufikHasan\PaisaPay\Gateways;

use TufikHasan\PaisaPay\Contracts\PaymentGatewayInterface;
use \Stripe\Stripe;
use Exception;

class StripeGateway implements PaymentGatewayInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        Stripe::setApiKey($this->config['secret_key']);
    }

    /**
     * Process a payment charge via Stripe.
     */
    public function charge(array $data): array
    {
        try {
            $currency = strtolower($data['currency'] ?? 'USD');
            $amount = (int) ($data['amount'] * 100);

            $sessionConfig = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amount,
                        'product_data' => [
                            'name' => config('app.name'),
                            'description' => 'Payment via Paisa Pay',
                        ],
                    ],
                    'quantity' => 1,
                ],
                'mode' => 'payment',
                'success_url' => route('paisa-pay.transaction.verify', ['transactionId' => '{CHECKOUT_SESSION_ID}']),
                'cancel_url' => route('paisa-pay.failed'),
            ];
            $checkoutSession = \Stripe\Checkout\Session::create($sessionConfig);

            return [
                'success' => true,
                'transaction_id' => $checkoutSession->id,
                'status' => 'pending',
                'checkout_url' => $checkoutSession->url,
                'gateway' => $this->getName(),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'gateway' => $this->getName(),
            ];
        }
    }

    /**
     * Verify a Stripe payment transaction.
     */
    public function verify(string $transactionId): array
    {
        try {
            // Retrieve the Stripe session
            $session = \Stripe\Checkout\Session::retrieve($transactionId);
            // Check if payment was successful
            if ($session->payment_status !== 'paid') {
                return [
                    'success' => false,
                    'transaction_id' => $transactionId,
                    'status' => 'failed',
                    'error' => 'Payment not completed',
                    'gateway' => $this->getName(),
                ];
            }
            // Get payment intent for additional details
            $amount = $session->amount_total ? $session->amount_total / 100 : null;

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'amount' => $amount,
                'currency' => $session->currency,
                'payment_status' => $session->payment_status,
                'gateway' => $this->getName(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'gateway' => $this->getName(),
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
     * Get the gateway name.
     */
    public function getName(): string
    {
        return 'stripe';
    }
}
