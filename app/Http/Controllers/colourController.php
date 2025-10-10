<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colour;

class ColourController extends Controller
{

     public function index()
    {
        $colours = Colour::all();
        return view('pages.Setup.product', compact('colours'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'colourName' => 'required|unique:colour|max:255' // or "colours" if plural
        ]);

        $colour = Colour::create($validated);

        return response()->json(['success' => true, 'colour' => $colour]); // Colour


    }

    public function destroy($id)
    {
        Colour::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Colour deleted successfully.');
    }
     
    
}

