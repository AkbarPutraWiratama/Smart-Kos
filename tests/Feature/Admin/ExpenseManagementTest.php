<?php

namespace Tests\Feature\Admin;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $tenant;
    private Location $location;
    private ExpenseCategory $operationalCategory;
    private ExpenseCategory $maintenanceCategory;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'name'     => 'Administrator',
            'username' => 'admin_test',
            'email'    => 'admin@smartkos.test',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Member',
            'username' => 'staff_test',
            'email'    => 'staff@smartkos.test',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenant = User::factory()->create([
            'name'     => 'Tenant Resident',
            'username' => 'tenant_test',
            'email'    => 'tenant@smartkos.test',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Melati Indah',
            'address'     => 'Jl. Melati No. 10',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->operationalCategory = ExpenseCategory::create([
            'name' => 'Listrik (PLN)',
            'type' => ExpenseCategory::TYPE_OPERATIONAL,
        ]);

        $this->maintenanceCategory = ExpenseCategory::create([
            'name' => 'Servis & Cuci AC',
            'type' => ExpenseCategory::TYPE_MAINTENANCE,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 1. Access Control
    // ─────────────────────────────────────────────────────────────────

    public function test_guest_cannot_access_expense_management(): void
    {
        $response = $this->get(route('admin.expenses.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_expense_management(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.expenses.index'));
        $response->assertForbidden();
    }

    public function test_tenant_cannot_access_expense_management(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('admin.expenses.index'));
        $response->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────────
    // 2. View Expenses & Metrics
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_expense_management_page_with_metrics(): void
    {
        Expense::create([
            'location_id'  => $this->location->id,
            'category_id'  => $this->operationalCategory->id,
            'recorded_by'  => $this->admin->id,
            'title'        => 'Token Listrik Kos Melati',
            'amount'       => 500000,
            'expense_date' => now()->toDateString(),
        ]);

        Expense::create([
            'location_id'  => $this->location->id,
            'category_id'  => $this->maintenanceCategory->id,
            'recorded_by'  => $this->admin->id,
            'title'        => 'Servis AC Kamar 101',
            'amount'       => 200000,
            'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.expenses.index'));

        $response->assertOk();
        $response->assertViewIs('admin.expenses.index');
        $response->assertViewHas('expenses');
        $response->assertViewHas('totalExpensesAmount');
        $response->assertViewHas('operationalTotal');
        $response->assertViewHas('maintenanceTotal');
        $response->assertViewHas('totalExpensesCount');

        $viewData = $response->original->getData();
        $this->assertEquals(700000.0, $viewData['totalExpensesAmount']);
        $this->assertEquals(500000.0, $viewData['operationalTotal']);
        $this->assertEquals(200000.0, $viewData['maintenanceTotal']);
        $this->assertEquals(2, $viewData['totalExpensesCount']);
    }

    // ─────────────────────────────────────────────────────────────────
    // 3. Create Expenses & Financial Sync
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_create_operational_expense_and_syncs_to_financial_records(): void
    {
        $payload = [
            'location_id'  => $this->location->id,
            'category_id'  => $this->operationalCategory->id,
            'title'        => 'Biaya Internet & Wi-Fi Bulan Ini',
            'description'  => 'Langganan paket internet 50 Mbps',
            'amount'       => 350000,
            'expense_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.expenses.store'), $payload);

        $response->assertRedirect(route('admin.expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'title'       => 'Biaya Internet & Wi-Fi Bulan Ini',
            'amount'      => 350000,
            'recorded_by' => $this->admin->id,
        ]);

        $expense = Expense::where('title', 'Biaya Internet & Wi-Fi Bulan Ini')->first();
        $this->assertNotNull($expense);

        // Verify automatic creation in financial_records
        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'type'           => FinancialRecord::TYPE_EXPENSE,
            'amount'         => 350000,
            'location_id'    => $this->location->id,
            'recorded_by'    => $this->admin->id,
        ]);
    }

    public function test_admin_can_create_maintenance_expense_with_receipt_upload(): void
    {
        $fakeReceipt = UploadedFile::fake()->create('nota_servis_ac.pdf', 100, 'application/pdf');

        $payload = [
            'location_id'  => $this->location->id,
            'category_id'  => $this->maintenanceCategory->id,
            'title'        => 'Penggantian Kapasitor AC Kamar 102',
            'description'  => 'AC tidak dingin, teknisi ganti kapasitor baru',
            'amount'       => 250000,
            'expense_date' => now()->toDateString(),
            'receipt'      => $fakeReceipt,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.expenses.store'), $payload);

        $response->assertRedirect(route('admin.expenses.index'));
        $response->assertSessionHas('success');

        $expense = Expense::where('title', 'Penggantian Kapasitor AC Kamar 102')->first();
        $this->assertNotNull($expense);
        $this->assertNotNull($expense->receipt_path);

        Storage::disk('public')->assertExists($expense->receipt_path);
    }

    // ─────────────────────────────────────────────────────────────────
    // 4. Update Expense & Sync
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_update_expense_and_syncs_to_financial_records(): void
    {
        $expense = Expense::create([
            'location_id'  => $this->location->id,
            'category_id'  => $this->operationalCategory->id,
            'recorded_by'  => $this->admin->id,
            'title'        => 'Biaya Air PDAM Awal',
            'amount'       => 150000,
            'expense_date' => now()->toDateString(),
        ]);
        FinancialRecord::recordFromExpense($expense);

        $updatePayload = [
            'location_id'  => $this->location->id,
            'category_id'  => $this->operationalCategory->id,
            'title'        => 'Biaya Air PDAM Revisi',
            'amount'       => 185000,
            'expense_date' => now()->toDateString(),
            'description'  => 'Penyesuaian tagihan meteran air',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.expenses.update', $expense), $updatePayload);

        $response->assertRedirect(route('admin.expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'id'     => $expense->id,
            'title'  => 'Biaya Air PDAM Revisi',
            'amount' => 185000,
        ]);

        // Verify financial record was updated
        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'amount'         => 185000,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 5. Delete Expense & Remove Financial Record
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_delete_expense_and_removes_from_financial_records(): void
    {
        $fakeFile = UploadedFile::fake()->create('nota.pdf', 50, 'application/pdf');
        $storedPath = $fakeFile->store('receipts', 'public');

        $expense = Expense::create([
            'location_id'  => $this->location->id,
            'category_id'  => $this->operationalCategory->id,
            'recorded_by'  => $this->admin->id,
            'title'        => 'Pengeluaran Yang Akan Dihapus',
            'amount'       => 100000,
            'expense_date' => now()->toDateString(),
            'receipt_path' => $storedPath,
        ]);
        FinancialRecord::recordFromExpense($expense);

        $response = $this->actingAs($this->admin)->delete(route('admin.expenses.destroy', $expense));

        $response->assertRedirect(route('admin.expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
        ]);

        Storage::disk('public')->assertMissing($storedPath);
    }

    // ─────────────────────────────────────────────────────────────────
    // 6. Validation
    // ─────────────────────────────────────────────────────────────────

    public function test_expense_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.expenses.store'), []);

        $response->assertSessionHasErrors(['title', 'category_id', 'amount', 'expense_date']);
    }

    public function test_expense_amount_must_be_positive(): void
    {
        $payload = [
            'category_id'  => $this->operationalCategory->id,
            'title'        => 'Invalid Amount',
            'amount'       => -50000,
            'expense_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.expenses.store'), $payload);

        $response->assertSessionHasErrors(['amount']);
    }

    // ─────────────────────────────────────────────────────────────────
    // 7. Filters & Search
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_filter_expenses_by_location_and_search(): void
    {
        $otherLocation = Location::create([
            'name'        => 'Kos Cempaka Putih',
            'address'     => 'Jl. Cempaka No. 8',
            'floor_count' => 1,
            'status'      => 'active',
        ]);

        Expense::create([
            'location_id'  => $this->location->id,
            'category_id'  => $this->operationalCategory->id,
            'recorded_by'  => $this->admin->id,
            'title'        => 'Listrik Melati',
            'amount'       => 500000,
            'expense_date' => now()->toDateString(),
        ]);

        Expense::create([
            'location_id'  => $otherLocation->id,
            'category_id'  => $this->operationalCategory->id,
            'recorded_by'  => $this->admin->id,
            'title'        => 'Listrik Cempaka',
            'amount'       => 400000,
            'expense_date' => now()->toDateString(),
        ]);

        // Filter by location
        $responseLoc = $this->actingAs($this->admin)->get(route('admin.expenses.index', [
            'location_id' => $this->location->id,
        ]));
        $responseLoc->assertOk();
        $this->assertEquals(500000.0, $responseLoc->original->getData()['totalExpensesAmount']);

        // Search by keyword
        $responseSearch = $this->actingAs($this->admin)->get(route('admin.expenses.index', [
            'search' => 'Cempaka',
        ]));
        $responseSearch->assertOk();
        $this->assertCount(1, $responseSearch->original->getData()['expenses']);
    }
}
