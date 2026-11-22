<?php

namespace App\Models;

use App\Models\LocationPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'google_maps_url',
        'description',
        'photo_path',
        'floor_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'floor_count' => 'integer',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(LocationPhoto::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function financialRecords(): HasMany
    {
        return $this->hasMany(FinancialRecord::class);
    }

    public function postTargets(): HasMany
    {
        return $this->hasMany(PostTarget::class);
    }

    public function hasOccupiedRooms(): bool
    {
        return $this->rooms()->whereHas('activeAssignment')->exists();
    }

    /**
     * Generate an embeddable Google Maps iframe URL from google_maps_url or address.
     */
    public function getMapEmbedUrlAttribute(): string
    {
        $url = $this->google_maps_url;
        $fallbackAddress = $this->address ?? $this->name;

        // If it's already an embed URL, use as-is
        if ($url && str_contains($url, 'maps/embed')) {
            return $url;
        }

        if ($url && (str_contains($url, 'maps.google') || str_contains($url, 'google.com/maps') || str_contains($url, 'goo.gl'))) {
            $parsed = parse_url($url);
            parse_str($parsed['query'] ?? '', $qs);

            if (! empty($qs['q'])) {
                $query = $qs['q'];
            } elseif (! empty($qs['query'])) {
                $query = $qs['query'];
            } elseif (preg_match('~/@(-?\d+\.\d+),(-?\d+\.\d+)~', $url, $m)) {
                $query = $m[1] . ',' . $m[2];
            } elseif (preg_match('~/place/([^/@?]+)~', $url, $m)) {
                $query = urldecode(str_replace('+', ' ', $m[1]));
            } else {
                $query = $fallbackAddress;
            }

            return 'https://maps.google.com/maps?q=' . urlencode($query) . '&output=embed&hl=id';
        }

        return 'https://maps.google.com/maps?q=' . urlencode($fallbackAddress) . '&output=embed&hl=id';
    }
}
