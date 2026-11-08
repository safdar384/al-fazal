<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CustomerCheque;
use App\Models\Labour;
use App\Models\NewCustomer;
use App\Support\BankLedgerQuery;
use App\Support\PartyLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerChequeController extends Controller
{
    public function index(): View
    {
        return view('pages.customerAccount.partyCheques', [
            'workers' => $this->workersList(),
            'bankAccounts' => BankAccount::query()
                ->orderBy('bankName')
                ->orderBy('bankAccount')
                ->get(['bankAccount', 'bankName', 'branchCode']),
            'parties' => NewCustomer::query()
                ->orderBy('custName')
                ->get(['custCode', 'custName', 'bussnessName', 'custArea', 'custCity', 'custCatagory']),
        ]);
    }

    public function inProcess(): View
    {
        return view('pages.customerAccount.chequesInProcess', [
            'workers' => $this->workersList(),
            'bankAccounts' => BankAccount::query()
                ->orderBy('bankName')
                ->orderBy('bankAccount')
                ->get(['bankAccount', 'bankName', 'branchCode']),
            'parties' => NewCustomer::query()
                ->orderBy('custName')
                ->get(['custCode', 'custName', 'bussnessName', 'custArea', 'custCity', 'custCatagory']),
        ]);
    }

    /**
     * Party cheques list.
     * scope=today   → due_date <= today (Pending + Cleared)
     * scope=month   → due_date in current month
     * scope=all     → all dates (Pending + Cleared)
     * scope=pending → Pending only (all dates)
     * Optional from_date / to_date filter on due_date.
     */
    public function list(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scope' => ['nullable', 'in:today,all,pending,month'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $scope = $validated['scope'] ?? 'today';
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 15);
        $today = now()->format('Y-m-d');
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $built = $this->buildChequeListContext($scope, $fromDate, $toDate, $today);
        $query = $built['query'];
        $stats = $built['stats'];

        $totalCount = (clone $query)->count();
        $totalAmount = (float) (clone $query)->sum('amount');
        $lastPage = max(1, (int) ceil($totalCount / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $rows = (clone $query)
            ->forPage($page, $perPage)
            ->get();

        $data = $this->mapChequeRows($rows, $today);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'count' => $totalCount,
                'total_amount' => round($totalAmount, 2),
                'pending_count' => $stats['pending_count'],
                'pending_amount' => $stats['pending_amount'],
                'cleared_count' => $stats['cleared_count'],
                'cleared_amount' => $stats['cleared_amount'],
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => $lastPage,
                'from' => $totalCount === 0 ? 0 : (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $totalCount),
                'scope' => $scope,
                'today' => $today,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ]);
    }

    /**
     * Print Party Cheque Detail (navy report design, current filters).
     */
    public function print(Request $request): View
    {
        $validated = $request->validate([
            'scope' => ['nullable', 'in:today,all,pending,month'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
        ]);

        $scope = $validated['scope'] ?? 'today';
        $today = now()->format('Y-m-d');
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $built = $this->buildChequeListContext($scope, $fromDate, $toDate, $today);
        $rows = $built['query']->limit(2000)->get();
        $entries = $this->mapChequeRows($rows, $today);
        $stats = $built['stats'];
        $totalAmount = round((float) collect($entries)->sum('amount'), 2);

        $scopeLabels = [
            'today' => 'Today Cheques',
            'month' => 'This Month Cheques',
            'pending' => 'Pending Cheques',
            'all' => 'All Cheques',
        ];

        return view('reports.party-cheques.print', [
            'entries' => collect($entries),
            'scope' => $scope,
            'scopeLabel' => $scopeLabels[$scope] ?? 'Party Cheques',
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'today' => $today,
            'totals' => [
                'count' => count($entries),
                'amount' => $totalAmount,
                'pending_count' => $stats['pending_count'],
                'pending_amount' => $stats['pending_amount'],
                'cleared_count' => $stats['cleared_count'],
                'cleared_amount' => $stats['cleared_amount'],
            ],
        ]);
    }

    /**
     * @return array{query: \Illuminate\Database\Eloquent\Builder, stats: array{pending_count: int, pending_amount: float, cleared_count: int, cleared_amount: float}}
     */
    protected function buildChequeListContext(string $scope, ?string $fromDate, ?string $toDate, string $today): array
    {
        $query = CustomerCheque::query()
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [CustomerCheque::STATUS_PENDING])
            ->orderBy('due_date')
            ->orderByDesc('id');

        if ($scope === 'pending') {
            $query->where('status', CustomerCheque::STATUS_PENDING);
        } else {
            $query->whereIn('status', [
                CustomerCheque::STATUS_PENDING,
                CustomerCheque::STATUS_CLEARED,
            ]);
        }

        if ($scope === 'today') {
            $query->whereDate('due_date', '<=', $today);
        } elseif ($scope === 'month') {
            $query->whereBetween('due_date', [
                now()->startOfMonth()->format('Y-m-d'),
                now()->endOfMonth()->format('Y-m-d'),
            ]);
        }

        if ($fromDate) {
            $query->whereDate('due_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('due_date', '<=', $toDate);
        }

        $statsBase = CustomerCheque::query();
        if ($scope === 'today') {
            $statsBase->whereDate('due_date', '<=', $today);
        } elseif ($scope === 'month') {
            $statsBase->whereBetween('due_date', [
                now()->startOfMonth()->format('Y-m-d'),
                now()->endOfMonth()->format('Y-m-d'),
            ]);
        }
        if ($fromDate) {
            $statsBase->whereDate('due_date', '>=', $fromDate);
        }
        if ($toDate) {
            $statsBase->whereDate('due_date', '<=', $toDate);
        }

        return [
            'query' => $query,
            'stats' => [
                'pending_count' => (clone $statsBase)->where('status', CustomerCheque::STATUS_PENDING)->count(),
                'pending_amount' => round((float) (clone $statsBase)->where('status', CustomerCheque::STATUS_PENDING)->sum('amount'), 2),
                'cleared_count' => (clone $statsBase)->where('status', CustomerCheque::STATUS_CLEARED)->count(),
                'cleared_amount' => round((float) (clone $statsBase)->where('status', CustomerCheque::STATUS_CLEARED)->sum('amount'), 2),
            ],
        ];
    }

    /**
     * Assign labour worker to selected cheque rows.
     */
    public function assignWorker(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id' => ['required', 'integer', 'exists:labours,id'],
            'cheque_ids' => ['required', 'array', 'min:1'],
            'cheque_ids.*' => ['integer', 'distinct'],
        ]);

        $worker = Labour::query()
            ->whereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) = ?', ['worker'])
            ->find($validated['worker_id']);
        if (! $worker) {
            return response()->json([
                'success' => false,
                'message' => 'Worker not found. Only labour with category Worker can be assigned.',
            ], 404);
        }

        $ids = array_values(array_unique(array_map('intval', $validated['cheque_ids'])));
        $updated = CustomerCheque::query()
            ->whereIn('id', $ids)
            ->update([
                'worker_id' => $worker->id,
                'worker_code' => $worker->labourCode,
                'worker_name' => $worker->labourName,
            ]);

        return response()->json([
            'success' => true,
            'message' => $updated.' cheque(s) assigned to '.$worker->labourName.'.',
            'updated' => $updated,
            'worker' => [
                'id' => $worker->id,
                'code' => $worker->labourCode,
                'name' => $worker->labourName,
            ],
        ]);
    }

    /**
     * Cheques assigned to workers (in process).
     */
    public function inProcessList(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'worker_id' => ['nullable', 'integer'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 15);
        $today = now()->format('Y-m-d');
        $q = trim((string) ($validated['q'] ?? ''));
        $workerId = isset($validated['worker_id']) ? (int) $validated['worker_id'] : null;
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $query = $this->buildInProcessQuery($q, $workerId, $fromDate, $toDate);

        $totalCount = (clone $query)->count();
        $totalAmount = (float) (clone $query)->sum('amount');
        $lastPage = max(1, (int) ceil($totalCount / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $rows = (clone $query)->forPage($page, $perPage)->get();
        $data = $this->mapChequeRows($rows, $today);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'count' => $totalCount,
                'total_amount' => round($totalAmount, 2),
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => $lastPage,
                'from' => $totalCount === 0 ? 0 : (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $totalCount),
                'q' => $q,
            ],
        ]);
    }

    /**
     * Print Cheque in Process report (navy design, current filters).
     */
    public function printInProcess(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'worker_id' => ['nullable', 'integer'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
        ]);

        $today = now()->format('Y-m-d');
        $q = trim((string) ($validated['q'] ?? ''));
        $workerId = isset($validated['worker_id']) ? (int) $validated['worker_id'] : null;
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $query = $this->buildInProcessQuery($q, $workerId, $fromDate, $toDate);
        $rows = $query->limit(2000)->get();
        $entries = $this->mapChequeRows($rows, $today);
        $totalAmount = round((float) collect($entries)->sum('amount'), 2);

        $workerLabel = 'All Workers';
        if ($workerId) {
            $worker = Labour::query()->find($workerId);
            if ($worker) {
                $workerLabel = trim($worker->labourName.' ('.$worker->labourCode.')');
            }
        }

        return view('reports.party-cheques.in-process-print', [
            'entries' => collect($entries),
            'q' => $q,
            'workerLabel' => $workerLabel,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totals' => [
                'count' => count($entries),
                'amount' => $totalAmount,
            ],
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\CustomerCheque>
     */
    protected function buildInProcessQuery(string $q, ?int $workerId, ?string $fromDate, ?string $toDate)
    {
        $query = CustomerCheque::query()
            ->whereNotNull('worker_id')
            ->where('worker_name', '!=', '')
            ->orderByDesc('due_date')
            ->orderByDesc('id');

        if ($workerId) {
            $query->where('worker_id', $workerId);
        }

        if ($fromDate) {
            $query->whereDate('due_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('due_date', '<=', $toDate);
        }

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($w) use ($like, $q) {
                $w->where('worker_name', 'like', $like)
                    ->orWhere('worker_code', 'like', $like)
                    ->orWhere('bank_name', 'like', $like)
                    ->orWhere('cheque_no', 'like', $like)
                    ->orWhere('customer_code', 'like', $like)
                    ->orWhere('customer_area', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('status', 'like', $like)
                    ->orWhere('payee_name', 'like', $like)
                    ->orWhere('payee_code', 'like', $like)
                    ->orWhere('pay_remark', 'like', $like);

                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $q)) {
                    $w->orWhereDate('due_date', $q)
                        ->orWhereDate('cheque_date', $q)
                        ->orWhereDate('original_due_date', $q);
                }

                $codes = NewCustomer::query()
                    ->where('custName', 'like', $like)
                    ->orWhere('bussnessName', 'like', $like)
                    ->limit(200)
                    ->pluck('custCode');
                if ($codes->isNotEmpty()) {
                    $w->orWhereIn('customer_code', $codes);
                }
            });
        }

        return $query;
    }

    /**
     * Pay / transfer an in-process cheque to Bank or Party.
     * payee_type=bank → bank ledger debit (deposit)
     * payee_type=party → party ledger debit (pay to customer/supplier)
     */
    public function pay(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'payee_type' => ['required', 'in:bank,party'],
            'payee_code' => ['required', 'string', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'remark' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ]);

        $cheque = CustomerCheque::query()->find($id);
        if (! $cheque) {
            return response()->json(['success' => false, 'message' => 'Cheque not found'], 404);
        }

        if ($cheque->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'This cheque is already paid to '.($cheque->payee_name ?: 'someone').'.',
            ], 422);
        }

        $amount = round((float) ($validated['amount'] ?? $cheque->amount), 2);
        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Invalid amount'], 422);
        }

        $payeeType = $validated['payee_type'];
        $payeeCode = trim($validated['payee_code']);
        $remark = trim((string) ($validated['remark'] ?? ''));
        $paidAt = $validated['paid_at'] ?? now()->format('Y-m-d');
        $chqLabel = 'Chq '.$cheque->cheque_no.' — '.$cheque->bank_name;

        try {
            $result = DB::transaction(function () use ($cheque, $payeeType, $payeeCode, $amount, $remark, $paidAt, $chqLabel) {
                $payeeName = '';

                if ($payeeType === CustomerCheque::PAYEE_BANK) {
                    $bank = BankAccount::query()->where('bankAccount', $payeeCode)->first();
                    if (! $bank) {
                        throw new \RuntimeException('Bank account not found.');
                    }
                    $payeeName = trim($bank->bankName.' — '.$bank->bankAccount);
                    $desc = 'Cheque deposit '.$chqLabel
                        .($remark !== '' ? ' | '.$remark : '')
                        .($cheque->worker_name ? ' | Worker: '.$cheque->worker_name : '');

                    DB::table('ledgers')->insert([
                        'entry_date' => $paidAt,
                        'reference_no' => 'CHQ-'.$cheque->id,
                        'market_voucher_id' => null,
                        'account_id' => BankLedgerQuery::ensureGlAccount($bank),
                        'debit' => $amount,
                        'credit' => 0,
                        'description' => $desc,
                        'category' => 'ChequeTransfer',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $party = NewCustomer::query()->where('custCode', $payeeCode)->first();
                    if (! $party) {
                        throw new \RuntimeException('Party / customer not found.');
                    }
                    $payeeName = trim($party->custName.($party->bussnessName ? ' | '.$party->bussnessName : ''));
                    $desc = 'Cheque pay '.$chqLabel
                        .($remark !== '' ? ' | '.$remark : '')
                        .($cheque->worker_name ? ' | Worker: '.$cheque->worker_name : '');

                    // Pay to party → debit (money / value out to them)
                    $ledger = PartyLedger::postManualEntry(
                        $party->custCode,
                        $paidAt,
                        $amount,
                        'debit',
                        'ChequeTransfer',
                        $desc
                    );
                    $ledger->update(['invoiceNumber' => 'CHQ-'.$cheque->id]);
                }

                $cheque->payee_type = $payeeType;
                $cheque->payee_code = $payeeCode;
                $cheque->payee_name = $payeeName;
                $cheque->pay_remark = $remark !== '' ? $remark : null;
                $cheque->paid_at = $paidAt;
                $cheque->status = CustomerCheque::STATUS_CLEARED;
                $cheque->cleared_at = $paidAt;
                if (! $cheque->original_due_date) {
                    $cheque->original_due_date = $cheque->due_date;
                }
                $cheque->save();

                return [
                    'payee_type' => $payeeType,
                    'payee_code' => $payeeCode,
                    'payee_name' => $payeeName,
                    'paid_at' => $paidAt,
                    'amount' => $amount,
                ];
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Pay failed',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cheque paid to '.$result['payee_name'].'.',
            'data' => $result,
        ]);
    }

    /**
     * Mark cheque cleared — status changes; row stays in Today/All lists.
     */
    public function clear(Request $request, int $id): JsonResponse
    {
        $cheque = CustomerCheque::find($id);
        if (! $cheque) {
            return response()->json(['success' => false, 'message' => 'Cheque not found'], 404);
        }

        if ($cheque->status === CustomerCheque::STATUS_CLEARED) {
            return response()->json(['success' => true, 'message' => 'Cheque already cleared.']);
        }

        if ($cheque->status !== CustomerCheque::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending cheques can be cleared (status: '.$cheque->status.').',
            ], 422);
        }

        $cheque->status = CustomerCheque::STATUS_CLEARED;
        $cheque->cleared_at = now()->format('Y-m-d');
        if (! $cheque->original_due_date) {
            $cheque->original_due_date = $cheque->due_date;
        }
        $cheque->save();

        return response()->json([
            'success' => true,
            'message' => 'Cheque status set to Cleared.',
            'id' => $cheque->id,
            'status' => $cheque->status,
            'cleared_at' => $cheque->cleared_at?->format('Y-m-d'),
            'days' => $cheque->delayDays(),
        ]);
    }

    /**
     * Bounce = shift due_date to next day (stay Pending).
     */
    public function bounce(int $id): JsonResponse
    {
        $cheque = CustomerCheque::find($id);
        if (! $cheque) {
            return response()->json(['success' => false, 'message' => 'Cheque not found'], 404);
        }

        if ($cheque->status !== CustomerCheque::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending cheques can be bounced.',
            ], 422);
        }

        if (! $cheque->original_due_date) {
            $cheque->original_due_date = $cheque->due_date;
        }

        $nextDay = now()->addDay()->startOfDay();
        $cheque->due_date = $nextDay->format('Y-m-d');
        $cheque->status = CustomerCheque::STATUS_PENDING;
        $cheque->save();

        return response()->json([
            'success' => true,
            'message' => 'Cheque shifted to next day ('.$nextDay->format('Y-m-d').').',
            'id' => $cheque->id,
            'due_date' => $nextDay->format('Y-m-d'),
            'original_due_date' => $cheque->original_due_date?->format('Y-m-d'),
            'days' => $cheque->delayDays(),
        ]);
    }

    /**
     * @return Collection<int, Labour>
     */
    protected function workersList(): Collection
    {
        return Labour::query()
            ->whereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) = ?', ['worker'])
            ->orderBy('labourName')
            ->get(['id', 'labourCode', 'labourName', 'labourCatagory']);
    }

    /**
     * @param  Collection<int, CustomerCheque>  $rows
     * @return list<array<string, mixed>>
     */
    protected function mapChequeRows(Collection $rows, string $today): array
    {
        $codes = $rows->pluck('customer_code')->unique()->filter()->values();
        $customers = $codes->isEmpty()
            ? collect()
            : NewCustomer::query()
                ->whereIn('custCode', $codes)
                ->get(['custCode', 'custName', 'bussnessName', 'custArea', 'custContect'])
                ->keyBy('custCode');

        return $rows->map(function (CustomerCheque $c) use ($customers, $today) {
            $cust = $customers->get($c->customer_code);
            $phone = $cust ? trim((string) ($cust->custContect ?? '')) : '';
            $party = (string) $c->customer_code;
            if ($cust) {
                $parts = array_filter([
                    trim((string) $cust->custName),
                    trim((string) ($cust->bussnessName ?? '')),
                    $phone !== '' ? $phone : null,
                ], static fn ($v) => $v !== null && $v !== '');
                $party = implode(' | ', $parts);
            }

            $due = $c->due_date?->format('Y-m-d');
            $originalDue = $c->original_due_date?->format('Y-m-d') ?: $due;
            $status = (string) $c->status;

            return [
                'id' => $c->id,
                'customer_code' => $c->customer_code,
                'party' => $party,
                'phone' => $phone,
                'area' => $c->customer_area ?: ($cust->custArea ?? '-'),
                'bank_name' => $c->bank_name,
                'cheque_no' => $c->cheque_no,
                'amount' => (float) $c->amount,
                'cheque_date' => $c->cheque_date?->format('Y-m-d'),
                'due_date' => $due,
                'original_due_date' => $originalDue,
                'cleared_at' => $c->cleared_at?->format('Y-m-d'),
                'days' => $c->delayDays(),
                'description' => $c->description,
                'status' => $status,
                'ledger_id' => $c->ledger_id,
                'worker_id' => $c->worker_id,
                'worker_code' => $c->worker_code,
                'worker_name' => $c->worker_name,
                'payee_type' => $c->payee_type,
                'payee_code' => $c->payee_code,
                'payee_name' => $c->payee_name,
                'pay_remark' => $c->pay_remark,
                'paid_at' => $c->paid_at?->format('Y-m-d'),
                'is_paid' => $c->isPaid(),
                'is_carry' => $status === CustomerCheque::STATUS_PENDING && $due && $due < $today,
            ];
        })->values()->all();
    }
}
