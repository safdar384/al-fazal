<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packing extends Model
{
    protected $table = 'packing';

    protected $fillable = ['pakingName', 'baseUnit', 'qtyInPack'];

    public $timestamps = false;

    /** Common base units for packing. */
    public const BASE_UNITS = [
        'Piece',
        'Dozen',
        'Gurs',
        'Kg',
        'Feet',
    ];

    public function displayLabel(): string
    {
        $name = (string) $this->pakingName;
        $qty = (int) ($this->qtyInPack ?: 1);
        $unit = (string) ($this->baseUnit ?: 'PIECE');

        return trim($name).' · '.$qty.' '.$unit;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (! empty($model->pakingName)) {
                $model->pakingName = strtoupper(trim((string) $model->pakingName));
            }
            if (! empty($model->baseUnit)) {
                $model->baseUnit = strtoupper(trim((string) $model->baseUnit));
            }
            $model->qtyInPack = max(1, (int) ($model->qtyInPack ?: 1));
        });
    }
}
