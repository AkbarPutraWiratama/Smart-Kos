<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAdvance extends Model
{
    use HasFactory;

    public const STATUS_OUTSTANDING = 'outstanding';
    public const STATUS_PARTIALLY_REPAID = 'partially_repaid';
    public const STATUS_REPAID = 'repaid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'staff_id',
        'recorded_by',
        'amount',
        'advance_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'advance_date' => 'date',
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

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OUTSTANDING);
    }

    public function scopeRepaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REPAID);
    }

    public function scopeStaff(Builder $query, int $staffId): Builder
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopePeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('advance_date', [$startDate, $endDate]);
    }

    public function isOutstanding(): bool
    {
        return $this->status === self::STATUS_OUTSTANDING;
    }

    public function isRepaid(): bool
    {
        return $this->status === self::STATUS_REPAID;
    }

    public function isPartiallyRepaid(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_REPAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OUTSTANDING => 'Belum Lunas',
            self::STATUS_PARTIALLY_REPAID => 'Sebagian Lunas',
            self::STATUS_REPAID => 'Lunas',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_OUTSTANDING => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            self::STATUS_PARTIALLY_REPAID => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400',
            self::STATUS_REPAID => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    public function formattedAmount(): string
    {
        return 'Rp ' . number_format((float) $this->amount, 0, ',', '.');
    }

    public function formattedAdvanceDate(): string
    {
        return $this->advance_date ? $this->advance_date->translatedFormat('d F Y') : '-';
    }
}
