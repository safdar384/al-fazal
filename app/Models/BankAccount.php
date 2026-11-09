<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bank_accounts'; // explicitly set table name

    protected $fillable = [
        'bankAccount',
        'bankName',
        'bankAddress',
        'branchCode',
    ];

    public $timestamps = false;

    /**
     * Bank account code to show in parentheses on printed invoices, or null when it would repeat the label
     * (e.g. "Cash Book" with account "CASH BOOK" → print without parens).
     */
    public function invoicePrintAccountSuffix(): ?string
    {
        $acct = trim((string) ($this->bankAccount ?? ''));
        if ($acct === '') {
            return null;
        }

        $normalize = static function (string $s): string {
            return strtoupper(preg_replace('/\s+/u', ' ', trim($s)));
        };

        $lettersOnly = static function (string $s): string {
            return preg_replace('/[^A-Z]/', '', strtoupper($s));
        };

        $na = $normalize($acct);
        $name = trim((string) ($this->bankName ?? ''));
        $nn = $name !== '' ? $normalize($name) : '';

        if (in_array($na, ['CASH BOOK', 'CASHBOOK', 'CASH'], true)) {
            return null;
        }

        if ($nn !== '' && $na === $nn) {
            return null;
        }

        // Text-only account same as bank title (e.g. Cash Book / CASH BOOK) — do not repeat on print
        if (! preg_match('/\d/', $acct)) {
            $a = $lettersOnly($acct);
            $b = $lettersOnly($name);
            if ($a !== '' && $b !== '' && $a === $b) {
                return null;
            }
        }

        return $acct;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (BankAccount $model) {
            foreach (['bankAccount', 'bankName', 'bankAddress'] as $field) {
                if ($model->{$field} !== null && $model->{$field} !== '') {
                    $model->{$field} = ucwords(strtolower(trim((string) $model->{$field})));
                }
            }

            if ($model->branchCode !== null && $model->branchCode !== '') {
                $model->branchCode = strtoupper(trim((string) $model->branchCode));
            }
        });
    }
}
