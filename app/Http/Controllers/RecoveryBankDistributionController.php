<?php

namespace App\Http\Controllers;

use App\Models\CustomerArea;
use App\Models\CustomerCheque;
use App\Models\CustomerLedger;
use App\Models\NewCustomer;
use App\Models\RecoveryVoucher;
use App\Support\PartyBalance;
use App\Support\PartyLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecoveryBankDistributionController extends Controller
{
    /**
     * Market Recovery page (customer recovery entry + area ledger).
     */
    public function index()
    {
        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->orderBy('custName', 'asc')
            ->get(['custCode', 'custName', 'bussnessName', 'custAddress', 'custArea', 'custCity']);

        $areas = CustomerArea::all();

        return view('Pages.customerAccount.recoveryBankDistribution', compact('customers', 'areas'));
    }

    /**
     * Get total ledger balance for an area (Recovery category, optional date).
     */
    public function getAreaBalance($areaCode)
    {
        $query = CustomerLedger::where('customerArea', $areaCode)
            ->whereIn('category', ['Recovery', 'Cheque'])
            ->where('statuse', '0');

        if (request()->has('date') && ! empty(request()->date)) {
            $query->whereDate('customerDate', request()->date);
        }

        $totalBalance = $query->sum(DB::raw('COALESCE(credit, 0) - COALESCE(debit, 0)'));

        return response()->json([
            'area_code' => $areaCode,
            'total_balance' => $totalBalance ?? 0,
        ]);
    }

    /**
     * Get ledger entries for an area (Recovery / Discount / Bilty / Other / Cheque).
     * Matches by ledger.customerArea OR parties whose custArea is this area.
     */
    public function getAreaLedgerEntries($areaCode)
    {
        $areaCode = trim(urldecode((string) $areaCode));
        $report = $this->buildAreaLedgerReport($areaCode);

        return response()->json([
            'entries' => $report['entries'],
            'total_credit' => $report['total_credit'],
            'total_debit' => $report['total_debit'],
            'available_balance' => $report['available_balance'],
            'totals' => $report['totals'],
        ]);
    }

    /**
     * Print pending Market Recovery area entries (same navy report design).
     */
    public function printAreaLedger(Request $request): View
    {
        $validated = $request->validate([
            'area' => ['required', 'string', 'max:50'],
        ]);

        $areaCode = trim((string) $validated['area']);
        $report = $this->buildAreaLedgerReport($areaCode);

        $area = CustomerArea::query()->where('areaCode', $areaCode)->first();

        return view('reports.market-recovery.print', [
            'areaCode' => $areaCode,
            'areaName' => $area->areaName ?? $areaCode,
            'agentName' => $area->ajentName ?? '',
            'entries' => $report['entries'],
            'totals' => $report['totals'],
            'totalCredit' => $report['total_credit'],
            'availableBalance' => $report['available_balance'],
        ]);
    }

    /**
     * @return array{
     *   entries: Collection<int, array<string, mixed>>,
     *   total_credit: float,
     *   total_debit: float,
     *   available_balance: float,
     *   totals: array{opening: float, recovery: float, cheque: float, discount: float, bilty: float, other: float, last: float}
     * }
     */
    protected function buildAreaLedgerReport(string $areaCode): array
    {
        $emptyTotals = [
            'opening' => 0.0,
            'recovery' => 0.0,
            'cheque' => 0.0,
            'discount' => 0.0,
            'bilty' => 0.0,
            'other' => 0.0,
            'last' => 0.0,
        ];

        if ($areaCode === '') {
            return [
                'entries' => collect(),
                'total_credit' => 0.0,
                'total_debit' => 0.0,
                'available_balance' => 0.0,
                'totals' => $emptyTotals,
            ];
        }

        $partyCodes = NewCustomer::query()
            ->where('custArea', $areaCode)
            ->pluck('custCode')
            ->filter()
            ->values()
            ->all();

        $categories = ['Recovery', 'Discount', 'Bilty', 'Other', 'Cheque'];

        $rawEntries = CustomerLedger::query()
            ->whereIn('category', $categories)
            ->where(function ($q) {
                $q->where('statuse', '0')->orWhere('statuse', 0)->orWhereNull('statuse');
            })
            ->where(function ($q) use ($areaCode, $partyCodes) {
                $q->where('customerArea', $areaCode);
                if (! empty($partyCodes)) {
                    $q->orWhereIn('customerCode', $partyCodes);
                }
            })
            ->orderBy('customerDate')
            ->orderBy('id')
            ->limit(400)
            ->get(['id', 'customerDate', 'customerCode', 'customerArea', 'description', 'credit', 'debit', 'accountType', 'category', 'statuse']);

        $customerCodes = $rawEntries->pluck('customerCode')->filter()->unique()->values()->all();
        $balanceMaps = $this->openingAndLastBalancesForCustomers($customerCodes);

        $customerNames = NewCustomer::query()
            ->whereIn('custCode', $customerCodes)
            ->get(['custCode', 'custName', 'bussnessName', 'custAddress', 'custCity', 'custPage'])
            ->keyBy(fn ($c) => (string) $c->custCode);

        $flat = $rawEntries->map(function ($entry) use ($balanceMaps) {
            $date = $entry->customerDate;
            if ($date instanceof \Carbon\CarbonInterface) {
                $date = $date->format('Y-m-d');
            } else {
                $date = substr((string) $date, 0, 10);
            }

            $code = (string) $entry->customerCode;
            $id = (int) $entry->id;
            $opening = (float) ($balanceMaps[$code]['opening'][$id] ?? 0);
            $last = (float) ($balanceMaps[$code]['last'][$id] ?? $opening);

            return [
                'id' => $id,
                'date' => $date,
                'code' => $code,
                'area' => $entry->customerArea,
                'description' => (string) ($entry->description ?? ''),
                'credit' => (float) $entry->credit,
                'debit' => (float) $entry->debit,
                'type' => $entry->accountType,
                'category' => (string) ($entry->category ?? ''),
                'statuse' => (string) ($entry->statuse ?? '0'),
                'opening_balance' => round($opening, 2),
                'last_balance' => round($last, 2),
            ];
        });

        // Same customer + same date → one display row (Recovery/Discount/Bilty/Other/Cheque columns).
        $grouped = [];
        foreach ($flat as $row) {
            $key = $row['code'].'|'.$row['date'];
            if (! isset($grouped[$key])) {
                $party = $customerNames->get($row['code']);
                $partyLabel = '';
                if ($party) {
                    $parts = array_filter([
                        trim((string) ($party->bussnessName ?: $party->custName)),
                        trim((string) ($party->custAddress ?? '')),
                        trim((string) ($party->custCity ?? '')),
                    ], static fn ($v) => $v !== '');
                    $partyLabel = implode(', ', $parts);
                }

                $grouped[$key] = [
                    'id' => $row['id'],
                    'ids' => [],
                    'date' => $row['date'],
                    'code' => $row['code'],
                    'page_no' => $party ? trim((string) ($party->custPage ?? '')) : '',
                    'area' => $row['area'],
                    'description' => $partyLabel !== '' ? $partyLabel : $row['description'],
                    'credit' => 0.0,
                    'debit' => 0.0,
                    'type' => $row['type'],
                    'category' => 'Grouped',
                    'statuse' => '0',
                    'approved' => false,
                    'opening_balance' => $row['opening_balance'],
                    'last_balance' => $row['last_balance'],
                    'recovery' => 0.0,
                    'cheque' => 0.0,
                    'discount' => 0.0,
                    'bilty' => 0.0,
                    'other' => 0.0,
                    '_first_id' => $row['id'],
                    '_last_id' => $row['id'],
                    '_desc_by_cat' => [],
                ];
            }

            $g = &$grouped[$key];
            $g['ids'][] = $row['id'];
            $g['credit'] += $row['credit'];
            $g['debit'] += $row['debit'];
            $g['_last_id'] = $row['id'];
            $g['last_balance'] = $row['last_balance'];
            if ($row['id'] < $g['_first_id']) {
                $g['_first_id'] = $row['id'];
                $g['opening_balance'] = $row['opening_balance'];
                $g['id'] = $row['id'];
            }

            $amount = (float) $row['credit'];
            $cat = $row['category'];
            if ($cat === 'Cheque') {
                $g['cheque'] += $amount;
            } elseif ($cat === 'Recovery') {
                $g['recovery'] += $amount;
            } elseif ($cat === 'Discount') {
                $g['discount'] += $amount;
            } elseif ($cat === 'Bilty') {
                $g['bilty'] += $amount;
            } else {
                $g['other'] += $amount;
            }

            if ($row['description'] !== '') {
                $g['_desc_by_cat'][$cat] = $row['description'];
            }
            unset($g);
        }

        $totals = $emptyTotals;
        $entries = collect(array_values($grouped))->map(function (array $g) use (&$totals) {
            $desc = (string) $g['description'];
            if ($desc === '') {
                foreach (['Recovery', 'Cheque', 'Discount', 'Bilty', 'Other'] as $cat) {
                    if (! empty($g['_desc_by_cat'][$cat])) {
                        $desc = (string) $g['_desc_by_cat'][$cat];
                        break;
                    }
                }
            }

            $ids = array_values(array_unique(array_map('intval', $g['ids'])));
            $primaryId = (int) ($ids[0] ?? $g['id']);

            $row = [
                'id' => $primaryId,
                'ids' => $ids,
                'date' => $g['date'],
                'code' => $g['code'],
                'page_no' => (string) ($g['page_no'] ?? ''),
                'area' => $g['area'],
                'description' => $desc,
                'credit' => round((float) $g['credit'], 2),
                'debit' => round((float) $g['debit'], 2),
                'type' => $g['type'],
                'category' => 'Grouped',
                'statuse' => '0',
                'approved' => false,
                'opening_balance' => round((float) $g['opening_balance'], 2),
                'last_balance' => round((float) $g['last_balance'], 2),
                'recovery' => round((float) $g['recovery'], 2),
                'cheque' => round((float) $g['cheque'], 2),
                'discount' => round((float) $g['discount'], 2),
                'bilty' => round((float) $g['bilty'], 2),
                'other' => round((float) $g['other'], 2),
            ];

            $totals['opening'] += $row['opening_balance'];
            $totals['last'] += $row['last_balance'];
            $totals['recovery'] += $row['recovery'];
            $totals['cheque'] += $row['cheque'];
            $totals['discount'] += $row['discount'];
            $totals['bilty'] += $row['bilty'];
            $totals['other'] += $row['other'];

            return $row;
        })->values();

        // Prefer Recovery/Cheque as primary edit id when present in flat map.
        $idToCategory = $flat->keyBy('id');
        $entries = $entries->map(function (array $row) use ($idToCategory) {
            $preferred = null;
            foreach (['Recovery', 'Cheque', 'Discount', 'Bilty', 'Other'] as $cat) {
                foreach ($row['ids'] as $id) {
                    $flatRow = $idToCategory->get($id);
                    if ($flatRow && ($flatRow['category'] ?? '') === $cat) {
                        $preferred = (int) $id;
                        break 2;
                    }
                }
            }
            if ($preferred) {
                $row['id'] = $preferred;
            }

            return $row;
        })->values();

        foreach ($totals as $key => $value) {
            $totals[$key] = round((float) $value, 2);
        }

        $totalCredit = (float) ($totals['recovery'] + $totals['cheque']);
        $totalDebit = (float) $entries->sum('debit');

        return [
            'entries' => $entries,
            'total_credit' => $totalCredit,
            'total_debit' => $totalDebit,
            'available_balance' => $totalCredit - $totalDebit,
            'totals' => $totals,
        ];
    }

    /**
     * Approve / unapprove Market Recovery ledger row(s) via customerledgers.statuse.
     * statuse "1" = approved (hidden from pending list); "0" = pending/active.
     */
    public function approveLedgerEntries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
            'approved' => ['required', 'boolean'],
        ]);

        $statuse = $validated['approved'] ? '1' : '0';
        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        $updated = CustomerLedger::query()
            ->whereIn('id', $ids)
            ->whereIn('category', ['Recovery', 'Discount', 'Bilty', 'Other', 'Cheque'])
            ->update(['statuse' => $statuse]);

        return response()->json([
            'success' => true,
            'message' => $validated['approved']
                ? 'Selected entries approved.'
                : 'Selected entries marked pending.',
            'updated' => $updated,
            'statuse' => $statuse,
        ]);
    }

    /**
     * Running receivable/payable display balances before/after each ledger id.
     *
     * @param  list<string>  $customerCodes
     * @return array<string, array{opening: array<int, float>, last: array<int, float>}>
     */
    protected function openingAndLastBalancesForCustomers(array $customerCodes): array
    {
        $maps = [];
        if ($customerCodes === []) {
            return $maps;
        }

        $parties = NewCustomer::query()
            ->whereIn('custCode', $customerCodes)
            ->get(['custCode', 'custCatagory'])
            ->keyBy('custCode');

        $ledgers = CustomerLedger::query()
            ->whereIn('customerCode', $customerCodes)
            ->where(function ($q) {
                $q->where('statuse', '0')->orWhere('statuse', 0)->orWhereNull('statuse');
            })
            ->orderBy('customerDate')
            ->orderBy('id')
            ->get(['id', 'customerCode', 'debit', 'credit']);

        $signedRunning = [];
        foreach ($customerCodes as $code) {
            $signedRunning[$code] = 0.0;
            $maps[$code] = ['opening' => [], 'last' => []];
        }

        foreach ($ledgers as $led) {
            $code = (string) $led->customerCode;
            if (! isset($maps[$code])) {
                continue;
            }

            $beforeSigned = (float) $signedRunning[$code];
            $afterSigned = $beforeSigned + (float) $led->debit - (float) $led->credit;
            $signedRunning[$code] = $afterSigned;

            $party = $parties->get($code);
            $payable = PartyBalance::isPayableParty($party);
            $maps[$code]['opening'][(int) $led->id] = $payable ? -$beforeSigned : $beforeSigned;
            $maps[$code]['last'][(int) $led->id] = $payable ? -$afterSigned : $afterSigned;
        }

        return $maps;
    }

    /**
     * Get list of recovery vouchers with filters.
     */
    public function getRecoveryList(Request $request)
    {
        $query = RecoveryVoucher::query();

        if ($request->has('area') && ! empty($request->area)) {
            $query->where('customerArea', $request->area);
        }

        if ($request->has('date') && ! empty($request->date)) {
            $query->whereDate('voucher_date', $request->date);
        }

        $query->where('recovery', '>', 0);

        $recoveries = $query->orderBy('voucher_date', 'desc')->get();

        return response()->json($recoveries);
    }

    /**
     * Delete a recovery voucher entry.
     */
    public function deleteRecovery($id)
    {
        try {
            $recovery = RecoveryVoucher::findOrFail($id);
            $recovery->delete();

            return response()->json([
                'message' => 'Recovery entry deleted successfully',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save cheque details + customer ledger credit entry.
     */
    public function storeCheque(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => ['required', 'string', 'max:50'],
            'customerArea' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['required', 'string', 'max:150'],
            'cheque_no' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'cheque_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:cheque_date'],
            'customerDate' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = NewCustomer::where('custCode', $validated['customerCode'])->first();
        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $exists = CustomerCheque::query()
            ->where('customer_code', $validated['customerCode'])
            ->where('cheque_no', $validated['cheque_no'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This cheque number is already saved for this customer.',
            ], 422);
        }

        $date = $validated['customerDate'] ?? now()->format('Y-m-d');
        $amount = (float) $validated['amount'];
        $baseDesc = trim((string) ($validated['description'] ?? ''));
        $ledgerDesc = $baseDesc !== ''
            ? $baseDesc.' (Chq '.$validated['cheque_no'].' — '.$validated['bank_name'].')'
            : 'Cheque '.$validated['cheque_no'].' — '.$validated['bank_name'];

        DB::beginTransaction();
        try {
            $entry = PartyLedger::postManualEntry(
                $validated['customerCode'],
                $date,
                $amount,
                'credit',
                'Cheque',
                $ledgerDesc
            );

            $cheque = CustomerCheque::create([
                'customer_code' => $validated['customerCode'],
                'customer_area' => $validated['customerArea'] ?: ($customer->custArea ?? null),
                'bank_name' => $validated['bank_name'],
                'cheque_no' => $validated['cheque_no'],
                'amount' => $amount,
                'cheque_date' => $validated['cheque_date'],
                'due_date' => $validated['due_date'],
                'original_due_date' => $validated['due_date'],
                'description' => $baseDesc !== '' ? $baseDesc : $ledgerDesc,
                'status' => CustomerCheque::STATUS_PENDING,
                'ledger_id' => $entry->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cheque and ledger entry saved successfully.',
                'id' => $entry->id,
                'cheque' => [
                    'id' => $cheque->id,
                    'cheque_no' => $cheque->cheque_no,
                    'amount' => (float) $cheque->amount,
                    'due_date' => $cheque->due_date?->format('Y-m-d'),
                    'status' => $cheque->status,
                    'ledger_id' => $cheque->ledger_id,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
