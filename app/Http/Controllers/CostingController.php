<?php

namespace App\Http\Controllers;

use App\Models\BomMaterial;
use App\Models\newProduct;
use App\Models\ProductCostVersion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostingController extends Controller
{
    public function calculate(Request $request)
    {
        $data = $request->validate([
            'productCode' => 'required|exists:product_info,productCode',
            'batch_size' => 'required|numeric|min:0.001',
            'batch_uom' => 'nullable|string|in:piece,dozen,gurs',
            'wastage_percent' => 'nullable|numeric|min:0',
            'items' => 'array',
            'items.*.unit_rate' => 'nullable|numeric|min:0',
            'labors' => 'array',
            'overheads' => 'array',
        ]);
        $materialCost = 0;
        $laborCost = 0;
        $overheadCost = 0;
        $wastageCost = 0;
        // Material costing: assume material current rate from product table standardRate
        foreach ($data['items'] ?? [] as $item) {
            $perDozen = (float) ($item['per_dozen'] ?? 0);
            $useQty = (float) ($item['use_qty'] ?? 0);
            $rate = isset($item['unit_rate']) ? (float) $item['unit_rate'] : null;

            if ($rate === null) {
                $bomMat = BomMaterial::query()->where('material_code', $item['materialCode'] ?? '')->first();
                if ($bomMat) {
                    $rate = (float) $bomMat->rate;
                } else {
                    $prod = newProduct::where('productCode', $item['materialCode'] ?? '')->first();
                    $rate = $prod ? (float) ($prod->standardRate ?? $prod->costRate ?? 0) : 0;
                }
            }

            if ($perDozen > 0) {
                $baseLine = ($rate / $perDozen) * $useQty;
            } else {
                $qty = (float) ($item['qty_per_batch'] ?? 0);
                $baseLine = $qty * $rate;
            }

            if ($baseLine <= 0) {
                continue;
            }

            $itemWastagePct = $item['wastage_percent'] ?? 0;
            $lineWastage = $baseLine * ($itemWastagePct / 100);
            $materialCost += $baseLine + $lineWastage;
        }
        // Labor cost
        foreach ($data['labors'] ?? [] as $labor) {
            $laborCost += ($labor['time_minutes'] ?? 0) * ($labor['rate_per_minute'] ?? 0);
        }
        // Overheads allocation types simplified: per_batch adds value, per_unit divides by batch later handled, percent_material applies % of materialCost
        foreach ($data['overheads'] ?? [] as $ov) {
            $type = $ov['type'] ?? 'per_batch';
            $val = $ov['value'] ?? 0;
            if ($type === 'per_batch') {
                $overheadCost += $val;
            } elseif ($type === 'percent_material') {
                $overheadCost += ($materialCost * ($val / 100));
            } elseif ($type === 'per_minute') {
                $overheadCost += $val;
            } // treat as already aggregated
        }
        $globalWastagePct = $data['wastage_percent'] ?? 0;
        $preWastage = $materialCost + $laborCost + $overheadCost;
        $wastageCost = $preWastage * ($globalWastagePct / 100);
        $batchCost = $preWastage + $wastageCost;
        $batchUom = strtolower($data['batch_uom'] ?? 'piece');
        $outputUnits = (float) $data['batch_size'];
        if ($batchUom === 'dozen') {
            $outputUnits *= 12;
        } elseif ($batchUom === 'gurs') {
            $outputUnits *= 144;
        }
        $unitCost = $outputUnits > 0 ? $batchCost / $outputUnits : 0;

        return response()->json([
            'material_cost' => round($materialCost, 2),
            'labor_cost' => round($laborCost, 2),
            'overhead_cost' => round($overheadCost, 2),
            'wastage_cost' => round($wastageCost, 2),
            'batch_cost' => round($batchCost, 2),
            'unit_cost' => round($unitCost, 4),
        ]);
    }

    public function saveVersion(Request $request)
    {
        $data = $request->validate([
            'productCode' => 'required|exists:product_info,productCode',
            'material_cost' => 'required|numeric',
            'labor_cost' => 'required|numeric',
            'overhead_cost' => 'required|numeric',
            'wastage_cost' => 'required|numeric',
            'batch_cost' => 'required|numeric',
            'unit_cost' => 'required|numeric',
            'currency' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);
        DB::transaction(function () use ($data) {
            ProductCostVersion::where('productCode', $data['productCode'])->update(['is_active' => false]);
            ProductCostVersion::create(array_merge($data, [
                'effective_from' => Carbon::now(),
                'is_active' => true,
            ]));
        });

        return response()->json(['status' => 'saved']);
    }

    public function history($productCode)
    {
        $versions = ProductCostVersion::where('productCode', $productCode)->orderByDesc('effective_from')->get();

        return response()->json($versions);
    }
}
