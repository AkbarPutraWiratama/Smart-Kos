<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'content',
        'image_path',
        'status',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at'   => 'datetime',
            'deleted_at'   => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PostTarget::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('order');
    }

    /**
     * Whether this post has passed its expiry date.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
