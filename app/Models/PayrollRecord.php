<?php

namespace App\Models;

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
}
