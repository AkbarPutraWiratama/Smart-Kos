<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantArrearsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $tenantUser;
    private Location $locationA;
    private Location $locationB;
    private Room $roomA1;
    private Room $roomA2;
    private Room $roomB1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Admin Arrears',
            'username' => 'admin_arrears',
            'email'    => 'admin.arr@test.com',
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Arrears',
            'username' => 'staff_arrears',
            'email'    => 'staff.arr@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Penyewa Biasa',
            'username' => 'penyewa_arr',
            'email'    => 'penyewa.arr@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->locationA = Location::create([
            'name'        => 'Kos Cempaka',
            'address'     => 'Jl. Cempaka No. 1',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->locationB = Location::create([
            'name'        => 'Kos Kenanga',
            'address'     => 'Jl. Kenanga No. 2',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->roomA1 = Room::create([
            'location_id' => $this->locationA->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1500000,
            'status'      => 'active',
        ]);

        $this->roomA2 = Room::create([
            'location_id' => $this->locationA->id,
            'floor_name'  => 1,
            'room_number' => 102,
            'rent_amount' => 2000000,
            'status'      => 'active',
        ]);

        $this->roomB1 = Room::create([
            'location_id' => $this->locationB->id,
            'floor_name'  => 2,
            'room_number' => 201,
            'rent_amount' => 1800000,
            'status'      => 'active',
        ]);
    }

    private function createTenantWithProfile(string $name, string $phone = '081234567890'): TenantProfile
    {
        $user = User::factory()->create([
            'name'     => $name,
            'username' => strtolower(str_replace(' ', '_', $name)) . '_' . rand(100, 999),
            'email'    => strtolower(str_replace(' ', '', $name)) . rand(100, 999) . '@test.com',
            'phone'    => $phone,
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        return TenantProfile::create([
            'user_id' => $user->id,
            'phone'   => $phone,
        ]);
    }

    public function test_guest_cannot_access_arrears_page(): void
    {
        $response = $this->get(route('admin.arrears.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_and_tenant_cannot_access_arrears_page(): void
    {
        $this->actingAs($this->staff)->get(route('admin.arrears.index'))->assertStatus(403);
        $this->actingAs($this->tenantUser)->get(route('admin.arrears.index'))->assertStatus(403);
    }

    public function test_admin_can_access_arrears_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.arrears.index'));

        $response->assertStatus(200);
        $response->assertSee('Tunggakan &amp; Piutang Sewa', false);
        $response->assertSee('Total Nominal Piutang');
        $response->assertSee('Penyewa Menunggak');
        $response->assertSee('Keterlambatan Terlama');
    }

    public function test_arrears_identifies_overdue_tenants_and_calculates_amounts(): void
    {
        // Today is day 23 of month
        // Tenant 1: due day = 15 (8 days ago -> overdue)
        $profile1 = $this->createTenantWithProfile('Budi Santoso', '0811111111');
        RoomAssignment::create([
            'room_id'              => $this->roomA1->id,
            'tenant_id'            => $profile1->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 15,
            'rent_amount_snapshot' => 1500000,
            'payment_cycle'        => 'monthly',
            'status'               => 'active',
        ]);

        // Tenant 2: due day = 5 (18 days ago -> more overdue)
        $profile2 = $this->createTenantWithProfile('Dewi Sartika', '0822222222');
        RoomAssignment::create([
            'room_id'              => $this->roomA2->id,
            'tenant_id'            => $profile2->id,
            'start_date'           => now()->subMonths(3)->toDateString(),
            'billing_due_day'      => 5,
            'rent_amount_snapshot' => 2000000,
            'payment_cycle'        => 'monthly',
            'status'               => 'active',
        ]);

        // Tenant 3: due day = 28 (in the future -> NOT overdue)
        $profile3 = $this->createTenantWithProfile('Eko Prasetyo');
        RoomAssignment::create([
            'room_id'              => $this->roomB1->id,
            'tenant_id'            => $profile3->id,
            'start_date'           => now()->subDays(10)->toDateString(),
            'billing_due_day'      => 28,
            'rent_amount_snapshot' => 1800000,
            'payment_cycle'        => 'monthly',
            'status'               => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.arrears.index'));

        $response->assertStatus(200);

        // Verify metrics
        $response->assertViewHas('totalOverdueTenants', 2);
        $response->assertViewHas('totalArrearsAmount', 3500000.0);

        // Overdue tenants are visible
        $response->assertSee('Budi Santoso');
        $response->assertSee('Dewi Sartika');
        $response->assertSee('1.500.000');
        $response->assertSee('2.000.000');

        // Non-overdue tenant is not in arrears
        $response->assertDontSee('Eko Prasetyo');
    }

    public function test_arrears_are_automatically_sorted_from_most_overdue_to_least_overdue(): void
    {
        // Tenant A: due day = 18 (fewer days overdue)
        $profileA = $this->createTenantWithProfile('Tenant Sedikit Terlambat');
        RoomAssignment::create([
            'room_id'              => $this->roomA1->id,
            'tenant_id'            => $profileA->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 18,
            'rent_amount_snapshot' => 1500000,
            'payment_cycle'        => 'monthly',
            'status'               => 'active',
        ]);

        // Tenant B: due day = 3 (most days overdue)
        $profileB = $this->createTenantWithProfile('Tenant Sangat Terlambat');
        RoomAssignment::create([
            'room_id'              => $this->roomA2->id,
            'tenant_id'            => $profileB->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 3,
            'rent_amount_snapshot' => 2000000,
            'payment_cycle'        => 'monthly',
            'status'               => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.arrears.index'));

        $response->assertStatus(200);

        $arrearsList = $response->viewData('arrearsList');

        // Automatic sort: most overdue (Tenant B, day 3) first, least overdue (Tenant A, day 18) last
        $this->assertEquals('Tenant Sangat Terlambat', $arrearsList->first()['tenant_name']);
        $this->assertGreaterThan($arrearsList->last()['days_overdue'], $arrearsList->first()['days_overdue']);

        $this->assertEquals('Tenant Sedikit Terlambat', $arrearsList->last()['tenant_name']);
    }

    public function test_arrears_can_be_filtered_by_location(): void
    {
        // Overdue in Location A (due day = 10)
        $profileA = $this->createTenantWithProfile('Penyewa Cempaka');
        RoomAssignment::create([
            'room_id'              => $this->roomA1->id,
            'tenant_id'            => $profileA->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 10,
            'rent_amount_snapshot' => 1500000,
            'status'               => 'active',
        ]);

        // Overdue in Location B (due day = 8)
        $profileB = $this->createTenantWithProfile('Penyewa Kenanga');
        RoomAssignment::create([
            'room_id'              => $this->roomB1->id,
            'tenant_id'            => $profileB->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 8,
            'rent_amount_snapshot' => 1800000,
            'status'               => 'active',
        ]);

        // Filter Location A
        $responseLocA = $this->actingAs($this->admin)->get(route('admin.arrears.index', [
            'location_id' => $this->locationA->id,
        ]));

        $responseLocA->assertStatus(200);
        $responseLocA->assertSee('Penyewa Cempaka');
        $responseLocA->assertDontSee('Penyewa Kenanga');

        // Filter Location B
        $responseLocB = $this->actingAs($this->admin)->get(route('admin.arrears.index', [
            'location_id' => $this->locationB->id,
        ]));

        $responseLocB->assertStatus(200);
        $responseLocB->assertSee('Penyewa Kenanga');
        $responseLocB->assertDontSee('Penyewa Cempaka');
    }

    public function test_paid_tenant_or_pending_verification_is_not_counted_in_arrears(): void
    {
        // Assignment overdue by due day, but has approved payment
        $profilePaid = $this->createTenantWithProfile('Penyewa Sudah Bayar');
        $assignmentPaid = RoomAssignment::create([
            'room_id'              => $this->roomA1->id,
            'tenant_id'            => $profilePaid->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 10,
            'rent_amount_snapshot' => 1500000,
            'status'               => 'active',
        ]);

        ManualPayment::create([
            'room_assignment_id'    => $assignmentPaid->id,
            'submitted_by'          => $profilePaid->user_id,
            'verified_by'           => $this->admin->id,
            'bank_name'             => 'BCA',
            'bank_account_number'   => '1234567890',
            'bank_account_holder'   => 'Budi Santoso',
            'proof_path'            => 'proofs/receipt1.jpg',
            'amount'                => 1500000,
            'status'                => 'approved',
            'submitted_at'          => now()->subDays(2),
            'verified_at'           => now()->subDays(1),
        ]);

        // Assignment overdue by due day, but has pending verification
        $profilePending = $this->createTenantWithProfile('Penyewa Pending Verifikasi');
        $assignmentPending = RoomAssignment::create([
            'room_id'              => $this->roomA2->id,
            'tenant_id'            => $profilePending->id,
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 10,
            'rent_amount_snapshot' => 2000000,
            'status'               => 'active',
        ]);

        ManualPayment::create([
            'room_assignment_id'    => $assignmentPending->id,
            'submitted_by'          => $profilePending->user_id,
            'bank_name'             => 'Mandiri',
            'bank_account_number'   => '0987654321',
            'bank_account_holder'   => 'Dewi Sartika',
            'proof_path'            => 'proofs/receipt2.jpg',
            'amount'                => 2000000,
            'status'                => 'pending',
            'submitted_at'          => now()->subHours(5),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.arrears.index'));

        $response->assertStatus(200);
        $response->assertViewHas('totalOverdueTenants', 0);
        $response->assertSee('Tidak Ada Tunggakan Sewa Aktif');
    }

    public function test_finance_tab_redirects_to_arrears(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/finance?tab=arrears');

        $response->assertRedirect(route('admin.arrears.index'));
    }
}
