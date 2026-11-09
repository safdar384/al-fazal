<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\CustomerCheque;
use App\Models\CustomerLedger;
use App\Models\NewCustomer;
use App\Support\PartyBalance;
use App\Support\PartyLedger;
use App\Support\SupplierModuleCache;
use Illuminate\Support\Facades\DB;

class CustomerLedgerController extends Controller
{
    /**
     * Customer Account Transaction page (Single / Bank / Customer entry).
     */
    public function showAccountTransaction()
    {
        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->orderBy('custName', 'asc')
            ->get(['custCode', 'custName', 'bussnessName', 'custAddress', 'custArea', 'custCity']);

        $bankAccounts = BankAccount::orderBy('bankName', 'asc')
            ->get(['id', 'bankAccount', 'bankName', 'branchCode']);

        $initialAccountTransactions = $this->buildAccountTransactionList(null);

        return view('Pages.customerAccount.custLedgerTransaction', [
            'customers' => $customers,
            'bankAccounts' => $bankAccounts,
            'initialAccountTransactions' => $initialAccountTransactions,
        ]);
    }

    /**
     * Recent Account Transaction / Transfer rows for the Account Transaction page table.
     * Optional ?customerCode= filters to one customer.
     */
    public function listAccountTransactions(Request $request)
    {
        try {
            $customerCode = trim((string) $request->query('customerCode', ''));

            return response()->json([
                'success' => true,
                'data' => $this->buildAccountTransactionList($customerCode !== '' ? $customerCode : null),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load transactions.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'data' => [],
            ], 500);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildAccountTransactionList(?string $customerCode): array
    {
        $query = CustomerLedger::query()
            ->whereIn('category', $this->accountTransactionCategories())
            ->where(function ($q) {
                $q->where('statuse', '0')->orWhere('statuse', 0);
            })
            ->orderByDesc('id');

        if ($customerCode !== null && $customerCode !== '') {
            $query->where('customerCode', $customerCode);
        }

        $rows = $query->limit(300)->get([
            'id',
            'customerDate',
            'customerCode',
            'customerArea',
            'description',
            'credit',
            'debit',
            'category',
        ]);

        $codes = $rows->pluck('customerCode')->unique()->filter()->values();
        $names = $codes->isEmpty()
            ? collect()
            : NewCustomer::query()
                ->whereIn('custCode', $codes)
                ->get(['custCode', 'custName', 'bussnessName', 'custArea'])
                ->keyBy('custCode');

        return $rows->map(function (CustomerLedger $r) use ($names) {
            $cust = $names->get($r->customerCode);
            $label = (string) $r->customerCode;
            if ($cust) {
                $label = trim($cust->custName.($cust->bussnessName ? ' | '.$cust->bussnessName : ''));
            }

            $area = trim((string) ($r->customerArea ?: ($cust?->custArea ?? '')));

            return [
                'id' => $r->id,
                'date' => (string) $r->customerDate,
                'customerCode' => $r->customerCode,
                'customer' => $label,
                'areaCode' => $area !== '' ? $area : '-',
                'description' => $r->description,
                'credit' => (float) $r->credit,
                'debit' => (float) $r->debit,
                'category' => $r->category,
                'balance' => null,
            ];
        })->values()->all();
    }

    // Simple ledger viewer page
    public function viewCustomerLedgerPage()
    {
        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->orderBy('custName', 'asc')
            ->get(['custCode', 'custName', 'bussnessName', 'custArea', 'custCity']);

        return view('Pages.customerAccount.viewCustLedger', compact('customers'));
    }






    // Return customers in area, with optional date range for delivered calculation
    public function getCustomersByArea(Request $request, $areaCode)
    {
        $from = $request->query('from') ?? now()->format('Y-m-d');
        $to   = $request->query('to')   ?? now()->format('Y-m-d');
        $categoryParam = $request->query('category');

        // Normalize category filter to a set used in whereIn
        $categorySet = null;
        if ($categoryParam) {
            $key = strtolower(trim($categoryParam));
            if ($key === 'sale' || $key === 'saleinvoice' || $key === 'sale invoice') {
                $categorySet = ['SaleInvoice', 'Sale Invoice'];
            } elseif ($key === 'recovery') {
                $categorySet = ['Recovery'];
            } elseif ($key === 'purchase' || $key === 'purchase invoice') {
                $categorySet = ['Purchase', 'Purchase Invoice'];
            } else {
                // Use the provided category literal
                $categorySet = [$categoryParam];
            }
        }

        // fetch customers in area (Product Supplier only for Supplier modules)
        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->where('custArea', $areaCode)
            ->orderBy('custPage', 'asc')
            ->get();

        // Don't fetch market_vouchers data - let users enter fresh recovery data
        // $recoveries = \DB::table('market_vouchers')
        //     ->where('customerArea', $areaCode)
        //     ->whereBetween('voucher_date', [$from, $to])
        //     ->get()
        //     ->groupBy('customerCode');

        $result = [];
        foreach ($customers as $c) {
            $custCode = $c->custCode;

            // Opening balance before the from date (respect category filter if provided)
            $preQuery = CustomerLedger::where('customerCode', $custCode)
                ->where('statuse', '0')
                ->whereDate('customerDate', '<', $from);
            PartyLedger::excludeSupplierTransactions($preQuery);
            if ($categorySet) { $preQuery->whereIn('category', $categorySet); }
            $preBalance = $preQuery->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as bal')->value('bal');

            // Period net between from/to (inclusive), respecting category filter
            $periodQuery = CustomerLedger::where('customerCode', $custCode)
                ->where('statuse', '0')
                ->whereBetween('customerDate', [$from, $to]);
            PartyLedger::excludeSupplierTransactions($periodQuery);
            if ($categorySet) { $periodQuery->whereIn('category', $categorySet); }
            $balance = $periodQuery->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as bal')->value('bal');

            // delivered: sum of debit for sales in range; if category filter provided, use it
            $deliveredQuery = CustomerLedger::where('customerCode', $custCode)
                ->where('statuse', '0')
                ->whereBetween('customerDate', [$from, $to]);
            PartyLedger::excludeSupplierTransactions($deliveredQuery);
            if ($categorySet) {
                $deliveredQuery->whereIn('category', $categorySet);
            } else {
                $deliveredQuery->whereIn('category', ['SaleInvoice', 'Sale Invoice']);
            }
            $delivered = $deliveredQuery->sum('debit');

            // Don't include market_vouchers data - initialize all to 0
            $recovery = 0;
            $discount = 0;
            $misc = 0;
            $lastVoucherDate = now()->format('Y-m-d');

            // last_balance is same as balance (no recovery deducted yet)
            $lastBalance = $balance;

            // If category filter is applied, only include customers with activity in range
            if (!$categorySet || ($balance != 0 || $delivered != 0)) {
                $result[] = [
                    'custCode'    => $custCode,
                    'custName'    => $c->custName,
                    'city'        => $c->custCity ?? null,
                    'pre_balance' => (float) ($preBalance ?? 0),
                    'delivered'   => (float) ($delivered ?? 0),
                    'balance'     => (float) $balance,
                    'recovery'    => (float) $recovery,
                    'discount'    => (float) $discount,
                    'misc'        => (float) $misc,
                    'voucher_date'=> $lastVoucherDate,
                    'last_balance'=> (float) $lastBalance,
                ];
            }
        }

        $areaBalance = array_sum(array_column($result, 'balance'));

        return response()->json([
            'customers' => $result,
            'balance'   => $areaBalance,
        ]);
    }

    // Fetch ledger entries for a specific customer with running balance
    public function getCustomerLedger(Request $request, $customerCode)
    {
        $query = CustomerLedger::where('customerCode', $customerCode)
            ->where('statuse', '0')
            ->orderBy('customerDate', 'asc')
            ->orderBy('id', 'asc');
        PartyLedger::excludeSupplierTransactions($query);

        $from = $request->query('from');
        $to   = $request->query('to');
        if ($from && $to) {
            $query->whereBetween('customerDate', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('customerDate', '>=', $from);
        } elseif ($to) {
            $query->whereDate('customerDate', '<=', $to);
        }

        $rows = $query->get(['id','customerDate','description','credit','debit','category','invoiceNumber']);

        $isPayable = PartyBalance::isPayableParty(null, $customerCode);
        $running = 0.0;
        $data = [];
        foreach ($rows as $r) {
            $running += (float) $r->debit - (float) $r->credit;
            $displayRunning = $isPayable ? -$running : $running;
            $data[] = [
                'id' => $r->id,
                'date' => (string) $r->customerDate,
                'description' => $r->description,
                'credit' => (float) $r->credit,
                'debit' => (float) $r->debit,
                'category' => $r->category,
                'invoiceNumber' => $r->invoiceNumber,
                'balance' => (float) $displayRunning,
                'ledger_signed' => (float) $running,
            ];
        }

        // Customer Ledger UI balance also excludes Supplier Transaction postings
        $signedQuery = CustomerLedger::where('customerCode', $customerCode)->where('statuse', '0');
        PartyLedger::excludeSupplierTransactions($signedQuery);
        $signed = (float) ($signedQuery
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance') ?? 0);
        $meta = PartyBalance::meta($customerCode);
        $uiBalance = $isPayable ? (float) (-$signed) : (float) $signed;

        return response()->json([
            'entries' => $data,
            'balance' => $uiBalance,
            'balance_label' => $meta['balance_label'],
            'balance_type' => $meta['balance_type'],
        ]);
    }

    // Preview ledger in separate window for printing
    public function previewLedger(Request $request, $customerCode)
    {
        $customer = NewCustomer::where('custCode', $customerCode)->first();
        if (!$customer) {
            abort(404, 'Customer not found');
        }

        $query = CustomerLedger::where('customerCode', $customerCode)
            ->where('statuse', '0')
            ->orderBy('customerDate', 'asc')
            ->orderBy('id', 'asc');
        PartyLedger::excludeSupplierTransactions($query);

        $from = $request->query('from');
        $to   = $request->query('to');
        
        if ($from && $to) {
            $query->whereBetween('customerDate', [$from, $to]);
            $periodText = "$from to $to";
        } elseif ($from) {
            $query->whereDate('customerDate', '>=', $from);
            $periodText = "From $from";
        } elseif ($to) {
            $query->whereDate('customerDate', '<=', $to);
            $periodText = "Until $to";
        } else {
            $periodText = "All Time";
        }

        $rows = $query->get(['id','customerDate','description','credit','debit','category']);

        $isPayable = PartyBalance::isPayableParty($customer, $customerCode);
        $running = 0.0;
        $entries = [];
        foreach ($rows as $r) {
            $running += (float)$r->debit - (float)$r->credit;
            $entries[] = (object) [
                'id' => $r->id,
                'customerDate' => $r->customerDate,
                'description' => $r->description,
                'credit' => (float) $r->credit,
                'debit' => (float) $r->debit,
                'category' => $r->category,
                'balance' => $isPayable ? (float) (-$running) : (float) $running,
            ];
        }

        $balance = $isPayable ? (float) (-$running) : (float) $running;

        return view('Pages.customerAccount.ledgerPreview', compact('customer', 'entries', 'balance', 'periodText'));
    }

    // Fetch single ledger entry details
    public function getLedgerEntry($id)
    {
        $e = CustomerLedger::find($id);
        if (! $e) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Always resolve by ledger_id (not only when category === Cheque)
        $cheque = null;
        $c = CustomerCheque::where('ledger_id', $e->id)->first();
        if ($c) {
                $cheque = [
                    'id' => $c->id,
                    'bank_name' => $c->bank_name,
                    'cheque_no' => $c->cheque_no,
                    'cheque_date' => $c->cheque_date?->format('Y-m-d'),
                    'due_date' => $c->due_date?->format('Y-m-d'),
                    'original_due_date' => $c->original_due_date?->format('Y-m-d') ?: $c->due_date?->format('Y-m-d'),
                    'cleared_at' => $c->cleared_at?->format('Y-m-d'),
                    'days' => $c->delayDays(),
                    'amount' => (float) $c->amount,
                    'status' => $c->status,
                    'description' => $c->description,
                ];
        } elseif (strcasecmp((string) $e->category, 'Cheque') === 0) {
            // Fallback: parse "… (Chq 123 — Bank)" / "Cheque 123 — Bank" from description
            $cheque = $this->parseChequeFromDescription((string) $e->description);
        }

        return response()->json([
            'id' => $e->id,
            'customerCode' => $e->customerCode,
            'customerArea' => $e->customerArea,
            'customerDate' => (string) $e->customerDate,
            'description' => $e->description,
            'credit' => (float) $e->credit,
            'debit' => (float) $e->debit,
            'category' => $e->category,
            'invoiceNumber' => $e->invoiceNumber,
            'cheque' => $cheque,
        ]);
    }

    /**
     * @return array{bank_name: string, cheque_no: string, cheque_date: null, due_date: null, amount: null, status: null, description: null}|null
     */
    protected function parseChequeFromDescription(string $description): ?array
    {
        $bank = '';
        $no = '';
        if (preg_match('/\(Chq\s+(.+?)\s+[—\-]\s+(.+)\)$/iu', $description, $m)) {
            $no = trim($m[1]);
            $bank = trim($m[2]);
        } elseif (preg_match('/^Cheque\s+(.+?)\s+[—\-]\s+(.+)$/iu', $description, $m)) {
            $no = trim($m[1]);
            $bank = trim($m[2]);
        }

        if ($bank === '' || $no === '') {
            return null;
        }

        return [
            'bank_name' => $bank,
            'cheque_no' => $no,
            'cheque_date' => null,
            'due_date' => null,
            'amount' => null,
            'status' => null,
            'description' => null,
        ];
    }

    public const ACCOUNT_TXN_LOCKED_MESSAGE = 'Only entries created on the Account Transaction page can be edited or deleted.';

    /**
     * Categories managed by Customer Account Transaction page.
     *
     * @return list<string>
     */
    protected function accountTransactionCategories(): array
    {
        return ['AccountTransaction', 'Transfer'];
    }

    protected function isAccountTransactionManaged(?string $category): bool
    {
        return in_array((string) $category, $this->accountTransactionCategories(), true);
    }

    /**
     * Delete ledger entry.
     * - Sale/Purchase invoices: delete via invoice services (full cascade).
     * - Recovery / manual / transfer: delete ledger + linked bank ledger rows.
     * - When source=account_transaction: only AccountTransaction / Transfer.
     */
    public function deleteLedgerEntry(Request $request, $id)
    {
        $entry = CustomerLedger::find($id);
        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        $fromAccountTxn = $request->query('source') === 'account_transaction'
            || $request->input('source') === 'account_transaction';

        if ($fromAccountTxn && ! $this->isAccountTransactionManaged($entry->category)) {
            return response()->json([
                'success' => false,
                'message' => self::ACCOUNT_TXN_LOCKED_MESSAGE,
            ], 403);
        }

        $category = strtolower(trim((string) $entry->category));
        $invoiceNo = trim((string) ($entry->invoiceNumber ?? ''));

        \DB::beginTransaction();
        try {
            if (in_array($category, ['sale invoice', 'saleinvoice'], true) && $invoiceNo !== '') {
                $invoice = \App\Models\SaleInvoice::where('invoiceNo', $invoiceNo)->first();
                if ($invoice) {
                    app(\App\Services\SaleInvoiceService::class)->delete($invoice);
                } else {
                    PartyLedger::deleteEntry($entry);
                }
            } elseif (in_array($category, ['purchase', 'purchase invoice'], true) && $invoiceNo !== '') {
                $invoice = \App\Models\PurchaseInvoice::where('invoiceNo', $invoiceNo)->first();
                if ($invoice) {
                    app(\App\Services\PurchaseInvoiceService::class)->delete($invoice);
                } else {
                    PartyLedger::deleteEntry($entry);
                }
            } else {
                PartyLedger::deleteEntry($entry);
            }

            \DB::commit();

            return response()->json(['success' => true, 'message' => 'Entry deleted successfully']);
        } catch (\Throwable $e) {
            \DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Update ledger entry (supports Recovery family, AccountTransaction and Transfer)
    public function updateLedgerEntry(Request $request, $id)
    {
        $entry = CustomerLedger::find($id);
        if (!$entry) {
            return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        $fromAccountTxn = $request->input('source') === 'account_transaction';
        if ($fromAccountTxn && ! $this->isAccountTransactionManaged($entry->category)) {
            return response()->json([
                'success' => false,
                'message' => self::ACCOUNT_TXN_LOCKED_MESSAGE,
            ], 403);
        }

        $editableCategories = $fromAccountTxn
            ? $this->accountTransactionCategories()
            : ['AccountTransaction', 'Recovery', 'Discount', 'Bilty', 'Other', 'Cheque'];

        if (in_array($entry->category, $editableCategories, true) && $entry->category !== 'Transfer') {
            $validated = $request->validate([
                'description' => 'nullable|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'type' => 'nullable|string|in:credit,debit',
                'customerDate' => 'nullable|date',
                'paymentMode' => 'nullable|string|in:Recovery,Discount,Bilty,Other,Cheque',
                'bank_name' => 'nullable|string|max:150',
                'cheque_no' => 'nullable|string|max:100',
                'cheque_date' => 'nullable|date',
                'due_date' => 'nullable|date',
            ]);

            $isChequeUpdate = $entry->category === 'Cheque' || ($validated['paymentMode'] ?? '') === 'Cheque';
            if (! $isChequeUpdate && trim((string) ($validated['description'] ?? '')) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Description is required.',
                ], 422);
            }

            $date = $validated['customerDate'] ?? (string) $entry->customerDate;
            $defaultType = $entry->credit > 0 ? 'credit' : 'debit';
            $type = $validated['type'] ?? $defaultType;
            $credit = $type === 'credit' ? (float) $validated['amount'] : 0.0;
            $debit = $type === 'debit' ? (float) $validated['amount'] : 0.0;
            $description = trim((string) ($validated['description'] ?? ''));
            $newCategory = $entry->category;

            if ($entry->category !== 'Cheque'
                && ! empty($validated['paymentMode'])
                && in_array($validated['paymentMode'], ['Recovery', 'Discount', 'Bilty', 'Other'], true)
            ) {
                $newCategory = $validated['paymentMode'];
            }

            \DB::beginTransaction();
            try {
                if ($entry->category === 'Cheque' || ($validated['paymentMode'] ?? '') === 'Cheque') {
                    $cheque = CustomerCheque::where('ledger_id', $entry->id)->first();
                    $bankName = trim((string) ($validated['bank_name'] ?? $cheque?->bank_name ?? ''));
                    $chequeNo = trim((string) ($validated['cheque_no'] ?? $cheque?->cheque_no ?? ''));
                    $chequeDate = $validated['cheque_date'] ?? $cheque?->cheque_date?->format('Y-m-d');
                    $dueDate = $validated['due_date'] ?? $cheque?->due_date?->format('Y-m-d');

                    if ($bankName === '' || $chequeNo === '' || ! $chequeDate || ! $dueDate) {
                        \DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => 'Please fill all cheque details.',
                        ], 422);
                    }

                    $dupQuery = CustomerCheque::query()
                        ->where('customer_code', $entry->customerCode)
                        ->where('cheque_no', $chequeNo);
                    if ($cheque) {
                        $dupQuery->where('id', '!=', $cheque->id);
                    }
                    if ($dupQuery->exists()) {
                        \DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => 'This cheque number is already saved for this customer.',
                        ], 422);
                    }

                    $baseDesc = trim((string) ($validated['description'] ?? ''));
                    $description = $baseDesc !== ''
                        ? $baseDesc.' (Chq '.$chequeNo.' — '.$bankName.')'
                        : 'Cheque '.$chequeNo.' — '.$bankName;

                    $chequePayload = [
                        'customer_code' => $entry->customerCode,
                        'customer_area' => $entry->customerArea,
                        'bank_name' => $bankName,
                        'cheque_no' => $chequeNo,
                        'amount' => (float) $validated['amount'],
                        'cheque_date' => $chequeDate,
                        'due_date' => $dueDate,
                        'description' => $baseDesc !== '' ? $baseDesc : $description,
                        'status' => $cheque?->status ?: CustomerCheque::STATUS_PENDING,
                        'ledger_id' => $entry->id,
                    ];

                    if ($cheque) {
                        // Do not overwrite original_due_date on edit
                        $cheque->update($chequePayload);
                    } else {
                        $chequePayload['original_due_date'] = $dueDate;
                        CustomerCheque::create($chequePayload);
                    }

                    $newCategory = 'Cheque';
                }

                $entry->update([
                    'customerDate' => $date,
                    'description' => $description,
                    'credit' => $credit,
                    'debit' => $debit,
                    'category' => $newCategory,
                ]);

                // Sync corresponding bank ledger row (if posted with this entry id)
                $led = \DB::table('ledgers')->where('reference_no', (string) $entry->id)->first();
                if ($led) {
                    \DB::table('ledgers')->where('id', $led->id)->update([
                        'entry_date' => $date,
                        'credit' => $debit,
                        'debit' => $credit,
                        'description' => 'Customer '.$type.' '.$entry->customerCode.' - '.$description,
                        'updated_at' => now(),
                    ]);
                }

                PartyBalance::syncCustBalance($entry->customerCode);
                \DB::commit();

                return response()->json(['success' => true, 'message' => 'Entry updated successfully']);
            } catch (\Throwable $e) {
                \DB::rollBack();

                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        // Transfer update: update both sides (keep customers same)
        if ($entry->category === 'Transfer') {
            $validated = $request->validate([
                'description'  => 'required|string|max:255',
                'amount'       => 'required|numeric|min:0.01',
                'customerDate' => 'nullable|date',
            ]);

            $date = $validated['customerDate'] ?? (string) $entry->customerDate;
            $amount = (float)$validated['amount'];

            \DB::beginTransaction();
            try {
                // Update this side
                $isCredit = $entry->credit > 0;
                $suffix = $this->buildTransferSuffix($entry->description);
                // Remove any existing transfer suffixes the user might have typed
                $baseDesc = $this->removeTransferSuffix($validated['description']);
                $entry->update([
                    'customerDate' => $date,
                    'description'  => trim($baseDesc.($suffix ? ' '.$suffix : '')),
                    'credit'       => $isCredit ? $amount : 0,
                    'debit'        => $isCredit ? 0 : $amount,
                ]);

                // Update counterpart if found
                $counter = $this->findTransferCounterpart($entry);
                if ($counter) {
                    $counterIsCredit = $counter->credit > 0;
                    $counterSuffix = $this->buildTransferSuffix($counter->description);
                    $counterBase = $this->removeTransferSuffix($validated['description']);
                    $counter->update([
                        'customerDate' => $date,
                        'description'  => trim($counterBase.($counterSuffix ? ' '.$counterSuffix : '')),
                        'credit'       => $counterIsCredit ? $amount : 0,
                        'debit'        => $counterIsCredit ? 0 : $amount,
                    ]);
                }

                PartyBalance::syncCustBalance($entry->customerCode);
                if ($counter) {
                    PartyBalance::syncCustBalance($counter->customerCode);
                }
                \DB::commit();
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Unsupported category'], 400);
    }

    private function findTransferCounterpart(CustomerLedger $entry)
    {
        $desc = $entry->description ?? '';
        $otherCode = null;
        $role = null; // 'to' or 'from'
        if (preg_match('/Transfer to\s+(\S+)/i', $desc, $m)) { $otherCode = $m[1]; $role = 'to'; }
        elseif (preg_match('/Transfer from\s+(\S+)/i', $desc, $m)) { $otherCode = $m[1]; $role = 'from'; }

        if ($otherCode) {
            if ($role === 'to') {
                return CustomerLedger::where('category','Transfer')
                    ->where('customerCode', $otherCode)
                    ->whereDate('customerDate', $entry->customerDate)
                    ->where('credit', $entry->debit)
                    ->where('debit', 0)
                    ->where('description', 'like', '%Transfer from '.$entry->customerCode.'%')
                    ->orderBy('id','asc')->first();
            } else {
                return CustomerLedger::where('category','Transfer')
                    ->where('customerCode', $otherCode)
                    ->whereDate('customerDate', $entry->customerDate)
                    ->where('debit', $entry->credit)
                    ->where('credit', 0)
                    ->where('description', 'like', '%Transfer to '.$entry->customerCode.'%')
                    ->orderBy('id','asc')->first();
            }
        }

        // Fallback: try to find by matching amount/opposite
        if ($entry->credit > 0) {
            return CustomerLedger::where('category','Transfer')
                ->where('customerCode', '!=', $entry->customerCode)
                ->whereDate('customerDate', $entry->customerDate)
                ->where('debit', $entry->credit)
                ->where('credit', 0)
                ->orderBy('id','asc')->first();
        } else {
            return CustomerLedger::where('category','Transfer')
                ->where('customerCode', '!=', $entry->customerCode)
                ->whereDate('customerDate', $entry->customerDate)
                ->where('credit', $entry->debit)
                ->where('debit', 0)
                ->orderBy('id','asc')->first();
        }
    }

    private function buildTransferSuffix(?string $desc)
    {
        if (!$desc) return null;
        if (preg_match('/\((Transfer (?:to|from)\s+[^\)]+)\)/i', $desc, $m)) {
            return '('.$m[1].')';
        }
        return null;
    }

    private function removeTransferSuffix(string $text): string
    {
        // Remove all occurrences of (Transfer to XXX) or (Transfer from XXX)
        $clean = preg_replace('/\s*\((?:Transfer\s+(?:to|from)\s+[^\)]+)\)\s*/i', ' ', $text);
        return trim((string)$clean);
    }

    /**
     * Store a customer account entry.
     * entryCategory: single | bank | customer
     * Also used by Market Recovery with paymentMode Recovery/Discount/Bilty/Other.
     */
    public function storeAccountTransaction(Request $request)
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
            'description' => 'required|string|max:255',
            'paymentMode' => 'nullable|string|in:Recovery,Discount,Bilty,Other,None',
            'entryCategory' => 'nullable|string|in:single,bank,customer',
            'bankAccount' => 'nullable|string|max:100',
            'toCustomerCode' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|string|in:credit,debit',
            'customerDate' => 'nullable|date',
        ]);

        $cust = NewCustomer::where('custCode', $validated['customerCode'])->first();
        if (! $cust) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $paymentMode = $validated['paymentMode'] ?? 'None';
        $entryCategory = strtolower((string) ($validated['entryCategory'] ?? 'single'));
        if (in_array($paymentMode, ['Recovery', 'Discount', 'Bilty', 'Other'], true)) {
            $entryCategory = 'single';
        }

        $category = in_array($paymentMode, ['Recovery', 'Discount', 'Bilty', 'Other'], true)
            ? $paymentMode
            : 'AccountTransaction';

        $date = $validated['customerDate'] ?? now()->format('Y-m-d');
        $amount = (float) $validated['amount'];
        $type = $validated['type'];

        if ($entryCategory === 'bank') {
            if (empty($validated['bankAccount'])) {
                return response()->json(['success' => false, 'message' => 'Please select a bank.'], 422);
            }
            $bank = BankAccount::where('bankAccount', $validated['bankAccount'])->first();
            if (! $bank) {
                return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
            }
        }

        if ($entryCategory === 'customer') {
            $toCode = trim((string) ($validated['toCustomerCode'] ?? ''));
            if ($toCode === '') {
                return response()->json(['success' => false, 'message' => 'Please select the other customer.'], 422);
            }
            if ($toCode === $validated['customerCode']) {
                return response()->json(['success' => false, 'message' => 'Cannot transfer to the same customer.'], 422);
            }
            if (! NewCustomer::where('custCode', $toCode)->exists()) {
                return response()->json(['success' => false, 'message' => 'Other customer not found'], 404);
            }
        }

        DB::beginTransaction();
        try {
            if ($entryCategory === 'customer') {
                $toCode = trim((string) $validated['toCustomerCode']);
                // Credit on primary = transfer out; Debit on primary = transfer in
                if ($type === 'credit') {
                    PartyLedger::postTransfer(
                        $validated['customerCode'],
                        $toCode,
                        $date,
                        $amount,
                        $validated['description']
                    );
                } else {
                    PartyLedger::postTransfer(
                        $toCode,
                        $validated['customerCode'],
                        $date,
                        $amount,
                        $validated['description']
                    );
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Customer transfer saved successfully',
                ]);
            }

            $entry = PartyLedger::postManualEntry(
                $validated['customerCode'],
                $date,
                $amount,
                $type,
                $category,
                $validated['description']
            );

            if ($entryCategory === 'bank') {
                $bank = BankAccount::where('bankAccount', $validated['bankAccount'])->first();
                // Opposite sides: customer credit → bank debit (money in), customer debit → bank credit
                DB::table('ledgers')->insert([
                    'entry_date' => $date,
                    'reference_no' => (string) $entry->id,
                    'market_voucher_id' => null,
                    'account_id' => \App\Support\BankLedgerQuery::ensureGlAccount($bank),
                    'debit' => $type === 'credit' ? $amount : 0,
                    'credit' => $type === 'debit' ? $amount : 0,
                    'description' => 'Customer '.$type.' '.$validated['customerCode'].' - '.$validated['description'],
                    'category' => 'AccountTransaction',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'id' => $entry->id,
                'message' => 'Transaction saved successfully',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Get balance for a customer or supplier (receivable / payable)
    public function getBalance($customerCode)
    {
        if (PartyBalance::isSupplier(null, (string) $customerCode)) {
            return response()->json(SupplierModuleCache::partyMeta((string) $customerCode));
        }

        return response()->json(PartyBalance::meta($customerCode));
    }
}
