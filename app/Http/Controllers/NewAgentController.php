<?php

namespace App\Http\Controllers;

use App\Models\NewAgent;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class NewAgentController extends Controller
{
    /**
     * Display a listing of agents.
     *
     * @return View
     */
    public function index(): View
    {
        $agents = NewAgent::orderBy('ajentName', 'asc')->get();
        
        return view('pages.Setup.agent', compact('agents'));
    }

    /**
     * Show the form for creating a new agent.
     *
     * @return View
     */
    public function create(): View
    {
        return view('pages.Setup.NewAgent');
    }

    /**
     * Store a newly created agent in storage.
     *
     * @param StoreAgentRequest $request
     * @return RedirectResponse
     */
    public function store(StoreAgentRequest $request): RedirectResponse
    {
        try {
            NewAgent::create($request->validated());

            return redirect()
                ->route('NewAgent.index')
                ->with('success', 'Agent created successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create agent. Please try again.');
        }
    }

    /**
     * Display the specified agent.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $agent = NewAgent::findOrFail($id);
        
        return view('pages.Setup.showAgent', compact('agent'));
    }

    /**
     * Show the form for editing the specified agent.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $editAgent = NewAgent::findOrFail($id);
        
        return view('pages.Setup.editAgent', compact('editAgent'));
    }

    /**
     * Update the specified agent in storage.
     *
     * @param UpdateAgentRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateAgentRequest $request, int $id): RedirectResponse
    {
        try {
            $agent = NewAgent::findOrFail($id);
            $agent->update($request->validated());

            return redirect()
                ->route('NewAgent.index')
                ->with('success', 'Agent updated successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update agent. Please try again.');
        }
    }

    /**
     * Remove the specified agent from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $agent = NewAgent::findOrFail($id);
            $agentName = $agent->ajentName;
            $agent->delete();

            return redirect()
                ->route('NewAgent.index')
                ->with('success', "Agent '{$agentName}' deleted successfully!");

        } catch (Exception $e) {
            return redirect()
                ->route('NewAgent.index')
                ->with('error', 'Failed to delete agent. Please try again.');
        }
    }
}
