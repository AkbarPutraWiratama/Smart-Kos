<?php

namespace Tests\Feature\Admin;

use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CashFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $tenant;
    private Location $locationA;
    private Location $locationB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Admin Cash Flow',
            'username' => 'admin_cashflow',
            'email'    => 'admin.cf@test.com',
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Kos',
            'username' => 'staff_cashflow',
            'email'    => 'staff.cf@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenant = User::factory()->create([
            'name'     => 'Penyewa Kos',
            'username' => 'penyewa_cashflow',
            'email'    => 'penyewa.cf@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->locationA = Location::create([
            'name'        => 'Kos Melati',
            'address'     => 'Jl. Melati No. 1',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->locationB = Location::create([
            'name'        => 'Kos Mawar',
            'address'     => 'Jl. Mawar No. 2',
            'floor_count' => 3,
            'status'      => 'active',
        ]);
    }

    public function test_guest_cannot_access_cash_flow_page(): void
    {
        $response = $this->get(route('admin.cash-flow.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_and_tenant_cannot_access_cash_flow_page(): void
    {
        $this->actingAs($this->staff)->get(route('admin.cash-flow.index'))->assertStatus(403);
        $this->actingAs($this->tenant)->get(route('admin.cash-flow.index'))->assertStatus(403);
    }

    public function test_admin_can_access_cash_flow_hub(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cash-flow.index'));

        $response->assertStatus(200);
        $response->assertSee('Visualisasi Arus Kas (Cash Flow)');
        $response->assertSee('Total Arus Masuk');
        $response->assertSee('Total Arus Keluar');
        $response->assertSee('Arus Kas Bersih');
        $response->assertSee('Rincian Arus Kas Per Bulan');
    }

    public function test_cash_flow_calculates_monthly_income_expense_and_net_correctly(): void
    {
        $year = (int) now()->year;

        // January: 2,000,000 income, 500,000 expense -> Net = +1,500,000 (Surplus)
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'description'      => 'Sewa Kamar Jan',
            'amount'           => 2000000,
            'transaction_date' => "{$year}-01-15",
        ]);

        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'expense',
            'description'      => 'Listrik Jan',
            'amount'           => 500000,
            'transaction_date' => "{$year}-01-20",
        ]);

        // February: 1,000,000 income, 3,000,000 expense -> Net = -2,000,000 (Defisit)
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'description'      => 'Sewa Kamar Feb',
            'amount'           => 1000000,
            'transaction_date' => "{$year}-02-10",
        ]);

        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'expense',
            'description'      => 'Perbaikan AC Feb',
            'amount'           => 3000000,
            'transaction_date' => "{$year}-02-18",
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.cash-flow.index', ['year' => $year]));

        $response->assertStatus(200);

        // Total Income: 3,000,000
        $response->assertSee('3.000.000');
        // Total Expense: 3,500,000
        $response->assertSee('3.500.000');
        // Total Net: -500,000
        $response->assertSee('500.000');
    }

    public function test_cash_flow_filters_by_year(): void
    {
        // Record in 2025
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'description'      => 'Income 2025',
            'amount'           => 7500000,
            'transaction_date' => '2025-05-10',
        ]);

        // Record in 2026
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'description'      => 'Income 2026',
            'amount'           => 12000000,
            'transaction_date' => '2026-05-10',
        ]);

        // Query year 2025
        $response2025 = $this->actingAs($this->admin)->get(route('admin.cash-flow.index', ['year' => 2025]));
        $response2025->assertStatus(200);
        $response2025->assertSee('7.500.000');
        $response2025->assertDontSee('12.000.000');

        // Query year 2026
        $response2026 = $this->actingAs($this->admin)->get(route('admin.cash-flow.index', ['year' => 2026]));
        $response2026->assertStatus(200);
        $response2026->assertSee('12.000.000');
        $response2026->assertDontSee('7.500.000');
    }

    public function test_cash_flow_filters_by_location(): void
    {
        $year = (int) now()->year;

        // Location A record
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'description'      => 'Income Melati',
            'amount'           => 4000000,
            'transaction_date' => "{$year}-03-01",
        ]);

        // Location B record
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationB->id,
            'type'             => 'income',
            'description'      => 'Income Mawar',
            'amount'           => 9000000,
            'transaction_date' => "{$year}-03-01",
        ]);

        // Query Location A
        $responseLocA = $this->actingAs($this->admin)->get(route('admin.cash-flow.index', [
            'year'        => $year,
            'location_id' => $this->locationA->id,
        ]));
        $responseLocA->assertStatus(200);
        $responseLocA->assertSee('4.000.000');
        $responseLocA->assertDontSee('9.000.000');

        // Query Location B
        $responseLocB = $this->actingAs($this->admin)->get(route('admin.cash-flow.index', [
            'year'        => $year,
            'location_id' => $this->locationB->id,
        ]));
        $responseLocB->assertStatus(200);
        $responseLocB->assertSee('9.000.000');
        $responseLocB->assertDontSee('4.000.000');
    }

    public function test_cash_flow_highlight_month_selector(): void
    {
        $year = (int) now()->year;

        // April record
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'description'      => 'Income April',
            'amount'           => 6000000,
            'transaction_date' => "{$year}-04-10",
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.cash-flow.index', [
            'year'  => $year,
            'month' => 4,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Proporsi Arus Kas Bulan April ' . $year);
        $response->assertSee('6.000.000');
    }

    public function test_finance_index_tab_redirects_to_cash_flow(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/finance?tab=cash-flow');

        $response->assertRedirect(route('admin.cash-flow.index'));
    }
}
