<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DownloadsDomPdf;
use App\Models\BankAccount;
use App\Models\SaleInvoiceBankPayment;
use App\Support\BankLedgerQuery;
use App\Support\ListPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class BankAccountTransactionController extends Controller
{
    use DownloadsDomPdf;

    public const LEDGER_CATEGORY = 'BankAccountTransaction';

    public const LOCKED_MESSAGE = 'Only entries created on the Bank Transaction page can be edited or deleted.';

    protected function properCase(string $value): string
    {
        return ucwords(strtolower(trim($value)));
    }

    protected function bankLedgersBaseQuery(BankAccount $bank)
    {
        return DB::table('ledgers')
            ->whereIn('account_id', BankLedgerQuery::ledgerAccountIds($bank))
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Entries created on this page only (not Cash Sale / invoice bank payments).
     */
    protected function isPageManagedEntry(?object $row): bool
    {
        if (! $row || ($row->category ?? null) !== self::LEDGER_CATEGORY) {
            return false;
        }

        return ! SaleInvoiceBankPayment::query()->where('ledger_id', $row->id)->exists();
    }

    protected function findPageManagedEntry(int $id): ?object
    {
        $row = DB::table('ledgers')->where('id', $id)->first();
        if (! $this->isPageManagedEntry($row)) {
            return null;
        }

        return $row;
    }

    protected function transactionListQuery(Request $request)
    {
        $query = DB::table('ledgers as l')
            ->join('accounts as a', 'a.id', '=', 'l.account_id')
            ->join('bank_accounts as b', function ($join) {
                $join->whereRaw("a.account_code = CONCAT('BANK:', b.`bankAccount`)");
            })
            ->where('l.category', self::LEDGER_CATEGORY)
            ->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('sale_invoice_bank_payments as p')
                    ->whereColumn('p.ledger_id', 'l.id');
            })
            ->orderByDesc('l.id')
            ->select(
                'l.id',
                'l.entry_date',
                'l.description',
                'l.debit',
                'l.credit',
                'b.bankName',
                'b.bankAccount as bankCode'
            );

        $code = trim((string) $request->query('bankAccount', ''));
        if ($code !== '') {
            $query->where('b.bankAccount', $code);
        }

        return $query;
    }

    public function index(Request $request): View
    {
        $bankAccounts = BankAccount::orderBy('bankName', 'asc')
            ->get(['id', 'bankAccount', 'bankName', 'branchCode']);

        $transactions = $this->transactionListQuery($request)
            ->paginate(ListPage::perPage($request))
            ->withQueryString();

        return view('Pages.bankAccount.bankAccountTransaction', compact('bankAccounts', 'transactions'));
    }

    public function listTransactions(Request $request): JsonResponse
    {
        $transactions = $this->transactionListQuery($request)
            ->paginate(ListPage::perPage($request));

        return response()->json([
            'data' => collect($transactions->items())->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'entry_date' => (string) $entry->entry_date,
                    'bankName' => $entry->bankName,
                    'bankCode' => $entry->bankCode,
                    'description' => $entry->description,
                    'debit' => (float) $entry->debit,
                    'credit' => (float) $entry->credit,
                ];
            })->values(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge([
            'description' => $this->properCase((string) $request->input('description', '')),
        ]);

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'bankAccount' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'entryType' => ['required', 'in:debit,credit'],
        ]);

        $bank = BankAccount::where('bankAccount', $validated['bankAccount'])->firstOrFail();
        $amount = (float) $validated['amount'];
        $debit = $validated['entryType'] === 'debit' ? $amount : 0.0;
        $credit = $validated['entryType'] === 'credit' ? $amount : 0.0;
        $accountId = BankLedgerQuery::ensureGlAccount($bank);

        $id = DB::table('ledgers')->insertGetId([
            'entry_date' => $validated['entry_date'],
            'reference_no' => null,
            'market_voucher_id' => null,
            'account_id' => $accountId,
            'debit' => $debit,
            'credit' => $credit,
            'description' => $validated['description'],
            'category' => self::LEDGER_CATEGORY,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ledgers')->where('id', $id)->update([
            'reference_no' => 'BAT-'.$id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bank transaction saved successfully.',
            ]);
        }

        return redirect()->route('bank.account.transaction')->with('success', 'Bank transaction saved successfully.');
    }

    public function transactionData(int $id): JsonResponse
    {
        $managed = $this->findPageManagedEntry($id);
        if (! $managed) {
            $exists = DB::table('ledgers')->where('id', $id)->exists();

            return response()->json([
                'success' => false,
                'message' => $exists ? self::LOCKED_MESSAGE : 'Not found',
            ], $exists ? 403 : 404);
        }

        $row = DB::table('ledgers as l')
            ->join('accounts as a', 'a.id', '=', 'l.account_id')
            ->join('bank_accounts as b', function ($join) {
                $join->whereRaw("a.account_code = CONCAT('BANK:', b.`bankAccount`)");
            })
            ->where('l.id', $id)
            ->select(
                'l.id',
                'l.entry_date',
                'l.description',
                'l.debit',
                'l.credit',
                'b.bankAccount',
                'b.bankName'
            )
            ->first();

        if (! $row) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $amount = (float) $row->debit > 0 ? (float) $row->debit : (float) $row->credit;
        $entryType = (float) $row->debit > 0 ? 'debit' : 'credit';

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $row->id,
                'entry_date' => (string) $row->entry_date,
                'bankAccount' => $row->bankAccount,
                'bankLabel' => $row->bankName.' - '.$row->bankAccount,
                'description' => $row->description,
                'amount' => $amount,
                'entryType' => $entryType,
            ],
        ]);
    }

    public function updateTransaction(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->merge([
            'description' => $this->properCase((string) $request->input('description', '')),
        ]);

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'entryType' => ['required', 'in:debit,credit'],
        ]);

        $row = $this->findPageManagedEntry($id);
        if (! $row) {
            $exists = DB::table('ledgers')->where('id', $id)->exists();
            $message = $exists ? self::LOCKED_MESSAGE : 'Not found';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], $exists ? 403 : 404);
            }
            abort($exists ? 403 : 404, $message);
        }

        $amount = (float) $validated['amount'];
        $debit = $validated['entryType'] === 'debit' ? $amount : 0.0;
        $credit = $validated['entryType'] === 'credit' ? $amount : 0.0;

        DB::table('ledgers')->where('id', $id)->where('category', self::LEDGER_CATEGORY)->update([
            'entry_date' => $validated['entry_date'],
            'description' => $validated['description'],
            'debit' => $debit,
            'credit' => $credit,
            'updated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bank transaction updated successfully.',
            ]);
        }

        return redirect()->route('bank.account.transaction')->with('success', 'Bank transaction updated successfully.');
    }

    public function destroyTransaction(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $row = $this->findPageManagedEntry($id);
        if (! $row) {
            $exists = DB::table('ledgers')->where('id', $id)->exists();
            $message = $exists ? self::LOCKED_MESSAGE : 'Not found';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], $exists ? 403 : 404);
            }
            abort($exists ? 403 : 404, $message);
        }

        DB::table('ledgers')->where('id', $id)->where('category', self::LEDGER_CATEGORY)->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bank transaction deleted.',
            ]);
        }

        return redirect()->route('bank.account.transaction')->with('success', 'Bank transaction deleted.');
    }

    public function bankSummary(string $bankAccountCode): JsonResponse
    {
        $bank = BankAccount::where('bankAccount', $bankAccountCode)->first();
        if (! $bank) {
            return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
        }

        $today = now()->format('Y-m-d');

        $openingBalance = BankLedgerQuery::signedBalance($bank, $today);
        $currentBalance = BankLedgerQuery::signedBalance($bank);

        return response()->json([
            'success' => true,
            'balance' => round($currentBalance, 2),
            'bank' => [
                'code' => $bank->bankAccount,
                'name' => $bank->bankName,
                'branch' => $bank->branchCode,
                'label' => $bank->bankName.' — '.$bank->bankAccount,
            ],
            'opening_balance' => round($openingBalance, 2),
            'current_balance' => round($currentBalance, 2),
            'opening_as_of' => $today,
        ]);
    }

    public function viewBankLedgerPage(): View
    {
        $bankAccounts = BankAccount::orderBy('bankName', 'asc')
            ->get(['bankAccount', 'bankName', 'branchCode']);

        return view('Pages.bankAccount.viewBankLedger', compact('bankAccounts'));
    }

    public function getBankLedger(Request $request, string $bankAccountCode): JsonResponse
    {
        $bank = BankAccount::where('bankAccount', $bankAccountCode)->first();
        if (! $bank) {
            return response()->json(['message' => 'Bank not found', 'entries' => []], 404);
        }

        $query = $this->bankLedgersBaseQuery($bank);

        $from = $request->query('from');
        $to = $request->query('to');
        if ($from && $to) {
            $query->whereBetween('entry_date', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('entry_date', '>=', $from);
        } elseif ($to) {
            $query->whereDate('entry_date', '<=', $to);
        }

        $rows = $query->get(['id', 'entry_date', 'description', 'credit', 'debit', 'category']);

        $running = 0.0;
        $data = [];
        foreach ($rows as $r) {
            $running += (float) $r->debit - (float) $r->credit;
            $data[] = [
                'id' => $r->id,
                'date' => (string) $r->entry_date,
                'description' => $this->formatLedgerDescription($r->description, $r->category),
                'credit' => (float) $r->credit,
                'debit' => (float) $r->debit,
                'balance' => $running,
            ];
        }

        return response()->json(['entries' => $data]);
    }

    protected function formatLedgerDescription(?string $description, ?string $category): string
    {
        $desc = preg_replace('/\s+/u', ' ', trim((string) $description)) ?? '';
        if ($category !== null && $category !== '') {
            $desc = trim($desc.' ['.$category.']');
        }

        return $desc;
    }

    public function previewBankLedger(Request $request, string $bankAccountCode): View
    {
        $bank = BankAccount::where('bankAccount', $bankAccountCode)->first();
        if (! $bank) {
            abort(404, 'Bank not found');
        }

        $query = $this->bankLedgersBaseQuery($bank);

        $from = $request->query('from');
        $to = $request->query('to');

        if ($from && $to) {
            $query->whereBetween('entry_date', [$from, $to]);
            $periodText = "{$from} to {$to}";
        } elseif ($from) {
            $query->whereDate('entry_date', '>=', $from);
            $periodText = "From {$from}";
        } elseif ($to) {
            $query->whereDate('entry_date', '<=', $to);
            $periodText = "Until {$to}";
        } else {
            $periodText = 'All Time';
        }

        $rows = $query->get(['id', 'entry_date', 'description', 'credit', 'debit', 'category']);

        $running = 0.0;
        $entries = [];
        foreach ($rows as $r) {
            $running += (float) $r->debit - (float) $r->credit;
            $row = new stdClass;
            $row->entry_date = $r->entry_date;
            $row->description = $this->formatLedgerDescription($r->description, $r->category);
            $row->credit = (float) $r->credit;
            $row->debit = (float) $r->debit;
            $row->balance = $running;
            $entries[] = $row;
        }

        $balance = $running;

        $viewData = compact('bank', 'entries', 'balance', 'periodText');

        if ($request->boolean('download_pdf')) {
            return $this->downloadDomPdf(
                'Pages.bankAccount.bankLedgerPreviewPdf',
                $viewData,
                'bank-ledger-'.$bankAccountCode.'-'.now()->format('YmdHis').'.pdf',
                'a4',
                'landscape'
            );
        }

        return view('Pages.bankAccount.bankLedgerPreview', $viewData);
    }
}
