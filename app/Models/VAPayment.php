<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VAPayment extends Model
{
    use HasFactory;

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
}
