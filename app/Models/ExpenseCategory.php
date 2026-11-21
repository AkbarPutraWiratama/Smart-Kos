<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory;

    public const TYPE_OPERATIONAL = 'operational';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'name',
        'type',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function scopeOperational(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OPERATIONAL);
    }

    public function scopeMaintenance(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MAINTENANCE);
    }

    public function scopeOther(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OTHER);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_OPERATIONAL => 'Operasional',
            self::TYPE_MAINTENANCE => 'Pemeliharaan',
            default => 'Lain-lain',
        };
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            self::TYPE_OPERATIONAL => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400',
            self::TYPE_MAINTENANCE => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    /**
     * Ensure default categories exist in the database.
     */
    public static function seedDefaultsIfEmpty(): void
    {
        if (self::count() > 0) {
            return;
        }

        $defaults = [
            // Operational Expenses
            ['name' => 'Listrik (PLN)', 'type' => self::TYPE_OPERATIONAL],
            ['name' => 'Air Bersih (PDAM / Air Tanah)', 'type' => self::TYPE_OPERATIONAL],
            ['name' => 'Internet & Wi-Fi', 'type' => self::TYPE_OPERATIONAL],
            ['name' => 'Iuran Keamanan & Kebersihan', 'type' => self::TYPE_OPERATIONAL],
            ['name' => 'Perlengkapan Kebersihan', 'type' => self::TYPE_OPERATIONAL],

            // Maintenance / Incidental Expenses
            ['name' => 'Servis & Cuci AC', 'type' => self::TYPE_MAINTENANCE],
            ['name' => 'Penggantian Lampu & Kelistrikan', 'type' => self::TYPE_MAINTENANCE],
            ['name' => 'Perbaikan Pompa Air & Pipa', 'type' => self::TYPE_MAINTENANCE],
            ['name' => 'Perbaikan Bangunan & Pengecatan', 'type' => self::TYPE_MAINTENANCE],
            ['name' => 'Perbaikan Sanitasi & Kamar Mandi', 'type' => self::TYPE_MAINTENANCE],

            // Other
            ['name' => 'Pengeluaran Lain-lain', 'type' => self::TYPE_OTHER],
        ];

        foreach ($defaults as $item) {
            self::firstOrCreate(['name' => $item['name']], ['type' => $item['type']]);
        }
    }
}
