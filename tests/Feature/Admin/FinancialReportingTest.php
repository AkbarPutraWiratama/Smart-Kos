<?php

namespace Tests\Feature\Admin;

use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\MidtransVAAccount;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\VAPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinancialReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $tenantUser;

    private TenantProfile $tenantProfile;

    private Location $location;

    private Room $room;

    private RoomAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'name'     => 'Admin Keuangan',
            'username' => 'admin_finance_test',
            'email'    => 'admin.finance@test.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Kos',
            'username' => 'staff_finance_test',
            'email'    => 'staff.finance@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Budi Santoso',
            'username' => 'budi_tenant',
            'email'    => 'budi@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone'   => '081298765432',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Melati Test',
            'address'     => 'Jl. Melati No. 5',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1500000,
            'status'      => 'occupied',
        ]);

        $this->assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => now()->subDays(30)->toDateString(),
            'status'               => 'active',
            'rent_amount_snapshot' => 1500000,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 1. Access Control
    // ─────────────────────────────────────────────────────────────────

    public function test_guest_cannot_access_admin_finance_page(): void
    {
        $response = $this->get(route('admin.finance.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_admin_finance_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.finance.index'));

        $response->assertForbidden();
    }

    public function test_tenant_cannot_access_admin_finance_page(): void
    {
        $response = $this->actingAs($this->tenantUser)->get(route('admin.finance.index'));

        $response->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────────
    // 2. Admin Can View Finance Dashboard
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_finance_dashboard_with_summary_metrics(): void
    {
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 1,
            'description'      => 'Pembayaran sewa VA Midtrans',
            'amount'           => 1500000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => FinancialRecord::TYPE_EXPENSE,
            'reference_type'   => 'expense',
            'reference_id'     => 1,
            'description'      => 'Biaya listrik',
            'amount'           => 300000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));

        $response->assertOk();
        $response->assertViewIs('admin.finance.index');
        $response->assertViewHas('totalIncome');
        $response->assertViewHas('totalExpense');
        $response->assertViewHas('netProfit');
        $response->assertViewHas('totalTransactions');
        $response->assertViewHas('monthlyReport');
        $response->assertViewHas('locationReport');
        $response->assertViewHas('roomReport');
        $response->assertViewHas('roomSummary');
        $response->assertViewHas('tenantReport');
        $response->assertViewHas('tenantSummary');
        $response->assertViewHas('records');
    }

    public function test_finance_dashboard_shows_correct_pl_metrics(): void
    {
        $year  = now()->year;
        $month = now()->month;

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 10,
            'description'      => 'Pemasukan VA',
            'amount'           => 2000000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => FinancialRecord::TYPE_EXPENSE,
            'reference_type'   => 'expense',
            'reference_id'     => 10,
            'description'      => 'Pengeluaran operasional',
            'amount'           => 500000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'year'  => $year,
            'month' => $month,
        ]));

        $response->assertOk();

        $viewData = $response->original->getData();
        $this->assertEquals(2000000.0, $viewData['totalIncome']);
        $this->assertEquals(500000.0, $viewData['totalExpense']);
        $this->assertEquals(1500000.0, $viewData['netProfit']);
        $this->assertEquals(2, $viewData['totalTransactions']);
    }

    public function test_finance_dashboard_provides_cash_flow_visualization_data_and_chart_series(): void
    {
        $year = now()->year;

        // Create income in January and expense in February of current year
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => FinancialRecord::TYPE_INCOME,
            'description'      => 'Sewa Januari',
            'amount'           => 3000000,
            'transaction_date' => "{$year}-01-15",
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => FinancialRecord::TYPE_EXPENSE,
            'description'      => 'Listrik Februari',
            'amount'           => 1000000,
            'transaction_date' => "{$year}-02-10",
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', ['year' => $year, 'month' => 'all']));

        $response->assertOk();
        $response->assertViewHas('chartCategories');
        $response->assertViewHas('chartIncome');
        $response->assertViewHas('chartExpense');
        $response->assertViewHas('chartNet');
        $response->assertViewHas('cashFlowSummary');

        $data = $response->original->getData();

        // 12 months array verification
        $this->assertCount(12, $data['chartCategories']);
        $this->assertCount(12, $data['chartIncome']);
        $this->assertCount(12, $data['chartExpense']);

        // Month 1 (Jan) income must be 3,000,000; Month 2 (Feb) expense must be 1,000,000
        $this->assertEquals(3000000.0, $data['chartIncome'][0]);
        $this->assertEquals(1000000.0, $data['chartExpense'][1]);

        // Cash flow summary metrics
        $summary = $data['cashFlowSummary'];
        $this->assertEquals(3000000.0, $summary['total_income']);
        $this->assertEquals(1000000.0, $summary['total_expense']);
        $this->assertEquals(2000000.0, $summary['net_flow']);
        $this->assertEquals(75.0, $summary['income_percentage']); // 3M / 4M = 75%
        $this->assertEquals(25.0, $summary['expense_percentage']); // 1M / 4M = 25%

        // View assertions: contains cashFlowMonthlyChart and proportion bars
        $response->assertSee('id="cashFlowMonthlyChart"', false);
        $response->assertSee('Visualisasi Arus Kas Bulanan (Monthly Cash Flow)');
        $response->assertSee('Rasio Arus Kas (Cash Flow Proportion)');
        $response->assertSee('75%');
        $response->assertSee('25%');
    }

    public function test_cash_flow_visualization_reflects_location_filter(): void
    {
        $year = now()->year;

        $otherLocation = Location::create([
            'name'        => 'Kos Mawar Lain',
            'address'     => 'Jl. Mawar No. 8',
            'floor_count' => 1,
            'status'      => 'active',
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => FinancialRecord::TYPE_INCOME,
            'description'      => 'Pendapatan Lokasi Melati',
            'amount'           => 1800000,
            'transaction_date' => "{$year}-03-05",
        ]);

        FinancialRecord::create([
            'location_id'      => $otherLocation->id,
            'type'             => FinancialRecord::TYPE_INCOME,
            'description'      => 'Pendapatan Lokasi Mawar',
            'amount'           => 5000000,
            'transaction_date' => "{$year}-03-05",
        ]);

        // Filter by location 1 only
        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'location_id' => $this->location->id,
            'year'        => $year,
            'month'       => 'all',
        ]));

        $response->assertOk();
        $data = $response->original->getData();

        // Income for March (index 2) must only reflect location 1 (1,800,000, not 6,800,000)
        $this->assertEquals(1800000.0, $data['chartIncome'][2]);
        $this->assertEquals(1800000.0, $data['cashFlowSummary']['total_income']);
    }

    // ─────────────────────────────────────────────────────────────────
    // 3. Auto-Sync: VA Payment → Financial Record
    // ─────────────────────────────────────────────────────────────────

    public function test_settled_va_payment_automatically_creates_financial_record(): void
    {
        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '1234567890',
            'external_id' => 'VA-EXT-TEST-001',
            'amount'      => 1500000,
            'status'      => 'active',
        ]);

        $vaPayment = VAPayment::create([
            'va_account_id'      => $vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id'           => 'ORDER-TEST-001',
            'transaction_id'     => 'TXN-TEST-001',
            'amount'             => 1500000,
            'transaction_status' => VAPayment::STATUS_SETTLEMENT,
            'transaction_time'   => now(),
            'paid_at'            => now(),
        ]);

        $record = FinancialRecord::recordFromVAPayment($vaPayment);

        $this->assertNotNull($record);
        $this->assertEquals(FinancialRecord::TYPE_INCOME, $record->type);
        $this->assertEquals('va_payment', $record->reference_type);
        $this->assertEquals($vaPayment->id, $record->reference_id);
        $this->assertEquals(1500000, (float) $record->amount);

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'va_payment',
            'reference_id'   => $vaPayment->id,
            'type'           => FinancialRecord::TYPE_INCOME,
        ]);
    }

    public function test_pending_va_payment_does_not_create_financial_record(): void
    {
        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '1234567891',
            'external_id' => 'VA-EXT-TEST-002',
            'amount'      => 1000000,
            'status'      => 'active',
        ]);

        $vaPayment = VAPayment::create([
            'va_account_id'      => $vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id'           => 'ORDER-PENDING-001',
            'transaction_id'     => 'TXN-PENDING-001',
            'amount'             => 1000000,
            'transaction_status' => VAPayment::STATUS_PENDING,
            'transaction_time'   => now(),
            'paid_at'            => null,
        ]);

        $result = FinancialRecord::recordFromVAPayment($vaPayment);

        $this->assertNull($result);
        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'va_payment',
            'reference_id'   => $vaPayment->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 4. Auto-Sync: Manual Payment → Financial Record
    // ─────────────────────────────────────────────────────────────────

    public function test_approved_manual_payment_automatically_creates_financial_record(): void
    {
        $fakeFile  = UploadedFile::fake()->create('bukti_transfer.pdf', 100, 'application/pdf');
        $proofPath = $fakeFile->store('manual-payments', 'public');

        $manualPayment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BNI',
            'bank_account_number' => '8765432109',
            'proof_path'          => $proofPath,
            'status'              => ManualPayment::STATUS_APPROVED,
            'verified_by'         => $this->admin->id,
            'submitted_at'        => now()->subDay(),
            'verified_at'         => now(),
        ]);

        $record = FinancialRecord::recordFromManualPayment($manualPayment, $this->admin->id);

        $this->assertNotNull($record);
        $this->assertEquals(FinancialRecord::TYPE_INCOME, $record->type);
        $this->assertEquals('manual_payment', $record->reference_type);
        $this->assertEquals($manualPayment->id, $record->reference_id);
        $this->assertEquals(1200000, (float) $record->amount);
        $this->assertEquals($this->admin->id, $record->recorded_by);

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'manual_payment',
            'reference_id'   => $manualPayment->id,
            'type'           => FinancialRecord::TYPE_INCOME,
        ]);
    }

    public function test_pending_manual_payment_does_not_create_financial_record(): void
    {
        $fakeFile  = UploadedFile::fake()->create('bukti_pending.pdf', 100, 'application/pdf');
        $proofPath = $fakeFile->store('manual-payments', 'public');

        $manualPayment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BRI',
            'bank_account_number' => '123456789',
            'proof_path'          => $proofPath,
            'status'              => ManualPayment::STATUS_PENDING,
            'verified_by'         => null,
            'submitted_at'        => now(),
            'verified_at'         => null,
        ]);

        $result = FinancialRecord::recordFromManualPayment($manualPayment);

        $this->assertNull($result);
        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'manual_payment',
            'reference_id'   => $manualPayment->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 5. Finance Dashboard Filtering
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_filter_finance_report_by_location(): void
    {
        $otherLocation = Location::create([
            'name'        => 'Kos Anggrek Test',
            'address'     => 'Jl. Anggrek No. 7',
            'floor_count' => 1,
            'status'      => 'active',
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 100,
            'description'      => 'Income at Melati',
            'amount'           => 1500000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $otherLocation->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 101,
            'description'      => 'Income at Anggrek',
            'amount'           => 800000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'location_id' => $this->location->id,
            'year'        => now()->year,
            'month'       => now()->month,
        ]));

        $response->assertOk();

        $viewData = $response->original->getData();
        $this->assertEquals(1500000.0, $viewData['totalIncome']);
    }

    public function test_admin_can_filter_finance_report_by_year_and_month(): void
    {
        // Record for current month
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 200,
            'description'      => 'Current month income',
            'amount'           => 2000000,
            'transaction_date' => now()->toDateString(),
        ]);

        // Record for last year (should not appear)
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 201,
            'description'      => 'Old income',
            'amount'           => 1000000,
            'transaction_date' => now()->subYear()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'year'  => now()->year,
            'month' => now()->month,
        ]));

        $response->assertOk();

        $viewData = $response->original->getData();
        // Should only count current month's income
        $this->assertEquals(2000000.0, $viewData['totalIncome']);
    }

    public function test_admin_can_filter_finance_report_by_type(): void
    {
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 300,
            'description'      => 'Income record',
            'amount'           => 3000000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => FinancialRecord::TYPE_EXPENSE,
            'reference_type'   => 'expense',
            'reference_id'     => 300,
            'description'      => 'Expense record',
            'amount'           => 400000,
            'transaction_date' => now()->toDateString(),
        ]);

        // Filter by income only
        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'type'  => 'income',
            'year'  => now()->year,
            'month' => now()->month,
        ]));

        $response->assertOk();

        $viewData = $response->original->getData();
        // All paginated records must be income type
        $records = $viewData['records'];
        $this->assertTrue($records->every(fn ($r) => $r->type === FinancialRecord::TYPE_INCOME));
    }

    // ─────────────────────────────────────────────────────────────────
    // 6. Monthly Cash Flow Breakdown
    // ─────────────────────────────────────────────────────────────────

    public function test_finance_dashboard_includes_12_month_cash_flow_breakdown(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));

        $response->assertOk();

        $viewData      = $response->original->getData();
        $monthlyReport = $viewData['monthlyReport'];

        // Should always have exactly 12 months
        $this->assertCount(12, $monthlyReport);

        // Each month entry must have required keys
        foreach ($monthlyReport as $monthData) {
            $this->assertArrayHasKey('month_number', $monthData);
            $this->assertArrayHasKey('month_name', $monthData);
            $this->assertArrayHasKey('income', $monthData);
            $this->assertArrayHasKey('expense', $monthData);
            $this->assertArrayHasKey('net', $monthData);
            $this->assertArrayHasKey('status', $monthData);
        }
    }

    public function test_monthly_cash_flow_surplus_status_calculated_correctly(): void
    {
        $year  = now()->year;
        $month = now()->month;

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 400,
            'description'      => 'Surplus income',
            'amount'           => 5000000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => FinancialRecord::TYPE_EXPENSE,
            'reference_type'   => 'expense',
            'reference_id'     => 400,
            'description'      => 'Expense',
            'amount'           => 2000000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'year' => $year,
        ]));

        $response->assertOk();

        $viewData      = $response->original->getData();
        $monthlyReport = $viewData['monthlyReport'];

        $currentMonthData = collect($monthlyReport)->firstWhere('month_number', $month);
        $this->assertNotNull($currentMonthData);
        $this->assertEquals('Surplus', $currentMonthData['status']);
        $this->assertEquals(3000000.0, $currentMonthData['net']);
    }

    // ─────────────────────────────────────────────────────────────────
    // 7. Location Performance Breakdown
    // ─────────────────────────────────────────────────────────────────

    public function test_finance_dashboard_includes_location_performance_breakdown(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));

        $response->assertOk();

        $viewData       = $response->original->getData();
        $locationReport = $viewData['locationReport'];

        $this->assertIsArray($locationReport);

        foreach ($locationReport as $locData) {
            $this->assertArrayHasKey('location_id', $locData);
            $this->assertArrayHasKey('location_name', $locData);
            $this->assertArrayHasKey('income', $locData);
            $this->assertArrayHasKey('expense', $locData);
            $this->assertArrayHasKey('net', $locData);
            $this->assertArrayHasKey('contribution', $locData);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // 8. FinancialRecord Model Helpers
    // ─────────────────────────────────────────────────────────────────

    public function test_financial_record_model_helpers_return_correct_values(): void
    {
        $incomeRecord = FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => null,
            'type'             => FinancialRecord::TYPE_INCOME,
            'reference_type'   => 'va_payment',
            'reference_id'     => 500,
            'description'      => 'Test income record',
            'amount'           => 1750000,
            'transaction_date' => now()->toDateString(),
        ]);

        $expenseRecord = FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => FinancialRecord::TYPE_EXPENSE,
            'reference_type'   => 'expense',
            'reference_id'     => 500,
            'description'      => 'Test expense record',
            'amount'           => 350000,
            'transaction_date' => now()->toDateString(),
        ]);

        // isIncome / isExpense
        $this->assertTrue($incomeRecord->isIncome());
        $this->assertFalse($incomeRecord->isExpense());
        $this->assertFalse($expenseRecord->isIncome());
        $this->assertTrue($expenseRecord->isExpense());

        // typeLabel
        $this->assertEquals('Pemasukan', $incomeRecord->typeLabel());
        $this->assertEquals('Pengeluaran', $expenseRecord->typeLabel());

        // formattedAmount
        $this->assertEquals('Rp 1.750.000', $incomeRecord->formattedAmount());
        $this->assertEquals('Rp 350.000', $expenseRecord->formattedAmount());

        // typeBadgeClass — income returns success class, expense returns error class
        $this->assertStringContainsString('success', $incomeRecord->typeBadgeClass());
        $this->assertStringContainsString('error', $expenseRecord->typeBadgeClass());
    }

    public function test_financial_record_idempotent_sync_does_not_create_duplicates(): void
    {
        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '9876543210',
            'external_id' => 'VA-EXT-DUP-001',
            'amount'      => 1500000,
            'status'      => 'active',
        ]);

        $vaPayment = VAPayment::create([
            'va_account_id'      => $vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id'           => 'ORDER-DUP-001',
            'transaction_id'     => 'TXN-DUP-001',
            'amount'             => 1500000,
            'transaction_status' => VAPayment::STATUS_SETTLEMENT,
            'transaction_time'   => now(),
            'paid_at'            => now(),
        ]);

        // Call twice — should use updateOrCreate (idempotent)
        FinancialRecord::recordFromVAPayment($vaPayment);
        FinancialRecord::recordFromVAPayment($vaPayment);

        $count = FinancialRecord::where('reference_type', 'va_payment')
            ->where('reference_id', $vaPayment->id)
            ->count();

        $this->assertEquals(1, $count);
    }

    // ─────────────────────────────────────────────────────────────────
    // 9. Comprehensive Room and Tenant Financial Recaps
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_room_financial_recap_with_occupancy_and_payment_status(): void
    {
        // Empty room
        $emptyRoom = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 102,
            'rent_amount' => 1200000,
            'status'      => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', ['tab' => 'rooms']));

        $response->assertOk();
        $response->assertViewHas('roomReport');
        $response->assertViewHas('roomSummary');

        $viewData = $response->original->getData();
        $roomReport = $viewData['roomReport'];
        $roomSummary = $viewData['roomSummary'];

        $this->assertEquals(2, $roomSummary['total_rooms']);
        $this->assertEquals(1, $roomSummary['occupied_rooms']);
        $this->assertEquals(1, $roomSummary['empty_rooms']);

        // Check occupied room details
        $occupiedData = $roomReport->firstWhere('room_id', $this->room->id);
        $this->assertNotNull($occupiedData);
        $this->assertTrue($occupiedData['is_occupied']);
        $this->assertEquals('Budi Santoso', $occupiedData['tenant_name']);
        $this->assertEquals(1500000.0, (float) $occupiedData['rent_amount']);

        // Check empty room details
        $emptyData = $roomReport->firstWhere('room_id', $emptyRoom->id);
        $this->assertNotNull($emptyData);
        $this->assertFalse($emptyData['is_occupied']);
        $this->assertEquals('Kosong', $emptyData['payment_status']);
        $this->assertEquals(0.0, $emptyData['outstanding']);
    }

    public function test_admin_can_view_tenant_financial_recap_with_payment_history_and_arrears(): void
    {
        // Seed VA payment for this tenant's assignment
        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '1122334455',
            'external_id' => 'VA-EXT-TENANT-001',
            'amount'      => 1500000,
            'status'      => 'active',
        ]);

        VAPayment::create([
            'va_account_id'      => $vaAccount->id,
            'room_assignment_id' => $this->assignment->id,
            'order_id'           => 'ORDER-TENANT-001',
            'transaction_id'     => 'TXN-TENANT-001',
            'amount'             => 1500000,
            'transaction_status' => VAPayment::STATUS_SETTLEMENT,
            'transaction_time'   => now(),
            'paid_at'            => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', ['tab' => 'tenants']));

        $response->assertOk();
        $response->assertViewHas('tenantReport');
        $response->assertViewHas('tenantSummary');

        $viewData = $response->original->getData();
        $tenantReport = $viewData['tenantReport'];
        $tenantSummary = $viewData['tenantSummary'];

        $this->assertGreaterThanOrEqual(1, $tenantSummary['total_tenants']);

        $budiData = $tenantReport->firstWhere('user_id', $this->tenantUser->id);
        $this->assertNotNull($budiData);
        $this->assertEquals('Budi Santoso', $budiData['name']);
        $this->assertTrue($budiData['has_active_assignment']);
        $this->assertEquals('101', (string) $budiData['room_number']);
        $this->assertEquals(1500000.0, (float) $budiData['total_paid_all']);
        $this->assertEquals(1, $budiData['transactions_count']);
        $this->assertEquals('Lunas', $budiData['payment_status']);
        $this->assertEquals(0.0, $budiData['outstanding']);
    }

    public function test_admin_can_filter_room_and_tenant_recap_by_search_and_location(): void
    {
        $otherLocation = Location::create([
            'name'        => 'Kos Teratai',
            'address'     => 'Jl. Teratai No. 12',
            'floor_count' => 1,
            'status'      => 'active',
        ]);

        $otherRoom = Room::create([
            'location_id' => $otherLocation->id,
            'floor_name'  => 1,
            'room_number' => 201,
            'rent_amount' => 1000000,
            'status'      => 'active',
        ]);

        // Filter by location
        $responseLoc = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'location_id' => $otherLocation->id,
            'tab'         => 'rooms',
        ]));
        $responseLoc->assertOk();
        $locRooms = $responseLoc->original->getData()['roomReport'];
        $this->assertTrue($locRooms->every(fn ($r) => $r['location_id'] === $otherLocation->id));

        // Filter by search
        $responseSearch = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'search' => 'Budi',
            'tab'    => 'tenants',
        ]));
        $responseSearch->assertOk();
        $searchTenants = $responseSearch->original->getData()['tenantReport'];
        $this->assertTrue($searchTenants->contains(fn ($t) => str_contains($t['name'], 'Budi')));
    }

    public function test_admin_can_switch_tabs_in_finance_dashboard(): void
    {
        foreach (['overview', 'rooms', 'tenants', 'records'] as $tab) {
            $response = $this->actingAs($this->admin)->get(route('admin.finance.index', ['tab' => $tab]));
            $response->assertOk();
            $this->assertEquals($tab, $response->original->getData()['activeTab']);
        }
    }
}
