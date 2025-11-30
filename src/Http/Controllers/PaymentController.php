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

    /**
     * Process a payment.
     */
    public function processPayment(PaymentRequest $request): JsonResponse
    {
        try {
            $transaction = $this->paymentService->processPayment($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully.',
                'data' => new TransactionResource($transaction),
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
     * Refund a transaction.
     */
    public function refundTransaction(string $transactionId): JsonResponse
    {
        try {
            $amount = request('amount');
            $response = $this->paymentService->refundTransaction($transactionId, $amount);

            return response()->json([
                'success' => $response['success'],
                'message' => $response['success'] ? 'Refund processed successfully.' : 'Refund failed.',
                'data' => $response,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund processing failed.',
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
    public function verifyTransaction(string $transactionId): JsonResponse
    {
        try {
            $response = $this->paymentService->verifyTransaction($transactionId);

            return response()->json([
                'success' => true,
                'message' => 'Transaction verified successfully.',
                'data' => $response,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction verification failed.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
