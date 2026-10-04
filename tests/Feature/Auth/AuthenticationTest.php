<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Smart Kos');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/admin/dashboard');
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_are_blocked_from_authenticating(): void
    {
        $user = User::factory()->create([
            'username' => 'inactiveuser',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'username' => 'inactiveuser',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_staff_redirects_to_staff_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'staff1',
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'username' => 'staff1',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/staff/dashboard');
    }

    public function test_penyewa_redirects_to_penyewa_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'penyewa1',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'username' => 'penyewa1',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/penyewa/dashboard');
    }
}
