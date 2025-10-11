<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewCustomer extends Model
{
 protected $table = 'newcustomers'; // optional if table name matches

    protected $fillable = [
        'custCode',
        'custArea',
        'bussnessName',
        'custName',
        'custAddress',
        'custContect',
        'custCity',
        'custPage',
        'custdiscount',
        'custPayTermDay',
        'custCreditLimit',
        'custOppDate',
        'custCatagory',
        
    ];
    public $timestamps = false;

    /**
     * Generate a unique customer code
     *
     * @return string
     */
    public static function generateUniqueCode(): string
    {
        do {
            // Get the latest customer code
            $latestCustomer = self::orderBy('id', 'desc')->first();
            
            if ($latestCustomer && preg_match('/CUST(\d+)/', $latestCustomer->custCode, $matches)) {
                $number = (int)$matches[1] + 1;
            } else {
                $number = 1;
            }
            
            $code = 'CUST' . str_pad($number, 3, '0', STR_PAD_LEFT);
            
        } while (self::where('custCode', $code)->exists());
        
        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 🔹 Auto-generate code if not provided
            if (empty($model->custCode)) {
                $model->custCode = self::generateUniqueCode();
            }

            // 🔹 Always uppercase for codes
            if (!empty($model->custCode)) {
                $model->custCode = strtoupper($model->custCode);
            }
            if (!empty($model->custArea)) {
                $model->custArea = strtoupper($model->custArea);
            }

            // 🔹 Proper Case for names, address, city, page, category
            foreach (['bussnessName', 'custName', 'custAddress', 'custCity', 'custPage', 'custCatagory'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }

            // 🔹 Contact number trim
            if (!empty($model->custContect)) {
                $model->custContect = trim($model->custContect);
            }
        });

        static::updating(function ($model) {
            // 🔹 Always uppercase for codes
            if (!empty($model->custCode)) {
                $model->custCode = strtoupper($model->custCode);
            }
            if (!empty($model->custArea)) {
                $model->custArea = strtoupper($model->custArea);
            }

            // 🔹 Proper Case for names, address, city, page, category
            foreach (['bussnessName', 'custName', 'custAddress', 'custCity', 'custPage', 'custCatagory'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }

            // 🔹 Contact number trim
            if (!empty($model->custContect)) {
                $model->custContect = trim($model->custContect);
            }
        });
    }
}


