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
    private ExpenseCategory $categoryOp;
    private ExpenseCategory $categoryMaint;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'name'     => 'Admin Kos',
            'username' => 'admin_exp_test',
            'email'    => 'admin.exp@test.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Kos',
            'username' => 'staff_exp_test',
            'email'    => 'staff.exp@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenant = User::factory()->create([
            'name'     => 'Penyewa Test',
            'username' => 'penyewa_exp_test',
            'email'    => 'penyewa.exp@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->categoryOp = ExpenseCategory::create([
            'name' => 'Listrik',
            'type' => 'operational',
        ]);

        $this->categoryMaint = ExpenseCategory::create([
            'name' => 'Service AC',
            'type' => 'maintenance',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Melati',
            'address'     => 'Jl. Melati No. 1',
            'floor_count' => 2,
            'status'      => 'active',
        ]);
    }

    // ─── Access Control ─────────────────────────────────────────────────────

    public function test_guest_staff_penyewa_cannot_access_expense_pages(): void
    {
        $this->get(route('admin.expenses.index'))->assertRedirect(route('login'));

        $this->actingAs($this->staff)->get(route('admin.expenses.index'))->assertStatus(403);
        $this->actingAs($this->tenant)->get(route('admin.expenses.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('admin.expenses.create'))->assertStatus(403);
    }

    public function test_admin_can_view_expense_list(): void
    {
        Expense::create([
            'title'        => 'Bayar listrik',
            'category_id'  => $this->categoryOp->id,
            'location_id'  => $this->location->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 500000,
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.expenses.index'))
            ->assertOk()
            ->assertViewIs('admin.expenses.expense-list')
            ->assertViewHas('expenses')
            ->assertViewHas('categories')
            ->assertViewHas('locations');
    }

    // ─── Create / Store ───────────────────────────────────────────────────────

    public function test_admin_can_view_create_expense_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.expenses.create'))
            ->assertOk()
            ->assertViewIs('admin.expenses.expense-create');
    }

    public function test_admin_can_create_expense_without_receipt(): void
    {
        $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'title'        => 'Bayar Internet',
            'category_id'  => $this->categoryOp->id,
            'location_id'  => $this->location->id,
            'amount'        => 300000,
            'expense_date'  => now()->toDateString(),
            'description'   => 'Tagihan bulanan ISP',
        ])->assertRedirect(route('admin.expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'title'       => 'Bayar Internet',
            'amount'      => '300000.00',
            'category_id' => $this->categoryOp->id,
            'location_id' => $this->location->id,
            'recorded_by' => $this->admin->id,
        ]);
    }

    public function test_creating_expense_auto_creates_financial_record(): void
    {
        $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'title'        => 'Service Pompa Air',
            'category_id'  => $this->categoryMaint->id,
            'location_id'  => $this->location->id,
            'amount'       => 150000,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect();

        $expense = Expense::where('title', 'Service Pompa Air')->first();
        $this->assertNotNull($expense);

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'type'           => 'expense',
            'amount'         => '150000.00',
            'location_id'    => $this->location->id,
            'recorded_by'    => $this->admin->id,
        ]);
    }

    public function test_admin_can_upload_receipt_with_expense(): void
    {
        $receipt = UploadedFile::fake()->image('struk.jpg', 100, 100);

        $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'title'        => 'Ganti Lampu',
            'category_id'  => $this->categoryMaint->id,
            'amount'       => 50000,
            'expense_date' => now()->toDateString(),
            'receipt'      => $receipt,
        ])->assertRedirect(route('admin.expenses.index'));

        $expense = Expense::where('title', 'Ganti Lampu')->first();
        $this->assertNotNull($expense->receipt_path);
        Storage::disk('public')->assertExists($expense->receipt_path);
    }

    public function test_expense_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.expenses.store'), [])
            ->assertSessionHasErrors(['title', 'category_id', 'amount', 'expense_date']);
    }

    public function test_expense_creation_rejects_invalid_category(): void
    {
        $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'title'        => 'Test',
            'category_id'  => 9999,
            'amount'       => 100000,
            'expense_date' => now()->toDateString(),
        ])->assertSessionHasErrors(['category_id']);
    }

    public function test_expense_creation_rejects_negative_amount(): void
    {
        $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'title'        => 'Test',
            'category_id'  => $this->categoryOp->id,
            'amount'       => -1000,
            'expense_date' => now()->toDateString(),
        ])->assertSessionHasErrors(['amount']);
    }

    // ─── Edit / Update ────────────────────────────────────────────────────────

    public function test_admin_can_view_edit_expense_form(): void
    {
        $expense = Expense::create([
            'title'        => 'Bayar Air',
            'category_id'  => $this->categoryOp->id,
            'location_id'  => $this->location->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 100000,
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.expenses.edit', $expense))
            ->assertOk()
            ->assertViewIs('admin.expenses.expense-edit')
            ->assertViewHas('expense', $expense);
    }

    public function test_admin_can_update_expense_and_financial_record_is_updated(): void
    {
        $expense = Expense::create([
            'title'        => 'Bayar Air Lama',
            'category_id'  => $this->categoryOp->id,
            'location_id'  => $this->location->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 80000,
            'expense_date' => now()->toDateString(),
        ]);

        // Create initial financial record
        FinancialRecord::create([
            'reference_type'   => 'expense',
            'reference_id'     => $expense->id,
            'type'             => 'expense',
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'description'      => 'Bayar Air Lama',
            'amount'           => 80000,
            'transaction_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)->put(route('admin.expenses.update', $expense), [
            'title'        => 'Bayar Air Baru',
            'category_id'  => $this->categoryOp->id,
            'amount'       => 95000,
            'expense_date' => now()->toDateString(),
        ])->assertRedirect(route('admin.expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'id'     => $expense->id,
            'title'  => 'Bayar Air Baru',
            'amount' => '95000.00',
        ]);

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
            'amount'         => '95000.00',
        ]);

        // Ensure no duplicate records
        $count = FinancialRecord::where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->count();
        $this->assertEquals(1, $count);
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function test_admin_can_delete_expense_and_financial_record_is_removed(): void
    {
        $expense = Expense::create([
            'title'        => 'Pengeluaran Hapus',
            'category_id'  => $this->categoryMaint->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 200000,
            'expense_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'reference_type'   => 'expense',
            'reference_id'     => $expense->id,
            'type'             => 'expense',
            'recorded_by'      => $this->admin->id,
            'description'      => 'Pengeluaran Hapus',
            'amount'           => 200000,
            'transaction_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.expenses.destroy', $expense))
            ->assertRedirect(route('admin.expenses.index'));

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'expense',
            'reference_id'   => $expense->id,
        ]);
    }

    public function test_deleting_expense_with_receipt_removes_file(): void
    {
        $receiptPath = 'receipts/dummy-receipt.jpg';
        Storage::disk('public')->put($receiptPath, 'fake content');

        $expense = Expense::create([
            'title'        => 'Pengeluaran Struk',
            'category_id'  => $this->categoryOp->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 75000,
            'expense_date' => now()->toDateString(),
            'receipt_path' => $receiptPath,
        ]);

        $this->actingAs($this->admin)->delete(route('admin.expenses.destroy', $expense));

        Storage::disk('public')->assertMissing($receiptPath);
    }

    // ─── Filters ─────────────────────────────────────────────────────────────

    public function test_expense_list_can_be_filtered_by_category(): void
    {
        Expense::create([
            'title' => 'Tagihan Listrik', 'category_id' => $this->categoryOp->id,
            'recorded_by' => $this->admin->id, 'amount' => 400000,
            'expense_date' => now()->toDateString(),
        ]);
        Expense::create([
            'title' => 'Service AC', 'category_id' => $this->categoryMaint->id,
            'recorded_by' => $this->admin->id, 'amount' => 200000,
            'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.expenses.index', ['category_id' => $this->categoryOp->id, 'time_scope' => 'all']))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('expenses')->total());
    }

    public function test_expense_list_can_be_filtered_by_location(): void
    {
        $otherLocation = Location::create([
            'name' => 'Kos B', 'address' => 'Jl. B No.2', 'floor_count' => 1, 'status' => 'active',
        ]);

        Expense::create([
            'title' => 'Listrik Melati', 'category_id' => $this->categoryOp->id,
            'location_id' => $this->location->id, 'recorded_by' => $this->admin->id,
            'amount' => 300000, 'expense_date' => now()->toDateString(),
        ]);
        Expense::create([
            'title' => 'Listrik B', 'category_id' => $this->categoryOp->id,
            'location_id' => $otherLocation->id, 'recorded_by' => $this->admin->id,
            'amount' => 250000, 'expense_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.expenses.index', ['location_id' => $this->location->id, 'time_scope' => 'all']))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('expenses')->total());
    }

    public function test_expense_list_month_filter(): void
    {
        $thisMonth = now()->toDateString();
        $lastMonth = now()->subMonthNoOverflow()->toDateString();

        Expense::create([
            'title' => 'Bulan ini', 'category_id' => $this->categoryOp->id,
            'recorded_by' => $this->admin->id, 'amount' => 100000, 'expense_date' => $thisMonth,
        ]);
        Expense::create([
            'title' => 'Bulan lalu', 'category_id' => $this->categoryOp->id,
            'recorded_by' => $this->admin->id, 'amount' => 100000, 'expense_date' => $lastMonth,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.expenses.index', ['time_scope' => 'month', 'month' => now()->format('Y-m')]))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('expenses')->total());
    }
}
