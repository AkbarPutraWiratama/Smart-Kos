<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ManualPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_assignment_id',
        'submitted_by',
        'amount',
        'bank_name',
        'bank_account_number',
        'proof_path',
        'status',
        'verified_by',
        'submitted_at',
        'verified_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function roomAssignment(): BelongsTo
    {
        return $this->belongsTo(RoomAssignment::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function financialRecord(): HasOne
    {
        return $this->hasOne(FinancialRecord::class, 'reference_id')->where('reference_type', 'manual_payment');
    }
}
