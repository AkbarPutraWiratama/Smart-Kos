<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialRecord extends Model
{
    use HasFactory;

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'location_id',
        'recorded_by',
        'type',
        'reference_type',
        'reference_id',
        'description',
        'amount',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope query to income records only.
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    /**
     * Scope query to expense records only.
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    /**
     * Scope query to specific location.
     */
    public function scopeLocation(Builder $query, int $locationId): Builder
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope query to a date range.
     */
    public function scopePeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    public function formattedAmount(): string
    {
        return 'Rp ' . number_format((float) $this->amount, 0, ',', '.');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_INCOME => 'Pemasukan',
            self::TYPE_EXPENSE => 'Pengeluaran',
            default => ucfirst((string) $this->type),
        };
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            self::TYPE_INCOME => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            self::TYPE_EXPENSE => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    /**
     * Automatically create or update financial reporting record from a settled VA payment.
     */
    public static function recordFromVAPayment(VAPayment $vaPayment): ?self
    {
        if (! $vaPayment->isPaid()) {
            return null;
        }

        $assignment = $vaPayment->roomAssignment;
        $room = $assignment?->room;
        $tenantUser = $assignment?->tenantProfile?->user;
        $tenantName = $tenantUser?->name ?? 'Penyewa';
        $locationId = $room?->location_id;
        $roomNumber = $room?->room_number ?? '-';

        return self::updateOrCreate(
            [
                'reference_type' => 'va_payment',
                'reference_id'   => $vaPayment->id,
            ],
            [
                'location_id'      => $locationId,
                'recorded_by'      => null, // System automated transaction
                'type'             => self::TYPE_INCOME,
                'description'      => "Pembayaran Sewa VA Midtrans ({$vaPayment->order_id}) - Kamar {$roomNumber} ({$tenantName})",
                'amount'           => $vaPayment->amount,
                'transaction_date' => $vaPayment->paid_at ? $vaPayment->paid_at->toDateString() : now()->toDateString(),
            ]
        );
    }

    /**
     * Automatically create or update financial reporting record from an approved manual payment.
     */
    public static function recordFromManualPayment(ManualPayment $manualPayment, ?int $recordedBy = null): ?self
    {
        if (! $manualPayment->isApproved()) {
            return null;
        }

        $assignment = $manualPayment->roomAssignment;
        $room = $assignment?->room;
        $tenantUser = $assignment?->tenantProfile?->user ?? $manualPayment->submittedBy;
        $tenantName = $tenantUser?->name ?? 'Penyewa';
        $locationId = $room?->location_id;
        $roomNumber = $room?->room_number ?? '-';

        return self::updateOrCreate(
            [
                'reference_type' => 'manual_payment',
                'reference_id'   => $manualPayment->id,
            ],
            [
                'location_id'      => $locationId,
                'recorded_by'      => $recordedBy ?? $manualPayment->verified_by,
                'type'             => self::TYPE_INCOME,
                'description'      => "Pembayaran Sewa Transfer Manual ({$manualPayment->bank_name}) - Kamar {$roomNumber} ({$tenantName})",
                'amount'           => $manualPayment->amount,
                'transaction_date' => $manualPayment->verified_at ? $manualPayment->verified_at->toDateString() : now()->toDateString(),
            ]
        );
    }

    /**
     * Automatically create or update financial reporting record from an expense.
     */
    public static function recordFromExpense(Expense $expense): ?self
    {
        $categoryName = $expense->category?->name ?? 'Pengeluaran';
        $locationName = $expense->location?->name ? " [{$expense->location->name}]" : '';

        return self::updateOrCreate(
            [
                'reference_type' => 'expense',
                'reference_id'   => $expense->id,
            ],
            [
                'location_id'      => $expense->location_id,
                'recorded_by'      => $expense->recorded_by,
                'type'             => self::TYPE_EXPENSE,
                'description'      => "Pengeluaran: {$expense->title} ({$categoryName}){$locationName}",
                'amount'           => $expense->amount,
                'transaction_date' => $expense->expense_date ? $expense->expense_date->toDateString() : now()->toDateString(),
            ]
        );
    }

    /**
     * Automatically create or update financial reporting record from a payroll payment.
     */
    public static function recordFromPayroll(PayrollRecord $payroll): ?self
    {
        $staffName = $payroll->staff?->name ?? 'Staff';
        $deductionNote = (float) $payroll->cash_advance_deduction > 0
            ? ' (Kotor: Rp ' . number_format((float) $payroll->gross_amount, 0, ',', '.') . ', Potong Kasbon: Rp ' . number_format((float) $payroll->cash_advance_deduction, 0, ',', '.') . ')'
            : '';

        return self::updateOrCreate(
            [
                'reference_type' => 'payroll',
                'reference_id'   => $payroll->id,
            ],
            [
                'location_id'      => null, // Staff operational salary across properties
                'recorded_by'      => $payroll->recorded_by,
                'type'             => self::TYPE_EXPENSE,
                'description'      => "Gaji Staff: {$staffName}{$deductionNote}",
                'amount'           => $payroll->net_amount,
                'transaction_date' => $payroll->payment_date ? $payroll->payment_date->toDateString() : now()->toDateString(),
            ]
        );
    }

    /**
     * Sync and backfill all existing approved/settled payments, expenses, and payrolls into financial reporting layer.
     */
    public static function syncFromPayments(): int
    {
        $count = 0;

        foreach (VAPayment::settled()->with('roomAssignment.room', 'roomAssignment.tenantProfile.user')->get() as $vp) {
            self::recordFromVAPayment($vp);
            $count++;
        }

        foreach (ManualPayment::approved()->with('roomAssignment.room', 'roomAssignment.tenantProfile.user', 'submittedBy')->get() as $mp) {
            self::recordFromManualPayment($mp);
            $count++;
        }

        foreach (Expense::with('category', 'location')->get() as $exp) {
            self::recordFromExpense($exp);
            $count++;
        }

        foreach (PayrollRecord::with('staff')->get() as $pr) {
            self::recordFromPayroll($pr);
            $count++;
        }

        return $count;
    }
}
