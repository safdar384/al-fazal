<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guds extends Model
{
    protected $table = 'guds';

    protected $fillable = [
        'gudsName',
        'gudsExpanse',
        'gudsLoader',
        'status',
    ];

    protected $casts = [
        'gudsExpanse' => 'decimal:2',
        'gudsLoader' => 'decimal:2',
    ];
}
