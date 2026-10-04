<?php

namespace Tests\Feature;

use App\Helpers\SmartKosMenuHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationAndMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_role_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/staff/dashboard')->assertRedirect('/login');
        $this->get('/penyewa/dashboard')->assertRedirect('/login');
    }

    public function test_staff_cannot_access_admin_routes(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
        ]);

        $this->actingAs($staff)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($staff)->get('/admin/finance')->assertStatus(403);
    }

    public function test_penyewa_cannot_access_admin_or_staff_routes(): void
    {
        $penyewa = User::factory()->create([
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->actingAs($penyewa)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($penyewa)->get('/staff/complaints')->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200)->assertSee('Admin Dashboard');
        $this->actingAs($admin)->get('/admin/finance')->assertStatus(200)->assertSee('Admin Finance');
    }

    public function test_staff_can_access_staff_routes(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
        ]);

        $this->actingAs($staff)->get('/staff/dashboard')->assertStatus(200)->assertSee('Staff Dashboard');
        $this->actingAs($staff)->get('/staff/complaints')->assertStatus(200)->assertSee('Staff Complaints');
    }

    public function test_penyewa_can_access_penyewa_routes(): void
    {
        $penyewa = User::factory()->create([
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $this->actingAs($penyewa)->get('/penyewa/dashboard')->assertStatus(200)->assertSee('Penyewa Dashboard');
    }

    public function test_inactive_user_is_logged_out_when_accessing_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_smart_kos_menu_helper_returns_correct_role_menus(): void
    {
        // Guest
        $guestMenu = SmartKosMenuHelper::getGuestMenu();
        $this->assertEquals('Landing Page', $guestMenu[0]['items'][0]['name']);
        $this->assertEquals('Lokasi & Kamar', $guestMenu[0]['items'][1]['name']);
        $this->assertEquals('Login', $guestMenu[0]['items'][2]['name']);

        // Penyewa
        $penyewa = User::factory()->create(['role' => 'penyewa', 'status' => 'active']);
        $this->actingAs($penyewa);
        $penyewaMenu = SmartKosMenuHelper::getMenuGroups();
        $this->assertEquals('Dashboard', $penyewaMenu[0]['items'][0]['name']);
        $this->assertEquals('Pembayaran', $penyewaMenu[0]['items'][1]['name']);
        $this->assertEquals('Aduan', $penyewaMenu[0]['items'][2]['name']);
        $this->assertEquals('Akun', $penyewaMenu[0]['items'][3]['name']);

        // Staff
        $staff = User::factory()->create(['role' => 'staff', 'status' => 'active']);
        $this->actingAs($staff);
        $staffMenu = SmartKosMenuHelper::getMenuGroups();
        $this->assertEquals('Dashboard', $staffMenu[0]['items'][0]['name']);
        $this->assertEquals('Status Pembayaran Penyewa', $staffMenu[0]['items'][1]['name']);
        $this->assertEquals('Tindakan Aduan', $staffMenu[0]['items'][2]['name']);
        $this->assertEquals('Akun', $staffMenu[0]['items'][3]['name']);

        // Admin
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin);
        $adminMenu = SmartKosMenuHelper::getMenuGroups();
        $this->assertEquals('Dashboard', $adminMenu[0]['items'][0]['name']);
        $this->assertEquals('Status Penyewa', $adminMenu[0]['items'][1]['name']);
        $this->assertEquals('Tindakan Aduan', $adminMenu[0]['items'][2]['name']);
        $this->assertEquals('Keuangan', $adminMenu[0]['items'][3]['name']);
        $this->assertEquals('Lokasi & Kamar', $adminMenu[1]['items'][0]['name']);
        $this->assertEquals('Pelanggan & Staff', $adminMenu[1]['items'][1]['name']);
        $this->assertEquals('Akun', $adminMenu[1]['items'][2]['name']);
    }
}
