<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'recorded_by',
        'gross_amount',
        'cash_advance_deduction',
        'net_amount',
        'payment_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'cash_advance_deduction' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function financialRecord(): HasOne
    {
        return $this->hasOne(FinancialRecord::class, 'reference_id')->where('reference_type', 'payroll');
    }

    /**
     * Scope query to specific staff.
     */
    public function scopeStaff(Builder $query, int $staffId): Builder
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope query to a date range.
     */
    public function scopePeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function formattedGrossAmount(): string
    {
        return 'Rp ' . number_format((float) $this->gross_amount, 0, ',', '.');
    }

    public function formattedDeduction(): string
    {
        return 'Rp ' . number_format((float) $this->cash_advance_deduction, 0, ',', '.');
    }

    public function formattedNetAmount(): string
    {
        return 'Rp ' . number_format((float) $this->net_amount, 0, ',', '.');
    }

    public function formattedPaymentDate(): string
    {
        return $this->payment_date ? $this->payment_date->translatedFormat('d F Y') : '-';
    }
}
