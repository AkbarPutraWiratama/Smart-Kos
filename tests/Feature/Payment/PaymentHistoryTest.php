<?php

namespace Tests\Feature\Payment;

use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\MidtransVAAccount;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\VAPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentHistoryTest extends TestCase
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

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->staff = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos History Melati',
            'address'     => 'Jl. History No. 1',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1500000,
            'status'      => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Budi Penyewa',
            'username' => 'budipenyewa',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone'   => '081234567890',
        ]);

        $this->assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => now()->subMonths(3)->toDateString(),
            'status'               => 'active',
            'rent_amount_snapshot' => 1500000,
        ]);
    }

    public function test_payment_history_is_preserved_when_tenant_leaves_or_checkout(): void
    {
        // 1. Create a settled VA Payment and an approved Manual Payment
        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '98765432101',
            'external_id' => 'VA-EXT-001',
            'amount'      => 1500000,
        ]);

        $vaPayment = VAPayment::create([
            'va_account_id'      => $vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id'           => 'VA-HIST-001',
            'amount'             => 1500000,
            'transaction_status' => 'settlement',
            'transaction_time'   => now()->subMonths(2),
            'paid_at'            => now()->subMonths(2),
        ]);

        $manualPayment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'Bank Mandiri',
            'bank_account_number' => '1234567890',
            'proof_path'          => 'proofs/test.jpg',
            'status'              => 'approved',
            'submitted_at'        => now()->subMonth(),
            'verified_at'         => now()->subMonth(),
            'verified_by'         => $this->admin->id,
        ]);

        // 2. Tenant checkout (assignment completed)
        $this->actingAs($this->admin)->post(route('admin.assignments.end', $this->assignment), [
            'password' => 'adminpassword',
        ]);

        $this->assignment->refresh();
        $this->assertSame('completed', $this->assignment->status);
        $this->assertFalse($this->room->isOccupied());

        // 3. Verify payments are preserved and reportable
        $this->assertDatabaseHas('va_payments', [
            'id'       => $vaPayment->id,
            'order_id' => 'VA-HIST-001',
        ]);

        $this->assertDatabaseHas('manual_payments', [
            'id'                  => $manualPayment->id,
            'bank_account_number' => '1234567890',
        ]);

        $this->assertCount(1, $this->assignment->vaPayments);
        $this->assertCount(1, $this->assignment->manualPayments);
    }

    public function test_payment_history_is_preserved_when_tenant_transfers_room(): void
    {
        $newRoom = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 2,
            'room_number' => 202,
            'rent_amount' => 2000000,
            'status'      => 'active',
        ]);

        $oldPayment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'Bank BCA',
            'bank_account_number' => '111222333',
            'proof_path'          => 'proofs/old.jpg',
            'status'              => 'approved',
            'submitted_at'        => now()->subMonths(1),
            'verified_at'         => now()->subMonths(1),
            'verified_by'         => $this->admin->id,
        ]);

        // Transfer room
        $response = $this->actingAs($this->admin)->patch(route('admin.assignments.change-room', $this->assignment), [
            'new_room_id'   => $newRoom->id,
            'transfer_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assignment->refresh();
        $this->assertSame('completed', $this->assignment->status);

        // Verify old payment remains intact
        $this->assertDatabaseHas('manual_payments', [
            'id'                  => $oldPayment->id,
            'room_assignment_id'  => $this->assignment->id,
            'amount'              => 1500000,
        ]);

        // Verify tenant can query historical payments
        $tenantAssignments = $this->tenantProfile->roomAssignments()->pluck('id');
        $this->assertCount(2, $tenantAssignments);
        $this->assertTrue(ManualPayment::whereIn('room_assignment_id', $tenantAssignments)->where('id', $oldPayment->id)->exists());
    }

    public function test_rent_amount_change_does_not_mutate_historical_payment_amounts(): void
    {
        $payment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'Bank BCA',
            'bank_account_number' => '999888777',
            'proof_path'          => 'proofs/rent.jpg',
            'status'              => 'approved',
            'submitted_at'        => now()->subDays(15),
            'verified_at'         => now()->subDays(15),
        ]);

        // Room rent increases
        $this->room->update(['rent_amount' => 2500000]);

        $payment->refresh();
        $this->assignment->refresh();

        // Historical snapshot and payment amount remain at original 1,500,000
        $this->assertEquals(1500000, $this->assignment->rent_amount_snapshot);
        $this->assertEquals(1500000, $payment->amount);
    }

    public function test_empty_room_with_payment_history_cannot_be_deleted(): void
    {
        // Add a payment and then complete assignment (room becomes empty)
        ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'Bank BNI',
            'bank_account_number' => '444555666',
            'proof_path'          => 'proofs/empty.jpg',
            'status'              => 'approved',
            'submitted_at'        => now()->subMonth(),
        ]);

        $this->assignment->update([
            'status'   => 'completed',
            'end_date' => now()->toDateString(),
        ]);

        $this->assertFalse($this->room->isOccupied());
        $this->assertTrue($this->room->hasPaymentHistory());

        // Attempting to delete the room should be blocked to preserve historical records
        $response = $this->actingAs($this->admin)->delete(
            route('admin.locations.rooms.destroy', [$this->location, $this->room]),
            ['password' => 'adminpassword']
        );

        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('rooms', ['id' => $this->room->id]);
    }

    public function test_location_with_rooms_having_payment_history_cannot_be_deleted(): void
    {
        ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'Bank BNI',
            'bank_account_number' => '444555666',
            'proof_path'          => 'proofs/loc.jpg',
            'status'              => 'approved',
            'submitted_at'        => now()->subMonth(),
        ]);

        $this->assignment->update([
            'status'   => 'completed',
            'end_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.locations.destroy', $this->location),
            ['password' => 'adminpassword']
        );

        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('locations', ['id' => $this->location->id]);
    }

    public function test_tenant_can_view_historical_payments_even_when_unassigned(): void
    {
        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '123456789012',
            'external_id' => 'VA-EXT-002',
            'amount'      => 1500000,
        ]);

        VAPayment::create([
            'va_account_id'      => $vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id'           => 'ORDER-HIST-UNASSIGNED',
            'amount'             => 1500000,
            'transaction_status' => 'settlement',
            'transaction_time'   => now()->subMonths(1),
            'paid_at'            => now()->subMonths(1),
        ]);

        // End assignment
        $this->assignment->update([
            'status'   => 'completed',
            'end_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Tagihan Sewa Aktif');
        $response->assertSee('ORDER-HIST-UNASSIGNED');
        $response->assertSee('Kamar 101');
    }

    public function test_admin_can_view_both_manual_and_va_payment_history(): void
    {
        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '123456789013',
            'external_id' => 'VA-EXT-003',
            'amount'      => 1500000,
        ]);

        VAPayment::create([
            'va_account_id'      => $vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id'           => 'VA-ADMIN-TEST',
            'amount'             => 1500000,
            'transaction_status' => 'settlement',
            'transaction_time'   => now(),
            'paid_at'            => now(),
        ]);

        ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'Bank Mandiri',
            'bank_account_number' => '555666777',
            'proof_path'          => 'proofs/admin.jpg',
            'status'              => 'approved',
            'submitted_at'        => now(),
        ]);

        // Manual tab
        $responseManual = $this->actingAs($this->admin)->get(route('admin.payments.index', ['type' => 'manual', 'status' => 'approved']));
        $responseManual->assertStatus(200);
        $responseManual->assertSee('Bank Mandiri');
        $responseManual->assertSee('555666777');

        // VA tab
        $responseVA = $this->actingAs($this->admin)->get(route('admin.payments.index', ['type' => 'va']));
        $responseVA->assertStatus(200);
        $responseVA->assertSee('VA-ADMIN-TEST');
        $responseVA->assertSee('Virtual Account Midtrans');
    }

    public function test_staff_can_view_payment_status_without_seeing_rental_nominal_amounts(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.payments.index'));

        $response->assertStatus(200);
        $response->assertSee('Status Pembayaran Penyewa');
        $response->assertSee('Budi Penyewa');
        $response->assertSee('Kamar 101');
        $response->assertSee('Kos History Melati');

        // Enforce strict staff security invariant: NO nominal rental amounts visible
        $response->assertDontSee('1.500.000');
        $response->assertDontSee('1500000');
        $response->assertDontSee('Rp 1.500.000');
    }

    public function test_unauthorized_roles_cannot_access_staff_or_admin_payments(): void
    {
        // Guest
        $this->get(route('staff.payments.index'))->assertRedirect(route('login'));
        $this->get(route('admin.payments.index'))->assertRedirect(route('login'));

        // Tenant cannot access staff or admin payment pages
        $this->actingAs($this->tenantUser)->get(route('staff.payments.index'))->assertForbidden();
        $this->actingAs($this->tenantUser)->get(route('admin.payments.index'))->assertForbidden();

        // Staff cannot access admin payment verification
        $this->actingAs($this->staff)->get(route('admin.payments.index'))->assertForbidden();
    }
}
