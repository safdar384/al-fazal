<?php

namespace App\Support;

use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;

/**
 * Resolve ledger account_id values for a bank (GL accounts row BANK:{code}).
 * ledgers.account_id FK references accounts.id — never use bank_accounts.id directly.
 */
class BankLedgerQuery
{
    public static function glAccountCode(BankAccount $bank): string
    {
        return 'BANK:'.$bank->bankAccount;
    }

    /**
     * Ensure a GL accounts row exists for this bank and return its id.
     */
    public static function ensureGlAccount(BankAccount $bank): int
    {
        $code = self::glAccountCode($bank);
        $existingId = DB::table('accounts')->where('account_code', $code)->value('id');

        if ($existingId) {
            DB::table('accounts')->where('id', $existingId)->update([
                'account_name' => $bank->bankName,
                'account_type' => 'Bank',
                'status' => 'Active',
            ]);

            return (int) $existingId;
        }

        return (int) DB::table('accounts')->insertGetId([
            'account_code' => $code,
            'account_name' => $bank->bankName,
            'account_type' => 'Bank',
            'opening_balance' => 0,
            'status' => 'Active',
        ]);
    }

    /**
     * When bankAccount code is renamed, move the GL account_code with it.
     */
    public static function syncGlAccount(BankAccount $bank, ?string $previousBankAccount = null): int
    {
        if ($previousBankAccount !== null && $previousBankAccount !== '' && $previousBankAccount !== $bank->bankAccount) {
            $oldCode = 'BANK:'.$previousBankAccount;
            $newCode = self::glAccountCode($bank);
            $old = DB::table('accounts')->where('account_code', $oldCode)->first();

            if ($old) {
                $conflict = DB::table('accounts')
                    ->where('account_code', $newCode)
                    ->where('id', '!=', $old->id)
                    ->exists();

                if (! $conflict) {
                    DB::table('accounts')->where('id', $old->id)->update([
                        'account_code' => $newCode,
                        'account_name' => $bank->bankName,
                        'account_type' => 'Bank',
                        'status' => 'Active',
                    ]);

                    return (int) $old->id;
                }
            }
        }

        return self::ensureGlAccount($bank);
    }

    /**
     * @return int[]
     */
    public static function ledgerAccountIds(BankAccount $bank): array
    {
        $ids = [self::ensureGlAccount($bank)];

        // Legacy rows that incorrectly used bank_accounts.id (only if that id also exists in accounts)
        if (DB::table('accounts')->where('id', $bank->id)->exists()) {
            $ids[] = (int) $bank->id;
        }

        return array_values(array_unique($ids));
    }

    public static function signedBalance(BankAccount $bank, ?string $beforeDate = null): float
    {
        $query = DB::table('ledgers')
            ->whereIn('account_id', self::ledgerAccountIds($bank));

        if ($beforeDate !== null) {
            $query->where('entry_date', '<', $beforeDate);
        }

        return (float) ($query
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance') ?? 0);
    }
}
