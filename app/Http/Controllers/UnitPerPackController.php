<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitPerPackRequest;
use App\Models\UnitPerPack;
use App\Support\ProductLookupUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitPerPackController extends Controller
{
    /**
     * Store a newly created unit per pack (supports AJAX from product form).
     */
    public function store(StoreUnitPerPackRequest $request)
    {
        try {
            $unit = UnitPerPack::create($request->validated());

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $unit], 201);
            }

            return redirect()->route('product.create')->with('success', 'Unit per pack added successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to add unit per pack.'], 422);
            }

            return redirect()->back()->withInput()->with('error', 'Failed to add unit per pack. Please try again.');
        }
    }

    /**
     * Remove the specified unit per pack (blocked when used by products).
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $unit = UnitPerPack::findOrFail($id);

            if (ProductLookupUsage::isUsed('unitPerPackName', $unit->unitName)) {
                $message = "Cannot delete \"{$unit->unitName}\" because it is used by one or more products.";

                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $name = $unit->unitName;
            $unit->delete();

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Unit per pack \"{$name}\" deleted successfully!",
                    'data' => ['id' => (int) $id, 'name' => $name],
                ]);
            }

            return redirect()->back()->with('success', "Unit per pack \"{$name}\" deleted successfully!");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete unit per pack.'], 422);
            }

            return redirect()->back()->with('error', 'Failed to delete unit per pack.');
        }
    }
}
