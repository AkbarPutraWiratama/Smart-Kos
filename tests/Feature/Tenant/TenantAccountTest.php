<?php

namespace Tests\Feature\Tenant;

use App\Models\Location;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $tenantUser;
    private TenantProfile $tenantProfile;
    private Location $location;
    private Room $room;
    private RoomAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantUser = User::factory()->create([
            'name'     => 'Dewi Lestari',
            'username' => 'dewilestari',
            'email'    => 'dewi@example.com',
            'phone'    => '081298765432',
            'password' => Hash::make('password123'),
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id'       => $this->tenantUser->id,
            'phone'         => '081298765432',
            'address'       => 'Jl. Anggrek No. 12, Bandung',
            'registered_at' => '2026-09-01',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Sakura Indah',
            'address'     => 'Jl. Sakura No. 8',
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
            'start_date'           => '2026-10-01',
            'rent_amount_snapshot' => 1500000,
            'status'               => 'active',
        ]);
    }

    public function test_tenant_can_view_account_page(): void
    {
        $response = $this->actingAs($this->tenantUser)->get(route('penyewa.account'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun Penyewa');
        $response->assertSee('Dewi Lestari');
        $response->assertSee('@dewilestari');
        $response->assertSee('Kamar 101');
        $response->assertSee('Kos Sakura Indah');
        $response->assertSee('Informasi Data Diri & Kontak', false);
        $response->assertSee('Keamanan & Kata Sandi', false);
    }

    public function test_tenant_can_update_personal_information(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.profile'), [
            'name'    => 'Dewi Lestari Updated',
            'email'   => 'dewi.baru@example.com',
            'phone'   => '081211112222',
            'address' => 'Jl. Mawar No. 99, Surabaya',
        ]);

        $response->assertRedirect(route('penyewa.account'));
        $response->assertSessionHas('success');

        $this->tenantUser->refresh();
        $this->tenantProfile->refresh();

        $this->assertEquals('Dewi Lestari Updated', $this->tenantUser->name);
        $this->assertEquals('dewi.baru@example.com', $this->tenantUser->email);
        $this->assertEquals('081211112222', $this->tenantUser->phone);
        $this->assertEquals('081211112222', $this->tenantProfile->phone);
        $this->assertEquals('Jl. Mawar No. 99, Surabaya', $this->tenantProfile->address);
    }

    public function test_tenant_can_update_profile_with_null_email(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.profile'), [
            'name'    => 'Dewi Lestari',
            'email'   => null,
            'phone'   => '081298765432',
            'address' => 'Jl. Anggrek No. 12',
        ]);

        $response->assertRedirect(route('penyewa.account'));
        $response->assertSessionHas('success');

        $this->tenantUser->refresh();
        $this->assertNull($this->tenantUser->email);
    }

    public function test_tenant_cannot_use_email_already_taken_by_another_user(): void
    {
        User::factory()->create([
            'email'    => 'taken@example.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.profile'), [
            'name'    => 'Dewi Lestari',
            'email'   => 'taken@example.com',
            'phone'   => '081298765432',
            'address' => 'Jl. Anggrek No. 12',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_tenant_can_keep_own_email_when_updating(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.profile'), [
            'name'    => 'Dewi Lestari Edit',
            'email'   => 'dewi@example.com',
            'phone'   => '081298765432',
            'address' => 'Jl. Anggrek No. 12',
        ]);

        $response->assertRedirect(route('penyewa.account'));
        $response->assertSessionHas('success');
    }

    public function test_tenant_cannot_tamper_role_status_or_room_assignment(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.profile'), [
            'name'                 => 'Dewi Hacker',
            'email'                => 'dewi@example.com',
            'phone'                => '081298765432',
            'role'                 => 'admin',
            'status'               => 'inactive',
            'room_id'              => 999,
            'rent_amount_snapshot' => 100,
        ]);

        $response->assertRedirect(route('penyewa.account'));

        $this->tenantUser->refresh();
        $this->assignment->refresh();

        // Invariants must remain intact
        $this->assertEquals('penyewa', $this->tenantUser->role);
        $this->assertEquals('active', $this->tenantUser->status);
        $this->assertEquals($this->room->id, $this->assignment->room_id);
        $this->assertEquals(1500000, (float) $this->assignment->rent_amount_snapshot);
    }

    public function test_tenant_can_update_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.password'), [
            'current_password'      => 'password123',
            'password'              => 'newsecurepassword',
            'password_confirmation' => 'newsecurepassword',
        ]);

        $response->assertRedirect(route('penyewa.account'));
        $response->assertSessionHas('success');

        $this->tenantUser->refresh();
        $this->assertTrue(Hash::check('newsecurepassword', $this->tenantUser->password));
        $this->assertFalse(Hash::check('password123', $this->tenantUser->password));
    }

    public function test_tenant_cannot_update_password_with_invalid_current_password(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.password'), [
            'current_password'      => 'wrongpassword',
            'password'              => 'newsecurepassword',
            'password_confirmation' => 'newsecurepassword',
        ]);

        $response->assertSessionHasErrors('current_password');

        $this->tenantUser->refresh();
        $this->assertTrue(Hash::check('password123', $this->tenantUser->password));
    }

    public function test_tenant_cannot_update_password_if_confirmation_mismatches(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.password'), [
            'current_password'      => 'password123',
            'password'              => 'newsecurepassword',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_tenant_cannot_update_password_under_8_characters(): void
    {
        $response = $this->actingAs($this->tenantUser)->put(route('penyewa.account.password'), [
            'current_password'      => 'password123',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_guest_and_non_tenant_cannot_access_tenant_account_page(): void
    {
        // Guest
        $this->get(route('penyewa.account'))->assertRedirect('/login');

        // Staff
        $staff = User::factory()->create(['role' => 'staff', 'status' => 'active']);
        $this->actingAs($staff)->get(route('penyewa.account'))->assertStatus(403);

        // Admin
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('penyewa.account'))->assertStatus(403);
    }
}
