<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\CustomerArea;
use App\Models\CustomerCheque;
use App\Models\CustomerLedger;
use App\Models\NewCustomer;
use App\Support\PartyLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecoveryApiController extends Controller
{
    /** @var list<string> */
    protected array $modes = ['Recovery', 'Discount', 'Bilty', 'Other'];

    public function meta(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'modes' => $this->modes,
                'areas' => CustomerArea::query()->orderBy('areaCode')->get(['areaCode', 'areaName', 'ajentName']),
                'banks' => BankAccount::query()
                    ->orderBy('bankName')
                    ->get(['bankAccount', 'bankName', 'branchCode']),
            ],
        ]);
    }

    public function customersByArea(Request $request, string $areaCode): JsonResponse
    {
        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->where('custArea', $areaCode)
            ->orderBy('custName')
            ->get(['custCode', 'custName', 'bussnessName', 'custCity', 'custContect', 'custArea', 'custBalance']);

        $data = $customers->map(function (NewCustomer $c) {
            $signed = (float) (CustomerLedger::query()
                ->where('customerCode', $c->custCode)
                ->where('statuse', '0')
                ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as bal')
                ->value('bal') ?? 0);

            return [
                'code' => $c->custCode,
                'name' => $c->custName,
                'business' => $c->bussnessName ?? '',
                'city' => $c->custCity ?? '',
                'phone' => $c->custContect ?? '',
                'area' => $c->custArea ?? '',
                'balance' => $signed,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'area' => $areaCode,
            'data' => $data,
        ]);
    }

    /**
     * Add Recovery / Discount / Bilty / Other.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
            'description' => 'required|string|max:255',
            'paymentMode' => 'required|string|in:Recovery,Discount,Bilty,Other',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'nullable|string|in:credit,debit',
            'customerDate' => 'nullable|date',
            'bankAccount' => 'nullable|string|max:100',
        ]);

        $cust = NewCustomer::where('custCode', $validated['customerCode'])->first();
        if (! $cust) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $paymentMode = $validated['paymentMode'];
        $type = $validated['type'] ?? 'credit';
        $date = $validated['customerDate'] ?? now()->format('Y-m-d');
        $amount = (float) $validated['amount'];
        $bankAccount = trim((string) ($validated['bankAccount'] ?? ''));

        if ($bankAccount !== '') {
            $bank = BankAccount::where('bankAccount', $bankAccount)->first();
            if (! $bank) {
                return response()->json(['success' => false, 'message' => 'Bank not found'], 404);
            }
        }

        DB::beginTransaction();
        try {
            $entry = PartyLedger::postManualEntry(
                $validated['customerCode'],
                $date,
                $amount,
                $type,
                $paymentMode,
                $validated['description']
            );

            if ($bankAccount !== '') {
                $bank = BankAccount::where('bankAccount', $bankAccount)->first();
                DB::table('ledgers')->insert([
                    'entry_date' => $date,
                    'reference_no' => (string) $entry->id,
                    'market_voucher_id' => null,
                    'account_id' => \App\Support\BankLedgerQuery::ensureGlAccount($bank),
                    'debit' => $type === 'credit' ? $amount : 0,
                    'credit' => $type === 'debit' ? $amount : 0,
                    'description' => 'Customer '.$type.' '.$validated['customerCode'].' - '.$validated['description'],
                    'category' => $paymentMode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $paymentMode.' saved successfully.',
                'id' => $entry->id,
                'data' => [
                    'id' => $entry->id,
                    'customerCode' => $validated['customerCode'],
                    'paymentMode' => $paymentMode,
                    'amount' => $amount,
                    'type' => $type,
                    'date' => $date,
                    'description' => $validated['description'],
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Add cheque detail (ledger + party cheque).
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
                'data' => [
                    'ledger_id' => $entry->id,
                    'cheque_id' => $cheque->id,
                    'cheque_no' => $cheque->cheque_no,
                    'amount' => (float) $cheque->amount,
                    'due_date' => $cheque->due_date?->format('Y-m-d'),
                    'status' => $cheque->status,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
