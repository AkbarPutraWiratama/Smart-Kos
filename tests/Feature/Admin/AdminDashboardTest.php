<?php

namespace Tests\Feature\Admin;

use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Post;
use App\Models\PostTarget;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Location $location;
    private Room $room;
    private User $tenantUser;
    private TenantProfile $tenantProfile;
    private RoomAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin user
        $this->adminUser = User::factory()->create([
            'name'     => 'Admin Utama',
            'username' => 'adminutama',
            'email'    => 'admin@smartkos.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // Location
        $this->location = Location::create([
            'name'        => 'Kos Anggrek Residence',
            'address'     => 'Jl. Anggrek No. 5',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        // Room
        $this->room = Room::create([
            'location_id' => $this->location->id,
            'room_number' => '101',
            'floor_name'  => '1',
            'status'      => 'active',
            'type'        => 'standard',
            'rent_amount' => 1500000,
        ]);

        // Tenant user
        $this->tenantUser = User::factory()->create([
            'name'     => 'Siti Rahayu',
            'username' => 'sitirahayu',
            'email'    => 'siti@tenant.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        // Tenant profile
        $this->tenantProfile = TenantProfile::create([
            'user_id'    => $this->tenantUser->id,
            'id_number'  => '3271234567890001',
            'id_type'    => 'ktp',
            'born_date'  => '1995-05-15',
            'gender'     => 'female',
            'occupation' => 'Karyawan',
        ]);

        // Active assignment — uses tenant_id referencing tenant_profiles.id
        $this->assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'rent_amount_snapshot' => 1500000,
            'status'               => 'active',
        ]);
    }

    // ── Access Control ────────────────────────────────────────────────────────

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_admin_dashboard(): void
    {
        $staff = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($staff)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->tenantUser)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    // ── Dashboard View ────────────────────────────────────────────────────────

    public function test_admin_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Admin Utama');
        $response->assertSee('Administrator');
        $response->assertSee('Dashboard Admin');
    }

    public function test_admin_dashboard_shows_room_metrics(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Kamar');
        $response->assertSee('Kamar Terisi');
        $response->assertSee('Kamar Kosong');
        $response->assertSee('Verifikasi Pending');
    }

    public function test_admin_dashboard_shows_financial_summary(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pemasukan Bulan Ini');
        $response->assertSee('Pengeluaran Bulan Ini');
        $response->assertSee('Pendapatan Bersih');
    }

    public function test_admin_dashboard_shows_financial_records_when_present(): void
    {
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->adminUser->id,
            'type'             => 'income',
            'reference_type'   => 'manual',
            'description'      => 'Pembayaran sewa bulan ini',
            'amount'           => 3000000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->adminUser->id,
            'type'             => 'expense',
            'reference_type'   => 'manual',
            'description'      => 'Biaya listrik',
            'amount'           => 500000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('3.000.000');
        $response->assertSee('500.000');
    }

    // ── Admin Sees Rent Amounts ───────────────────────────────────────────────

    public function test_admin_dashboard_shows_rental_nominal_amounts(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        // Admin CAN see rent amounts — Rp 1.500.000
        $response->assertSee('1.500.000');
        $response->assertSee('Nominal Sewa');
    }

    public function test_admin_dashboard_shows_payment_deadlines_table(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Tenggat Pembayaran Penyewa');
        $response->assertSee('Siti Rahayu');
        $response->assertSee('sitirahayu');
    }

    // ── Pending Verifications ─────────────────────────────────────────────────

    public function test_admin_dashboard_shows_pending_verification_count(): void
    {
        ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'proof_path'          => 'proofs/test.jpg',
            'status'              => 'pending',
            'submitted_at'        => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Verifikasi Pending');
    }

    // ── Announcements ─────────────────────────────────────────────────────────

    public function test_admin_can_view_announcements_on_dashboard(): void
    {
        $post = Post::create([
            'author_id'    => $this->adminUser->id,
            'title'        => 'Pengumuman Libur Nasional',
            'content'      => 'Kami ingin memberitahukan libur tanggal 17 Agustus.',
            'status'       => 'published',
            'published_at' => now(),
        ]);

        PostTarget::create([
            'post_id'     => $post->id,
            'target_type' => 'all',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Papan Pengumuman');
        $response->assertSee('Pengumuman Libur Nasional');
    }

    // ── Create Announcement ───────────────────────────────────────────────────

    public function test_admin_can_create_announcement_targeting_all(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.posts.store'), [
            'title'       => 'Pengumuman untuk Semua',
            'content'     => 'Isi pengumuman untuk semua penyewa.',
            'target_type' => 'all',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title'     => 'Pengumuman untuk Semua',
            'author_id' => $this->adminUser->id,
            'status'    => 'published',
        ]);

        $post = Post::where('title', 'Pengumuman untuk Semua')->first();
        $this->assertDatabaseHas('post_targets', [
            'post_id'     => $post->id,
            'target_type' => 'all',
        ]);
    }

    public function test_admin_can_create_announcement_targeting_location(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.posts.store'), [
            'title'       => 'Pengumuman Lokasi Anggrek',
            'content'     => 'Isi pengumuman khusus Kos Anggrek.',
            'target_type' => 'location',
            'location_id' => $this->location->id,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Pengumuman Lokasi Anggrek')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id'     => $post->id,
            'target_type' => 'location',
            'location_id' => $this->location->id,
        ]);
    }

    public function test_admin_can_create_announcement_targeting_room(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.posts.store'), [
            'title'       => 'Pengumuman Kamar 101',
            'content'     => 'Isi pengumuman khusus Kamar 101.',
            'target_type' => 'room',
            'location_id' => $this->location->id,
            'room_id'     => $this->room->id,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Pengumuman Kamar 101')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id'     => $post->id,
            'target_type' => 'room',
            'location_id' => $this->location->id,
            'room_id'     => $this->room->id,
        ]);
    }

    public function test_admin_post_validates_room_belongs_to_location(): void
    {
        $otherLocation = Location::create([
            'name'        => 'Kos Lain',
            'address'     => 'Jl. Lain No. 1',
            'floor_count' => 1,
            'status'      => 'active',
        ]);

        // Room belongs to $this->location but we pass $otherLocation — cross-validation fails
        $response = $this->actingAs($this->adminUser)->post(route('admin.posts.store'), [
            'title'       => 'Pengumuman Invalid',
            'content'     => 'Isi pengumuman invalid cross-location.',
            'target_type' => 'room',
            'location_id' => $otherLocation->id,
            'room_id'     => $this->room->id,
        ]);

        $response->assertSessionHasErrors('room_id');
        $this->assertDatabaseMissing('posts', ['title' => 'Pengumuman Invalid']);
    }

    public function test_admin_post_requires_title_and_content(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.posts.store'), [
            'title'       => '',
            'content'     => '',
            'target_type' => 'all',
        ]);

        $response->assertSessionHasErrors(['title', 'content']);
    }

    // ── Delete Announcement (Soft Delete) ───────────────────────────────────

    public function test_admin_can_delete_own_post(): void
    {
        $post = Post::create([
            'author_id'    => $this->adminUser->id,
            'title'        => 'Post Milik Admin',
            'content'      => 'Konten pengumuman admin.',
            'status'       => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create(['post_id' => $post->id, 'target_type' => 'all']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.posts.destroy', $post));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        // Soft-deleted: record still exists in DB but with deleted_at set
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_admin_can_delete_post_authored_by_staff(): void
    {
        $staff = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);

        $post = Post::create([
            'author_id'    => $staff->id,
            'title'        => 'Post Milik Staff',
            'content'      => 'Konten pengumuman staff.',
            'status'       => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create(['post_id' => $post->id, 'target_type' => 'all']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.posts.destroy', $post));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_admin_post_with_image_is_deleted_from_storage(): void
    {
        Storage::fake('public');

        $fakeImage = UploadedFile::fake()->create('pengumuman_banner.jpg', 100, 'image/jpeg');

        $this->actingAs($this->adminUser)->post(route('admin.posts.store'), [
            'title'       => 'Pengumuman Dengan Gambar',
            'content'     => 'Isi pengumuman dengan lampiran gambar.',
            'target_type' => 'all',
            'image'       => $fakeImage,
        ]);

        $post = Post::where('title', 'Pengumuman Dengan Gambar')->first();
        $this->assertNotNull($post);
        $this->assertNotNull($post->image_path);
        Storage::disk('public')->assertExists($post->image_path);

        // Soft-delete via destroy — image kept until force-deleted
        $this->actingAs($this->adminUser)->delete(route('admin.posts.destroy', $post));
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
        Storage::disk('public')->assertExists($post->image_path);

        // Force-delete via force-destroy — image now removed
        $this->actingAs($this->adminUser)->delete(route('admin.posts.force-destroy', $post->id));
        Storage::disk('public')->assertMissing($post->image_path);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
