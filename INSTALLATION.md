# Installation Guide

This guide provides step-by-step instructions for installing the Paisa Laravel Payment Package.

## Prerequisites

- PHP 8.2 or higher
- Laravel 11.x or 12.x
- Composer

## Installation Methods

### Method 1: Install from GitHub Repository (Recommended)

1. **Add the repository to your composer.json:**

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/tufikhasan/paisa-pay.git"
        }
    ],
    "require": {
        "tufikhasan/paisa-pay": "dev-main"
    }
}
```

2. **Update Composer dependencies:**

```bash
composer update
```

### Method 2: Install via Composer (if published to Packagist)

```bash
composer require tufikhasan/paisa-pay
```

### Method 3: Local Development Installation

If you're developing the package locally:

1. **Clone the repository:**

```bash
git clone https://github.com/tufikhasan/paisa-pay.git
```

2. **Add to your main project's composer.json:**

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/paisa-pay"
        }
    ],
    "require": {
        "tufikhasan/paisa-pay": "*"
    }
}
```

3. **Update Composer:**

```bash
composer update
```

## Configuration

### Step 1: Publish Configuration

```bash
php artisan vendor:publish --tag=paisa-config
```

This will create `config/paisa.php` in your project.

### Step 2: Publish Migrations

```bash
php artisan vendor:publish --tag=paisa-migrations
```

This will create the transactions table migration.

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Configure Environment Variables

Add the following to your `.env` file:

```env
# Default Gateway
PAISA_PAY_DEFAULT_GATEWAY=stripe
PAISA_PAY_CURRENCY=USD

# Stripe Configuration
STRIPE_ENABLED=true
STRIPE_PUBLISHABLE_KEY=your_stripe_publishable_key
STRIPE_SECRET_KEY=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

# PayPal Configuration
PAYPAL_ENABLED=true
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox
PAYPAL_WEBHOOK_ID=your_paypal_webhook_id

# bKash Configuration
BKASH_ENABLED=true
BKASH_APP_KEY=your_bkash_app_key
BKASH_APP_SECRET=your_bkash_app_secret
BKASH_USERNAME=your_bkash_username
BKASH_PASSWORD=your_bkash_password
BKASH_BASE_URL=https://tokenized.sandbox.bka.sh/v1.2.0-beta
```

## Testing the Installation

### 1. Check if the service provider is registered:

```bash
php artisan route:list | grep paisa
```

You should see routes like:
- `POST /api/paisa/payment`
- `POST /api/paisa/refund/{transactionId}`
- `GET /api/paisa/transaction/{transactionId}`
- `GET /api/paisa/verify/{transactionId}`

### 2. Test a simple payment request:

```bash
curl -X POST http://your-app-url/api/paisa/payment \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100.00,
    "payment_type": "stripe",
    "user_id": 1,
    "type": "test"
  }'
```

## Troubleshooting

### Common Issues

1. **Class not found errors:**
   - Run `composer dump-autoload`
   - Make sure the service provider is registered in `config/app.php`

2. **Routes not showing:**
   - Check if the package is properly installed
   - Run `php artisan config:clear` and `php artisan route:clear`

3. **Migration errors:**
   - Make sure you published the migrations
   - Check database connection

4. **Gateway configuration errors:**
   - Verify your API keys in `.env`
   - Check if the gateway is enabled in config

### Getting Help

If you encounter issues:

1. Check the [GitHub Issues](https://github.com/tufikhasan/paisa-pay/issues) page
2. Review the [USE_CASES.md](USE_CASES.md) for examples
3. Check the [QUICK_REFERENCE.md](QUICK_REFERENCE.md) for common tasks

## Next Steps

After successful installation:

1. Review the [README.md](README.md) for API documentation
2. Check out [USE_CASES.md](USE_CASES.md) for practical examples
3. Configure webhooks for production use
4. Test with sandbox credentials first