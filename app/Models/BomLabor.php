<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomLabor extends Model
{
    protected $fillable = ['bom_id', 'operation_name', 'time_minutes', 'rate_per_minute'];

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
