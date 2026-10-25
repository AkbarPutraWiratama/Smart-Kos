<?php

namespace Tests\Feature\Admin;

use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);
    }

    public function test_admin_can_view_tenants_index_page(): void
    {
        $tenant = User::factory()->create([
            'name'     => 'Ahmad Dhani',
            'username' => 'ahmaddhani',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);
        TenantProfile::create([
            'user_id' => $tenant->id,
            'phone'   => '08123456789',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Ahmad Dhani');
        $response->assertSee('ahmaddhani');
        $response->assertSee('08123456789');
    }

    public function test_admin_can_view_create_tenant_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Akun Penyewa Baru');
    }

    public function test_admin_can_create_a_tenant_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'          => 'Siti Nurhaliza',
            'username'      => 'sitinur',
            'email'         => 'siti@example.com',
            'password'      => 'secretpassword123',
            'phone'         => '082211334455',
            'address'       => 'Jl. Anggrek No. 10',
            'registered_at' => '2026-10-25',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'     => 'Siti Nurhaliza',
            'username' => 'sitinur',
            'email'    => 'siti@example.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $user = User::where('username', 'sitinur')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secretpassword123', $user->password));

        $this->assertDatabaseHas('tenant_profiles', [
            'user_id' => $user->id,
            'phone'   => '082211334455',
            'address' => 'Jl. Anggrek No. 10',
        ]);
    }

    public function test_admin_cannot_create_tenant_with_duplicate_username_or_email(): void
    {
        User::factory()->create([
            'username' => 'existinguser',
            'email'    => 'existing@example.com',
            'role'     => 'penyewa',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name'     => 'Duplicate Test',
            'username' => 'existinguser',
            'email'    => 'existing@example.com',
            'password' => 'secret12345',
        ]);

        $response->assertSessionHasErrors(['username', 'email']);
    }

    public function test_admin_can_view_edit_tenant_page(): void
    {
        $tenant = User::factory()->create([
            'name'     => 'Joko Anwar',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);
        TenantProfile::create([
            'user_id' => $tenant->id,
            'phone'   => '087711223344',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $tenant));

        $response->assertStatus(200);
        $response->assertSee('Joko Anwar');
        $response->assertSee('087711223344');
    }

    public function test_admin_can_update_tenant_account_and_profile(): void
    {
        $tenant = User::factory()->create([
            'name'     => 'Old Name',
            'username' => 'oldusername',
            'email'    => 'old@example.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);
        TenantProfile::create([
            'user_id' => $tenant->id,
            'phone'   => '081111111',
            'address' => 'Old Address',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $tenant), [
            'name'     => 'New Name',
            'username' => 'newusername',
            'email'    => 'new@example.com',
            'phone'    => '082222222',
            'address'  => 'New Address',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'       => $tenant->id,
            'name'     => 'New Name',
            'username' => 'newusername',
            'email'    => 'new@example.com',
        ]);

        $this->assertDatabaseHas('tenant_profiles', [
            'user_id' => $tenant->id,
            'phone'   => '082222222',
            'address' => 'New Address',
        ]);
    }

    public function test_admin_can_deactivate_tenant_with_valid_password(): void
    {
        $tenant = User::factory()->create([
            'role'   => 'penyewa',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.deactivate', $tenant), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('inactive', $tenant->fresh()->status);
        $this->assertNotNull($tenant->fresh()->inactive_at);
    }

    public function test_admin_cannot_deactivate_tenant_with_invalid_password(): void
    {
        $tenant = User::factory()->create([
            'role'   => 'penyewa',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.deactivate', $tenant), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('active', $tenant->fresh()->status);
    }

    public function test_admin_can_reactivate_tenant_with_valid_password(): void
    {
        $tenant = User::factory()->create([
            'role'        => 'penyewa',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(3),
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.reactivate', $tenant), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('active', $tenant->fresh()->status);
        $this->assertNull($tenant->fresh()->inactive_at);
    }

    public function test_admin_can_reset_tenant_password_with_admin_password(): void
    {
        $tenant = User::factory()->create([
            'role'     => 'penyewa',
            'status'   => 'active',
            'password' => Hash::make('oldtenantpassword'),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.reset-password', $tenant), [
            'password'                  => 'adminpassword',
            'new_password'              => 'brandnewpassword123',
            'new_password_confirmation' => 'brandnewpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('brandnewpassword123', $tenant->fresh()->password));
    }

    public function test_deactivated_tenant_cannot_login(): void
    {
        $tenant = User::factory()->create([
            'username' => 'inactivetenant',
            'password' => Hash::make('tenantpass123'),
            'role'     => 'penyewa',
            'status'   => 'inactive',
        ]);

        $response = $this->post(route('login'), [
            'username' => 'inactivetenant',
            'password' => 'tenantpass123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_non_admin_cannot_access_tenant_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $penyewa = User::factory()->create(['role' => 'penyewa']);

        $this->actingAs($staff)->get(route('admin.users.index'))->assertStatus(403);
        $this->actingAs($penyewa)->get(route('admin.users.index'))->assertStatus(403);
    }
}

