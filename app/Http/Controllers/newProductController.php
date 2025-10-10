<?php

namespace App\Http\Controllers;
use App\Models\newProduct;
use App\Models\Colour;
use App\Models\Catagory;
use App\Models\companyController;
use App\Models\productCompany;
use App\Models\packing;

use Illuminate\Http\Request;

class newProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = newProduct::all();
        return view('pages.Setup.product', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $colours = Colour::orderBy('id', 'asc')->get();
        $catagorys = Catagory::orderBy('id', 'asc')->get();
        $proCompanys = productCompany::orderBy('id', 'asc')->get();
        $packings = packing::orderBy('id', 'asc')->get();
        return view('pages.Setup.newProduct', compact('colours','catagorys', 'proCompanys', 'packings'));
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'productCode'     => 'required|string|max:50|unique:product_info,productCode',
            'productName'     => 'required|string|max:100',
            'companyName'     => 'required|string|max:150',
            'colourName'      => 'required|string|max:100',
            'pakingName'      => 'required|string|max:255',
            'saleRate'        => 'required|numeric|min:0|',
            'costRate'        => 'required|numeric|min:0|',
            'lessQty'         => 'required|numeric|min:0|',
            'stockQty'        => 'required|numeric',
            'catagoryName'    => 'required|string',
        ]);

        newProduct::create($request->all());

        return redirect()->route('product.index')->with('success', 'Product added successfully.');

}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    $colours = Colour::orderBy('id', 'asc')->get();
    $catagorys = Catagory::orderBy('id', 'asc')->get();
    $proCompanys = productCompany::orderBy('id', 'asc')->get();
    $packings = packing::orderBy('id', 'asc')->get();
    
    $product = newProduct::findOrFail($id);
    
    return view('pages.Setup.editProduct', compact('product','colours','catagorys','proCompanys','packings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $product = newProduct::findOrFail($id);
        $product->update($request->all());
         return redirect()->route('product.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = newProduct::findOrFail($id);
        $product->delete();

        return redirect()->route('product.index')->with('success', 'Product deleted successfully.');
    }
}
