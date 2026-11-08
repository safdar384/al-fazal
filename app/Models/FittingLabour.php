<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FittingLabour extends Model
{
    public const TYPE_WORK = 'Work';

    public const TYPE_SALE = 'Sale'; // legacy

    public const TYPE_PURCHASE = 'Purchase'; // legacy

    public const TYPE_CASH = 'Cash';

    protected $table = 'fitting_labours';

    protected $fillable = [
        'txn_no',
        'labour_id',
        'labour_code',
        'labour_name',
        'txn_type',
        'entry_type',
        'txn_date',
        'total_amount',
        'remarks',
        'labour_account_transaction_id',
        'status',
    ];

    protected $casts = [
        'txn_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FittingLabourItem::class, 'fitting_labour_id');
    }

    public function labour(): BelongsTo
    {
        return $this->belongsTo(Labour::class, 'labour_id');
    }
}
