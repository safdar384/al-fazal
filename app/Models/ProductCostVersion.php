<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCostVersion extends Model
{
    protected $fillable = ['productCode', 'effective_from', 'is_active', 'material_cost', 'labor_cost', 'overhead_cost', 'wastage_cost', 'batch_cost', 'unit_cost', 'currency', 'remarks'];

    protected $casts = ['effective_from' => 'date', 'is_active' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(newProduct::class, 'productCode', 'productCode');
    }
}
