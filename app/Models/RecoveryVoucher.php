<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;  // ✅ add this

class RecoveryVoucher extends Model
{
    use HasFactory;

    protected $table = 'market_vouchers';

    protected $fillable = [
        'voucher_date',
        'customerArea',
        'customerCode',
        'description',
        'recovery',
        'discount',
        'misc',
        'preBalance',
        'lastBalance',
        'market_vouchers_id',
    ];

    protected $casts = [
        'voucher_date' => 'date',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            // Proper case for description
            if (! empty($model->description)) {
                $model->description = ucwords(strtolower($model->description));
            }
        });

    }

    public function customer()
    {
        return $this->belongsTo(NewCustomer::class, 'customerCode', 'custCode');
    }
}
