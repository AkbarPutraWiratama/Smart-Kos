<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'category_id',
        'recorded_by',
        'title',
        'description',
        'amount',
        'expense_date',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function financialRecord(): HasOne
    {
        return $this->hasOne(FinancialRecord::class, 'reference_id')->where('reference_type', 'expense');
    }

    public function scopeLocation(Builder $query, int $locationId): Builder
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopePeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function formattedAmount(): string
    {
        return 'Rp ' . number_format((float) $this->amount, 0, ',', '.');
    }

    public function hasReceipt(): bool
    {
        return ! empty($this->receipt_path) && Storage::disk('public')->exists($this->receipt_path);
    }

    public function receiptUrl(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_path);
    }
}
