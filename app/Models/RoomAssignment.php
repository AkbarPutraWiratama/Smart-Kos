<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'tenant_id',
        'start_date',
        'end_date',
        'status',
        'rent_amount_snapshot',
        'deactivated_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rent_amount_snapshot' => 'decimal:2',
            'deactivated_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenantProfile(): BelongsTo
    {
        return $this->belongsTo(TenantProfile::class, 'tenant_id');
    }

    public function vaPayments(): HasMany
    {
        return $this->hasMany(VAPayment::class);
    }

    public function manualPayments(): HasMany
    {
        return $this->hasMany(ManualPayment::class);
    }

    public function paymentReminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
    }
}
