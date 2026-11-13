<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DownloadsDomPdf;
use App\Models\BankAccount;
use App\Models\FittingLabour;
use App\Models\Labour;
use App\Models\LabourAccountTransaction;
use App\Support\BankLedgerQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class LabourAccountController extends Controller
{
    use DownloadsDomPdf;

    public const BANK_LEDGER_CATEGORY = 'LabourAccountTransaction';

    public function index(): View
    {
        // Fitter labours belong to Fitting Labour module — keep them out of Account Transaction.
        $labours = Labour::orderBy('labourName', 'asc')
            ->whereRaw('LOWER(COALESCE(labourCatagory, "")) NOT LIKE ?', ['%fitter%'])
            ->get(['id', 'labourCode', 'labourName', 'labourCatagory', 'commissionPercentage', 'salary', 'advance', 'balance']);

        $bankAccounts = BankAccount::orderBy('bankName', 'asc')
            ->get(['bankAccount', 'bankName', 'branchCode']);

        $transactions = LabourAccountTransaction::orderBy('id', 'desc')
            ->whereRaw('LOWER(COALESCE(labourCatagory, "")) NOT LIKE ?', ['%fitter%'])
            ->get();

        return view('Pages.labourAccount.labourAccountTransaction', compact('labours', 'bankAccounts', 'transactions'));
    }

    public function getLabourDetails(int $id): JsonResponse
    {
        $labour = Labour::find($id);
        if (! $labour) {
            return response()->json(['success' => false, 'message' => 'Labour not found'], 404);
        }

        return response()->json([
            'success' => true,
            'labour' => [
                'id' => $labour->id,
                'labourCode' => $labour->labourCode,
                'labourName' => $labour->labourName,
                'labourCatagory' => $labour->labourCatagory,
                'commissionPercentage' => (float) ($labour->commissionPercentage ?? 0),
                'advance' => (float) ($labour->advance ?? 0),
                'balance' => (float) ($labour->balance ?? 0),
                'ledgerBalance' => $this->labourLedgerRunningBalance($labour->id),
            ],
        ]);
    }

    /** Running balance from labour account transactions (debit − credit), same as Labour Ledger. */
    private function labourLedgerRunningBalance(int $labourId): float
    {
        $running = 0.0;
        $rows = LabourAccountTransaction::where('labour_id', $labourId)
            ->orderBy('transactionDate', 'asc')
            ->orderBy('id', 'asc')
            ->get(['entryType', 'amount']);

        foreach ($rows as $row) {
            $amount = (float) $row->amount;
            $running += $row->entryType === 'debit' ? $amount : -$amount;
        }

        return round($running, 2);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->merge([
            'bankAccount' => trim((string) $request->input('bankAccount', '')) ?: null,
        ]);

        $validated = $request->validate([
            'transactionDate' => ['required', 'date'],
            'labour_id' => ['required', 'exists:labours,id'],
            'description' => ['required', 'string', 'max:255'],
            'bankAccount' => ['nullable', 'string', 'max:100', 'exists:bank_accounts,bankAccount'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'entryType' => ['required', 'in:debit,credit'],
            'marketBalance' => ['nullable', 'numeric', 'min:0'],
            'recovery' => ['nullable', 'numeric', 'min:0'],
            'recoveryCommPercent' => ['nullable', 'numeric', 'min:0'],
            'commissionPercent' => ['nullable', 'numeric', 'min:0'],
            'isAdvance' => ['nullable', 'boolean'],
        ]);

        $labour = Labour::findOrFail($validated['labour_id']);
        if ($this->labourCategoryIsFitter($labour->labourCatagory)) {
            $message = 'Fitter labour is managed under Fitting Labour, not Account Transaction.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['labour_id' => [$message]],
                ], 422);
            }

            return redirect()->back()->withInput()->withErrors(['labour_id' => $message]);
        }

        $bank = ! empty($validated['bankAccount'])
            ? BankAccount::where('bankAccount', $validated['bankAccount'])->first()
            : null;
        $isAjent = $this->labourCategoryIsAjent($labour->labourCatagory);

        if (! $isAjent) {
            $validated['marketBalance'] = null;
            $validated['recovery'] = null;
            $validated['recoveryCommPercent'] = null;
            $validated['commissionPercent'] = null;
        } else {
            $validated['commissionPercent'] = $validated['commissionPercent'] ?? $labour->commissionPercentage ?? 0;
        }

        $validated['isAdvance'] = (bool) ($validated['isAdvance'] ?? false);
        $validated['labourCode'] = $labour->labourCode;
        $validated['labourName'] = $labour->labourName;
        $validated['labourCatagory'] = $labour->labourCatagory;

        DB::transaction(function () use ($validated, $labour) {
            $row = LabourAccountTransaction::create($validated);

            $this->applyLabourAdvanceEffect(
                $labour,
                (float) $validated['amount'],
                $validated['entryType'],
                $validated['isAdvance'],
                false
            );
            if ($validated['isAdvance']) {
                $labour->save();
            }

            // Double entry: labour debit → bank credit; labour credit → bank debit
            $this->syncBankLedgerForTransaction($row);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Labour account transaction saved successfully.',
                'ledgerBalance' => $this->labourLedgerRunningBalance($labour->id),
                'bankBalance' => $bank ? BankLedgerQuery::signedBalance($bank) : null,
            ]);
        }

        return redirect()->route('labour.account.index')->with('success', 'Labour account transaction saved successfully.');
    }

    public function listRecent(Request $request): JsonResponse
    {
        $query = LabourAccountTransaction::orderBy('id', 'desc')
            ->whereRaw('LOWER(COALESCE(labourCatagory, "")) NOT LIKE ?', ['%fitter%']);

        if ($request->filled('labour_id')) {
            $query->where('labour_id', (int) $request->input('labour_id'));
        }

        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $transactions->map(fn (LabourAccountTransaction $entry) => [
                'id' => $entry->id,
                'labour_id' => $entry->labour_id,
                'transactionDate' => $entry->transactionDate?->format('Y-m-d'),
                'labour' => trim(($entry->labourCode ?? '').' - '.($entry->labourName ?? ''), ' -'),
                'description' => $entry->description,
                'bankAccount' => $entry->bankAccount ?: '-',
                'entryType' => $entry->entryType,
                'amount' => (float) $entry->amount,
                'isAdvance' => (bool) $entry->isAdvance,
            ])->values(),
        ]);
    }

    public function transactionData(LabourAccountTransaction $transaction): JsonResponse
    {
        $labour = Labour::find($transaction->labour_id);
        $isAjent = $this->transactionIsAjentLabour($transaction, $labour);

        $commissionForForm = '';
        if ($isAjent) {
            $commissionForForm = $transaction->commissionPercent !== null
                ? (float) $transaction->commissionPercent
                : (float) ($labour?->commissionPercentage ?? 0);
        } elseif ($transaction->commissionPercent !== null) {
            $commissionForForm = (float) $transaction->commissionPercent;
        }

        return response()->json([
            'success' => true,
            'transaction' => [
                'id' => $transaction->id,
                'transactionDate' => $transaction->transactionDate->format('Y-m-d'),
                'labour_id' => $transaction->labour_id,
                'labourLabel' => $transaction->labourCode.' - '.$transaction->labourName,
                'labourCatagory' => $transaction->labourCatagory,
                'description' => $transaction->description,
                'bankAccount' => $transaction->bankAccount,
                'amount' => (float) $transaction->amount,
                'entryType' => $transaction->entryType,
                'marketBalance' => $transaction->marketBalance !== null ? (float) $transaction->marketBalance : '',
                'recovery' => $transaction->recovery !== null ? (float) $transaction->recovery : '',
                'commissionPercent' => $commissionForForm === '' ? '' : $commissionForForm,
                'recoveryCommPercent' => $transaction->recoveryCommPercent !== null ? (float) $transaction->recoveryCommPercent : '',
                'isAdvance' => (bool) $transaction->isAdvance,
                'isAjent' => $isAjent,
            ],
        ]);
    }

    public function updateTransaction(Request $request, LabourAccountTransaction $transaction): JsonResponse|RedirectResponse
    {
        $request->merge([
            'bankAccount' => trim((string) $request->input('bankAccount', '')) ?: null,
        ]);

        $validated = $request->validate([
            'transactionDate' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'bankAccount' => ['nullable', 'string', 'max:100', 'exists:bank_accounts,bankAccount'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'entryType' => ['required', 'in:debit,credit'],
            'marketBalance' => ['nullable', 'numeric', 'min:0'],
            'recovery' => ['nullable', 'numeric', 'min:0'],
            'recoveryCommPercent' => ['nullable', 'numeric', 'min:0'],
            'commissionPercent' => ['nullable', 'numeric', 'min:0'],
            'isAdvance' => ['nullable', 'boolean'],
        ]);

        $labour = Labour::findOrFail($transaction->labour_id);
        $bank = ! empty($validated['bankAccount'])
            ? BankAccount::where('bankAccount', $validated['bankAccount'])->first()
            : null;

        DB::transaction(function () use ($validated, $labour, $transaction) {
            $hadAdvance = (bool) $transaction->isAdvance;

            $this->applyLabourAdvanceEffect(
                $labour,
                (float) $transaction->amount,
                $transaction->entryType,
                $hadAdvance,
                true
            );

            $isAjent = $this->transactionIsAjentLabour($transaction, $labour);
            if (! $isAjent) {
                $validated['marketBalance'] = null;
                $validated['recovery'] = null;
                $validated['recoveryCommPercent'] = null;
                $validated['commissionPercent'] = null;
            } else {
                $validated['commissionPercent'] = $validated['commissionPercent'] ?? $labour->commissionPercentage ?? 0;
            }

            $validated['isAdvance'] = (bool) ($validated['isAdvance'] ?? false);
            $validated['labourCode'] = $labour->labourCode;
            $validated['labourName'] = $labour->labourName;
            $validated['labourCatagory'] = $labour->labourCatagory;

            $transaction->fill($validated);

            $this->applyLabourAdvanceEffect(
                $labour,
                (float) $validated['amount'],
                $validated['entryType'],
                $validated['isAdvance'],
                false
            );

            if ($hadAdvance || $validated['isAdvance']) {
                $labour->save();
            }
            $transaction->save();

            $this->syncBankLedgerForTransaction($transaction);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Labour account transaction updated successfully.',
                'ledgerBalance' => $this->labourLedgerRunningBalance($labour->id),
                'bankBalance' => $bank ? BankLedgerQuery::signedBalance($bank) : null,
            ]);
        }

        return redirect()->route('labour.account.index')->with('success', 'Labour account transaction updated successfully.');
    }

    public function destroyTransaction(Request $request, LabourAccountTransaction $transaction): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $labour = Labour::find($transaction->labour_id);
            if ($labour && $transaction->isAdvance) {
                $this->applyLabourAdvanceEffect(
                    $labour,
                    (float) $transaction->amount,
                    $transaction->entryType,
                    true,
                    true
                );
                $labour->save();
            }

            $this->deleteBankLedgerForTransaction($transaction);
            $transaction->delete();
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaction deleted.']);
        }

        return redirect()->route('labour.account.index')->with('success', 'Labour account transaction deleted.');
    }

    /**
     * Opposite bank side of labour entry:
     * labour debit → bank credit; labour credit → bank debit.
     */
    private function syncBankLedgerForTransaction(LabourAccountTransaction $txn): void
    {
        $bankCode = trim((string) ($txn->bankAccount ?? ''));
        if ($bankCode === '') {
            $this->deleteBankLedgerForTransaction($txn);

            return;
        }

        $bank = BankAccount::where('bankAccount', $bankCode)->first();
        if (! $bank) {
            throw new \RuntimeException('Bank account not found for labour transaction.');
        }

        $amount = (float) $txn->amount;
        $bankDebit = $txn->entryType === 'credit' ? $amount : 0.0;
        $bankCredit = $txn->entryType === 'debit' ? $amount : 0.0;
        $ref = 'LAT-'.$txn->id;
        $accountId = BankLedgerQuery::ensureGlAccount($bank);
        $description = trim(($txn->description ?: 'Labour Transaction').' — '.$txn->labourName);

        $payload = [
            'entry_date' => $txn->transactionDate?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'reference_no' => $ref,
            'market_voucher_id' => null,
            'account_id' => $accountId,
            'debit' => $bankDebit,
            'credit' => $bankCredit,
            'description' => $description,
            'category' => self::BANK_LEDGER_CATEGORY,
            'updated_at' => now(),
        ];

        $existingId = DB::table('ledgers')
            ->where('reference_no', $ref)
            ->where('category', self::BANK_LEDGER_CATEGORY)
            ->value('id');

        if ($existingId) {
            DB::table('ledgers')->where('id', $existingId)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('ledgers')->insert($payload);
        }
    }

    private function deleteBankLedgerForTransaction(LabourAccountTransaction $txn): void
    {
        DB::table('ledgers')
            ->where('reference_no', 'LAT-'.$txn->id)
            ->where('category', self::BANK_LEDGER_CATEGORY)
            ->delete();
    }

    /**
     * Agent labour category (supports common spellings saved in setup).
     */
    private function labourCategoryIsAjent(?string $category): bool
    {
        $c = strtolower(trim((string) $category));

        return $c === 'ajent' || $c === 'agent';
    }

    /** Fitter category (Fitting Labour module) — excluded from Account Transaction. */
    private function labourCategoryIsFitter(?string $category): bool
    {
        return str_contains(strtolower(trim((string) $category)), 'fitter');
    }

    /**
     * Whether this transaction row should use agent-only fields (edit form / rules).
     * Uses current labour master, snapshot on the row, or stored agent columns.
     */
    private function transactionIsAjentLabour(LabourAccountTransaction $transaction, ?Labour $labour): bool
    {
        if ($this->labourCategoryIsAjent($labour?->labourCatagory) || $this->labourCategoryIsAjent($transaction->labourCatagory)) {
            return true;
        }

        return $transaction->marketBalance !== null
            || $transaction->recovery !== null
            || $transaction->recoveryCommPercent !== null
            || $transaction->commissionPercent !== null;
    }

    /**
     * Apply or reverse advance effect on labour master (must save labour after).
     * Forward: as when posting a new transaction. Reverse: undo that posting.
     */
    private function applyLabourAdvanceEffect(
        Labour $labour,
        float $amount,
        string $entryType,
        bool $isAdvance,
        bool $reverse
    ): void {
        if (! $isAdvance) {
            return;
        }

        $advance = (float) ($labour->advance ?? 0);
        $balance = (float) ($labour->balance ?? 0);

        if (! $reverse) {
            if ($entryType === 'debit') {
                $labour->advance = $advance + $amount;
                $labour->balance = $balance - $amount;
            } else {
                $labour->advance = max(0, $advance - $amount);
                $labour->balance = $balance + $amount;
            }

            return;
        }

        if ($entryType === 'debit') {
            $labour->advance = max(0, $advance - $amount);
            $labour->balance = $balance + $amount;
        } else {
            $labour->advance = $advance + $amount;
            $labour->balance = $balance - $amount;
        }
    }

    public function viewLabourLedgerPage(): View
    {
        $labours = Labour::orderBy('labourName', 'asc')
            ->get(['id', 'labourCode', 'labourName', 'labourCatagory', 'balance']);

        return view('Pages.labourAccount.viewLabourLedger', compact('labours'));
    }

    public function getLabourLedger(Request $request, string $labourCode): JsonResponse
    {
        $labour = Labour::where('labourCode', $labourCode)->first();
        if (! $labour) {
            return response()->json(['message' => 'Labour not found', 'category' => 'worker', 'entries' => []], 404);
        }

        $from = $request->query('from');
        $to = $request->query('to');
        $built = $this->buildLabourLedgerEntries($labour, $from, $to);

        return response()->json([
            'category' => $built['category'],
            'labourCatagory' => $labour->labourCatagory,
            'opening_balance' => $built['opening'],
            'balance' => $built['balance'],
            'entries' => $built['entries'],
        ]);
    }

    public function previewLabourLedger(Request $request, string $labourCode): View|Response
    {
        $labour = Labour::where('labourCode', $labourCode)->first();
        if (! $labour) {
            abort(404, 'Labour not found');
        }

        $from = $request->query('from');
        $to = $request->query('to');

        if ($from && $to) {
            $periodText = "{$from} to {$to}";
        } elseif ($from) {
            $periodText = "From {$from}";
        } elseif ($to) {
            $periodText = "Until {$to}";
        } else {
            $periodText = 'All Time';
        }

        $built = $this->buildLabourLedgerEntries($labour, $from, $to);
        $entries = [];
        foreach ($built['entries'] as $e) {
            $row = new stdClass;
            foreach ($e as $key => $value) {
                $row->{$key} = $value;
            }
            $row->transactionDate = $e['date'] ?? null;
            $entries[] = $row;
        }
        $balance = $built['balance'];
        $category = $built['category'];

        $viewData = compact('labour', 'entries', 'balance', 'periodText', 'category');

        if ($request->boolean('download_pdf')) {
            return $this->downloadDomPdf(
                'Pages.labourAccount.labourLedgerPreviewPdf',
                $viewData,
                'labour-ledger-'.$labourCode.'-'.now()->format('YmdHis').'.pdf',
                'a4',
                'landscape'
            );
        }

        return view('Pages.labourAccount.labourLedgerPreview', $viewData);
    }

    /**
     * @return array{category: string, opening: float, balance: float, entries: list<array<string, mixed>>}
     */
    private function buildLabourLedgerEntries(Labour $labour, ?string $from, ?string $to): array
    {
        $category = $this->labourCategoryKind($labour->labourCatagory);

        $opening = 0.0;
        if ($from) {
            $prior = LabourAccountTransaction::where('labour_id', $labour->id)
                ->whereDate('transactionDate', '<', $from)
                ->get(['entryType', 'amount']);
            foreach ($prior as $p) {
                $opening += $p->entryType === 'debit' ? (float) $p->amount : -(float) $p->amount;
            }
            $opening = round($opening, 2);
        }

        $query = LabourAccountTransaction::where('labour_id', $labour->id)
            ->orderBy('transactionDate', 'asc')
            ->orderBy('id', 'asc');

        if ($from && $to) {
            $query->whereBetween('transactionDate', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('transactionDate', '>=', $from);
        } elseif ($to) {
            $query->whereDate('transactionDate', '<=', $to);
        }

        $rows = $query->get();
        $fittingByLedgerId = $category === 'fitter'
            ? $this->fittingMapForLedgerIds($labour->id, $rows->pluck('id'))
            : collect();

        $running = $opening;
        $data = [];

        if ($from) {
            $data[] = [
                'id' => null,
                'date' => $from,
                'description' => 'Opening Balance',
                'bankAccount' => '',
                'isAdvance' => false,
                'credit' => 0.0,
                'debit' => 0.0,
                'balance' => $opening,
                'isOpening' => true,
            ];
        }

        foreach ($rows as $r) {
            $debit = $r->entryType === 'debit' ? (float) $r->amount : 0.0;
            $credit = $r->entryType === 'credit' ? (float) $r->amount : 0.0;
            $running += $debit - $credit;

            $base = [
                'id' => $r->id,
                'date' => $r->transactionDate?->format('Y-m-d'),
                'description' => (string) $r->description,
                'bankAccount' => $r->bankAccount ?: '',
                'isAdvance' => (bool) $r->isAdvance,
                'credit' => $credit,
                'debit' => $debit,
                'balance' => round($running, 2),
                'isOpening' => false,
            ];

            if ($category === 'ajent') {
                $data[] = array_merge($base, [
                    'marketBalance' => $r->marketBalance !== null ? (float) $r->marketBalance : null,
                    'recovery' => $r->recovery !== null ? (float) $r->recovery : null,
                    'recoveryCommPercent' => $r->recoveryCommPercent !== null ? (float) $r->recoveryCommPercent : null,
                    'commissionPercent' => $r->commissionPercent !== null ? (float) $r->commissionPercent : null,
                ]);
            } elseif ($category === 'fitter') {
                $fit = $fittingByLedgerId->get($r->id);
                $item = $fit?->items?->first();
                $isCash = $fit && strcasecmp((string) $fit->txn_type, FittingLabour::TYPE_CASH) === 0;
                $productName = $item ? (string) ($item->product_name ?? '') : '';
                if ($isCash || ($item && strtoupper((string) ($item->product_code ?? '')) === 'CASH')) {
                    $productName = (string) ($item->product_name ?: $r->description);
                }

                $data[] = array_merge($base, [
                    'txnNo' => $fit?->txn_no ?: '',
                    'txnType' => $fit?->txn_type ?: '',
                    'productName' => $productName,
                    'workName' => $item && ! $isCash ? (string) ($item->work_name ?? '') : '',
                    'qty' => $item && ! $isCash ? (float) ($item->qty ?? 0) : null,
                    'rate' => $item && ! $isCash ? (float) ($item->rate ?? 0) : null,
                ]);
            } else {
                $data[] = $base;
            }
        }

        return [
            'category' => $category,
            'opening' => $opening,
            'balance' => round($running, 2),
            'entries' => $data,
        ];
    }

    /**
     * @param  Collection<int, int|string>  $ledgerIds
     * @return Collection<int|string, FittingLabour>
     */
    private function fittingMapForLedgerIds(int $labourId, Collection $ledgerIds): Collection
    {
        $ids = $ledgerIds->filter()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return FittingLabour::query()
            ->with('items')
            ->where('labour_id', $labourId)
            ->whereIn('labour_account_transaction_id', $ids)
            ->get()
            ->keyBy('labour_account_transaction_id');
    }

    /** Normalize labour category for ledger column sets. */
    private function labourCategoryKind(?string $category): string
    {
        $c = strtolower(trim((string) $category));
        if ($c === 'ajent' || $c === 'agent') {
            return 'ajent';
        }
        if (str_contains($c, 'fitter')) {
            return 'fitter';
        }

        return 'worker';
    }
}
