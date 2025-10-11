<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewAgent extends Model
{
    protected $table = 'new_ajensts'; // Keep table name for now (will migrate later)

    protected $fillable = [
        'ajentCode',
        'ajentName',
        'AjentAdvance',
        'ajentContact',
        'ajentAdderss',
        'ajentCommission',
        'ajentBalance',
    ];
    public $timestamps = false;

    /**
     * Generate a unique agent code
     *
     * @return string
     */
    public static function generateUniqueCode(): string
    {
        do {
            // Get the latest agent code
            $latestAgent = self::orderBy('id', 'desc')->first();
            
            if ($latestAgent && preg_match('/AGT(\d+)/', $latestAgent->ajentCode, $matches)) {
                $number = (int)$matches[1] + 1;
            } else {
                $number = 1;
            }
            
            $code = 'AGT' . str_pad($number, 3, '0', STR_PAD_LEFT);
            
        } while (self::where('ajentCode', $code)->exists());
        
        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 🔹 Auto-generate code if not provided
            if (empty($model->ajentCode)) {
                $model->ajentCode = self::generateUniqueCode();
            }
            
            // 🔹 Uppercase code
            if (!empty($model->ajentCode)) {
                $model->ajentCode = strtoupper($model->ajentCode);
            }

            // 🔹 Proper case fields
            foreach (['ajentName', 'ajentAdderss'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });

        static::updating(function ($model) {
            // 🔹 Uppercase code
            if (!empty($model->ajentCode)) {
                $model->ajentCode = strtoupper($model->ajentCode);
            }

            // 🔹 Proper case fields
            foreach (['ajentName', 'ajentAdderss'] as $field) {
                if (!empty($model->$field)) {
                    $model->$field = ucwords(strtolower($model->$field));
                }
            }
        });
    }
}
