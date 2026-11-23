<?php

namespace Tests\Feature\Staff;

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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffComplaintHandlingTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $admin;
    private User $tenantUser;
    private TenantProfile $tenantProfile;
    private Location $location;
    private Room $room;
    private Complaint $complaint;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->staff = User::factory()->create([
            'name'     => 'Staf Lapangan Kos',
            'username' => 'staff_lapangan',
            'email'    => 'staff.lapangan@test.com',
            'role'     => 'staff',
            'status'   => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->admin = User::factory()->create([
            'name'     => 'Admin Kos',
            'username' => 'admin_kos',
            'email'    => 'admin@test.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Penyewa Melati',
            'username' => 'penyewa_melati',
            'email'    => 'penyewa.melati@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id'         => $this->tenantUser->id,
            'nik'             => '3201012345678901',
            'phone'           => '081234567890',
            'emergency_name'  => 'Kontak Darurat',
            'emergency_phone' => '089876543210',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Cempaka Indah',
            'address'     => 'Jl. Cempaka No. 10',
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

        RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'status'               => 'active',
            'start_date'           => now()->subMonths(1)->toDateString(),
            'billing_due_day'      => 15,
            'rent_amount_snapshot' => 1500000,
        ]);

        $this->complaint = Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Keran Air Kamar Mandi Bocor',
            'description'  => 'Air menetes deras dan tidak bisa ditutup rapat.',
            'status'       => 'belum',
            'highlighted'  => false,
            'submitted_at' => now()->subHours(3),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Access Control
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_guest_cannot_access_staff_complaints(): void
    {
        $this->get(route('staff.complaints.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function test_tenant_cannot_access_staff_complaints(): void
    {
        $this->actingAs($this->tenantUser)->get(route('staff.complaints.index'))->assertStatus(403);
    }

    /** @test */
    public function test_staff_can_view_complaints_handling_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.complaints.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Tindakan Aduan');
        $response->assertSeeText('Keran Air Kamar Mandi Bocor');
        $response->assertSeeText('Penyewa Melati');
        $response->assertSeeText('Kamar #101');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Filtering & Search
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_staff_can_filter_complaints_by_status(): void
    {
        Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Lampu Kamar Selesai Diperbaiki',
            'description'  => 'Lampu sudah diganti',
            'status'       => 'selesai',
            'submitted_at' => now()->subDay(),
        ]);

        // Filter status = belum
        $response = $this->actingAs($this->staff)->get(route('staff.complaints.index', ['status' => 'belum']));
        $response->assertSeeText('Keran Air Kamar Mandi Bocor');
        $response->assertDontSeeText('Lampu Kamar Selesai Diperbaiki');

        // Filter status = selesai
        $responseSelesai = $this->actingAs($this->staff)->get(route('staff.complaints.index', ['status' => 'selesai']));
        $responseSelesai->assertSeeText('Lampu Kamar Selesai Diperbaiki');
        $responseSelesai->assertDontSeeText('Keran Air Kamar Mandi Bocor');
    }

    /** @test */
    public function test_staff_can_filter_complaints_by_location(): void
    {
        $otherLocation = Location::create([
            'name'        => 'Kos Kenanga',
            'address'     => 'Jl. Kenanga No. 20',
            'floor_count' => 1,
            'status'      => 'active',
        ]);
        $otherRoom = Room::create([
            'location_id' => $otherLocation->id,
            'floor_name'  => 1,
            'room_number' => 202,
            'rent_amount' => 1200000,
            'status'      => 'active',
        ]);
        $otherUser = User::factory()->create(['role' => 'penyewa', 'status' => 'active']);
        $otherProfile = TenantProfile::create([
            'user_id' => $otherUser->id,
            'nik'     => '3201099999999991',
            'phone'   => '081299990000',
        ]);
        RoomAssignment::create([
            'room_id'              => $otherRoom->id,
            'tenant_id'            => $otherProfile->id,
            'status'               => 'active',
            'start_date'           => now()->subMonths(1)->toDateString(),
            'rent_amount_snapshot' => 1200000,
        ]);
        Complaint::create([
            'tenant_id'    => $otherProfile->id,
            'title'        => 'AC Rusak di Kos Kenanga',
            'description'  => 'AC mati total',
            'status'       => 'belum',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('staff.complaints.index', ['location_id' => $this->location->id]));
        $response->assertSeeText('Keran Air Kamar Mandi Bocor');
        $response->assertDontSeeText('AC Rusak di Kos Kenanga');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Complaint Action Handling
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_staff_can_record_action_updating_status_to_dalam_perbaikan(): void
    {
        $response = $this->actingAs($this->staff)->post(route('staff.complaints.actions.store', $this->complaint), [
            'action_description' => 'Petugas sedang menuju lokasi untuk membongkar keran air.',
            'status'             => 'dalam_perbaikan',
        ]);

        $response->assertRedirect(route('staff.complaints.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'id'     => $this->complaint->id,
            'status' => 'dalam_perbaikan',
        ]);

        $this->assertDatabaseHas('complaint_actions', [
            'complaint_id'       => $this->complaint->id,
            'staff_id'           => $this->staff->id,
            'action_description' => 'Petugas sedang menuju lokasi untuk membongkar keran air.',
            'status'             => 'dalam_perbaikan',
        ]);
    }

    /** @test */
    public function test_staff_can_record_action_with_optional_image(): void
    {
        $image = UploadedFile::fake()->image('perbaikan.jpg', 600, 600);

        $response = $this->actingAs($this->staff)->post(route('staff.complaints.actions.store', $this->complaint), [
            'action_description' => 'Pipa telah dilepas dan diganti klep.',
            'status'             => 'dalam_perbaikan',
            'image'              => $image,
        ]);

        $response->assertRedirect(route('staff.complaints.index'));

        $action = ComplaintAction::where('complaint_id', $this->complaint->id)->first();
        $this->assertNotNull($action);
        $this->assertNotNull($action->image_path);
        Storage::disk('public')->assertExists($action->image_path);
    }

    /** @test */
    public function test_changing_status_to_selesai_requires_confirmation(): void
    {
        // Attempting to change to 'selesai' without confirm_selesai must fail validation
        $response = $this->actingAs($this->staff)->post(route('staff.complaints.actions.store', $this->complaint), [
            'action_description' => 'Keran sudah diperbaiki.',
            'status'             => 'selesai',
        ]);

        $response->assertSessionHasErrors(['confirm_selesai']);
        $this->assertDatabaseHas('complaints', [
            'id'     => $this->complaint->id,
            'status' => 'belum', // not changed!
        ]);

        // Attempting with confirm_selesai = 1 succeeds
        $successResponse = $this->actingAs($this->staff)->post(route('staff.complaints.actions.store', $this->complaint), [
            'action_description' => 'Keran sudah diganti dan berfungsi sempurna.',
            'status'             => 'selesai',
            'confirm_selesai'    => 1,
        ]);

        $successResponse->assertRedirect(route('staff.complaints.index'));
        $successResponse->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'id'     => $this->complaint->id,
            'status' => 'selesai',
        ]);

        $action = ComplaintAction::where('complaint_id', $this->complaint->id)->first();
        $this->assertNotNull($action->completed_at);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Financial Module Integration (Repair Expense)
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_staff_action_with_repair_cost_records_expense_and_financial_record(): void
    {
        $response = $this->actingAs($this->staff)->post(route('staff.complaints.actions.store', $this->complaint), [
            'action_description' => 'Beli kran wasser baru dan seal tape di toko bangunan.',
            'status'             => 'selesai',
            'confirm_selesai'    => 1,
            'cost'               => 75000,
        ]);

        $response->assertRedirect(route('staff.complaints.index'));

        // Verify Expense record is created
        $this->assertDatabaseHas('expenses', [
            'location_id' => $this->location->id,
            'recorded_by' => $this->staff->id,
            'amount'      => 75000,
        ]);

        // Verify FinancialRecord is created as expense
        $this->assertDatabaseHas('financial_records', [
            'location_id'    => $this->location->id,
            'recorded_by'    => $this->staff->id,
            'type'           => 'expense',
            'reference_type' => 'expense',
            'amount'         => 75000,
        ]);
    }
}
