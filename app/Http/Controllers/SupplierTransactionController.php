<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CustomerProductRate;
use App\Models\NewCustomer;
use App\Models\SupplierTransaction;
use App\Services\SupplierTransactionService;
use App\Support\SupplierModuleCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierTransactionController extends Controller
{
    public function __construct(
        protected SupplierTransactionService $transactions
    ) {}

    public function index(): View
    {
        // Keep first paint light — full product catalog loads only for Net Rate modal.
        return view('Pages.customerAccount.supplierTransaction', [
            'customers' => SupplierModuleCache::suppliersForTransactionPage(),
            'bankAccounts' => BankAccount::query()
                ->orderBy('bankName')
                ->orderBy('bankAccount')
                ->get(['bankAccount', 'bankName', 'branchCode']),
        ]);
    }

    /**
     * Full product list + variants for Add Net Rate modal (file-cached).
     */
    public function catalog(): JsonResponse
    {
        return response()->json(SupplierModuleCache::productsCatalogJson());
    }

    /**
     * Fast supplier select: balance + entries + products in one response (file-cached).
     */
    public function selectContext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
            'type' => 'nullable|string|in:Sale,Purchase,Cash',
        ]);

        $type = $validated['type'] ?? 'Purchase';
        $party = NewCustomer::query()
            ->where('custCode', $validated['customerCode'])
            ->first();

        if (! $party || ! NewCustomer::isProductSupplierCategory($party->custCatagory ?? null)) {
            return response()->json([
                'entries' => [],
                'products' => [],
                'message' => 'Selected party is not a Product Supplier',
            ], 422);
        }

        $payload = SupplierModuleCache::selectContext($validated['customerCode'], $type);

        return response()->json($payload);
    }

    /**
     * Products with active rate for supplier.
     * Sale → Sale rates; Purchase/Cash → Purchase (net) rates.
     */
    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
            'type' => 'required|string|in:Sale,Purchase,Cash',
        ]);

        $customerCode = $validated['customerCode'];
        $type = $validated['type'];

        return response()->json(SupplierModuleCache::supplierProducts($customerCode, $type));
    }

    public function productRate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
            'productCode' => 'required|string',
            'type' => 'required|string|in:Sale,Purchase,Cash',
            'variant_id' => 'nullable|integer',
            'workName' => 'nullable|string|max:100',
        ]);

        $result = SupplierModuleCache::productRatePayload(
            $validated['customerCode'],
            $validated['productCode'],
            $validated['type'],
            isset($validated['variant_id']) ? (int) $validated['variant_id'] : null,
            $validated['workName'] ?? null
        );

        return response()->json($result['payload'], (int) ($result['status'] ?? 200));
    }

    /**
     * Rate + works + stock in one request (avoids double round-trip on product select).
     */
    public function productContext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
            'productCode' => 'required|string',
            'type' => 'required|string|in:Sale,Purchase,Cash',
            'variant_id' => 'nullable|integer',
            'workName' => 'nullable|string|max:100',
        ]);

        $result = SupplierModuleCache::productContextPayload(
            $validated['customerCode'],
            $validated['productCode'],
            $validated['type'],
            isset($validated['variant_id']) ? (int) $validated['variant_id'] : null,
            $validated['workName'] ?? null
        );

        return response()->json($result['payload'], (int) ($result['http'] ?? $result['status'] ?? 200));
    }

    /**
     * Works for a product that have supplier Sale/Purchase net rate set.
     * Includes current work stock for display beside Work label.
     */
    public function productWorks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productCode' => 'required|string',
            'customerCode' => 'nullable|string',
            'type' => 'nullable|string|in:Sale,Purchase,Cash',
        ]);

        $productCode = $validated['productCode'];
        $customerCode = trim((string) ($validated['customerCode'] ?? ''));
        $type = $validated['type'] ?? 'Purchase';

        if ($customerCode === '') {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => SupplierModuleCache::productWorksPayload($customerCode, $productCode, $type),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->persist($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $txn = SupplierTransaction::query()->find($id);
        if (! $txn) {
            return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        return $this->persist($request, $txn);
    }

    protected function persist(Request $request, ?SupplierTransaction $existing = null): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
            'type' => 'required|string|in:Sale,Purchase,Cash',
            'entryType' => 'required_if:type,Cash|nullable|string|in:pay,receive',
            'bankAccount' => 'required_if:type,Cash|nullable|string|max:100|exists:bank_accounts,bankAccount',
            'productCode' => 'required_unless:type,Cash|nullable|string',
            'workName' => 'nullable|string|max:100',
            'description' => 'required_if:type,Cash|nullable|string|max:255',
            'variant_id' => 'nullable|integer',
            'variant_name' => 'nullable|string',
            'variant_value' => 'nullable|string',
            'rate' => 'required_unless:type,Cash|nullable|numeric|min:0',
            'qty' => 'required_unless:type,Cash|nullable|numeric|min:0.01',
            'amount' => 'required|numeric|min:0.01',
            'transactionDate' => 'nullable|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        if ($validated['type'] !== 'Cash') {
            $rateType = $validated['type'] === 'Sale' ? 'Sale' : 'Purchase';
            $workName = ucwords(strtolower(trim((string) ($validated['workName'] ?? ''))));
            if ($workName === '') {
                $workName = null;
            }

            $rateQuery = CustomerProductRate::query()
                ->where('customerCode', $validated['customerCode'])
                ->where('productCode', $validated['productCode'])
                ->where('status', 'Active')
                ->where('is_active', true)
                ->forRateType($rateType);

            if ($workName !== null) {
                $rateQuery->where('work_name', $workName);
            } else {
                $rateQuery->where(function ($q) {
                    $q->whereNull('work_name')->orWhere('work_name', '');
                });
            }

            if (! $rateQuery->exists()) {
                $label = $rateType === 'Sale' ? 'sale rate' : 'purchase net rate';
                $msg = $workName !== null
                    ? "No {$label} for product + work ({$workName}). Add Net Rate with Work first."
                    : "No {$label} for this product (without work). Add Net Rate first.";

                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
        }

        $payload = [
            'supplierCode' => $validated['customerCode'],
            'type' => $validated['type'],
            'entryType' => $validated['entryType'] ?? null,
            'bankAccount' => $validated['bankAccount'] ?? null,
            'productCode' => $validated['productCode'] ?? null,
            'workName' => $validated['workName'] ?? null,
            'description' => $validated['description'] ?? null,
            'variant_id' => $validated['variant_id'] ?? null,
            'variant_name' => $validated['variant_name'] ?? null,
            'variant_value' => $validated['variant_value'] ?? null,
            'rate' => $validated['rate'] ?? null,
            'qty' => $validated['qty'] ?? null,
            'amount' => $validated['amount'],
            'transactionDate' => $validated['transactionDate'] ?? null,
            'remarks' => $validated['remarks'] ?? $validated['description'] ?? null,
        ];

        try {
            $txn = $existing
                ? $this->transactions->update($existing, $payload)
                : $this->transactions->store($payload);

            return response()->json([
                'success' => true,
                'message' => $validated['type'].' transaction '.($existing ? 'updated' : 'saved'),
                'txnNo' => $txn->txn_no,
                'id' => $txn->id,
                'type' => $txn->txn_type,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function entries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => 'required|string',
        ]);

        $code = $validated['customerCode'];

        return response()->json(SupplierModuleCache::transactionEntries($code));
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $txn = SupplierTransaction::query()->find($id);
            if (! $txn) {
                return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
            }

            $this->transactions->delete($txn);

            return response()->json(['success' => true, 'message' => 'Entry deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
