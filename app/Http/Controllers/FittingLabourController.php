<?php

namespace App\Http\Controllers;

use App\Models\BomLabourWork;
use App\Models\FittingLabour;
use App\Models\FittingProductWork;
use App\Models\newProduct;
use App\Services\FittingLabourService;
use App\Support\FittingModuleCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FittingLabourController extends Controller
{
    public function __construct(
        protected FittingLabourService $fittingLabour
    ) {}

    public function index(): View
    {
        $catalog = FittingModuleCache::productsCatalog();
        $workRows = FittingProductWork::query()
            ->where('status', 'Active')
            ->orderBy('work_name')
            ->get(['id', 'product_code', 'product_name', 'work_name', 'rate']);

        $allWorkNames = $workRows->pluck('work_name')->unique()->sort()->values();
        if ($allWorkNames->isEmpty() && class_exists(BomLabourWork::class)) {
            $allWorkNames = BomLabourWork::query()->orderBy('work_name')->pluck('work_name')->unique()->values();
        }

        $productList = $catalog['products']->map(fn ($p) => [
            'productCode' => $p->productCode,
            'productName' => $p->productName,
            'companyName' => $p->companyName,
            'unitName' => \App\Support\FittingWorkStockService::normalizeUnitLabel($p->unitPerPackName ?? null),
            'saleRate' => (float) ($p->saleRate ?? 0),
            'costRate' => (float) ($p->costRate ?? 0),
            'displayRate' => (float) ($p->costRate ?: $p->saleRate ?: 0),
        ])->values();

        $workRates = $workRows->map(fn (FittingProductWork $w) => [
            'id' => $w->id,
            'productCode' => $w->product_code,
            'productName' => $w->product_name,
            'workName' => $w->work_name,
            'rate' => (float) $w->rate,
        ])->values();

        return view('pages.fittingLabour.fittingLabour', [
            'fitters' => FittingModuleCache::fitters(),
            'products' => $catalog['products'],
            'productVariants' => $catalog['productVariants'],
            'productListJson' => $productList,
            'allWorkNames' => $allWorkNames,
            'workRatesJson' => $workRates,
            'workNameSuggestions' => $allWorkNames,
            'workStocks' => FittingModuleCache::workStocks(),
        ]);
    }

    public function listWorkStocks(): JsonResponse
    {
        return response()->json([
            'data' => FittingModuleCache::workStocks()->values(),
        ]);
    }

    public function listWorks(Request $request): JsonResponse
    {
        $productCode = $request->query('productCode');

        $query = FittingProductWork::query()
            ->where('status', 'Active')
            ->orderBy('product_name')
            ->orderBy('work_name');

        if ($productCode) {
            $query->where('product_code', $productCode);
        }

        $rows = $query->get()->map(fn (FittingProductWork $w) => [
            'id' => $w->id,
            'productCode' => $w->product_code,
            'productName' => $w->product_name,
            'workName' => $w->work_name,
            'rate' => (float) $w->rate,
        ])->values();

        return response()->json(['data' => $rows]);
    }

    public function storeWork(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productCode' => 'required|string',
            'workName' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0',
            'id' => 'nullable|integer',
        ]);

        $product = newProduct::query()->where('productCode', $validated['productCode'])->first();
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 422);
        }

        $workName = ucwords(strtolower(trim($validated['workName'])));
        $rate = round((float) $validated['rate'], 2);

        try {
            if (! empty($validated['id'])) {
                $row = FittingProductWork::query()->findOrFail($validated['id']);
                $dup = FittingProductWork::query()
                    ->where('product_code', $validated['productCode'])
                    ->where('work_name', $workName)
                    ->where('id', '!=', $row->id)
                    ->exists();
                if ($dup) {
                    return response()->json(['success' => false, 'message' => 'This work already exists for the product'], 422);
                }
                $row->update([
                    'product_code' => $product->productCode,
                    'product_name' => $product->productName,
                    'work_name' => $workName,
                    'rate' => $rate,
                    'status' => 'Active',
                ]);
                $msg = 'Work updated successfully';
            } else {
                $row = FittingProductWork::query()->updateOrCreate(
                    [
                        'product_code' => $product->productCode,
                        'work_name' => $workName,
                    ],
                    [
                        'product_name' => $product->productName,
                        'rate' => $rate,
                        'status' => 'Active',
                    ]
                );
                $msg = 'Work saved successfully';
            }

            FittingModuleCache::flushCatalog();

            return response()->json(['success' => true, 'message' => $msg, 'data' => [
                'id' => $row->id,
                'productCode' => $row->product_code,
                'productName' => $row->product_name,
                'workName' => $row->work_name,
                'rate' => (float) $row->rate,
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyWork(int $id): JsonResponse
    {
        try {
            $row = FittingProductWork::query()->findOrFail($id);
            $row->delete();
            FittingModuleCache::flushCatalog();

            return response()->json(['success' => true, 'message' => 'Work deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function selectContext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'labourCode' => 'required|string',
        ]);

        $payload = FittingModuleCache::selectContext($validated['labourCode']);
        if (empty($payload['is_fitter'])) {
            return response()->json([
                'entries' => [],
                'products' => [],
                'message' => 'Selected labour is not a fitter',
            ], 422);
        }

        return response()->json($payload);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->persist($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $txn = FittingLabour::query()->find($id);
        if (! $txn) {
            return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
        }

        return $this->persist($request, $txn);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $txn = FittingLabour::query()->find($id);
            if (! $txn) {
                return response()->json(['success' => false, 'message' => 'Entry not found'], 404);
            }
            $this->fittingLabour->delete($txn);

            return response()->json(['success' => true, 'message' => 'Entry deleted successfully']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function persist(Request $request, ?FittingLabour $existing = null): JsonResponse
    {
        $validated = $request->validate([
            'labourCode' => 'required|string',
            'type' => 'required|string|in:Work,Cash',
            'entryType' => 'required_if:type,Cash|nullable|string|in:pay,receive',
            'productCode' => 'required_unless:type,Cash|nullable|string',
            'workName' => 'required_unless:type,Cash|nullable|string|max:100',
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

        $payload = [
            'labourCode' => $validated['labourCode'],
            'type' => $validated['type'],
            'entryType' => $validated['entryType'] ?? null,
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
                ? $this->fittingLabour->update($existing, $payload)
                : $this->fittingLabour->store($payload);

            return response()->json([
                'success' => true,
                'message' => $validated['type'].' transaction '.($existing ? 'updated' : 'saved'),
                'txnNo' => $txn->txn_no,
                'id' => $txn->id,
                'type' => $txn->txn_type,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
