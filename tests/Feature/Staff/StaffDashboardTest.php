<?php

namespace Tests\Feature\Staff;

use App\Models\Location;
use App\Models\Post;
use App\Models\PostTarget;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $staffUser;

    private Location $locationA;

    private Location $locationB;

    private Room $roomA1;

    private Room $roomB1;

    private User $tenantUserA;

    private User $tenantUserB;

    private TenantProfile $tenantProfileA;

    private TenantProfile $tenantProfileB;

    private RoomAssignment $assignmentA;

    private RoomAssignment $assignmentB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create active staff user
        $this->staffUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budistaff',
            'email' => 'budi@smartkos.com',
            'phone' => '081234567890',
            'role' => 'staff',
            'status' => 'active',
        ]);

        // Create locations
        $this->locationA = Location::create([
            'name' => 'Kos Melati Residence',
            'address' => 'Jl. Melati No. 10',
            'floor_count' => 2,
            'status' => 'active',
        ]);

        $this->locationB = Location::create([
            'name' => 'Kos Mawar Residence',
            'address' => 'Jl. Mawar No. 20',
            'floor_count' => 2,
            'status' => 'active',
        ]);

        // Create rooms
        $this->roomA1 = Room::create([
            'location_id' => $this->locationA->id,
            'floor_name' => 1,
            'room_number' => 101,
            'rent_amount' => 1750000,
            'status' => 'active',
        ]);

        $this->roomB1 = Room::create([
            'location_id' => $this->locationB->id,
            'floor_name' => 2,
            'room_number' => 201,
            'rent_amount' => 2200000,
            'status' => 'active',
        ]);

        // Create tenants and profiles
        $this->tenantUserA = User::factory()->create([
            'name' => 'Siti Rahma',
            'username' => 'sitirahma',
            'email' => 'siti@example.com',
            'phone' => '081211112222',
            'role' => 'penyewa',
            'status' => 'active',
        ]);
        $this->tenantProfileA = TenantProfile::create([
            'user_id' => $this->tenantUserA->id,
            'phone' => '081211112222',
        ]);

        $this->tenantUserB = User::factory()->create([
            'name' => 'Ahmad Fauzi',
            'username' => 'ahmadfauzi',
            'email' => 'ahmad@example.com',
            'phone' => '081233334444',
            'role' => 'penyewa',
            'status' => 'active',
        ]);
        $this->tenantProfileB = TenantProfile::create([
            'user_id' => $this->tenantUserB->id,
            'phone' => '081233334444',
        ]);

        // Assignments
        $this->assignmentA = RoomAssignment::create([
            'room_id' => $this->roomA1->id,
            'tenant_id' => $this->tenantProfileA->id,
            'start_date' => now()->subMonths(1)->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 1750000,
        ]);

        $this->assignmentB = RoomAssignment::create([
            'room_id' => $this->roomB1->id,
            'tenant_id' => $this->tenantProfileB->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 2200000,
        ]);
    }

    public function test_staff_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Staff');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Staff Operasional');
        $response->assertSee('budistaff');
        $response->assertSee('081234567890');
        $response->assertSee('Tenggat Pembayaran Penyewa');
    }

    public function test_staff_dashboard_shows_payment_deadlines_and_status(): void
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff.dashboard'));

        $response->assertStatus(200);
        // Tenant A
        $response->assertSee('Siti Rahma');
        $response->assertSee('Kamar 101');
        $response->assertSee('Kos Melati Residence');

        // Tenant B
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('Kamar 201');
        $response->assertSee('Kos Mawar Residence');
    }

    public function test_staff_dashboard_strictly_hides_rental_nominal_amounts(): void
    {
        $response = $this->actingAs($this->staffUser)->get(route('staff.dashboard'));

        $response->assertStatus(200);

        // Ensure rental amounts are NEVER shown in staff operational view
        $response->assertDontSee('1.750.000');
        $response->assertDontSee('1750000');
        $response->assertDontSee('2.200.000');
        $response->assertDontSee('2200000');
        $response->assertDontSee('Tarif Sewa');
        $response->assertDontSee('Harga Kamar');
    }

    public function test_staff_can_filter_dashboard_by_location(): void
    {
        // Filter by Location A
        $responseA = $this->actingAs($this->staffUser)
            ->get(route('staff.dashboard', ['location_filter' => $this->locationA->id]));

        $responseA->assertStatus(200);
        $responseA->assertSee('Siti Rahma');
        $responseA->assertDontSee('Ahmad Fauzi');

        // Filter by Location B
        $responseB = $this->actingAs($this->staffUser)
            ->get(route('staff.dashboard', ['location_filter' => $this->locationB->id]));

        $responseB->assertStatus(200);
        $responseB->assertSee('Ahmad Fauzi');
        $responseB->assertDontSee('Siti Rahma');
    }

    public function test_staff_can_view_announcements(): void
    {
        $postGlobal = Post::create([
            'author_id' => $this->staffUser->id,
            'title' => 'Pemberitahuan Libur Nasional',
            'content' => 'Kantor operasional tutup pada tanggal merah.',
            'status' => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create([
            'post_id' => $postGlobal->id,
            'target_type' => 'all',
        ]);

        $postLocB = Post::create([
            'author_id' => $this->staffUser->id,
            'title' => 'Pemeliharaan Wi-Fi Kos Mawar',
            'content' => 'Router lantai 2 akan direstart.',
            'status' => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create([
            'post_id' => $postLocB->id,
            'target_type' => 'location',
            'location_id' => $this->locationB->id,
        ]);

        // In 'all' view, both are visible
        $responseAll = $this->actingAs($this->staffUser)->get(route('staff.dashboard'));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Pemberitahuan Libur Nasional');
        $responseAll->assertSee('Pemeliharaan Wi-Fi Kos Mawar');

        // Filter Location A: global is visible, Location B specific is not
        $responseLocA = $this->actingAs($this->staffUser)
            ->get(route('staff.dashboard', ['location_filter' => $this->locationA->id]));
        $responseLocA->assertStatus(200);
        $responseLocA->assertSee('Pemberitahuan Libur Nasional');
        $responseLocA->assertDontSee('Pemeliharaan Wi-Fi Kos Mawar');
    }

    public function test_staff_can_create_announcement_targeting_all_locations(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title' => 'Pembersihan Tandon Air Serentak',
            'content' => 'Harap menampung air bersih sebelum pukul 09:00 WIB.',
            'target_type' => 'all',
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'author_id' => $this->staffUser->id,
            'title' => 'Pembersihan Tandon Air Serentak',
            'status' => 'published',
        ]);

        $post = Post::where('title', 'Pembersihan Tandon Air Serentak')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id' => $post->id,
            'target_type' => 'all',
            'location_id' => null,
            'room_id' => null,
        ]);
    }

    public function test_staff_can_create_announcement_targeting_specific_location(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title' => 'Pengecatan Pagar Kos Melati',
            'content' => 'Pagar depan akan dicat besok pagi.',
            'target_type' => 'location',
            'location_id' => $this->locationA->id,
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Pengecatan Pagar Kos Melati')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id' => $post->id,
            'target_type' => 'location',
            'location_id' => $this->locationA->id,
            'room_id' => null,
        ]);
    }

    public function test_staff_can_create_announcement_targeting_specific_room(): void
    {
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title' => 'Pemeriksaan Stopkontak Kamar 101',
            'content' => 'Teknisi listrik akan berkunjung jam 14:00.',
            'target_type' => 'room',
            'location_id' => $this->locationA->id,
            'room_id' => $this->roomA1->id,
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Pemeriksaan Stopkontak Kamar 101')->first();
        $this->assertNotNull($post);
        $this->assertDatabaseHas('post_targets', [
            'post_id' => $post->id,
            'target_type' => 'room',
            'location_id' => $this->locationA->id,
            'room_id' => $this->roomA1->id,
        ]);
    }

    public function test_staff_post_creation_validates_room_belongs_to_location(): void
    {
        // Try submitting room from Location B with location_id set to Location A
        $response = $this->actingAs($this->staffUser)->post(route('staff.posts.store'), [
            'title' => 'Pengumuman Salah Kamar',
            'content' => 'Isi konten.',
            'target_type' => 'room',
            'location_id' => $this->locationA->id,
            'room_id' => $this->roomB1->id, // belongs to location B!
        ]);

        $response->assertSessionHasErrors('room_id');
        $this->assertDatabaseMissing('posts', ['title' => 'Pengumuman Salah Kamar']);
    }

    public function test_guest_and_penyewa_cannot_access_staff_dashboard_or_post(): void
    {
        // Guest
        $this->get(route('staff.dashboard'))->assertRedirect(route('login'));
        $this->post(route('staff.posts.store'), [
            'title' => 'Test Post',
            'content' => 'Content',
            'target_type' => 'all',
        ])->assertRedirect(route('login'));

        // Penyewa
        $this->actingAs($this->tenantUserA)->get(route('staff.dashboard'))->assertStatus(403);
        $this->actingAs($this->tenantUserA)->post(route('staff.posts.store'), [
            'title' => 'Test Post',
            'content' => 'Content',
            'target_type' => 'all',
        ])->assertStatus(403);
    }
}
