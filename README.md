# Paisa - Laravel Payment Package

A flexible Laravel payment package that provides a unified interface for processing payments through multiple payment gateways including Stripe, PayPal, and bKash.

## Features

- 🔌 **Multiple Payment Gateways**: Support for Stripe, PayPal, and bKash
- 💳 **Unified Interface**: Process payments through different gateways with a consistent API
- 📊 **Transaction Tracking**: Comprehensive transaction logging with status tracking
- 🔄 **Refund Support**: Easy refund processing for all supported gateways
- ✅ **Payment Verification**: Verify transaction status with payment providers
- ⚙️ **Configurable**: Easy configuration through environment variables
- 🛡️ **Validation**: Built-in request validation for secure payment processing

## 📚 Documentation

- **[INSTALLATION.md](INSTALLATION.md)** - Step-by-step installation guide with troubleshooting
- **[USE_CASES.md](USE_CASES.md)** - 10+ practical examples and use cases
- **[README.md](README.md)** - Complete package documentation (this file)

## Installation

### Option 1: Install from GitHub Repository

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

### Option 2: Install via Composer (if published to Packagist)

```bash
composer require tufikhasan/paisa-pay
```

### Option 3: Local Development Installation

If you're developing locally, add the package repository to your `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/paisa-pay"
    }
],
"require": {
    "tufikhasan/paisa-pay": "*"
}
```

Then run:

```bash
composer update
```

### Step 2: Publish Configuration and Migrations

Publish the package configuration file:

```bash
php artisan vendor:publish --tag=paisa-config
```

Publish the migrations:

```bash
php artisan vendor:publish --tag=paisa-migrations
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Configure Payment Gateways

Add your payment gateway credentials to your `.env` file:

```env
# Default Gateway
PAISA_PAY_DEFAULT_GATEWAY=stripe
PAISA_PAY_CURRENCY=USD

# Stripe
STRIPE_ENABLED=true
STRIPE_PUBLISHABLE_KEY=your_stripe_publishable_key
STRIPE_SECRET_KEY=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

# PayPal
PAYPAL_ENABLED=true
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox
PAYPAL_WEBHOOK_ID=your_paypal_webhook_id

# bKash
BKASH_ENABLED=true
BKASH_APP_KEY=your_bkash_app_key
BKASH_APP_SECRET=your_bkash_app_secret
BKASH_USERNAME=your_bkash_username
BKASH_PASSWORD=your_bkash_password
BKASH_BASE_URL=https://tokenized.sandbox.bka.sh/v1.2.0-beta
```

## Usage

### API Endpoints

The package provides the following API endpoints:

#### 1. Process Payment

**Endpoint:** `POST /api/paisa/payment`

**Request Body:**
```json
{
    "amount": 100.00,
    "payment_type": "stripe",
    "user_id": 1,
    "type": "subscription",
    "currency": "USD",
    "metadata": {
        "order_id": "ORD-12345",
        "description": "Monthly subscription"
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
        "transaction_id": "stripe_abc123",
        "amount": 100.00,
        "user_id": 1,
        "type": "subscription",
        "payment_gateway": "stripe",
        "status": "completed",
        "metadata": {...},
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 2. Refund Transaction

**Endpoint:** `POST /api/paisa/refund/{transactionId}`

**Request Body (optional):**
```json
{
    "amount": 50.00
}
```

**Response:**
```json
{
    "success": true,
    "message": "Refund processed successfully.",
    "data": {
        "transaction_id": "stripe_abc123",
        "refund_id": "refund_xyz789",
        "status": "refunded",
        "amount": 50.00
    }
}
```

#### 3. Get Transaction Details

**Endpoint:** `GET /api/paisa/transaction/{transactionId}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "transaction_id": "stripe_abc123",
        "amount": 100.00,
        "user_id": 1,
        "type": "subscription",
        "payment_gateway": "stripe",
        "status": "completed",
        "metadata": {...},
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 4. Verify Transaction

**Endpoint:** `GET /api/paisa/verify/{transactionId}`

**Response:**
```json
{
    "success": true,
    "message": "Transaction verified successfully.",
    "data": {
        "transaction_id": "stripe_abc123",
        "status": "completed",
        "gateway": "stripe"
    }
}
```

### Programmatic Usage

You can use the PaymentService through the convenient PaisaPay facade or directly:

#### Using the PaisaPay Facade (Recommended)

```php
use PaisaPay; // Facade is auto-loaded

// Process a payment
$transaction = PaisaPay::processPayment([
    'amount' => 100.00,
    'payment_type' => 'stripe',
    'user_id' => 1,
    'type' => 'subscription',
    'currency' => 'USD',
]);

// Refund a transaction
$response = PaisaPay::refundTransaction('stripe_abc123', 50.00);

// Verify a transaction
$response = PaisaPay::verifyTransaction('stripe_abc123');

// Get transaction details
$transaction = PaisaPay::getTransaction('stripe_abc123');
```

#### Using Dependency Injection

```php
use TufikHasan\PaisaPay\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function process(Request $request)
    {
        $transaction = $this->paymentService->processPayment($request->validated());
        return response()->json(['transaction' => $transaction]);
    }
}
```

#### Using Service Container

```php
use TufikHasan\PaisaPay\Services\PaymentService;

$paymentService = app(PaymentService::class);

// Process a payment
$transaction = $paymentService->processPayment([
    'amount' => 100.00,
    'payment_type' => 'stripe',
    'user_id' => 1,
    'type' => 'subscription',
    'currency' => 'USD',
]);
```

## Supported Payment Gateways

### Stripe
- **Currency**: Multiple currencies supported
- **Features**: Charge, Refund, Verify
- **Documentation**: [Stripe API Docs](https://stripe.com/docs/api)

### PayPal
- **Currency**: Multiple currencies supported
- **Features**: Charge, Refund, Verify
- **Documentation**: [PayPal API Docs](https://developer.paypal.com/docs/api/overview/)

### bKash
- **Currency**: BDT (Bangladeshi Taka) only
- **Features**: Charge, Refund, Verify
- **Documentation**: [bKash API Docs](https://developer.bka.sh/)

## Database Schema

The package creates a `transactions` table with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| transaction_id | string | Unique transaction ID from gateway |
| amount | decimal(10,2) | Transaction amount |
| user_id | bigint | User who made the transaction |
| type | string | Transaction type (subscription, one-time, etc.) |
| payment_gateway | string | Gateway used (stripe, paypal, bkash) |
| status | enum | Transaction status (pending, completed, failed, refunded) |
| metadata | json | Additional transaction data |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

## Transaction Model

The `Transaction` model provides several helpful methods:

```php
// Check transaction status
$transaction->isCompleted();
$transaction->isPending();
$transaction->isFailed();
$transaction->isRefunded();

// Query scopes
Transaction::status('completed')->get();
Transaction::gateway('stripe')->get();
Transaction::type('subscription')->get();

// Relationships
$transaction->user; // Get the user who made the transaction
```

## Important Notes

1. **Gateway Implementation**: The current gateway implementations are mock implementations for demonstration. For production use, you'll need to integrate the actual SDK for each payment gateway:
   - Stripe: `composer require stripe/stripe-php`
   - PayPal: `composer require paypal/rest-api-sdk-php`
   - bKash: Implement according to bKash API documentation

2. **Security**: Always validate and sanitize payment data. Use HTTPS in production.

3. **Webhooks**: For production, implement webhook handlers to receive real-time payment status updates from payment gateways.

4. **Testing**: Test thoroughly in sandbox/test mode before going live.

## License

MIT License
