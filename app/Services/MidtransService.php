<?php

namespace App\Services;

use App\Models\MidtransVAAccount;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use App\Models\VAPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected string $merchantId;
    protected bool $isProduction;
    protected string $baseUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('services.midtrans.server_key', '');
        $this->clientKey = (string) config('services.midtrans.client_key', '');
        $this->merchantId = (string) config('services.midtrans.merchant_id', '');
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->baseUrl = (string) config(
            'services.midtrans.base_url',
            $this->isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com'
        );
    }

    public function getServerKey(): string
    {
        return $this->serverKey;
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Create or regenerate a Virtual Account for a room assignment.
     */
    public function createVirtualAccount(RoomAssignment $assignment, string $bankCode): MidtransVAAccount
    {
        $room = $assignment->room;
        if (! $room) {
            throw new RuntimeException('Penugasan kamar tidak memiliki data kamar yang valid.');
        }

        $amount = (float) ($assignment->rent_amount_snapshot ?? $room->rent_amount);
        $customer = $assignment->tenantProfile?->user;

        $result = $this->createVirtualAccountForRoom($room, $bankCode, $amount, $customer);
        $vaAccount = $result['va_account'];
        $body = $result['response_body'];
        $orderId = $result['order_id'];

        // Also record pending VA payment for this assignment
        VAPayment::updateOrCreate(
            ['order_id' => $orderId],
            [
                'va_account_id'      => $vaAccount->id,
                'room_assignment_id' => $assignment->id,
                'transaction_id'     => $body['transaction_id'] ?? null,
                'amount'             => $amount,
                'transaction_status' => $this->normalizeStatus($body['transaction_status'] ?? 'pending'),
                'transaction_time'   => isset($body['transaction_time']) ? Carbon::parse($body['transaction_time']) : now(),
                'paid_at'            => null,
            ]
        );

        return $vaAccount;
    }

    /**
     * Create or regenerate a Virtual Account directly for a room.
     *
     * @return array{va_account: MidtransVAAccount, response_body: array, order_id: string}
     */
    public function createVirtualAccountForRoom(Room $room, string $bankCode, ?float $amount = null, ?User $customer = null): array
    {
        $bankCode = strtolower(trim($bankCode));
        $validBanks = ['bca', 'bni', 'bri', 'mandiri', 'permata'];
        if (! in_array($bankCode, $validBanks, true)) {
            throw new RuntimeException("Bank {$bankCode} tidak didukung. Pilih salah satu: " . implode(', ', $validBanks));
        }

        $amount = (float) ($amount ?? $room->rent_amount);
        $orderId = 'SMARTKOS-VA-' . $room->id . '-' . time();

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) round($amount),
            ],
            'customer_details' => [
                'first_name' => $customer?->name ?? "Penghuni Kamar {$room->room_number}",
                'email'      => $customer?->email ?? null,
                'phone'      => $customer?->phone ?? null,
            ],
            'item_details' => [
                [
                    'id'       => 'ROOM-' . $room->id,
                    'price'    => (int) round($amount),
                    'quantity' => 1,
                    'name'     => "Sewa Kamar No. {$room->room_number}",
                ],
            ],
        ];

        if ($bankCode === 'mandiri') {
            $payload['payment_type'] = 'echannel';
            $payload['echannel'] = [
                'bill_info1' => 'Smart Kos Rent',
                'bill_info2' => "Kamar {$room->room_number}",
            ];
        } elseif ($bankCode === 'permata') {
            $payload['payment_type'] = 'permata';
        } else {
            $payload['payment_type'] = 'bank_transfer';
            $payload['bank_transfer'] = [
                'bank' => $bankCode,
            ];
        }

        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
        ])->post($this->baseUrl . '/v2/charge', $payload);

        $body = $response->json() ?? [];

        if (! $response->successful() || (isset($body['status_code']) && ! in_array((string) $body['status_code'], ['200', '201'], true))) {
            $msg = $body['status_message'] ?? 'Gagal membuat Virtual Account di Midtrans.';
            Log::error('Midtrans Charge Error', ['response' => $body, 'status' => $response->status()]);
            throw new RuntimeException($msg);
        }

        $vaNumber = $this->extractVaNumber($body, $bankCode);
        $expiryTime = isset($body['expiry_time'])
            ? Carbon::parse($body['expiry_time'])
            : now()->addHours(24);

        $vaAccount = MidtransVAAccount::updateOrCreate(
            ['room_id' => $room->id],
            [
                'bank_code'   => $bankCode,
                'va_number'   => $vaNumber,
                'external_id' => $orderId,
                'amount'      => $amount,
                'status'      => 'active',
                'expires_at'  => $expiryTime,
            ]
        );

        return [
            'va_account'    => $vaAccount,
            'response_body' => $body,
            'order_id'      => $orderId,
        ];
    }

    /**
     * Check transaction status directly with Midtrans.
     */
    public function checkTransactionStatus(string $orderId): array
    {
        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
        ])->get($this->baseUrl . '/v2/' . $orderId . '/status');

        $body = $response->json() ?? [];

        if (! $response->successful()) {
            throw new RuntimeException($body['status_message'] ?? 'Gagal mengecek status transaksi di Midtrans.');
        }

        return $body;
    }

    /**
     * Verify Midtrans notification webhook signature.
     */
    public function verifyWebhookSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Normalize Midtrans transaction status to standard application status.
     * Possible normalized statuses: pending, settlement, expired, cancelled, refund.
     */
    public function normalizeStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        $status = strtolower($transactionStatus);

        if ($status === 'capture') {
            return ($fraudStatus === 'challenge') ? 'pending' : 'settlement';
        }

        if (in_array($status, ['settlement', 'paid', 'success'], true)) {
            return 'settlement';
        }

        if ($status === 'pending') {
            return 'pending';
        }

        if (in_array($status, ['deny', 'cancel'], true)) {
            return 'cancelled';
        }

        if ($status === 'expire') {
            return 'expired';
        }

        if ($status === 'refund') {
            return 'refund';
        }

        return $status;
    }

    /**
     * Extract Virtual Account number from Midtrans response.
     */
    protected function extractVaNumber(array $body, string $bankCode): string
    {
        if (! empty($body['va_numbers']) && is_array($body['va_numbers'])) {
            return (string) ($body['va_numbers'][0]['va_number'] ?? '');
        }

        if ($bankCode === 'mandiri' && ! empty($body['bill_key'])) {
            return (string) $body['bill_key']; // Biller code is typically in $body['biller_code']
        }

        if ($bankCode === 'permata' && ! empty($body['permata_va_number'])) {
            return (string) $body['permata_va_number'];
        }

        return (string) ($body['va_number'] ?? '');
    }
}

