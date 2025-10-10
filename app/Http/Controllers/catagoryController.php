<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Catagory;


class CatagoryController extends Controller
{
    
    public function index()
    {
        $cataGory = Catagory::all();
        return view('pages.Setup.product', compact('cataGory'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'catagoryName' => 'required|unique:product_catagory|max:255' // or "catagory" if plural
        ]);

        $catagory = Catagory::create($validated);

        return response()->json(['success' => true, 'catagory' => $catagory]); // Category
        


    }

    public function destroy($id)
    {
        Catagory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Catagory deleted successfully.');
    }
}
