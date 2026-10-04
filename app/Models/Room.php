<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'floor_name',
        'room_number',
        'rent_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rent_amount' => 'decimal:2',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function roomAssignments(): HasMany
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function activeAssignment(): HasOne
    {
        return $this->hasOne(RoomAssignment::class)->where('status', 'active');
    }

    public function midtransVaAccount(): HasOne
    {
        return $this->hasOne(MidtransVAAccount::class);
    }

    public function postTargets(): HasMany
    {
        return $this->hasMany(PostTarget::class);
    }

    public function isOccupied(): bool
    {
        return $this->activeAssignment()->exists();
    }
}
