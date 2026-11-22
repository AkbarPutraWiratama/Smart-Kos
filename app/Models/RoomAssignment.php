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
        'payment_cycle',
        'billing_due_day',
        'next_due_date',
        'deactivated_at',
    ];

    protected $appends = [
        'has_pending_payment',
        'payment_status',
        'deadline_label',
        'deadline_color',
        'deadline_badge_class',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rent_amount_snapshot' => 'decimal:2',
            'billing_due_day' => 'integer',
            'next_due_date' => 'date',
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
        $today = now()->startOfDay();
        $start = $this->start_date ? $this->start_date->copy()->startOfDay() : $today;
        $dueDay = (int) ($this->billing_due_day ?: $start->day);

        if ($this->payment_cycle === 'yearly') {
            $dueMonth = $start->month;
            $dueYear = $today->year;
            $daysInDueMonth = \Carbon\Carbon::create($dueYear, $dueMonth, 1)->daysInMonth;
            $cycleDue = $today->copy()
                ->month($dueMonth)
                ->day(min($dueDay, $daysInDueMonth));

            if ($this->isPaidForCurrentPeriod() && $cycleDue->lte($today)) {
                $nextYear = $today->copy()->addYear();
                $daysInNextMonth = \Carbon\Carbon::create($nextYear->year, $dueMonth, 1)->daysInMonth;

                return $nextYear
                    ->month($dueMonth)
                    ->day(min($dueDay, $daysInNextMonth))
                    ->startOfDay();
            }

            return $cycleDue;
        }

        $daysInCurrentMonth = $today->daysInMonth;
        $cycleDue = $today->copy()->day(min($dueDay, $daysInCurrentMonth));

        if ($this->isPaidForCurrentPeriod() && $cycleDue->lte($today)) {
            $nextMonth = $today->copy()->addMonth();
            $daysInNextMonth = $nextMonth->daysInMonth;

            return $nextMonth
                ->day(min($dueDay, $daysInNextMonth))
                ->startOfDay();
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
     * Accessor: whether this assignment has any pending manual payment.
     * Avoids extra queries when the manualPayments relation is already eager-loaded.
     */
    public function getHasPendingPaymentAttribute(): bool
    {
        if ($this->relationLoaded('manualPayments')) {
            return $this->manualPayments->contains(
                fn (ManualPayment $payment): bool => $payment->isPending()
            );
        }

        return $this->hasPendingPayment();
    }

    public function getPaymentStatusAttribute(): string
    {
        return $this->getPaymentStatus();
    }

    public function getDeadlineLabelAttribute(): string
    {
        return $this->getDeadlineLabel();
    }

    public function getDeadlineColorAttribute(): string
    {
        return $this->getDeadlineColor();
    }

    public function getDeadlineBadgeClassAttribute(): string
    {
        return $this->getDeadlineBadgeClass();
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
