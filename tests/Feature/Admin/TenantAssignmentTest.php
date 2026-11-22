<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Location $location;
    private Room $room1;
    private Room $room2;
    private User $tenantUser;
    private TenantProfile $tenantProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Melati Indah',
            'address'     => 'Jl. Melati No. 1',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room1 = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1200000,
            'status'      => 'active',
        ]);

        $this->room2 = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 102,
            'rent_amount' => 1300000,
            'status'      => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Budi Penyet',
            'username' => 'budipenyet',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone'   => '081234567890',
        ]);
    }

    public function test_admin_can_view_tenants_status_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.tenants.index', [
            'location_id' => $this->location->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Status Penyewa');
        $response->assertSee('Kos Melati Indah');
    }

    public function test_admin_can_view_and_switch_locations_via_side_scrolling_cards(): void
    {
        $secondLocation = Location::create([
            'name'        => 'Kos Mawar Residence',
            'address'     => 'Jl. Mawar Raya No. 45',
            'floor_count' => 3,
            'status'      => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.tenants.index', [
            'location_id' => $this->location->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kos Melati Indah');
        $response->assertSee('Kos Mawar Residence');
        $response->assertSee('Lokasi Terpilih');
        $response->assertSee('Klik untuk pilih lokasi');
    }

    public function test_admin_can_assign_tenant_to_room(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.assignments.store'), [
            'tenant_id'  => $this->tenantProfile->id,
            'room_id'    => $this->room1->id,
            'start_date' => '2026-10-25',
            'end_date'   => '2026-11-25',
            'payment_cycle' => 'monthly',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('room_assignments', [
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
            'payment_cycle'        => 'monthly',
            'billing_due_day'      => 25,
        ]);

        $assignment = RoomAssignment::where('room_id', $this->room1->id)
            ->where('tenant_id', $this->tenantProfile->id)
            ->where('status', 'active')
            ->first();

        $this->assertSame('2026-10-25', $assignment->next_due_date->toDateString());

        $this->assertTrue($this->room1->fresh()->isOccupied());
    }

    public function test_admin_can_update_current_and_future_room_price_from_status_page(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $response = $this->actingAs($this->admin)->patch(
            route('admin.assignments.update-price', $assignment),
            [
                'rent_amount' => 1450000,
                'payment_cycle' => 'yearly',
                'start_date' => '2026-10-25',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame('1450000.00', $assignment->fresh()->rent_amount_snapshot);
        $this->assertSame('1450000.00', $this->room1->fresh()->rent_amount);
        $this->assertSame('yearly', $assignment->fresh()->payment_cycle);
        $this->assertSame('2026-10-25', $assignment->fresh()->start_date->toDateString());
        $this->assertSame('2027-10-25', $assignment->fresh()->end_date->toDateString());
    }

    public function test_non_admin_cannot_update_assignment_price(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $response = $this->actingAs($this->tenantUser)->patch(
            route('admin.assignments.update-price', $assignment),
            ['rent_amount' => 1450000]
        );

        $response->assertStatus(403);
        $this->assertSame('1200000.00', $assignment->fresh()->rent_amount_snapshot);
        $this->assertSame('1200000.00', $this->room1->fresh()->rent_amount);
    }

    public function test_status_page_shows_assignment_dates_prices_and_payment_history(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'end_date'             => '2026-12-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1250000,
        ]);

        ManualPayment::create([
            'room_assignment_id'  => $assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1250000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '123456',
            'proof_path'           => 'payment-proofs/proof.jpg',
            'status'               => 'pending',
            'submitted_at'         => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.tenants.index', [
            'location_id' => $this->location->id,
            'tab' => 'assigned',
        ]));

        $response->assertStatus(200);
        $response->assertSee('1250000');
        $response->assertSee('2026-10-01');
        $response->assertSee('2026-12-01');
        $response->assertSee('approve');
        $response->assertSee('reject');
        $response->assertSee('Konfirmasi Setujui Pembayaran');
        $response->assertSee('Konfirmasi Tolak Pembayaran');
        $response->assertSee('Alasan Penolakan');
        $response->assertSee('Edit Kamar');
        $response->assertSee('Simpan Perubahan');
        $response->assertSee('Pindah Kamar');
        $response->assertSee('Akhiri Masa Sewa');
        $response->assertSee('Konfirmasi Simpan Perubahan');
        $response->assertDontSee('backdrop-blur-sm');
        $response->assertSee('Rentang Pembayaran');
        $response->assertSee('Bulanan');
        $response->assertSee('Tahunan');
        $response->assertSee('Tanggal Mulai');
        $response->assertSee('Tanggal Berakhir');
    }

    public function test_assignment_price_requires_a_positive_amount(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $response = $this->actingAs($this->admin)->patch(
            route('admin.assignments.update-price', $assignment),
            ['rent_amount' => 0]
        );

        $response->assertSessionHasErrors('rent_amount');
        $this->assertSame('1200000.00', $assignment->fresh()->rent_amount_snapshot);
    }

    public function test_monthly_assignment_end_date_is_locked_to_one_month(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.assignments.store'), [
            'tenant_id'     => $this->tenantProfile->id,
            'room_id'       => $this->room1->id,
            'start_date'    => '2026-01-31',
            'end_date'      => '2026-02-28',
            'payment_cycle' => 'monthly',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment = RoomAssignment::where('room_id', $this->room1->id)
            ->where('tenant_id', $this->tenantProfile->id)
            ->where('status', 'active')
            ->first();

        $this->assertSame('2026-02-28', $assignment->end_date->toDateString());
    }

    public function test_yearly_assignment_end_date_is_locked_to_one_year(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.assignments.store'), [
            'tenant_id'     => $this->tenantProfile->id,
            'room_id'       => $this->room1->id,
            'start_date'    => '2026-02-28',
            'end_date'      => '2027-02-28',
            'payment_cycle' => 'yearly',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment = RoomAssignment::where('room_id', $this->room1->id)
            ->where('tenant_id', $this->tenantProfile->id)
            ->where('status', 'active')
            ->first();

        $this->assertSame('2027-02-28', $assignment->end_date->toDateString());
    }

    public function test_assignment_rejects_unmatched_locked_end_date(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.assignments.store'), [
            'tenant_id'     => $this->tenantProfile->id,
            'room_id'       => $this->room1->id,
            'start_date'    => '2026-01-31',
            'end_date'      => '2026-03-31',
            'payment_cycle' => 'monthly',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_monthly_due_date_clamps_short_months(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-01-31',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
            'payment_cycle'        => 'monthly',
            'billing_due_day'      => 31,
            'next_due_date'        => '2026-01-31',
        ]);

        \Carbon\Carbon::setTestNow('2026-02-15');

        $this->assertSame('2026-02-28', $assignment->getPaymentDueDate()->toDateString());

        \Carbon\Carbon::setTestNow();
    }

    public function test_yearly_due_date_keeps_month_and_clamps_leap_day(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2024-02-29',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
            'payment_cycle'        => 'yearly',
            'billing_due_day'      => 29,
            'next_due_date'        => '2024-02-29',
        ]);

        \Carbon\Carbon::setTestNow('2025-01-15');

        $this->assertSame('2025-02-28', $assignment->getPaymentDueDate()->toDateString());

        \Carbon\Carbon::setTestNow();
    }

    public function test_cannot_assign_tenant_to_already_occupied_room(): void
    {
        // First assignment
        RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        // Second tenant
        $secondUser = User::factory()->create(['role' => 'penyewa', 'status' => 'active']);
        $secondProfile = TenantProfile::create(['user_id' => $secondUser->id]);

        $response = $this->actingAs($this->admin)->post(route('admin.assignments.store'), [
            'tenant_id'  => $secondProfile->id,
            'room_id'    => $this->room1->id,
            'start_date' => '2026-10-25',
            'end_date'   => '2026-11-25',
            'payment_cycle' => 'monthly',
        ]);

        $response->assertSessionHasErrors('room_id');
        $this->assertEquals(1, RoomAssignment::where('room_id', $this->room1->id)->where('status', 'active')->count());
    }

    public function test_admin_can_change_tenant_room(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $manualPayment = ManualPayment::create([
            'room_assignment_id'  => $assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '123456789',
            'proof_path'          => 'payment-proofs/move-test.jpg',
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.assignments.change-room', $assignment), [
            'new_room_id'   => $this->room2->id,
            'transfer_date' => '2026-10-25',
            'password'      => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Old assignment completed
        $this->assertEquals('completed', $assignment->fresh()->status);
        $this->assertFalse($this->room1->fresh()->isOccupied());

        // New assignment created and active
        $this->assertDatabaseHas('room_assignments', [
            'room_id'              => $this->room2->id,
            'tenant_id'            => $this->tenantProfile->id,
            'status'               => 'active',
            'rent_amount_snapshot' => 1300000,
        ]);
        $this->assertTrue($this->room2->fresh()->isOccupied());

        $newAssignment = RoomAssignment::where('room_id', $this->room2->id)
            ->where('tenant_id', $this->tenantProfile->id)
            ->where('status', 'active')
            ->first();

        $this->assertEquals($newAssignment->id, $manualPayment->fresh()->room_assignment_id);
    }

    public function test_cannot_change_room_to_already_occupied_room(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        // Room 2 is already occupied by someone else
        $otherUser = User::factory()->create(['role' => 'penyewa', 'status' => 'active']);
        $otherProfile = TenantProfile::create(['user_id' => $otherUser->id]);
        RoomAssignment::create([
            'room_id'              => $this->room2->id,
            'tenant_id'            => $otherProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1300000,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.assignments.change-room', $assignment), [
            'new_room_id'   => $this->room2->id,
            'transfer_date' => '2026-10-25',
            'password'      => 'adminpassword',
        ]);

        $response->assertSessionHasErrors('new_room_id');
        $this->assertEquals('active', $assignment->fresh()->status);
    }

    public function test_change_room_requires_valid_admin_password(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.assignments.change-room', $assignment), [
            'new_room_id'   => $this->room2->id,
            'transfer_date' => '2026-10-25',
            'password'      => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('active', $assignment->fresh()->status);
        $this->assertTrue($this->room1->fresh()->isOccupied());
        $this->assertFalse($this->room2->fresh()->isOccupied());
    }

    public function test_admin_can_end_room_assignment_with_password(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.assignments.end', $assignment), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('completed', $assignment->fresh()->status);
        $this->assertNotNull($assignment->fresh()->deactivated_at);
        $this->assertFalse($this->room1->fresh()->isOccupied());
    }

    public function test_end_assignment_fails_with_invalid_password(): void
    {
        $assignment = RoomAssignment::create([
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => '2026-10-01',
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.assignments.end', $assignment), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('active', $assignment->fresh()->status);
        $this->assertTrue($this->room1->fresh()->isOccupied());
    }
}

