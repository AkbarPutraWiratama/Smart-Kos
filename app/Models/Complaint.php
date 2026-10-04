<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'image_path',
        'status',
        'highlighted',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'highlighted' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function tenantProfile(): BelongsTo
    {
        return $this->belongsTo(TenantProfile::class, 'tenant_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ComplaintAction::class);
    }
}
