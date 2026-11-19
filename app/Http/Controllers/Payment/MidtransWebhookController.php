<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\MidtransVAAccount;
use App\Models\RoomAssignment;
use App\Models\VAPayment;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Handle incoming webhook notification from Midtrans.
     */
    public function handle(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = isset($payload['fraud_status']) ? (string) $payload['fraud_status'] : null;

        if (empty($orderId) || empty($statusCode) || empty($signatureKey) || empty($transactionStatus)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing required webhook notification parameters.',
            ], 422);
        }

        // Verify SHA-512 signature from Midtrans
        if (! $midtransService->verifyWebhookSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans webhook signature verification failed', [
                'order_id'     => $orderId,
                'status_code'  => $statusCode,
                'gross_amount' => $grossAmount,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid signature.',
            ], 403);
        }

        $normalizedStatus = $midtransService->normalizeStatus($transactionStatus, $fraudStatus);

        // Find existing VAPayment or find by VA account
        $vaPayment = VAPayment::where('order_id', $orderId)->first();

        if (! $vaPayment) {
            $vaAccount = MidtransVAAccount::where('external_id', $orderId)->first();

            if (! $vaAccount) {
                Log::warning('Midtrans webhook received for unknown order_id', ['order_id' => $orderId]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Order not found.',
                ], 404);
            }

            $assignment = RoomAssignment::where('room_id', $vaAccount->room_id)
                ->where('status', 'active')
                ->latest()
                ->first();

            $vaPayment = new VAPayment([
                'va_account_id'      => $vaAccount->id,
                'room_assignment_id' => $assignment?->id,
                'order_id'           => $orderId,
                'amount'             => (float) $grossAmount,
            ]);
        }

        // Update transaction attributes
        $vaPayment->transaction_status = $normalizedStatus;

        if (! empty($payload['transaction_id'])) {
            $vaPayment->transaction_id = (string) $payload['transaction_id'];
        }

        if (! empty($payload['transaction_time'])) {
            try {
                $vaPayment->transaction_time = Carbon::parse($payload['transaction_time']);
            } catch (\Throwable $e) {
                $vaPayment->transaction_time = now();
            }
        }

        if ($normalizedStatus === 'settlement') {
            $vaPayment->paid_at = ! empty($payload['settlement_time'])
                ? Carbon::parse($payload['settlement_time'])
                : now();
        } elseif (in_array($normalizedStatus, ['expired', 'cancelled'], true)) {
            $vaPayment->paid_at = null;
        }

        $vaPayment->save();

        Log::info('Midtrans payment updated via webhook', [
            'order_id'           => $orderId,
            'transaction_status' => $normalizedStatus,
            'paid_at'            => $vaPayment->paid_at,
        ]);

        return response()->json([
            'status'             => 'success',
            'message'            => 'Notification handled successfully.',
            'order_id'           => $orderId,
            'transaction_status' => $normalizedStatus,
        ]);
    }
}
