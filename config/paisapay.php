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
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | This option controls the default currency that will be used
    | when processing payments if no currency is specified.
    |
    */

    'default_currency' => env('PAISA_PAY_DEFAULT_CURRENCY', 'USD'),

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
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'supported_currencies' => env('STRIPE_SUPPORTED_CURRENCIES')
                ? explode(',', env('STRIPE_SUPPORTED_CURRENCIES'))
                : ['USD', 'EUR', 'GBP', 'BDT'],
        ],
    ],
];
