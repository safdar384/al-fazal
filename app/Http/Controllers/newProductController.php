<?php

namespace App\Http\Controllers;

use App\Models\newProduct;
use App\Models\Colour;
use App\Models\Catagory;
use App\Models\productCompany;
use App\Models\packing;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class newProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(): View
    {
        $products = newProduct::orderBy('productName', 'asc')->get();
        
        return view('pages.Setup.product', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $colours = Colour::orderBy('id', 'asc')->get();
        $catagorys = Catagory::orderBy('id', 'asc')->get();
        $proCompanys = productCompany::orderBy('id', 'asc')->get();
        $packings = packing::orderBy('id', 'asc')->get();
        
        return view('pages.Setup.newProduct', compact('colours', 'catagorys', 'proCompanys', 'packings'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        try {
            newProduct::create($request->validated());

            return redirect()
                ->route('product.index')
                ->with('success', 'Product created successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create product. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(int $id): View
    {
        $colours = Colour::orderBy('id', 'asc')->get();
        $catagorys = Catagory::orderBy('id', 'asc')->get();
        $proCompanys = productCompany::orderBy('id', 'asc')->get();
        $packings = packing::orderBy('id', 'asc')->get();
        $product = newProduct::findOrFail($id);
        
        return view('pages.Setup.editProduct', compact('product', 'colours', 'catagorys', 'proCompanys', 'packings'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        try {
            $product = newProduct::findOrFail($id);
            $product->update($request->validated());

            return redirect()
                ->route('product.index')
                ->with('success', 'Product updated successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $product = newProduct::findOrFail($id);
            $productName = $product->productName;
            $product->delete();

            return redirect()
                ->route('product.index')
                ->with('success', "Product '{$productName}' deleted successfully!");

        } catch (Exception $e) {
            return redirect()
                ->route('product.index')
                ->with('error', 'Failed to delete product. Please try again.');
        }
    }
}
