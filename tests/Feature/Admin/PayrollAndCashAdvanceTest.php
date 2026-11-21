<?php

namespace Tests\Feature\Admin;

use App\Models\CashAdvance;
use App\Models\FinancialRecord;
use App\Models\PayrollRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollAndCashAdvanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
        ]);

        $this->staff = User::factory()->create([
            'role'   => 'staff',
            'status' => 'active',
        ]);

        $this->tenant = User::factory()->create([
            'role'   => 'penyewa',
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_access_payroll_management(): void
    {
        $response = $this->get(route('admin.payroll.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_payroll_management(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.payroll.index'));
        $response->assertForbidden();
    }

    public function test_tenant_cannot_access_payroll_management(): void
    {
        $response = $this->actingAs($this->tenant)->get(route('admin.payroll.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_view_payroll_and_cash_advances_page_with_metrics(): void
    {
        PayrollRecord::create([
            'staff_id'               => $this->staff->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 3000000,
            'cash_advance_deduction' => 500000,
            'net_amount'             => 2500000,
            'payment_date'           => now()->toDateString(),
            'notes'                  => 'Gaji bulan ini',
        ]);

        CashAdvance::create([
            'staff_id'     => $this->staff->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 500000,
            'advance_date' => now()->toDateString(),
            'status'       => 'outstanding',
            'notes'        => 'Kasbon keperluan mendesak',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payroll.index'));

        $response->assertOk();
        $response->assertViewIs('admin.payroll.index');
        $response->assertSee('Penggajian &amp; Kasbon Staff', false);
        $response->assertSee('2.500.000');
        $response->assertSee('3.000.000');
        $response->assertSee('500.000');
    }

    public function test_admin_can_record_payroll_and_syncs_to_financial_records(): void
    {
        $payload = [
            'staff_id'               => $this->staff->id,
            'gross_amount'           => 3500000,
            'cash_advance_deduction' => 0,
            'payment_date'           => now()->toDateString(),
            'notes'                  => 'Gaji pokok staff',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.payroll.store'), $payload);

        $response->assertRedirect(route('admin.payroll.index', ['tab' => 'payroll']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payroll_records', [
            'staff_id'     => $this->staff->id,
            'gross_amount' => 3500000,
            'net_amount'   => 3500000,
        ]);

        $payroll = PayrollRecord::first();
        $this->assertNotNull($payroll);

        // Verify financial record general ledger synchronization
        $this->assertDatabaseHas('financial_records', [
            'type'           => 'expense',
            'reference_type' => 'payroll',
            'reference_id'   => $payroll->id,
            'amount'         => 3500000,
        ]);
    }

    public function test_admin_can_record_payroll_with_cash_advance_deduction_and_settles_advances(): void
    {
        $advance = CashAdvance::create([
            'staff_id'     => $this->staff->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 500000,
            'advance_date' => now()->subDays(5)->toDateString(),
            'status'       => 'outstanding',
            'notes'        => 'Pinjaman awal bulan',
        ]);

        $payload = [
            'staff_id'               => $this->staff->id,
            'gross_amount'           => 4000000,
            'cash_advance_deduction' => 500000,
            'payment_date'           => now()->toDateString(),
            'notes'                  => 'Gaji dipotong kasbon',
            'settle_advances'        => '1',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.payroll.store'), $payload);

        $response->assertRedirect(route('admin.payroll.index', ['tab' => 'payroll']));

        $payroll = PayrollRecord::first();
        $this->assertEquals(3500000, (float) $payroll->net_amount);

        // Verify advance was marked as repaid
        $advance->refresh();
        $this->assertEquals('repaid', $advance->status);

        // Financial record records net paid out
        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'payroll',
            'reference_id'   => $payroll->id,
            'amount'         => 3500000,
        ]);
    }

    public function test_admin_can_update_payroll_record(): void
    {
        $payroll = PayrollRecord::create([
            'staff_id'               => $this->staff->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 3000000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 3000000,
            'payment_date'           => now()->toDateString(),
        ]);
        FinancialRecord::recordFromPayroll($payroll);

        $payload = [
            'staff_id'               => $this->staff->id,
            'gross_amount'           => 3200000,
            'cash_advance_deduction' => 200000,
            'payment_date'           => now()->toDateString(),
            'notes'                  => 'Penyesuaian gaji',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.payroll.update', $payroll), $payload);

        $response->assertRedirect(route('admin.payroll.index', ['tab' => 'payroll']));

        $payroll->refresh();
        $this->assertEquals(3200000, (float) $payroll->gross_amount);
        $this->assertEquals(200000, (float) $payroll->cash_advance_deduction);
        $this->assertEquals(3000000, (float) $payroll->net_amount);

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'payroll',
            'reference_id'   => $payroll->id,
            'amount'         => 3000000,
        ]);
    }

    public function test_admin_can_delete_payroll_and_removes_financial_record(): void
    {
        $payroll = PayrollRecord::create([
            'staff_id'               => $this->staff->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 2500000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 2500000,
            'payment_date'           => now()->toDateString(),
        ]);
        FinancialRecord::recordFromPayroll($payroll);

        $this->assertDatabaseCount('financial_records', 1);

        $response = $this->actingAs($this->admin)->delete(route('admin.payroll.destroy', $payroll));

        $response->assertRedirect(route('admin.payroll.index', ['tab' => 'payroll']));
        $this->assertDatabaseMissing('payroll_records', ['id' => $payroll->id]);
        $this->assertDatabaseCount('financial_records', 0);
    }

    public function test_admin_can_create_cash_advance(): void
    {
        $payload = [
            'staff_id'     => $this->staff->id,
            'amount'       => 400000,
            'advance_date' => now()->toDateString(),
            'status'       => 'outstanding',
            'notes'        => 'Kasbon darurat',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.cash-advances.store'), $payload);

        $response->assertRedirect(route('admin.payroll.index', ['tab' => 'advances']));
        $this->assertDatabaseHas('cash_advances', [
            'staff_id' => $this->staff->id,
            'amount'   => 400000,
            'status'   => 'outstanding',
        ]);
    }

    public function test_admin_can_update_cash_advance_and_status(): void
    {
        $advance = CashAdvance::create([
            'staff_id'     => $this->staff->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 300000,
            'advance_date' => now()->toDateString(),
            'status'       => 'outstanding',
        ]);

        // Quick status update
        $response = $this->actingAs($this->admin)->patch(route('admin.cash-advances.status', $advance), [
            'status' => 'repaid',
        ]);

        $response->assertRedirect(route('admin.payroll.index', ['tab' => 'advances']));
        $advance->refresh();
        $this->assertEquals('repaid', $advance->status);

        // Full update
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.cash-advances.update', $advance), [
            'staff_id'     => $this->staff->id,
            'amount'       => 350000,
            'advance_date' => now()->toDateString(),
            'status'       => 'partially_repaid',
            'notes'        => 'Cicilan kasbon diperbarui',
        ]);

        $updateResponse->assertRedirect(route('admin.payroll.index', ['tab' => 'advances']));
        $advance->refresh();
        $this->assertEquals(350000, (float) $advance->amount);
        $this->assertEquals('partially_repaid', $advance->status);
    }

    public function test_admin_can_delete_cash_advance(): void
    {
        $advance = CashAdvance::create([
            'staff_id'     => $this->staff->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 200000,
            'advance_date' => now()->toDateString(),
            'status'       => 'cancelled',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.cash-advances.destroy', $advance));

        $response->assertRedirect(route('admin.payroll.index', ['tab' => 'advances']));
        $this->assertDatabaseMissing('cash_advances', ['id' => $advance->id]);
    }

    public function test_historical_payroll_and_cash_advances_survive_staff_deactivation(): void
    {
        $payroll = PayrollRecord::create([
            'staff_id'               => $this->staff->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 3000000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 3000000,
            'payment_date'           => now()->toDateString(),
        ]);

        $advance = CashAdvance::create([
            'staff_id'     => $this->staff->id,
            'recorded_by'  => $this->admin->id,
            'amount'       => 500000,
            'advance_date' => now()->toDateString(),
            'status'       => 'repaid',
        ]);

        // Deactivate staff
        $this->staff->update([
            'status'      => 'inactive',
            'inactive_at' => now(),
        ]);

        $this->assertEquals('inactive', $this->staff->fresh()->status);

        // Verify historical records remain intact and accessible
        $this->assertDatabaseHas('payroll_records', ['id' => $payroll->id]);
        $this->assertDatabaseHas('cash_advances', ['id' => $advance->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.payroll.index'));
        $response->assertOk();
        $response->assertSee($this->staff->name);
        $response->assertSee('Nonaktif');
    }

    public function test_staff_with_payroll_or_cash_advance_cannot_be_deleted(): void
    {
        $this->staff->update([
            'status'      => 'inactive',
            'inactive_at' => now()->subDays(10), // eligible by days
        ]);

        PayrollRecord::create([
            'staff_id'               => $this->staff->id,
            'recorded_by'            => $this->admin->id,
            'gross_amount'           => 2000000,
            'cash_advance_deduction' => 0,
            'net_amount'             => 2000000,
            'payment_date'           => now()->toDateString(),
        ]);

        $this->assertTrue($this->staff->hasFinancialOrOperationalHistory());

        // Attempting sensitive destroy via StaffController
        $response = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $this->staff), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['deletion']);
        $this->assertDatabaseHas('users', ['id' => $this->staff->id]);
    }
}
