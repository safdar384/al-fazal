<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientUrlRestriction extends Model
{
    protected $fillable = [
        'pattern',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return list<string>
     */
    public static function activePatterns(): array
    {
        if (! static::query()->exists()) {
            return config('client_access.admin_only_patterns', []);
        }

        return static::query()
            ->where('is_active', true)
            ->orderBy('pattern')
            ->pluck('pattern')
            ->all();
    }
}
