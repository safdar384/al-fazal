<?php

namespace App\Http\Controllers;

use App\Models\Catagory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CatagoryController extends Controller
{
    /**
     * Store a newly created category
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'catagoryName' => 'required|unique:product_catagory|max:255'
        ]);

        Catagory::create($validated);

        return redirect()->route('product.create')->with('success', 'Category added successfully!');
    }

    /**
     * Remove the specified category
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $category = Catagory::findOrFail($id);
            $category->delete();
            
            return redirect()->back()->with('success', 'Category deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete category.');
        }
    }
}

