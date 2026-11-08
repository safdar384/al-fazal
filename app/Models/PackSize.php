<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackSize extends Model
{
    protected $table = 'pack_sizes';

    protected $fillable = [
        'packing_type',
        'unit_name',
        'qty',
        'label',
    ];

    public $timestamps = false;

    public const PACKINGS = [
        'Box',
        'Bag',
        'Pakit',
        'Bundle',
    ];

    /** Measure units — Piece / Dozen / Gurs / Kg / Feet */
    public const UNITS = [
        'Piece',
        'Dozen',
        'Gurs',
        'Kg',
        'Feet',
    ];

    /** Suggested units per packing (UI hint; all units still allowed). */
    public const SUGGESTED_UNITS = [
        'Box' => ['Piece', 'Dozen'],
        'Bag' => ['Kg'],
        'Pakit' => ['Gurs'],
        'Bundle' => ['Feet', 'Kg'],
    ];

    public static function makeLabel(string $packing, string $unit, float|int|string $qty): string
    {
        $q = self::formatQty($qty);

        return trim($packing).' · '.$q.' '.trim($unit);
    }

    public static function formatQty(float|int|string $qty): string
    {
        $n = (float) $qty;
        if (abs($n - round($n)) < 0.00001) {
            return (string) (int) round($n);
        }

        return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }

    /**
     * Value stored on product.piecesPerUnit (Dozen → pieces).
     */
    public function piecesPerUnitForProduct(): float
    {
        $qty = (float) $this->qty;
        if (strcasecmp((string) $this->unit_name, 'Dozen') === 0) {
            return $qty * 12;
        }

        return $qty > 0 ? $qty : 1;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (PackSize $model) {
            $model->packing_type = ucwords(strtolower(trim((string) $model->packing_type)));
            $model->unit_name = ucwords(strtolower(trim((string) $model->unit_name)));
            $model->qty = round((float) $model->qty, 2);
            $model->label = self::makeLabel($model->packing_type, $model->unit_name, $model->qty);
        });
    }
}
