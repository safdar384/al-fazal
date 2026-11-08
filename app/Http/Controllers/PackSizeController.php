<?php

namespace App\Http\Controllers;

use App\Models\newProduct;
use App\Models\PackSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackSizeController extends Controller
{
    public function index(): View
    {
        $packSizes = PackSize::query()
            ->orderBy('packing_type')
            ->orderBy('unit_name')
            ->orderBy('qty')
            ->get();

        return view('pages.Setup.packSize', [
            'packSizes' => $packSizes,
            'packings' => PackSize::PACKINGS,
            'units' => PackSize::UNITS,
            'suggestedUnits' => PackSize::SUGGESTED_UNITS,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'packing_type' => 'required|string|max:50|in:'.implode(',', PackSize::PACKINGS),
            'unit_name' => 'required|string|max:50|in:'.implode(',', PackSize::UNITS),
            'qty' => 'required|numeric|min:0.01|max:999999.99',
        ], [
            'packing_type.required' => 'Packing is required.',
            'packing_type.in' => 'Select a valid packing type.',
            'unit_name.required' => 'Unit is required.',
            'unit_name.in' => 'Select a valid unit.',
            'qty.required' => 'Quantity is required.',
            'qty.min' => 'Quantity must be greater than zero.',
        ]);

        $exists = PackSize::query()
            ->where('packing_type', $validated['packing_type'])
            ->where('unit_name', $validated['unit_name'])
            ->where('qty', round((float) $validated['qty'], 2))
            ->exists();

        if ($exists) {
            $message = 'This pack size already exists.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->withInput()->with('error', $message);
        }

        $pack = PackSize::create($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pack size saved.',
                'data' => [
                    'id' => $pack->id,
                    'packing_type' => $pack->packing_type,
                    'unit_name' => $pack->unit_name,
                    'qty' => (float) $pack->qty,
                    'label' => $pack->label,
                    'piecesPerUnit' => $pack->piecesPerUnitForProduct(),
                ],
            ], 201);
        }

        return redirect()->route('packSize.index')->with('success', 'Pack size "'.$pack->label.'" saved.');
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $pack = PackSize::findOrFail($id);

        $inUse = newProduct::query()
            ->whereRaw('LOWER(pakingName) = ?', [strtolower($pack->packing_type)])
            ->whereRaw('LOWER(unitPerPackName) = ?', [strtolower($pack->unit_name)])
            ->where('piecesPerUnit', $pack->piecesPerUnitForProduct())
            ->exists();

        if ($inUse) {
            $message = 'Cannot delete "'.$pack->label.'" — used by one or more products.';
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $label = $pack->label;
        $pack->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pack size "'.$label.'" deleted.',
            ]);
        }

        return redirect()->route('packSize.index')->with('success', 'Pack size "'.$label.'" deleted.');
    }
}
