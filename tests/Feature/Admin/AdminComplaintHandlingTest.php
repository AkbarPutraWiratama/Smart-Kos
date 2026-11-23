<?php

namespace Tests\Feature\Admin;

use App\Models\Complaint;
use App\Models\ComplaintAction;
use App\Models\Expense;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminComplaintHandlingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $tenantUser;
    private TenantProfile $tenantProfile;
    private Location $location;
    private Room $room;
    private Complaint $complaint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Admin Utama',
            'username' => 'admin_utama',
            'email'    => 'admin.utama@test.com',
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staf Lapangan',
            'username' => 'staff_lapangan',
            'email'    => 'staff.lapangan@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Penyewa Cempaka',
            'username' => 'penyewa_cempaka',
            'email'    => 'penyewa.cempaka@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id'         => $this->tenantUser->id,
            'nik'             => '3201012345678902',
            'phone'           => '081234567891',
            'emergency_name'  => 'Kontak',
            'emergency_phone' => '089876543211',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Cempaka',
            'address'     => 'Jl. Cempaka No. 12',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 102,
            'rent_amount' => 1500000,
            'status'      => 'active',
        ]);

        RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'status'               => 'active',
            'start_date'           => now()->subMonths(1)->toDateString(),
            'rent_amount_snapshot' => 1500000,
        ]);

        $this->complaint = Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Saklar Lampu Kamar Korslet',
            'description'  => 'Kadang timbul percikan api saat dinyalakan.',
            'status'       => 'belum',
            'highlighted'  => false,
            'submitted_at' => now()->subHours(2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Access Control & Page Viewing
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_view_complaints_management_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.complaints.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Tindakan Aduan');
        $response->assertSeeText('Saklar Lampu Kamar Korslet');
        $response->assertSeeText('Penyewa Cempaka');
        $response->assertSeeText('Kamar #102');
    }

    /** @test */
    public function test_staff_cannot_access_admin_complaints_page(): void
    {
        $this->actingAs($this->staff)->get(route('admin.complaints.index'))->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Admin Action Handling & Confirmation
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_record_action_and_update_status(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.complaints.actions.store', $this->complaint), [
            'action_description' => 'Admin telah memanggil teknisi PLN / kelistrikan.',
            'status'             => 'dalam_perbaikan',
        ]);

        $response->assertRedirect(route('admin.complaints.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'id'     => $this->complaint->id,
            'status' => 'dalam_perbaikan',
        ]);

        $this->assertDatabaseHas('complaint_actions', [
            'complaint_id'       => $this->complaint->id,
            'staff_id'           => $this->admin->id,
            'action_description' => 'Admin telah memanggil teknisi PLN / kelistrikan.',
            'status'             => 'dalam_perbaikan',
        ]);
    }

    /** @test */
    public function test_admin_changing_status_to_selesai_requires_confirmation(): void
    {
        // Without confirmation fails
        $response = $this->actingAs($this->admin)->post(route('admin.complaints.actions.store', $this->complaint), [
            'action_description' => 'Saklar sudah diganti',
            'status'             => 'selesai',
        ]);

        $response->assertSessionHasErrors(['confirm_selesai']);

        // With confirmation succeeds
        $successResponse = $this->actingAs($this->admin)->post(route('admin.complaints.actions.store', $this->complaint), [
            'action_description' => 'Saklar broco baru sudah dipasang dan dites aman.',
            'status'             => 'selesai',
            'confirm_selesai'    => 1,
            'cost'               => 45000,
        ]);

        $successResponse->assertRedirect(route('admin.complaints.index'));
        $successResponse->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'id'     => $this->complaint->id,
            'status' => 'selesai',
        ]);

        $this->assertDatabaseHas('expenses', [
            'location_id' => $this->location->id,
            'recorded_by' => $this->admin->id,
            'amount'      => 45000,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Highlighting Feature (Phase 37 ready)
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_toggle_complaint_highlight(): void
    {
        $this->assertFalse($this->complaint->highlighted);

        // Turn highlight ON
        $responseOn = $this->actingAs($this->admin)->patch(route('admin.complaints.highlight', $this->complaint));
        $responseOn->assertRedirect();
        $this->assertTrue($this->complaint->fresh()->highlighted);

        // Turn highlight OFF
        $responseOff = $this->actingAs($this->admin)->patch(route('admin.complaints.highlight', $this->complaint));
        $responseOff->assertRedirect();
        $this->assertFalse($this->complaint->fresh()->highlighted);
    }

    /** @test */
    public function test_staff_cannot_toggle_complaint_highlight(): void
    {
        $this->actingAs($this->staff)->patch(route('admin.complaints.highlight', $this->complaint))
            ->assertStatus(403);
    }
}
