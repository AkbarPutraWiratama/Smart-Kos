<?php

namespace Tests\Feature\Admin;

use App\Models\CashAdvance;
use App\Models\FinancialRecord;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayrollManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff1;
    private User $staff2;
    private User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'     => 'Admin Utama',
            'username' => 'admin_payroll_test',
            'email'    => 'admin.payroll@test.com',
            'role'     => 'admin',
            'status'   => 'active',
            'password' => Hash::make('adminpassword'),
        ]);

        $this->staff1 = User::factory()->create([
            'name'     => 'Staf Agus',
            'username' => 'agus_staff',
            'email'    => 'agus@staff.test',
            'role'     => 'staff',
            'status'   => 'active',
            'password' => Hash::make('staffpassword'),
        ]);

        $this->staff2 = User::factory()->create([
            'name'     => 'Staf Budi',
            'username' => 'budi_staff',
            'email'    => 'budi@staff.test',
            'role'     => 'staff',
            'status'   => 'active',
            'password' => Hash::make('staffpassword'),
        ]);

        $this->tenant = User::factory()->create([
            'name'     => 'Penyewa Kos',
            'username' => 'penyewa_payroll',
            'email'    => 'penyewa@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);
    }

    public function test_guests_cannot_access_payroll_page(): void
    {
        $response = $this->get(route('admin.payroll.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_tenants_and_staff_cannot_access_payroll_page(): void
    {
        $tenantResponse = $this->actingAs($this->tenant)->get(route('admin.payroll.index'));
        $tenantResponse->assertStatus(403);

        $staffResponse = $this->actingAs($this->staff1)->get(route('admin.payroll.index'));
        $staffResponse->assertStatus(403);
    }

    public function test_admin_can_view_payroll_hub_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.payroll.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Gaji & Kasbon Staf', false);
        $response->assertSeeText('Catat Gaji Staf');
        $response->assertSeeText('Catat Kasbon');
        $response->assertSeeText('Total Gaji Bersih');
    }

    public function test_admin_can_record_payroll_without_cash_advance_deduction(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payroll.store'), [
            'staff_id'               => $this->staff1->id,
            'gross_amount'           => 2500000,
            'cash_advance_deduction' => 0,
            'payment_date'           => now()->toDateString(),
            'notes'                  => 'Gaji pokok bulan berjalan',
        ]);

        $response->assertRedirect(route('admin.payroll.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payroll_records', [
            'staff_id'               => $this->staff1->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 2500000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 2500000,
        ]);

        $payroll = PayrollRecord::first();
        $this->assertNotNull($payroll);

        // Verify financial_records sync
        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'payroll',
            'reference_id'   => $payroll->id,
            'type'           => 'expense',
            'amount'         => 2500000,
        ]);
    }

    public function test_admin_can_record_payroll_with_cash_advance_deduction_and_it_auto_settles_advances(): void
    {
        // Setup outstanding cash advance for staff1
        $advance = CashAdvance::create([
            'staff_id'     => $this->staff1->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 300000,
            'advance_date' => now()->subDays(5)->toDateString(),
            'status'       => 'outstanding',
            'notes'        => 'Kasbon darurat',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payroll.store'), [
            'staff_id'               => $this->staff1->id,
            'gross_amount'           => 3000000,
            'cash_advance_deduction' => 300000,
            'payment_date'           => now()->toDateString(),
            'notes'                  => 'Gaji dengan pelunasan kasbon',
        ]);

        $response->assertRedirect(route('admin.payroll.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payroll_records', [
            'staff_id'               => $this->staff1->id,
            'gross_amount'           => 3000000,
            'cash_advance_deduction' => 300000,
            'net_amount'             => 2700000,
        ]);

        // Advance should now be marked repaid
        $advance->refresh();
        $this->assertEquals('repaid', $advance->status);

        // FinancialRecord should record net amount
        $payroll = PayrollRecord::latest('id')->first();
        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'payroll',
            'reference_id'   => $payroll->id,
            'type'           => 'expense',
            'amount'         => 2700000,
        ]);
    }

    public function test_payroll_deduction_cannot_exceed_gross_amount(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.payroll.store'), [
            'staff_id'               => $this->staff1->id,
            'gross_amount'           => 1000000,
            'cash_advance_deduction' => 1500000, // Exceeds gross
            'payment_date'           => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['cash_advance_deduction']);
        $this->assertDatabaseCount('payroll_records', 0);
    }

    public function test_admin_can_delete_payroll_and_its_financial_record_is_removed(): void
    {
        $payroll = PayrollRecord::create([
            'staff_id'               => $this->staff1->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 2000000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 2000000,
            'payment_date'           => now()->toDateString(),
        ]);

        $financialRecord = FinancialRecord::create([
            'reference_type'   => 'payroll',
            'reference_id'     => $payroll->id,
            'type'             => 'expense',
            'description'      => 'Gaji Staf Test',
            'amount'           => 2000000,
            'transaction_date' => now()->toDateString(),
            'recorded_by'      => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.payroll.destroy', $payroll));

        $response->assertRedirect(route('admin.payroll.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('payroll_records', ['id' => $payroll->id]);
        $this->assertDatabaseMissing('financial_records', ['id' => $financialRecord->id]);
    }

    public function test_admin_can_record_cash_advance(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.cash-advances.store'), [
            'staff_id'     => $this->staff1->id,
            'amount'       => 500000,
            'advance_date' => now()->toDateString(),
            'notes'        => 'Pinjaman awal bulan',
        ]);

        $response->assertRedirect(route('admin.payroll.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_advances', [
            'staff_id'    => $this->staff1->id,
            'recorded_by' => $this->admin->id,
            'amount'      => 500000,
            'status'      => 'outstanding',
            'notes'       => 'Pinjaman awal bulan',
        ]);

        $advance = CashAdvance::first();
        $this->assertNotNull($advance);

        // Auto-sync into financial_records
        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'cash_advance',
            'reference_id'   => $advance->id,
            'type'           => 'expense',
            'amount'         => 500000,
        ]);
    }

    public function test_admin_can_update_cash_advance_status(): void
    {
        $advance = CashAdvance::create([
            'staff_id'     => $this->staff1->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 400000,
            'advance_date' => now()->toDateString(),
            'status'       => 'outstanding',
        ]);

        $response = $this->actingAs($this->admin)->patch(
            route('admin.cash-advances.update-status', $advance),
            ['status' => 'repaid']
        );

        $response->assertRedirect(route('admin.payroll.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_advances', [
            'id'     => $advance->id,
            'status' => 'repaid',
        ]);
    }

    public function test_cancelling_cash_advance_removes_financial_record(): void
    {
        $advance = CashAdvance::create([
            'staff_id'     => $this->staff1->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 400000,
            'advance_date' => now()->toDateString(),
            'status'       => 'outstanding',
        ]);

        $finRecord = FinancialRecord::create([
            'reference_type'   => 'cash_advance',
            'reference_id'     => $advance->id,
            'type'             => 'expense',
            'description'      => 'Kasbon Test',
            'amount'           => 400000,
            'transaction_date' => now()->toDateString(),
            'recorded_by'      => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->patch(
            route('admin.cash-advances.update-status', $advance),
            ['status' => 'cancelled']
        );

        $response->assertRedirect(route('admin.payroll.index'));
        $this->assertDatabaseMissing('financial_records', ['id' => $finRecord->id]);
    }

    public function test_admin_can_delete_cash_advance_and_its_financial_record_is_removed(): void
    {
        $advance = CashAdvance::create([
            'staff_id'     => $this->staff1->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 250000,
            'advance_date' => now()->toDateString(),
            'status'       => 'outstanding',
        ]);

        $finRecord = FinancialRecord::create([
            'reference_type'   => 'cash_advance',
            'reference_id'     => $advance->id,
            'type'             => 'expense',
            'description'      => 'Kasbon Dihapus Test',
            'amount'           => 250000,
            'transaction_date' => now()->toDateString(),
            'recorded_by'      => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.cash-advances.destroy', $advance));

        $response->assertRedirect(route('admin.payroll.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('cash_advances', ['id' => $advance->id]);
        $this->assertDatabaseMissing('financial_records', ['id' => $finRecord->id]);
    }

    public function test_payroll_and_cash_advance_filters_by_staff_and_period(): void
    {
        // Record for staff 1 in current month
        PayrollRecord::create([
            'staff_id'               => $this->staff1->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 2000000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 2000000,
            'payment_date'           => now()->toDateString(),
            'notes'                  => 'Gaji Staff 1',
        ]);

        // Record for staff 2 in past month
        PayrollRecord::create([
            'staff_id'               => $this->staff2->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 1800000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 1800000,
            'payment_date'           => now()->subMonths(2)->toDateString(),
            'notes'                  => 'Gaji Staff 2 Lalu',
        ]);

        // Filter by staff 1
        $response = $this->actingAs($this->admin)->get(route('admin.payroll.index', [
            'staff_id'   => $this->staff1->id,
            'time_scope' => 'all',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Gaji Staff 1');
        $response->assertDontSee('Gaji Staff 2 Lalu');
    }

    public function test_historical_payroll_and_cash_advance_survives_staff_deactivation_and_blocks_hard_delete(): void
    {
        $this->staff1->update([
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(15),
        ]);

        // Give this staff a payroll record
        PayrollRecord::create([
            'staff_id'               => $this->staff1->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 2000000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 2000000,
            'payment_date'           => now()->subDays(20)->toDateString(),
        ]);

        // Attempting to delete inactive staff with payroll record must be blocked
        $response = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $this->staff1), [
            'password' => 'adminpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('deletion');
        $this->assertDatabaseHas('users', ['id' => $this->staff1->id]);
    }
}
