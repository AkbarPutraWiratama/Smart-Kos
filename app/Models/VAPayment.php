<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VAPayment extends Model
{
    use HasFactory;

    protected $appends = ['status_label'];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SETTLEMENT = 'settlement';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUND = 'refund';

    protected $table = 'va_payments';

    protected $fillable = [
        'va_account_id',
        'room_assignment_id',
        'order_id',
        'transaction_id',
        'amount',
        'transaction_status',
        'transaction_time',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_time' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function vaAccount(): BelongsTo
    {
        return $this->belongsTo(MidtransVAAccount::class, 'va_account_id');
    }

    public function roomAssignment(): BelongsTo
    {
        return $this->belongsTo(RoomAssignment::class);
    }

    public function financialRecord(): HasOne
    {
        return $this->hasOne(FinancialRecord::class, 'reference_id')->where('reference_type', 'va_payment');
    }

    /**
     * Check if payment is settled / paid.
     */
    public function isPaid(): bool
    {
        return in_array($this->transaction_status, ['settlement', 'paid', 'capture', 'success'], true)
            && $this->paid_at !== null;
    }

    /**
     * Check if payment is in pending status.
     */
    public function isPending(): bool
    {
        return $this->transaction_status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is expired.
     */
    public function isExpired(): bool
    {
        return in_array($this->transaction_status, ['expire', self::STATUS_EXPIRED], true);
    }

    /**
     * Check if payment is cancelled or denied.
     */
    public function isCancelled(): bool
    {
        return in_array($this->transaction_status, ['deny', 'cancel', self::STATUS_CANCELLED], true);
    }

    /**
     * Check if payment is refunded.
     */
    public function isRefund(): bool
    {
        return $this->transaction_status === self::STATUS_REFUND;
    }

    /**
     * Human-readable Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->transaction_status) {
            'settlement', 'paid', 'capture', 'success' => 'Lunas',
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            'expire', self::STATUS_EXPIRED => 'Kedaluwarsa',
            'deny', 'cancel', self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_REFUND => 'Dikembalikan',
            default => ucfirst((string) $this->transaction_status),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusLabel();
    }

    /**
     * Tailwind CSS badge styling classes for status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->transaction_status) {
            'settlement', 'paid', 'capture', 'success' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
            self::STATUS_PENDING => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            'expire', self::STATUS_EXPIRED => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            'deny', 'cancel', self::STATUS_CANCELLED => 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
            self::STATUS_REFUND => 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400',
            default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        };
    }

    /**
     * Format currency amount to Rupiah string.
     */
    public function formattedAmount(): string
    {
        return 'Rp ' . number_format((float) $this->amount, 0, ',', '.');
    }

    /**
     * Scope for settled/paid payments.
     */
    public function scopeSettled(Builder $query): Builder
    {
        return $query->whereIn('transaction_status', ['settlement', 'paid', 'capture', 'success'])
            ->whereNotNull('paid_at');
    }

    /**
     * Scope for pending payments.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('transaction_status', self::STATUS_PENDING);
    }

    /**
     * Scope for expired payments.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereIn('transaction_status', ['expire', self::STATUS_EXPIRED]);
    }
}

