<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    protected $fillable = [
        'bom_id', 'materialCode', 'uom_id', 'qty_per_batch', 'per_dozen', 'use_qty',
        'unit_rate', 'wastage_percent', 'note',
    ];

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }

    public function material()
    {
        return $this->belongsTo(newProduct::class, 'materialCode', 'productCode');
    }

    public function uom()
    {
        return $this->belongsTo(Unit::class, 'uom_id');
    }
}
