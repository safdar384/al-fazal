<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    protected $fillable = ['productCode', 'batch_size', 'batch_uom', 'wastage_percent', 'status', 'remarks'];

    public function items()
    {
        return $this->hasMany(BomItem::class);
    }

    public function labors()
    {
        return $this->hasMany(BomLabor::class);
    }

    public function overheads()
    {
        return $this->hasMany(BomOverhead::class);
    }

    public function product()
    {
        return $this->belongsTo(newProduct::class, 'productCode', 'productCode');
    }
}
