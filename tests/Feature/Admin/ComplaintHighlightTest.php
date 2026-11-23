<?php

namespace Tests\Feature\Admin;

use App\Models\Complaint;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComplaintHighlightTest extends TestCase
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
            'name'     => 'Admin Kos',
            'username' => 'admin_kos',
            'email'    => 'admin@smartkos.test',
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staf Lapangan',
            'username' => 'staff_lapangan',
            'email'    => 'staff@smartkos.test',
            'role'     => 'staff',
            'status'   => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Penyewa Melati',
            'username' => 'penyewa_melati',
            'email'    => 'penyewa@smartkos.test',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id'         => $this->tenantUser->id,
            'nik'             => '3201012345678903',
            'phone'           => '081234567899',
            'emergency_name'  => 'Kontak',
            'emergency_phone' => '089876543219',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Cempaka Asri',
            'address'     => 'Jl. Cempaka No. 15',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 103,
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
            'title'        => 'Kebocoran Pipa Utama Kamar Mandi',
            'description'  => 'Pipa utama bocor dan air merembes ke lorong.',
            'status'       => 'belum',
            'highlighted'  => false,
            'submitted_at' => now()->subHours(2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Admin Highlight Actions
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_highlight_a_complaint(): void
    {
        $this->assertFalse($this->complaint->highlighted);

        $response = $this->actingAs($this->admin)->patch(route('admin.complaints.highlight', $this->complaint));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'id'          => $this->complaint->id,
            'highlighted' => true,
        ]);
        $this->assertTrue($this->complaint->fresh()->highlighted);
    }

    /** @test */
    public function test_admin_can_unhighlight_a_complaint(): void
    {
        $this->complaint->update(['highlighted' => true]);

        $response = $this->actingAs($this->admin)->patch(route('admin.complaints.highlight', $this->complaint));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'id'          => $this->complaint->id,
            'highlighted' => false,
        ]);
        $this->assertFalse($this->complaint->fresh()->highlighted);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Authorization Security
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_guest_cannot_toggle_complaint_highlight(): void
    {
        $response = $this->patch(route('admin.complaints.highlight', $this->complaint));
        $response->assertRedirect(route('login'));
        $this->assertFalse($this->complaint->fresh()->highlighted);
    }

    /** @test */
    public function test_staff_cannot_toggle_complaint_highlight(): void
    {
        $response = $this->actingAs($this->staff)->patch(route('admin.complaints.highlight', $this->complaint));
        $response->assertStatus(403);
        $this->assertFalse($this->complaint->fresh()->highlighted);
    }

    /** @test */
    public function test_tenant_cannot_toggle_complaint_highlight(): void
    {
        $response = $this->actingAs($this->tenantUser)->patch(route('admin.complaints.highlight', $this->complaint));
        $response->assertStatus(403);
        $this->assertFalse($this->complaint->fresh()->highlighted);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Staff Interface Noticeability & Ordering
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_highlighted_complaint_is_visually_noticeable_in_staff_interface(): void
    {
        $this->complaint->update(['highlighted' => true]);

        $response = $this->actingAs($this->staff)->get(route('staff.complaints.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Prioritas Admin');
        $response->assertSeeText('Aduan Prioritas dari Admin');
        $response->assertSeeText('Kebocoran Pipa Utama Kamar Mandi');
    }

    /** @test */
    public function test_highlighted_complaints_are_sorted_to_the_top_for_staff(): void
    {
        // Older complaint with highlight = true
        $urgentComplaint = Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Darurat: Plafon Jebol',
            'description'  => 'Plafon runtuh',
            'status'       => 'belum',
            'highlighted'  => true,
            'submitted_at' => now()->subHours(5),
        ]);

        // Newer complaint with highlight = false
        $normalComplaint = Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Biasa: Gagang Lemari Longgar',
            'description'  => 'Gagang lemari goyang',
            'status'       => 'belum',
            'highlighted'  => false,
            'submitted_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->staff)->get(route('staff.complaints.index'));

        $response->assertStatus(200);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $complaints */
        $complaints = $response->viewData('complaints');

        // First item must be the highlighted one
        $this->assertTrue($complaints->first()->highlighted);
        $this->assertEquals('Darurat: Plafon Jebol', $complaints->first()->title);
    }

    /** @test */
    public function test_staff_can_filter_specifically_by_highlighted_complaints(): void
    {
        $this->complaint->update(['highlighted' => true]);

        Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Aduan Biasa Tanpa Highlight',
            'description'  => 'Deskripsi biasa',
            'status'       => 'belum',
            'highlighted'  => false,
            'submitted_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->staff)->get(route('staff.complaints.index', ['highlighted' => '1']));

        $response->assertStatus(200);
        $response->assertSeeText('Kebocoran Pipa Utama Kamar Mandi');
        $response->assertDontSeeText('Aduan Biasa Tanpa Highlight');
    }

    /** @test */
    public function test_admin_can_filter_specifically_by_highlighted_complaints(): void
    {
        $this->complaint->update(['highlighted' => true]);

        Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Aduan Biasa Untuk Admin',
            'description'  => 'Deskripsi biasa',
            'status'       => 'belum',
            'highlighted'  => false,
            'submitted_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.complaints.index', ['highlighted' => '1']));

        $response->assertStatus(200);
        $response->assertSeeText('Kebocoran Pipa Utama Kamar Mandi');
        $response->assertDontSeeText('Aduan Biasa Untuk Admin');
    }

    /** @test */
    public function test_highlight_and_alert_automatically_clears_when_complaint_is_resolved(): void
    {
        // 1. Complaint is highlighted
        $this->complaint->update(['highlighted' => true]);

        // 2. Staff sees the alert banner
        $staffView = $this->actingAs($this->staff)->get(route('staff.complaints.index'));
        $staffView->assertSeeText('Aduan Prioritas dari Admin');

        // 3. Staff resolves the complaint
        $actionResponse = $this->actingAs($this->staff)->post(route('staff.complaints.actions.store', $this->complaint), [
            'action_description' => 'Pipa utama telah diganti pipa PVC baru dan kebocoran tuntas.',
            'status'             => 'selesai',
            'confirm_selesai'    => 1,
        ]);
        $actionResponse->assertRedirect(route('staff.complaints.index'));

        // 4. In database, complaint is resolved and highlight is cleared
        $this->complaint->refresh();
        $this->assertEquals('selesai', $this->complaint->status);
        $this->assertFalse($this->complaint->highlighted);

        // 5. Staff page no longer shows the alert banner
        $staffViewAfter = $this->actingAs($this->staff)->get(route('staff.complaints.index'));
        $staffViewAfter->assertDontSeeText('Aduan Prioritas dari Admin');
    }
}
