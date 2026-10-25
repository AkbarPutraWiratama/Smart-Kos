<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'tenant_id',
        'start_date',
        'end_date',
        'status',
        'rent_amount_snapshot',
        'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rent_amount_snapshot' => 'decimal:2',
            'deactivated_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenantProfile(): BelongsTo
    {
        return $this->belongsTo(TenantProfile::class, 'tenant_id');
    }

    public function vaPayments(): HasMany
    {
        return $this->hasMany(VAPayment::class);
    }

    public function manualPayments(): HasMany
    {
        return $this->hasMany(ManualPayment::class);
    }

    public function paymentReminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
    }

    /**
     * Get the payment due date for the assignment.
     */
    public function getPaymentDueDate(): \Carbon\Carbon
    {
        if ($this->end_date) {
            return $this->end_date->copy()->startOfDay();
        }

        $today = now()->startOfDay();
        $start = $this->start_date ? $this->start_date->copy()->startOfDay() : $today;
        $dayOfMonth = $start->day;

        $cycleDue = $today->copy()->day(min($dayOfMonth, $today->daysInMonth));

        // If today is past the cycle due date and already paid, next cycle is next month
        if ($this->isPaidForCurrentPeriod() && $cycleDue->lte($today)) {
            $nextMonth = $today->copy()->addMonth();
            return $nextMonth->day(min($dayOfMonth, $nextMonth->daysInMonth));
        }

        return $cycleDue;
    }

    /**
     * Calculate remaining days until payment deadline.
     */
    public function getDaysUntilDue(): int
    {
        $today = now()->startOfDay();
        $dueDate = $this->getPaymentDueDate()->startOfDay();

        return (int) $today->diffInDays($dueDate, false);
    }

    /**
     * Check if rent is paid for current period.
     */
    public function isPaidForCurrentPeriod(): bool
    {
        $hasPaidVA = $this->vaPayments()
            ->whereIn('transaction_status', ['settlement', 'paid', 'capture', 'success'])
            ->where('paid_at', '>=', now()->subDays(30))
            ->exists();

        if ($hasPaidVA) {
            return true;
        }

        return $this->manualPayments()
            ->where('status', 'approved')
            ->where('submitted_at', '>=', now()->subDays(30))
            ->exists();
    }

    /**
     * Check if a manual payment is awaiting verification.
     */
    public function hasPendingPayment(): bool
    {
        return $this->manualPayments()
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Get payment status string.
     */
    public function getPaymentStatus(): string
    {
        if ($this->isPaidForCurrentPeriod()) {
            return 'Lunas';
        }

        if ($this->hasPendingPayment()) {
            return 'Menunggu Verifikasi';
        }

        $days = $this->getDaysUntilDue();
        if ($days < 0) {
            return 'Menunggak';
        }

        if ($days === 0) {
            return 'Jatuh Tempo Hari Ini';
        }

        return 'Belum Bayar';
    }

    /**
     * Get deadline display color category:
     * - more than 7 days → gray
     * - less than 7 days → yellow
     * - today / overdue   → red
     */
    public function getDeadlineColor(): string
    {
        $days = $this->getDaysUntilDue();

        if ($days > 7) {
            return 'gray';
        }

        if ($days > 0 && $days <= 7) {
            return 'yellow';
        }

        return 'red';
    }

    /**
     * Get deadline badge CSS classes.
     */
    public function getDeadlineBadgeClass(): string
    {
        return match ($this->getDeadlineColor()) {
            'gray'   => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700',
            'yellow' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400 border border-warning-200 dark:border-warning-500/20',
            'red'    => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400 border border-error-200 dark:border-error-500/20',
        };
    }

    /**
     * Get human-readable deadline label.
     */
    public function getDeadlineLabel(): string
    {
        $days = $this->getDaysUntilDue();

        if ($days > 7) {
            return "Tersisa {$days} hari (> 7 hari)";
        }

        if ($days > 0) {
            return "Tersisa {$days} hari (< 7 hari)";
        }

        if ($days === 0) {
            return 'Jatuh tempo hari ini';
        }

        $lateDays = abs($days);
        return "Terlambat {$lateDays} hari";
    }
}
