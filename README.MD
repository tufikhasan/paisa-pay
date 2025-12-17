# Paisa Pay - Laravel Payment Gateway Package

A modern, flexible Laravel package for integrating multiple payment gateways with support for Stripe and more. Built with type safety, comprehensive error handling, and a beautiful payment failed page.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x%7C12.x-red)](https://laravel.com/)

## Features

- ✅ **Multiple Payment Gateways** - Currently supports Stripe (easily extensible)
- ✅ **Type-Safe Enums** - PaymentGateway enum for compile-time safety
- ✅ **Currency Validation** - Automatic validation of supported currencies per gateway
- ✅ **Beautiful UI** - Modern, responsive payment failed page
- ✅ **Dual Interface** - Use via Facade or API routes
- ✅ **Database Transactions** - Atomic operations for data consistency
- ✅ **Comprehensive Error Handling** - Detailed error messages and logging
- ✅ **Easy Configuration** - Simple config file with environment variable support
- ✅ **Publishable Assets** - Customize views and config to your needs

---

## Requirements

- PHP 8.2 or higher
- Laravel 11.x or 12.x
- Stripe PHP SDK (automatically installed)

---

## Installation

### 1. Option 1: Install from GitHub Repository

Add the package repository to your `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/tufikhasan/paisa-pay.git"
    }
],
"require": {
    "tufikhasan/paisa-pay": "dev-main"
}
```

Then run:
```bash
composer update
```

### 2 Install via Composer

```bash
composer require tufikhasan/paisa-pay
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=paisapay-config
```

This creates `config/paisapay.php`.

### 3. Publish Migrations

```bash
php artisan vendor:publish --tag=paisapay-migrations
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. (Optional) Publish Views

To customize the payment failed page:

```bash
php artisan vendor:publish --tag=paisapay-views
```

Views will be published to `resources/views/vendor/paisapay/`.

---

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Paisa Pay Settings
PAISA_PAY_DEFAULT_GATEWAY=stripe
PAISA_PAY_DEFAULT_CURRENCY=USD

# Stripe Configuration
STRIPE_ENABLED=true
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key_here
STRIPE_SUPPORTED_CURRENCIES=USD,EUR,GBP,BDT
```

### Config File

The `config/paisapay.php` file allows you to configure:

```php
return [
    'default_gateway' => env('PAISA_PAY_DEFAULT_GATEWAY', 'stripe'),

    'default_currency' => env('PAISA_PAY_DEFAULT_CURRENCY', 'USD'),
    
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
```

---

## Usage

Paisa Pay provides two ways to process payments: **Facade** (programmatic) and **API Routes** (HTTP).

### Method 1: Using Facade (Programmatic)

#### Process a Payment

```php
use TufikHasan\PaisaPay\Facades\PaisaPay;

$result = PaisaPay::payment([
    'amount' => 100.00,
    'currency' => 'USD', // optional
    'payment_gateway' => 'stripe', // optional
    'type' => 'one-time', // optional
    'metadata' => ['order_id' => 123], // optional
]);

// Redirect user to checkout
return redirect($result['checkout_url']);
```

**Response:**
```php
[
    'transaction' => Transaction, // Eloquent model
    'checkout_url' => 'https://checkout.stripe.com/...',
    'gateway_response' => [...]
]
```

#### Verify a Transaction

```php
$response = PaisaPay::verifyTransaction($transactionId);

if ($response['success']) {
    // Payment successful
    $transaction = $response['transaction'];
}
```

#### Get Transaction Details

```php
$transaction = PaisaPay::getTransaction($transactionId);

echo $transaction->status; // 'completed', 'pending', 'failed'
echo $transaction->amount;
echo $transaction->currency;
```

---

### Method 2: Using API Routes (HTTP)

All routes are automatically registered under the `api/paisa-pay` prefix.

#### Process Payment

**Endpoint:** `POST /api/paisa-pay/payment`

**Request:** (amount is required; currency, payment_gateway, type, metadata are optional)
```json
{
    "amount": 100.00,
    "currency": "USD",
    "payment_gateway": "stripe",
    "type": "one-time",
    "metadata": {
        "order_id": 123
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Payment processed successfully.",
    "data": {
        "id": 1,
        "transaction_id": "cs_test_...",
        "amount": "100.00",
        "currency": "USD",
        "status": "pending",
        "payment_gateway": "stripe"
    },
    "checkout_url": "https://checkout.stripe.com/..."
}
```

**cURL Example:**
```bash
curl -X POST http://localhost/api/paisa-pay/payment \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100,
    "currency": "USD",
    "payment_gateway": "stripe"
  }'
```

#### Verify Transaction

**Endpoint:** `GET /api/paisa-pay/verify/{transactionId}`

**Response:**
```json
{
    "success": true,
    "message": "Transaction verified successfully.",
    "data": {
        "success": true,
        "status": "completed",
        "amount": 100.00,
        "currency": "USD"
    }
}
```

#### Get Transaction

**Endpoint:** `GET /api/paisa-pay/transaction/{transactionId}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "transaction_id": "cs_test_...",
        "amount": "100.00",
        "currency": "USD",
        "status": "completed",
        "payment_gateway": "stripe",
        "created_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

---

## Available Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/paisa-pay/payment` | Process a new payment |
| GET | `/api/paisa-pay/verify/{transactionId}` | Verify transaction status |
| GET | `/api/paisa-pay/transaction/{transactionId}` | Get transaction details |
| GET | `/api/paisa-pay/failed` | Payment failed page (UI) |

---

## Payment Flow

```
1. User initiates payment
   ↓
2. Create payment via Facade or API
   ↓
3. Redirect to checkout_url (Stripe Checkout)
   ↓
4. User completes or cancels payment
   ↓
5. Stripe redirects to verify URL or failed page
   ↓
6. Verify transaction status
   ↓
7. Update transaction in database
```

---

## Payment Failed Page

A beautiful, modern payment failed page is included and automatically displayed when payments are cancelled or fail.

**Features:**
- Modern gradient design
- Responsive (mobile-friendly)
- Animated error icon
- Dynamic error messages
- "Return to Home" button
- Support contact link

**Customization:**

After publishing views, edit:
```
resources/views/vendor/paisapay/payment-failed.blade.php
```

---

## Currency Validation

The package automatically validates currencies based on gateway configuration.

**Supported Currencies (Stripe):**
- USD, EUR, GBP, BDT (default)
- Customize via `STRIPE_SUPPORTED_CURRENCIES` env variable

**Example Error:**
```
Currency 'JPY' is not supported by stripe gateway. 
Supported currencies: USD, EUR, GBP, BDT
```

---

## Database Schema

The `transactions` table includes:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| transaction_id | string | Gateway transaction ID |
| amount | decimal(10,2) | Payment amount |
| currency | string | Currency code (USD, EUR, etc.) |
| type | string | Transaction type (one-time, subscription) |
| payment_gateway | enum | Gateway used (stripe) |
| status | enum | pending, completed, failed, refunded |
| metadata | json | Additional data |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update time |

---

## Error Handling

The package provides comprehensive error handling:

### Gateway Errors
```php
try {
    $result = PaisaPay::payment([...]);
} catch (\Exception $e) {
    // Handle error
    echo $e->getMessage();
}
```

### Common Errors

- **Unsupported gateway:** `"Unsupported payment gateway: paypal. Supported gateways: stripe"`
- **Gateway disabled:** `"Payment gateway 'stripe' is not enabled or configured."`
- **Invalid currency:** `"Currency 'JPY' is not supported by stripe gateway."`
- **Validation error:** `"Payment amount is required."`

---

## Testing

### Test Mode (Stripe)

Use Stripe test keys:
```env
STRIPE_SECRET_KEY=sk_test_...
```

### Test Cards

| Card Number | Result |
|-------------|--------|
| 4242 4242 4242 4242 | Success |
| 4000 0000 0000 0002 | Declined |
| 4000 0000 0000 9995 | Insufficient funds |

---

## Advanced Usage

### Using Dependency Injection

```php
use TufikHasan\PaisaPay\Services\PaymentService;

class CheckoutController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}
    
    public function checkout(Request $request)
    {
        $result = $this->paymentService->payment([
            'amount' => $request->amount,
            'currency' => 'USD',
            'payment_gateway' => 'stripe',
        ]);
        
        return redirect($result['checkout_url']);
    }
}
```

### Custom Metadata

Store additional information with transactions:

```php
PaisaPay::payment([
    'amount' => 100,
    'currency' => 'USD',
    'payment_gateway' => 'stripe',
    'metadata' => [
        'user_id' => auth()->id(),
        'order_id' => 12345,
        'product_name' => 'Premium Subscription',
        'custom_field' => 'any value',
    ],
]);
```

---

## Customization with Events

This package fires several Laravel events that allow you to customize the transaction creation and verification process.

### Available Events

- `TufikHasan\PaisaPay\Events\TransactionCreated`: Fired after a transaction is created in the database.

- `TufikHasan\PaisaPay\Events\TransactionVerifying`: Fired before verifying a transaction with the payment gateway. Use this to add custom pre-verification logic.

- `TufikHasan\PaisaPay\Events\TransactionVerified`: Fired after verification is complete. Use this for post-verification processing.

### Listening to Events

In your `App\Providers\EventServiceProvider`, add listeners:

```php
protected $listen = [
    \TufikHasan\PaisaPay\Events\TransactionVerifying::class => [
        \App\Listeners\CustomVerificationLogic::class,
    ],
    \TufikHasan\PaisaPay\Events\TransactionVerified::class => [
        \App\Listeners\PostVerificationActions::class,
    ],
];
```

Then, create the listeners to implement your custom logic, such as additional validation, sending notifications, or updating other models.

### Example: Updating User Data on Payment Success

When a user completes payment on Stripe's checkout page, they are redirected to the verify URL (`/api/paisa-pay/verify/{transactionId}`), which triggers the `TransactionVerified` event. You can listen to this event to update your application's data based on successful transactions.

Create a listener:

```php
<?php

namespace App\Listeners;

use TufikHasan\PaisaPay\Events\TransactionVerified;
use App\Models\User;
use App\Models\Order;

class UpdateUserSubscription
{
    public function handle(TransactionVerified $event)
    {
        $transaction = $event->transaction;
        $response = $event->verificationResponse;

        if ($response['success'] && $response['status'] === 'completed') {
            // Update user's subscription status
            $user = User::find($transaction->metadata['user_id']);
            if ($user) {
                $user->subscription_status = 'active';
                $user->save();
            }

            // Update order status
            $order = Order::find($transaction->metadata['order_id']);
            if ($order) {
                $order->status = 'paid';
                $order->save();
            }

            // Add any other custom logic here
        }
    }
}
```

Register the listener in `EventServiceProvider`:

```php
protected $listen = [
    \TufikHasan\PaisaPay\Events\TransactionVerified::class => [
        \App\Listeners\UpdateUserSubscription::class,
    ],
];
```

This allows you to set custom verification logic for various actions in your project, such as updating user statuses, sending emails, or modifying related tables upon successful payment verification.

---

## Adding New Payment Gateways

1. **Add to PaymentGateway Enum:**
```php
enum PaymentGateway: string
{
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal'; // New
}
```

2. **Create Gateway Class:**
```php
class PaypalGateway implements PaymentGatewayInterface
{
    // Implement interface methods
}
```

3. **Update PaymentService:**
```php
return match ($gateway) {
    PaymentGateway::STRIPE->value => new StripeGateway($config),
    PaymentGateway::PAYPAL->value => new PaypalGateway($config),
};
```

4. **Add Configuration:**
```php
'paypal' => [
    'enabled' => env('PAYPAL_ENABLED', false),
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),
    'supported_currencies' => ['USD', 'EUR'],
],
```

---

## Security

- Always use HTTPS in production
- Keep API keys secure in `.env` file
- Never commit `.env` to version control
- Validate all payment data
- Use database transactions for atomic operations

---

## Troubleshooting

### "Payment gateway 'stripe' is not enabled"
- Check `STRIPE_ENABLED=true` in `.env`
- Verify config is published: `php artisan config:cache`

### "Currency not supported"
- Check `STRIPE_SUPPORTED_CURRENCIES` in `.env`
- Ensure currency is in uppercase (USD, not usd)

### Routes not working
- Run `php artisan route:list` to verify routes are registered
- Check route prefix: `/api/paisa-pay/`

---

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Support

For issues, questions, or contributions:
- Email: tufikhasan05@gmail.com
- GitHub Issues: [Create an issue](https://github.com/tufikhasan/paisa-pay/issues)

---

## Credits

Developed and maintained by **Tufik Hasan**

---

## Changelog

### Version 1.0.0
- Initial release
- Stripe integration
- Payment failed page
- Facade and API route support
- Currency validation
- Type-safe enums