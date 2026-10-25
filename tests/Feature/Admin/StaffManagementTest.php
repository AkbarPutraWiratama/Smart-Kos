<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManagementTest extends TestCase
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

    public function test_admin_can_view_staff_index_page(): void
    {
        $staff = User::factory()->create([
            'name'     => 'Bambang Sudarsono',
            'username' => 'bambangs',
            'email'    => 'bambang@staff.test',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('Bambang Sudarsono');
        $response->assertSee('bambangs');
        $response->assertSee('bambang@staff.test');
    }

    public function test_admin_can_view_create_staff_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.staff.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Akun Staff Baru');
    }

    public function test_admin_can_create_a_staff_account(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.staff.store'), [
            'name'     => 'Agus Setiawan',
            'username' => 'agusstaff',
            'email'    => 'agus@staff.test',
            'password' => 'staffsecret123',
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'     => 'Agus Setiawan',
            'username' => 'agusstaff',
            'email'    => 'agus@staff.test',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $user = User::where('username', 'agusstaff')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('staffsecret123', $user->password));
    }

    public function test_admin_cannot_create_staff_with_duplicate_username_or_email(): void
    {
        User::factory()->create([
            'username' => 'existingstaff',
            'email'    => 'existingstaff@test.com',
            'role'     => 'staff',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.staff.store'), [
            'name'     => 'Duplicate Test',
            'username' => 'existingstaff',
            'email'    => 'existingstaff@test.com',
            'password' => 'secret12345',
        ]);

        $response->assertSessionHasErrors(['username', 'email']);
    }

    public function test_admin_can_view_edit_staff_page(): void
    {
        $staff = User::factory()->create([
            'name'     => 'Rina Operasional',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.staff.edit', $staff));

        $response->assertStatus(200);
        $response->assertSee('Rina Operasional');
    }

    public function test_admin_can_update_staff_account(): void
    {
        $staff = User::factory()->create([
            'name'     => 'Old Staff Name',
            'username' => 'oldstaff',
            'email'    => 'oldstaff@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.staff.update', $staff), [
            'name'     => 'New Staff Name',
            'username' => 'newstaffuser',
            'email'    => 'newstaff@test.com',
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'       => $staff->id,
            'name'     => 'New Staff Name',
            'username' => 'newstaffuser',
            'email'    => 'newstaff@test.com',
        ]);
    }

    public function test_admin_can_deactivate_staff_with_valid_password(): void
    {
        $staff = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.staff.deactivate', $staff), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('inactive', $staff->fresh()->status);
        $this->assertNotNull($staff->fresh()->inactive_at);
    }

    public function test_admin_cannot_deactivate_staff_with_invalid_password(): void
    {
        $staff = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.staff.deactivate', $staff), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals('active', $staff->fresh()->status);
    }

    public function test_admin_can_reactivate_staff_with_valid_password(): void
    {
        $staff = User::factory()->create([
            'role'        => 'staff',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.staff.reactivate', $staff), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('active', $staff->fresh()->status);
        $this->assertNull($staff->fresh()->inactive_at);
    }

    public function test_admin_can_reset_staff_password_with_admin_password(): void
    {
        $staff = User::factory()->create([
            'role'     => 'staff',
            'status'   => 'active',
            'password' => Hash::make('oldstaffpass'),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.staff.reset-password', $staff), [
            'password'                  => 'adminpassword',
            'new_password'              => 'newstaffpass123',
            'new_password_confirmation' => 'newstaffpass123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newstaffpass123', $staff->fresh()->password));
    }

    public function test_deactivated_staff_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'inactivestaff',
            'password' => Hash::make('staffpass123'),
            'role'     => 'staff',
            'status'   => 'inactive',
        ]);

        $response = $this->post(route('login'), [
            'username' => 'inactivestaff',
            'password' => 'staffpass123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_non_admin_cannot_access_staff_management(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $penyewa = User::factory()->create(['role' => 'penyewa']);

        $this->actingAs($staff)->get(route('admin.staff.index'))->assertStatus(403);
        $this->actingAs($penyewa)->get(route('admin.staff.index'))->assertStatus(403);
    }
}

