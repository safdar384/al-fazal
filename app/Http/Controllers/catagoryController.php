<?php

namespace App\Http\Controllers;

use App\Models\Catagory;
use App\Support\ProductLookupUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatagoryController extends Controller
{
    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'catagoryName' => 'required|unique:product_catagory|max:255',
        ]);
        $category = Catagory::create($validated);
        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $category], 201);
        }

        return redirect()->route('product.create')->with('success', 'Category added successfully!');
    }

    /**
     * Remove the specified category (blocked when used by products).
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $category = Catagory::findOrFail($id);

            if (ProductLookupUsage::isUsed('catagoryName', $category->catagoryName)) {
                $message = "Cannot delete \"{$category->catagoryName}\" because it is used by one or more products.";

                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $name = $category->catagoryName;
            $category->delete();

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Category \"{$name}\" deleted successfully!",
                    'data' => ['id' => (int) $id, 'name' => $name],
                ]);
            }

            return redirect()->back()->with('success', "Category \"{$name}\" deleted successfully!");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete category.'], 422);
            }

            return redirect()->back()->with('error', 'Failed to delete category.');
        }
    }
}
