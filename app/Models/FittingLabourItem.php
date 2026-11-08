<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FittingLabourItem extends Model
{
    protected $table = 'fitting_labour_items';

    protected $fillable = [
        'fitting_labour_id',
        'txn_no',
        'product_code',
        'product_name',
        'work_name',
        'variant_id',
        'variant_name',
        'variant_value',
        'qty',
        'rate',
        'amount',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function fittingLabour(): BelongsTo
    {
        return $this->belongsTo(FittingLabour::class, 'fitting_labour_id');
    }
}
