<?php

namespace TufikHasan\PaisaPay\Gateways;

use Exception;

class StripeGateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'stripe';
    }

    public function init(array $config): void
    {
        \Stripe\Stripe::setApiKey($config['secret_key']);
    }

    /**
     * Process a payment via Stripe.
     */
    public function pay(array $data): array
    {
        try {
            $currency = $data['currency'] ?? config('paisapay.default_currency');
            // Validate currency is supported
            $this->validateCurrency($currency);

            $currency = strtolower($currency);
            $amount = (float) ($data['amount'] * 100);

            $sessionConfig = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'unit_amount' => $amount,
                            'product_data' => [
                                'name' => $data['product_name'] ?? config('app.name'),
                                'description' => $data['product_description'] ?? 'Payment via Paisa Pay',
                            ],
                        ],
                        'quantity' => $data['quantity'] ?? 1,
                    ],
                ],

                'mode' => 'payment',
                'success_url' => url(rtrim(config('paisapay.route_prefix', 'api/paisa-pay'), '/'). "/verify/{CHECKOUT_SESSION_ID}"),
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
}
