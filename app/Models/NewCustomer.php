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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
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

            // 🔹 Contact number ko trim kar do (just in case)
            if (!empty($model->custContect)) {
                $model->custContect = trim($model->custContect);
            }
        });
    }
}

