<?php

namespace Tests\Feature;

use App\Models\AdminGoogleAccount;
use App\Models\CashAdvance;
use App\Models\Complaint;
use App\Models\ComplaintAction;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialRecord;
use App\Models\Location;
use App\Models\ManualPayment;
use App\Models\MidtransVAAccount;
use App\Models\PayrollRecord;
use App\Models\PaymentReminder;
use App\Models\Post;
use App\Models\PostTarget;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\VAPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_and_relationships_can_be_instantiated(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());

        $google = AdminGoogleAccount::create([
            'user_id' => $admin->id,
            'google_id' => 'gid_12345',
            'google_email' => 'admin@gmail.com',
            'verified_at' => now(),
        ]);
        $this->assertEquals($admin->id, $google->user->id);

        $location = Location::create([
            'name' => 'Kos Mawar',
            'address' => 'Jl. Mawar No. 1',
            'floor_count' => 2,
            'status' => 'active',
        ]);

        $room = Room::create([
            'location_id' => $location->id,
            'floor_name' => 'Lantai 1',
            'room_number' => '101',
            'rent_amount' => 1500000,
            'status' => 'active',
        ]);
        $this->assertFalse($room->isOccupied());

        $tenantUser = User::create([
            'name' => 'Penyewa Test',
            'username' => 'penyewa_test',
            'email' => 'penyewa@example.com',
            'password' => 'secret123',
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $tenantProfile = TenantProfile::create([
            'user_id' => $tenantUser->id,
            'phone' => '08123456789',
            'address' => 'Alamat Asal',
            'registered_at' => now()->toDateString(),
        ]);

        $assignment = RoomAssignment::create([
            'room_id' => $room->id,
            'tenant_id' => $tenantProfile->id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'rent_amount_snapshot' => 1500000,
        ]);
        $this->assertTrue($room->fresh()->isOccupied());

        $vaAccount = MidtransVAAccount::create([
            'room_id' => $room->id,
            'bank_code' => 'bni',
            'va_number' => '98812345678',
            'external_id' => 'VA-101',
            'amount' => 1500000,
            'status' => 'active',
        ]);

        $vaPayment = VAPayment::create([
            'va_account_id' => $vaAccount->id,
            'room_assignment_id' => $assignment->id,
            'order_id' => 'ORD-VA-001',
            'amount' => 1500000,
            'transaction_status' => 'settlement',
            'paid_at' => now(),
        ]);
        $this->assertEquals($assignment->id, $vaPayment->roomAssignment->id);

        $manualPayment = ManualPayment::create([
            'room_assignment_id' => $assignment->id,
            'submitted_by' => $tenantUser->id,
            'amount' => 1500000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'proof_path' => 'proofs/test.jpg',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        $this->assertEquals('pending', $manualPayment->status);

        $reminder = PaymentReminder::create([
            'room_assignment_id' => $assignment->id,
            'channel' => 'whatsapp',
            'reminder_type' => 'H-3',
            'scheduled_for' => now()->toDateString(),
            'status' => 'pending',
        ]);
        $this->assertEquals('H-3', $reminder->reminder_type);

        $post = Post::create([
            'author_id' => $admin->id,
            'title' => 'Pengumuman Penting',
            'content' => 'Isi pengumuman',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $target = PostTarget::create([
            'post_id' => $post->id,
            'target_type' => 'location',
            'location_id' => $location->id,
        ]);
        $this->assertEquals($location->id, $target->location->id);

        $complaint = Complaint::create([
            'tenant_id' => $tenantProfile->id,
            'title' => 'AC Bocor',
            'description' => 'Air AC menetes',
            'status' => 'belum',
            'submitted_at' => now(),
        ]);

        $staff = User::create([
            'name' => 'Staff Test',
            'username' => 'staff_test',
            'email' => 'staff@example.com',
            'password' => 'secret123',
            'role' => 'staff',
            'status' => 'active',
        ]);

        $action = ComplaintAction::create([
            'complaint_id' => $complaint->id,
            'staff_id' => $staff->id,
            'action_description' => 'Teknisi dijadwalkan',
            'status' => 'dalam_perbaikan',
        ]);
        $this->assertEquals($staff->id, $action->staff->id);

        $category = ExpenseCategory::create([
            'name' => 'Listrik',
            'type' => 'operational',
        ]);

        $expense = Expense::create([
            'location_id' => $location->id,
            'category_id' => $category->id,
            'recorded_by' => $admin->id,
            'title' => 'Token Listrik',
            'amount' => 500000,
            'expense_date' => now()->toDateString(),
        ]);
        $this->assertEquals(500000, (float) $expense->amount);

        $payroll = PayrollRecord::create([
            'staff_id' => $staff->id,
            'recorded_by' => $admin->id,
            'gross_amount' => 2500000,
            'cash_advance_deduction' => 0,
            'net_amount' => 2500000,
            'payment_date' => now()->toDateString(),
        ]);
        $this->assertEquals(2500000, (float) $payroll->net_amount);

        $advance = CashAdvance::create([
            'staff_id' => $staff->id,
            'recorded_by' => $admin->id,
            'amount' => 200000,
            'advance_date' => now()->toDateString(),
            'status' => 'outstanding',
        ]);
        $this->assertEquals('outstanding', $advance->status);

        $finance = FinancialRecord::create([
            'location_id' => $location->id,
            'recorded_by' => $admin->id,
            'type' => 'income',
            'reference_type' => 'va_payment',
            'reference_id' => $vaPayment->id,
            'description' => 'Pembayaran VA Kamar 101',
            'amount' => 1500000,
            'transaction_date' => now()->toDateString(),
        ]);
        $this->assertEquals('income', $finance->type);
    }
}
