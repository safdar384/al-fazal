<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreColourRequest;
use App\Http\Requests\UpdateColourRequest;
use App\Models\Colour;
use App\Support\ProductLookupUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(StoreColourRequest $request)
    {
        try {
            $colour = Colour::create($request->validated());
            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $colour], 201);
            }

            return redirect()->route('colours.index')->with('success', 'Colour added successfully!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to add colour.'], 422);
            }

            return redirect()->back()->withInput()->with('error', 'Failed to add colour. Please try again.');
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
     * Remove the specified colour (blocked when used by products).
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $colour = Colour::findOrFail($id);

            if (ProductLookupUsage::isUsed('colourName', $colour->colourName)) {
                $message = "Cannot delete \"{$colour->colourName}\" because it is used by one or more products.";

                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $name = $colour->colourName;
            $colour->delete();

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Colour \"{$name}\" deleted successfully!",
                    'data' => ['id' => (int) $id, 'name' => $name],
                ]);
            }

            return redirect()->route('colours.index')
                ->with('success', "Colour \"{$name}\" deleted successfully!");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete colour.'], 422);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete colour. Please try again.');
        }
    }
}
