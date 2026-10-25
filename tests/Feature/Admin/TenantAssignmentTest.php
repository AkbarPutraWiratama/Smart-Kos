<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
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

    public function test_admin_can_assign_tenant_to_room(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.assignments.store'), [
            'tenant_id'  => $this->tenantProfile->id,
            'room_id'    => $this->room1->id,
            'start_date' => '2026-10-25',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('room_assignments', [
            'room_id'              => $this->room1->id,
            'tenant_id'            => $this->tenantProfile->id,
            'status'               => 'active',
            'rent_amount_snapshot' => 1200000,
        ]);

        $this->assertTrue($this->room1->fresh()->isOccupied());
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

        $response = $this->actingAs($this->admin)->patch(route('admin.assignments.change-room', $assignment), [
            'new_room_id'   => $this->room2->id,
            'transfer_date' => '2026-10-25',
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
        ]);

        $response->assertSessionHasErrors('new_room_id');
        $this->assertEquals('active', $assignment->fresh()->status);
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

