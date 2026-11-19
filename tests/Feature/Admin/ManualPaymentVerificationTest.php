<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $tenantUser;

    private TenantProfile $tenantProfile;

    private Location $location;

    private Room $room;

    private RoomAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'name'     => 'Administrator',
            'username' => 'admin_test',
            'email'    => 'admin@test.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Member',
            'username' => 'staff_test',
            'email'    => 'staff@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Rian Firmansyah',
            'username' => 'rian_tenant',
            'email'    => 'rian@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone'   => '081299887766',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Menteng Exclusive',
            'address'     => 'Jl. Menteng No. 10',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 102,
            'rent_amount' => 1200000,
            'status'      => 'active',
        ]);

        $this->assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => now()->subDays(10)->toDateString(),
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);
    }

    public function test_guest_staff_penyewa_cannot_access_admin_payments(): void
    {
        // Guest
        $this->get(route('admin.payments.index'))->assertRedirect(route('login'));

        // Staff
        $this->actingAs($this->staff)->get(route('admin.payments.index'))->assertStatus(403);

        // Tenant
        $this->actingAs($this->tenantUser)->get(route('admin.payments.index'))->assertStatus(403);
    }

    public function test_admin_can_view_manual_payments_verification_page(): void
    {
        ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '821099887711',
            'proof_path'          => 'payment-proofs/sample.jpg',
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Pembayaran Manual');
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('Rian Firmansyah');
        $response->assertSee('102');
        $response->assertSee('Kos Menteng Exclusive');
        $response->assertSee('Rp 1.200.000');
        $response->assertSee('Setujui');
        $response->assertSee('Tolak');
    }

    public function test_admin_can_filter_manual_payments_by_status(): void
    {
        $pending = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '111111',
            'proof_path'          => 'payment-proofs/p1.jpg',
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        $approved = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'Mandiri',
            'bank_account_number' => '222222',
            'proof_path'          => 'payment-proofs/p2.jpg',
            'status'              => 'approved',
            'submitted_at'        => now()->subDay(),
            'verified_at'         => now(),
            'verified_by'         => $this->admin->id,
        ]);

        // Filter: pending
        $responsePending = $this->actingAs($this->admin)->get(route('admin.payments.index', ['status' => 'pending']));
        $responsePending->assertStatus(200);
        $responsePending->assertSee('111111');
        $responsePending->assertDontSee('222222');

        // Filter: approved
        $responseApproved = $this->actingAs($this->admin)->get(route('admin.payments.index', ['status' => 'approved']));
        $responseApproved->assertStatus(200);
        $responseApproved->assertSee('222222');
        $responseApproved->assertDontSee('111111');
    }

    public function test_admin_can_approve_pending_manual_payment(): void
    {
        $payment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '821099887711',
            'proof_path'          => 'payment-proofs/sample.jpg',
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        $this->assertFalse($this->assignment->isPaidForCurrentPeriod());

        $response = $this->actingAs($this->admin)->post(route('admin.payments.approve', $payment));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame('approved', $payment->status);
        $this->assertEquals($this->admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);
        $this->assertTrue($payment->isApproved());

        // Room assignment is now recognized as paid for current period
        $this->assertTrue($this->assignment->fresh()->isPaidForCurrentPeriod());
    }

    public function test_admin_cannot_approve_already_verified_payment(): void
    {
        $payment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '821099887711',
            'proof_path'          => 'payment-proofs/sample.jpg',
            'status'              => 'approved',
            'submitted_at'        => now()->subDay(),
            'verified_at'         => now()->subDay(),
            'verified_by'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.approve', $payment));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_reject_pending_manual_payment_with_reason(): void
    {
        $payment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '821099887711',
            'proof_path'          => 'payment-proofs/sample.jpg',
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment), [
            'rejection_reason' => 'Foto bukti transfer buram dan tidak terbaca jelas.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $payment->refresh();
        $this->assertSame('rejected', $payment->status);
        $this->assertSame('Foto bukti transfer buram dan tidak terbaca jelas.', $payment->rejection_reason);
        $this->assertEquals($this->admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);
        $this->assertTrue($payment->isRejected());
    }

    public function test_admin_cannot_reject_without_rejection_reason(): void
    {
        $payment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '821099887711',
            'proof_path'          => 'payment-proofs/sample.jpg',
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment), [
            'rejection_reason' => '',
        ]);

        $response->assertSessionHasErrors(['rejection_reason']);

        $payment->refresh();
        $this->assertSame('pending', $payment->status);
    }

    public function test_tenant_sees_rejection_reason_on_payments_page(): void
    {
        ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '821099887711',
            'proof_path'          => 'payment-proofs/sample.jpg',
            'status'              => 'rejected',
            'rejection_reason'    => 'Nominal tidak sesuai tagihan kamar',
            'submitted_at'        => now(),
            'verified_at'         => now(),
            'verified_by'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Ditolak');
        $response->assertSee('Nominal tidak sesuai tagihan kamar');
    }
}
