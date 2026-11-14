<?php

namespace Tests\Feature\Tenant;

use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $tenantUser;

    private TenantProfile $tenantProfile;

    private Location $location;

    private Room $room;

    private RoomAssignment $assignment;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name' => 'Dewi Lestari',
            'username' => 'dewilestari',
            'email' => 'dewi@example.com',
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone' => '081298765432',
        ]);

        $this->location = Location::create([
            'name' => 'Kos Sakura Indah',
            'address' => 'Jl. Sakura No. 8',
            'floor_count' => 2,
            'status' => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name' => 2,
            'room_number' => 205,
            'rent_amount' => 1500000,
            'status' => 'active',
        ]);

        $this->assignment = RoomAssignment::create([
            'room_id' => $this->room->id,
            'tenant_id' => $this->tenantProfile->id,
            'start_date' => now()->subMonths(1)->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 1500000,
        ]);
    }

    public function test_tenant_can_view_payment_page_with_active_bill(): void
    {
        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Pembayaran Sewa');
        $response->assertSee('Rp 1.500.000');
        $response->assertSee('205');
        $response->assertSee('Kos Sakura Indah');
        $response->assertSee('Midtrans Virtual Account');
        $response->assertSee('Transfer Manual Bank');
    }

    public function test_destination_banks_are_loaded_from_configuration(): void
    {
        config()->set('payment.manual_banks', [
            [
                'bank' => 'Bank Jago Syariah',
                'account_number' => '5001-9922-8811',
                'account_name' => 'Ibu Kos Jago',
            ],
        ]);

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Bank Jago Syariah');
        $response->assertSee('5001-9922-8811');
        $response->assertSee('Ibu Kos Jago');
    }

    public function test_multiple_modular_destination_banks_can_be_configured(): void
    {
        config()->set('payment.manual_banks', [
            ['bank' => 'Bank A', 'account_number' => '111', 'account_name' => 'Kos A'],
            ['bank' => 'Bank B', 'account_number' => '222', 'account_name' => 'Kos B'],
            ['bank' => 'Bank C', 'account_number' => '333', 'account_name' => 'Kos C'],
            ['bank' => 'Bank D', 'account_number' => '444', 'account_name' => 'Kos D'],
        ]);

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Bank A');
        $response->assertSee('Bank B');
        $response->assertSee('Bank C');
        $response->assertSee('Bank D');
    }

    public function test_unassigned_tenant_sees_fallback_notice(): void
    {
        $unassignedUser = User::factory()->create([
            'name' => 'Budi Baru',
            'role' => 'penyewa',
            'status' => 'active',
        ]);
        TenantProfile::create(['user_id' => $unassignedUser->id]);

        $response = $this->actingAs($unassignedUser)->get(route('penyewa.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Tagihan Sewa Aktif');
        $response->assertDontSee('Midtrans Virtual Account');
    }

    public function test_tenant_can_generate_midtrans_virtual_account(): void
    {
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'status_message' => 'Success, Bank Transfer transaction is created',
                'transaction_id' => 'trx-12345678',
                'order_id' => 'SMARTKOS-VA-'.$this->room->id.'-999',
                'gross_amount' => '1500000.00',
                'payment_type' => 'bank_transfer',
                'transaction_time' => now()->toDateTimeString(),
                'transaction_status' => 'pending',
                'va_numbers' => [
                    [
                        'bank' => 'bca',
                        'va_number' => '12345678901234',
                    ],
                ],
                'expiry_time' => now()->addHours(24)->toDateTimeString(),
            ], 201),
        ]);

        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.payments.va.create'), [
            'bank_code' => 'bca',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('midtrans_va_accounts', [
            'room_id' => $this->room->id,
            'bank_code' => 'bca',
            'va_number' => '12345678901234',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('va_payments', [
            'room_assignment_id' => $this->assignment->id,
            'transaction_status' => 'pending',
            'amount' => 1500000,
        ]);
    }

    public function test_tenant_can_submit_manual_payment(): void
    {
        $file = UploadedFile::fake()->create('struk_transfer.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.payments.manual.store'), [
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '1234567890',
            'amount' => 1500000,
            'proof' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('manual_payments', [
            'room_assignment_id' => $this->assignment->id,
            'submitted_by' => $this->tenantUser->id,
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '1234567890',
            'amount' => 1500000,
            'status' => 'pending',
        ]);

        $payment = ManualPayment::first();
        $this->assertNotNull($payment->proof_path);
        Storage::disk('public')->assertExists($payment->proof_path);
    }

    public function test_tenant_is_rate_limited_to_10_minutes_for_manual_payment(): void
    {
        // First submission
        ManualPayment::create([
            'room_assignment_id' => $this->assignment->id,
            'submitted_by' => $this->tenantUser->id,
            'amount' => 1500000,
            'bank_name' => 'BCA',
            'bank_account_number' => '12345',
            'proof_path' => 'payment-proofs/test.jpg',
            'status' => 'pending',
            'submitted_at' => now()->subMinutes(3), // Submitted 3 minutes ago
        ]);

        // Second submission attempts within 10 minutes
        $file = UploadedFile::fake()->create('struk2.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.payments.manual.store'), [
            'bank_name' => 'Mandiri',
            'bank_account_number' => '67890',
            'amount' => 1500000,
            'proof' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('dibatasi 1 kali setiap 10 menit', session('error'));

        // Ensure second record was NOT created
        $this->assertEquals(1, ManualPayment::count());
    }

    public function test_manual_payment_validation_fails_on_missing_proof(): void
    {
        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.payments.manual.store'), [
            'bank_name' => 'BCA',
            'bank_account_number' => '12345',
            'amount' => 1500000,
        ]);

        $response->assertSessionHasErrors(['proof']);
    }

    public function test_guest_and_staff_cannot_access_tenant_payment_endpoints(): void
    {
        // Guest
        $this->get(route('penyewa.payments.index'))->assertRedirect(route('login'));

        // Staff
        $this->actingAs($this->staff)->get(route('penyewa.payments.index'))->assertStatus(403);
    }
}
