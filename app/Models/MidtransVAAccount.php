<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MidtransVAAccount extends Model
{
    use HasFactory;

    protected $table = 'midtrans_va_accounts';

    protected $fillable = [
        'room_id',
        'bank_code',
        'va_number',
        'external_id',
        'amount',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function vaPayments(): HasMany
    {
        return $this->hasMany(VAPayment::class, 'va_account_id');
    }

    /**
     * Check if the VA account is currently active and not expired.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the VA account is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Scope for active non-expired VA accounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Get human-formatted currency amount.
     */
    public function formattedAmount(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get uppercase bank code.
     */
    public function formattedBank(): string
    {
        return strtoupper($this->bank_code);
    }
}
