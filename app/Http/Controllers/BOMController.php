<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\BomLabor;
use App\Models\BomLabourWork;
use App\Models\BomMaterial;
use App\Models\BomOverhead;
use App\Models\newProduct;
use App\Models\ProductCostVersion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BOMController extends Controller
{
    public function index()
    {
        $products = newProduct::query()
            ->where('productType', 'Finished')
            ->orderBy('productName')
            ->get(['productCode', 'productName', 'companyName', 'costRate']);

        $activeCosts = ProductCostVersion::query()
            ->where('is_active', true)
            ->get()
            ->keyBy('productCode');

        $bomsByProduct = Bom::query()
            ->with(['items', 'labors'])
            ->whereIn('productCode', $products->pluck('productCode'))
            ->get()
            ->keyBy('productCode');

        $productsForJs = $products->map(function ($p) use ($activeCosts, $bomsByProduct) {
            $cost = $activeCosts[$p->productCode] ?? null;

            if (! $cost && isset($bomsByProduct[$p->productCode])) {
                $computed = $this->calculateCostsFromBomModel($bomsByProduct[$p->productCode]);
                if ($computed['unit_cost'] > 0) {
                    $cost = (object) $computed;
                }
            }

            return [
                'code' => $p->productCode,
                'name' => $p->productName,
                'company' => $p->companyName ?? '',
                'display' => trim($p->productName.' — '.($p->companyName ?: $p->productCode)),
                'hasCost' => $cost !== null,
                'unitCost' => $cost ? (float) $cost->unit_cost : null,
                'currency' => $cost->currency ?? 'PKR',
                'materialCost' => $cost ? (float) $cost->material_cost : null,
                'laborCost' => $cost ? (float) $cost->labor_cost : null,
                'overheadCost' => $cost ? (float) ($cost->overhead_cost ?? 0) : null,
                'wastageCost' => $cost ? (float) ($cost->wastage_cost ?? 0) : null,
                'batchCost' => $cost ? (float) ($cost->batch_cost ?? 0) : null,
                'effectiveFrom' => $cost && ! empty($cost->effective_from)
                    ? (is_string($cost->effective_from) ? $cost->effective_from : $cost->effective_from->format('Y-m-d'))
                    : null,
            ];
        })->values();

        return view('bom.index', compact('products', 'activeCosts', 'productsForJs'));
    }

    public function builder($productCode)
    {
        $product = newProduct::where('productCode', $productCode)->firstOrFail();
        $bom = Bom::where('productCode', $productCode)->with(['items.material', 'items.uom', 'labors', 'overheads'])->first();

        $materials = BomMaterial::query()
            ->orderBy('material_name')
            ->get(['id', 'material_code', 'material_name', 'packing', 'rate']);

        $labourWorks = BomLabourWork::query()
            ->orderBy('work_name')
            ->get(['id', 'work_name', 'rate']);

        $bomMaterialsByCode = $materials->keyBy('material_code');

        return view('bom.builder', compact('product', 'bom', 'materials', 'labourWorks', 'bomMaterialsByCode'));
    }

    public function costReport(string $productCode)
    {
        $product = newProduct::where('productCode', $productCode)->firstOrFail();
        $bom = Bom::where('productCode', $productCode)->with(['items.material', 'labors'])->first();

        $materialCodes = $bom?->items->pluck('materialCode')->filter()->unique() ?? collect();
        $bomMaterialsByCode = BomMaterial::query()
            ->when($materialCodes->isNotEmpty(), fn ($q) => $q->whereIn('material_code', $materialCodes))
            ->get()
            ->keyBy('material_code');

        $materialRows = [];
        $materialTotal = 0.0;

        foreach ($bom?->items ?? [] as $item) {
            $bomMat = $bomMaterialsByCode[$item->materialCode] ?? null;
            $rate = (float) ($item->unit_rate ?? $bomMat?->rate ?? 0);
            $perDozen = (float) ($item->per_dozen ?? 0);
            $useQty = (float) ($item->use_qty ?? 1);
            $lineTotal = $perDozen > 0 ? ($rate / $perDozen) * $useQty : 0.0;
            $materialTotal += $lineTotal;

            $materialRows[] = [
                'name' => $bomMat?->material_name ?? ($item->material?->productName ?? $item->materialCode),
                'code' => $item->materialCode,
                'packing' => $bomMat?->packing ?? '—',
                'rate' => $rate,
                'per_dozen' => $perDozen,
                'use_qty' => $useQty,
                'total' => $lineTotal,
            ];
        }

        $labourRows = [];
        $labourTotal = 0.0;

        foreach ($bom?->labors ?? [] as $labor) {
            $qty = (float) ($labor->time_minutes ?? 0);
            $rate = (float) ($labor->rate_per_minute ?? 0);
            $lineTotal = $qty * $rate;
            $labourTotal += $lineTotal;

            $labourRows[] = [
                'name' => $labor->operation_name,
                'qty' => $qty,
                'rate' => $rate,
                'total' => $lineTotal,
            ];
        }

        $costVersion = ProductCostVersion::query()
            ->where('productCode', $productCode)
            ->where('is_active', true)
            ->first();

        if (! $bom && $costVersion) {
            $materialTotal = (float) $costVersion->material_cost;
            $labourTotal = (float) $costVersion->labor_cost;
        } elseif ($costVersion && $materialTotal <= 0 && $labourTotal <= 0) {
            $materialTotal = (float) $costVersion->material_cost;
            $labourTotal = (float) $costVersion->labor_cost;
        }

        $batchUom = $bom?->batch_uom ?? 'dozen';
        $costPerLabel = $batchUom === 'gurs' ? 'Cost Per Gurs' : 'Cost Per Dozen';
        $grandTotal = $materialTotal + $labourTotal;
        $currency = $costVersion?->currency ?? 'PKR';
        $effectiveFrom = $costVersion?->effective_from?->format('d-M-Y');

        return view('bom.costReport', compact(
            'product',
            'bom',
            'materialRows',
            'labourRows',
            'materialTotal',
            'labourTotal',
            'grandTotal',
            'costPerLabel',
            'currency',
            'effectiveFrom',
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'productCode' => 'required|exists:product_info,productCode',
            'batch_size' => 'required|numeric|min:1',
            'batch_uom' => 'nullable|string|in:dozen,gurs,piece',
            'wastage_percent' => 'nullable|numeric|min:0',
            'items' => 'array',
            'labors' => 'array',
            'overheads' => 'array',
        ]);

        [$materialCost, $laborCost] = $this->calculateBomCosts($data);

        DB::transaction(function () use ($data, $materialCost, $laborCost) {
            $bom = Bom::updateOrCreate(['productCode' => $data['productCode']], [
                'batch_size' => $data['batch_size'],
                'batch_uom' => $data['batch_uom'] ?? 'dozen',
                'wastage_percent' => $data['wastage_percent'] ?? 0,
                'status' => 'Draft',
            ]);
            // reset children
            BomItem::where('bom_id', $bom->id)->delete();
            BomLabor::where('bom_id', $bom->id)->delete();
            BomOverhead::where('bom_id', $bom->id)->delete();
            foreach ($data['items'] ?? [] as $item) {
                $perDozen = (float) ($item['per_dozen'] ?? 0);
                $useQty = (float) ($item['use_qty'] ?? 1);
                BomItem::create([
                    'bom_id' => $bom->id,
                    'materialCode' => $item['materialCode'],
                    'uom_id' => $item['uom_id'] ?? null,
                    'per_dozen' => $perDozen,
                    'use_qty' => $useQty,
                    'qty_per_batch' => $item['qty_per_batch'] ?? $useQty,
                    'unit_rate' => $item['unit_rate'] ?? null,
                    'wastage_percent' => $item['wastage_percent'] ?? 0,
                    'note' => $item['note'] ?? null,
                ]);
            }
            foreach ($data['labors'] ?? [] as $labor) {
                BomLabor::create([
                    'bom_id' => $bom->id,
                    'operation_name' => $labor['operation_name'],
                    'time_minutes' => $labor['time_minutes'],
                    'rate_per_minute' => $labor['rate_per_minute'],
                ]);
            }
            foreach ($data['overheads'] ?? [] as $ov) {
                BomOverhead::create([
                    'bom_id' => $bom->id,
                    'overhead_name' => $ov['overhead_name'],
                    'type' => $ov['type'],
                    'allocation_base' => $ov['allocation_base'],
                    'value' => $ov['value'],
                ]);
            }

            $this->syncProductCostVersion($data['productCode'], $materialCost, $laborCost);
        });

        return response()->json([
            'status' => 'ok',
            'material_cost' => round($materialCost, 2),
            'labor_cost' => round($laborCost, 2),
            'unit_cost' => round($materialCost + $laborCost, 4),
        ]);
    }

    /**
     * @return array{0: float, 1: float} [materialCost, laborCost]
     */
    private function calculateBomCosts(array $data): array
    {
        $materialCost = 0.0;
        foreach ($data['items'] ?? [] as $item) {
            $perDozen = (float) ($item['per_dozen'] ?? 0);
            $useQty = (float) ($item['use_qty'] ?? 0);
            $rate = (float) ($item['unit_rate'] ?? 0);

            if ($perDozen > 0) {
                $materialCost += ($rate / $perDozen) * $useQty;
            }
        }

        $laborCost = 0.0;
        foreach ($data['labors'] ?? [] as $labor) {
            $laborCost += (float) ($labor['time_minutes'] ?? 0) * (float) ($labor['rate_per_minute'] ?? 0);
        }

        return [$materialCost, $laborCost];
    }

    private function calculateCostsFromBomModel(Bom $bom): array
    {
        $materialCost = 0.0;
        foreach ($bom->items as $item) {
            $perDozen = (float) ($item->per_dozen ?? 0);
            $useQty = (float) ($item->use_qty ?? 0);
            $rate = (float) ($item->unit_rate ?? 0);

            if ($perDozen > 0) {
                $materialCost += ($rate / $perDozen) * $useQty;
            }
        }

        $laborCost = 0.0;
        foreach ($bom->labors as $labor) {
            $laborCost += (float) ($labor->time_minutes ?? 0) * (float) ($labor->rate_per_minute ?? 0);
        }

        $batchCost = $materialCost + $laborCost;

        return [
            'material_cost' => $materialCost,
            'labor_cost' => $laborCost,
            'overhead_cost' => 0,
            'wastage_cost' => 0,
            'batch_cost' => $batchCost,
            'unit_cost' => $batchCost,
            'currency' => 'PKR',
            'effective_from' => optional($bom->updated_at)->format('Y-m-d'),
        ];
    }

    private function syncProductCostVersion(string $productCode, float $materialCost, float $laborCost): void
    {
        $overheadCost = 0.0;
        $wastageCost = 0.0;
        $batchCost = $materialCost + $laborCost + $overheadCost;
        $unitCost = $batchCost;

        ProductCostVersion::where('productCode', $productCode)->update(['is_active' => false]);

        ProductCostVersion::updateOrCreate(
            [
                'productCode' => $productCode,
                'effective_from' => Carbon::today(),
            ],
            [
                'is_active' => true,
                'material_cost' => round($materialCost, 4),
                'labor_cost' => round($laborCost, 4),
                'overhead_cost' => round($overheadCost, 4),
                'wastage_cost' => round($wastageCost, 4),
                'batch_cost' => round($batchCost, 4),
                'unit_cost' => round($unitCost, 6),
                'currency' => 'PKR',
            ]
        );
    }

    public function storeMaterial(Request $request)
    {
        $request->merge([
            'materialName' => ucwords(strtolower(trim((string) $request->input('materialName', '')))),
            'packing' => ucwords(strtolower(trim((string) $request->input('packing', '')))),
        ]);

        $data = $request->validate([
            'materialName' => 'required|string|max:100',
            'packing' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0',
        ]);

        $material = BomMaterial::create([
            'material_name' => $data['materialName'],
            'packing' => $data['packing'],
            'rate' => $data['rate'],
        ]);

        return response()->json([
            'status' => 'ok',
            'material' => [
                'id' => $material->id,
                'code' => $material->material_code,
                'name' => $material->material_name,
                'packing' => $material->packing,
                'rate' => (float) $material->rate,
            ],
        ]);
    }

    public function storeLabourWork(Request $request)
    {
        $request->merge([
            'workName' => ucwords(strtolower(trim((string) $request->input('workName', '')))),
        ]);

        $data = $request->validate([
            'workName' => 'required|string|max:100|unique:bom_labour_works,work_name',
            'rate' => 'required|numeric|min:0',
        ]);

        $work = BomLabourWork::create([
            'work_name' => $data['workName'],
            'rate' => $data['rate'],
        ]);

        return response()->json([
            'status' => 'ok',
            'work' => [
                'id' => $work->id,
                'name' => $work->work_name,
                'rate' => (float) $work->rate,
            ],
        ]);
    }

    public function updateMaterial(Request $request, int $id)
    {
        $material = BomMaterial::findOrFail($id);

        $data = $request->validate([
            'rate' => 'required|numeric|min:0',
            'apply_all' => 'nullable|boolean',
        ]);

        $material->update(['rate' => $data['rate']]);
        $applyAll = (bool) ($data['apply_all'] ?? false);
        $affectedProducts = $applyAll
            ? $this->applyMaterialRateToAllProductCosts($material, (float) $data['rate'])
            : 0;

        return response()->json([
            'status' => 'ok',
            'apply_all' => $applyAll,
            'affected_products' => $affectedProducts,
            'material' => [
                'id' => $material->id,
                'code' => $material->material_code,
                'name' => $material->material_name,
                'packing' => $material->packing,
                'rate' => (float) $material->rate,
            ],
        ]);
    }

    public function destroyMaterial(int $id)
    {
        BomMaterial::findOrFail($id)->delete();

        return response()->json(['status' => 'ok']);
    }

    public function updateLabourWork(Request $request, int $id)
    {
        $work = BomLabourWork::findOrFail($id);

        $data = $request->validate([
            'rate' => 'required|numeric|min:0',
            'apply_all' => 'nullable|boolean',
        ]);

        $work->update(['rate' => $data['rate']]);
        $applyAll = (bool) ($data['apply_all'] ?? false);
        $affectedProducts = $applyAll
            ? $this->applyLabourRateToAllProductCosts($work, (float) $data['rate'])
            : 0;

        return response()->json([
            'status' => 'ok',
            'apply_all' => $applyAll,
            'affected_products' => $affectedProducts,
            'work' => [
                'id' => $work->id,
                'name' => $work->work_name,
                'rate' => (float) $work->rate,
            ],
        ]);
    }

    private function applyMaterialRateToAllProductCosts(BomMaterial $material, float $rate): int
    {
        DB::transaction(function () use ($material, $rate) {
            BomItem::query()
                ->where('materialCode', $material->material_code)
                ->update(['unit_rate' => $rate]);
        });

        $affectedBoms = Bom::query()
            ->whereHas('items', function ($q) use ($material) {
                $q->where('materialCode', $material->material_code);
            })
            ->with(['items', 'labors'])
            ->get();

        foreach ($affectedBoms as $bom) {
            $costs = $this->calculateCostsFromBomModel($bom);
            $this->syncProductCostVersion($bom->productCode, (float) $costs['material_cost'], (float) $costs['labor_cost']);
        }

        return $affectedBoms->count();
    }

    private function applyLabourRateToAllProductCosts(BomLabourWork $work, float $rate): int
    {
        DB::transaction(function () use ($work, $rate) {
            BomLabor::query()
                ->where('operation_name', $work->work_name)
                ->update(['rate_per_minute' => $rate]);
        });

        $affectedBoms = Bom::query()
            ->whereHas('labors', function ($q) use ($work) {
                $q->where('operation_name', $work->work_name);
            })
            ->with(['items', 'labors'])
            ->get();

        foreach ($affectedBoms as $bom) {
            $costs = $this->calculateCostsFromBomModel($bom);
            $this->syncProductCostVersion($bom->productCode, (float) $costs['material_cost'], (float) $costs['labor_cost']);
        }

        return $affectedBoms->count();
    }

    public function destroyLabourWork(int $id)
    {
        BomLabourWork::findOrFail($id)->delete();

        return response()->json(['status' => 'ok']);
    }
}
