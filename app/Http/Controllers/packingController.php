<?php

namespace App\Http\Controllers;

use App\Models\packing;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PackingController extends Controller
{
    /**
     * Store a newly created packing
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pakingName' => 'required|unique:packing|max:255'
        ]);

        packing::create($validated);

        return redirect()->route('product.create')->with('success', 'Packing added successfully!');
    }

    /**
     * Remove the specified packing
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $packing = packing::findOrFail($id);
            $packing->delete();
            
            return redirect()->back()->with('success', 'Packing deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete packing.');
        }
    }
}

