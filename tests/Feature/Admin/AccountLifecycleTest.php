<?php

namespace Tests\Feature\Admin;

use App\Models\Expense;
use App\Models\ManualPayment;
use App\Models\PayrollRecord;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountLifecycleTest extends TestCase
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

    public function test_inactive_account_under_7_days_cannot_be_deleted(): void
    {
        $tenant = User::factory()->create([
            'role'        => 'penyewa',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(3),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $tenant), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('deletion');
        $this->assertDatabaseHas('users', ['id' => $tenant->id]);
    }

    public function test_active_account_cannot_be_deleted(): void
    {
        $tenant = User::factory()->create([
            'role'   => 'penyewa',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $tenant), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('deletion');
        $this->assertDatabaseHas('users', ['id' => $tenant->id]);
    }

    public function test_inactive_tenant_over_7_days_without_financial_records_can_be_deleted(): void
    {
        $tenant = User::factory()->create([
            'name'        => 'Clean Inactive Tenant',
            'role'        => 'penyewa',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(8),
        ]);
        $profile = TenantProfile::create([
            'user_id' => $tenant->id,
            'phone'   => '0812345678',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $tenant), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $tenant->id]);
        $this->assertDatabaseMissing('tenant_profiles', ['id' => $profile->id]);
    }

    public function test_inactive_tenant_over_7_days_with_room_assignment_cannot_be_deleted(): void
    {
        $tenant = User::factory()->create([
            'role'        => 'penyewa',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(10),
        ]);
        $profile = TenantProfile::create([
            'user_id' => $tenant->id,
            'phone'   => '0812345678',
        ]);

        $location = \App\Models\Location::create([
            'name' => 'Kos Lifecycle',
            'address' => 'Jl. Lifecycle',
            'floor_count' => 1,
            'status' => 'active',
        ]);

        $room = Room::create([
            'location_id' => $location->id,
            'floor_name' => 1,
            'room_number' => 301,
            'rent_amount' => 1000000,
            'status' => 'active',
        ]);

        RoomAssignment::create([
            'room_id' => $room->id,
            'tenant_id' => $profile->id,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->subDays(10)->toDateString(),
            'status' => 'completed',
            'rent_amount_snapshot' => 1000000,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $tenant), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('deletion');
        $this->assertDatabaseHas('users', ['id' => $tenant->id]);
        $this->assertDatabaseHas('tenant_profiles', ['id' => $profile->id]);
    }

    public function test_inactive_staff_under_7_days_cannot_be_deleted(): void
    {
        $staff = User::factory()->create([
            'role'        => 'staff',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $staff), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('deletion');
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_inactive_staff_over_7_days_without_financial_records_can_be_deleted(): void
    {
        $staff = User::factory()->create([
            'name'        => 'Clean Inactive Staff',
            'role'        => 'staff',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(9),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $staff), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_inactive_staff_over_7_days_with_financial_history_cannot_be_deleted(): void
    {
        $staff = User::factory()->create([
            'role'        => 'staff',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(15),
        ]);

        $location = \App\Models\Location::create([
            'name' => 'Kos Finance History',
            'address' => 'Jl. History',
            'floor_count' => 1,
            'status' => 'active',
        ]);

        $category = \App\Models\ExpenseCategory::create([
            'name' => 'Operasional Kebersihan',
            'type' => 'operational',
        ]);

        // Attach an expense recorded by this staff
        Expense::create([
            'location_id' => $location->id,
            'category_id' => $category->id,
            'recorded_by' => $staff->id,
            'title' => 'Beli Sapu dan Pembersih Lantai',
            'amount' => 50000,
            'expense_date' => now()->subDays(20)->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $staff), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('deletion');
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_delete_requires_valid_admin_password(): void
    {
        $staff = User::factory()->create([
            'role'        => 'staff',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $staff), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_edit_pages_display_lifecycle_banner_for_inactive_accounts(): void
    {
        $inactiveTenant = User::factory()->create([
            'role'        => 'penyewa',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(3),
        ]);
        TenantProfile::create([
            'user_id' => $inactiveTenant->id,
            'phone'   => '0812345678',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $inactiveTenant));
        $response->assertStatus(200);
        $response->assertSee('Status Siklus Akun');
        $response->assertSee('Tersisa');

        $eligibleStaff = User::factory()->create([
            'role'        => 'staff',
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(8),
        ]);

        $staffResponse = $this->actingAs($this->admin)->get(route('admin.staff.edit', $eligibleStaff));
        $staffResponse->assertStatus(200);
        $staffResponse->assertSee('eligible for deletion');
    }
}
