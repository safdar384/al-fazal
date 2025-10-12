<?php

namespace App\Http\Controllers;

use App\Models\Colour;
use App\Http\Requests\StoreColourRequest;
use App\Http\Requests\UpdateColourRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ColourController extends Controller
{
    /**
     * Display a listing of colours
     */
    public function index(): View
    {
        $colours = Colour::orderBy('colourName', 'asc')->get();
        return view('Pages.Setup.colour', compact('colours'));
    }

    /**
     * Show the form for creating a new colour
     */
    public function create(): View
    {
        return view('Pages.Setup.newColour');
    }

    /**
     * Store a newly created colour
     */
    public function store(StoreColourRequest $request): RedirectResponse
    {
        try {
            Colour::create($request->validated());
            
            return redirect()->route('colours.index')
                ->with('success', 'Colour added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add colour. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified colour
     */
    public function edit(string $id): View
    {
        $colour = Colour::findOrFail($id);
        return view('Pages.Setup.editColour', compact('colour'));
    }

    /**
     * Update the specified colour
     */
    public function update(UpdateColourRequest $request, string $id): RedirectResponse
    {
        try {
            $colour = Colour::findOrFail($id);
            $colour->update($request->validated());
            
            return redirect()->route('colours.index')
                ->with('success', 'Colour updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update colour. Please try again.');
        }
    }

    /**
     * Remove the specified colour
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $colour = Colour::findOrFail($id);
            $colour->delete();
            
            return redirect()->route('colours.index')
                ->with('success', 'Colour deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete colour. Please try again.');
        }
    }
}
