<?php

namespace Tests\Feature\Admin;

use App\Models\FinancialRecord;
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

class FinancialRecapTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $tenantUser;
    private Location $location;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Admin Recap',
            'username' => 'admin_recap',
            'email'    => 'admin.recap@test.com',
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Recap',
            'username' => 'staff_recap',
            'email'    => 'staff.recap@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Penyewa Recap',
            'username' => 'penyewa_recap',
            'email'    => 'penyewa.recap@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Melati',
            'address'     => 'Jl. Melati No. 5',
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
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Access Control
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_guest_cannot_access_financial_recap_page(): void
    {
        $response = $this->get(route('admin.financial-recap.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_staff_cannot_access_financial_recap_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.financial-recap.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_tenant_cannot_access_financial_recap_page(): void
    {
        $response = $this->actingAs($this->tenantUser)->get(route('admin.financial-recap.index'));

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Basic Page Load
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_admin_can_access_financial_recap_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Rekapitulasi Keuangan');
        $response->assertSeeText('Total Pembayaran Diterima');
        $response->assertSeeText('Total Piutang Tertunggak');
        $response->assertSeeText('Total Beban Operasional');
        $response->assertSeeText('Net Amount');
    }

    /** @test */
    public function test_financial_recap_defaults_to_tenant_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index'));

        $response->assertStatus(200);
        $response->assertViewHas('viewMode', 'tenant');
    }

    /** @test */
    public function test_financial_recap_can_switch_to_room_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index', ['view' => 'room']));

        $response->assertStatus(200);
        $response->assertViewHas('viewMode', 'room');
        $response->assertSeeText('Rekap Keuangan per Kamar');
    }

    /** @test */
    public function test_financial_recap_invalid_view_param_falls_back_to_tenant(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index', ['view' => 'invalid']));

        $response->assertStatus(200);
        $response->assertViewHas('viewMode', 'tenant');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Global Metric Calculations
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_financial_recap_calculates_global_metrics_correctly(): void
    {
        // Create income and expense records
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'amount'           => 2000000,
            'description'      => 'Pembayaran sewa',
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => 'expense',
            'reference_type'   => 'expense',
            'amount'           => 500000,
            'description'      => 'Listrik',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index'));

        $response->assertStatus(200);
        $response->assertViewHas('globalTotalPayments', 2000000.0);
        $response->assertViewHas('globalTotalExpenses', 500000.0);
        $response->assertViewHas('globalNetAmount', 1500000.0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Tenant Recap Calculations
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_tenant_recap_calculates_total_payments_correctly(): void
    {
        $tenantProfile = TenantProfile::create([
            'user_id'         => $this->tenantUser->id,
            'nik'             => '1234567890123456',
            'phone'           => '081234567890',
            'emergency_name'  => 'Kontak Darurat',
            'emergency_phone' => '089876543210',
        ]);

        $assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $tenantProfile->id,
            'status'               => 'active',
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 15,
            'rent_amount_snapshot' => 1500000,
        ]);

        ManualPayment::create([
            'room_assignment_id'  => $assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Penyewa Recap',
            'proof_path'          => 'proofs/test.jpg',
            'amount'              => 1500000,
            'status'              => 'approved',
            'submitted_at'        => now()->subDays(5),
            'verified_at'         => now()->subDays(4),
            'verified_by'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index'));

        $response->assertStatus(200);
        $response->assertViewHas('viewMode', 'tenant');

        $tenantRecap = $response->viewData('tenantRecap');
        $this->assertNotEmpty($tenantRecap);

        $found = $tenantRecap->first(fn ($t) => $t['tenant_name'] === 'Penyewa Recap');
        $this->assertNotNull($found, 'Expected to find Penyewa Recap in tenantRecap');
        $this->assertEquals(1500000.0, $found['total_payments']);
        $this->assertEquals(0.0, $found['total_expenses']);
        $this->assertEquals(1500000.0, $found['net_amount']);
    }

    /** @test */
    public function test_tenant_recap_shows_correct_table_headers_in_tenant_mode(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Rekap Keuangan per Penyewa');
        $response->assertSeeText('Total Pembayaran');
        $response->assertSeeText('Piutang Tertunggak');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Room Recap Calculations
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_room_recap_calculates_total_payments_correctly(): void
    {
        $tenantProfile = TenantProfile::create([
            'user_id'         => $this->tenantUser->id,
            'nik'             => '1234567890123456',
            'phone'           => '081234567890',
            'emergency_name'  => 'Kontak Darurat',
            'emergency_phone' => '089876543210',
        ]);

        $assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $tenantProfile->id,
            'status'               => 'active',
            'start_date'           => now()->subMonths(2)->toDateString(),
            'billing_due_day'      => 15,
            'rent_amount_snapshot' => 1500000,
        ]);

        ManualPayment::create([
            'room_assignment_id'  => $assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Penyewa Recap',
            'proof_path'          => 'proofs/test.jpg',
            'amount'              => 1500000,
            'status'              => 'approved',
            'submitted_at'        => now()->subDays(5),
            'verified_at'         => now()->subDays(4),
            'verified_by'         => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index', ['view' => 'room']));

        $response->assertStatus(200);
        $response->assertViewHas('viewMode', 'room');

        $roomRecap = $response->viewData('roomRecap');
        $this->assertNotEmpty($roomRecap);

        $found = $roomRecap->first(fn ($r) => $r['room_number'] == 101);
        $this->assertNotNull($found, 'Expected to find room 101 in roomRecap');
        $this->assertEquals(1500000.0, $found['total_payments']);
        $this->assertTrue($found['is_occupied']);
        $this->assertEquals('Penyewa Recap', $found['current_tenant']);
    }

    /** @test */
    public function test_room_recap_shows_correct_table_headers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index', ['view' => 'room']));

        $response->assertStatus(200);
        $response->assertSeeText('Rekap Keuangan per Kamar');
        $response->assertSeeText('Beban Alokasi');
        $response->assertSeeText('Net Amount');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Filtering
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_financial_recap_filters_by_location(): void
    {
        $otherLocation = Location::create([
            'name'        => 'Kos Anggrek',
            'address'     => 'Jl. Anggrek No. 9',
            'floor_count' => 1,
            'status'      => 'active',
        ]);

        $otherRoom = Room::create([
            'location_id' => $otherLocation->id,
            'floor_name'  => 1,
            'room_number' => 201,
            'rent_amount' => 2000000,
            'status'      => 'active',
        ]);

        // Income in this->location
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'amount'           => 1500000,
            'description'      => 'Bayar kamar 101',
            'transaction_date' => now()->toDateString(),
        ]);

        // Income in other location
        FinancialRecord::create([
            'location_id'      => $otherLocation->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'amount'           => 2000000,
            'description'      => 'Bayar kamar 201',
            'transaction_date' => now()->toDateString(),
        ]);

        // Filter only this->location
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index', [
            'location_id' => $this->location->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('globalTotalPayments', 1500000.0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  View Data Shapes
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_financial_recap_passes_locations_to_view(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index'));

        $response->assertStatus(200);
        $response->assertViewHas('locations');

        $locations = $response->viewData('locations');
        $this->assertCount(1, $locations); // Only this->location was created as 'active'
    }

    /** @test */
    public function test_financial_recap_passes_all_required_view_data(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.financial-recap.index'));

        $response->assertStatus(200);
        $response->assertViewHasAll([
            'viewMode',
            'tenantRecap',
            'roomRecap',
            'globalTotalPayments',
            'globalTotalOutstanding',
            'globalTotalExpenses',
            'globalNetAmount',
            'locations',
            'selectedLocationId',
            'search',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Finance Tab Redirect
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_finance_tab_recap_redirects_to_financial_recap_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', ['tab' => 'recap']));

        $response->assertRedirect(route('admin.financial-recap.index'));
    }
}
