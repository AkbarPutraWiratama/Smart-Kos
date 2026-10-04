<?php

namespace App\Models;

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
}
