<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\BomMaterial;
use App\Models\BomItem;

class BomMaterialUsageService
{
    /**
     * BOM material lines for a completed quantity.
     * Formula per batch: unit_cost = (rate / per_dozen) × bom_use_qty
     * Scaled by complete_qty / batch_size (output in BOM batch UOM, default dozen).
     */
    public function planForProduct(string $productCode, float $completeQty): array
    {
        $completeQty = max(0, $completeQty);
        $bom = Bom::query()
            ->where('productCode', trim($productCode))
            ->with('items')
            ->first();

        if (! $bom || $bom->items->isEmpty()) {
            return [
                'has_bom' => false,
                'product_code' => $productCode,
                'complete_qty' => $completeQty,
                'batch_size' => 1,
                'batch_uom' => 'dozen',
                'batch_uom_label' => 'Dozen',
                'bom_wastage_percent' => 0,
                'materials' => [],
                'total_material_cost' => 0,
                'total_waste_qty' => 0,
                'message' => 'No Product Cost (BOM) defined for this product.',
            ];
        }

        $batchSize = max((float) $bom->batch_size, 1);
        $batchUom = strtolower((string) ($bom->batch_uom ?? 'dozen'));
        $multiplier = $completeQty / $batchSize;

        $materialCodes = $bom->items->pluck('materialCode')->filter()->unique();
        $catalog = BomMaterial::query()
            ->whereIn('material_code', $materialCodes)
            ->get()
            ->keyBy('material_code');

        $lines = [];
        $totalCost = 0.0;
        $totalWasteQty = 0.0;
        $sort = 0;

        foreach ($bom->items as $item) {
            $line = $this->buildLine($item, $catalog, $multiplier, $sort++);
            if ($line) {
                $lines[] = $line;
                $totalCost += $line['line_cost'];
                $totalWasteQty += $line['waste_qty'];
            }
        }

        $globalWastage = (float) ($bom->wastage_percent ?? 0);
        if ($globalWastage > 0 && $totalCost > 0) {
            $globalExtra = $totalCost * ($globalWastage / 100);
            $totalCost += $globalExtra;
        }

        return [
            'has_bom' => true,
            'product_code' => $productCode,
            'complete_qty' => $completeQty,
            'batch_size' => $batchSize,
            'batch_uom' => $batchUom,
            'batch_uom_label' => $this->batchUomLabel($batchUom),
            'bom_wastage_percent' => $globalWastage,
            'materials' => $lines,
            'total_material_cost' => round($totalCost, 4),
            'total_waste_qty' => round($totalWasteQty, 3),
            'formula_hint' => '(Rate ÷ Per Dozen) × Use Qty × (Complete Qty ÷ Batch Size)',
            'message' => null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $submitted
     * @return array{materials: array, total_material_cost: float, total_waste_qty: float}
     */
    public function normalizeSubmittedMaterials(array $submitted, float $completeQty): array
    {
        $lines = [];
        $totalCost = 0.0;
        $totalWaste = 0.0;
        $sort = 0;

        foreach ($submitted as $row) {
            $rate = (float) ($row['rate'] ?? 0);
            $perDozen = max((float) ($row['per_dozen'] ?? 12), 0.000001);
            $bomUseQty = max((float) ($row['bom_use_qty'] ?? 0), 0.000001);
            $consumed = max(0, (float) ($row['consumed_qty'] ?? 0));
            $wasteQty = max(0, (float) ($row['waste_qty'] ?? 0));
            $wastagePct = max(0, (float) ($row['wastage_percent'] ?? 0));

            if ($consumed <= 0 && $completeQty > 0) {
                $consumed = $bomUseQty * $completeQty;
            }

            $unitCost = ($rate / $perDozen) * $bomUseQty;
            $outputMultiplier = $consumed / $bomUseQty;
            $baseCost = $unitCost * $outputMultiplier;
            $lineWastageCost = $baseCost * ($wastagePct / 100);
            $lineCost = round($baseCost + $lineWastageCost, 4);

            $lines[] = [
                'material_code' => trim((string) ($row['material_code'] ?? '')),
                'material_name' => trim((string) ($row['material_name'] ?? '')),
                'rate' => $rate,
                'per_dozen' => $perDozen,
                'bom_use_qty' => $bomUseQty,
                'wastage_percent' => $wastagePct,
                'consumed_qty' => round($consumed, 6),
                'waste_qty' => round($wasteQty, 6),
                'unit_cost' => round($unitCost, 6),
                'line_cost' => $lineCost,
                'sort_order' => $sort++,
            ];

            $totalCost += $lineCost;
            $totalWaste += $wasteQty;
        }

        return [
            'materials' => $lines,
            'total_material_cost' => round($totalCost, 4),
            'total_waste_qty' => round($totalWaste, 3),
        ];
    }

    private function buildLine(BomItem $item, $catalog, float $multiplier, int $sort): ?array
    {
        $code = trim((string) $item->materialCode);
        if ($code === '') {
            return null;
        }

        $bomMat = $catalog[$code] ?? null;
        $rate = (float) ($item->unit_rate ?? $bomMat?->rate ?? 0);
        $perDozen = (float) ($item->per_dozen ?? 0);
        if ($perDozen <= 0) {
            $perDozen = (float) ($item->qty_per_batch ?? 12);
        }
        if ($perDozen <= 0) {
            $perDozen = 12;
        }

        $bomUseQty = (float) ($item->use_qty ?? 1);
        $wastagePct = (float) ($item->wastage_percent ?? 0);

        $unitCost = ($rate / $perDozen) * $bomUseQty;
        $consumedQty = round($bomUseQty * $multiplier, 6);
        $baseCost = $unitCost * $multiplier;
        $lineWastageCost = $baseCost * ($wastagePct / 100);
        $lineCost = round($baseCost + $lineWastageCost, 4);
        $wasteQty = round($consumedQty * ($wastagePct / 100), 6);

        return [
            'material_code' => $code,
            'material_name' => $bomMat?->material_name ?? $code,
            'packing' => $bomMat?->packing ?? '',
            'rate' => $rate,
            'per_dozen' => $perDozen,
            'bom_use_qty' => $bomUseQty,
            'wastage_percent' => $wastagePct,
            'consumed_qty' => $consumedQty,
            'waste_qty' => $wasteQty,
            'unit_cost' => round($unitCost, 6),
            'line_cost' => $lineCost,
            'formula_text' => '('.number_format($rate, 2).' ÷ '.number_format($perDozen, 2).') × '.number_format($bomUseQty, 3),
            'sort_order' => $sort,
        ];
    }

    private function batchUomLabel(string $uom): string
    {
        return match ($uom) {
            'gurs' => 'Gurs',
            'piece' => 'Piece',
            default => 'Dozen',
        };
    }
}
