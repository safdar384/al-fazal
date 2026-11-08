<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomOverhead extends Model
{
    protected $fillable = ['bom_id', 'overhead_name', 'type', 'allocation_base', 'value'];

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
