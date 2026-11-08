<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCheque extends Model
{
    public const STATUS_PENDING = 'Pending';

    public const STATUS_CLEARED = 'Cleared';

    public const STATUS_BOUNCED = 'Bounced';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $table = 'customer_cheques';

    protected $fillable = [
        'customer_code',
        'customer_area',
        'bank_name',
        'cheque_no',
        'amount',
        'cheque_date',
        'due_date',
        'original_due_date',
        'description',
        'status',
        'cleared_at',
        'ledger_id',
        'worker_id',
        'worker_code',
        'worker_name',
        'payee_type',
        'payee_code',
        'payee_name',
        'pay_remark',
        'paid_at',
    ];

    public const PAYEE_BANK = 'bank';

    public const PAYEE_PARTY = 'party';

    protected $casts = [
        'amount' => 'decimal:2',
        'cheque_date' => 'date',
        'due_date' => 'date',
        'original_due_date' => 'date',
        'cleared_at' => 'date',
        'paid_at' => 'date',
    ];

    public function isPaid(): bool
    {
        return $this->paid_at !== null || filled($this->payee_name);
    }

    /**
     * Days from original due until clear (or until today if still pending).
     */
    public function delayDays(?\Carbon\CarbonInterface $asOf = null): int
    {
        $start = $this->original_due_date ?? $this->due_date;
        if (! $start) {
            return 0;
        }

        $end = $this->cleared_at
            ?? ($this->status === self::STATUS_CLEARED ? ($asOf ?? now()) : ($asOf ?? now()));

        $days = $start->startOfDay()->diffInDays($end->copy()->startOfDay(), false);

        return max(0, (int) $days);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(NewCustomer::class, 'customer_code', 'custCode');
    }
}
