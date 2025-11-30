# Use Cases & Examples

This document provides practical examples and use cases for the Paisa Laravel Payment Package.

## Table of Contents

1. [Basic Payment Processing](#basic-payment-processing)
2. [Subscription Payments](#subscription-payments)
3. [E-commerce Integration](#e-commerce-integration)
4. [Refund Management](#refund-management)
5. [Payment Verification](#payment-verification)
6. [Multi-gateway Strategy](#multi-gateway-strategy)
7. [Webhook Handling](#webhook-handling)
8. [Error Handling](#error-handling)
9. [Testing Examples](#testing-examples)
10. [Advanced Features](#advanced-features)

## Basic Payment Processing

### One-time Payment

```php
use Towfik\PaisaPay\Services\PaymentService;
use Towfik\PaisaPay\Models\Transaction;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        $paymentService = app(PaymentService::class);

        try {
            $transaction = $paymentService->processPayment([
                'amount' => 99.99,
                'payment_type' => 'stripe',
                'user_id' => auth()->id(),
                'type' => 'one-time',
                'currency' => 'USD',
                'metadata' => [
                    'order_id' => 'ORD-2024-001',
                    'description' => 'Premium Plan Purchase'
                ]
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'status' => $transaction->status
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
```

### API Request Example

```bash
curl -X POST http://your-app.com/api/paisa/payment \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 99.99,
    "payment_type": "stripe",
    "user_id": 1,
    "type": "one-time",
    "currency": "USD",
    "metadata": {
      "order_id": "ORD-2024-001",
      "description": "Premium Plan Purchase"
    }
  }'
```

## Subscription Payments

### Monthly Subscription Setup

```php
class SubscriptionService
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createMonthlySubscription($userId, $planAmount = 29.99)
    {
        // Process initial payment
        $transaction = $this->paymentService->processPayment([
            'amount' => $planAmount,
            'payment_type' => 'stripe',
            'user_id' => $userId,
            'type' => 'subscription',
            'currency' => 'USD',
            'metadata' => [
                'plan' => 'monthly',
                'billing_cycle' => 'monthly',
                'next_billing_date' => now()->addMonth()->toDateString()
            ]
        ]);

        // Create subscription record
        Subscription::create([
            'user_id' => $userId,
            'transaction_id' => $transaction->id,
            'plan_name' => 'Monthly Premium',
            'amount' => $planAmount,
            'currency' => 'USD',
            'status' => 'active',
            'next_billing_date' => now()->addMonth(),
            'metadata' => $transaction->metadata
        ]);

        return $transaction;
    }

    public function processRecurringPayment($subscriptionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        if ($subscription->next_billing_date->isFuture()) {
            return; // Not due yet
        }

        try {
            $transaction = $this->paymentService->processPayment([
                'amount' => $subscription->amount,
                'payment_type' => $subscription->payment_gateway,
                'user_id' => $subscription->user_id,
                'type' => 'subscription_renewal',
                'currency' => $subscription->currency,
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'billing_cycle' => 'monthly',
                    'renewal_date' => now()->toDateString()
                ]
            ]);

            // Update subscription
            $subscription->update([
                'last_billing_date' => now(),
                'next_billing_date' => now()->addMonth(),
                'status' => 'active'
            ]);

            return $transaction;

        } catch (Exception $e) {
            // Handle failed payment
            $subscription->update(['status' => 'payment_failed']);
            throw $e;
        }
    }
}
```

## E-commerce Integration

### Shopping Cart Checkout

```php
class CheckoutController extends Controller
{
    public function processOrder(Request $request)
    {
        $cart = session('cart', []);
        $total = collect($cart)->sum('price');

        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'status' => 'pending',
                'items' => $cart
            ]);

            // Process payment
            $transaction = app(PaymentService::class)->processPayment([
                'amount' => $total,
                'payment_type' => $request->payment_method,
                'user_id' => auth()->id(),
                'type' => 'order',
                'currency' => 'USD',
                'metadata' => [
                    'order_id' => $order->id,
                    'items_count' => count($cart),
                    'checkout_method' => 'web'
                ]
            ]);

            // Update order with transaction
            $order->update([
                'status' => 'paid',
                'transaction_id' => $transaction->transaction_id,
                'paid_at' => now()
            ]);

            DB::commit();

            // Clear cart
            session()->forget('cart');

            return redirect()->route('order.success', $order);

        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'payment' => 'Payment failed: ' . $e->getMessage()
            ]);
        }
    }
}
```

### Order Model with Payment Integration

```php
class Order extends Model
{
    protected $fillable = [
        'user_id', 'total_amount', 'status', 'transaction_id',
        'paid_at', 'items', 'shipping_address'
    ];

    protected $casts = [
        'items' => 'array',
        'paid_at' => 'datetime'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }

    public function canRefund()
    {
        return $this->status === 'paid' &&
               $this->paid_at &&
               $this->paid_at->diffInDays(now()) <= 30; // 30-day refund policy
    }

    public function processRefund($amount = null)
    {
        if (!$this->canRefund()) {
            throw new Exception('Order is not eligible for refund');
        }

        $refundAmount = $amount ?: $this->total_amount;

        $paymentService = app(PaymentService::class);
        $refund = $paymentService->refundTransaction(
            $this->transaction_id,
            $refundAmount
        );

        if ($refund['success']) {
            $this->update(['status' => 'refunded']);
        }

        return $refund;
    }
}
```

## Refund Management

### Partial and Full Refunds

```php
class RefundService
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function processRefund($transactionId, $refundAmount = null, $reason = null)
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        // Validate refund eligibility
        if (!$this->isRefundEligible($transaction)) {
            throw new Exception('Transaction is not eligible for refund');
        }

        // Determine refund amount
        $refundAmount = $refundAmount ?: $transaction->amount;

        if ($refundAmount > $transaction->amount) {
            throw new Exception('Refund amount cannot exceed transaction amount');
        }

        try {
            $refund = $this->paymentService->refundTransaction(
                $transactionId,
                $refundAmount
            );

            if ($refund['success']) {
                // Create refund record
                Refund::create([
                    'transaction_id' => $transaction->id,
                    'refund_transaction_id' => $refund['refund_id'] ?? null,
                    'amount' => $refundAmount,
                    'reason' => $reason,
                    'status' => 'completed',
                    'processed_at' => now()
                ]);

                // Update transaction status if full refund
                if ($refundAmount == $transaction->amount) {
                    $transaction->update(['status' => 'refunded']);
                }
            }

            return $refund;

        } catch (Exception $e) {
            // Log failed refund attempt
            Refund::create([
                'transaction_id' => $transaction->id,
                'amount' => $refundAmount,
                'reason' => $reason,
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function isRefundEligible($transaction)
    {
        // Business rules for refund eligibility
        if (!in_array($transaction->status, ['completed'])) {
            return false;
        }

        // Check time limit (e.g., 30 days)
        if ($transaction->created_at->diffInDays(now()) > 30) {
            return false;
        }

        // Check if already refunded
        $existingRefunds = Refund::where('transaction_id', $transaction->id)->sum('amount');
        if ($existingRefunds >= $transaction->amount) {
            return false;
        }

        return true;
    }
}
```

## Payment Verification

### Transaction Verification Service

```php
class PaymentVerificationService
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function verifyAndUpdateTransaction($transactionId)
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        try {
            $verification = $this->paymentService->verifyTransaction($transactionId);

            if ($verification['success']) {
                $currentStatus = $transaction->status;
                $gatewayStatus = $verification['status'];

                // Map gateway status to local status
                $statusMapping = [
                    'succeeded' => 'completed',
                    'paid' => 'completed',
                    'completed' => 'completed',
                    'pending' => 'pending',
                    'failed' => 'failed',
                    'cancelled' => 'cancelled'
                ];

                $newStatus = $statusMapping[$gatewayStatus] ?? $gatewayStatus;

                if ($currentStatus !== $newStatus) {
                    $transaction->update([
                        'status' => $newStatus,
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'last_verified_at' => now()->toISOString(),
                            'verification_response' => $verification
                        ])
                    ]);

                    // Trigger status change events
                    $this->handleStatusChange($transaction, $currentStatus, $newStatus);
                }
            }

            return $verification;

        } catch (Exception $e) {
            // Log verification failure
            Log::warning('Payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function handleStatusChange($transaction, $oldStatus, $newStatus)
    {
        // Example: Send notifications, update related records
        if ($oldStatus === 'pending' && $newStatus === 'completed') {
            // Payment completed - trigger order fulfillment
            event(new PaymentCompleted($transaction));
        } elseif ($newStatus === 'failed') {
            // Payment failed - notify user
            event(new PaymentFailed($transaction));
        }
    }
}
```

## Multi-gateway Strategy

### Gateway Selection Service

```php
class GatewaySelectionService
{
    protected $gateways = ['stripe', 'paypal', 'bkash'];
    protected $userPreferences = [];

    public function selectGateway($amount, $currency, $userId = null, $country = null)
    {
        // Load user preferences if available
        if ($userId) {
            $this->userPreferences = $this->getUserGatewayPreferences($userId);
        }

        $availableGateways = $this->getAvailableGateways($amount, $currency, $country);

        // Priority 1: User preferred gateway
        if (!empty($this->userPreferences)) {
            foreach ($this->userPreferences as $preferred) {
                if (in_array($preferred, $availableGateways)) {
                    return $preferred;
                }
            }
        }

        // Priority 2: Gateway with lowest fees for amount
        $gatewayFees = $this->calculateGatewayFees($amount, $availableGateways);
        asort($gatewayFees); // Sort by fee (lowest first)
        return array_key_first($gatewayFees);
    }

    private function getAvailableGateways($amount, $currency, $country = null)
    {
        $available = [];

        foreach ($this->gateways as $gateway) {
            $config = config("paisa.gateways.{$gateway}");

            if (!($config['enabled'] ?? false)) {
                continue;
            }

            // Check currency support
            if (!$this->supportsCurrency($gateway, $currency)) {
                continue;
            }

            // Check amount limits
            if (!$this->withinAmountLimits($gateway, $amount, $currency)) {
                continue;
            }

            // Check country restrictions
            if ($country && !$this->supportsCountry($gateway, $country)) {
                continue;
            }

            $available[] = $gateway;
        }

        return $available;
    }

    private function supportsCurrency($gateway, $currency)
    {
        $currencyMap = [
            'stripe' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'], // Many currencies
            'paypal' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'], // Many currencies
            'bkash' => ['BDT'] // Only BDT
        ];

        return in_array($currency, $currencyMap[$gateway] ?? []);
    }

    private function withinAmountLimits($gateway, $amount, $currency)
    {
        $limits = [
            'stripe' => ['min' => 0.50, 'max' => 999999.99],
            'paypal' => ['min' => 1.00, 'max' => 10000.00],
            'bkash' => ['min' => 10.00, 'max' => 25000.00] // BDT limits
        ];

        $gatewayLimits = $limits[$gateway] ?? ['min' => 0, 'max' => PHP_FLOAT_MAX];

        return $amount >= $gatewayLimits['min'] && $amount <= $gatewayLimits['max'];
    }

    private function calculateGatewayFees($amount, $gateways)
    {
        $fees = [];

        foreach ($gateways as $gateway) {
            $fees[$gateway] = $this->calculateFee($gateway, $amount);
        }

        return $fees;
    }

    private function calculateFee($gateway, $amount)
    {
        // Simplified fee calculation (percentage + fixed fee)
        $feeStructure = [
            'stripe' => ['percentage' => 0.029, 'fixed' => 0.30],
            'paypal' => ['percentage' => 0.034, 'fixed' => 0.49],
            'bkash' => ['percentage' => 0.015, 'fixed' => 0.00] // Lower fees for local
        ];

        $structure = $feeStructure[$gateway];
        return ($amount * $structure['percentage']) + $structure['fixed'];
    }

    private function getUserGatewayPreferences($userId)
    {
        // In a real app, this would come from user preferences/settings
        return ['stripe', 'paypal']; // Example preferences
    }
}
```

## Webhook Handling

### Webhook Controller

```php
class WebhookController extends Controller
{
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('paisa.gateways.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Webhook signature verification failed', 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            case 'charge.dispute.created':
                $this->handleDisputeCreated($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
        }

        return response('Webhook processed', 200);
    }

    private function handlePaymentSucceeded($paymentIntent)
    {
        $transaction = Transaction::where('transaction_id', $paymentIntent->id)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'completed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'webhook_received_at' => now()->toISOString(),
                    'stripe_payment_intent' => $paymentIntent
                ])
            ]);

            // Trigger success actions
            event(new PaymentCompleted($transaction));
        }
    }

    private function handlePaymentFailed($paymentIntent)
    {
        $transaction = Transaction::where('transaction_id', $paymentIntent->id)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'failed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'failure_reason' => $paymentIntent->last_payment_error?->message,
                    'webhook_received_at' => now()->toISOString()
                ])
            ]);

            // Trigger failure actions
            event(new PaymentFailed($transaction));
        }
    }
}
```

## Error Handling

### Payment Exception Handler

```php
class PaymentException extends Exception
{
    protected $errorCode;
    protected $gateway;
    protected $retryable;

    public function __construct($message, $errorCode = null, $gateway = null, $retryable = false)
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->gateway = $gateway;
        $this->retryable = $retryable;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    public function isRetryable()
    {
        return $this->retryable;
    }
}
```

### Enhanced Payment Service with Error Handling

```php
class EnhancedPaymentService extends PaymentService
{
    public function processPayment(array $data)
    {
        try {
            return parent::processPayment($data);

        } catch (PaymentException $e) {
            $this->logPaymentError($e, $data);

            if ($e->isRetryable()) {
                // Attempt retry with different gateway
                return $this->retryWithAlternativeGateway($data, $e->getGateway());
            }

            throw $e;

        } catch (Exception $e) {
            // Handle unexpected errors
            $this->logPaymentError($e, $data);
            throw new PaymentException(
                'Payment processing failed: ' . $e->getMessage(),
                'PAYMENT_FAILED',
                null,
                false
            );
        }
    }

    private function retryWithAlternativeGateway($data, $failedGateway)
    {
        $gateways = ['stripe', 'paypal', 'bkash'];
        $availableGateways = array_diff($gateways, [$failedGateway]);

        foreach ($availableGateways as $gateway) {
            try {
                $data['payment_type'] = $gateway;
                return parent::processPayment($data);

            } catch (Exception $e) {
                // Log retry failure and continue to next gateway
                Log::warning('Payment retry failed', [
                    'gateway' => $gateway,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        throw new PaymentException(
            'All payment gateways failed',
            'ALL_GATEWAYS_FAILED',
            null,
            false
        );
    }

    private function logPaymentError($exception, $data)
    {
        Log::error('Payment processing error', [
            'error' => $exception->getMessage(),
            'gateway' => $exception->getGateway(),
            'error_code' => $exception->getErrorCode(),
            'user_id' => $data['user_id'] ?? null,
            'amount' => $data['amount'] ?? null,
            'payment_type' => $data['payment_type'] ?? null,
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
```

## Testing Examples

### Payment Testing with Mock Data

```php
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_payment_processing()
    {
        $user = User::factory()->create();

        $paymentData = [
            'amount' => 100.00,
            'payment_type' => 'stripe',
            'user_id' => $user->id,
            'type' => 'test',
            'currency' => 'USD'
        ];

        $paymentService = app(PaymentService::class);
        $transaction = $paymentService->processPayment($paymentData);

        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals(100.00, $transaction->amount);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals('stripe', $transaction->payment_gateway);
    }

    public function test_payment_refund()
    {
        // Create a completed transaction
        $transaction = Transaction::factory()->create([
            'status' => 'completed',
            'amount' => 50.00,
            'payment_gateway' => 'stripe'
        ]);

        $paymentService = app(PaymentService::class);
        $refund = $paymentService->refundTransaction($transaction->transaction_id);

        $this->assertTrue($refund['success']);
        $this->assertEquals('refunded', $transaction->fresh()->status);
    }

    public function test_gateway_selection()
    {
        $selectionService = app(GatewaySelectionService::class);

        // Test gateway selection for different amounts
        $gateway = $selectionService->selectGateway(10.00, 'USD');
        $this->assertEquals('stripe', $gateway); // Lowest fees for small amounts

        $gateway = $selectionService->selectGateway(1000.00, 'USD');
        $this->assertEquals('paypal', $gateway); // Better for large amounts
    }
}
```

## Advanced Features

### Payment Analytics Service

```php
class PaymentAnalyticsService
{
    public function getRevenueMetrics($startDate = null, $endDate = null)
    {
        $query = Transaction::where('status', 'completed');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total_revenue' => $query->sum('amount'),
            'transaction_count' => $query->count(),
            'average_transaction' => $query->avg('amount'),
            'revenue_by_gateway' => $query->selectRaw('payment_gateway, SUM(amount) as total')
                                         ->groupBy('payment_gateway')
                                         ->pluck('total', 'payment_gateway'),
            'revenue_by_type' => $query->selectRaw('type, SUM(amount) as total')
                                     ->groupBy('type')
                                     ->pluck('total', 'type')
        ];
    }

    public function getFailureRate()
    {
        $total = Transaction::count();
        $failed = Transaction::where('status', 'failed')->count();

        return $total > 0 ? ($failed / $total) * 100 : 0;
    }

    public function getTopPayingUsers($limit = 10)
    {
        return Transaction::selectRaw('user_id, SUM(amount) as total_spent, COUNT(*) as transaction_count')
                         ->where('status', 'completed')
                         ->groupBy('user_id')
                         ->orderBy('total_spent', 'desc')
                         ->limit($limit)
                         ->with('user')
                         ->get();
    }
}
```

### Usage in Controller

```php
class AnalyticsController extends Controller
{
    public function dashboard()
    {
        $analytics = app(PaymentAnalyticsService::class);

        return view('admin.analytics', [
            'metrics' => $analytics->getRevenueMetrics(now()->startOfMonth(), now()),
            'failure_rate' => $analytics->getFailureRate(),
            'top_users' => $analytics->getTopPayingUsers(5)
        ]);
    }
}
```

This collection of use cases demonstrates the flexibility and power of the Paisa payment package for various real-world scenarios. Each example includes proper error handling, logging, and follows Laravel best practices.