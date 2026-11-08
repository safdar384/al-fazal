<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomMaterial extends Model
{
    protected $fillable = [
        'material_code',
        'material_name',
        'packing',
        'rate',
    ];

    protected $casts = [
        'rate' => 'float',
    ];

    public static function generateUniqueCode(): string
    {
        do {
            $latest = self::query()->orderByDesc('id')->first();

            if ($latest && preg_match('/MAT(\d+)/', $latest->material_code, $matches)) {
                $number = (int) $matches[1] + 1;
            } else {
                $number = 1;
            }

            $code = 'MAT'.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
        } while (self::query()->where('material_code', $code)->exists());

        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->material_code)) {
                $model->material_code = self::generateUniqueCode();
            }

            $model->material_code = strtoupper($model->material_code);

            foreach (['material_name', 'packing'] as $field) {
                if (! empty($model->$field)) {
                    $model->$field = ucwords(strtolower(trim($model->$field)));
                }
            }
        });

        static::updating(function (self $model) {
            foreach (['material_name', 'packing'] as $field) {
                if ($model->isDirty($field) && ! empty($model->$field)) {
                    $model->$field = ucwords(strtolower(trim($model->$field)));
                }
            }
        });
    }
}
