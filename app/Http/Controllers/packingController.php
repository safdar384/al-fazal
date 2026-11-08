<?php

namespace App\Http\Controllers;

use App\Models\packing;
use App\Support\ProductLookupUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PackingController extends Controller
{
    /**
     * Store a newly created packing (name + base unit + qty in one pack).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pakingName' => [
                'required',
                'string',
                'max:255',
            ],
            'baseUnit' => [
                'required',
                'string',
                'max:50',
            ],
            'qtyInPack' => [
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
        ], [
            'pakingName.required' => 'Packing name is required (e.g. Box, Bag, Pakit).',
            'baseUnit.required' => 'Base unit is required (e.g. Piece, Kg, Gurs).',
            'qtyInPack.required' => 'Quantity in one pack is required.',
            'qtyInPack.min' => 'Quantity in one pack must be at least 1.',
        ]);

        $name = strtoupper(trim($validated['pakingName']));
        $unit = strtoupper(trim($validated['baseUnit']));
        $qty = (int) $validated['qtyInPack'];

        $exists = packing::query()
            ->where('pakingName', $name)
            ->where('baseUnit', $unit)
            ->where('qtyInPack', $qty)
            ->exists();

        if ($exists) {
            $message = 'This packing already exists: '.$name.' · '.$qty.' '.$unit.'.';

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->withInput()->with('error', $message);
        }

        $packing = packing::create([
            'pakingName' => $name,
            'baseUnit' => $unit,
            'qtyInPack' => $qty,
        ]);

        $data = $packing->toArray();
        $data['label'] = $packing->displayLabel();

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data], 201);
        }

        return redirect()->route('product.create')->with('success', 'Packing added successfully!');
    }

    /**
     * Remove the specified packing (blocked when used by products).
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $packing = packing::findOrFail($id);

            if (ProductLookupUsage::isUsed('pakingName', $packing->pakingName)) {
                $message = "Cannot delete \"{$packing->pakingName}\" because it is used by one or more products.";

                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $name = $packing->displayLabel();
            $packing->delete();

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Packing \"{$name}\" deleted successfully!",
                    'data' => ['id' => (int) $id, 'name' => $packing->pakingName],
                ]);
            }

            return redirect()->back()->with('success', "Packing \"{$name}\" deleted successfully!");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete packing.'], 422);
            }

            return redirect()->back()->with('error', 'Failed to delete packing.');
        }
    }
}
