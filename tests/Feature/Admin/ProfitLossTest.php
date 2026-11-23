<?php

namespace Tests\Feature\Admin;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfitLossTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $tenant;
    private Location $locationA;
    private Location $locationB;
    private ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Admin PnL',
            'username' => 'admin_pnl',
            'email'    => 'admin.pnl@test.com',
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff PnL',
            'username' => 'staff_pnl',
            'email'    => 'staff.pnl@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenant = User::factory()->create([
            'name'     => 'Penyewa PnL',
            'username' => 'penyewa_pnl',
            'email'    => 'penyewa.pnl@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->locationA = Location::create([
            'name'        => 'Kos Sakura',
            'address'     => 'Jl. Sakura No. 1',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->locationB = Location::create([
            'name'        => 'Kos Anggrek',
            'address'     => 'Jl. Anggrek No. 2',
            'floor_count' => 3,
            'status'      => 'active',
        ]);

        $this->category = ExpenseCategory::create([
            'name' => 'Operasional Listrik',
            'type' => 'operational',
        ]);
    }

    public function test_guest_cannot_access_profit_loss_page(): void
    {
        $response = $this->get(route('admin.profit-loss.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_and_tenant_cannot_access_profit_loss_page(): void
    {
        $this->actingAs($this->staff)->get(route('admin.profit-loss.index'))->assertStatus(403);
        $this->actingAs($this->tenant)->get(route('admin.profit-loss.index'))->assertStatus(403);
    }

    public function test_admin_can_access_profit_loss_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.profit-loss.index'));

        $response->assertStatus(200);
        $response->assertSee('Laporan Laba Rugi (Profit &amp; Loss)', false);
        $response->assertSee('Total Pendapatan');
        $response->assertSee('Total Beban Operasional');
        $response->assertSee('Margin Laba Bersih');
    }

    public function test_profit_loss_calculates_profit_correctly_at_application_layer(): void
    {
        $currentMonth = now()->format('Y-m');

        // Create income: 30,000,000 (Example matching STEPBYSTEP.md)
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'reference_type'   => 'va_payment',
            'description'      => 'Sewa Kamar 101',
            'amount'           => 30000000,
            'transaction_date' => now()->startOfMonth()->toDateString(),
        ]);

        // Create expense: 10,000,000 (Example matching STEPBYSTEP.md)
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'expense',
            'reference_type'   => 'expense',
            'description'      => 'Biaya Listrik',
            'amount'           => 10000000,
            'transaction_date' => now()->startOfMonth()->addDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.profit-loss.index', [
            'time_scope' => 'month',
            'month'      => $currentMonth,
        ]));

        $response->assertStatus(200);

        // Verify calculations from application layer in view data
        $response->assertViewHas('totalIncome', 30000000.0);
        $response->assertViewHas('totalExpenses', 10000000.0);
        $response->assertViewHas('netProfitOrLoss', 20000000.0);
        $response->assertViewHas('isProfit', true);
        $response->assertViewHas('profitMargin', 66.7);

        // Verify rendered values
        $response->assertSee('30.000.000');
        $response->assertSee('10.000.000');
        $response->assertSee('+Rp 20.000.000');
        $response->assertSee('Laba Bersih Operasional');
    }

    public function test_profit_loss_calculates_loss_correctly_at_application_layer(): void
    {
        $currentMonth = now()->format('Y-m');

        // Income: 2,000,000
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'description'      => 'Sewa Kamar',
            'amount'           => 2000000,
            'transaction_date' => now()->startOfMonth()->toDateString(),
        ]);

        // Expenses: 5,000,000
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'expense',
            'reference_type'   => 'expense',
            'description'      => 'Perbaikan Darurat',
            'amount'           => 5000000,
            'transaction_date' => now()->startOfMonth()->addDays(2)->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.profit-loss.index', [
            'time_scope' => 'month',
            'month'      => $currentMonth,
        ]));

        $response->assertStatus(200);

        // Verify application-layer calculation
        $response->assertViewHas('totalIncome', 2000000.0);
        $response->assertViewHas('totalExpenses', 5000000.0);
        $response->assertViewHas('netProfitOrLoss', -3000000.0);
        $response->assertViewHas('isLoss', true);

        $response->assertSee('Rugi Bersih Operasional');
        $response->assertSee('-Rp 3.000.000');
    }

    public function test_profit_loss_filters_by_month(): void
    {
        $targetMonth = '2026-04';

        // Target month record
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'reference_type'   => 'va_payment',
            'description'      => 'Income April',
            'amount'           => 15000000,
            'transaction_date' => '2026-04-10',
        ]);

        // Other month record
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'reference_type'   => 'va_payment',
            'description'      => 'Income Mei',
            'amount'           => 25000000,
            'transaction_date' => '2026-05-10',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.profit-loss.index', [
            'time_scope' => 'month',
            'month'      => $targetMonth,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('totalIncome', 15000000.0);
        $response->assertSee('15.000.000');
        $response->assertDontSee('25.000.000');
    }

    public function test_profit_loss_filters_by_year(): void
    {
        // 2025 record
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'reference_type'   => 'va_payment',
            'description'      => 'Income 2025',
            'amount'           => 8000000,
            'transaction_date' => '2025-06-15',
        ]);

        // 2026 record
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'reference_type'   => 'va_payment',
            'description'      => 'Income 2026',
            'amount'           => 18000000,
            'transaction_date' => '2026-06-15',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.profit-loss.index', [
            'time_scope' => 'year',
            'year'       => 2025,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('totalIncome', 8000000.0);
        $response->assertSee('8.000.000');
        $response->assertDontSee('18.000.000');
    }

    public function test_profit_loss_filters_by_location(): void
    {
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationA->id,
            'type'             => 'income',
            'reference_type'   => 'va_payment',
            'description'      => 'Income Sakura',
            'amount'           => 11000000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'location_id'      => $this->locationB->id,
            'type'             => 'income',
            'reference_type'   => 'va_payment',
            'description'      => 'Income Anggrek',
            'amount'           => 22000000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.profit-loss.index', [
            'location_id' => $this->locationA->id,
            'time_scope'  => 'month',
            'month'       => now()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('totalIncome', 11000000.0);
        $response->assertSee('11.000.000');
        $response->assertDontSee('22.000.000');
    }

    public function test_finance_tab_redirects_to_profit_loss(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/finance?tab=profit-loss');

        $response->assertRedirect(route('admin.profit-loss.index'));
    }
}
