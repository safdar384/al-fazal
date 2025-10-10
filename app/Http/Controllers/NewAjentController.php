<?php

namespace App\Http\Controllers;

use App\Models\NewAjent;
use Illuminate\Http\Request;

class NewAjentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ajents = NewAjent::all(); // Fetch all ajenst
        return view('pages.Setup.ajent', compact('ajents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.Setup.NewAjent'); // Return the view for creating a new ajent
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'ajentCode'       => 'required|string|max:50|unique:new_ajensts,ajentCode',
        'ajentName'       => 'required|string|max:255',
        'AjentAdvance'    => 'nullable|numeric|min:0',
        'ajentContact'    => 'nullable|string|max:20',
        'ajentAdderss'    => 'nullable|string|max:255',
        'ajentCommission' => 'nullable|numeric|min:0',
        'ajentBalance'    => 'nullable|numeric|min:0',
    ]);

    $ajent = NewAjent::create($validated);

    return redirect()->route('NewAjent.index')->with('success', 'Ajent created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(NewAjent $newAjent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $editAjent = NewAjent::findOrFail($id);

        return view('pages.Setup.editAjent', compact('editAjent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ajent = NewAjent::findOrFail($id);
        $ajent->update($request->all());
        return redirect()->route('NewAjent.index')->with('success', 'Ajent Updaye successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $newAjent = NewAjent::findOrFail($id);
        $newAjent->delete();
        return redirect()->route('NewAjent.index')->with('success', 'Ajent deleted successfully!');
    }
}
