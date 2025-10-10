<?php

namespace App\Http\Controllers;

use App\Models\productCompany;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    
    public function index()
    {
        $comapany = productCompany::all();
        return view('pages.Setup.product', compact('comapany'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'companyName' => 'required|unique:product_company|max:255' // or "Company" if plural
        ]);

        $company = ProductCompany::create($validated);
        

        return response()->json(['success' => true, 'company' => $company]); // Company
        


    }

    public function destroy($id)
    {
        productCompany::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Catagory deleted successfully.');
    }
}