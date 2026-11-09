<?php

namespace App\Http\Controllers;

use App\Models\ProductCompany;
use App\Support\ProductLookupUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'companyName' => 'required|unique:product_company|max:255',
        ]);

        $company = ProductCompany::create($validated);

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $company], 201);
        }

        return redirect()->route('product.create')->with('success', 'Company added successfully!');
    }

    /**
     * Remove the specified company (blocked when used by products).
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $company = ProductCompany::findOrFail($id);

            if (ProductLookupUsage::isUsed('companyName', $company->companyName)) {
                $message = "Cannot delete \"{$company->companyName}\" because it is used by one or more products.";

                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $name = $company->companyName;
            $company->delete();

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Company \"{$name}\" deleted successfully!",
                    'data' => ['id' => (int) $id, 'name' => $name],
                ]);
            }

            return redirect()->back()->with('success', "Company \"{$name}\" deleted successfully!");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete company.'], 422);
            }

            return redirect()->back()->with('error', 'Failed to delete company.');
        }
    }
}
