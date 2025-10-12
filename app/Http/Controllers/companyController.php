<?php

namespace App\Http\Controllers;

use App\Models\productCompany;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CompanyController extends Controller
{
    /**
     * Store a newly created company
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'companyName' => 'required|unique:product_company|max:255'
        ]);

        productCompany::create($validated);

        return redirect()->route('product.create')->with('success', 'Company added successfully!');
    }

    /**
     * Remove the specified company
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $company = productCompany::findOrFail($id);
            $company->delete();
            
            return redirect()->back()->with('success', 'Company deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete company.');
        }
    }
}

