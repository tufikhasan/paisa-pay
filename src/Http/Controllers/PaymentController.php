<?php

namespace TufikHasan\PaisaPay\Http\Controllers;

use TufikHasan\PaisaPay\Http\Requests\PaymentRequest;
use TufikHasan\PaisaPay\Http\Resources\TransactionResource;
use TufikHasan\PaisaPay\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Exception;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function payment(PaymentRequest $request): JsonResponse
    {
        try {
            $result = $this->paymentService->payment($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully.',
                'data' => new TransactionResource($result['transaction']),
                'checkout_url' => $result['checkout_url'],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get transaction details.
     */
    public function getTransaction(string $transactionId): JsonResponse
    {
        try {
            $transaction = $this->paymentService->getTransaction($transactionId);

            return response()->json([
                'success' => true,
                'data' => new TransactionResource($transaction),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Verify a transaction.
     */
    public function verifyTransaction(string $transactionId)
    {
        try {
            $response = $this->paymentService->verifyTransaction($transactionId);

            // Get the transaction
            $transaction = $this->paymentService->getTransaction($transactionId);

            // Check if verification was successful
            if ($response['success'] && $response['status'] === 'completed') {
                return view('paisapay::payment-success', compact('transaction'));
            } else {
                return redirect()->route('paisa-pay.failed', [
                    'error' => $response['error'] ?? 'Payment verification failed'
                ]);
            }
        } catch (Exception $e) {
            return redirect()->route('paisa-pay.failed', [
                'error' => 'Payment verification failed',
            ]);
        }
    }

    public function failed()
    {
        return view('paisapay::payment-failed');
    }
}
