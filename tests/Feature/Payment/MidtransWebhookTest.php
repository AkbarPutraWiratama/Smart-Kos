<?php

namespace Tests\Feature\Payment;

use App\Models\Location;
use App\Models\MidtransVAAccount;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\VAPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $serverKey = 'SB-Mid-server-test-key-12345';

    private Location $location;

    private Room $room;

    private User $tenantUser;

    private TenantProfile $tenantProfile;

    private RoomAssignment $assignment;

    private MidtransVAAccount $vaAccount;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', $this->serverKey);
        Config::set('services.midtrans.is_production', false);

        $this->location = Location::create([
            'name' => 'Kos Melati Indah',
            'address' => 'Jl. Melati No. 12',
            'floor_count' => 2,
            'status' => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name' => 1,
            'room_number' => 101,
            'rent_amount' => 1000000,
            'status' => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budisantoso',
            'email' => 'budi@example.com',
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone' => '081234567890',
        ]);

        $this->assignment = RoomAssignment::create([
            'room_id' => $this->room->id,
            'tenant_id' => $this->tenantProfile->id,
            'start_date' => now()->subDays(15)->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 1000000,
        ]);

        $this->vaAccount = MidtransVAAccount::create([
            'room_id' => $this->room->id,
            'bank_code' => 'bca',
            'va_number' => '12345678901234',
            'external_id' => 'SMARTKOS-VA-' . $this->room->id . '-12345',
            'amount' => 1000000,
            'status' => 'active',
            'expires_at' => now()->addDay(),
        ]);
    }

    /**
     * Generate a valid SHA-512 signature for test payload.
     */
    private function generateSignature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
    }

    public function test_webhook_with_valid_signature_updates_va_payment_to_settlement_and_sets_paid_at(): void
    {
        $orderId = 'SMARTKOS-VA-' . $this->room->id . '-12345';
        $grossAmount = '1000000.00';
        $statusCode = '200';

        $vaPayment = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => $orderId,
            'transaction_id' => 'midtrans-tx-001',
            'amount' => 1000000,
            'transaction_status' => 'pending',
            'transaction_time' => now()->subMinutes(10),
        ]);

        $signature = $this->generateSignature($orderId, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-tx-001',
            'transaction_time' => now()->toDateTimeString(),
            'settlement_time' => now()->toDateTimeString(),
            'payment_type' => 'bank_transfer',
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'order_id' => $orderId,
            'transaction_status' => 'settlement',
        ]);

        $vaPayment->refresh();
        $this->assertSame('settlement', $vaPayment->transaction_status);
        $this->assertNotNull($vaPayment->paid_at);
        $this->assertTrue($vaPayment->isPaid());
        $this->assertTrue($this->assignment->fresh()->isPaidForCurrentPeriod());
    }

    public function test_webhook_with_invalid_signature_is_rejected_with_403(): void
    {
        $orderId = 'SMARTKOS-VA-' . $this->room->id . '-12345';

        $vaPayment = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => $orderId,
            'amount' => 1000000,
            'transaction_status' => 'pending',
        ]);

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '1000000.00',
            'signature_key' => 'invalid-fake-signature',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(403);
        $response->assertJson(['status' => 'error', 'message' => 'Invalid signature.']);

        $vaPayment->refresh();
        $this->assertSame('pending', $vaPayment->transaction_status);
        $this->assertNull($vaPayment->paid_at);
    }

    public function test_webhook_handles_expired_status(): void
    {
        $orderId = 'SMARTKOS-VA-' . $this->room->id . '-12345';
        $grossAmount = '1000000.00';
        $statusCode = '200';

        $vaPayment = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => $orderId,
            'amount' => 1000000,
            'transaction_status' => 'pending',
        ]);

        $signature = $this->generateSignature($orderId, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'transaction_time' => now()->toDateTimeString(),
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(200);
        $vaPayment->refresh();
        $this->assertSame('expired', $vaPayment->transaction_status);
        $this->assertTrue($vaPayment->isExpired());
        $this->assertNull($vaPayment->paid_at);
    }

    public function test_webhook_handles_cancel_and_deny_status(): void
    {
        $orderId = 'SMARTKOS-VA-' . $this->room->id . '-12345';
        $grossAmount = '1000000.00';
        $statusCode = '200';

        $vaPayment = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => $orderId,
            'amount' => 1000000,
            'transaction_status' => 'pending',
        ]);

        $signature = $this->generateSignature($orderId, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'cancel',
            'transaction_time' => now()->toDateTimeString(),
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(200);
        $vaPayment->refresh();
        $this->assertSame('cancelled', $vaPayment->transaction_status);
        $this->assertTrue($vaPayment->isCancelled());
    }

    public function test_webhook_handles_refund_status(): void
    {
        $orderId = 'SMARTKOS-VA-' . $this->room->id . '-12345';
        $grossAmount = '1000000.00';
        $statusCode = '200';

        $vaPayment = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => $orderId,
            'amount' => 1000000,
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $signature = $this->generateSignature($orderId, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'refund',
            'transaction_time' => now()->toDateTimeString(),
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(200);
        $vaPayment->refresh();
        $this->assertSame('refund', $vaPayment->transaction_status);
        $this->assertTrue($vaPayment->isRefund());
    }

    public function test_webhook_creates_va_payment_if_not_present_but_va_account_matches_order_id(): void
    {
        $orderId = 'SMARTKOS-VA-' . $this->room->id . '-12345';
        $grossAmount = '1000000.00';
        $statusCode = '200';

        $signature = $this->generateSignature($orderId, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'tx-auto-created',
            'settlement_time' => now()->toDateTimeString(),
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('va_payments', [
            'order_id' => $orderId,
            'transaction_status' => 'settlement',
            'room_assignment_id' => $this->assignment->id,
        ]);
    }

    public function test_webhook_returns_404_when_order_id_is_unrecognized(): void
    {
        $orderId = 'UNKNOWN-ORDER-9999';
        $grossAmount = '500000.00';
        $statusCode = '200';

        $signature = $this->generateSignature($orderId, $statusCode, $grossAmount);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('midtrans.callback'), $payload);

        $response->assertStatus(404);
        $response->assertJson(['status' => 'error', 'message' => 'Order not found.']);
    }

    public function test_tenant_can_check_va_status_manually_and_settle_payment(): void
    {
        $orderId = 'SMARTKOS-VA-' . $this->room->id . '-12345';

        $vaPayment = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => $orderId,
            'amount' => 1000000,
            'transaction_status' => 'pending',
        ]);

        Http::fake([
            '*/v2/' . $orderId . '/status' => Http::response([
                'status_code' => '200',
                'order_id' => $orderId,
                'transaction_id' => 'tx-midtrans-settled-123',
                'transaction_status' => 'settlement',
                'settlement_time' => now()->toDateTimeString(),
            ], 200),
        ]);

        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.payments.va.check-status'), [
            'order_id' => $orderId,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $vaPayment->refresh();
        $this->assertSame('settlement', $vaPayment->transaction_status);
        $this->assertNotNull($vaPayment->paid_at);
        $this->assertSame('tx-midtrans-settled-123', $vaPayment->transaction_id);
    }

    public function test_va_payment_model_helper_methods_and_scopes(): void
    {
        $settled = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => 'ORDER-SETTLED-1',
            'amount' => 1250000,
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);

        $pending = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => 'ORDER-PENDING-1',
            'amount' => 1250000,
            'transaction_status' => 'pending',
        ]);

        $expired = VAPayment::create([
            'va_account_id' => $this->vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id' => 'ORDER-EXPIRED-1',
            'amount' => 1250000,
            'transaction_status' => 'expired',
        ]);

        $this->assertTrue($settled->isPaid());
        $this->assertFalse($settled->isPending());
        $this->assertSame('Lunas', $settled->statusLabel());
        $this->assertSame('Rp 1.250.000', $settled->formattedAmount());

        $this->assertTrue($pending->isPending());
        $this->assertSame('Menunggu Pembayaran', $pending->statusLabel());

        $this->assertTrue($expired->isExpired());
        $this->assertSame('Kedaluwarsa', $expired->statusLabel());

        $this->assertSame(1, VAPayment::settled()->count());
        $this->assertSame(1, VAPayment::pending()->count());
        $this->assertSame(1, VAPayment::expired()->count());
    }
}
