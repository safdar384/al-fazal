<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\packing;


class PackingController extends Controller
{
     public function index()
    {
        $packings = Packing::all();
        return view('pages.Setup.product', compact('packings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pakingName' => 'required|unique:packing|max:255' // or "Company" if plural
        ]);

        $packing = packing::create($validated);

        return response()->json(['success' => true, 'packing' => $packing]); // Packing
        


    }

    public function destroy($id)
    {
        packing::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Catagory deleted successfully.');
    }
}
