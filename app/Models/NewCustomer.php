<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewCustomer extends Model
{
    protected $table = 'newcustomers'; // optional if table name matches

    /** Fixed customer categories (no DB / modal management). */
    public const CATEGORIES = [
        'Retailers',
        'WholeSale',
        'Raw Material Supplier',
        'Product Supplier',
        'Transport',
    ];

    public const CATEGORY_PRODUCT_SUPPLIER = 'Product Supplier';

    public const CATEGORY_RAW_MATERIAL_SUPPLIER = 'Raw Material Supplier';

    /** Old category values still allowed on edit (existing records). */
    public const LEGACY_CATEGORIES = [
        'Suplier',
        'Supplier',
    ];

    /**
     * Product Supplier appears only in Supplier Transaction / Supplier Ledger
     * (and Customer Management). Exclude from all other party pickers.
     */
    public static function isProductSupplierCategory(?string $category): bool
    {
        return strcasecmp(trim((string) $category), self::CATEGORY_PRODUCT_SUPPLIER) === 0;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\NewCustomer>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\NewCustomer>
     */
    public function scopeExcludeProductSupplier($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('custCatagory')
                ->orWhereRaw('LOWER(TRIM(custCatagory)) <> ?', [strtolower(self::CATEGORY_PRODUCT_SUPPLIER)]);
        });
    }

    /**
     * Parties for Purchase Invoice: Raw Material Supplier (+ legacy Supplier/Suplier).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\NewCustomer>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\NewCustomer>
     */
    public function scopeRawMaterialSuppliers($query)
    {
        $allowed = array_values(array_unique(array_map(
            'strtolower',
            array_merge([self::CATEGORY_RAW_MATERIAL_SUPPLIER], self::LEGACY_CATEGORIES)
        )));
        $placeholders = implode(',', array_fill(0, count($allowed), '?'));

        return $query->whereRaw('LOWER(TRIM(custCatagory)) IN ('.$placeholders.')', $allowed);
    }

    /**
     * Parties for Supplier Transaction / Supplier Ledger: Product Supplier only.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\NewCustomer>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\NewCustomer>
     */
    public function scopeProductSuppliers($query)
    {
        return $query->whereRaw(
            'LOWER(TRIM(custCatagory)) = ?',
            [strtolower(self::CATEGORY_PRODUCT_SUPPLIER)]
        );
    }

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
        'custBalance',
    ];

    public $timestamps = false;

    /**
     * Generate a unique customer code
     */
    public static function generateUniqueCode(): string
    {
        do {
            $latestCustomer = self::orderBy('id', 'desc')->first();

            if ($latestCustomer && preg_match('/CUST(\d+)/', $latestCustomer->custCode, $matches)) {
                $number = (int) $matches[1] + 1;
            } else {
                $number = 1;
            }

            $code = 'CUST'.str_pad($number, 3, '0', STR_PAD_LEFT);

        } while (self::where('custCode', $code)->exists());

        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (NewCustomer $model) {
            if (empty($model->custCode) && ! $model->exists) {
                $model->custCode = self::generateUniqueCode();
            }

            // Code / area code → UPPERCASE
            if (! empty($model->custCode)) {
                $model->custCode = strtoupper(trim((string) $model->custCode));
            }
            if (! empty($model->custArea)) {
                $model->custArea = strtoupper(trim((string) $model->custArea));
            }

            // Text fields → Proper Case
            foreach (['bussnessName', 'custName', 'custAddress', 'custCity', 'custPage'] as $field) {
                if (! empty($model->{$field})) {
                    $model->{$field} = ucwords(strtolower(trim((string) $model->{$field})));
                }
            }

            // Keep exact category labels (WholeSale must not become Wholesale)
            if (! empty($model->custCatagory)) {
                $raw = trim((string) $model->custCatagory);
                foreach (self::CATEGORIES as $cat) {
                    if (strcasecmp($cat, $raw) === 0) {
                        $model->custCatagory = $cat;
                        break;
                    }
                }
            }

            if (! empty($model->custContect)) {
                $model->custContect = trim((string) $model->custContect);
            }
        });
    }
}
