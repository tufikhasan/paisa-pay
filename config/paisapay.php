<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used
    | when processing payments. You may set this to any of the gateways
    | defined in the "gateways" configuration array.
    |
    */

    'default_gateway' => env('PAISA_PAY_DEFAULT_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for transactions.
    |
    */

    'currency' => env('PAISA_PAY_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Here you may configure the payment gateways for your application.
    | Each gateway has its own set of credentials and configuration.
    |
    */

    'gateways' => [

        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', false),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

        'paypal' => [
            'enabled' => env('PAYPAL_ENABLED', false),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        ],

        'bkash' => [
            'enabled' => env('BKASH_ENABLED', false),
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'base_url' => env('BKASH_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook URLs
    |--------------------------------------------------------------------------
    |
    | URLs for payment gateway webhooks.
    |
    */

    'webhooks' => [
        'stripe' => env('PAISA_PAY_STRIPE_WEBHOOK_URL', '/api/paisa/webhooks/stripe'),
        'paypal' => env('PAISA_PAY_PAYPAL_WEBHOOK_URL', '/api/paisa/webhooks/paypal'),
        'bkash' => env('PAISA_PAY_BKASH_WEBHOOK_URL', '/api/paisa/webhooks/bkash'),
    ],

];
