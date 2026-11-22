<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialRecord extends Model
{
    use HasFactory;

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

    public function vaPayment(): BelongsTo
    {
        return $this->belongsTo(VAPayment::class, 'reference_id');
    }

    public function manualPayment(): BelongsTo
    {
        return $this->belongsTo(ManualPayment::class, 'reference_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'reference_id');
    }

    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class, 'reference_id');
    }

    public function cashAdvance(): BelongsTo
    {
        return $this->belongsTo(CashAdvance::class, 'reference_id');
    }
}
