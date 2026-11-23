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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private User $tenantUser;
    private TenantProfile $tenantProfile;
    private Location $location;
    private Location $otherLocation;
    private Room $room;
    private RoomAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'name'     => 'Administrator',
            'username' => 'admin_fr_test',
            'email'    => 'admin.fr@test.com',
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $this->staff = User::factory()->create([
            'name'     => 'Staff Kos',
            'username' => 'staff_fr_test',
            'email'    => 'staff.fr@test.com',
            'role'     => 'staff',
            'status'   => 'active',
        ]);

        $this->tenantUser = User::factory()->create([
            'name'     => 'Budi Santoso',
            'username' => 'budi_fr_test',
            'email'    => 'budi.fr@test.com',
            'role'     => 'penyewa',
            'status'   => 'active',
        ]);

        $this->tenantProfile = TenantProfile::create([
            'user_id' => $this->tenantUser->id,
            'phone'   => '081200001111',
        ]);

        $this->location = Location::create([
            'name'        => 'Kos Melati',
            'address'     => 'Jl. Melati No. 1',
            'floor_count' => 2,
            'status'      => 'active',
        ]);

        $this->otherLocation = Location::create([
            'name'        => 'Kos Mawar',
            'address'     => 'Jl. Mawar No. 5',
            'floor_count' => 1,
            'status'      => 'active',
        ]);

        $this->room = Room::create([
            'location_id' => $this->location->id,
            'floor_name'  => 1,
            'room_number' => 101,
            'rent_amount' => 1000000,
            'status'      => 'active',
        ]);

        $this->assignment = RoomAssignment::create([
            'room_id'              => $this->room->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => now()->subDays(30)->toDateString(),
            'status'               => 'active',
            'rent_amount_snapshot' => 1000000,
        ]);
    }

    // ─── Access Control ────────────────────────────────────────────────────────

    public function test_guest_and_staff_cannot_access_finance_report(): void
    {
        $this->get(route('admin.finance.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->staff)
            ->get(route('admin.finance.index'))
            ->assertStatus(403);

        $this->actingAs($this->tenantUser)
            ->get(route('admin.finance.index'))
            ->assertStatus(403);
    }

    // ─── Default / All Scope ───────────────────────────────────────────────────

    public function test_admin_can_access_finance_overview_with_default_scope(): void
    {
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 999,
            'description'      => 'Pembayaran Sewa',
            'amount'           => 1000000,
            'transaction_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.finance.index'))
            ->assertOk()
            ->assertViewIs('admin.finance.finance-overview')
            ->assertViewHas('scope', 'all')
            ->assertViewHas('totalIncome');
    }

    public function test_finance_overview_shows_correct_income_and_expense_totals(): void
    {
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 1,
            'description'      => 'Sewa Kamar A',
            'amount'           => 1500000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'recorded_by'      => $this->admin->id,
            'type'             => 'expense',
            'reference_type'   => 'expense',
            'reference_id'     => 1,
            'description'      => 'Bayar Listrik',
            'amount'           => 300000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['time_scope' => 'all']))
            ->assertOk();

        $this->assertEquals(1500000, $response->viewData('totalIncome'));
        $this->assertEquals(300000, $response->viewData('totalExpense'));
        $this->assertEquals(1200000, $response->viewData('netTotal'));
        $this->assertEquals(2, $response->viewData('recordCount'));
    }

    // ─── Time Scopes ───────────────────────────────────────────────────────────

    public function test_month_time_scope_filters_records_by_current_month(): void
    {
        $thisMonth = now()->toDateString();
        $lastMonth = now()->subMonthNoOverflow()->toDateString();

        FinancialRecord::create([
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 10,
            'description'      => 'Sewa bulan ini',
            'amount'           => 800000,
            'transaction_date' => $thisMonth,
        ]);

        FinancialRecord::create([
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 11,
            'description'      => 'Sewa bulan lalu',
            'amount'           => 800000,
            'transaction_date' => $lastMonth,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['time_scope' => 'month', 'month' => now()->format('Y-m')]))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('recordCount'));
        $this->assertEquals(800000, $response->viewData('totalIncome'));
    }

    public function test_year_time_scope_filters_records_by_current_year(): void
    {
        FinancialRecord::create([
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 20,
            'description'      => 'Sewa tahun ini',
            'amount'           => 500000,
            'transaction_date' => now()->startOfYear()->toDateString(),
        ]);

        FinancialRecord::create([
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 21,
            'description'      => 'Sewa tahun lalu',
            'amount'           => 500000,
            'transaction_date' => now()->subYear()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['time_scope' => 'year', 'year' => now()->year]))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('recordCount'));
    }

    public function test_all_time_scope_returns_all_records_regardless_of_date(): void
    {
        FinancialRecord::create([
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 30,
            'description'      => 'Lama',
            'amount'           => 200000,
            'transaction_date' => '2023-01-15',
        ]);

        FinancialRecord::create([
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 31,
            'description'      => 'Baru',
            'amount'           => 200000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['time_scope' => 'all']))
            ->assertOk();

        $this->assertEquals(2, $response->viewData('recordCount'));
    }

    // ─── Scope Filters ─────────────────────────────────────────────────────────

    public function test_location_scope_filters_records_by_location(): void
    {
        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 40,
            'description'      => 'Sewa lokasi A',
            'amount'           => 900000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->otherLocation->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 41,
            'description'      => 'Sewa lokasi B',
            'amount'           => 700000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', [
                'time_scope'  => 'all',
                'scope'       => 'location',
                'location_id' => $this->location->id,
            ]))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('recordCount'));
        $this->assertEquals(900000, $response->viewData('totalIncome'));
    }

    public function test_person_scope_filters_records_by_recorded_by_user(): void
    {
        FinancialRecord::create([
            'recorded_by'      => $this->admin->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 50,
            'description'      => 'Verifikasi oleh admin',
            'amount'           => 600000,
            'transaction_date' => now()->toDateString(),
        ]);

        FinancialRecord::create([
            'recorded_by'      => $this->staff->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => 51,
            'description'      => 'Verifikasi oleh staff',
            'amount'           => 400000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', [
                'time_scope' => 'all',
                'scope'      => 'person',
                'person_id'  => $this->staff->id,
            ]))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('recordCount'));
        $this->assertEquals(400000, $response->viewData('totalIncome'));
    }

    public function test_room_scope_filters_records_via_manual_payment_room_assignment(): void
    {
        $otherRoom = Room::create([
            'location_id' => $this->otherLocation->id,
            'floor_name'  => 1,
            'room_number' => 201,
            'rent_amount' => 800000,
            'status'      => 'active',
        ]);

        // Manual payment for $this->room → linked to assignment
        $payment = ManualPayment::create([
            'room_assignment_id' => $this->assignment->id,
            'submitted_by'       => $this->tenantUser->id,
            'amount'             => 1000000,
            'bank_name'          => 'BCA',
            'bank_account_number'=> '1234567890',
            'proof_path'         => 'proofs/dummy.jpg',
            'status'             => 'approved',
            'submitted_at'       => now()->subDay(),
        ]);

        FinancialRecord::create([
            'location_id'      => $this->location->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => $payment->id,
            'description'      => 'Sewa kamar 101',
            'amount'           => 1000000,
            'transaction_date' => now()->toDateString(),
        ]);

        // Another record unrelated to this room
        $otherAssignment = RoomAssignment::create([
            'room_id'              => $otherRoom->id,
            'tenant_id'            => $this->tenantProfile->id,
            'start_date'           => now()->subDays(5)->toDateString(),
            'status'               => 'active',
            'rent_amount_snapshot' => 800000,
        ]);
        $otherPayment = ManualPayment::create([
            'room_assignment_id' => $otherAssignment->id,
            'submitted_by'       => $this->tenantUser->id,
            'amount'             => 800000,
            'bank_name'          => 'BNI',
            'bank_account_number'=> '0987654321',
            'proof_path'         => 'proofs/dummy2.jpg',
            'status'             => 'approved',
            'submitted_at'       => now()->subDay(),
        ]);
        FinancialRecord::create([
            'location_id'      => $this->otherLocation->id,
            'type'             => 'income',
            'reference_type'   => 'manual_payment',
            'reference_id'     => $otherPayment->id,
            'description'      => 'Sewa kamar 201',
            'amount'           => 800000,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', [
                'time_scope' => 'all',
                'scope'      => 'room',
                'room_id'    => $this->room->id,
            ]))
            ->assertOk();

        $this->assertEquals(1, $response->viewData('recordCount'));
        $this->assertEquals(1000000, $response->viewData('totalIncome'));
    }

    // ─── Auto-sync: Manual Payment Approval → FinancialRecord ─────────────────

    public function test_approving_manual_payment_creates_financial_record(): void
    {
        Storage::fake('public');

        $proof = UploadedFile::fake()->image('bukti.jpg');
        $proofPath = $proof->store('proofs', 'public');

        $payment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1200000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '1111222233334444',
            'proof_path'          => $proofPath,
            'status'              => 'pending',
            'submitted_at'        => now()->subHour(),
        ]);

        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'manual_payment',
            'reference_id'   => $payment->id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.approve', $payment))
            ->assertRedirect();

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'manual_payment',
            'reference_id'   => $payment->id,
            'type'           => 'income',
            'amount'         => '1200000.00',
        ]);

        $record = FinancialRecord::where('reference_type', 'manual_payment')
            ->where('reference_id', $payment->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals('income', $record->type);
        $this->assertEquals($this->location->id, $record->location_id);
        $this->assertEquals($this->admin->id, $record->recorded_by);
        $this->assertStringContainsString('101', $record->description);
        $this->assertStringContainsString('Budi Santoso', $record->description);
    }

    public function test_approving_same_manual_payment_twice_does_not_duplicate_financial_record(): void
    {
        Storage::fake('public');

        $payment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'amount'              => 1000000,
            'bank_name'           => 'BNI',
            'bank_account_number' => '5555666677778888',
            'proof_path'          => 'proofs/test.jpg',
            'status'              => 'pending',
            'submitted_at'        => now()->subHour(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.approve', $payment));

        // Try approve again (should return error redirect, not duplicate record)
        $this->actingAs($this->admin)
            ->post(route('admin.payments.approve', $payment));

        $count = FinancialRecord::where('reference_type', 'manual_payment')
            ->where('reference_id', $payment->id)
            ->count();

        $this->assertEquals(1, $count);
    }

    // ─── Auto-sync: Midtrans VA Settlement → FinancialRecord ──────────────────

    public function test_midtrans_settlement_webhook_creates_financial_record(): void
    {
        $serverKey = 'SB-Mid-server-test-key-fr';
        Config::set('services.midtrans.server_key', $serverKey);
        Config::set('services.midtrans.is_production', false);

        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bca',
            'va_number'   => '99887766554433',
            'external_id' => 'SMARTKOS-VA-' . $this->room->id . '-99001',
            'amount'      => 1000000,
            'status'      => 'active',
            'expires_at'  => now()->addDay(),
        ]);

        $orderId     = $vaAccount->external_id;
        $grossAmount = '1000000.00';
        $statusCode  = '200';
        $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'va_payment',
        ]);

        $response = $this->postJson(route('midtrans.callback'), [
            'order_id'          => $orderId,
            'status_code'       => $statusCode,
            'gross_amount'      => $grossAmount,
            'transaction_status'=> 'settlement',
            'fraud_status'      => 'accept',
            'transaction_time'  => now()->format('Y-m-d H:i:s'),
            'settlement_time'   => now()->format('Y-m-d H:i:s'),
            'signature_key'     => $signature,
        ]);

        $response->assertOk();

        $vaPayment = VAPayment::where('order_id', $orderId)->first();
        $this->assertNotNull($vaPayment);

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'va_payment',
            'reference_id'   => $vaPayment->id,
            'type'           => 'income',
        ]);

        $record = FinancialRecord::where('reference_type', 'va_payment')
            ->where('reference_id', $vaPayment->id)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals('income', $record->type);
        $this->assertEquals($this->location->id, $record->location_id);
        $this->assertStringContainsString('VA Midtrans', $record->description);
    }

    public function test_midtrans_non_settlement_webhook_does_not_create_financial_record(): void
    {
        $serverKey = 'SB-Mid-server-test-key-fr';
        Config::set('services.midtrans.server_key', $serverKey);
        Config::set('services.midtrans.is_production', false);

        $vaAccount = MidtransVAAccount::create([
            'room_id'     => $this->room->id,
            'bank_code'   => 'bni',
            'va_number'   => '11223344556677',
            'external_id' => 'SMARTKOS-VA-' . $this->room->id . '-99002',
            'amount'      => 500000,
            'status'      => 'active',
            'expires_at'  => now()->addDay(),
        ]);

        $orderId     = $vaAccount->external_id;
        $grossAmount = '500000.00';
        $statusCode  = '201';
        $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $this->postJson(route('midtrans.callback'), [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'transaction_status' => 'pending',
            'fraud_status'       => 'accept',
            'transaction_time'   => now()->format('Y-m-d H:i:s'),
            'signature_key'      => $signature,
        ])->assertOk();

        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'va_payment',
        ]);
    }

    // ─── Finance Report View Data ───────────────────────────────────────────────

    public function test_finance_report_view_passes_locations_rooms_and_people(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index'))
            ->assertOk();

        $locations = $response->viewData('locations');
        $rooms     = $response->viewData('rooms');
        $people    = $response->viewData('people');

        $this->assertNotNull($locations);
        $this->assertNotNull($rooms);
        $this->assertNotNull($people);

        // 'admin' role should NOT appear in people (only penyewa & staff)
        $this->assertFalse($people->contains('id', $this->admin->id));
        $this->assertTrue($people->contains('id', $this->tenantUser->id));
        $this->assertTrue($people->contains('id', $this->staff->id));
    }

    public function test_finance_report_per_page_filter_is_respected(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            FinancialRecord::create([
                'type'             => 'income',
                'reference_type'   => 'manual_payment',
                'reference_id'     => 100 + $i,
                'description'      => "Sewa bulan {$i}",
                'amount'           => 500000,
                'transaction_date' => now()->toDateString(),
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['time_scope' => 'all', 'per_page' => 5]))
            ->assertOk();

        $this->assertEquals(5, $response->viewData('perPage'));
        $this->assertEquals(5, $response->viewData('financialRecords')->perPage());
    }

    public function test_invalid_scope_is_rejected_with_validation_error(): void
    {
        // The FinanceController uses Validator::validate() which throws ValidationException
        // for unknown scope values, resulting in a redirect with errors.
        $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['scope' => 'invalid_scope']))
            ->assertRedirect();
    }

    public function test_expenses_tab_query_redirects_to_expense_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.finance.index', ['tab' => 'expenses']))
            ->assertRedirect(route('admin.expenses.index'));
    }

    public function test_missing_financial_records_are_automatically_synced_on_finance_index(): void
    {
        // Create an approved manual payment without a financial record
        $manualPayment = ManualPayment::create([
            'room_assignment_id'  => $this->assignment->id,
            'submitted_by'        => $this->tenantUser->id,
            'verified_by'         => $this->admin->id,
            'amount'              => 850000,
            'bank_name'           => 'BCA',
            'bank_account_number' => '987654321',
            'proof_path'          => 'proofs/sync-test.jpg',
            'status'              => 'approved',
            'submitted_at'        => now(),
            'verified_at'         => now(),
        ]);

        $this->assertDatabaseMissing('financial_records', [
            'reference_type' => 'manual_payment',
            'reference_id'   => $manualPayment->id,
        ]);

        // Accessing the finance overview triggers syncIfNeeded
        $this->actingAs($this->admin)->get(route('admin.finance.index'))->assertOk();

        $this->assertDatabaseHas('financial_records', [
            'reference_type' => 'manual_payment',
            'reference_id'   => $manualPayment->id,
            'amount'         => 850000,
            'type'           => 'income',
        ]);
    }
}
