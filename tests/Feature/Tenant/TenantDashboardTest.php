<?php

namespace Tests\Feature\Tenant;

use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Post;
use App\Models\PostTarget;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\VAPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $tenantUser;
    private TenantProfile $tenantProfile;
    private Location $location;
    private Room $room;
    private RoomAssignment $assignment;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Dewi Lestari',
            'username' => 'dewilestari',
            'email'    => 'dewi@example.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone'   => '081298765432',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Sakura Indah',
            'address'     => 'Jl. Sakura No. 8',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 2,
            'room_number' => 205,
            'rent_amount' => 1500000,
            'status'      => 'active',
        ]);

        $this->assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => now()->subMonths(1)->toDateString(),
            'status'               => 'active',
            'rent_amount_snapshot' => 1500000,
        ]);
    }

    public function test_tenant_can_view_own_dashboard(): void
    {
        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dewi Lestari');
        $response->assertSee('Penyewa');
        $response->assertSee('205');
        $response->assertSee('Lantai 2');
        $response->assertSee('Kos Sakura Indah');
        $response->assertSee('Rp 1.500.000');
    }

    public function test_unassigned_tenant_views_fallback_notice(): void
    {
        $unassignedUser = User::factory()->create([
            'name'   => 'Rian Pratama',
            'role'   => 'penyewa',
            'status' => 'active',
        ]);
        TenantProfile::create(['user_id' => $unassignedUser->id]);

        $response = $this->actingAs($unassignedUser)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Belum Ada Kamar Aktif Ditugaskan');
    }

    public function test_tenant_cannot_see_other_tenant_assignment(): void
    {
        $otherLocation = Location::create([
            'name'    => 'Kos Anggrek',
            'address' => 'Jl. Anggrek No. 1',
        ]);

        $otherRoom = Room::create([
            'location_id' => $otherLocation->id,
            'floor_name'  => 1,
            'room_number' => 109,
            'rent_amount' => 2000000,
            'status'      => 'active',
        ]);

        $otherUser = User::factory()->create([
            'name'   => 'Siti Rahma',
            'role'   => 'penyewa',
            'status' => 'active',
        ]);
        $otherProfile = TenantProfile::create(['user_id' => $otherUser->id]);

        RoomAssignment::create([
            'room_id'              => $otherRoom->id,
            'tenant_id'            => $otherProfile->id,
            'start_date'           => now()->toDateString(),
            'status'               => 'active',
            'rent_amount_snapshot' => 2000000,
        ]);

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Kos Anggrek');
        $response->assertDontSee('Kamar Anda</p>', false);
        $response->assertDontSee('Siti Rahma');
        $response->assertDontSee('Rp 2.000.000');
        // Ensure the tenant only sees their own room
        $response->assertSee('205');
        $response->assertSee('Kos Sakura Indah');
    }

    public function test_payment_deadline_color_gray_when_more_than_7_days(): void
    {
        // Set end_date 15 days from now
        $this->assignment->update([
            'end_date' => now()->addDays(15)->toDateString(),
        ]);

        $this->assertEquals('gray', $this->assignment->getDeadlineColor());

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('> 7 hari (Aman)');
    }

    public function test_payment_deadline_color_yellow_when_less_than_7_days(): void
    {
        // Set end_date 4 days from now
        $this->assignment->update([
            'end_date' => now()->addDays(4)->toDateString(),
        ]);

        $this->assertEquals('yellow', $this->assignment->getDeadlineColor());

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('< 7 hari');
    }

    public function test_payment_deadline_color_red_when_today(): void
    {
        // Set end_date today
        $this->assignment->update([
            'end_date' => now()->toDateString(),
        ]);

        $this->assertEquals('red', $this->assignment->getDeadlineColor());

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Jatuh Tempo Hari Ini');
    }

    public function test_payment_status_reflects_paid_status(): void
    {
        // Approved manual payment
        ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1500000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '123456789',
            'proof_path'          => 'proofs/test.jpg',
            'status'              => 'approved',
            'submitted_at'        => now(),
        ]);

        $this->assertTrue($this->assignment->isPaidForCurrentPeriod());
        $this->assertEquals('Lunas', $this->assignment->getPaymentStatus());

        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Lunas');
    }

    public function test_tenant_can_filter_announcements_by_location(): void
    {
        // 1. Post targeted to ALL locations
        $postAll = Post::create([
            'author_id'    => $this->admin->id,
            'title'        => 'Pengumuman Bersama Semua Lokasi',
            'content'      => 'Libur nasional pengelola kos.',
            'status'       => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create([
            'post_id'     => $postAll->id,
            'target_type' => 'all',
        ]);

        // 2. Post targeted to tenant's specific location (Kos Sakura Indah)
        $postSakura = Post::create([
            'author_id'    => $this->admin->id,
            'title'        => 'Pembersihan Filter AC Sakura',
            'content'      => 'Jadwal pembersihan AC di Kos Sakura.',
            'status'       => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create([
            'post_id'     => $postSakura->id,
            'target_type' => 'location',
            'location_id' => $this->location->id,
        ]);

        // 3. Post targeted to another location
        $otherLocation = Location::create([
            'name'    => 'Kos Cempaka',
            'address' => 'Jl. Cempaka',
        ]);
        $postOther = Post::create([
            'author_id'    => $this->admin->id,
            'title'        => 'Info Khusus Kos Cempaka',
            'content'      => 'Perbaikan parkiran Kos Cempaka.',
            'status'       => 'published',
            'published_at' => now(),
        ]);
        PostTarget::create([
            'post_id'     => $postOther->id,
            'target_type' => 'location',
            'location_id' => $otherLocation->id,
        ]);

        // Test Default / Relevant: Sees All + Sakura, but NOT Cempaka
        $responseRelevant = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard', ['location_filter' => 'relevant']));
        $responseRelevant->assertStatus(200);
        $responseRelevant->assertSee('Pengumuman Bersama Semua Lokasi');
        $responseRelevant->assertSee('Pembersihan Filter AC Sakura');
        $responseRelevant->assertDontSee('Info Khusus Kos Cempaka');

        // Test Filter: all locations only
        $responseAll = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard', ['location_filter' => 'all']));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Pengumuman Bersama Semua Lokasi');
        $responseAll->assertDontSee('Pembersihan Filter AC Sakura');
        $responseAll->assertDontSee('Info Khusus Kos Cempaka');

        // Test Filter: specific location only
        $responseSpecific = $this->actingAs($this->tenantUser)->get(route('penyewa.dashboard', ['location_filter' => 'specific']));
        $responseSpecific->assertStatus(200);
        $responseSpecific->assertDontSee('Pengumuman Bersama Semua Lokasi');
        $responseSpecific->assertSee('Pembersihan Filter AC Sakura');
        $responseSpecific->assertDontSee('Info Khusus Kos Cempaka');
    }

    public function test_guest_and_non_penyewa_cannot_access_tenant_dashboard(): void
    {
        $guestResponse = $this->get(route('penyewa.dashboard'));
        $guestResponse->assertRedirect(route('login'));

        $staffUser = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);
        $staffResponse = $this->actingAs($staffUser)->get(route('penyewa.dashboard'));
        $staffResponse->assertStatus(403);
    }
}

