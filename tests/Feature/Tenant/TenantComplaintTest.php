<?php

namespace Tests\Feature\Tenant;

use App\Models\Complaint;
use App\Models\ComplaintAction;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantComplaintTest extends TestCase
{
    use RefreshDatabase;

    private User $tenantUser;
    private TenantProfile $tenantProfile;
    private User $admin;
    private User $staff;
    private Location $location;
    private Room $room;
    private RoomAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->tenantUser = User::factory()->create([
            'name'     => 'Penyewa Melati',
            'username' => 'penyewa_melati',
            'email'    => 'penyewa.melati@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id'         => $this->tenantUser->id,
            'nik'             => '3201012345678901',
            'phone'           => '081234567890',
            'emergency_name'  => 'Kontak Darurat',
            'emergency_phone' => '089876543210',
        ]);

        $this->admin = User::factory()->create([
            'name'     => 'Admin Kos',
            'username' => 'admin_kos',
            'email'    => 'admin@test.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staf Lapangan',
            'username' => 'staf_lapangan',
            'email'    => 'staff@test.com',
            'role'     => 'staff',
            'status'   => 'active',
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

        $this->assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'status'               => 'active',
            'start_date'           => now()->subMonths(1)->toDateString(),
            'billing_due_day'      => 15,
            'rent_amount_snapshot' => 1500000,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Access Control
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_guest_cannot_access_tenant_complaints_page(): void
    {
        $response = $this->get(route('penyewa.complaints.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_guest_cannot_submit_tenant_complaint(): void
    {
        $response = $this->post(route('penyewa.complaints.store'), [
            'title'       => 'Keran bocor',
            'description' => 'Keran air bocor terus',
        ]);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_staff_and_admin_cannot_access_tenant_complaints_page(): void
    {
        $this->actingAs($this->staff)->get(route('penyewa.complaints.index'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('penyewa.complaints.index'))->assertStatus(403);
    }

    /** @test */
    public function test_staff_and_admin_cannot_submit_to_tenant_complaints_store(): void
    {
        $this->actingAs($this->staff)->post(route('penyewa.complaints.store'), [
            'title'       => 'Tes',
            'description' => 'Deskripsi',
        ])->assertStatus(403);

        $this->actingAs($this->admin)->post(route('penyewa.complaints.store'), [
            'title'       => 'Tes',
            'description' => 'Deskripsi',
        ])->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Page Viewing
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_tenant_can_view_complaints_page(): void
    {
        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.complaints.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Aduan Fasilitas');
        $response->assertSeeText('Kirim Aduan Baru');
        $response->assertSeeText('Riwayat Aduan Saya');
        $response->assertSeeText('Kos Cempaka Indah');
        $response->assertSeeText('Kamar #101');
    }

    /** @test */
    public function test_tenant_without_active_assignment_can_still_view_page(): void
    {
        $unassignedUser = User::factory()->create([
            'role'   => 'penyewa',
            'status' => 'active',
        ]);
        TenantProfile::create([
            'user_id'         => $unassignedUser->id,
            'nik'             => '3201098765432109',
            'phone'           => '081299998888',
            'emergency_name'  => 'Kontak',
            'emergency_phone' => '081299997777',
        ]);

        $response = $this->actingAs($unassignedUser)->get(route('penyewa.complaints.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Aduan Fasilitas');
        $response->assertSeeText('Anda belum terhubung ke kamar aktif saat ini');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Complaint Submission
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_tenant_can_submit_complaint_successfully(): void
    {
        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => 'Lampu Kamar Mandi Mati',
            'description' => 'Lampu kamar mandi mati sejak semalam, mohon diganti bohlam baru.',
        ]);

        $response->assertRedirect(route('penyewa.complaints.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('complaints', [
            'tenant_id'   => $this->tenantProfile->id,
            'title'       => 'Lampu Kamar Mandi Mati',
            'description' => 'Lampu kamar mandi mati sejak semalam, mohon diganti bohlam baru.',
            'status'      => 'belum',
            'highlighted' => false,
        ]);
    }

    /** @test */
    public function test_tenant_can_submit_complaint_with_optional_image(): void
    {
        $image = UploadedFile::fake()->image('keran-bocor.jpg', 600, 600);

        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => 'Pipa Wastafel Bocor',
            'description' => 'Air menetes cukup deras di bawah wastafel.',
            'image'       => $image,
        ]);

        $response->assertRedirect(route('penyewa.complaints.index'));
        $response->assertSessionHas('success');

        $complaint = Complaint::where('title', 'Pipa Wastafel Bocor')->first();
        $this->assertNotNull($complaint);
        $this->assertNotNull($complaint->image_path);
        Storage::disk('public')->assertExists($complaint->image_path);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Validation
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_complaint_submission_requires_title_and_description(): void
    {
        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => '',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'description']);
        $this->assertDatabaseCount('complaints', 0);
    }

    /** @test */
    public function test_complaint_submission_validates_image_file_type(): void
    {
        $pdfFile = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => 'AC Bocor',
            'description' => 'AC meneteskan air',
            'image'       => $pdfFile,
        ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseCount('complaints', 0);
    }

    /** @test */
    public function test_complaint_submission_rejects_image_larger_than_4mb(): void
    {
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(5000); // 5MB

        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => 'Pintu Rusak',
            'description' => 'Engsel pintu lepas',
            'image'       => $largeImage,
        ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseCount('complaints', 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  10-Minute Rate Limit
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_tenant_is_rate_limited_to_one_submission_every_10_minutes(): void
    {
        // First submission succeeds
        $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => 'Aduan Pertama',
            'description' => 'Deskripsi aduan pertama.',
        ])->assertRedirect(route('penyewa.complaints.index'));

        $this->assertDatabaseCount('complaints', 1);

        // Immediate second submission must be rejected by rate limiting
        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => 'Aduan Kedua',
            'description' => 'Deskripsi aduan kedua langsung dikirim.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('complaints', 1); // still 1

        // Verify rate limited notice appears on the index view
        $viewResponse = $this->actingAs($this->tenantUser)->get(route('penyewa.complaints.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSeeText('Batas Pengiriman Aktif');
        $viewResponse->assertSeeText('1 kali setiap 10 menit');
    }

    /** @test */
    public function test_tenant_can_submit_again_after_10_minute_cooldown(): void
    {
        // Create an older complaint from 11 minutes ago
        Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Aduan 11 Menit Lalu',
            'description'  => 'Sudah lewat cooldown',
            'status'       => 'belum',
            'submitted_at' => now()->subMinutes(11),
        ]);

        // New submission should now succeed
        $response = $this->actingAs($this->tenantUser)->post(route('penyewa.complaints.store'), [
            'title'       => 'Aduan Baru Setelah Cooldown',
            'description' => 'Ini harus berhasil terkirim.',
        ]);

        $response->assertRedirect(route('penyewa.complaints.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('complaints', 2);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Complaint History & Staff Action Visibility
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_tenant_only_sees_own_complaints_in_history(): void
    {
        // Own complaint
        $ownComplaint = Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Aduan Saya Sendiri',
            'description'  => 'Deskripsi kepunyaan saya',
            'status'       => 'belum',
            'submitted_at' => now()->subHour(),
        ]);

        // Another tenant's complaint
        $otherUser = User::factory()->create(['role' => 'penyewa', 'status' => 'active']);
        $otherProfile = TenantProfile::create([
            'user_id'         => $otherUser->id,
            'nik'             => '3201099999999999',
            'phone'           => '081999999999',
            'emergency_name'  => 'Kontak Lain',
            'emergency_phone' => '081888888888',
        ]);
        $otherComplaint = Complaint::create([
            'tenant_id'    => $otherProfile->id,
            'title'        => 'Aduan Milik Orang Lain',
            'description'  => 'Penyewa lain mengeluh',
            'status'       => 'belum',
            'submitted_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.complaints.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Aduan Saya Sendiri');
        $response->assertDontSeeText('Aduan Milik Orang Lain');
    }

    /** @test */
    public function test_tenant_sees_staff_actions_and_highlight_in_history(): void
    {
        $complaint = Complaint::create([
            'tenant_id'    => $this->tenantProfile->id,
            'title'        => 'Gagang Pintu Kamar Rusak',
            'description'  => 'Gagang pintu longgar dan hampir lepas',
            'status'       => 'dalam_perbaikan',
            'highlighted'  => true,
            'submitted_at' => now()->subHours(2),
        ]);

        ComplaintAction::create([
            'complaint_id'       => $complaint->id,
            'staff_id'           => $this->staff->id,
            'action_description' => 'Sedang dibelikan baut pengganti di toko besi.',
            'status'             => 'dalam_perbaikan',
        ]);

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.complaints.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Gagang Pintu Kamar Rusak');
        $response->assertSeeText('Prioritas Admin');
        $response->assertSeeText('Dalam Perbaikan');
        $response->assertSeeText('Sedang dibelikan baut pengganti di toko besi.');
        $response->assertSeeText($this->staff->name);
    }
}
