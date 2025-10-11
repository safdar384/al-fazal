<?php

namespace App\Http\Controllers;

use App\Models\CustomerArea;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;

class CustomerAreaController extends Controller
{
    /**
     * Display a listing of customer areas.
     *
     * @return View
     */
    public function index(): View
    {
        $areas = CustomerArea::orderBy('areaName', 'asc')->get();
        
        return view('pages.Setup.area', compact('areas'));
    }

    /**
     * Show the form for creating a new area.
     *
     * @return View
     */
    public function create(): View
    {
        return view('pages.Setup.newArea');
    }

    /**
     * Store a newly created area in storage.
     *
     * @param StoreAreaRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(StoreAreaRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $area = CustomerArea::create($request->validated());

            // Handle AJAX requests
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Area created successfully!',
                    'area' => $area
                ], 201);
            }

            return redirect()
                ->route('areas.index')
                ->with('success', 'Area created successfully!');

        } catch (Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create area.',
                    'errors' => $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create area. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified area.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $area = CustomerArea::findOrFail($id);
        
        return view('pages.Setup.editArea', compact('area'));
    }

    /**
     * Update the specified area in storage.
     *
     * @param UpdateAreaRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateAreaRequest $request, int $id): RedirectResponse
    {
        try {
            $area = CustomerArea::findOrFail($id);
            $area->update($request->validated());

            return redirect()
                ->route('areas.index')
                ->with('success', 'Area updated successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update area. Please try again.');
        }
    }

    /**
     * Remove the specified area from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $area = CustomerArea::findOrFail($id);
            $areaName = $area->areaName;
            $area->delete();

            return redirect()
                ->route('areas.index')
                ->with('success', "Area '{$areaName}' deleted successfully!");

        } catch (Exception $e) {
            return redirect()
                ->route('areas.index')
                ->with('error', 'Failed to delete area. Please try again.');
        }
    }

    /**
     * Get areas list for AJAX requests.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getsAreas(Request $request): JsonResponse
    {
        $query = $request->input('search');
        
        $areas = CustomerArea::when($query, function($q) use ($query) {
            $q->where('areaCode', 'like', "%{$query}%")
              ->orWhere('areaName', 'like', "%{$query}%");
        })->orderBy('areaName', 'asc')->get();

        return response()->json($areas);
    }
}
